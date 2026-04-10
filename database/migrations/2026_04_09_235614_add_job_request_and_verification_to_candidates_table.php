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
            // إضافة حقل تاريخ الميلاد (إذا لم يكن موجوداً من قبل)
            if (!Schema::hasColumn('candidates', 'BirthDate')) {
                $table->date('BirthDate')->nullable()->after('Name');
            }

            // إضافة حقل الربط بجدول الطلبات (الدورات التدريبية)
            // جعلناه nullable حتى لا تحدث مشكلة مع أي بيانات قديمة مسجلة مسبقاً
            $table->foreignId('job_request_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('job_requests')
                  ->nullOnDelete();

            // إضافة حقل رقم التحقق
            $table->string('VerificationCode', 10)
                  ->nullable()
                  ->after('job_request_id')
                  ->comment('رقم التحقق للمتابعة وتعديل الطلب');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            // إزالة الربط أولاً ثم حذف الحقول عند التراجع
            $table->dropForeign(['job_request_id']);
            $table->dropColumn(['job_request_id', 'VerificationCode', 'BirthDate']);
        });
    }
};
