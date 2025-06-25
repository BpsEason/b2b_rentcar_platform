from celery import Celery
import time
import logging
import os # 引入 os 模組

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Celery 應用程式實例
# TODO: 將 broker 和 backend 配置從程式碼移至環境變數或專門的配置文件
CELERY_BROKER_URL = os.getenv('CELERY_BROKER_URL', 'pyamqp://guest:guest@rabbitmq:5672//')
CELERY_RESULT_BACKEND = os.getenv('CELERY_RESULT_BACKEND', 'redis://redis:6379/0')

celery_app = Celery('tasks',
                    broker=CELERY_BROKER_URL,
                    backend=CELERY_RESULT_BACKEND) # Celery 經紀人與結果後端

# 配置任務重試策略 (可在任務裝飾器中指定)
# Celery 預設有重試機制，但建議顯式配置 max_retries 和 retry_backoff
# @celery_app.task(bind=True, max_retries=3, default_retry_delay=300) # 3 次重試，每次間隔 5 分鐘
@celery_app.task(bind=True, max_retries=3, retry_backoff=True, retry_backoff_max=600) # 指數退避，最大 10 分鐘
def process_return_task(self, booking_id: int, vehicle_condition: dict):
    """
    模擬還車任務處理 (車況回報、通知)。
    TODO: 實作更完善的錯誤處理和死信隊列 (DLQ) 配置。
    """
    try:
        logger.info(f"處理還車任務: 預訂 ID {booking_id}, 車況: {vehicle_condition}")
        time.sleep(5) # 模擬耗時操作
        # 這裡將更新資料庫、發送通知等
        logger.info(f"還車任務 {booking_id} 處理完成。")
        return {"status": "success", "booking_id": booking_id, "processed_at": time.time()}
    except Exception as e:
        logger.error(f"還車任務 {booking_id} 失敗: {e}")
        self.retry(exc=e) # 任務失敗時重試

@celery_app.task
def send_maintenance_notification(vehicle_id: int, next_maintenance_date: str):
    """
    模擬發送保養通知。
    """
    logger.info(f"發送車輛 {vehicle_id} 的保養通知，預計保養日期: {next_maintenance_date}")
    time.sleep(2) # 模擬通知發送
    logger.info(f"保養通知已發送給車輛 {vehicle_id}。")
    return {"status": "sent", "vehicle_id": vehicle_id}

@celery_app.task
def recommend_vehicles_async(user_id: int):
    """
    模擬非同步的車輛推薦任務。
    TODO: 在此處載入 ML 模型並生成推薦，將結果存入資料庫或快取。
    """
    logger.info(f"為用戶 {user_id} 生成非同步車輛推薦...")
    time.sleep(10) # 模擬 ML 模型推理時間
    # 這裡可以載入 ML 模型並生成推薦
    recommendations = [
        {"vehicle_id": 201, "score": 0.9, "reason": "高匹配度"},
        {"vehicle_id": 202, "score": 0.85, "reason": "同類熱門"},
    ]
    # 將推薦結果存儲到資料庫或 Redis，以便後續查詢
    # 例如: redis_client.set(f"user:{user_id}:recommendations", json.dumps(recommendations))
    logger.info(f"用戶 {user_id} 的推薦已生成: {recommendations}")
    return recommendations
