<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CONTOH MIGRATION KETIGA — tambah field tambahan untuk Sample Item.
 * Pola upgrade modul: migration baru, tidak mengubah migration lama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_items', function (Blueprint $table) {
            $table->decimal('weight', 8, 2)->nullable()->after('price');
            $table->text('dimensions')->nullable()->after('weight');
            $table->string('category', 45)->nullable()->after('dimensions');
            $table->boolean('is_active')->default(true)->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('sample_items', function (Blueprint $table) {
            $table->dropColumn(['weight', 'dimensions', 'category', 'is_active']);
        });
    }
};