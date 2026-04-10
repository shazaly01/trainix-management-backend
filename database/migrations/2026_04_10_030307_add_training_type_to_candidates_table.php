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
        // نستخدم التدريب "الداخلي" كقيمة افتراضية
        $table->string('TrainingType')->default('internal')->after('Notes');
    });
}

public function down(): void
{
    Schema::table('candidates', function (Blueprint $table) {
        $table->dropColumn('TrainingType');
    });
}
};
