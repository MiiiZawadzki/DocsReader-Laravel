<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One reading clock per user: credited reading time can't outrun wall-clock time across all their sessions
        Schema::create('reading_time_budgets', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            // Epoch milliseconds, so sub-second credit survives on every database driver
            $table->unsignedBigInteger('credited_until_ms')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_time_budgets');
    }
};
