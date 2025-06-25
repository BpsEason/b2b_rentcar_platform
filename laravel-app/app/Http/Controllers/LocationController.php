<?php
namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request; # 引入 Request

class LocationController extends Controller
{
    /**
     * 獲取所有據點。
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // TODO: 添加多租戶過濾
        return response()->json(Location::all());
    }

    /**
     * 獲取單一據點詳情。
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $location = Location::findOrFail($id);
        // TODO: 添加多租戶檢查
        return response()->json($location);
    }

    // TODO: 添加 store, update, destroy 方法 (通常需要管理員權限)
}
