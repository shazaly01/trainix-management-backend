<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // مهم جداً للتعامل مع البيانات الحالية

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            // 1. إضافة الحقل مع جعل القيمة الافتراضية true مؤقتاً للبيانات القديمة
            $table->boolean('is_approved')->default(true)->after('Notes');
        });

        // 2. (اختياري للتأكيد) تحديث كل السجلات القديمة لتكون معتمدة
        DB::table('candidates')->update(['is_approved' => true]);

        // 3. الآن نجعل القيمة الافتراضية false للطلبات الجديدة التي ستأتي مستقبلاً
        // ملاحظة: SQL Server أو MySQL سيفهمان أن القادم فقط هو الـ false
        Schema::table('candidates', function (Blueprint $table) {
            $table->boolean('is_approved')->default(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });
    }
};
