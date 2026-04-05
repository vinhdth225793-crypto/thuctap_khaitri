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

    public function checkIn(int $lichHocId): RedirectResponse
    {
        $lichHoc = LichHoc::findOrFail($lichHocId);
        $actor = auth()->user();
        $giangVien = $actor?->giangVien;

        abort_if(!$actor || !$giangVien, 403);

        try {
            $this->teacherAttendanceService->checkIn($lichHoc, $giangVien, $actor);
        } catch (ValidationException $exception) {
            throw $exception;
        }

        return back()->with('success', 'Da ghi nhan check-in giang vien cho buoi hoc.');
    }

    public function checkOut(int $lichHocId): RedirectResponse
    {
        $lichHoc = LichHoc::findOrFail($lichHocId);
        $actor = auth()->user();
        $giangVien = $actor?->giangVien;

        abort_if(!$actor || !$giangVien, 403);

        try {
            $this->teacherAttendanceService->checkOut($lichHoc, $giangVien, $actor);
        } catch (ValidationException $exception) {
            throw $exception;
        }

        return back()->with('success', 'Da ghi nhan check-out giang vien cho buoi hoc.');
    }

    public function start(int $lichHocId): RedirectResponse
    {
        return $this->checkIn($lichHocId);
    }

    public function finish(int $lichHocId): RedirectResponse
    {
        return $this->checkOut($lichHocId);
    }
}
