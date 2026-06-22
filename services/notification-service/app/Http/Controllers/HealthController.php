<?php

namespace App\Http\Controllers;

class HealthController extends Controller
{
    public function check()
    {
        return response()->json([
            'service' => 'notification-service',
            'status' => 'healthy',
            'timestamp' => now(),
            'redis' => $this->checkRedis(),
        ]);
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
