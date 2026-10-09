<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('origin')->index(); // form_capture, whatsapp, subscription_checkout
            $table->string('ip_address')->nullable();
            $table->json('headers')->nullable();
            $table->json('payload')->nullable();
            $table->integer('response_status')->default(200);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
