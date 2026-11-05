from functools import lru_cache

from pydantic import Field
from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(env_file=".env", extra="ignore")

    database_url: str = "mysql+pymysql://root:@127.0.0.1:3306/sistema_doacao?charset=utf8mb4"

    jwt_secret: str = Field(min_length=32)
    jwt_algorithm: str = "HS256"
    jwt_issuer: str = "sistema-doacao-api"
    access_token_minutes: int = 30
    refresh_token_days: int = 7

    admin_username: str = ""
    admin_password_hash: str = ""

    upload_dir: str = "../public/uploads"
    max_upload_mb: int = 5

    expose_verification_code: bool = False
    cors_origins: list[str] = []


@lru_cache
def get_settings() -> Settings:
    return Settings()
