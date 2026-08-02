<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_template_id')->constrained()->cascadeOnDelete();
            $table->string('field_key');
            $table->string('label');
            $table->decimal('x', 10, 2)->default(0);
            $table->decimal('y', 10, 2)->default(0);
            $table->unsignedInteger('font_size')->default(12);
            $table->string('font_family')->default('Helvetica');
            $table->string('font_weight')->default('normal');
            $table->string('text_align')->default('left');
            $table->unsignedTinyInteger('page_number')->default(1);
            $table->boolean('is_bold')->default(false);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['document_template_id', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_mappings');
    }
};
