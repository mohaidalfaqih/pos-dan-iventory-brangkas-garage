<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'sparepart_id',
        'type',
        'qty',
        'note',
        'reference_id',
        'user_id',
    ];

    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}