<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('total_days')->default(15);
            $table->timestamps();
        });

        Schema::create('flow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flow_id')->constrained('flows')->cascadeOnDelete();
            $table->integer('step_number');
            $table->integer('day_offset')->default(0);
            $table->time('send_time')->default('09:00:00');
            $table->string('channel')->default('both'); // whatsapp, email, both
            $table->boolean('is_active')->default(true);
            $table->boolean('is_promotion')->default(false);
            $table->string('promo_coupon_code')->nullable();
            $table->integer('promo_expires_in_days')->nullable();
            $table->json('conditions')->nullable();
            $table->timestamps();

            $table->unique(['flow_id', 'step_number']);
        });

        Schema::create('step_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flow_step_id')->constrained('flow_steps')->cascadeOnDelete();
            $table->string('channel'); // whatsapp, email
            $table->string('subject')->nullable(); // Para e-mail
            $table->text('body_text');
            $table->string('image_path')->nullable();
            $table->string('button_label')->nullable();
            $table->text('button_url')->nullable();
            $table->string('whatsapp_template_name')->nullable();
            $table->timestamps();
        });

        Schema::create('lead_flow_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('flow_id')->constrained('flows')->cascadeOnDelete();
            $table->integer('current_step_number')->default(1);
            $table->timestamp('started_at');
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('status')->default('active'); // active, paused, completed, cancelled
            $table->timestamps();

            $table->index(['lead_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_flow_runs');
        Schema::dropIfExists('step_messages');
        Schema::dropIfExists('flow_steps');
        Schema::dropIfExists('flows');
    }
};
