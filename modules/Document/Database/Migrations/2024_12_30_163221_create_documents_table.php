<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('name')->nullable(false);
            $table->string('source_name')->nullable(false);
            $table->text('description')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->text('file_path')->nullable(false);
            $table->dateTime('date_from')->nullable(false);
            $table->dateTime('date_to')->nullable();
            $table->text('declaration_message')->nullable();
            $table->integer('delay')->nullable(false)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
