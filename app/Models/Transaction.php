<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'invoice',
        'buyer_name',
        'total',
        'paid',
        'status',
        'change',
        'lack',
        'user_id'
    ];

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}