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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained("users")->cascadeOnDelete();
            $table->foreignId('ordertype_id')->constrained("ordertypes")->restrictOnDelete();
            $table->foreignId('paymenttype_id')->constrained("paymenttypes")->restrictOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained("drivers")->nullOnDelete();
            $table->foreignId('status_id')->nullable()->constrained("statuses")->nullOnDelete();
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('transaction_id')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->string('order_note')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
