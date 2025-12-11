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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->nullable();
            $table->string('image')->nullable();
            $table->string('description')->nullable();
            $table->decimal('price',10,2);
            $table->tinyInteger('rating')->default(5);
            $table->foreignId('subcategory_id')->constrained('subcategories')->onDelete("cascade");
            $table->foreignId('category_id')->constrained('categories')->onDelete("cascade");
            $table->foreignId('status_id')->default(3)->constrained('statuses')->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
