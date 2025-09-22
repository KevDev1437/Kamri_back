<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1..5
            $table->text('comment');
            $table->json('photos')->nullable();
            $table->unsignedInteger('helpful_count')->default(0);
            $table->unsignedInteger('reported_count')->default(0);
            $table->boolean('verified')->default(false);
            $table->boolean('anonymous')->default(false);
            $table->enum('status', ['published', 'pending', 'rejected'])->default('published');
            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index(['product_id', 'rating']);
        });

        Schema::create('review_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('reviews')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['review_id', 'user_id']);
        });

        Schema::create('review_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('reviews')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->unique(['review_id', 'user_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('review_reports');
        Schema::dropIfExists('review_votes');
        Schema::dropIfExists('reviews');
    }
};
