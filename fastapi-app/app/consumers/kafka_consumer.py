import asyncio
import logging
from confluent_kafka import Consumer, KafkaException
import json
import os # 引入 os 模組

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Kafka 配置 (從環境變數或 config 載入)
KAFKA_BROKERS = os.getenv("KAFKA_BROKERS", "kafka:9092")
KAFKA_GROUP_ID = os.getenv("KAFKA_GROUP_ID", "fastapi_consumer_group")
KAFKA_TOPICS = os.getenv("KAFKA_TOPICS", "booking_events,payment_events").split(',') # 監聽多個主題

async def consume_kafka_messages():
    """
    啟動 Kafka 消費者，監聽指定主題的訊息。
    """
    consumer_conf = {
        'bootstrap.servers': KAFKA_BROKERS,
        'group.id': KAFKA_GROUP_ID,
        'auto.offset.reset': 'earliest',
        'enable.auto.commit': True,
        'auto.commit.interval.ms': 5000 # 每 5 秒自動提交一次偏移量
    }
    
    consumer = Consumer(consumer_conf)

    try:
        consumer.subscribe(KAFKA_TOPICS)
        logger.info(f"Kafka 消費者已訂閱主題: {KAFKA_TOPICS}")

        while True:
            msg = consumer.poll(timeout=1.0) # 每秒輪詢一次訊息
            if msg is None:
                continue
            if msg.error():
                if msg.error().code() == KafkaException._PARTITION_EOF:
                    # 分區結束事件 - 不是錯誤
                    logger.debug(f"Reached end of partition {msg.topic()} [{msg.partition()}] at offset {msg.offset()}")
                    continue
                elif msg.error():
                    logger.error(f"Kafka 消費者錯誤: {msg.error()}")
                    break
            else:
                # 訊息成功接收
                key = msg.key().decode('utf-8') if msg.key() else None
                value = msg.value().decode('utf-8')
                headers = {k.decode('utf-8'): v.decode('utf-8') for k, v in msg.headers()} if msg.headers() else {}

                logger.info(f"收到來自主題 '{msg.topic()}' 的訊息 - Key: {key}, Headers: {headers}, Value: {value}")
                
                # 根據訊息類型執行相應的邏輯
                if msg.topic() == "booking_events" and headers.get('type') == 'booking_created':
                    await handle_booking_created_event(json.loads(value))
                elif msg.topic() == "payment_events" and headers.get('type') == 'payment_successful':
                    await handle_payment_successful_event(json.loads(value))
                
                # 手動提交偏移量 (如果 enable.auto.commit 為 False)
                # consumer.commit(message=msg)

    except KeyboardInterrupt:
        logger.info("Kafka 消費者終止。")
    except Exception as e:
        logger.error(f"Kafka 消費者發生未預期錯誤: {e}", exc_info=True)
    finally:
        logger.info("關閉 Kafka 消費者...")
        consumer.close()

async def handle_booking_created_event(event_data: dict):
    """
    處理訂單創建事件的範例邏輯。
    例如：更新車輛狀態、觸發後續的合約生成任務。
    """
    logger.info(f"處理訂單創建事件: {event_data['booking_id']}")
    # 這裡可以呼叫服務層來更新資料庫，或者發送給另一個 Celery 任務
    await asyncio.sleep(1) # 模擬異步處理
    logger.info(f"已處理訂單 {event_data['booking_id']} 的創建事件。")
    # 可以觸發 Celery 任務來異步處理更複雜的邏輯，例如：
    # from app.tasks.example_tasks import process_booking_notification
    # process_booking_notification.delay(event_data['booking_id'])

async def handle_payment_successful_event(event_data: dict):
    """
    處理付款成功事件的範例邏輯。
    例如：更新訂單付款狀態、確認訂單。
    """
    logger.info(f"處理付款成功事件: 訂單 ID {event_data['booking_id']}, 金額: {event_data['amount']}")
    await asyncio.sleep(1) # 模擬異步處理
    logger.info(f"已處理訂單 {event_data['booking_id']} 的付款成功事件。")

# 假設在 FastAPI 應用啟動時可以作為背景任務運行
# from fastapi import FastAPI
# @app.on_event("startup")
# async def startup_event():
#     asyncio.create_task(consume_kafka_messages())
