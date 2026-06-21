<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('currency')->default('USD');
            $table->string('locale')->default('en-US');
            $table->string('theme')->default('dark');
            $table->string('pin_hash')->nullable();
            $table->boolean('has_completed_onboarding')->default(false);
            $table->string('payday_freq')->nullable();
            $table->string('payday_date')->nullable();
            $table->double('payday_amount')->nullable();
            $table->string('payday_override')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
