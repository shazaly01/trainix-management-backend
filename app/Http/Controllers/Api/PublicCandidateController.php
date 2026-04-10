<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Http\Requests\StoreCandidatePublicRequest;
use App\Http\Requests\UpdateCandidatePublicRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PublicCandidateController extends Controller
{
    /**
     * مسار التقديم العام لدورة تدريبية
     */
    public function submitApplication(StoreCandidatePublicRequest $request): JsonResponse
    {
        $data = $request->validated();

        // 1. معالجة الصورة الشخصية
        if ($request->hasFile('image')) {
            // تخزين الصورة في مجلد candidates_images على قرص public
            $path = $request->file('image')->store('candidates_images', 'public');
            // حفظ المسار في حقل image_url الخاص بقاعدة البيانات
            $data['image_url'] = $path;
        }

        // 2. توليد رقم تحقق (إذا لم يكن الموديل يقوم بذلك تلقائياً)
        if (!isset($data['VerificationCode'])) {
            $data['VerificationCode'] = (string) rand(100000, 999999);
        }

        // 3. توليد رقم تسلسلي (SequenceNo) إذا لزم الأمر
        $data['SequenceNo'] = now()->format('ymd') . rand(100, 999);

        // حفظ البيانات
        $candidate = Candidate::create($data);

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
     * مسار جلب بيانات المتدرب (مع إضافة رابط الصورة الكامل)
     */
    public function getApplicationByVerification(Request $request): JsonResponse
    {
        $request->validate([
            'passport_no' => 'required_without:national_no|string',
            'national_no' => 'required_without:passport_no|numeric',
            'verification_code' => 'required|string',
        ]);

        $candidateQuery = Candidate::where('VerificationCode', $request->verification_code);

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

        // تحويل مسار الصورة لرابط كامل ليظهر في Vue
        if ($candidate->image_url) {
            $candidate->image_url = asset('storage/' . $candidate->image_url);
        }

        return response()->json([
            'status' => 'success',
            'data' => $candidate
        ]);
    }

    /**
     * مسار تحديث بيانات المتدرب (مع معالجة استبدال الصورة)
     */
    public function updateApplication(UpdateCandidatePublicRequest $request): JsonResponse
    {
        // البحث عن المترشح
        $candidate = Candidate::where('VerificationCode', $request->verification_code)->first();

        if (!$candidate) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات التحقق غير صحيحة، لا يمكنك التعديل.'
            ], 403);
        }

        $dataToUpdate = $request->validated();

        // معالجة تحديث الصورة
        if ($request->hasFile('image')) {
            // حذف الصورة القديمة من السيرفر لتوفير المساحة
            if ($candidate->image_url) {
                Storage::disk('public')->delete($candidate->image_url);
            }
            // رفع الصورة الجديدة
            $dataToUpdate['image_url'] = $request->file('image')->store('candidates_images', 'public');
        }

        // تحديث السجل في قاعدة البيانات
        $candidate->update($dataToUpdate);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث بيانات طلبك بنجاح.',
            'data' => $candidate
        ]);
    }
}
