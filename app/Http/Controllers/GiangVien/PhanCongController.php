<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\DiemDanhGiangVien;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\PhanCongModuleGiangVien;
use App\Models\YeuCauHocVien;
use App\Services\TeacherAttendanceService;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhanCongController extends Controller
{
    public function __construct(
        private readonly TeacherAttendanceService $teacherAttendanceService,
    ) {
    }

    /**
     * Hiển thị lộ trình giảng dạy gom nhóm theo Khóa học
     */
    public function index()
    {
        $giangVien = auth()->user()->giangVien;
        if (!$giangVien) {
            return redirect()->route('home')
                ->with('error', 'Tài khoản chưa được liên kết với giảng viên.');
        }

        $khoaHocs = KhoaHoc::with(['nhomNganh', 'moduleHocs' => function ($q) use ($giangVien) {
                $q->whereHas('phanCongGiangViens', function ($q2) use ($giangVien) {
                    $q2->where('giang_vien_id', $giangVien->id);
                })->with(['phanCongGiangViens' => function ($q2) use ($giangVien) {
                    $q2->where('giang_vien_id', $giangVien->id);
                }, 'lichHocs']);
            }])
            ->whereHas('moduleHocs.phanCongGiangViens', function ($q) use ($giangVien) {
                $q->where('giang_vien_id', $giangVien->id);
            })
            ->orderBy('id', 'desc');

        $khoaHocs = $khoaHocs->get();

        // 1. Khóa học có module chưa xác nhận
        $khoaHocsChuaNhan = $khoaHocs->filter(function ($khoaHoc) {
            return $khoaHoc->moduleHocs->contains(function ($module) {
                $pc = $module->phanCongGiangViens->first();
                return $pc && $pc->trang_thai === 'cho_xac_nhan';
            });
        });

        // 2. Khóa học đã nhận dạy và đã hoàn thành (tiến độ 100%)
        $khoaHocsHoanThanh = $khoaHocs->filter(function ($khoaHoc) {
            // Không nằm trong nhóm chưa nhận
            $daXacNhanHet = !$khoaHoc->moduleHocs->contains(function ($module) {
                $pc = $module->phanCongGiangViens->first();
                return $pc && $pc->trang_thai === 'cho_xac_nhan';
            });
            return $daXacNhanHet && (int)$khoaHoc->tien_do_hoc_tap === 100;
        });

        // 3. Khóa học đã nhận dạy và đang trong quá trình (tiến độ < 100%)
        $khoaHocsDaNhan = $khoaHocs->filter(function ($khoaHoc) use ($khoaHocsChuaNhan, $khoaHocsHoanThanh) {
            return !$khoaHocsChuaNhan->contains('id', $khoaHoc->id) && 
                   !$khoaHocsHoanThanh->contains('id', $khoaHoc->id);
        });

        $phanCongChoXacNhan = PhanCongModuleGiangVien::where('giang_vien_id', $giangVien->id)
            ->where('trang_thai', 'cho_xac_nhan')
            ->count();

        return view('pages.giang-vien.phan-cong.index', compact(
            'khoaHocsChuaNhan', 
            'khoaHocsDaNhan', 
            'khoaHocsHoanThanh',
            'phanCongChoXacNhan'
        ));
    }

    public function show($id)
    {
        $giangVien = auth()->user()->giangVien;

        $phanCong = $this->resolveTeacherAssignment($giangVien->id, (int) $id);

        $khoaHoc = $phanCong->khoaHoc;

        $lichHocIds = LichHoc::where('khoa_hoc_id', $khoaHoc->id)->pluck('id');

        $lichDays = LichHoc::with([
                'taiNguyen',
                'baiKiemTras',
                'baiGiangs.phongHocLive',
                'diemDanhs',
                'giangVien.nguoiDung',
                'moduleHoc.phanCongGiangViens.giangVien.nguoiDung',
                'teacherAttendanceLogs' => function ($query) use ($giangVien) {
                    $query->where('giang_vien_id', $giangVien->id)
                        ->with('giangVien.nguoiDung');
                },
            ])
            ->where('module_hoc_id', $phanCong->module_hoc_id)
            ->orderBy('ngay_hoc')
            ->get();

        $phanCong->moduleHoc->setRelation('lichHocs', $lichDays);
        $khoaHoc->setRelation(
            'moduleHocs',
            $khoaHoc->moduleHocs->map(function ($module) use ($phanCong, $lichDays) {
                if ($module->id === $phanCong->module_hoc_id) {
                    $module->setRelation('lichHocs', $lichDays);
                }

                return $module;
            })
        );

        $timelineItems = $lichDays->map(function (LichHoc $lich) use ($phanCong) {
            $teacherAttendance = $lich->teacher_attendance_log;
            $teacherLiveLecture = $lich->teacher_live_lecture;
            $teacherLiveRoom = $lich->teacher_live_room;
            $lectures = $lich->baiGiangs->sortBy('thu_tu_hien_thi')->values();
            $canManageSession = $phanCong->trang_thai === 'da_nhan';
            $sessionStatus = $this->buildSessionStatus($lich, $canManageSession);
            $attendanceStatus = $this->buildTeacherAttendanceStatus($teacherAttendance, $sessionStatus, $canManageSession);
            $studentAttendanceStatus = $this->buildStudentAttendanceStatus(
                $lich,
                $phanCong->khoaHoc->hocVienKhoaHocs->count(),
                $sessionStatus,
                $canManageSession
            );

            return [
                'lich' => $lich,
                'teacherAttendance' => $teacherAttendance,
                'teacherLiveLecture' => $teacherLiveLecture,
                'teacherLiveRoom' => $teacherLiveRoom,
                'sessionStatus' => $sessionStatus,
                'teacherAttendanceStatus' => $attendanceStatus,
                'studentAttendanceStatus' => $studentAttendanceStatus,
                'teachingStatus' => $this->buildTeachingStatus($lich, $teacherLiveRoom, $canManageSession, $sessionStatus),
                'attendanceStatus' => [
                    'label' => $teacherAttendance?->trang_thai_label ?? 'Chưa điểm danh',
                    'color' => $teacherAttendance?->trang_thai_color ?? 'secondary',
                    'can_check_in' => $canManageSession && !$sessionStatus['is_locked'] && !$teacherAttendance?->has_checked_in,
                    'can_check_out' => $canManageSession && ($teacherAttendance?->has_checked_in ?? false) && !$teacherAttendance?->has_checked_out,
                ],
                'resourceCount' => $lich->taiNguyen->count(),
                'examCount' => $lich->baiKiemTras->count(),
                'contentSummary' => [
                    'resource_count' => $lich->taiNguyen->count(),
                    'lecture_count' => $lectures->count(),
                    'exam_count' => $lich->baiKiemTras->count(),
                    'has_report' => filled($lich->bao_cao_giang_vien),
                ],
                'lecturePreview' => $lectures->take(3)->map(function ($lecture) {
                    $typeLabel = match ($lecture->loai_bai_giang) {
                        'live' => 'Live',
                        'video' => 'Video',
                        'tai_lieu' => 'Tài liệu',
                        'bai_doc' => 'Bài đọc',
                        'bai_tap' => 'Bài tập',
                        'hon_hop' => 'Tổng hợp',
                        default => 'Bài giảng',
                    };

                    return [
                        'id' => $lecture->id,
                        'title' => $lecture->tieu_de,
                        'type_label' => $typeLabel,
                        'is_live' => $lecture->isLive(),
                        'has_internal_room' => (bool) $lecture->phongHocLive,
                    ];
                })->values(),
            ];
        });

        return view('pages.giang-vien.phan-cong.show', compact('phanCong', 'khoaHoc', 'lichDays', 'lichHocIds', 'timelineItems'));
    }

    /**
     * Phase 3: Cập nhật link học Online
     */
    public function startTeachingSession($id)
    {
        $giangVien = auth()->user()->giangVien;

        if (!$giangVien) {
            return redirect()->route('home')
                ->with('error', 'Tài khoản chưa được liên kết với giảng viên.');
        }

        [$lichHoc, $canManage] = $this->resolveTeacherScheduleContext($giangVien->id, (int) $id);

        if (!$canManage) {
            return back()->with('error', 'Bạn không được phân công giảng dạy buổi học này.');
        }

        $sessionStatus = $this->buildSessionStatus($lichHoc, true);

        if (!$sessionStatus['can_start']) {
            return back()->with('error', $this->resolveTeachingSessionGuardMessage($sessionStatus, 'start'));
        }

        DB::transaction(function () use ($lichHoc, $giangVien) {
            $lichHoc->forceFill([
                'trang_thai' => 'dang_hoc',
            ])->save();

            $this->teacherAttendanceService->ensureCheckIn(
                $lichHoc->fresh(),
                $giangVien,
                auth()->user(),
                [
                    'note' => 'Tu dong check-in khi giang vien bam nut bat dau buoi hoc.',
                ]
            );
        });

        return back()->with('success', 'Đã bắt đầu buổi học và chuyển sang trạng thái đang diễn ra.');
    }

    public function finishTeachingSession($id)
    {
        $giangVien = auth()->user()->giangVien;

        if (!$giangVien) {
            return redirect()->route('home')
                ->with('error', 'Tài khoản chưa được liên kết với giảng viên.');
        }

        [$lichHoc, $canManage] = $this->resolveTeacherScheduleContext($giangVien->id, (int) $id);

        if (!$canManage) {
            return back()->with('error', 'Bạn không được phân công giảng dạy buổi học này.');
        }

        $sessionStatus = $this->buildSessionStatus($lichHoc, true);

        if (!$sessionStatus['can_finish']) {
            return back()->with('error', $this->resolveTeachingSessionGuardMessage($sessionStatus, 'finish'));
        }

        DB::transaction(function () use ($lichHoc, $giangVien) {
            $this->teacherAttendanceService->ensureCheckOut(
                $lichHoc,
                $giangVien,
                auth()->user(),
                [
                    'note' => 'Tu dong check-out khi giang vien bam nut ket thuc buoi hoc.',
                ]
            );

            $lichHoc->forceFill([
                'trang_thai' => 'hoan_thanh',
            ])->save();
        });

        return back()->with('success', 'Đã kết thúc buổi học và đánh dấu buổi học hoàn tất.');
    }

    public function updateLinkOnline(Request $request, $id)
    {
        $giangVien = auth()->user()->giangVien;
        $lichHoc = LichHoc::findOrFail($id);

        $isAssigned = PhanCongModuleGiangVien::where('module_hoc_id', $lichHoc->module_hoc_id)
            ->where('giang_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->exists();

        if (!$isAssigned) {
            return back()->with('error', 'Bạn không có quyền cập nhật lịch dạy này.');
        }

        $request->validate([
            'hinh_thuc' => 'required|in:truc_tiep,online',
            'nen_tang' => 'required_if:hinh_thuc,online|nullable|string',
            'link_online' => 'required_if:hinh_thuc,online|nullable|url',
            'meeting_id' => 'nullable|string',
            'mat_khau_cuoc_hop' => 'nullable|string',
            'phong_hoc' => 'required_if:hinh_thuc,truc_tiep|nullable|string',
        ]);

        $lichHoc->update($request->only([
            'hinh_thuc', 'nen_tang', 'link_online', 'meeting_id', 'mat_khau_cuoc_hop', 'phong_hoc',
        ]));

        return back()->with('success', 'Đã cập nhật thông tin buổi học thành công.');
    }

    /**
     * Phase 6: Giảng viên gửi yêu cầu thay đổi học viên
     */
    public function guiYeuCauHocVien(Request $request, $khoaHocId)
    {
        $giangVien = auth()->user()->giangVien;
        $khoaHoc = KhoaHoc::findOrFail($khoaHocId);

        $duocPhanCong = PhanCongModuleGiangVien::query()
            ->where('giang_vien_id', $giangVien->id)
            ->where('khoa_hoc_id', $khoaHoc->id)
            ->where('trang_thai', 'da_nhan')
            ->exists();

        if (!$duocPhanCong) {
            return back()->with('error', 'Bạn không được phân công giảng dạy khóa học này.');
        }

        $request->validate([
            'loai_yeu_cau' => 'required|in:them,xoa,sua',
            'ly_do' => 'required|string|max:1000',
            'email_hoc_vien' => 'required_if:loai_yeu_cau,them|nullable|email',
            'ten_hoc_vien' => 'required_if:loai_yeu_cau,them|nullable|string|max:255',
            'hoc_vien_id' => 'required_if:loai_yeu_cau,xoa,sua|nullable|exists:nguoi_dung,ma_nguoi_dung',
        ]);

        $duLieu = [
            'loai' => $request->loai_yeu_cau,
            'email' => $request->email_hoc_vien,
            'ten' => $request->ten_hoc_vien,
            'id' => $request->hoc_vien_id,
        ];

        YeuCauHocVien::create([
            'khoa_hoc_id' => $khoaHocId,
            'giang_vien_id' => $giangVien->id,
            'loai_yeu_cau' => $request->loai_yeu_cau,
            'du_lieu_yeu_cau' => $duLieu,
            'ly_do' => $request->ly_do,
            'trang_thai' => 'cho_duyet',
        ]);

        return back()->with('success', 'Yêu cầu của bạn đã được gửi đến ban quản trị để xem xét.');
    }

    public function xacNhan(Request $request, $id)
    {
        $giangVien = auth()->user()->giangVien;
        $phanCong = PhanCongModuleGiangVien::where('id', $id)
            ->where('giang_vien_id', $giangVien->id)
            ->firstOrFail();

        if ($phanCong->trang_thai !== 'cho_xac_nhan') {
            return back()->with('error', 'Phân công này đã được xử lý hoặc không còn khả dụng.');
        }

        $validated = $request->validate([
            'hanh_dong' => 'required|in:da_nhan,tu_choi',
            'ghi_chu' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $phanCong->update([
                'trang_thai' => $validated['hanh_dong'],
                'ghi_chu' => $validated['ghi_chu'] ?? $phanCong->ghi_chu,
            ]);

            if ($validated['hanh_dong'] === 'da_nhan') {
                $khoaHoc = $phanCong->khoaHoc;
                if ($khoaHoc->isFullyAssigned()) {
                    $khoaHoc->update(['trang_thai_van_hanh' => 'san_sang']);
                    ThongBaoService::guiSanSangChoAdmin($khoaHoc);
                }
            }

            DB::commit();

            $msg = $validated['hanh_dong'] === 'da_nhan'
                ? 'Tuyệt vời! Bạn đã xác nhận nhận dạy bài này.'
                : 'Đã gửi phản hồi từ chối bài dạy đến hệ thống.';

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    private function resolveTeacherAssignment(int $teacherId, int $identifier): PhanCongModuleGiangVien
    {
        $relations = [
            'khoaHoc.nhomNganh',
            'moduleHoc',
            'khoaHoc.hocVienKhoaHocs.hocVien' => function ($query) {
                $query->with(['diemDanhs']);
            },
        ];

        $baseQuery = PhanCongModuleGiangVien::with($relations)
            ->where('giang_vien_id', $teacherId);

        $directAssignment = (clone $baseQuery)->find($identifier);
        if ($directAssignment) {
            return $directAssignment;
        }

        $statusPriority = "CASE WHEN trang_thai = 'da_nhan' THEN 0 WHEN trang_thai = 'cho_xac_nhan' THEN 1 ELSE 2 END";

        $courseAssignment = (clone $baseQuery)
            ->where('khoa_hoc_id', $identifier)
            ->orderByRaw($statusPriority)
            ->orderByDesc('id')
            ->first();

        if ($courseAssignment) {
            return $courseAssignment;
        }

        $moduleAssignment = (clone $baseQuery)
            ->where('module_hoc_id', $identifier)
            ->orderByRaw($statusPriority)
            ->orderByDesc('id')
            ->first();

        abort_if(!$moduleAssignment, 404);

        return $moduleAssignment;
    }

    private function resolveTeacherScheduleContext(int $teacherId, int $scheduleId): array
    {
        $lichHoc = LichHoc::findOrFail($scheduleId);
        $statusPriority = "CASE WHEN trang_thai = 'da_nhan' THEN 0 WHEN trang_thai = 'cho_xac_nhan' THEN 1 ELSE 2 END";

        $phanCong = PhanCongModuleGiangVien::query()
            ->where('khoa_hoc_id', $lichHoc->khoa_hoc_id)
            ->where('module_hoc_id', $lichHoc->module_hoc_id)
            ->where('giang_vien_id', $teacherId)
            ->orderByRaw($statusPriority)
            ->orderByDesc('id')
            ->first();

        $matchesDirectTeacher = $lichHoc->giang_vien_id !== null
            && (int) $lichHoc->giang_vien_id === $teacherId;

        return [$lichHoc, $matchesDirectTeacher || $phanCong?->trang_thai === 'da_nhan'];
    }

    private function buildSessionStatus(LichHoc $lichHoc, bool $canManage): array
    {
        $status = $lichHoc->teaching_session_status;
        $isLocked = in_array($status, ['da_ket_thuc', 'da_huy'], true);

        return [
            'value' => $status,
            'label' => $lichHoc->teaching_session_status_label,
            'color' => $lichHoc->teaching_session_status_color,
            'can_start' => $canManage && $lichHoc->can_start_teaching_session,
            'can_finish' => $canManage && $lichHoc->can_finish_teaching_session,
            'is_active' => $status === 'dang_dien_ra',
            'is_locked' => $isLocked,
            'is_cancelled' => $status === 'da_huy',
            'hint' => match ($status) {
                'dang_dien_ra' => 'Buổi học đang mở để tiếp tục điểm danh, điều hành lớp và xử lý nội dung buổi học.',
                'da_ket_thuc' => 'Buổi học đã được đánh dấu hoàn tất. Hệ thống sẽ ẩn các thao tác mở lại không hợp lệ.',
                'da_huy' => 'Buổi học này đã bị hủy, chỉ giữ lại thông tin để theo dõi lịch sử.',
                default => 'Buổi học đang ở trạng thái chờ. Giảng viên có thể bắt đầu buổi học khi sẵn sàng lên lớp.',
            },
        ];
    }

    private function buildTeacherAttendanceStatus(?DiemDanhGiangVien $attendance, array $sessionStatus, bool $canManage): array
    {
        $hasCheckedIn = $attendance?->has_checked_in ?? false;
        $hasCheckedOut = $attendance?->has_checked_out ?? false;

        $value = match (true) {
            $hasCheckedOut && $sessionStatus['is_locked'] => DiemDanhGiangVien::STATUS_HOAN_THANH,
            $hasCheckedOut => DiemDanhGiangVien::STATUS_DA_CHECKOUT,
            $hasCheckedIn => DiemDanhGiangVien::STATUS_DA_CHECKIN,
            default => DiemDanhGiangVien::STATUS_CHUA_DIEM_DANH,
        };

        return [
            'value' => $value,
            'label' => match ($value) {
                DiemDanhGiangVien::STATUS_DA_CHECKIN => 'Đã check-in',
                DiemDanhGiangVien::STATUS_DA_CHECKOUT => 'Đã check-out',
                DiemDanhGiangVien::STATUS_HOAN_THANH => 'Hoàn thành',
                default => 'Chưa điểm danh',
            },
            'color' => match ($value) {
                DiemDanhGiangVien::STATUS_DA_CHECKIN => 'warning',
                DiemDanhGiangVien::STATUS_DA_CHECKOUT => 'primary',
                DiemDanhGiangVien::STATUS_HOAN_THANH => 'success',
                default => 'secondary',
            },
            'check_in_time' => $attendance?->check_in_at,
            'check_out_time' => $attendance?->check_out_at,
            'duration_minutes' => $attendance?->tong_thoi_luong_day_phut,
            'can_check_in' => $canManage && !$sessionStatus['is_locked'] && !$hasCheckedIn,
            'can_check_out' => $canManage && $hasCheckedIn && !$hasCheckedOut,
            'status_hint' => match ($value) {
                DiemDanhGiangVien::STATUS_DA_CHECKIN => 'Giảng viên đã check-in. Có thể check-out khi kết thúc phần giảng dạy của buổi học.',
                DiemDanhGiangVien::STATUS_DA_CHECKOUT => 'Giờ vào và giờ ra đã được ghi nhận. Có thể tiếp tục kết thúc buổi học nếu chưa chốt phiên.',
                DiemDanhGiangVien::STATUS_HOAN_THANH => 'Attendance giảng viên đã hoàn tất và khớp với trạng thái kết thúc của buổi học.',
                default => 'Giảng viên chưa check-in cho buổi học này. Có thể thao tác trực tiếp ngay trên card buổi học.',
            },
            'log_hint' => $attendance?->status_hint,
            'is_completed' => in_array($value, [
                DiemDanhGiangVien::STATUS_DA_CHECKOUT,
                DiemDanhGiangVien::STATUS_HOAN_THANH,
            ], true),
        ];
    }

    private function buildStudentAttendanceStatus(LichHoc $lichHoc, int $totalStudents, array $sessionStatus, bool $canManage): array
    {
        $attendances = $lichHoc->diemDanhs;
        $markedStudents = $attendances->count();
        $presentCount = $attendances->where('trang_thai', 'co_mat')->count();
        $lateCount = $attendances->where('trang_thai', 'vao_tre')->count();
        $absentCount = $attendances->where('trang_thai', 'vang_mat')->count();
        $excusedCount = $attendances->where('trang_thai', 'co_phep')->count();
        $isFinalized = $lichHoc->trang_thai_bao_cao === 'da_bao_cao';

        return [
            'label' => $isFinalized ? 'Đã chốt attendance' : 'Chưa chốt attendance',
            'color' => $isFinalized ? 'success' : ($markedStudents > 0 ? 'warning' : 'secondary'),
            'total_students' => $totalStudents,
            'marked_students' => $markedStudents,
            'present_count' => $presentCount,
            'late_count' => $lateCount,
            'absent_count' => $absentCount,
            'excused_count' => $excusedCount,
            'can_manage' => $canManage && !$sessionStatus['is_cancelled'],
            'is_finalized' => $isFinalized,
            'status_hint' => $isFinalized
                ? 'Attendance học viên đã được chốt bằng báo cáo cuối buổi. Bạn vẫn có thể mở lại modal để rà soát và cập nhật nếu cần.'
                : 'Giảng viên có thể cập nhật attendance nhiều lần trong buổi học rồi chốt lại khi đã kiểm tra xong.',
        ];
    }

    private function resolveTeachingSessionGuardMessage(array $sessionStatus, string $action): string
    {
        return match ($sessionStatus['value']) {
            'dang_dien_ra' => $action === 'start'
                ? 'Buổi học này đã ở trạng thái đang diễn ra.'
                : 'Buổi học này đang diễn ra, vui lòng hoàn tất các thao tác còn lại trước khi kết thúc.',
            'da_ket_thuc' => 'Buổi học này đã kết thúc, không thể thao tác thêm.',
            'da_huy' => 'Buổi học này đã bị hủy, không thể thay đổi trạng thái vận hành.',
            default => $action === 'finish'
                ? 'Bạn cần bắt đầu buổi học trước khi kết thúc.'
                : 'Buổi học này không thể bắt đầu trong trạng thái hiện tại.',
        };
    }

    private function buildTeachingStatus(LichHoc $lichHoc, $teacherLiveRoom, bool $canManage, array $sessionStatus): array
    {
        if ($lichHoc->hinh_thuc !== 'online') {
            return [
                'label' => 'Buoi hoc truc tiep',
                'color' => 'success',
                'room_status_label' => 'Khong ap dung',
                'room_status_color' => 'secondary',
                'can_create_room' => false,
                'can_enter_room' => false,
                'can_end_room' => false,
            ];
        }

        $sessionLocked = $sessionStatus['is_locked'];

        if (!$teacherLiveRoom) {
            return [
                'label' => 'Buoi hoc online',
                'color' => 'info',
                'room_status_label' => 'Chua tao',
                'room_status_color' => 'secondary',
                'can_create_room' => $canManage && !$sessionLocked,
                'can_enter_room' => false,
                'can_end_room' => false,
            ];
        }

        return [
            'label' => 'Buoi hoc online',
            'color' => 'info',
            'room_status_label' => $teacherLiveRoom->teaching_timeline_status_label,
            'room_status_color' => $teacherLiveRoom->teaching_timeline_status_color,
            'can_create_room' => false,
            'can_enter_room' => $canManage && !$sessionLocked,
            'can_end_room' => $canManage && $teacherLiveRoom->isDangDienRa(),
        ];
    }
}
