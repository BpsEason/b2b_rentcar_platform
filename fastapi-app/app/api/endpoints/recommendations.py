from fastapi import APIRouter, Depends
from typing import List, Dict
from pydantic import BaseModel
from ..tasks.example_tasks import recommend_vehicles_async # 引入 Celery 任務 

router = APIRouter()

class RecommendedVehicle(BaseModel):
    vehicle_id: int
    make: str
    model: str
    score: float
    reason: str

@router.get("/recommendations/{user_id}", response_model=List[RecommendedVehicle])
async def get_vehicle_recommendations(user_id: int):
    """
    獲取用戶的車輛推薦。
    TODO: 這裡將觸發 Celery 任務來異步生成推薦，並返回初步結果或查詢最終推薦結果。
    """
    # 觸發非同步推薦任務
    task_result = recommend_vehicles_async.delay(user_id) # .delay() 是 Celery 異步調用
    
    # 範例假數據：可以返回一個任務 ID，讓前端查詢結果
    # 或者直接返回一個預設的推薦列表
    return [
        {"vehicle_id": 103, "make": "Tesla", "model": "Model 3", "score": 0.95, "reason": "基於您的租賃歷史"},
        {"vehicle_id": 104, "make": "Nissan", "model": "Kicks", "score": 0.88, "reason": "您所在地區的熱門車款"},
    ]
