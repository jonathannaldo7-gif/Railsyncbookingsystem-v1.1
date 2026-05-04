<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Train;
use App\Models\Seat;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'train_id',
        'seat_id',
        'booking_date',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function train()
    {
        return $this->belongsTo(Train::class);
    }

    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }
}
