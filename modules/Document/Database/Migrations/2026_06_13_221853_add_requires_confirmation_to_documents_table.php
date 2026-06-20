<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('documents', 'requires_confirmation')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->boolean('requires_confirmation')->nullable()->default(false);
            });
        }

        DB::table('documents')
            ->whereNotNull('declaration_message')
            ->where('declaration_message', '!=', '')
            ->update(['requires_confirmation' => true]);

        DB::table('documents')
            ->whereNull('requires_confirmation')
            ->update(['requires_confirmation' => false]);

        Schema::table('documents', function (Blueprint $table) {
            $table->boolean('requires_confirmation')->default(false)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('requires_confirmation');
        });
    }
};
