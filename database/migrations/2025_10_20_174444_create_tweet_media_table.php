<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tweet_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tweet_id')->constrained()->cascadeOnDelete();
            $table->enum('kind', ['image','video']);
            $table->string('path');       // storage/app/private/... など
            $table->string('thumb_path')->nullable(); // サムネ
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('duration')->nullable(); // 秒（動画）
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tweet_media');
    }
};
