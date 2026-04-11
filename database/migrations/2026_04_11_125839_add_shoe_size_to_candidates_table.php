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
    Schema::table('candidates', function (Blueprint $table) {
        // الالتزام بقاعدة DECIMAL(18, 0) للأرقام
        $table->decimal('ShoeSize', 18, 0)->nullable()->after('Size');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            //
        });
    }
};
