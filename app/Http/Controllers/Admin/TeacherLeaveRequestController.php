<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewTeacherLeaveRequest;
use App\Models\GiangVien;
use App\Models\GiangVienDonXinNghi;
use App\Services\Scheduling\TeacherLeaveRequestService;
use Illuminate\Http\Request;

class TeacherLeaveRequestController extends Controller
{
    public function __construct(
        private readonly TeacherLeaveRequestService $leaveRequestService,
    ) {
    }

    public function index(Request $request)
    {
        $query = GiangVienDonXinNghi::query()
            ->with(['giangVien.nguoiDung', 'khoaHoc', 'moduleHoc', 'lichHoc', 'nguoiDuyet'])
            ->orderByDesc('created_at');

        if (filled($request->trang_thai)) {
            $query->where('trang_thai', $request->string('trang_thai'));
        }

        if (filled($request->giang_vien_id)) {
            $query->where('giang_vien_id', (int) $request->input('giang_vien_id'));
        }

        $leaveRequests = $query->paginate(12)->withQueryString();
        $teachers = GiangVien::with('nguoiDung')->get();

        return view('pages.admin.giang-vien-don-xin-nghi.index', [
            'leaveRequests' => $leaveRequests,
            'teachers' => $teachers,
            'filters' => $request->only(['trang_thai', 'giang_vien_id']),
        ]);
    }

    public function show(int $id)
    {
        $leaveRequest = GiangVienDonXinNghi::with([
            'giangVien.nguoiDung',
            'khoaHoc',
            'moduleHoc',
            'lichHoc.khoaHoc',
            'lichHoc.moduleHoc',
            'nguoiDuyet',
        ])->findOrFail($id);

        return view('pages.admin.giang-vien-don-xin-nghi.show', [
            'leaveRequest' => $leaveRequest,
        ]);
    }

    public function approve(ReviewTeacherLeaveRequest $request, int $id)
    {
        $leaveRequest = GiangVienDonXinNghi::findOrFail($id);
        $this->leaveRequestService->approve($leaveRequest, auth()->user(), $request->validated('ghi_chu_phan_hoi'));

        return redirect()
            ->route('admin.giang-vien-don-xin-nghi.show', $leaveRequest->id)
            ->with('success', 'Da duyet don xin nghi. Buoi hoc lien quan van can admin doi lich hoac thay giang vien neu can.');
    }

    public function reject(ReviewTeacherLeaveRequest $request, int $id)
    {
        $leaveRequest = GiangVienDonXinNghi::findOrFail($id);
        $this->leaveRequestService->reject($leaveRequest, auth()->user(), $request->validated('ghi_chu_phan_hoi'));

        return redirect()
            ->route('admin.giang-vien-don-xin-nghi.show', $leaveRequest->id)
            ->with('success', 'Da tu choi don xin nghi.');
    }
}
