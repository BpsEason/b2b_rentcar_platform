from fastapi import FastAPI, Depends, HTTPException, status
from fastapi.security import OAuth2PasswordBearer
from jose import JWTError, jwt
from pydantic import BaseModel
from typing import Annotated, Optional
import os # 引入 os 用於讀取環境變數
from fastapi.middleware.cors import CORSMiddleware # 引入 CORS 中間件 (根據優化建議)
from fastapi_limiter import FastAPILimiter # 引入速率限制庫 (根據優化建議)
from redis.asyncio import Redis # 引入 Redis 客戶端


from .core.config import settings
from .api.endpoints import vehicles, recommendations, pricing # 假設為車輛查詢、推薦、動態定價 API
from .schemas.token import TokenData # JWT Token 數據模型範例

app = FastAPI(
    title=settings.PROJECT_NAME,
    version=settings.PROJECT_VERSION,
    description="租車平台 FastAPI 服務 - 高效能 API 與 AI/ML 服務",
    docs_url="/api/docs",
    redoc_url="/api/redoc",
    openapi_url="/api/openapi.json"
)

# 配置 CORS (根據優化建議)
origins = os.getenv("CORS_ALLOWED_ORIGINS", "http://localhost").split(",")
app.add_middleware(
    CORSMiddleware,
    allow_origins=origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# 配置速率限制 (根據優化建議)
@app.on_event("startup")
async def startup_event():
    # 這裡的 Redis 服務名稱 'redis' 應該與 docker-compose.yml 中的服務名稱一致
    redis_instance = Redis(host=settings.REDIS_HOST, port=settings.REDIS_PORT, decode_responses=True)
    await FastAPILimiter.init(redis_instance)

# OAuth2PasswordBearer 用於 JWT 驗證
oauth2_scheme = OAuth2PasswordBearer(tokenUrl="token") # 指向你的 JWT 登入端點

# 基本 JWT 驗證 (範例 - 實際應包含用戶資料庫查詢)
async def get_current_user(token: Annotated[str, Depends(oauth2_scheme)]):
    credentials_exception = HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="無法驗證憑證",
        headers={"WWW-Authenticate": "Bearer"},
    )
    try:
        # JWT 密鑰強度檢查 (根據優化建議)
        if not settings.JWT_SECRET_KEY or len(settings.JWT_SECRET_KEY) < 32:
            raise HTTPException(status_code=500, detail="JWT_SECRET_KEY 未配置或強度不足")
        
        payload = jwt.decode(token, settings.JWT_SECRET_KEY, algorithms=[settings.ALGORITHM])
        username: str = payload.get("sub")
        if username is None:
            raise credentials_exception
        token_data = TokenData(username=username) # 用你的實際用戶獲取邏輯替換
    except JWTError:
        raise credentials_exception
    return {"username": token_data.username} # 僅返回用戶名以作簡單範例

# 包含路由器
app.include_router(vehicles.router, prefix="/api/v1", tags=["vehicles"])
app.include_router(recommendations.router, prefix="/api/v1", tags=["recommendations"])
app.include_router(pricing.router, prefix="/api/v1", tags=["pricing"])

@app.get("/api/v1/health")
async def health_check():
    """
    健康檢查端點。
    TODO: 應該包含數據庫連線、Redis 連線、Celery 狀態等更詳細的檢查。
    """
    return {"status": "ok", "message": "FastAPI 服務運行正常"}

@app.get("/api/v1/secure_test")
async def secure_test(current_user: Annotated[dict, Depends(get_current_user)]):
    return {"message": "訪問已授權", "user": current_user}

