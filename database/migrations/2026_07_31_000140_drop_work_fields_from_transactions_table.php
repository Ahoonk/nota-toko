<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['work_type', 'work_location', 'work_duration']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('work_type')->nullable()->after('description');
            $table->string('work_location')->nullable()->after('work_type');
            $table->string('work_duration')->nullable()->after('work_location');
            $table->index(['work_type', 'work_location']);
            $table->index('work_duration');
        });
    }
};
