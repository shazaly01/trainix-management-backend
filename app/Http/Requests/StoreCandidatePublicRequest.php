<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCandidatePublicRequest extends FormRequest
{
    public function authorize(): bool
    {
        // مسموح للجميع بالتقديم (لأنه تقديم عام)
        return true;
    }

    public function rules(): array
    {
        return [
            // الدورة التدريبية مطلوبة
            'job_request_id' => ['required', 'exists:job_requests,id'],

            // البيانات الأساسية
            'Name' => ['required', 'string', 'max:255'],
            'NationalNo' => ['required', 'numeric', 'digits_between:10,18'],

            // بيانات إضافية (جعلتها اختيارية حسب احتياجك، يمكنك تغييرها لـ required)
            'BirthDate' => ['nullable', 'date'],
            'Qualification' => ['nullable', 'string', 'max:255'],
            'PassportNo' => ['nullable', 'string', 'max:50'],
            'PassportExpiry' => ['nullable', 'date'],
            'Phone' => ['nullable', 'string', 'max:20'],
            'Residence' => ['nullable', 'string', 'max:255'],
            'Size' => ['nullable', 'string', 'max:10'],
        ];
    }
}
