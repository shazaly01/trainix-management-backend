<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Http\Requests\StoreCandidatePublicRequest;
use App\Http\Requests\UpdateCandidatePublicRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Api\CandidateResource;

class PublicCandidateController extends Controller
{
    /**
     * مسار التقديم العام - إضافة مترشح جديد مع صورته
     */
   public function submitApplication(StoreCandidatePublicRequest $request): JsonResponse
{
    // جلب البيانات التي تم التحقق منها
    $validatedData = $request->validated();

    // إجبار الحالة على "غير معتمد" مهما كانت البيانات القادمة من الفرونت إند
    $validatedData['is_approved'] = false;

    // إنشاء السجل
    $candidate = Candidate::create($validatedData);

    // معالجة رفع الصورة (باستخدام الدالة المساعدة الموجودة لديك)
    $this->handlePublicImageUpload($request, $candidate);

    return response()->json([
        'status' => 'success',
        'message' => 'تم استلام طلبك بنجاح. وهو الآن قيد المراجعة والتدقيق.',
        'data' => [
            'verification_code' => $candidate->VerificationCode,
            'name' => $candidate->Name
        ]
    ], 201);
}

    /**
     * جلب بيانات المترشح (مع شحن علاقة الصورة)
     */
    public function getApplicationByVerification(Request $request): JsonResponse
    {
        $request->validate([
            'passport_no' => 'required_without:national_no|string',
            'national_no' => 'required_without:passport_no|numeric',
            'verification_code' => 'required|string',
        ]);

        $query = Candidate::where('VerificationCode', $request->verification_code);

        if ($request->filled('passport_no')) {
            $query->where('PassportNo', $request->passport_no);
        } else {
            $query->where('NationalNo', $request->national_no);
        }

        // شحن علاقة الصورة (image) كما في الـ Index الخاص بك
        $candidate = $query->with('image')->first();

        if (!$candidate) {
            return response()->json(['status' => 'error', 'message' => 'البيانات غير صحيحة.'], 404);
        }

        // تجهيز رابط الصورة للـ Vue (إذا كانت العلاقة موجودة)
        if ($candidate->image) {
            $candidate->image_url = asset('storage/' . $candidate->image->file_path);
        }

       return response()->json([
        'status' => 'success',
        'data' => new CandidateResource($candidate)
    ]);
    }

    /**
     * تحديث بيانات المترشح وصورته
     */
    public function updateApplication(UpdateCandidatePublicRequest $request): JsonResponse
    {
        $candidate = Candidate::where('VerificationCode', $request->verification_code)->first();

        if (!$candidate) {
            return response()->json(['status' => 'error', 'message' => 'لا يمكن التعديل.'], 403);
        }

        // تحديث البيانات الأساسية
        $candidate->update($request->validated());

        // تحديث الصورة (حذف القديمة ورفع الجديدة)
       // $this->handlePublicImageUpload($request, $candidate);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث بياناتك بنجاح.',
            'data' => $candidate->load('image')
        ]);
    }

    /**
     * دالة مساعدة مطابقة تماماً للسيستم الداخلي الخاص بك
     */
    private function handlePublicImageUpload($request, Candidate $candidate): void
    {
        if ($request->hasFile('image')) {
            // 1. حذف الصورة القديمة من التخزين وقاعدة البيانات (عبر العلاقة)
            if ($candidate->image) {
                Storage::disk('public')->delete($candidate->image->file_path);
                $candidate->image()->delete();
            }

            // 2. رفع الصورة الجديدة في نفس المسار المعتمد عندك
            $path = $request->file('image')->store('candidates/images', 'public');

            // 3. إنشاء السجل في جدول الصور المرتبط
            $candidate->image()->create([
                'name' => 'الصورة الشخصية - ' . $candidate->Name,
                'file_path' => $path,
                'DocumentType' => 'Profile Picture'
            ]);
        }
    }
}
