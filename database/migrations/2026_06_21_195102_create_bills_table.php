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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->double('amount');
            $table->string('category');
            $table->string('frequency')->default('monthly'); // weekly, biweekly, monthly, quarterly, yearly
            $table->integer('due_day');
            $table->integer('due_month')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->boolean('is_auto_pay')->default(false);
            $table->boolean('is_paid')->default(false);
            $table->boolean('is_variable')->default(false);
            $table->string('last_paid_date')->nullable();
            $table->string('next_due_date');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
