<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name');
            $table->string('item_category_name')->nullable();
            $table->string('brand')->nullable();
            $table->string('replacement_item_name')->nullable();
            $table->decimal('qty', 15, 2);
            $table->string('unit_name');
            $table->decimal('price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['transaction_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
