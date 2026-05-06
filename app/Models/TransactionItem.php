<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    protected $fillable = [
        'transaction_id',
        'sparepart_id',
        'kode',
        'nama',
        'harga',
        'qty',
        'subtotal',
    ];

    protected $casts = [
        'transaction_id' => 'integer',
        'sparepart_id' => 'integer',
        'harga' => 'integer',
        'qty' => 'integer',
        'subtotal' => 'integer',
    ];

    // ==========================
    // RELASI
    // ==========================

    // Ke transaksi
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    // 🔥 TAMBAHAN PENTING
    // Ke sparepart (boleh NULL karena pakai nullOnDelete)
    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class, 'sparepart_id');
    }
}