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
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type')->default('other'); // credit_card, personal_loan, student_loan, mortgage, car_loan, medical, other
            $table->double('original_amount');
            $table->double('current_balance');
            $table->double('interest_rate');
            $table->double('minimum_payment');
            $table->integer('due_day');
            $table->string('start_date');
            $table->string('currency')->default('USD');
            $table->string('lender')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_paid_off')->default(false);
            $table->boolean('exclude_from_balance')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
