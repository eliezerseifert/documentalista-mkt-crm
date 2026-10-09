<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone')->index();
            $table->string('saas_user_id')->nullable()->index();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cycle_ends_at')->nullable();
            $table->string('status')->default('novo')->index();
            $table->integer('score')->default(0);
            $table->boolean('is_whatsapp_opted_out')->default(false);
            $table->boolean('is_email_opted_out')->default(false);
            $table->timestamp('last_whatsapp_received_at')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
