<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_gachas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('gacha_date'); // 1日1回制限
            $table->enum('rarity', ['normal','rare','super_rare','ultra_rare','secret']);
            $table->boolean('is_win')->default(false); // 両想い成立かどうか
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_gachas');
    }
};
