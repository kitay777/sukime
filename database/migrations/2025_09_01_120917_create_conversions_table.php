<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('conversations', function (Blueprint $table) {
      $table->id();
      $table->string('type')->default('private'); // private, group など
      // 1:1専用のペアを一意にするため、低いID/高いIDを記録（private用）
      $table->unsignedBigInteger('user_low_id')->nullable();
      $table->unsignedBigInteger('user_high_id')->nullable();
      $table->timestamps();

      $table->unique(['type','user_low_id','user_high_id']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('conversations');
  }
};
