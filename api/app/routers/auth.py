import hmac
import logging
import time
from datetime import datetime
from typing import Literal

import jwt
from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy import select
from sqlalchemy.orm import Session

from ..config import get_settings
from ..database import get_db
from ..models import Cliente, Doador, Endereco
from ..schemas import (AdminLoginIn, LoginIn, RefreshIn, RegisterIn, RegisterOut,
                       ResendIn, TokenPair, VerifyIn)
from ..security import (create_token, decode_token, generate_code, hash_password,
                        needs_rehash, verify_password)

router = APIRouter(prefix="/auth", tags=["Autenticação"])
log = logging.getLogger("auth")

Role = Literal["doador", "cliente"]
MODELS = {"doador": Doador, "cliente": Cliente}
_last_resend: dict[tuple[str, int], float] = {}  # cooldown em memória (30s), como no app PHP


def issue_tokens(sub: int | str, role: str) -> TokenPair:
    access, exp = create_token(str(sub), role, "access")
    refresh, _ = create_token(str(sub), role, "refresh")
    return TokenPair(access_token=access, refresh_token=refresh, expires_in=exp)


def send_verification_code(user, role: str, code: str) -> None:
    """Ponto de integração: troque por e-mail/SMS. Por padrão só registra no log."""
    log.warning("Código de verificação (%s #%s): %s", role, user.id, code)


# ---------- Admin (declarado antes de /{role}/... para ter prioridade) ----------
@router.post("/admin/login", response_model=TokenPair)
def admin_login(body: AdminLoginIn):
    s = get_settings()
    user_ok = bool(s.admin_username) and hmac.compare_digest(
        s.admin_username.encode(), body.usuario.strip().encode()
    )
    # sempre executa a verificação do hash para não vazar se o usuário existe
    pwd_ok = verify_password(body.senha, s.admin_password_hash or None)
    if not (user_ok and pwd_ok and s.admin_password_hash):
        raise HTTPException(status.HTTP_401_UNAUTHORIZED, "Usuário ou senha inválidos")
    return issue_tokens("admin", "admin")


# ---------- Refresh ----------
@router.post("/refresh", response_model=TokenPair)
def refresh(body: RefreshIn, db: Session = Depends(get_db)):
    try:
        p = decode_token(body.refresh_token, "refresh")
    except jwt.PyJWTError:
        raise HTTPException(status.HTTP_401_UNAUTHORIZED, "Refresh token inválido ou expirado")
    if p["role"] in MODELS:  # garante que a conta ainda existe e está verificada
        user = db.get(MODELS[p["role"]], int(p["sub"]))
        if not user or user.verificado != 1:
            raise HTTPException(status.HTTP_401_UNAUTHORIZED, "Conta inválida")
    return issue_tokens(p["sub"], p["role"])


# ---------- Cadastro / verificação / login ----------
@router.post("/{role}/register", response_model=RegisterOut, status_code=201)
def register(role: Role, body: RegisterIn, db: Session = Depends(get_db)):
    Model = MODELS[role]
    if db.scalar(select(Model).where(Model.email == body.email)):
        raise HTTPException(409, "Email já cadastrado")
    if db.scalar(select(Model).where(Model.celular == body.celular)):
        raise HTTPException(409, "Celular já cadastrado")

    code = generate_code()
    user = Model(
        nome=body.nome.strip(), email=body.email, celular=body.celular,
        data_nas=body.data_nas, senha=hash_password(body.senha),
        codigo_verificacao=code, verificado=0,
    )
    db.add(user)
    db.flush()
    db.add(Endereco(
        idReferencia=user.id, tipo=role, rua=body.rua, complemento=body.complemento,
        bairro=body.bairro, municipio=body.municipio, estado=body.estado, cep=body.cep,
    ))
    db.commit()
    send_verification_code(user, role, code)
    return RegisterOut(
        id=user.id,
        mensagem="Cadastro criado. Confirme o código de verificação.",
        codigo_verificacao=code if get_settings().expose_verification_code else None,
    )


@router.post("/{role}/verify", response_model=TokenPair)
def verify(role: Role, body: VerifyIn, db: Session = Depends(get_db)):
    user = db.get(MODELS[role], body.id)
    if not user or not user.codigo_verificacao or not hmac.compare_digest(
        user.codigo_verificacao.encode(), body.codigo.strip().encode()
    ):
        raise HTTPException(400, "Código incorreto")
    user.verificado = 1
    user.codigo_verificacao = None
    user.ultimo_acesso = datetime.now()
    db.commit()
    return issue_tokens(user.id, role)


@router.post("/{role}/resend-code", status_code=202)
def resend_code(role: Role, body: ResendIn, db: Session = Depends(get_db)):
    key = (role, body.id)
    if time.time() - _last_resend.get(key, 0) < 30:
        raise HTTPException(429, "Aguarde 30 segundos")
    user = db.get(MODELS[role], body.id)
    if user and user.verificado != 1:
        _last_resend[key] = time.time()
        user.codigo_verificacao = generate_code()
        db.commit()
        send_verification_code(user, role, user.codigo_verificacao)
    return {"mensagem": "Se a conta existir e estiver pendente, um novo código foi gerado."}


@router.post("/{role}/login", response_model=TokenPair)
def login(role: Role, body: LoginIn, db: Session = Depends(get_db)):
    user = db.scalar(select(MODELS[role]).where(MODELS[role].email == body.email))
    ok = verify_password(body.senha, user.senha if user else None)
    if not (user and ok):
        raise HTTPException(status.HTTP_401_UNAUTHORIZED, "Email ou senha inválidos")
    if user.verificado != 1:
        raise HTTPException(status.HTTP_403_FORBIDDEN, "Verifique seu número primeiro")
    if needs_rehash(user.senha):  # migra senha legada (texto puro) para bcrypt
        user.senha = hash_password(body.senha)
    user.ultimo_acesso = datetime.now()
    db.commit()
    return issue_tokens(user.id, role)
