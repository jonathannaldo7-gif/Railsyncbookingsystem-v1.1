<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use App\Models\Train;
use App\Models\Seat;

class BookingService
{
    /**
     * Validate and create booking with local model validation
     */
    public function createBooking(array $data)
    {
        // Validate user exists
        $user = User::find($data['user_id']);
        if (!$user) {
            return ['error' => 'User not found'];
        }

        // Validate train exists
        $train = Train::find($data['train_id']);
        if (!$train) {
            return ['error' => 'Train not found'];
        }

        // Validate seat exists and is available
        $seat = Seat::find($data['seat_id']);
        if (!$seat) {
            return ['error' => 'Seat not found'];
        }

        if ($seat->is_reserved) {
            return ['error' => 'Seat is already reserved'];
        }

        // Check if seat belongs to the train
        if ($seat->train_id !== $train->id) {
            return ['error' => 'Seat does not belong to this train'];
        }

        // Reserve seat
        try {
            $seat->update(['is_reserved' => true]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to reserve seat: ' . $e->getMessage());
            return ['error' => 'Failed to reserve seat'];
        }

        // Create booking
        try {
            $amount = $seat->price > 0 ? $seat->price : $train->marketPrice();

            $booking = Booking::create(array_merge($data, [
                'payment_status' => 'unpaid',
                'payment_amount' => $amount,
                'payment_method' => null,
                'payment_reference' => null,
                'paid_at' => null,
                'payment_expires_at' => null,
            ]));
            return ['success' => $booking];
        } catch (\Exception $e) {
            // If booking creation fails, release the seat
            $seat->update(['is_reserved' => false]);
            \Illuminate\Support\Facades\Log::error('Booking creation error: ' . $e->getMessage());
            return ['error' => 'Booking creation failed'];
        }
    }

    /**
     * Confirm a booking and send notification
     */
    public function confirmBooking(Booking $booking)
    {
        try {
            $booking->update([
                'status' => 'confirmed',
                'payment_expires_at' => now()->addMinutes(3),
            ]);

            // Dispatch confirmation email as a queued job
            \App\Jobs\SendBookingConfirmation::dispatch($booking);

            return ['success' => $booking];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to confirm booking: ' . $e->getMessage());
            return ['error' => 'Failed to confirm booking'];
        }
    }

    public function expirePendingPayments(): void
    {
        Booking::with('seat')
            ->where('status', 'confirmed')
            ->where('payment_status', 'unpaid')
            ->whereNotNull('payment_expires_at')
            ->where('payment_expires_at', '<', now())
            ->get()
            ->each(function (Booking $booking) {
                if ($booking->seat) {
                    $booking->seat->update(['is_reserved' => false]);
                }

                $booking->update(['status' => 'cancelled']);
            });
    }
}
