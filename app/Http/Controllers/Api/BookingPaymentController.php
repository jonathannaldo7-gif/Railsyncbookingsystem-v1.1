<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingPaymentController extends Controller
{
    protected PaymentService $paymentService;
    protected BookingService $bookingService;

    public function __construct(PaymentService $paymentService, BookingService $bookingService)
    {
        $this->paymentService = $paymentService;
        $this->bookingService = $bookingService;
    }

    public function pay(Request $request, string $id)
    {
        $this->bookingService->expirePendingPayments();

        $booking = Booking::with(['user', 'train', 'seat'])->find($id);

        if (!$booking) {
            return response()->json(['status' => 'error', 'message' => 'Booking not found'], 404);
        }

        if ($booking->status !== 'confirmed') {
            return response()->json(['status' => 'error', 'message' => 'Booking is not confirmed'], 400);
        }

        if ($booking->payment_status === 'paid') {
            return response()->json(['status' => 'error', 'message' => 'Booking already paid'], 400);
        }

        if ($booking->payment_expires_at && now()->greaterThan($booking->payment_expires_at)) {
            if ($booking->seat) {
                $booking->seat->update(['is_reserved' => false]);
            }
            $booking->update(['status' => 'cancelled']);

            return response()->json(['status' => 'error', 'message' => 'Payment time expired, booking has been cancelled'], 400);
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|in:card,bank_transfer,cash',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'data' => $validator->errors()], 422);
        }

        $result = $this->paymentService->processBookingPayment($booking, $request->all());

        if (isset($result['error'])) {
            return response()->json(['status' => 'error', 'message' => $result['error']], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Payment processed successfully',
            'data' => $result['success'],
        ]);
    }
}
