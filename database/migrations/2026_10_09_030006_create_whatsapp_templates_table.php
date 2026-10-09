<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('language')->default('pt_BR');
            $table->string('category')->default('MARKETING');
            $table->string('status')->default('APPROVED'); // APPROVED, PENDING, REJECTED
            $table->text('body_preview')->nullable();
            $table->json('variables_mapping')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
