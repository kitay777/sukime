<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // school_name -> school
            if (Schema::hasColumn('users', 'school_name') && !Schema::hasColumn('users', 'school')) {
                $table->renameColumn('school_name', 'school');
            }

            // class_or_department -> faculty
            if (Schema::hasColumn('users', 'class_or_department') && !Schema::hasColumn('users', 'faculty')) {
                $table->renameColumn('class_or_department', 'faculty');
            }

            // grade はそのまま存在（名前は既に grade）
            // gender は enum('male','female','other') -> enum('male','female','other','unknown')
            // 既存が enum のため change()
        });

        // enum 拡張（unknown を追加）: MySQL は直接の変更が難しいため安全に再定義
        // 環境により SQL 実行が必要になるケースがあります
        if (Schema::hasColumn('users', 'gender')) {
            DB::statement("ALTER TABLE `users` MODIFY `gender` ENUM('male','female','other','unknown') NULL");
        }
    }

    public function down(): void
    {
        // gender enum を元に戻す（必要なら）
        if (Schema::hasColumn('users', 'gender')) {
            DB::statement("ALTER TABLE `users` MODIFY `gender` ENUM('male','female','other') NULL");
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'school') && !Schema::hasColumn('users', 'school_name')) {
                $table->renameColumn('school', 'school_name');
            }
            if (Schema::hasColumn('users', 'faculty') && !Schema::hasColumn('users', 'class_or_department')) {
                $table->renameColumn('faculty', 'class_or_department');
            }
        });
    }
};
