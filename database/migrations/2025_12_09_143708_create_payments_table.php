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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2);
            $table->foreignId('order_id')->constrained('orders')->on('restrict');
            $table->foreignId('user_id')->constrained("users")->onDelete('restrict');
            $table->foreignId('stage_id')->default(1)->constrained('stages')->onDelete('cascade');
            $table->foreignId('paymenttype_id')->constrained('paymenttypes')->onDelete('cascade');
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
