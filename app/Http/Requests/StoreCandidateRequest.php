<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // الصلاحيات تمت معالجتها في الـ Policy
    }

    public function rules(): array
    {
        $rules = [
            // الأرقام الطويلة أضفنا لها numeric
            'SequenceNo' => ['nullable', 'numeric', 'unique:candidates,SequenceNo'],
            'Name' => ['required', 'string', 'max:255'],
            'BirthDate' => ['nullable', 'date'],
            'Qualification' => ['nullable', 'string', 'max:255'],
            'PassportNo' => ['nullable', 'string', 'max:50'],
            'PassportExpiry' => ['nullable', 'date'],
            'NationalNo' => ['nullable', 'numeric', 'unique:candidates,NationalNo'],
            'TrainingType' => ['required', 'in:internal,external'],
            'Phone' => ['nullable', 'string', 'max:50'],
            'Residence' => ['nullable', 'string', 'max:255'],
            'Size' => ['nullable', 'string', 'max:50'],
            'Notes' => ['nullable', 'string'],
            'BankName' => ['nullable', 'string', 'max:255'],
            'BankAccountNo' => ['nullable', 'string', 'max:50'],
            'ShoeSize' => ['nullable', 'numeric'],

            // التحقق من الصورة المرفقة (اختيارية، ويجب أن تكون صورة بحجم أقصى 5 ميجا)
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];

        // 👈 التعديل هنا: نقبل حقل IsFit فقط إذا كان المستخدم يمتلك صلاحية التعديل عليه
        if ($this->user() && $this->user()->can('candidate.update_isfit')) {
            $rules['IsFit'] = ['boolean'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'Name' => 'اسم المترشح',
            'NationalNo' => 'الرقم الوطني',
            'image' => 'الصورة الشخصية',
        ];
    }
}
