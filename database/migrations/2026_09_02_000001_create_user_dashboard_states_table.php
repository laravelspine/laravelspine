<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_dashboard_states', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()->index();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->json('layout')->nullable();      // {area: [widgetId,...]} — null = belum diatur
            $table->json('visibility')->nullable();  // {widgetId: bool} — null = semua tampil
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_dashboard_states');
    }
};
