import time

import jwt
import pytest

from app.config import get_settings
from app.models import Doador
from app.routers import auth as auth_router

PNG = b"\x89PNG\r\n\x1a\n" + b"\x00" * 32


def reg_body(n=1, **kw):
    b = {"nome": f"Pessoa {n}", "email": f"p{n}@ex.com", "celular": f"2199999000{n}",
         "senha": "senha-forte-1", "bairro": "Centro"}
    b.update(kw)
    return b


def signup(client, role, n=1):
    r = client.post(f"/auth/{role}/register", json=reg_body(n))
    assert r.status_code == 201, r.text
    d = r.json()
    v = client.post(f"/auth/{role}/verify", json={"id": d["id"], "codigo": d["codigo_verificacao"]})
    assert v.status_code == 200, v.text
    return d["id"], v.json()


def auth(tokens):
    return {"Authorization": f"Bearer {tokens['access_token']}"}


# ---------------- autenticação ----------------
def test_register_hashes_password_and_blocks_login_until_verified(client):
    r = client.post("/auth/doador/register", json=reg_body())
    uid = r.json()["id"]
    with client.app.state.Session() as db:
        stored = db.get(Doador, uid).senha
    assert stored.startswith("$2") and stored != "senha-forte-1"
    login = client.post("/auth/doador/login", json={"email": "p1@ex.com", "senha": "senha-forte-1"})
    assert login.status_code == 403


def test_login_ok_wrong_password_and_unknown_user(client):
    signup(client, "doador")
    ok = client.post("/auth/doador/login", json={"email": "p1@ex.com", "senha": "senha-forte-1"})
    assert ok.status_code == 200 and {"access_token", "refresh_token"} <= ok.json().keys()
    bad = client.post("/auth/doador/login", json={"email": "p1@ex.com", "senha": "errada-123"})
    nobody = client.post("/auth/doador/login", json={"email": "x@ex.com", "senha": "errada-123"})
    assert bad.status_code == nobody.status_code == 401
    assert bad.json() == nobody.json()  # não revela se o e-mail existe


def test_wrong_verification_code(client):
    d = client.post("/auth/cliente/register", json=reg_body()).json()
    assert client.post("/auth/cliente/verify", json={"id": d["id"], "codigo": "000000"}).status_code == 400


def test_duplicate_email_and_phone(client):
    signup(client, "doador")
    assert client.post("/auth/doador/register", json=reg_body()).status_code == 409
    assert client.post("/auth/doador/register",
                       json=reg_body(2, celular="21999990001")).status_code == 409


def test_legacy_plaintext_password_is_migrated_on_login(client):
    uid, _ = signup(client, "doador")
    with client.app.state.Session() as db:
        u = db.get(Doador, uid)
        u.senha = "legada123"
        db.commit()
    r = client.post("/auth/doador/login", json={"email": "p1@ex.com", "senha": "legada123"})
    assert r.status_code == 200
    with client.app.state.Session() as db:
        assert db.get(Doador, uid).senha.startswith("$2")
    assert client.post("/auth/doador/login", json={"email": "p1@ex.com", "senha": "legada123"}).status_code == 200


def test_protected_route_requires_valid_token(client):
    assert client.get("/doador/me").status_code == 401
    assert client.get("/doador/me", headers={"Authorization": "Bearer lixo"}).status_code == 401


def test_expired_token_rejected(client):
    uid, _ = signup(client, "doador")
    s = get_settings()
    expired = jwt.encode({"sub": str(uid), "role": "doador", "type": "access", "iss": s.jwt_issuer,
                          "iat": int(time.time()) - 100, "exp": int(time.time()) - 10},
                         s.jwt_secret, algorithm="HS256")
    assert client.get("/doador/me", headers={"Authorization": f"Bearer {expired}"}).status_code == 401


def test_token_signed_with_other_secret_or_alg_none_rejected(client):
    uid, _ = signup(client, "doador")
    s = get_settings()
    claims = {"sub": str(uid), "role": "doador", "type": "access", "iss": s.jwt_issuer,
              "iat": int(time.time()), "exp": int(time.time()) + 600}
    forged = jwt.encode(claims, "y" * 48, algorithm="HS256")
    none_tok = jwt.encode(claims, None, algorithm="none")
    for t in (forged, none_tok):
        assert client.get("/doador/me", headers={"Authorization": f"Bearer {t}"}).status_code == 401


def test_refresh_token_cannot_be_used_as_access_and_vice_versa(client):
    _, tk = signup(client, "doador")
    assert client.get("/doador/me", headers={"Authorization": f"Bearer {tk['refresh_token']}"}).status_code == 401
    assert client.post("/auth/refresh", json={"refresh_token": tk["access_token"]}).status_code == 401
    r = client.post("/auth/refresh", json={"refresh_token": tk["refresh_token"]})
    assert r.status_code == 200
    assert client.get("/doador/me", headers=auth(r.json())).status_code == 200


def test_role_separation(client):
    _, d = signup(client, "doador")
    _, c = signup(client, "cliente", 2)
    assert client.get("/doador/me", headers=auth(c)).status_code == 403
    assert client.get("/cliente/me", headers=auth(d)).status_code == 403
    assert client.get("/admin/stats", headers=auth(d)).status_code == 403


def test_admin_login_and_stats(client):
    assert client.post("/auth/admin/login", json={"usuario": "admin", "senha": "x"}).status_code == 401
    r = client.post("/auth/admin/login", json={"usuario": "admin", "senha": "admin-pass-123"})
    assert r.status_code == 200
    s = client.get("/admin/stats", headers=auth(r.json()))
    assert s.status_code == 200 and s.json()["doadores"] == 0
    csv = client.get("/admin/exportar", headers=auth(r.json()))
    assert csv.status_code == 200 and "RELAT" in csv.text


def test_resend_code_cooldown(client):
    d = client.post("/auth/doador/register", json=reg_body()).json()
    auth_router._last_resend.clear()
    assert client.post("/auth/doador/resend-code", json={"id": d["id"]}).status_code == 202
    assert client.post("/auth/doador/resend-code", json={"id": d["id"]}).status_code == 429


# ---------------- fluxo de negócio ----------------
def test_full_donation_flow(client):
    _, d = signup(client, "doador", 1)
    _, c = signup(client, "cliente", 2)

    r = client.post("/doador/roupas", headers=auth(d),
                    data={"tipo": "Casaco", "cor": "azul", "quantidade": 2,
                          "latitude": -22.9068, "longitude": -43.1729},
                    files={"imagem": ("a.png", PNG, "image/png")})
    assert r.status_code == 201, r.text
    item = r.json()
    assert item["imagem"].endswith(".png") and item["status"] == "disponível"

    cat = client.get("/catalogo").json()
    assert len(cat) == 1 and "doador_email" not in cat[0]
    near = client.get("/catalogo", params={"latitude": -22.90, "longitude": -43.17, "raio": 5}).json()
    far = client.get("/catalogo", params={"latitude": -23.55, "longitude": -46.63, "raio": 5}).json()
    assert len(near) == 1 and near[0]["distancia_km"] < 5 and far == []

    assert client.post(f"/cliente/interesse/{item['id']}").status_code == 401
    assert client.post(f"/cliente/interesse/{item['id']}", headers=auth(c)).json()["registrado"] is True
    assert client.post(f"/cliente/interesse/{item['id']}", headers=auth(c)).json()["registrado"] is False
    assert len(client.get("/cliente/historico", headers=auth(c)).json()) == 1
    assert client.get(f"/cliente/roupas/{item['id']}", headers=auth(c)).json()["doador_email"] == "p1@ex.com"

    inter = client.get("/doador/interessados", headers=auth(d)).json()
    assert len(inter) == 1 and inter[0]["email"] == "p2@ex.com"

    assert client.post(f"/doador/roupas/{item['id']}/doado", headers=auth(d)).json()["status"] == "doado"
    assert client.get("/catalogo").json() == []
    assert client.post(f"/cliente/interesse/{item['id']}", headers=auth(c)).status_code == 404


def test_donor_cannot_touch_other_donors_items(client):
    _, d1 = signup(client, "doador", 1)
    _, d2 = signup(client, "doador", 3)
    item = client.post("/doador/roupas", headers=auth(d1), data={"tipo": "Calça"}).json()
    assert client.delete(f"/doador/roupas/{item['id']}", headers=auth(d2)).status_code == 404
    assert client.post(f"/doador/roupas/{item['id']}/doado", headers=auth(d2)).status_code == 404
    assert client.get("/doador/interessados", headers=auth(d2), params={"item_id": item["id"]}).json() == []
    assert client.delete(f"/doador/roupas/{item['id']}", headers=auth(d1)).status_code == 204


def test_upload_rejects_non_image(client):
    _, d = signup(client, "doador")
    r = client.post("/doador/roupas", headers=auth(d), data={"tipo": "Camisa"},
                    files={"imagem": ("x.png", b"<?php echo 1; ?>", "image/png")})
    assert r.status_code == 415


def test_profile_update_changes_password_with_hash(client):
    _, d = signup(client, "doador")
    r = client.put("/doador/me", headers=auth(d), json={"nome": "Novo Nome", "senha": "outra-senha-9", "cidade": "x",
                                                         "municipio": "Niterói"})
    assert r.status_code == 200 and r.json()["nome"] == "Novo Nome" and r.json()["endereco"]["municipio"] == "Niterói"
    assert client.post("/auth/doador/login", json={"email": "p1@ex.com", "senha": "outra-senha-9"}).status_code == 200
    assert client.post("/auth/doador/login", json={"email": "p1@ex.com", "senha": "senha-forte-1"}).status_code == 401
