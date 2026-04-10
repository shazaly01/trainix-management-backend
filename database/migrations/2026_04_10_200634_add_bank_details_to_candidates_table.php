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
            // إضافة الحقول كنصوص قابلة للفراغ (اختيارية) كما طلبت
            $table->string('BankName')->nullable()->after('Notes');
            $table->string('BankAccountNo')->nullable()->after('BankName');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['BankName', 'BankAccountNo']);
        });
    }
};
