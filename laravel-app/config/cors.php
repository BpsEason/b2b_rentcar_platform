<?php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    # 在生產環境中，請將 CORS_ALLOWED_ORIGINS 設置為您的前端域名，例如 'https://your-frontend-domain.com'
    # 多個域名請以逗號分隔，例如 'http://localhost,https://your-frontend-domain.com'
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost')),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
