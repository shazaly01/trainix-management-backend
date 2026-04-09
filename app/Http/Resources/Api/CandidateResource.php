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

            'Name' => $this->Name,
            'BirthDate' => $this->BirthDate ? $this->BirthDate->format('Y-m-d') : null,
            'Qualification' => $this->Qualification,
            'PassportNo' => $this->PassportNo,
            'PassportExpiry' => $this->PassportExpiry ? $this->PassportExpiry->format('Y-m-d') : null,

            // تطبيق القاعدة هنا أيضاً للرقم الوطني
            'NationalNo' => (string) $this->NationalNo,

            'Phone' => $this->Phone,
            'Residence' => $this->Residence,
            'Size' => $this->Size,
            'IsFit' => $this->IsFit,
            'Notes' => $this->Notes,

            // استدعاء ملف الـ Resource الخاص بالمرفقات لجلب بيانات ورابط الصورة الشخصية
            'image_url' => $this->image ? $this->image->url : null,

            'CreatedAt' => $this->created_at->format('Y-m-d H:i'),
            'UpdatedAt' => $this->updated_at->format('Y-m-d H:i'),
        ];
    }
}

