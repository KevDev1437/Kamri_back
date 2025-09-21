<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_streams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('stream_url');
            $table->string('thumbnail')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('viewer_count')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_streams');
    }
};
