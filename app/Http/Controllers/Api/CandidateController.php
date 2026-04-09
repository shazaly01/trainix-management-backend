<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Http\Requests\StoreCandidateRequest;
use App\Http\Requests\UpdateCandidateRequest;
use App\Http\Resources\Api\CandidateResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class CandidateController extends Controller
{
    /**
     * ربط المتحكم بصلاحيات CandidatePolicy
     */
    public function __construct()
    {
        $this->authorizeResource(Candidate::class, 'candidate');
    }

    /**
     * عرض قائمة المترشحين
     */
    public function index(): AnonymousResourceCollection
    {
        // جلب المترشحين مع صورهم إن وجدت، مع الترتيب والتصفح
        $candidates = Candidate::with('image')->latest()->paginate(15);

        return CandidateResource::collection($candidates);
    }

    /**
     * إضافة مترشح جديد
     */
    public function store(StoreCandidateRequest $request): CandidateResource
    {

        $candidate = Candidate::create($request->validated());

        // معالجة الصورة المرفقة إن وجدت
        $this->handleImageUpload($request, $candidate);

        return new CandidateResource($candidate->load('image'));
    }

    /**
     * عرض تفاصيل مترشح محدد
     */
    public function show(Candidate $candidate): CandidateResource
    {
        return new CandidateResource($candidate->load('image'));
    }

    /**
     * تحديث بيانات المترشح
     */
    public function update(UpdateCandidateRequest $request, Candidate $candidate): CandidateResource
    {
        $candidate->update($request->validated());

        // معالجة تحديث الصورة (استبدالها إن تم رفع واحدة جديدة)
        $this->handleImageUpload($request, $candidate);

        return new CandidateResource($candidate->load('image'));
    }

    /**
     * نقل المترشح إلى الأرشيف (Soft Delete)
     */
    public function destroy(Candidate $candidate): JsonResponse
    {
        $candidate->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم نقل المترشح إلى الأرشيف بنجاح'
        ], 200);
    }

    /**
     * دالة مساعدة خاصة لمعالجة رفع الصورة الشخصية
     */
    private function handleImageUpload($request, Candidate $candidate): void
    {
        if ($request->hasFile('image')) {
            // 1. حذف الصورة القديمة من التخزين وقاعدة البيانات إذا كانت موجودة
            if ($candidate->image) {
                Storage::disk('public')->delete($candidate->image->file_path);
                $candidate->image()->delete();
            }

            // 2. رفع الصورة الجديدة في مجلد candidates/images
            $path = $request->file('image')->store('candidates/images', 'public');

            // 3. إنشاء سجل المستند وربطه بالمرشح (علاقة Polymorphic)
            $candidate->image()->create([
                'name' => 'الصورة الشخصية - ' . $candidate->Name,
                'file_path' => $path,
                'DocumentType' => 'Profile Picture'
            ]);
        }
    }
}
