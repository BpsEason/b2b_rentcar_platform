from fastapi.testclient import TestClient
from app.main import app
from datetime import datetime, timedelta

client = TestClient(app)

def test_calculate_pricing_for_single_vehicle():
    """測試單一車輛的動態定價計算。"""
    now = datetime.now()
    future_date = now + timedelta(days=1)
    response = client.post("/api/v1/pricing/calculate", json={
        "vehicle_ids": [1],
        "pickup_datetime": now.isoformat(),
        "return_datetime": future_date.isoformat(),
        "pickup_location_id": 1
    })
    assert response.status_code == 200
    assert isinstance(response.json(), list)
    assert len(response.json()) == 1
    assert "final_price" in response.json()[0]
    assert response.json()[0]["vehicle_id"] == 1

def test_calculate_pricing_for_multiple_vehicles():
    """測試多個車輛的動態定價計算。"""
    now = datetime.now()
    future_date = now + timedelta(days=2)
    response = client.post("/api/v1/pricing/calculate", json={
        "vehicle_ids": [1, 2, 3],
        "pickup_datetime": now.isoformat(),
        "return_datetime": future_date.isoformat(),
        "pickup_location_id": 1
    })
    assert response.status_code == 200
    assert isinstance(response.json(), list)
    assert len(response.json()) == 3

def test_pricing_requires_valid_datetimes():
    """測試定價 API 對時間參數的驗證。"""
    now = datetime.now()
    response = client.post("/api/v1/pricing/calculate", json={
        "vehicle_ids": [1],
        "pickup_datetime": now.isoformat(),
        "return_datetime": now.isoformat(), # 還車時間不晚於取車時間
        "pickup_location_id": 1
    })
    assert response.status_code == 400
    assert "detail" in response.json()
    assert "還車時間必須晚於取車時間" in response.json()["detail"]

def test_pricing_with_vehicle_type():
    """測試根據車輛類型進行定價。"""
    now = datetime.now()
    future_date = now + timedelta(days=1)
    response = client.post("/api/v1/pricing/calculate", json={
        "vehicle_type": "luxury",
        "pickup_datetime": now.isoformat(),
        "return_datetime": future_date.isoformat(),
        "pickup_location_id": 1
    })
    assert response.status_code == 200
    assert response.json()[0]["final_price"] > 2500 # 豪華車應有更高價格
