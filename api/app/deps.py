import jwt
from fastapi import Depends, HTTPException, status
from fastapi.security import HTTPAuthorizationCredentials, HTTPBearer
from sqlalchemy.orm import Session

from .database import get_db
from .models import Cliente, Doador
from .security import decode_token

bearer = HTTPBearer(auto_error=False, description="Cole o access_token retornado no login")

_401 = HTTPException(
    status_code=status.HTTP_401_UNAUTHORIZED,
    detail="Token ausente, inválido ou expirado",
    headers={"WWW-Authenticate": "Bearer"},
)


def _payload(creds: HTTPAuthorizationCredentials | None, role: str) -> dict:
    if creds is None:
        raise _401
    try:
        payload = decode_token(creds.credentials, "access")
    except jwt.PyJWTError:
        raise _401
    if payload["role"] != role:
        raise HTTPException(status.HTTP_403_FORBIDDEN, "Acesso não permitido para este perfil")
    return payload


def current_doador(creds=Depends(bearer), db: Session = Depends(get_db)) -> Doador:
    p = _payload(creds, "doador")
    user = db.get(Doador, int(p["sub"]))
    if not user or user.verificado != 1:
        raise _401
    return user


def current_cliente(creds=Depends(bearer), db: Session = Depends(get_db)) -> Cliente:
    p = _payload(creds, "cliente")
    user = db.get(Cliente, int(p["sub"]))
    if not user or user.verificado != 1:
        raise _401
    return user


def current_admin(creds=Depends(bearer)) -> dict:
    return _payload(creds, "admin")
