<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // تطبيق القاعدة: تحويل الأرقام الطويلة لنص
            'SequenceNo' => (string) $this->SequenceNo,

            // إضافة كود التحقق (مهم جداً عند استرجاع البيانات للتعديل الخارجي)
            'VerificationCode' => $this->VerificationCode,

            'Name' => $this->Name,
            'BirthDate' => $this->BirthDate ? $this->BirthDate->format('Y-m-d') : null,
            'Qualification' => $this->Qualification,
            'PassportNo' => $this->PassportNo,
            'PassportExpiry' => $this->PassportExpiry ? $this->PassportExpiry->format('Y-m-d') : null,

            // تطبيق القاعدة هنا أيضاً للرقم الوطني
            'NationalNo' => (string) $this->NationalNo,
            'TrainingType' => $this->TrainingType,

            'Phone' => $this->Phone,
            'Residence' => $this->Residence,
            'Size' => $this->Size,

            // 👈 إضافة رقم الحذاء هنا (مع تحويله لنص كونه Decimal)
            'ShoeSize' => (string) $this->ShoeSize,

            'IsFit' => $this->IsFit,
            'Notes' => $this->Notes,

            // حقول البنك
            'BankName' => $this->BankName,
            'BankAccountNo' => $this->BankAccountNo,

            // 👈 إضافة حالة الاعتماد (لتستفيد منها في لوحة التحكم لاحقاً)
            'is_approved' => $this->is_approved,

            // استدعاء ملف الـ Resource الخاص بالمرفقات لجلب بيانات ورابط الصورة الشخصية
            'image_url' => $this->image ? \Illuminate\Support\Facades\URL::signedRoute('documents.download', $this->image->id) : null,

            'CreatedAt' => $this->created_at->format('Y-m-d H:i'),
            'UpdatedAt' => $this->updated_at->format('Y-m-d H:i'),
        ];
    }
}
