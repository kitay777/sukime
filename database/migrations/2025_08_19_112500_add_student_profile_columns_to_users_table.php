<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/xxxx_xx_xx_xxxxxx_add_student_profile_columns_to_users_table.php

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('real_name')->nullable()->after('name');             // 本名
            $table->string('school_name')->nullable()->after('real_name');      // 学校名
            $table->string('grade')->nullable()->after('school_name');          // 学年（文字列にしておくと「高1」「B2」等もOK）
            $table->string('class_or_department')->nullable()->after('grade');  // クラスor学部・学科
            $table->boolean('profile_completed')->default(false)->after('remember_token'); // 入力済みフラグ
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'real_name',
                'school_name',
                'grade',
                'class_or_department',
                'profile_completed',
            ]);
        });
    }
};

