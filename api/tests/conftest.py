import os
import tempfile

import bcrypt

os.environ["JWT_SECRET"] = "x" * 48
os.environ["ADMIN_USERNAME"] = "admin"
os.environ["ADMIN_PASSWORD_HASH"] = bcrypt.hashpw(b"admin-pass-123", bcrypt.gensalt(prefix=b"2b")).decode()
os.environ["UPLOAD_DIR"] = tempfile.mkdtemp()
os.environ["EXPOSE_VERIFICATION_CODE"] = "true"
os.environ["DATABASE_URL"] = "sqlite://"

import pytest
from fastapi.testclient import TestClient
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
from sqlalchemy.pool import StaticPool

from app.database import get_db
from app.main import app
from app.models import Base


@pytest.fixture()
def client():
    engine = create_engine("sqlite://", connect_args={"check_same_thread": False}, poolclass=StaticPool)
    Base.metadata.create_all(engine)
    Session = sessionmaker(bind=engine, autoflush=False, expire_on_commit=False)

    def override():
        db = Session()
        try:
            yield db
        finally:
            db.close()

    app.dependency_overrides[get_db] = override
    app.state.Session = Session
    yield TestClient(app)
    app.dependency_overrides.clear()
