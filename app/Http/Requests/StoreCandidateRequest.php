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
        return [
            // الأرقام الطويلة أضفنا لها numeric
            'SequenceNo' => ['nullable', 'numeric', 'unique:candidates,SequenceNo'],
            'Name' => ['required', 'string', 'max:255'],
            'BirthDate' => ['nullable', 'date'],
            'Qualification' => ['nullable', 'string', 'max:255'],
            'PassportNo' => ['nullable', 'string', 'max:50'],
            'PassportExpiry' => ['nullable', 'date'],
            'NationalNo' => ['nullable', 'numeric', 'unique:candidates,NationalNo'],
            'Phone' => ['nullable', 'string', 'max:50'],
            'Residence' => ['nullable', 'string', 'max:255'],
            'Size' => ['nullable', 'string', 'max:50'],
            'IsFit' => ['boolean'],
            'Notes' => ['nullable', 'string'],

            // التحقق من الصورة المرفقة (اختيارية، ويجب أن تكون صورة بحجم أقصى 5 ميجا)
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
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
