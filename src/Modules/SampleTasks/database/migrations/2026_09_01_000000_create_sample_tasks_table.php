<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CONTOH MIGRATION — tabel child SampleTask (belongsTo SampleItem).
 *
 * Pola child-parent legacy: quotation_items (child quotation), licence_items
 * (child licence). Di sini: SampleTask.child -> SampleItem.parent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_tasks', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGINT unsigned — alias eksplisit dari $table->id()
            $table->string('ulid', 26)->nullable()->unique(); // HasUlids
            $table->foreignId('sample_item_id')->constrained('sample_items')->cascadeOnDelete();
            $table->string('title');
            $table->string('status', 32)->default('pending'); // pending|in_progress|done
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_tasks');
    }
};
