<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_reads', function (Blueprint $table) {
            $table->char('certificate_id', 26)->nullable()->unique()->after('confirmed');
        });
    }

    public function down(): void
    {
        Schema::table('document_reads', function (Blueprint $table) {
            $table->dropUnique(['certificate_id']);
            $table->dropColumn('certificate_id');
        });
    }
};
