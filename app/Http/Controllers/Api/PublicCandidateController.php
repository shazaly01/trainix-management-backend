<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\JobRequest;
use App\Http\Requests\StoreCandidatePublicRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UpdateCandidatePublicRequest;

class PublicCandidateController extends Controller
{
    /**
     * مسار التقديم العام لدورة تدريبية
     */
    public function submitApplication(StoreCandidatePublicRequest $request): JsonResponse
    {
        // حفظ البيانات في قاعدة البيانات
        $candidate = Candidate::create($request->validated());

        // نرجع استجابة ناجحة تحتوي على رسالة واضحة ورقم التحقق للمستخدم
        return response()->json([
            'status' => 'success',
            'message' => 'تم استلام طلبك بنجاح. يرجى الاحتفاظ برقم التحقق لتتمكن من متابعة أو تعديل طلبك لاحقاً.',
            'data' => [
                'verification_code' => $candidate->VerificationCode,
                'name' => $candidate->Name
            ]
        ], 201);
    }

    /**
     * مسار جلب بيانات المتدرب للمتابعة/التعديل باستخدام رقم الجواز ورقم التحقق
     */
    public function getApplicationByVerification(Request $request): JsonResponse
    {
        // التحقق من المدخلات
        $request->validate([
            'passport_no' => 'required_without:national_no|string',
            'national_no' => 'required_without:passport_no|numeric',
            'verification_code' => 'required|string',
        ]);

        // البحث عن المترشح
        $candidateQuery = Candidate::where('VerificationCode', $request->verification_code);

        // البحث إما برقم الجواز أو الرقم الوطني
        if ($request->filled('passport_no')) {
            $candidateQuery->where('PassportNo', $request->passport_no);
        } elseif ($request->filled('national_no')) {
            $candidateQuery->where('NationalNo', $request->national_no);
        }

        $candidate = $candidateQuery->with('jobRequest')->first();

        if (!$candidate) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات التحقق غير صحيحة، أو لا يوجد طلب بهذا الرقم.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $candidate
        ]);
    }



    /**
     * مسار تحديث بيانات المتدرب باستخدام رقم التحقق
     */
    public function updateApplication(UpdateCandidatePublicRequest $request): JsonResponse
    {
        // البحث عن المترشح للتحقق من هويته
        $candidateQuery = Candidate::where('VerificationCode', $request->verification_code);

        if ($request->filled('passport_no')) {
            $candidateQuery->where('PassportNo', $request->passport_no);
        } elseif ($request->filled('NationalNo')) {
            $candidateQuery->where('NationalNo', $request->NationalNo);
        }

        $candidate = $candidateQuery->first();

        if (!$candidate) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات التحقق غير صحيحة، لا يمكنك التعديل.'
            ], 403); // 403 Forbidden
        }

        // استبعاد حقول التحقق من البيانات التي سيتم تحديثها
        $dataToUpdate = $request->except(['verification_code', 'passport_no', 'NationalNo']);

        // تحديث البيانات
        $candidate->update($dataToUpdate);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث بيانات طلبك بنجاح.',
            'data' => $candidate
        ]);
    }
}
