"""Mapeamento das tabelas já existentes em app/Database/sistema_doacao.sql.
A API NÃO cria/altera tabelas: o schema continua sendo o do script SQL."""
from datetime import date, datetime

from sqlalchemy import Date, DateTime, ForeignKey, Integer, Numeric, String, UniqueConstraint, func
from sqlalchemy.orm import DeclarativeBase, Mapped, mapped_column


class Base(DeclarativeBase):
    pass


class UserMixin:
    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    nome: Mapped[str] = mapped_column(String(150))
    email: Mapped[str] = mapped_column(String(190), unique=True)
    celular: Mapped[str] = mapped_column(String(20), unique=True)
    data_nas: Mapped[date | None] = mapped_column(Date, nullable=True)
    senha: Mapped[str] = mapped_column(String(255))
    codigo_verificacao: Mapped[str | None] = mapped_column(String(20), nullable=True)
    verificado: Mapped[int] = mapped_column(Integer, default=0)
    ultimo_acesso: Mapped[datetime | None] = mapped_column(DateTime, nullable=True)


class Doador(UserMixin, Base):
    __tablename__ = "doador"


class Cliente(UserMixin, Base):
    __tablename__ = "cliente"


class Endereco(Base):
    __tablename__ = "endereco"
    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    idReferencia: Mapped[int] = mapped_column(Integer)
    tipo: Mapped[str] = mapped_column(String(20))
    rua: Mapped[str | None] = mapped_column(String(190), nullable=True)
    complemento: Mapped[str | None] = mapped_column(String(190), nullable=True)
    bairro: Mapped[str | None] = mapped_column(String(120), nullable=True)
    municipio: Mapped[str | None] = mapped_column(String(120), nullable=True)
    estado: Mapped[str | None] = mapped_column(String(60), nullable=True)
    local_doacao: Mapped[str | None] = mapped_column(String(255), nullable=True)
    cep: Mapped[str | None] = mapped_column(String(20), nullable=True)


class Roupa(Base):
    __tablename__ = "roupas"
    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    tipo: Mapped[str] = mapped_column(String(120))
    cor: Mapped[str | None] = mapped_column(String(80), nullable=True)
    quantidade: Mapped[int] = mapped_column(Integer, default=1)
    bairro: Mapped[str | None] = mapped_column(String(120), nullable=True)
    ponto_doacao: Mapped[str | None] = mapped_column(String(255), nullable=True)
    latitude: Mapped[float | None] = mapped_column(Numeric(10, 7, asdecimal=False), nullable=True)
    longitude: Mapped[float | None] = mapped_column(Numeric(10, 7, asdecimal=False), nullable=True)
    idDoador: Mapped[int] = mapped_column(ForeignKey("doador.id", ondelete="CASCADE"))
    imagem: Mapped[str | None] = mapped_column(String(255), nullable=True)
    criado_em: Mapped[datetime] = mapped_column(DateTime, server_default=func.now())
    status: Mapped[str] = mapped_column(String(30), default="disponível", server_default="disponível")
    data_doado: Mapped[datetime | None] = mapped_column(DateTime, nullable=True)


class Interesse(Base):
    __tablename__ = "interesse"
    __table_args__ = (UniqueConstraint("idRoupa", "idCliente", name="uq_interesse_item_cliente"),)
    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    idRoupa: Mapped[int] = mapped_column(ForeignKey("roupas.id", ondelete="CASCADE"))
    idCliente: Mapped[int] = mapped_column(ForeignKey("cliente.id", ondelete="CASCADE"))
    data_interesse: Mapped[datetime] = mapped_column(DateTime, server_default=func.now())
