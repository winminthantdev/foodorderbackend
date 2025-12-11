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
        Schema::create('menu_addons', function (Blueprint $table) {
    $table->id();
    $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
    $table->foreignId('addon_id')->constrained('addons')->onDelete('cascade');

    // if you want quantity or custom price:
    $table->integer('max_quantity')->default(1);
    $table->decimal('custom_price', 10, 2)->nullable(); // optional

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_addons');
    }
};
