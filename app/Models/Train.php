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
}
