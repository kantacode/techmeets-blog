<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = ['title', 'description', 'event_date'];

    protected $casts = ['event_date' => 'datetime'];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
