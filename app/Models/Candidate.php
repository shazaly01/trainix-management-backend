<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- استيراد العلاقة الجديدة

class Candidate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'job_request_id',    // <-- إضافة حقل الربط
        'VerificationCode',  // <-- إضافة رقم التحقق
        'SequenceNo',
        'Name',
        'BirthDate',
        'Qualification',
        'PassportNo',
        'PassportExpiry',
        'NationalNo',
        'TrainingType',
        'Phone',
        'Residence',
        'Size',
        'IsFit',
        'Notes',
        'BankName',
    'BankAccountNo',
    ];

    protected $casts = [
        'SequenceNo' => 'decimal:0',
        'NationalNo' => 'decimal:0',
        'BirthDate' => 'date',
        'PassportExpiry' => 'date',
        'IsFit' => 'boolean',
    ];

    /**
     * منطق الأحداث (Events)
     */
    protected static function booted()
    {
        static::creating(function ($candidate) {
            // توليد رقم التسلسل
            if (!$candidate->SequenceNo) {
                $candidate->SequenceNo = now()->format('ymd') . rand(1000, 9999);
            }

            // توليد رقم تحقق (VerificationCode) عشوائي من 6 أرقام لتعديل ومتابعة الطلب
            if (!$candidate->VerificationCode) {
                $candidate->VerificationCode = (string) rand(100000, 999999);
            }
        });
    }

    /**
     * --- العلاقات (Relationships) ---
     */

    // علاقة: المترشح يتبع لطلب (دورة تدريبية) واحد
    public function jobRequest(): BelongsTo
    {
        return $this->belongsTo(JobRequest::class);
    }

    // علاقة الصورة الشخصية
    public function image(): MorphOne
    {
        return $this->morphOne(Document::class, 'documentable')
                    ->where('DocumentType', 'Profile Picture');
    }


    public function attachment(): MorphOne
    {
        return $this->morphOne(Document::class, 'documentable')
                    ->where('DocumentType', 'Applicant File');
    }
}
