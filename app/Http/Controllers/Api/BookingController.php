<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Seat;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->bookingService->expirePendingPayments();

        $bookings = Booking::with(['user', 'train', 'seat'])->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Bookings retrieved successfully',
            'data' => $bookings
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'train_id' => 'required|integer',
            'seat_id' => 'required|integer',
            'booking_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        $result = $this->bookingService->createBooking($request->all());

        if (isset($result['error'])) {
            return response()->json([
                'status' => 'error',
                'message' => $result['error']
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Booking created successfully',
            'data' => $result['success']
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $booking = Booking::with(['user', 'train', 'seat'])->find($id);

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Booking retrieved successfully',
            'data' => $booking
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|required|integer',
            'train_id' => 'sometimes|required|integer',
            'seat_id' => 'sometimes|required|integer',
            'booking_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:pending,confirmed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        $booking->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Booking updated successfully',
            'data' => $booking
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking not found'
            ], 404);
        }

        // Release the seat
        $seat = Seat::find($booking->seat_id);
        if ($seat) {
            $seat->update(['is_reserved' => false]);
        }

        $booking->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Booking deleted successfully'
        ]);
    }

    /**
     * Confirm a booking (admin action)
     */
    public function confirm(string $id)
    {
        $booking = Booking::with(['user', 'train', 'seat'])->find($id);

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking not found'
            ], 404);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking is not pending'
            ], 400);
        }

        $result = $this->bookingService->confirmBooking($booking);

        if (isset($result['error'])) {
            return response()->json([
                'status' => 'error',
                'message' => $result['error']
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Booking confirmed successfully',
            'data' => $result['success']
        ]);
    }
}
