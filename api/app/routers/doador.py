import uuid
from datetime import datetime, timedelta
from pathlib import Path

from fastapi import APIRouter, Depends, File, Form, HTTPException, UploadFile
from sqlalchemy import select
from sqlalchemy.orm import Session

from ..config import get_settings
from ..database import get_db
from ..deps import current_doador
from ..models import Cliente, Doador, Endereco, Interesse, Roupa
from ..schemas import InteressadoOut, ProfileOut, ProfileUpdate, RoupaOut
from ..security import hash_password

router = APIRouter(prefix="/doador", tags=["Doador"])


def _sniff_image(b: bytes) -> str | None:
    """Detecta o tipo pelos magic bytes (imghdr foi removido no Python 3.13)."""
    if b.startswith(b"\xff\xd8\xff"):
        return ".jpg"
    if b.startswith(b"\x89PNG\r\n\x1a\n"):
        return ".png"
    if b[:6] in (b"GIF87a", b"GIF89a"):
        return ".gif"
    if b[:4] == b"RIFF" and b[8:12] == b"WEBP":
        return ".webp"
    return None


def _upload_dir() -> Path:
    d = Path(get_settings().upload_dir)
    d.mkdir(parents=True, exist_ok=True)
    return d


def limpar_doacoes(db: Session) -> None:
    """Apaga itens marcados como doados há mais de 7 dias (regra do app PHP)."""
    limite = datetime.now() - timedelta(days=7)
    itens = db.scalars(select(Roupa).where(Roupa.status == "doado", Roupa.data_doado <= limite)).all()
    for r in itens:
        if r.imagem:
            (_upload_dir() / Path(r.imagem).name).unlink(missing_ok=True)
        db.delete(r)
    db.commit()


def _own_item(db: Session, item_id: int, doador: Doador) -> Roupa:
    r = db.get(Roupa, item_id)
    if not r or r.idDoador != doador.id:  # 404 também para item alheio (não vaza existência)
        raise HTTPException(404, "Item não encontrado")
    return r


# ---------- Perfil ----------
def _profile(db: Session, u: Doador) -> ProfileOut:
    end = db.scalar(select(Endereco).where(Endereco.idReferencia == u.id, Endereco.tipo == "doador"))
    return ProfileOut(id=u.id, nome=u.nome, email=u.email, celular=u.celular,
                      data_nas=u.data_nas, endereco=end)


@router.get("/me", response_model=ProfileOut)
def me(u: Doador = Depends(current_doador), db: Session = Depends(get_db)):
    return _profile(db, u)


@router.put("/me", response_model=ProfileOut)
def update_me(body: ProfileUpdate, u: Doador = Depends(current_doador), db: Session = Depends(get_db)):
    data = body.model_dump(exclude_unset=True)
    for field in ("email", "celular"):
        if data.get(field) and data[field] != getattr(u, field):
            if db.scalar(select(Doador).where(getattr(Doador, field) == data[field], Doador.id != u.id)):
                raise HTTPException(409, f"{field.capitalize()} já cadastrado")
    for field in ("nome", "email", "celular", "data_nas"):
        if data.get(field) is not None:
            setattr(u, field, data[field])
    if data.get("senha"):
        u.senha = hash_password(data["senha"])

    end = db.scalar(select(Endereco).where(Endereco.idReferencia == u.id, Endereco.tipo == "doador"))
    if not end:
        end = Endereco(idReferencia=u.id, tipo="doador")
        db.add(end)
    for field in ("rua", "complemento", "bairro", "municipio", "estado", "cep"):
        if field in data:
            setattr(end, field, data[field])
    db.commit()
    return _profile(db, u)


# ---------- Roupas ----------
@router.get("/roupas", response_model=list[RoupaOut])
def listar(u: Doador = Depends(current_doador), db: Session = Depends(get_db)):
    limpar_doacoes(db)
    return db.scalars(select(Roupa).where(Roupa.idDoador == u.id).order_by(Roupa.id.desc())).all()


@router.post("/roupas", response_model=RoupaOut, status_code=201)
async def cadastrar(
    tipo: str = Form(min_length=1, max_length=120),
    cor: str | None = Form(None, max_length=80),
    quantidade: int = Form(1, ge=1, le=1000),
    bairro: str | None = Form(None, max_length=120),
    ponto_doacao: str | None = Form(None, max_length=255),
    latitude: float | None = Form(None, ge=-90, le=90),
    longitude: float | None = Form(None, ge=-180, le=180),
    imagem: UploadFile | None = File(None),
    u: Doador = Depends(current_doador),
    db: Session = Depends(get_db),
):
    nome_img = None
    if imagem is not None and imagem.filename:
        max_bytes = get_settings().max_upload_mb * 1024 * 1024
        content = await imagem.read(max_bytes + 1)
        if len(content) > max_bytes:
            raise HTTPException(413, f"Imagem maior que {get_settings().max_upload_mb} MB")
        ext = _sniff_image(content)  # valida pelo conteúdo, não pelo nome/Content-Type
        if ext is None:
            raise HTTPException(415, "Envie uma imagem JPEG, PNG, GIF ou WebP")
        nome_img = f"{uuid.uuid4().hex}{ext}"
        (_upload_dir() / nome_img).write_bytes(content)

    r = Roupa(tipo=tipo.strip(), cor=cor, quantidade=quantidade, bairro=bairro,
              ponto_doacao=ponto_doacao, latitude=latitude, longitude=longitude,
              idDoador=u.id, imagem=nome_img, criado_em=datetime.now(), status="disponível")
    db.add(r)
    db.commit()
    return r


@router.delete("/roupas/{item_id}", status_code=204)
def excluir(item_id: int, u: Doador = Depends(current_doador), db: Session = Depends(get_db)):
    r = _own_item(db, item_id, u)
    if r.imagem:
        (_upload_dir() / Path(r.imagem).name).unlink(missing_ok=True)
    db.delete(r)
    db.commit()


@router.post("/roupas/{item_id}/doado", response_model=RoupaOut)
def marcar_doado(item_id: int, u: Doador = Depends(current_doador), db: Session = Depends(get_db)):
    r = _own_item(db, item_id, u)
    r.status = "doado"
    r.data_doado = datetime.now()
    db.commit()
    return r


@router.get("/interessados", response_model=list[InteressadoOut])
def interessados(item_id: int | None = None, u: Doador = Depends(current_doador),
                 db: Session = Depends(get_db)):
    q = (select(Interesse.idRoupa, Roupa.tipo, Roupa.cor, Cliente.nome, Cliente.celular,
                Cliente.email, Interesse.data_interesse)
         .join(Roupa, Roupa.id == Interesse.idRoupa)
         .join(Cliente, Cliente.id == Interesse.idCliente)
         .where(Roupa.idDoador == u.id)
         .order_by(Interesse.data_interesse.desc()))
    if item_id is not None:
        q = q.where(Interesse.idRoupa == item_id)
    return [InteressadoOut(**dict(row._mapping)) for row in db.execute(q)]
