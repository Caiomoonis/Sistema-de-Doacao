import csv
import io
from datetime import datetime

from fastapi import APIRouter, Depends
from fastapi.responses import Response
from sqlalchemy import func, select
from sqlalchemy.orm import Session

from ..database import get_db
from ..deps import current_admin
from ..models import Cliente, Doador, Interesse, Roupa

router = APIRouter(prefix="/admin", tags=["Admin"], dependencies=[Depends(current_admin)])


def _count(db: Session, model) -> int:
    return db.scalar(select(func.count()).select_from(model)) or 0


@router.get("/stats")
def stats(db: Session = Depends(get_db)):
    return {
        "doadores": _count(db, Doador),
        "beneficiarios": _count(db, Cliente),
        "itens": _count(db, Roupa),
        "interesses": _count(db, Interesse),
    }


@router.get("/exportar")
def exportar(db: Session = Depends(get_db)):
    s = stats(db)
    buf = io.StringIO()
    w = csv.writer(buf, delimiter=";")
    w.writerow(["RELATÓRIO DE IMPACTO — VESTE+"])
    w.writerow(["Gerado em", datetime.now().strftime("%d/%m/%Y %H:%M")])
    w.writerow([])
    w.writerow(["Indicador", "Total"])
    for label, key in [("Doadores cadastrados", "doadores"), ("Beneficiários cadastrados", "beneficiarios"),
                       ("Itens registrados", "itens"), ("Interesses registrados", "interesses")]:
        w.writerow([label, s[key]])
    w.writerow([])
    w.writerow(["ITENS DOADOS"])
    w.writerow(["ID", "Item", "Cor", "Quantidade", "Status", "Doador", "Latitude", "Longitude"])
    rows = db.execute(select(Roupa.id, Roupa.tipo, Roupa.cor, Roupa.quantidade, Roupa.status,
                             Doador.nome, Roupa.latitude, Roupa.longitude)
                      .join(Doador, Doador.id == Roupa.idDoador).order_by(Roupa.id.desc()))
    for row in rows:
        # evita injeção de fórmula ao abrir no Excel
        w.writerow([("'" + c) if isinstance(c, str) and c[:1] in "=+-@" else c for c in row])
    fname = f"relatorio-impacto-{datetime.now():%Y-%m-%d}.csv"
    return Response("\ufeff" + buf.getvalue(), media_type="text/csv; charset=UTF-8",
                    headers={"Content-Disposition": f'attachment; filename="{fname}"'})
