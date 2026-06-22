<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function pay(Request $request, string $id)
    {
        $booking = Booking::with(['user', 'train', 'seat'])->find($id);

        if (!$booking) {
            return response()->json(['status' => 'error', 'message' => 'Booking not found'], 404);
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

        return response()->json(['status' => 'success', 'message' => 'Payment successful', 'data' => $result['success']]);
    }
}
