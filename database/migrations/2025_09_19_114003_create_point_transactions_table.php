<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('point_transactions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->bigInteger('amount')->comment('ポイント増減。加算=正、減算=負');
      $table->string('type', 20)->comment('purchase/spend/refund/adjust');
      $table->string('reason')->nullable();
      $table->json('meta')->nullable(); // stripe ids 等
      $table->timestamps();

      $table->index(['user_id','created_at']);
    });

    Schema::create('point_credits', function (Blueprint $table) {
      // 二重付与防止用（Stripeの支払IDに対して一度だけクレジット）
      $table->id();
      $table->string('payment_intent_id')->unique();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->bigInteger('amount_points');
      $table->timestamps();
    });
  }

  public function down(): void {
    Schema::dropIfExists('point_credits');
    Schema::dropIfExists('point_transactions');
  }
};
