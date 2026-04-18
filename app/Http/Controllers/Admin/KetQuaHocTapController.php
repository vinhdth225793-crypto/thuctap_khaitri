<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KetQuaHocTap;
use App\Services\ExamResultReportDataService;
use App\Services\LearningResultFinalizationService;
use Illuminate\Http\Request;

class KetQuaHocTapController extends Controller
{
    public function __construct(
        private readonly ExamResultReportDataService $reportDataService,
        private readonly LearningResultFinalizationService $finalizationService,
    ) {
    }

    public function index(Request $request)
    {
        $courses = $this->reportDataService->courseOptions();

        return view('pages.admin.ket-qua.index', compact('courses'));
    }

    public function show(int $khoaHocId)
    {
        $report = $this->reportDataService->buildCourseReport($khoaHocId);

        return view('pages.admin.ket-qua.show', $report);
    }

    public function approve(Request $request, int $resultId)
    {
        $validated = $request->validate([
            'ghi_chu_duyet' => 'nullable|string|max:2000',
        ]);

        $result = KetQuaHocTap::findOrFail($resultId);
        $this->finalizationService->approveResult($result, $request->user(), $validated['ghi_chu_duyet'] ?? null);

        return back()->with('success', 'Da duyet ket qua va luu ho so.');
    }

    public function reject(Request $request, int $resultId)
    {
        $validated = $request->validate([
            'ghi_chu_duyet' => 'nullable|string|max:2000',
        ]);

        $result = KetQuaHocTap::findOrFail($resultId);
        $this->finalizationService->rejectResult($result, $request->user(), $validated['ghi_chu_duyet'] ?? null);

        return back()->with('success', 'Da tra ket qua ve cho giang vien chinh sua.');
    }
}
