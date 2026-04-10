<?php

namespace App\Services\Scheduling;

use App\Models\GiangVien;
use App\Models\GiangVienDonXinNghi;
use App\Models\ModuleHoc;
use App\Models\PhanCongModuleGiangVien;
use Illuminate\Support\Carbon;

class AdminSchedulePlanningService
{
    public function __construct(
        private readonly TeacherScheduleRuleService $ruleService,
        private readonly TeacherLeaveRequestService $leaveRequestService,
        private readonly TeacherScheduleConflictService $conflictService,
        private readonly ScheduleSuggestionService $suggestionService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function inspect(int $courseId, array $payload, ?int $ignoreScheduleId = null): array
    {
        $moduleId = isset($payload['module_hoc_id']) ? (int) $payload['module_hoc_id'] : 0;
        $teacherId = filled($payload['giang_vien_id'] ?? null) ? (int) $payload['giang_vien_id'] : null;
        $startTime = substr((string) ($payload['gio_bat_dau'] ?? ''), 0, 5);
        $endTime = substr((string) ($payload['gio_ket_thuc'] ?? ''), 0, 5);
        $dateValue = (string) ($payload['ngay_hoc'] ?? '');
        $startPeriod = isset($payload['tiet_bat_dau']) && $payload['tiet_bat_dau'] !== null ? (int) $payload['tiet_bat_dau'] : null;
        $endPeriod = isset($payload['tiet_ket_thuc']) && $payload['tiet_ket_thuc'] !== null ? (int) $payload['tiet_ket_thuc'] : null;

        $context = [
            'can_schedule' => true,
            'errors' => [],
            'module' => null,
            'teacher' => null,
            'assignment' => [
                'ok' => null,
                'message' => 'Chưa chọn giảng viên.',
            ],
            'standard_window' => [
                'ok' => null,
                'message' => 'Chưa có dữ liệu để kiểm tra khung dạy chuẩn.',
                'rule_label' => $this->ruleService->ruleLabel(),
            ],
            'teaching_window' => [
                'ok' => null,
                'message' => 'Chưa có dữ liệu để kiểm tra khung dạy chuẩn và đơn xin nghỉ.',
            ],
            'leave_requests' => [
                'ok' => null,
                'message' => 'Chưa chọn giảng viên để kiểm tra đơn xin nghỉ.',
                'items' => [],
            ],
            'conflicts' => [
                'ok' => null,
                'message' => 'Chưa có dữ liệu để kiểm tra xung đột.',
                'items' => [],
            ],
            'suggestions' => [],
        ];

        $module = ModuleHoc::with(['khoaHoc', 'phanCongGiangViens.giangVien.nguoiDung'])->find($moduleId);
        if (!$module || (int) $module->khoa_hoc_id !== $courseId) {
            $context['can_schedule'] = false;
            $context['errors']['module_hoc_id'] = 'Module được chọn không thuộc khóa học hiện tại.';

            return $context;
        }

        $context['module'] = $module;

        if (blank($dateValue) || blank($startTime) || blank($endTime)) {
            $context['can_schedule'] = false;
            $context['errors']['ngay_hoc'] = 'Cần chọn ngày học và khung giờ dạy để kiểm tra.';

            return $context;
        }

        $date = Carbon::parse($dateValue);
        $durationMinutes = Carbon::createFromFormat('H:i', $endTime)->diffInMinutes(Carbon::createFromFormat('H:i', $startTime));
        $standardWindow = $this->ruleService->inspect($date, $startTime, $endTime);
        $context['standard_window'] = $standardWindow;
        $context['teaching_window'] = [
            'ok' => $standardWindow['ok'],
            'message' => $standardWindow['message'],
        ];

        if (!$standardWindow['ok']) {
            $context['can_schedule'] = false;
            $context['errors']['ngay_hoc'] = $standardWindow['message'];
        }

        if ($teacherId === null) {
            $context['can_schedule'] = false;
            $context['assignment']['ok'] = false;
            $context['assignment']['message'] = 'Vui lòng chọn giảng viên trước khi lưu lịch học.';
            $context['leave_requests']['ok'] = true;
            $context['leave_requests']['message'] = 'Chưa có giảng viên để đối chiếu đơn xin nghỉ.';
            $context['teaching_window']['message'] = $standardWindow['message'] . ' Vui lòng chọn giảng viên để đối chiếu đơn xin nghỉ.';
            $context['conflicts']['ok'] = true;
            $context['conflicts']['message'] = 'Chưa đủ dữ liệu để kiểm tra xung đột cho giảng viên.';
            $context['errors']['giang_vien_id'] = $context['assignment']['message'];

            return $context;
        }

        $teacher = GiangVien::with('nguoiDung')->find($teacherId);
        if (!$teacher) {
            $context['can_schedule'] = false;
            $context['errors']['giang_vien_id'] = 'Giảng viên được chọn không hợp lệ.';

            return $context;
        }

        $context['teacher'] = $teacher;
        $context['suggestions'] = $this->suggestionService->suggest($teacherId, $durationMinutes, $date, 5, $ignoreScheduleId);

        $assignment = PhanCongModuleGiangVien::query()
            ->where('khoa_hoc_id', $courseId)
            ->where('module_hoc_id', $moduleId)
            ->where('giang_vien_id', $teacherId)
            ->latest('id')
            ->first();

        if (!$assignment || $assignment->trang_thai !== 'da_nhan') {
            $context['can_schedule'] = false;
            $context['assignment']['ok'] = false;
            $context['assignment']['message'] = 'Giảng viên này chưa có phân công đã nhận cho module được chọn.';
            $context['errors']['giang_vien_id'] = $context['assignment']['message'];
        } else {
            $context['assignment']['ok'] = true;
            $context['assignment']['message'] = 'Giảng viên đã được phân công và đã xác nhận module này.';
        }

        $blockingLeaveRequests = $this->leaveRequestService->findOverlappingRequests(
            $teacherId,
            $date,
            $startPeriod,
            $endPeriod,
            null,
            [GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET, GiangVienDonXinNghi::TRANG_THAI_DA_DUYET],
        );

        $context['leave_requests']['items'] = $blockingLeaveRequests
            ->map(fn (GiangVienDonXinNghi $item) => [
                'id' => $item->id,
                'status' => $item->trang_thai,
                'status_label' => $item->trang_thai_label,
                'range' => $item->schedule_range_label,
                'reason' => $item->ly_do,
            ])
            ->values()
            ->all();

        $approvedLeaveRequests = $blockingLeaveRequests
            ->where('trang_thai', GiangVienDonXinNghi::TRANG_THAI_DA_DUYET)
            ->values();
        $pendingLeaveRequests = $blockingLeaveRequests
            ->where('trang_thai', GiangVienDonXinNghi::TRANG_THAI_CHO_DUYET)
            ->values();

        if ($approvedLeaveRequests->isNotEmpty()) {
            $context['can_schedule'] = false;
            $context['leave_requests']['ok'] = false;
            $context['leave_requests']['message'] = 'Khung dạy này trùng với đơn xin nghỉ đã duyệt của giảng viên.';
            $context['errors']['gio_bat_dau'] = $context['leave_requests']['message'];
        } elseif ($pendingLeaveRequests->isNotEmpty()) {
            $context['leave_requests']['ok'] = true;
            $context['leave_requests']['message'] = 'Giảng viên đang có đơn xin nghỉ cho khung này. Admin cần xem xét trước khi lưu.';
        } else {
            $context['leave_requests']['ok'] = true;
            $context['leave_requests']['message'] = 'Không có đơn xin nghỉ nào trùng với khung dạy này.';
        }

        $context['teaching_window']['ok'] = $standardWindow['ok'] && $approvedLeaveRequests->isEmpty();
        $context['teaching_window']['message'] = $approvedLeaveRequests->isNotEmpty()
            ? $context['leave_requests']['message']
            : ($pendingLeaveRequests->isNotEmpty()
                ? $standardWindow['message'] . ' ' . $context['leave_requests']['message']
                : $standardWindow['message']);

        $conflicts = $this->conflictService->findConflicts(
            $teacherId,
            $date,
            $startTime,
            $endTime,
            $ignoreScheduleId,
            $startPeriod,
            $endPeriod,
        );
        $context['conflicts']['items'] = $conflicts
            ->map(fn ($item) => [
                'id' => $item->id,
                'course_code' => $item->khoaHoc?->ma_khoa_hoc ?? 'N/A',
                'module_name' => $item->moduleHoc?->ten_module ?? 'N/A',
                'date' => $item->ngay_hoc?->format('d/m/Y') ?? 'N/A',
                'time' => substr((string) $item->gio_bat_dau, 0, 5) . ' - ' . substr((string) $item->gio_ket_thuc, 0, 5),
                'schedule' => $item->schedule_range_label,
            ])
            ->values()
            ->all();

        if ($conflicts->isNotEmpty()) {
            $context['can_schedule'] = false;
            $context['conflicts']['ok'] = false;
            $context['conflicts']['message'] = $this->conflictService->buildConflictMessage($conflicts);
            $context['errors']['gio_bat_dau'] = $context['conflicts']['message'];
        } else {
            $context['conflicts']['ok'] = true;
            $context['conflicts']['message'] = 'Không phát hiện lịch dạy nào bị giao nhau với khung này.';
        }

        return $context;
    }
}

