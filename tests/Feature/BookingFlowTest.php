<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\Train;
use App\Models\Seat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test complete booking workflow
     */
    public function test_complete_booking_workflow(): void
    {
        // Create test data
        $user = User::factory()->create();
        $train = Train::factory()->create([
            'departure_time' => now()->addHours(2),
            'market_price' => 150000,
        ]);
        $seat = Seat::factory()->create([
            'train_id' => $train->id,
            'price' => 150000,
            'is_reserved' => false,
        ]);

        // Step 1: Create booking
        $bookingData = [
            'user_id' => $user->id,
            'train_id' => $train->id,
            'seat_id' => $seat->id,
        ];

        $response = $this->postJson('/api/bookings', $bookingData);
        $response->assertStatus(201);
        $booking = Booking::first();
        $this->assertEquals('pending', $booking->status);
        $this->assertEquals('unpaid', $booking->payment_status);

        // Step 2: Confirm booking
        $response = $this->patchJson("/api/bookings/{$booking->id}/confirm");
        $response->assertStatus(200);
        $booking->refresh();
        $this->assertEquals('confirmed', $booking->status);

        // Step 3: Process payment
        $response = $this->postJson("/api/bookings/{$booking->id}/pay", [
            'payment_method' => 'card',
        ]);
        $response->assertStatus(200);
        $booking->refresh();
        $this->assertEquals('paid', $booking->payment_status);
    }

    /**
     * Test booking cancellation
     */
    public function test_booking_cancellation(): void
    {
        $user = User::factory()->create();
        $train = Train::factory()->create();
        $seat = Seat::factory()->create([
            'train_id' => $train->id,
            'is_reserved' => true,
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'train_id' => $train->id,
            'seat_id' => $seat->id,
            'status' => 'pending',
        ]);

        $response = $this->deleteJson("/api/bookings/{$booking->id}");
        $response->assertStatus(200);
        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
        $this->assertFalse($seat->fresh()->is_reserved);
    }

    /**
     * Test multiple bookings same user different seats
     */
    public function test_multiple_bookings_same_user(): void
    {
        $user = User::factory()->create();
        $train = Train::factory()->create();
        $seats = Seat::factory()->count(3)->create(['train_id' => $train->id]);

        foreach ($seats as $seat) {
            $response = $this->postJson('/api/bookings', [
                'user_id' => $user->id,
                'train_id' => $train->id,
                'seat_id' => $seat->id,
            ]);
            $response->assertStatus(201);
        }

        $this->assertEquals(3, $user->bookings()->count());
    }

    /**
     * Test booking status transitions
     */
    public function test_booking_status_transitions(): void
    {
        $booking = Booking::factory()->create(['status' => 'pending']);

        // pending -> confirmed
        $this->patchJson("/api/bookings/{$booking->id}/confirm")->assertStatus(200);
        $this->assertEquals('confirmed', $booking->fresh()->status);

        // confirmed -> paid
        $booking->update(['payment_status' => 'unpaid']);
        $this->postJson("/api/bookings/{$booking->id}/pay", [
            'payment_method' => 'card',
        ])->assertStatus(200);
        $this->assertEquals('paid', $booking->fresh()->payment_status);
    }
}
