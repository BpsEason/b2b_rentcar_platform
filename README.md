# 租車平台 MVP

這是一個模組化、可擴展的租車平台 MVP，包含 Laravel 後端、Vue 前端和 FastAPI（AI/動態定價預留）。

## 專案結構
- `laravel-app/`: Laravel 後端，包含 API 路由、控制器和模型。
- `frontend-app/`: Vue 3 前端，使用 Vite 構建。
- `fastapi-app/`: FastAPI 後端，預留 AI 推薦和動態定價。
- `docker-compose.yml`: Docker 服務配置，包含 MySQL、Redis、RabbitMQ、Kafka、Prometheus、Grafana 等。
- `.editorconfig`: 統一程式碼風格配置。

## 部署步驟
1.  **進入專案目錄：**
    ```bash
    cd b2b_rentcar_platform
    ```
2.  **啟動 Docker 服務並建置映像：**
    ```bash
    docker compose up --build -d
    ```
    *此步驟將下載所有必要的 Docker 映像並安裝專案依賴，可能需要一些時間。*
3.  **進入 Laravel 容器執行安裝與設定：**
    ```bash
    docker compose exec laravel-app bash
    ```
    *在容器內部，您需要運行以下命令：*
    * 生成 Laravel 應用金鑰：`php artisan key:generate`
    * 生成 JWT 密鑰：`php artisan jwt:secret`
    * 執行資料庫遷移和填充數據 (如果需要)：`php artisan migrate --seed`
    * 退出容器：`exit`

4.  **進入前端容器執行安裝與設定：**
    ```bash
    docker compose exec frontend-app bash
    ```
    *在容器內部，您需要運行以下命令：*
    * 安裝 npm 依賴：`npm install`
    * 運行測試：`npm test`
    * 運行 Lint 檢查並修復：`npm run lint`
    * 完成後，輸入 `exit` 退出容器。

5.  **進入 FastAPI 容器執行安裝與設定 (如果啟用 FastAPI)：**
    ```bash
    docker compose exec fastapi-app bash
    ```
    *在容器內部，您需要運行以下命令：*
    * 安裝 pip 依賴 (如果 Dockerfile 中沒有自動安裝)：`pip install -r requirements.txt`
    * 運行測試：`pytest`
    * 退出容器：`exit`

6.  **訪問應用程式：**
    * **前端應用:** [http://localhost](http://localhost) (透過 Nginx 代理 Laravel API)
    * **Laravel API 服務:** [http://localhost:8000](http://localhost:8000) (直接訪問 Laravel API)
    * **FastAPI 服務:** [http://localhost:8001](http://localhost:8001) (如果已啟用)
    * **Grafana 監控介面:** [http://localhost:3000](http://localhost:3000) (預設帳密: `admin`/`admin`)
    * **Prometheus 監控介面:** [http://localhost:9090](http://localhost:9090)
    * **RabbitMQ 管理介面:** [http://localhost:15672](http://localhost:15672) (預設帳密: `guest`/`你的 .env 中的 RABBITMQ_PASSWORD_INPUT`)

## 下一步優化與開發
-   **前端表單驗證：** 建議在 Vue 應用中使用 'Vuelidate' 或 'VeeValidate' 等庫進行更嚴格的表單驗證。
-   **後端 API 實現：** 填充 Laravel 和 FastAPI 控制器中的業務邏輯，並添加請求驗證 (Request Validation)。
-   **任務佇列可靠性：** 為 Laravel 和 Celery 任務配置死信隊列 (DLQ) 和更完善的重試策略，確保訊息不丟失。
-   **應用層監控：** 在 Laravel 安裝 'prometheus-laravel'，在 FastAPI 安裝 'prometheus_fastapi_instrumentator'，以便從應用層暴露更多 metrics 給 Prometheus。
-   **日誌聚合與分析：** 導入 ELK Stack (Elasticsearch, Logstash, Kibana) 或 Grafana Loki 來集中管理和分析應用日誌。
-   **持續整合/部署 (CI/CD)：** 設定 GitHub Actions 或 GitLab CI/CD 來自動化測試、建置和部署流程。
-   **安全性強化：** 實施更強大的密碼策略、Two-Factor Authentication (2FA)、API 速率限制和 Web Application Firewall (WAF)。
-   **多租戶實現：** 根據實際需求，完善 Laravel 和 FastAPI 中的多租戶邏輯，例如使用獨立資料庫、Schema 或 Row-level Security (RLS)。
-   **性能測試：** 使用 JMeter 或 Locust 等工具進行負載和壓力測試。
-   **備份與恢復：** 為資料庫和其他重要數據配置定期備份和恢復策略。
-   **服務網格 (Service Mesh)：** 考慮使用 Istio 或 Linkerd 來管理微服務間的通訊、流量控制和策略執行。
-   **版本控制策略：** 實施 Git Flow 或 Trunk-based Development 等版本控制策略。
-   **文件：** 撰寫詳細的 API 文件 (Scribe/Swagger/OpenAPI)、部署指南和開發者文檔。
-   **程式碼品質：** 使用 SonarQube 或其他靜態程式碼分析工具來持續改進程式碼品質。
