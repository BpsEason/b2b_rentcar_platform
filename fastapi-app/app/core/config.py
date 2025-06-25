from pydantic_settings import BaseSettings, SettingsConfigDict
import os # 引入 os 模組

class Settings(BaseSettings):
    PROJECT_NAME: str = "${PROJECT_NAME}_FastAPI"
    PROJECT_VERSION: str = "0.1.0"
    APP_ENV: str = "development"

    DATABASE_URL: str
    REDIS_HOST: str
    REDIS_PORT: int

    CELERY_BROKER_URL: str
    CELERY_RESULT_BACKEND: str

    KAFKA_BROKERS: str

    JWT_SECRET_KEY: str
    ALGORITHM: str
    ACCESS_TOKEN_EXPIRE_MINUTES: int

    model_config = SettingsConfigDict(env_file='.env', extra='ignore')

settings = Settings()
