<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ip_bans')) {
            return;
        }

        Schema::create('ip_bans', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45);
            $table->string('reason');
            $table->timestamp('banned_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['ip', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_bans');
    }
};
