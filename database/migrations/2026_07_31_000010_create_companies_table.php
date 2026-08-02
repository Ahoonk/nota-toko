<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('website')->nullable();
            $table->string('npwp')->nullable()->index();
            $table->string('responsible_name')->nullable();
            $table->string('responsible_position')->nullable();
            $table->string('signature_path')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
