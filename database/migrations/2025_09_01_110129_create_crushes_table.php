<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('crushes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('school')->nullable();
            $table->string('faculty')->nullable();
            $table->string('grade')->nullable();
            $table->string('gender')->nullable(); // 男性 / 女性 / その他 / 未回答 など
            // 週1回制限 判定に使う。updated_at でも良いが、明示的な方が扱いやすい
            $table->timestamp('last_changed_at')->nullable();
            $table->timestamps();

            $table->unique('user_id'); // ユーザーにつき1件だけ
        });
    }

    public function down(): void {
        Schema::dropIfExists('crushes');
    }
};
