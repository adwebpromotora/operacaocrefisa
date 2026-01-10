from pydantic_settings import BaseSettings
from pathlib import Path

class Settings(BaseSettings):
    DATABASE_URL: str = "mysql+aiomysql://root:suasenha@localhost:3306/operacaocrefisa"
    SECRET_KEY: str = "sua-chave-secreta-muito-forte-aqui-1234567890abcdef"  # MUDE ISSO!
    ALGORITHM: str = "HS256"
    ACCESS_TOKEN_EXPIRE_MINUTES: int = 1440  # 24h

    class Config:
        env_file = Path(__file__).parent.parent.parent / ".env"
        env_file_encoding = "utf-8"

settings = Settings()
