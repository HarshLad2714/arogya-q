<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('specialty');
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('state')->default('Gujarat');
            $table->string('pincode', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->json('documents')->nullable();
            $table->json('photos')->nullable();
            $table->json('services')->nullable();
            $table->unsignedSmallInteger('cancel_cutoff_minutes')->default(30);
            $table->unsignedTinyInteger('refund_percent')->default(100);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('specialization');
            $table->string('qualification');
            $table->unsignedTinyInteger('experience_years')->default(0);
            $table->unsignedInteger('consultation_fee')->default(0);
            $table->string('room')->default('Room 1');
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedSmallInteger('max_tokens_per_day')->default(40);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedTinyInteger('avg_consultation_minutes')->default(12);
            $table->timestamps();
            $table->index(['doctor_id', 'day_of_week']);
        });

        Schema::create('doctor_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->date('leave_date');
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->unique(['doctor_id', 'leave_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_leaves');
        Schema::dropIfExists('doctor_schedules');
        Schema::dropIfExists('doctors');
        Schema::dropIfExists('clinics');
    }
};
