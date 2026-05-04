<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Train;

class Seat extends Model
{
    protected $fillable = [
        'train_id',
        'seat_number',
        'is_reserved',
    ];

    protected $casts = [
        'is_reserved' => 'boolean',
    ];

    public function train()
    {
        return $this->belongsTo(Train::class);
    }
}
