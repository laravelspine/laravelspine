<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CONTOH MIGRATION MODUL — tabel milik modul sendiri.
 * Berjalan otomatis saat modul di-install (ModuleService menjalankan migrate).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_items');
    }
};
