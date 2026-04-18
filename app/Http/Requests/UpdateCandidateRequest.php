<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'SequenceNo' => [
                'nullable',
                'numeric',
                Rule::unique('candidates')->ignore($this->route('candidate'))
            ],
            'Name' => ['sometimes', 'required', 'string', 'max:255'],
            'BirthDate' => ['nullable', 'date'],
            'Qualification' => ['nullable', 'string', 'max:255'],
            'PassportNo' => ['nullable', 'string', 'max:50'],
            'PassportExpiry' => ['nullable', 'date'],
            'NationalNo' => [
                'nullable',
                'numeric',
                Rule::unique('candidates')->ignore($this->route('candidate'))
            ],
            'TrainingType' => ['required', 'in:internal,external'],
            'Phone' => ['nullable', 'string', 'max:50'],
            'Residence' => ['nullable', 'string', 'max:255'],
            'Size' => ['nullable', 'string', 'max:50'],
            'Notes' => ['nullable', 'string'],
            'BankName' => ['nullable', 'string', 'max:255'],
            'BankAccountNo' => ['nullable', 'string', 'max:50'],
            'ShoeSize' => ['nullable', 'numeric'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];

        // 👈 التعديل هنا: يتم قبول الحقل فقط إذا كان المستخدم يملك صلاحية التعديل عليه
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
