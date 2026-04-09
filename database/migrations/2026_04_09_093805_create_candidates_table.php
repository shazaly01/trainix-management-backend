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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();

            // 1. رقم التسلسل: DECIMAL(18,0) مع خاصية الفريد (Unique)
            $table->decimal('SequenceNo', 18, 0)->unique()->nullable();

            // 2. البيانات الشخصية
            $table->string('Name'); // الاسم رباعي
            $table->date('BirthDate')->nullable();

            // 3. الهوية والاتصال
            // الرقم الوطني: DECIMAL(18,0) لضمان عدم ضياع الأرقام الطويلة
            $table->decimal('NationalNo', 18, 0)->unique()->nullable();
            $table->string('PassportNo', 50)->nullable();
            $table->date('PassportExpiry')->nullable();
            $table->string('Phone', 50)->nullable(); // String للحفاظ على الصفر الدولي أو المحلي في البداية

            // 4. بيانات إضافية
            $table->string('Qualification')->nullable(); // المؤهل العلمي
            $table->string('Residence')->nullable();    // السكن
            $table->string('Size', 50)->nullable();      // المقاس

            // 5. الحالة الصحية والملاحظات
            // استخدام boolean (0 أو 1) للياقة الطبية
            $table->boolean('IsFit')->default(true);
            $table->text('Notes')->nullable();

            // 6. الحقول الإدارية (تاريخ الإنشاء، التحديث، والحذف الآمن)
            $table->softDeletes(); // ⚠️ هذا الحقل ضروري جداً لأنك تستخدم SoftDeletes في الموديل
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
