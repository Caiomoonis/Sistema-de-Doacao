# API Python (FastAPI) com autenticação JWT

API REST que usa **o mesmo banco MySQL** do app PHP (`app/Database/sistema_doacao.sql`).
Não cria nem altera tabelas.

## Rodando
```bash
cd api
python -m venv .venv && source .venv/bin/activate      # Windows: .venv\Scripts\activate
pip install -r requirements.txt
cp .env.example .env     # preencha JWT_SECRET, DATABASE_URL e o admin
uvicorn app.main:app --reload --port 8000
```
Documentação interativa: http://localhost:8000/docs (botão **Authorize** → cole o `access_token`).

Testes (usam SQLite em memória, não tocam no MySQL): `python -m pytest -q`

## Autenticação
| Endpoint | Descrição |
|---|---|
| `POST /auth/{doador\|cliente}/register` | cria conta (senha em bcrypt) e gera código de verificação |
| `POST /auth/{doador\|cliente}/verify` | confirma o código → retorna tokens |
| `POST /auth/{doador\|cliente}/resend-code` | novo código (intervalo de 30 s) |
| `POST /auth/{doador\|cliente}/login` | e-mail + senha → `access_token` + `refresh_token` |
| `POST /auth/admin/login` | usuário/hash do `.env` (aceita o `$2y$` gerado pelo PHP) |
| `POST /auth/refresh` | troca um refresh token por um novo par |

Enviar nas demais rotas: `Authorization: Bearer <access_token>`.
Access token: 30 min · Refresh token: 7 dias (configuráveis). Claims: `sub`, `role`, `type`, `iss`, `iat`, `exp`, `jti`.

## Rotas
- **Público:** `GET /catalogo` (filtros `latitude`, `longitude`, `raio`, `limit`, `offset`), `GET /catalogo/{id}`
- **Doador:** `GET|PUT /doador/me`, `GET|POST /doador/roupas` (multipart, imagem opcional), `DELETE /doador/roupas/{id}`, `POST /doador/roupas/{id}/doado`, `GET /doador/interessados?item_id=`
- **Cliente:** `GET|PUT /cliente/me`, `GET /cliente/roupas/{id}` (com contato do doador), `POST /cliente/interesse/{id}`, `GET /cliente/historico`
- **Admin:** `GET /admin/stats`, `GET /admin/exportar` (CSV)

## Migração de senhas (PHP e API compartilham o mesmo formato)
O app PHP agora usa bcrypt (`app/Helpers/senha_helper.php`: `senha_hash`, `senha_confere`, `senha_precisa_rehash`),
o mesmo formato da API — uma conta criada ou logada em um lado funciona no outro.
Contas antigas com senha em texto puro continuam logando (PHP ou API) e são convertidas para bcrypt
no primeiro login bem-sucedido. Senhas bcrypt têm limite de 72 bytes.

## Limitações conhecidas
- Logout/revogação de tokens não é stateful (token vale até expirar). Para revogar, adicione uma blacklist de `jti` (Redis/tabela).
- Sem limite de tentativas de login — coloque atrás de um proxy/WAF ou adicione `slowapi`.
- `send_verification_code` (em `app/routers/auth.py`) só escreve no log; ligue ao SMTP/SMS.
