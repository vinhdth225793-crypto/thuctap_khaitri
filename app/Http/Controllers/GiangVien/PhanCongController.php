<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\PhanCongModuleGiangVien;
use App\Models\YeuCauHocVien;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhanCongController extends Controller
{
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

            return [
                'lich' => $lich,
                'teacherAttendance' => $teacherAttendance,
                'teacherLiveLecture' => $teacherLiveLecture,
                'teacherLiveRoom' => $teacherLiveRoom,
                'teachingStatus' => $this->buildTeachingStatus($lich, $teacherLiveRoom, $phanCong->trang_thai === 'da_nhan'),
                'attendanceStatus' => [
                    'label' => $teacherAttendance?->trang_thai_label ?? 'Chua diem danh',
                    'color' => $teacherAttendance?->trang_thai_color ?? 'secondary',
                    'can_check_in' => $phanCong->trang_thai === 'da_nhan' && !$teacherAttendance?->has_checked_in,
                    'can_check_out' => $phanCong->trang_thai === 'da_nhan' && ($teacherAttendance?->has_checked_in ?? false) && !$teacherAttendance?->has_checked_out,
                ],
                'resourceCount' => $lich->taiNguyen->count(),
                'examCount' => $lich->baiKiemTras->count(),
            ];
        });

        return view('pages.giang-vien.phan-cong.show', compact('phanCong', 'khoaHoc', 'lichDays', 'lichHocIds', 'timelineItems'));
    }

    /**
     * Phase 3: Cập nhật link học Online
     */
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

    private function buildTeachingStatus(LichHoc $lichHoc, $teacherLiveRoom, bool $canManage): array
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

        if (!$teacherLiveRoom) {
            return [
                'label' => 'Buoi hoc online',
                'color' => 'info',
                'room_status_label' => 'Chua tao',
                'room_status_color' => 'secondary',
                'can_create_room' => $canManage,
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
            'can_enter_room' => $canManage,
            'can_end_room' => $canManage && $teacherLiveRoom->isDangDienRa(),
        ];
    }
}
