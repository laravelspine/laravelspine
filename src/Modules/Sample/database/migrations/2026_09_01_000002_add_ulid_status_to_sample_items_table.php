<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CONTOH MIGRATION KETIGA — tambah ulid (bukan uuid) + status.
 *
 * ulid  : identitas publik (Laravel HasUlids auto-generate saat create)
 * status: lifecycle status — dipakai untuk menguji pola status-change hook
 *         (EntityUpdated dengan changes['status'], seperti estimate_accepted).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_items', function (Blueprint $table) {
            $table->string('ulid', 26)->nullable()->unique()->after('id');
            $table->string('status', 32)->default('draft')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('sample_items', function (Blueprint $table) {
            $table->dropColumn(['ulid', 'status']);
        });
    }
};
