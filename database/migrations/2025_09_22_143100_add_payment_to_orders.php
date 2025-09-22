<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $t) {
            $t->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $t->timestamp('paid_at')->nullable();
        });
    }

    public function down(): void {
        Schema::table('orders', function (Blueprint $t) {
            $t->dropForeign(['payment_id']);
            $t->dropColumn(['payment_id', 'paid_at']);
        });
    }
};
