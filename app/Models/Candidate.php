<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Candidate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'SequenceNo',
        'Name',
        'BirthDate',
        'Qualification',
        'PassportNo',
        'PassportExpiry',
        'NationalNo',
        'Phone',
        'Residence',
        'Size',
        'IsFit',
        'Notes',
    ];

    // تطبيق قاعدتك الذهبية للحفاظ على الأرقام الطويلة
    protected $casts = [
        'SequenceNo' => 'decimal:0',
        'NationalNo' => 'decimal:0',
        'BirthDate' => 'date',
        'PassportExpiry' => 'date',
        'IsFit' => 'boolean',
    ];

    /**
     * منطق الأحداث (Events): توليد رقم التسلسل تلقائياً قبل الحفظ إذا كان فارغاً
     */
    protected static function booted()
    {
        static::creating(function ($candidate) {
            if (!$candidate->SequenceNo) {
                // توليد رقم بناءً على التاريخ ورقم عشوائي
                $candidate->SequenceNo = now()->format('ymd') . rand(1000, 9999);
            }
        });
    }

    /**
     * --- العلاقات (Relationships) ---
     * علاقة الصورة الشخصية عبر نظام المرفقات (Document)
     */
    public function image(): MorphOne
    {
        return $this->morphOne(Document::class, 'documentable')
                    ->where('DocumentType', 'Profile Picture');
    }
}
