<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category', 50);
            $table->string('summary', 500);
            $table->text('body')->nullable();
            $table->string('ticker_text')->nullable();
            $table->string('button_label', 50)->nullable();
            $table->string('button_url')->nullable();
            $table->string('image_path')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('event_date')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('show_in_ticker')->default(true);
            $table->timestamps();

            $table->index(['is_published', 'show_in_ticker']);
            $table->index(['category', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
