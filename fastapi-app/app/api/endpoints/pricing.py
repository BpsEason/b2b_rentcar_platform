from fastapi import APIRouter, Depends, HTTPException, status
from pydantic import BaseModel
import datetime
from typing import Optional, Dict, List # 引入 List (根據優化建議)
from fastapi_limiter.depends import RateLimiter # 引入速率限制依賴

router = APIRouter()

class PricingRequest(BaseModel):
    vehicle_ids: Optional[List[int]] = None # 調整為 List (根據優化建議)
    vehicle_type: Optional[str] = None # 例如 'economy', 'suv'
    pickup_datetime: datetime.datetime
    return_datetime: datetime.datetime
    pickup_location_id: int # 或 pickup_location_latitude, pickup_location_longitude

class CalculatedPrice(BaseModel):
    vehicle_id: Optional[int] = None # 新增 vehicle_id (根據優化建議)
    base_price: float
    discount_amount: float
    final_price: float
    currency: str = "TWD"
    breakdown: Dict = {}

@router.post("/pricing/calculate", response_model=List[CalculatedPrice], dependencies=[Depends(RateLimiter(times=5, seconds=1))]) # 添加速率限制
async def calculate_dynamic_price(request: PricingRequest):
    """
    根據多個因素計算動態租賃價格。
    TODO: 這裡將實作動態定價邏輯，可能考慮:
    1. 基礎費率 (從 MySQL)
    2. 需求量 (從 Redis 或即時分析)
    3. 節假日、特殊事件 (從配置或外部日曆)
    4. 車輛庫存狀況
    5. ML 模型預測價格
    """
    # 確保還車時間晚於取車時間
    if request.pickup_datetime >= request.return_datetime:
        raise HTTPException(status_code=status.HTTP_400_BAD_REQUEST, detail="還車時間必須晚於取車時間")

    duration_hours = (request.return_datetime - request.pickup_datetime).total_seconds() / 3600
    
    results = [] # 用於存儲多個車輛的定價結果

    vehicle_ids_to_process = request.vehicle_ids if request.vehicle_ids else [None] # 如果沒有指定車輛ID，則處理通用類型

    for vehicle_id in vehicle_ids_to_process:
        # 範例基礎日費率
        daily_rate = 2500.0 
        if request.vehicle_type == "luxury":
            daily_rate = 5000.0
        elif vehicle_id: # 如果有指定車輛ID，則可以從資料庫獲取具體車輛的每日費率
            # 實際情況下，會根據 vehicle_id 從資料庫查詢其每日費率
            # 例如：從資料庫獲取 Vehicle.daily_rate
            pass 
        
        base_price = (duration_hours / 24) * daily_rate
        
        # 範例動態因子：高峰期加成、短租加成
        demand_multiplier = 1.0
        if duration_hours < 24: # 短租加成
            demand_multiplier *= 1.2
        
        # 假設在特定日期有加成 (例如春節)
        # if request.pickup_datetime.month == 2 and request.pickup_datetime.day in [10, 11, 12]:
        #     demand_multiplier *= 1.5

        final_price = base_price * demand_multiplier
        discount_amount = 0.0 # 暫無折扣，可從優惠券或會員等級計算

        results.append({
            "vehicle_id": vehicle_id, # 返回車輛ID
            "base_price": round(base_price, 2),
            "discount_amount": round(discount_amount, 2),
            "final_price": round(final_price, 2),
            "breakdown": {
                "duration_hours": round(duration_hours, 2),
                "daily_rate": daily_rate,
                "demand_multiplier": demand_multiplier,
                "pickup_location_id": request.pickup_location_id,
                "vehicle_type": request.vehicle_type
            }
        })
    return results

