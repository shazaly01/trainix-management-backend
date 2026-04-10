<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\JobRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrainingDashboardController extends Controller
{
    /**
     * جلب إحصائيات لوحة تحكم الدورات التدريبية
     */
    public function index(Request $request): JsonResponse
    {
        // 1. الأرقام السريعة (Quick Stats)
        $stats = [
            'total_candidates' => Candidate::count(),
            'total_job_requests' => JobRequest::count(), // يمثل إجمالي الدورات التدريبية
            'fit_candidates' => Candidate::where('IsFit', true)->count(),
            'unfit_candidates' => Candidate::where('IsFit', false)->count(),
        ];

        // 2. إحصائيات المخططات البيانية (Charts Analytics)

        // التوزيع حسب المقاس (لتجهيز الزي)
        $sizeChart = Candidate::select('Size', DB::raw('count(*) as count'))
            ->whereNotNull('Size')
            ->groupBy('Size')
            ->pluck('count', 'Size');

        // التوزيع حسب نوع التدريب
        $trainingTypeChart = Candidate::select('TrainingType', DB::raw('count(*) as count'))
            ->whereNotNull('TrainingType')
            ->groupBy('TrainingType')
            ->pluck('count', 'TrainingType');

        // التوزيع الجغرافي حسب السكن
        $residenceChart = Candidate::select('Residence', DB::raw('count(*) as count'))
            ->whereNotNull('Residence')
            ->groupBy('Residence')
            ->pluck('count', 'Residence');

        // 3. أحدث المتدربين المسجلين (Recent Candidates)
        $recentCandidates = Candidate::with('jobRequest')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($candidate) {
                return [
                    'id' => $candidate->id,
                    // تحويل الأرقام الطويلة لنصوص لحماية الدقة
                    'SequenceNo' => (string) $candidate->SequenceNo,
                    'NationalNo' => (string) $candidate->NationalNo,
                    'Name' => $candidate->Name,
                    'TrainingType' => $candidate->TrainingType ?? 'غير محدد',
                    'CourseName' => $candidate->jobRequest->RequiredMajor ?? 'غير محدد', // جلب اسم الدورة إذا توفر
                    'IsFit' => $candidate->IsFit,
                    'RegisteredAt' => $candidate->created_at->format('Y-m-d H:i'),
                ];
            });

        // 4. إرجاع الاستجابة المجمعة
        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'charts' => [
                    'sizes' => $sizeChart,
                    'training_types' => $trainingTypeChart,
                    'residences' => $residenceChart,
                ],
                'recent_candidates' => $recentCandidates,
            ]
        ], 200);
    }
}
