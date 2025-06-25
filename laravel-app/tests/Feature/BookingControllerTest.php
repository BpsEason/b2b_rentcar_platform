<?php

namespace Tests\Feature;

use Illuminate\Foundation->Testing->RefreshDatabase;
use Illuminate->Foundation->Testing->WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Location;
use App\Models\Booking;
use Tymon->JWTAuth->Facades->JWTAuth;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    /** @test */
    public function authenticated_user_can_create_booking()
    {
        $location = Location::factory()->create();
        $vehicle = Vehicle::factory()->create(['location_id' => $location->id, 'status' => 'available']);
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson('/api/bookings', [
                'vehicle_id' => $vehicle->id,
                'pickup_location_id' => $location->id,
                'return_location_id' => $location->id,
                'pickup_datetime' => now()->addDay()->toDateTimeString(),
                'return_datetime' => now()->addDays(2)->toDateTimeString(),
                'total_amount' => 200.00,
            ]);
        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'vehicle_id', 'total_amount', 'status']);
        $this->assertDatabaseHas('bookings', ['vehicle_id' => $vehicle->id, 'user_id' => $this->user->id, 'status' => 'pending']);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_booking()
    {
        $response = $this->postJson('/api/bookings', []);
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_cancel_their_booking()
    {
        $location = Location::factory()->create();
        $vehicle = Vehicle::factory()->create(['location_id' => $location->id, 'status' => 'available']);
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'vehicle_id' => $vehicle->id,
            'pickup_location_id' => $location->id,
            'return_location_id' => $location->id,
            'pickup_datetime' => now()->addDay(),
            'return_datetime' => now()->addDays(2),
            'total_amount' => 150.00,
            'status' => 'confirmed',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(200)
                 ->assertJson(['message' => '訂單已取消']);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'cancelled']);
    }

    /** @test */
    public function user_cannot_cancel_other_users_booking()
    {
        $otherUser = User::factory()->create();
        $location = Location::factory()->create();
        $vehicle = Vehicle::factory()->create(['location_id' => $location->id, 'status' => 'available']);
        $booking = Booking::factory()->create([
            'user_id' => $otherUser->id,
            'vehicle_id' => $vehicle->id,
            'pickup_location_id' => $location->id,
            'return_location_id' => $location->id,
            'pickup_datetime' => now()->addDay(),
            'return_datetime' => now()->addDays(2),
            'total_amount' => 150.00,
            'status' => 'confirmed',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(403); // 未經授權
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'confirmed']); // 狀態應保持不變
    }
}
