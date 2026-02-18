<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_it_reserves_seats_for_15_minutes(): void
    {
        Carbon::setTestNow('2026-02-13 10:00:00');

        $travel = $this->createTravel();

        $response = $this->postJson('/api/carts', [
            'travel_id' => $travel->id,
            'email' => 'user@example.com',
            'seats' => 3,
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', Cart::STATUS_PENDING)
            ->assertJsonPath('seats', 3)
            ->assertJsonPath('expires_in_seconds', 900);
    }

    public function test_it_prevents_overbooking(): void
    {
        $travel = $this->createTravel();

        Cart::query()->create([
            'travel_id' => $travel->id,
            'email' => 'a@example.com',
            'seats' => 4,
            'status' => Cart::STATUS_PENDING,
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/carts', [
            'travel_id' => $travel->id,
            'email' => 'b@example.com',
            'seats' => 2,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('seats_left', 1);
    }

    public function test_it_expires_and_rejects_payment_for_old_cart(): void
    {
        $travel = $this->createTravel();

        $cart = Cart::query()->create([
            'travel_id' => $travel->id,
            'email' => 'user@example.com',
            'seats' => 1,
            'status' => Cart::STATUS_PENDING,
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->postJson("/api/carts/{$cart->id}/pay");

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Cart expired');

        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'status' => Cart::STATUS_EXPIRED,
        ]);
    }

    public function test_it_confirms_payment_for_valid_cart(): void
    {
        $travel = $this->createTravel();

        $cart = Cart::query()->create([
            'travel_id' => $travel->id,
            'email' => 'user@example.com',
            'seats' => 1,
            'status' => Cart::STATUS_PENDING,
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson("/api/carts/{$cart->id}/pay");

        $response->assertOk()
            ->assertJsonPath('message', 'Payment confirmed')
            ->assertJsonPath('cart.status', Cart::STATUS_PAID);
    }

    private function createTravel(): Travel
    {
        return Travel::query()->create([
            'id' => '11111111-1111-1111-1111-111111111111',
            'slug' => 'jordan-360',
            'name' => 'Jordan 360°',
            'description' => 'Test travel',
            'starting_date' => '2026-03-01',
            'ending_date' => '2026-03-09',
            'price' => 199900,
            'moods' => ['nature' => 80],
        ]);
    }
}
