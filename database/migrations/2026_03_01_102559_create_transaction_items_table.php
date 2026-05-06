<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();

            // Relasi ke transaksi (hapus transaksi → item ikut terhapus)
            $table->foreignId('transaction_id')
                  ->constrained('transactions')
                  ->cascadeOnDelete();

            // 🔥 PERBAIKAN DI SINI
            // Jika sparepart dihapus → jadi NULL (tidak error)
            $table->foreignId('sparepart_id')
                  ->nullable()
                  ->constrained('spareparts')
                  ->nullOnDelete();

            // Data snapshot (biar histori aman)
            $table->string('kode', 50);
            $table->string('nama', 150);

            $table->unsignedBigInteger('harga')->default(0);
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedBigInteger('subtotal')->default(0);

            $table->timestamps();

            $table->index(['transaction_id', 'sparepart_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};