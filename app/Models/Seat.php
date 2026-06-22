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
        'price',
    ];

    protected $casts = [
        'is_reserved' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function train()
    {
        return $this->belongsTo(Train::class);
    }
}
