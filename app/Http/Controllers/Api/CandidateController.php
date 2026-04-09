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
use Illuminate\Http\Request;

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

public function index(Request $request): AnonymousResourceCollection
{
    // 1. نبدأ بإنشاء الاستعلام مع شحن الصور
    $query = Candidate::query()->with('image');

    // 2. فلتر البحث العام (الاسم، الرقم الوطني، الهاتف)
    $query->when($request->search, function ($q, $search) {
        $q->where(function ($sq) use ($search) {
            $sq->where('Name', 'like', "%{$search}%")
               ->orWhere('NationalNo', 'like', "%{$search}%")
               ->orWhere('Phone', 'like', "%{$search}%");
        });
    });

    // 3. فلتر السكن (بحث جزئي)
    $query->when($request->Residence, function ($q, $residence) {
        $q->where('Residence', 'like', "%{$residence}%");
    });

    // 4. فلتر المقاس (مطابقة تامة)
    $query->when($request->Size, function ($q, $size) {
        $q->where('Size', $size);
    });

    // 5. فلتر حالة اللياقة
    // نستخدم has لنتأكد أن القيمة أُرسلت فعلاً (لأنها قد تكون 0)
    if ($request->has('IsFit') && $request->IsFit !== '') {
        $query->where('IsFit', (bool)$request->IsFit);
    }

    // 6. الترتيب والجلب مع التصفح
    // نستخدم appends لضمان بقاء الفلاتر فعالة عند التنقل بين الصفحات
    $candidates = $query->latest()->paginate(15)->appends($request->query());

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
