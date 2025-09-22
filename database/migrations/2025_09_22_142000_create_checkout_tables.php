<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('number')->unique();
            $t->enum('status', ['pending','paid','failed','canceled'])->default('pending');
            $t->string('currency', 3)->default('EUR');
            $t->decimal('subtotal', 10, 2)->default(0);
            $t->decimal('discount', 10, 2)->default(0);
            $t->decimal('shipping_price', 10, 2)->default(0);
            $t->decimal('tax', 10, 2)->default(0);
            $t->decimal('total', 10, 2)->default(0);
            $t->json('delivery_method')->nullable();
            $t->json('shipping_address');
            $t->json('billing_address');
            $t->string('payment_intent_id')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();
        });

        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $t->string('product_name');
            $t->string('product_image')->nullable();
            $t->decimal('unit_price', 10, 2);
            $t->unsignedInteger('qty');
            $t->decimal('subtotal', 10, 2);
            $t->json('options')->nullable();
            $t->string('options_hash', 64)->default('');
            $t->timestamps();
        });

        Schema::create('coupons', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->enum('type', ['percentage','fixed']);
            $t->decimal('value', 10, 2);
            $t->decimal('min_subtotal', 10, 2)->nullable();
            $t->unsignedInteger('max_uses')->nullable();
            $t->unsignedInteger('used_count')->default(0);
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('shipping_methods', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('label');
            $t->decimal('price', 10, 2);
            $t->string('eta');
            $t->boolean('active')->default(true);
            $t->json('countries')->nullable(); // liste de codes pays
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
