<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Train;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrainController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $trains = Train::all();
        return response()->json([
            'status' => 'success',
            'message' => 'Trains retrieved successfully',
            'data' => $trains
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'route' => 'required|string|max:255',
            'departure_time' => 'required|date',
            'arrival_time' => 'required|date|after:departure_time',
            'capacity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        $train = Train::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Train created successfully',
            'data' => $train
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $train = Train::find($id);

        if (!$train) {
            return response()->json([
                'status' => 'error',
                'message' => 'Train not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Train retrieved successfully',
            'data' => $train
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $train = Train::find($id);

        if (!$train) {
            return response()->json([
                'status' => 'error',
                'message' => 'Train not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'route' => 'sometimes|required|string|max:255',
            'departure_time' => 'sometimes|required|date',
            'arrival_time' => 'sometimes|required|date|after:departure_time',
            'capacity' => 'sometimes|required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        $train->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Train updated successfully',
            'data' => $train
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $train = Train::find($id);

        if (!$train) {
            return response()->json([
                'status' => 'error',
                'message' => 'Train not found'
            ], 404);
        }

        $train->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Train deleted successfully'
        ]);
    }
}
