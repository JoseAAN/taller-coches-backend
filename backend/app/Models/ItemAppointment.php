<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemAppointment extends Model
{
    protected $fillable = ['item_id', 'appointment_id'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
