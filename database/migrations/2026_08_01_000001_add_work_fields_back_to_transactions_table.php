<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('work_type')->nullable()->after('description');
            $table->string('work_location')->nullable()->after('work_type');
            $table->string('work_duration')->nullable()->after('work_location');
            $table->foreignId('brought_item_id')->nullable()->after('work_duration')->constrained('items')->nullOnDelete();

            $table->index(['work_type', 'work_location']);
            $table->index('work_duration');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brought_item_id');
            $table->dropIndex(['work_type', 'work_location']);
            $table->dropIndex(['work_duration']);
            $table->dropColumn(['work_type', 'work_location', 'work_duration']);
        });
    }
};
