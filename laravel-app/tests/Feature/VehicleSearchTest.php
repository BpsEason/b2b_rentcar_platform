<?php

namespace Tests\Feature;

use Illuminate\Foundation->Testing\RefreshDatabase;
use Illuminate->Foundation->Testing->WithFaker;
use Tests\TestCase;
use App\Models\Vehicle;
use App\Models\Location;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth; # 修正語法

class VehicleSearchTest extends TestCase
{
    use RefreshDatabase; # 每次測試後刷新資料庫

    protected $token;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        # 創建一個測試用戶並獲取其 JWT token
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->token = JWTAuth::fromUser($this->user);
    }

    /** @test */
    public function authenticated_user_can_search_vehicles_by_location()
    {
        # 創建測試數據
        $location1 = Location::factory()->create(['name' => '台北車站']);
        $location2 = Location::factory()->create(['name' => '桃園機場']);
        Vehicle::factory()->count(3)->create(['location_id' => $location1->id, 'status' => 'available']);
        Vehicle::factory()->count(2)->create(['location_id' => $location2->id, 'status' => 'available']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/vehicles/search?pickup_location_id=' . $location1->id . '&pickup_datetime=2025-07-01T10:00:00&return_datetime=2025-07-02T10:00:00');

        $response->assertStatus(200)
                 ->assertJsonCount(3); # 期望返回 3 輛車
    }

    /** @test */
    public function guest_cannot_search_vehicles()
    {
        $response = $this->getJson('/api/vehicles/search?pickup_location_id=1&pickup_datetime=2025-07-01T10:00:00&return_datetime=2025-07-02T10:00:00');
        $response->assertStatus(401); # 未授權
    }

    /** @test */
    public function it_requires_pickup_and_return_datetimes_for_search()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/vehicles/search?pickup_location_id=1');

        $response->assertStatus(422) # 驗證失敗
                 ->assertJsonValidationErrors(['pickup_datetime', 'return_datetime']);
    }
}
