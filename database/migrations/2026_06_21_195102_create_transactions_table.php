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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->foreignId('bill_id')->nullable()->constrained()->onDelete('set null');
            $table->double('amount');
            $table->string('type')->default('expense'); // income, expense, transfer
            $table->string('category');
            $table->string('subcategory')->nullable();
            $table->string('description');
            $table->string('date');
            $table->boolean('is_recurring')->default(false);
            $table->unsignedBigInteger('recurring_id')->nullable();
            $table->text('tags')->nullable(); // stored as JSON or comma-separated
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
