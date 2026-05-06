<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sparepart extends Model
{
    protected $fillable = [
        'kode',
        'nama_barang',
        'stok',
        'foto',
        'tanggal_masuk',
        'harga_beli',
        'harga_jual',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
    ];

    // ==========================
    // RELASI
    // ==========================

    // Relasi ke inventory movements
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    // 🔥 TAMBAHAN (PENTING)
    // Relasi ke transaction items (POS)
    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
}