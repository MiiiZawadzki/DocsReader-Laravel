<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_reads', function (Blueprint $table) {
            $table->unique(['document_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('document_reads', function (Blueprint $table) {
            $table->dropUnique(['document_id', 'user_id']);
        });
    }
};
