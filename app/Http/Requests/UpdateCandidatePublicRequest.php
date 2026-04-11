<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCandidatePublicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // حقول التحقق الإلزامية للسماح بالتعديل
            'verification_code' => ['required', 'string'],
            'passport_no' => ['required_without:NationalNo', 'string'],
            'NationalNo' => ['required_without:passport_no', 'numeric'],
            'TrainingType' => ['required', 'in:internal,external'],

            // البيانات المسموح بتعديلها (استخدمنا sometimes لكي يتم التحديث فقط للحقول المُرسلة)
            'Name' => ['sometimes', 'required', 'string', 'max:255'],
            'BirthDate' => ['nullable', 'date'],
            'Qualification' => ['nullable', 'string', 'max:255'],
            'Phone' => ['nullable', 'string', 'max:20'],
            'Residence' => ['nullable', 'string', 'max:255'],
            'Size' => ['nullable', 'string', 'max:10'],
'Notes' => ['nullable', 'string'],
            'BankName' => ['nullable', 'string', 'max:255'],
        'BankAccountNo' => ['nullable', 'string', 'max:50'],
        'ShoeSize' => ['nullable', 'numeric'],

            // نمنع تعديل job_request_id بعد التقديم لضمان عدم التلاعب (لذلك لم نضفه هنا)
        ];
    }
}
