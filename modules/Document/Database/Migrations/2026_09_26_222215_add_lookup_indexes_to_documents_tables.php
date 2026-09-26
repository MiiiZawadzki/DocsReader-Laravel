<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->unique('uuid');
        });

        Schema::table('user_documents', function (Blueprint $table) {
            $table->unique(['user_id', 'document_id']);
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
        });

        Schema::table('user_documents', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id', 'document_id']);
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
