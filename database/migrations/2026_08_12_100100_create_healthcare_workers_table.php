<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('healthcare_workers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone_number', 20)->unique();
            $table->string('password_hash');
            $table->enum('role', ['bidan', 'kader']);
            $table->string('str_number', 50)->nullable();
            $table->string('appointment_letter_ref')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->foreignId('verified_by_admin_id')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->string('region_code', 20);
            $table->boolean('is_available')->default(true);
            $table->date('unavailable_from')->nullable();
            $table->date('unavailable_until')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('region_code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('healthcare_workers');
    }
};
