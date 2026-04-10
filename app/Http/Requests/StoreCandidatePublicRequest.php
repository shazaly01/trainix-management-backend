<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCandidatePublicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // مسموح للجميع
    }

    public function rules(): array
    {
        return [
            // الدورة التدريبية
            'job_request_id' => ['required', 'exists:job_requests,id'],
            'TrainingType' => ['required', 'in:internal,external'],

            // البيانات الأساسية
            'Name' => ['required', 'string', 'max:255'],
            'NationalNo' => ['required', 'numeric', 'digits_between:10,18'],


            // بيانات إضافية
            'BirthDate' => ['nullable', 'date'],
            'Qualification' => ['nullable', 'string', 'max:255'],
            'PassportNo' => ['nullable', 'string', 'max:50'],
            'PassportExpiry' => ['nullable', 'date'],
            'Phone' => ['nullable', 'string', 'max:20'],
            'Residence' => ['nullable', 'string', 'max:255'],
            'Size' => ['nullable', 'string', 'max:10'],
            'Notes' => ['nullable', 'string'],

            // 🔥 إضافة حقل الصورة هنا هو السر 🔥
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }

    // يفضل إضافة الأسماء باللغة العربية للرسائل
    public function attributes(): array
    {
        return [
            'Name' => 'اسم المترشح',
            'NationalNo' => 'الرقم الوطني',
            'image' => 'الصورة الشخصية',
        ];
    }
}
