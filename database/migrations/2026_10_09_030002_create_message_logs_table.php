<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('flow_step_id')->nullable()->constrained('flow_steps')->nullOnDelete();
            $table->string('channel'); // whatsapp, email
            $table->string('direction')->default('outbound'); // outbound, inbound
            $table->string('status')->default('enfileirada'); // enfileirada, enviada, entregue, lida, falhou
            $table->string('external_message_id')->nullable()->index();
            $table->text('content_sent');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Garantia de idempotência estrita para steps de fluxo
            $table->unique(['lead_id', 'flow_step_id', 'channel'], 'unique_lead_step_channel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_logs');
    }
};
