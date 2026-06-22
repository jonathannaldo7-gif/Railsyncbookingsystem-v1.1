<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Seat;

class Train extends Model
{
    protected $fillable = [
        'name',
        'route',
        'departure_time',
        'arrival_time',
        'capacity',
    ];

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function marketPrice(): float
    {
        $route = strtolower($this->route);

        // realistic min/max ranges per common route (IDR)
        $ranges = [
            'jakarta-bandung' => [80000, 250000],
            'jakarta-surabaya' => [350000, 900000],
            'jakarta-yogyakarta' => [150000, 400000],
            'bandung-yogyakarta' => [200000, 450000],
            'bandung-surabaya' => [300000, 800000],
        ];

        // default range
        $min = 100000;
        $max = 300000;

        foreach ($ranges as $k => $range) {
            $parts = explode('-', $k);
            if (str_contains($route, $parts[0]) && str_contains($route, $parts[1])) {
                $min = $range[0];
                $max = $range[1];
                break;
            }
        }

        // small random variance for more realistic variation
        $price = mt_rand((int) ($min * 100), (int) ($max * 100)) / 100;

        // cap to a sane maximum (1.2 million)
        return min($price, 1200000.00);
    }
}
