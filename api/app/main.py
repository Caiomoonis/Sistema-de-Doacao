from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from .config import get_settings
from .routers import admin, auth, cliente, doador

app = FastAPI(title="Veste+ — API Sistema de Doação", version="1.0.0")

origins = get_settings().cors_origins
if origins:
    app.add_middleware(CORSMiddleware, allow_origins=origins, allow_methods=["*"],
                       allow_headers=["*"], allow_credentials=False)

app.include_router(auth.router)
app.include_router(doador.router)
app.include_router(cliente.catalogo_router)
app.include_router(cliente.router)
app.include_router(admin.router)


@app.get("/health", tags=["Infra"])
def health():
    return {"status": "ok"}
