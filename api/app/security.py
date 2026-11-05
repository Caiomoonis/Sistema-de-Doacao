import hmac
import secrets
import uuid
from datetime import datetime, timedelta, timezone
from typing import Literal

import bcrypt
import jwt

from .config import get_settings

TokenType = Literal["access", "refresh"]

# hash descartável para igualar o tempo de resposta quando o usuário não existe
_DUMMY_HASH = bcrypt.hashpw(b"dummy-password", bcrypt.gensalt()).decode()


def _is_bcrypt(stored: str) -> bool:
    return stored.startswith(("$2a$", "$2b$", "$2y$"))


def hash_password(password: str) -> str:
    return bcrypt.hashpw(password.encode(), bcrypt.gensalt()).decode()


def verify_password(password: str, stored: str | None) -> bool:
    """Aceita bcrypt (inclui $2y$ do PHP) e, para migração, senha legada em texto puro."""
    if stored is None:
        try:
            bcrypt.checkpw(password.encode(), _DUMMY_HASH.encode())
        except ValueError:
            pass
        return False
    if _is_bcrypt(stored):
        try:
            return bcrypt.checkpw(password.encode(), stored.encode())
        except ValueError:
            return False
    return hmac.compare_digest(password.encode(), stored.encode())


def needs_rehash(stored: str) -> bool:
    """True para senhas legadas em texto puro (devem ser convertidas para bcrypt no login)."""
    return not _is_bcrypt(stored)


def generate_code() -> str:
    return f"{secrets.randbelow(900000) + 100000}"


def create_token(sub: str, role: str, token_type: TokenType) -> tuple[str, int]:
    s = get_settings()
    now = datetime.now(timezone.utc)
    delta = (
        timedelta(minutes=s.access_token_minutes)
        if token_type == "access"
        else timedelta(days=s.refresh_token_days)
    )
    payload = {
        "sub": sub,
        "role": role,
        "type": token_type,
        "iss": s.jwt_issuer,
        "iat": now,
        "exp": now + delta,
        "jti": uuid.uuid4().hex,
    }
    return jwt.encode(payload, s.jwt_secret, algorithm=s.jwt_algorithm), int(delta.total_seconds())


def decode_token(token: str, expected_type: TokenType) -> dict:
    """Levanta jwt.PyJWTError se inválido/expirado/tipo errado."""
    s = get_settings()
    payload = jwt.decode(
        token,
        s.jwt_secret,
        algorithms=[s.jwt_algorithm],  # fixa o algoritmo: nunca aceitar "none"
        issuer=s.jwt_issuer,
        options={"require": ["exp", "iat", "sub", "role", "type"]},
    )
    if payload["type"] != expected_type:
        raise jwt.InvalidTokenError("tipo de token incorreto")
    return payload
