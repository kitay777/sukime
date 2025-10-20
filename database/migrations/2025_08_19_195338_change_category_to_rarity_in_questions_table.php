<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('questions', function (Blueprint $table) {
        $table->dropColumn('category'); // 旧カラム削除
        $table->enum('rarity', [
            'normal',
            'rare',
            'super_rare',
            'ultra_rare',
            'secret'
        ])->after('content');
    });
}

public function down(): void
{
    Schema::table('questions', function (Blueprint $table) {
        $table->dropColumn('rarity');
        $table->enum('category', ['initial', 'daily'])->nullable();
    });
}

};
