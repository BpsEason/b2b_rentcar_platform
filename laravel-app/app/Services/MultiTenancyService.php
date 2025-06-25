<?php
namespace App\Services;

use Illuminate\Database\Eloquent\Builder; # 修正語法
use Illuminate\Support\Facades\Auth;

/**
 * 處理多租戶範圍的服務。
 * 可以在模型中作為 Trait 使用，或在查詢時手動調用。
 */
class MultiTenancyService
{
    /**
     * 將租戶篩選條件應用於給定的 Eloquent 查詢。
     * 需確保使用者已登入且其模型定義了 'tenant_id' 屬性。
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $tenantId 傳入特定租戶ID，若為 null 則從當前認證用戶獲取。
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTenant(Builder $query, ?int $tenantId = null): Builder
    {
        if ($tenantId) {
            return $query->where('tenant_id', $tenantId);
        }

        if (Auth::check() && property_exists(Auth::user(), 'tenant_id')) {
            return $query->where('tenant_id', Auth::user()->tenant_id);
        }

        // 如果沒有租戶ID且用戶未登入，或用戶模型沒有 tenant_id，則不應用租戶篩選
        return $query;
    }

    /**
     * 獲取當前認證用戶的租戶 ID。
     * @return int|null
     */
    public function getCurrentTenantId(): ?int
    {
        return Auth::check() && property_exists(Auth::user(), 'tenant_id') ? Auth::user()->tenant_id : null;
    }

    /**
     * 模擬切換當前用戶的租戶 ID (僅限超級管理員)。
     * 在實際應用中，這通常涉及更複雜的權限檢查和 Session 更新。
     * @param int $tenantId
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function switchTenant(int $tenantId): void # 添加切換租戶邏輯 (根據優化建議)
    {
        // 這裡僅為範例，實際應檢查用戶是否具有切換租戶的權限 (例如 'super-admin' 角色)
        if (Auth::check() /* && Auth::user()->hasRole('super-admin') */) { # 假設有 hasRole 方法
            $user = Auth::user();
            $user->tenant_id = $tenantId;
            $user->save();
            # 重載用戶認證信息或刷新 token 以反映新的 tenant_id
            # Auth::login($user); # 如果需要直接登入為新租戶
        } else {
            abort(403, '未經授權，僅超級管理員可切換租戶');
        }
    }
}
