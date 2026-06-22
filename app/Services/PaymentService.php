<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    public function processBookingPayment(Booking $booking, array $data)
    {
        if ($booking->status !== 'confirmed') {
            return ['error' => 'Booking must be confirmed before payment'];
        }

        if ($booking->payment_status === 'paid') {
            return ['error' => 'Booking has already been paid'];
        }

        $paymentMethod = $data['payment_method'] ?? 'card';
        $validMethods = ['card', 'bank_transfer', 'cash'];
        if (!in_array($paymentMethod, $validMethods, true)) {
            return ['error' => 'Unsupported payment method'];
        }

        $amount = $booking->payment_amount ?? 0;
        if ($amount <= 0) {
            $booking->update([
                'payment_status' => 'paid',
                'payment_method' => $paymentMethod,
                'payment_reference' => 'FREE-' . Str::upper(Str::random(8)),
                'paid_at' => now(),
                'payment_expires_at' => null,
            ]);

            return ['success' => $booking];
        }

        try {
            $reference = 'PAY-' . Str::upper(Str::random(10));
            // In a real app, integrate with an external gateway here.
            $booking->update([
                'payment_status' => 'paid',
                'payment_method' => $paymentMethod,
                'payment_reference' => $reference,
                'paid_at' => now(),
                'payment_expires_at' => null,
            ]);

            return ['success' => $booking];
        } catch (\Exception $e) {
            Log::error('Payment failed: ' . $e->getMessage());
            return ['error' => 'Payment processing failed'];
        }
    }
}
