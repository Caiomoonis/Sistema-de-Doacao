import re
from datetime import date, datetime

from pydantic import BaseModel, ConfigDict, EmailStr, Field, field_validator


def _check_password(v: str) -> str:
    if len(v.encode()) > 72:  # limite do bcrypt
        raise ValueError("senha deve ter no máximo 72 bytes")
    return v


def _check_celular(v: str) -> str:
    digits = re.sub(r"\D", "", v)
    if len(digits) != 11:
        raise ValueError("celular inválido (use DDD + 9 dígitos)")
    return digits


class TokenPair(BaseModel):
    access_token: str
    refresh_token: str
    token_type: str = "bearer"
    expires_in: int


class RefreshIn(BaseModel):
    refresh_token: str


class LoginIn(BaseModel):
    email: EmailStr
    senha: str = Field(min_length=1, max_length=72)


class AdminLoginIn(BaseModel):
    usuario: str
    senha: str = Field(min_length=1, max_length=72)


class EnderecoIn(BaseModel):
    rua: str | None = Field(default=None, max_length=190)
    complemento: str | None = Field(default=None, max_length=190)
    bairro: str | None = Field(default=None, max_length=120)
    municipio: str | None = Field(default=None, max_length=120)
    estado: str | None = Field(default=None, max_length=60)
    cep: str | None = Field(default=None, max_length=20)


class EnderecoOut(EnderecoIn):
    model_config = ConfigDict(from_attributes=True)


class RegisterIn(EnderecoIn):
    nome: str = Field(min_length=1, max_length=150)
    email: EmailStr
    celular: str
    data_nas: date | None = None
    senha: str = Field(min_length=8, max_length=72)

    _v_cel = field_validator("celular")(_check_celular)
    _v_pwd = field_validator("senha")(_check_password)


class RegisterOut(BaseModel):
    id: int
    mensagem: str
    codigo_verificacao: str | None = None  # só com EXPOSE_VERIFICATION_CODE=true (dev)


class VerifyIn(BaseModel):
    id: int
    codigo: str = Field(min_length=1, max_length=20)


class ResendIn(BaseModel):
    id: int


class ProfileOut(BaseModel):
    id: int
    nome: str
    email: str
    celular: str
    data_nas: date | None
    endereco: EnderecoOut | None = None


class ProfileUpdate(EnderecoIn):
    nome: str | None = Field(default=None, min_length=1, max_length=150)
    email: EmailStr | None = None
    celular: str | None = None
    data_nas: date | None = None
    senha: str | None = Field(default=None, min_length=8, max_length=72)

    @field_validator("celular")
    @classmethod
    def _cel(cls, v):
        return _check_celular(v) if v else v

    @field_validator("senha")
    @classmethod
    def _pwd(cls, v):
        return _check_password(v) if v else v


class RoupaOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)
    id: int
    tipo: str
    cor: str | None
    quantidade: int
    bairro: str | None
    ponto_doacao: str | None
    latitude: float | None
    longitude: float | None
    imagem: str | None
    criado_em: datetime | None
    status: str
    data_doado: datetime | None = None
    idDoador: int


class RoupaCatalogo(RoupaOut):
    nome_doador: str
    distancia_km: float | None = None


class RoupaDetalheCliente(RoupaCatalogo):
    doador_email: str
    doador_celular: str


class InteressadoOut(BaseModel):
    idRoupa: int
    tipo: str
    cor: str | None
    nome: str
    celular: str
    email: str
    data_interesse: datetime


class HistoricoOut(BaseModel):
    idRoupa: int
    tipo: str
    cor: str | None
    imagem: str | None
    status: str
    data_interesse: datetime
