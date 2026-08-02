<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('printed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_type')->index();
            $table->string('template_path')->nullable();
            $table->string('generated_path')->nullable();
            $table->string('status')->default('printed')->index();
            $table->json('metadata')->nullable();
            $table->timestamp('printed_at')->useCurrent()->index();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['transaction_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_logs');
    }
};
