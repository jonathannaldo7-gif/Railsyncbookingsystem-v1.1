<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = new PaymentService();
    }

    /**
     * Test process payment with card method
     */
    public function test_process_payment_with_card_method(): void
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_amount' => 100000,
        ]);

        $result = $this->paymentService->processBookingPayment($booking, [
            'payment_method' => 'card',
        ]);

        $this->assertArrayHasKey('success', $result);
        $this->assertEquals('paid', $result['success']->payment_status);
        $this->assertEquals('card', $result['success']->payment_method);
        $this->assertNotNull($result['success']->payment_reference);
    }

    /**
     * Test process payment with bank transfer method
     */
    public function test_process_payment_with_bank_transfer_method(): void
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_amount' => 50000,
        ]);

        $result = $this->paymentService->processBookingPayment($booking, [
            'payment_method' => 'bank_transfer',
        ]);

        $this->assertArrayHasKey('success', $result);
        $this->assertEquals('paid', $result['success']->payment_status);
        $this->assertEquals('bank_transfer', $result['success']->payment_method);
    }

    /**
     * Test process payment with cash method
     */
    public function test_process_payment_with_cash_method(): void
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_amount' => 75000,
        ]);

        $result = $this->paymentService->processBookingPayment($booking, [
            'payment_method' => 'cash',
        ]);

        $this->assertArrayHasKey('success', $result);
        $this->assertEquals('paid', $result['success']->payment_status);
        $this->assertEquals('cash', $result['success']->payment_method);
    }

    /**
     * Test payment fails with unconfirmed booking
     */
    public function test_payment_fails_with_unconfirmed_booking(): void
    {
        $booking = Booking::factory()->create([
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $result = $this->paymentService->processBookingPayment($booking, [
            'payment_method' => 'card',
        ]);

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Booking must be confirmed before payment', $result['error']);
    }

    /**
     * Test payment fails with already paid booking
     */
    public function test_payment_fails_with_already_paid_booking(): void
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $result = $this->paymentService->processBookingPayment($booking, [
            'payment_method' => 'card',
        ]);

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Booking has already been paid', $result['error']);
    }

    /**
     * Test payment fails with unsupported payment method
     */
    public function test_payment_fails_with_unsupported_method(): void
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
        ]);

        $result = $this->paymentService->processBookingPayment($booking, [
            'payment_method' => 'crypto',
        ]);

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Unsupported payment method', $result['error']);
    }

    /**
     * Test free booking payment (amount = 0)
     */
    public function test_free_booking_payment(): void
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_amount' => 0,
        ]);

        $result = $this->paymentService->processBookingPayment($booking, [
            'payment_method' => 'card',
        ]);

        $this->assertArrayHasKey('success', $result);
        $this->assertEquals('paid', $result['success']->payment_status);
        $this->assertStringStartsWith('FREE-', $result['success']->payment_reference);
    }

    /**
     * Test default payment method is card
     */
    public function test_default_payment_method_is_card(): void
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_amount' => 100000,
        ]);

        $result = $this->paymentService->processBookingPayment($booking, []);

        $this->assertArrayHasKey('success', $result);
        $this->assertEquals('card', $result['success']->payment_method);
    }

    /**
     * Test payment reference is generated correctly
     */
    public function test_payment_reference_generated_correctly(): void
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_amount' => 100000,
        ]);

        $result = $this->paymentService->processBookingPayment($booking, [
            'payment_method' => 'card',
        ]);

        $this->assertArrayHasKey('success', $result);
        $this->assertStringStartsWith('PAY-', $result['success']->payment_reference);
        $this->assertEquals(14, strlen($result['success']->payment_reference)); // PAY- (4) + 10 random chars
    }

    /**
     * Test paid_at timestamp is set
     */
    public function test_paid_at_timestamp_is_set(): void
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_amount' => 100000,
            'paid_at' => null,
        ]);

        $result = $this->paymentService->processBookingPayment($booking, [
            'payment_method' => 'card',
        ]);

        $this->assertArrayHasKey('success', $result);
        $this->assertNotNull($result['success']->paid_at);
    }

    /**
     * Test payment expires at is cleared
     */
    public function test_payment_expires_at_is_cleared(): void
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_amount' => 100000,
            'payment_expires_at' => now()->addMinutes(10),
        ]);

        $result = $this->paymentService->processBookingPayment($booking, [
            'payment_method' => 'card',
        ]);

        $this->assertArrayHasKey('success', $result);
        $this->assertNull($result['success']->payment_expires_at);
    }
}
