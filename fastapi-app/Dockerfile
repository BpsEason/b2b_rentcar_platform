FROM python:3.11-slim-bookworm

WORKDIR /app

# 僅複製 requirements.txt 以優化 Docker Layer Cache
COPY requirements.txt ./
# 安裝 Python 依賴
RUN pip install --no-cache-dir -r requirements.txt
# 生成 lock 檔案 (可在 build 後手動執行 docker compose exec fastapi-app pip freeze > requirements.lock)
# RUN pip freeze > requirements.lock

# 複製應用程式其餘代碼
COPY . .

# 暴露 FastAPI 端口
EXPOSE 8000

# 運行 FastAPI 應用程式
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8000"]
