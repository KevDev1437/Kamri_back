<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $t->string('provider')->default('stripe');
            $t->string('intent_id')->unique();
            $t->enum('status', [
                'requires_payment_method','requires_confirmation','processing',
                'succeeded','requires_action','canceled','failed','refunded'
            ])->default('requires_payment_method');
            $t->decimal('amount', 10, 2)->default(0);
            $t->string('currency', 3)->default('EUR');
            $t->json('last_error')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('payments');
    }
};
