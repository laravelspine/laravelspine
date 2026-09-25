<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CONTOH MIGRATION KEDUA — menambah field untuk menguji small_table.
 * Pola upgrade modul: migration baru, bukan edit migration lama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_items', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->unsignedInteger('quantity')->default(0)->after('description');
            $table->decimal('price', 12, 2)->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('sample_items', function (Blueprint $table) {
            $table->dropColumn(['description', 'quantity', 'price']);
        });
    }
};
