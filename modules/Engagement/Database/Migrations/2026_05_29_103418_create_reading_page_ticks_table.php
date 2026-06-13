<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_page_ticks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reading_session_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('document_id');
            $table->unsignedInteger('page_number');

            $table->char('client_event_id', 26);
            $table->unsignedInteger('active_ms');
            $table->dateTime('occurred_at');
            $table->timestamp('created_at');

            $table->unique(['reading_session_id', 'client_event_id']);
            $table->index(['document_id', 'page_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_page_ticks');
    }
};
