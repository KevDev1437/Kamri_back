<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('currency', 3)->default('EUR');
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('product_name');
            $table->string('product_image')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('qty');
            $table->json('options')->nullable();
            $table->string('options_hash', 64)->default(''); // md5 json trié
            $table->timestamps();

            $table->unique(['cart_id', 'product_id', 'options_hash']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
