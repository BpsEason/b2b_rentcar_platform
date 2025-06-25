<?php
return [
    'secret' => env('JWT_SECRET', ''),
    'ttl' => env('JWT_TTL', 60), # Token 有效時間 (分鐘)
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160), # Refresh Token 有效時間 (分鐘)
    'algo' => env('JWT_ALGO', 'HS256'),
    'user' => 'App\Models\User',
    'identifier' => 'id',
    'required_claims' => ['iss', 'iat', 'exp', 'nbf', 'sub', 'jti'],
    'blacklist_enabled' => true, # 啟用 JWT 黑名單 (登出時使用)
    'providers' => [
        'user' => 'Tymon\JWTAuth\Providers\User\EloquentUserAdapter',
        'jwt' => 'Tymon\JWTAuth\Providers\JWT\Lcobucci',
        'auth' => 'Tymon\JWTAuth\Providers\Auth\Illuminate',
        'storage' => 'Tymon\JWTAuth\Providers\Storage\Illuminate',
    ],
];
# 注意：在生產環境中，請定期輪換 JWT_SECRET，並使用安全的密鑰管理工具（如 AWS Secrets Manager）。
# 確保 JWT_SECRET 強度足夠，至少 32 個字元，包含大小寫字母、數字和特殊字元。
