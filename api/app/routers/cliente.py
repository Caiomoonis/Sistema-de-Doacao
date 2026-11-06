import math
from typing import Annotated

from fastapi import APIRouter, Depends, HTTPException, Query
from sqlalchemy import select
from sqlalchemy.orm import Session

from ..database import get_db
from ..deps import current_cliente
from ..models import Cliente, Doador, Endereco, Interesse, Roupa
from ..schemas import (HistoricoOut, ProfileOut, ProfileUpdate, RoupaCatalogo,
                       RoupaDetalheCliente, RoupaOut)
from ..security import hash_password

catalogo_router = APIRouter(tags=["Catálogo (público)"])
router = APIRouter(prefix="/cliente", tags=["Cliente"])


def _build(cls, roupa: Roupa, **extra):
    return cls(**RoupaOut.model_validate(roupa, from_attributes=True).model_dump(), **extra)


def _haversine_km(lat1, lon1, lat2, lon2) -> float:
    p1, p2 = math.radians(lat1), math.radians(lat2)
    dphi, dl = p2 - p1, math.radians(lon2 - lon1)
    a = math.sin(dphi / 2) ** 2 + math.cos(p1) * math.cos(p2) * math.sin(dl / 2) ** 2
    return 6371.0088 * 2 * math.asin(math.sqrt(a))


@catalogo_router.get("/catalogo", response_model=list[RoupaCatalogo])
def catalogo(
    latitude: Annotated[float | None, Query(ge=-90, le=90)] = None,
    longitude: Annotated[float | None, Query(ge=-180, le=180)] = None,
    raio: Annotated[float, Query(gt=0, le=500, description="km")] = 10,
    limit: Annotated[int, Query(ge=1, le=200)] = 50,
    offset: Annotated[int, Query(ge=0)] = 0,
    db: Session = Depends(get_db),
):
    if (latitude is None) != (longitude is None):
        raise HTTPException(422, "Informe latitude e longitude juntas")
    rows = db.execute(
        select(Roupa, Doador.nome).join(Doador, Doador.id == Roupa.idDoador)
        .where(Roupa.status == "disponível").order_by(Roupa.id.desc())
    ).all()
    out = []
    for r, nome_doador in rows:
        dist = None
        if latitude is not None:
            if r.latitude is None or r.longitude is None:
                continue
            dist = _haversine_km(latitude, longitude, r.latitude, r.longitude)
            if dist > raio:
                continue
        out.append(_build(RoupaCatalogo, r, nome_doador=nome_doador, distancia_km=dist))
    if latitude is not None:
        out.sort(key=lambda x: x.distancia_km)
    return out[offset:offset + limit]


@catalogo_router.get("/catalogo/{item_id}", response_model=RoupaCatalogo)
def catalogo_item(item_id: int, db: Session = Depends(get_db)):
    row = db.execute(select(Roupa, Doador.nome).join(Doador, Doador.id == Roupa.idDoador)
                     .where(Roupa.id == item_id)).first()
    if not row:
        raise HTTPException(404, "Item não encontrado")
    return _build(RoupaCatalogo, row[0], nome_doador=row[1])


# ---------- Área autenticada do cliente ----------
@router.get("/roupas/{item_id}", response_model=RoupaDetalheCliente)
def detalhes(item_id: int, _: Cliente = Depends(current_cliente), db: Session = Depends(get_db)):
    """Detalhe com contato do doador (só para clientes autenticados)."""
    row = db.execute(select(Roupa, Doador).join(Doador, Doador.id == Roupa.idDoador)
                     .where(Roupa.id == item_id)).first()
    if not row:
        raise HTTPException(404, "Item não encontrado")
    r, d = row
    return _build(RoupaDetalheCliente, r, nome_doador=d.nome,
                  doador_email=d.email, doador_celular=d.celular)


@router.post("/interesse/{item_id}", status_code=201)
def interesse(item_id: int, c: Cliente = Depends(current_cliente), db: Session = Depends(get_db)):
    r = db.get(Roupa, item_id)
    if not r or r.status != "disponível":
        raise HTTPException(404, "Item não encontrado ou indisponível")
    if db.scalar(select(Interesse).where(Interesse.idRoupa == item_id, Interesse.idCliente == c.id)):
        return {"registrado": False, "mensagem": "Interesse já registrado"}
    db.add(Interesse(idRoupa=item_id, idCliente=c.id))
    db.commit()
    return {"registrado": True, "mensagem": "Interesse registrado!"}


@router.get("/historico", response_model=list[HistoricoOut])
def historico(c: Cliente = Depends(current_cliente), db: Session = Depends(get_db)):
    q = (select(Interesse.idRoupa, Roupa.tipo, Roupa.cor, Roupa.imagem, Roupa.status,
                Interesse.data_interesse)
         .join(Roupa, Roupa.id == Interesse.idRoupa)
         .where(Interesse.idCliente == c.id).order_by(Interesse.data_interesse.desc()))
    return [HistoricoOut(**dict(row._mapping)) for row in db.execute(q)]


def _profile(db: Session, u: Cliente) -> ProfileOut:
    end = db.scalar(select(Endereco).where(Endereco.idReferencia == u.id, Endereco.tipo == "cliente"))
    return ProfileOut(id=u.id, nome=u.nome, email=u.email, celular=u.celular,
                      data_nas=u.data_nas, endereco=end)


@router.get("/me", response_model=ProfileOut)
def me(u: Cliente = Depends(current_cliente), db: Session = Depends(get_db)):
    return _profile(db, u)


@router.put("/me", response_model=ProfileOut)
def update_me(body: ProfileUpdate, u: Cliente = Depends(current_cliente), db: Session = Depends(get_db)):
    data = body.model_dump(exclude_unset=True)
    for field in ("email", "celular"):
        if data.get(field) and data[field] != getattr(u, field):
            if db.scalar(select(Cliente).where(getattr(Cliente, field) == data[field], Cliente.id != u.id)):
                raise HTTPException(409, f"{field.capitalize()} já cadastrado")
    for field in ("nome", "email", "celular", "data_nas"):
        if data.get(field) is not None:
            setattr(u, field, data[field])
    if data.get("senha"):
        u.senha = hash_password(data["senha"])
    end = db.scalar(select(Endereco).where(Endereco.idReferencia == u.id, Endereco.tipo == "cliente"))
    if not end:
        end = Endereco(idReferencia=u.id, tipo="cliente")
        db.add(end)
    for field in ("rua", "complemento", "bairro", "municipio", "estado", "cep"):
        if field in data:
            setattr(end, field, data[field])
    db.commit()
    return _profile(db, u)
