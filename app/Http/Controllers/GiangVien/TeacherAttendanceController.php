<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\LichHoc;
use App\Services\TeacherAttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class TeacherAttendanceController extends Controller
{
    public function __construct(
        private readonly TeacherAttendanceService $teacherAttendanceService,
    ) {
    }

    public function start(int $lichHocId): RedirectResponse
    {
        $lichHoc = LichHoc::findOrFail($lichHocId);
        $actor = auth()->user();
        $giangVien = $actor?->giangVien;

        abort_if(!$actor || !$giangVien, 403);

        try {
            $this->teacherAttendanceService->startTeaching($lichHoc, $giangVien, $actor);
        } catch (ValidationException $exception) {
            throw $exception;
        }

        return back()->with('success', 'Đã ghi nhận giảng viên bắt đầu buổi học online.');
    }

    public function finish(int $lichHocId): RedirectResponse
    {
        $lichHoc = LichHoc::findOrFail($lichHocId);
        $actor = auth()->user();
        $giangVien = $actor?->giangVien;

        abort_if(!$actor || !$giangVien, 403);

        try {
            $this->teacherAttendanceService->finishTeaching($lichHoc, $giangVien, $actor);
        } catch (ValidationException $exception) {
            throw $exception;
        }

        return back()->with('success', 'Đã ghi nhận giảng viên kết thúc buổi học online.');
    }
}
