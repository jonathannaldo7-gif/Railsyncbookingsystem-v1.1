<?php

namespace App\Http\Controllers;

class HealthController extends Controller
{
    public function check()
    {
        return response()->json([
            'service' => 'train-service',
            'status' => 'healthy',
            'timestamp' => now(),
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
        ]);
    }

    private function checkDatabase()
    {
        try {
            \DB::connection()->getPdo();
            return 'connected';
        } catch (\Exception $e) {
            return 'error: ' . $e->getMessage();
        }
    }

    private function checkRedis()
    {
        try {
            \Redis::ping();
            return 'connected';
        } catch (\Exception $e) {
            return 'error: ' . $e->getMessage();
        }
    }
}
