<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_sessions', function (Blueprint $table) {
            $table->id();

            $table->char('uuid', 26)->unique();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('document_id')->constrained();
            $table->dateTime('started_at');
            $table->dateTime('last_tick_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->unsignedInteger('total_active_seconds')->default(0);

            $table->unsignedInteger('last_page')->default(1);
            $table->json('client_meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'document_id']);
            $table->index('document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_sessions');
    }
};
