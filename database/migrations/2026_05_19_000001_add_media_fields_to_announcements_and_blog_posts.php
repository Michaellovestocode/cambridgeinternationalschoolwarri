<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->json('gallery_images')->nullable()->after('image_path');
            $table->string('video_url')->nullable()->after('gallery_images');
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->json('gallery_images')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['gallery_images', 'video_url']);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('gallery_images');
        });
    }
};
