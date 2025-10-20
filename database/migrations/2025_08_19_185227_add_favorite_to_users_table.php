<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_favorite_to_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('favorite_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('favorite_set_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('favorite_user_id');
            $table->dropColumn('favorite_set_at');
        });
    }
};

