<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('rating'); // 1..5
            $table->text('comment');
            $table->boolean('anonymous')->default(false);
            $table->boolean('verified')->default(false); // achat vérifié (placeholder)
            $table->unsignedInteger('helpful_count')->default(0);
            $table->timestamps();
            $table->index(['product_id', 'rating']);
        });

        Schema::create('review_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('reviews')->cascadeOnDelete();
            $table->string('path'); // Storage::url
            $table->timestamps();
        });

        Schema::create('review_helpful_votes', function (Blueprint $table) {
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
        Schema::dropIfExists('review_helpful_votes');
        Schema::dropIfExists('review_photos');
        Schema::dropIfExists('reviews');
    }
};
