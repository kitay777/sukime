// database/migrations/2025_09_18_180000_add_timestamps_to_conversation_user.php
<?php
// database/migrations/2025_09_18_180000_add_timestamps_to_conversation_user.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('conversation_user', function (Blueprint $table) {
            if (!Schema::hasColumn('conversation_user', 'created_at')) {
                $table->timestamp('created_at')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('conversation_user', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
            // 必要ならユニーク。MySQL8 なら IF NOT EXISTS も可（環境に応じて）
            // $table->unique(['conversation_id','user_id'], 'conversation_user_conversation_id_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('conversation_user', function (Blueprint $table) {
            if (Schema::hasColumn('conversation_user', 'updated_at')) $table->dropColumn('updated_at');
            if (Schema::hasColumn('conversation_user', 'created_at')) $table->dropColumn('created_at');
        });
    }
};
