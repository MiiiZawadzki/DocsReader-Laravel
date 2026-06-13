<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_reads', function (Blueprint $table) {
            $table->dateTime('confirmed_at')->nullable()->after('confirmed');

            $table->unsignedInteger('total_active_seconds')->nullable()->after('confirmed_at');
            $table->unsignedInteger('pages_viewed_count')->nullable()->after('total_active_seconds');

            $table->unsignedBigInteger('last_session_id')->nullable()->after('pages_viewed_count');
        });
    }

    public function down(): void
    {
        Schema::table('document_reads', function (Blueprint $table) {
            $table->dropColumn([
                'confirmed_at',
                'total_active_seconds',
                'pages_viewed_count',
                'last_session_id',
            ]);
        });
    }
};
