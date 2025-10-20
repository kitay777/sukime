<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('crushes', function (Blueprint $table) {
      $table->foreignId('crush_user_id')->nullable()->after('user_id')
            ->constrained('users')->nullOnDelete();
      $table->index(['user_id', 'crush_user_id']);
    });
  }
  public function down(): void {
    Schema::table('crushes', function (Blueprint $table) {
      $table->dropConstrainedForeignId('crush_user_id');
      $table->dropIndex(['user_id', 'crush_user_id']);
    });
  }
};
