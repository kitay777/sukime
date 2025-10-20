<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tweet_unlocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tweet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('price_points');
            $table->timestamps();

            $table->unique(['tweet_id','buyer_id']); // 二重購入防止
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tweet_unlocks');
    }
};
