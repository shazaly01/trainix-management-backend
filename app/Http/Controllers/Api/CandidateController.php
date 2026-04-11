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
     * عرض قائمة المترشحين (المعتمدين فقط - البيانات النهائية)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // استخدام scopeApproved لجلب البيانات المعتمدة فقط
        $query = Candidate::query()->approved()->with('image');

        // فلتر البحث العام (الاسم، الرقم الوطني، الهاتف)
        $query->when($request->search, function ($q, $search) {
            $q->where(function ($sq) use ($search) {
                $sq->where('Name', 'like', "%{$search}%")
                   ->orWhere('NationalNo', 'like', "%{$search}%")
                   ->orWhere('Phone', 'like', "%{$search}%");
            });
        });

        // فلتر السكن
        $query->when($request->Residence, function ($q, $residence) {
            $q->where('Residence', 'like', "%{$residence}%");
        });

        // فلتر المؤهل العلمي
        $query->when($request->Qualification, function ($q, $qualification) {
            $q->where('Qualification', 'like', "%{$qualification}%");
        });

        // فلتر المقاس
        $query->when($request->Size, function ($q, $size) {
            $q->where('Size', $size);
        });

        $query->when($request->ShoeSize, function ($q, $shoeSize) {
        $q->where('ShoeSize', $shoeSize);
    });

        // فلتر حالة اللياقة
        if ($request->has('IsFit') && $request->IsFit !== '') {
            $query->where('IsFit', (bool)$request->IsFit);
        }

        // فلتر نوع التدريب
        $query->when($request->TrainingType, function ($q, $type) {
            $q->where('TrainingType', $type);
        });

        // الترتيب والجلب
        $perPage = $request->get('per_page', 15);

        if ($perPage == -1) {
            $candidates = $query->latest()->get();
        } else {
            $candidates = $query->latest()->paginate($perPage)->appends($request->query());
        }

        return CandidateResource::collection($candidates);
    }

    /**
     * عرض قائمة الطلبات "تحت المراجعة" (التقديم الخارجي فقط)
     */
    public function pendingList(Request $request): AnonymousResourceCollection
    {
        // استخدام scopePending لجلب الطلبات التي لم يتم اعتمادها بعد
        $query = Candidate::query()->pending()->with('image');

        // يمكنك تطبيق نفس فلاتر البحث هنا إذا كنت تريد البحث داخل الطلبات المعلقة
        $query->when($request->search, function ($q, $search) {
            $q->where('Name', 'like', "%{$search}%");
        });

        $candidates = $query->latest()->paginate($request->get('per_page', 15));

        return CandidateResource::collection($candidates);
    }

    /**
     * اعتماد مترشح ونقله للبيانات النهائية
     */
    public function approve($id): JsonResponse
    {
        $candidate = Candidate::findOrFail($id);

        $candidate->update([
            'is_approved' => true
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم اعتماد المترشح بنجاح وظهوره في القوائم النهائية.'
        ], 200);
    }

    /**
     * إضافة مترشح جديد (إدخال يدوي داخلي - معتمد تلقائياً)
     */
    public function store(StoreCandidateRequest $request): CandidateResource
    {
        $data = $request->validated();
        $data['is_approved'] = true; // الإدخال المباشر من النظام يعتبر معتمداً

        $candidate = Candidate::create($data);

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

        $this->handleImageUpload($request, $candidate);

        return new CandidateResource($candidate->load('image'));
    }

    /**
     * حذف/أرشفة المترشح
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
     * دالة مساعدة لمعالجة رفع الصور الشخصية
     */
    private function handleImageUpload($request, Candidate $candidate): void
    {
        if ($request->hasFile('image')) {
            if ($candidate->image) {
                Storage::disk('local')->delete($candidate->image->file_path);
                $candidate->image()->delete();
            }

            $path = $request->file('image')->store('candidates/images', 'local');

            $candidate->image()->create([
                'name' => 'الصورة الشخصية - ' . $candidate->Name,
                'file_path' => $path,
                'DocumentType' => 'Profile Picture'
            ]);
        }
    }
}
