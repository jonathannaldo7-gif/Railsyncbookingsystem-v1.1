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
            $booking = Booking::create($data);
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
            $booking->update(['status' => 'confirmed']);

            // Send notification to user
            $this->sendConfirmationNotification($booking);

            return ['success' => $booking];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to confirm booking: ' . $e->getMessage());
            return ['error' => 'Failed to confirm booking'];
        }
    }

    /**
     * Send confirmation notification to user
     */
    private function sendConfirmationNotification(Booking $booking)
    {
        try {
            \Illuminate\Support\Facades\Mail::to($booking->user->email)->send(new \App\Mail\BookingConfirmed($booking));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send confirmation email: ' . $e->getMessage());
        }
    }
}
