<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'status')) {
                $table->string('status', 10)->default('OK')->after('paid');
            }
            if (!Schema::hasColumn('transactions', 'change')) {
                $table->integer('change')->default(0)->after('status');
            }
            if (!Schema::hasColumn('transactions', 'lack')) {
                $table->integer('lack')->default(0)->after('change');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'lack')) $table->dropColumn('lack');
            if (Schema::hasColumn('transactions', 'change')) $table->dropColumn('change');
            if (Schema::hasColumn('transactions', 'status')) $table->dropColumn('status');
        });
    }
};