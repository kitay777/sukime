<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tweets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120)->nullable();
            $table->text('body'); // 最大8,000文字（バリデーションで制限）
            $table->boolean('is_paid')->default(false);
            $table->unsignedInteger('price_points')->nullable(); // 有償価格（pt）
            $table->timestamps();

            $table->index(['user_id','is_paid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tweets');
    }
};
