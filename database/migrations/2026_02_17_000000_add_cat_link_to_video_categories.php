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
        Schema::connection('crafty_video_mysql')->table('main_categories', function (Blueprint $table) {
            $table->string('cat_link', 255)->nullable()->after('id_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('crafty_video_mysql')->table('main_categories', function (Blueprint $table) {
            $table->dropColumn('cat_link');
        });
    }
};
