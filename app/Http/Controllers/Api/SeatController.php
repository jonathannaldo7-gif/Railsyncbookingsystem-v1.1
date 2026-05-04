<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SeatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Seat::with('train');

        if ($request->has('train_id')) {
            $query->where('train_id', $request->query('train_id'));
        }

        $seats = $query->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Seats retrieved successfully',
            'data' => $seats
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'train_id' => 'required|exists:trains,id',
            'seat_number' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        $seat = Seat::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Seat created successfully',
            'data' => $seat
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $seat = Seat::with('train')->find($id);

        if (!$seat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seat not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Seat retrieved successfully',
            'data' => $seat
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $seat = Seat::find($id);

        if (!$seat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seat not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'train_id' => 'sometimes|required|exists:trains,id',
            'seat_number' => 'sometimes|required|string|max:10',
            'is_reserved' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        $seat->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Seat updated successfully',
            'data' => $seat
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $seat = Seat::find($id);

        if (!$seat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seat not found'
            ], 404);
        }

        $seat->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Seat deleted successfully'
        ]);
    }

    /**
     * Reserve a seat.
     */
    public function reserve(Request $request, string $id)
    {
        $seat = Seat::find($id);

        if (!$seat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seat not found'
            ], 404);
        }

        if ($seat->is_reserved) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seat already reserved'
            ], 409);
        }

        $seat->update(['is_reserved' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Seat reserved successfully',
            'data' => $seat
        ]);
    }

    /**
     * Release a seat.
     */
    public function release(Request $request, string $id)
    {
        $seat = Seat::find($id);

        if (!$seat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seat not found'
            ], 404);
        }

        $seat->update(['is_reserved' => false]);

        return response()->json([
            'status' => 'success',
            'message' => 'Seat released successfully',
            'data' => $seat
        ]);
    }
}
