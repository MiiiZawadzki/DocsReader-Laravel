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
        Schema::create('document_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Document::class)->constrained();
            $table->foreignIdFor(\App\Models\User::class)->constrained();
            $table->boolean('confirmed')->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_reads');
    }
};
