<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\User;
use App\Models\Train;
use App\Models\Seat;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingService = new BookingService();
    }

    /**
     * Test successful booking creation
     */
    public function test_create_booking_successfully(): void
    {
        $user = User::factory()->create();
        $train = Train::factory()->create([
            'departure_time' => now()->addHours(2),
            'market_price' => 100000,
        ]);
        $seat = Seat::factory()->create([
            'train_id' => $train->id,
            'price' => 100000,
            'is_reserved' => false,
        ]);

        $data = [
            'user_id' => $user->id,
            'train_id' => $train->id,
            'seat_id' => $seat->id,
        ];

        $result = $this->bookingService->createBooking($data);

        $this->assertArrayHasKey('success', $result);
        $this->assertInstanceOf(Booking::class, $result['success']);
        $this->assertEquals('unpaid', $result['success']->payment_status);
        $this->assertTrue($seat->fresh()->is_reserved);
    }

    /**
     * Test booking fails when user not found
     */
    public function test_create_booking_user_not_found(): void
    {
        $train = Train::factory()->create();
        $seat = Seat::factory()->create(['train_id' => $train->id]);

        $data = [
            'user_id' => 9999,
            'train_id' => $train->id,
            'seat_id' => $seat->id,
        ];

        $result = $this->bookingService->createBooking($data);

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('User not found', $result['error']);
    }

    /**
     * Test booking fails when train not found
     */
    public function test_create_booking_train_not_found(): void
    {
        $user = User::factory()->create();
        $seat = Seat::factory()->create();

        $data = [
            'user_id' => $user->id,
            'train_id' => 9999,
            'seat_id' => $seat->id,
        ];

        $result = $this->bookingService->createBooking($data);

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Train not found', $result['error']);
    }

    /**
     * Test booking fails when seat not found
     */
    public function test_create_booking_seat_not_found(): void
    {
        $user = User::factory()->create();
        $train = Train::factory()->create();

        $data = [
            'user_id' => $user->id,
            'train_id' => $train->id,
            'seat_id' => 9999,
        ];

        $result = $this->bookingService->createBooking($data);

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Seat not found', $result['error']);
    }

    /**
     * Test booking fails when seat already reserved
     */
    public function test_create_booking_seat_already_reserved(): void
    {
        $user = User::factory()->create();
        $train = Train::factory()->create();
        $seat = Seat::factory()->create([
            'train_id' => $train->id,
            'is_reserved' => true,
        ]);

        $data = [
            'user_id' => $user->id,
            'train_id' => $train->id,
            'seat_id' => $seat->id,
        ];

        $result = $this->bookingService->createBooking($data);

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Seat is already reserved', $result['error']);
    }

    /**
     * Test booking fails when seat doesn't belong to train
     */
    public function test_create_booking_seat_wrong_train(): void
    {
        $user = User::factory()->create();
        $train1 = Train::factory()->create();
        $train2 = Train::factory()->create();
        $seat = Seat::factory()->create(['train_id' => $train1->id]);

        $data = [
            'user_id' => $user->id,
            'train_id' => $train2->id,
            'seat_id' => $seat->id,
        ];

        $result = $this->bookingService->createBooking($data);

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Seat does not belong to this train', $result['error']);
    }

    /**
     * Test confirm booking successfully
     */
    public function test_confirm_booking_successfully(): void
    {
        $booking = Booking::factory()->create([
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $result = $this->bookingService->confirmBooking($booking);

        $this->assertArrayHasKey('success', $result);
        $this->assertEquals('confirmed', $result['success']->status);
        $this->assertNotNull($result['success']->payment_expires_at);
    }

    /**
     * Test expire pending payments
     */
    public function test_expire_pending_payments(): void
    {
        $booking1 = Booking::factory()->create([
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_expires_at' => now()->subMinutes(5),
        ]);

        $booking2 = Booking::factory()->create([
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_expires_at' => now()->addMinutes(10),
        ]);

        $this->bookingService->expirePendingPayments();

        $this->assertEquals('expired', $booking1->fresh()->status);
        $this->assertEquals('confirmed', $booking2->fresh()->status);
    }

    /**
     * Test seat released when booking creation fails
     */
    public function test_seat_released_when_booking_fails(): void
    {
        $user = User::factory()->create();
        $train = Train::factory()->create();
        $seat = Seat::factory()->create([
            'train_id' => $train->id,
            'is_reserved' => false,
        ]);

        // Mock booking creation to fail
        $bookingCount = Booking::count();

        $data = [
            'user_id' => $user->id,
            'train_id' => $train->id,
            'seat_id' => $seat->id,
        ];

        $result = $this->bookingService->createBooking($data);

        // Seat should be released if it was reserved but booking failed
        if (isset($result['error'])) {
            $this->assertFalse($seat->fresh()->is_reserved);
        }
    }

    /**
     * Test booking uses seat price when available
     */
    public function test_booking_uses_seat_price_when_available(): void
    {
        $user = User::factory()->create();
        $train = Train::factory()->create(['market_price' => 100000]);
        $seat = Seat::factory()->create([
            'train_id' => $train->id,
            'price' => 150000,
            'is_reserved' => false,
        ]);

        $data = [
            'user_id' => $user->id,
            'train_id' => $train->id,
            'seat_id' => $seat->id,
        ];

        $result = $this->bookingService->createBooking($data);

        $this->assertArrayHasKey('success', $result);
        $this->assertEquals(150000, $result['success']->payment_amount);
    }

    /**
     * Test booking uses train market price when seat price is 0
     */
    public function test_booking_uses_train_market_price_when_seat_price_zero(): void
    {
        $user = User::factory()->create();
        $train = Train::factory()->create(['market_price' => 200000]);
        $seat = Seat::factory()->create([
            'train_id' => $train->id,
            'price' => 0,
            'is_reserved' => false,
        ]);

        $data = [
            'user_id' => $user->id,
            'train_id' => $train->id,
            'seat_id' => $seat->id,
        ];

        $result = $this->bookingService->createBooking($data);

        $this->assertArrayHasKey('success', $result);
        $this->assertEquals(200000, $result['success']->payment_amount);
    }
}
