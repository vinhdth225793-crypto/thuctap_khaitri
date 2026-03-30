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
                'message' => 'Chua chon giang vien.',
            ],
            'standard_window' => [
                'ok' => null,
                'message' => 'Chua co du lieu de kiem tra khung day chuan.',
                'rule_label' => $this->ruleService->ruleLabel(),
            ],
            'availability' => [
                'ok' => null,
                'message' => 'Chua co du lieu de kiem tra khung day chuan.',
                'matched_slots' => [],
                'summary' => [
                    'weekly' => [],
                    'specific' => [],
                    'active_count' => 0,
                ],
            ],
            'leave_requests' => [
                'ok' => null,
                'message' => 'Chua chon giang vien de kiem tra don xin nghi.',
                'items' => [],
            ],
            'conflicts' => [
                'ok' => null,
                'message' => 'Chua co du lieu de kiem tra xung dot.',
                'items' => [],
            ],
            'suggestions' => [],
        ];

        $module = ModuleHoc::with(['khoaHoc', 'phanCongGiangViens.giangVien.nguoiDung'])->find($moduleId);
        if (!$module || (int) $module->khoa_hoc_id !== $courseId) {
            $context['can_schedule'] = false;
            $context['errors']['module_hoc_id'] = 'Module duoc chon khong thuoc khoa hoc hien tai.';

            return $context;
        }

        $context['module'] = $module;

        if (blank($dateValue) || blank($startTime) || blank($endTime)) {
            $context['can_schedule'] = false;
            $context['errors']['ngay_hoc'] = 'Can chon ngay hoc va khung gio day de kiem tra.';

            return $context;
        }

        $date = Carbon::parse($dateValue);
        $durationMinutes = Carbon::createFromFormat('H:i', $endTime)->diffInMinutes(Carbon::createFromFormat('H:i', $startTime));
        $standardWindow = $this->ruleService->inspect($date, $startTime, $endTime);
        $context['standard_window'] = $standardWindow;

        if (!$standardWindow['ok']) {
            $context['can_schedule'] = false;
            $context['errors']['ngay_hoc'] = $standardWindow['message'];
        }

        if ($teacherId === null) {
            $context['can_schedule'] = false;
            $context['assignment']['ok'] = false;
            $context['assignment']['message'] = 'Vui long chon giang vien truoc khi luu lich hoc.';
            $context['leave_requests']['ok'] = true;
            $context['leave_requests']['message'] = 'Chua co giang vien de doi chieu don xin nghi.';
            $context['conflicts']['ok'] = true;
            $context['conflicts']['message'] = 'Chua du du lieu de kiem tra xung dot cho giang vien.';
            $context['errors']['giang_vien_id'] = $context['assignment']['message'];

            return $context;
        }

        $teacher = GiangVien::with('nguoiDung')->find($teacherId);
        if (!$teacher) {
            $context['can_schedule'] = false;
            $context['errors']['giang_vien_id'] = 'Giang vien duoc chon khong hop le.';

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
            $context['assignment']['message'] = 'Giang vien nay chua co phan cong da nhan cho module duoc chon.';
            $context['errors']['giang_vien_id'] = $context['assignment']['message'];
        } else {
            $context['assignment']['ok'] = true;
            $context['assignment']['message'] = 'Giang vien da duoc phan cong va da xac nhan module nay.';
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
            $context['leave_requests']['message'] = 'Khung day nay trung voi don xin nghi da duyet cua giang vien.';
            $context['errors']['gio_bat_dau'] = $context['leave_requests']['message'];
        } elseif ($pendingLeaveRequests->isNotEmpty()) {
            $context['leave_requests']['ok'] = true;
            $context['leave_requests']['message'] = 'Giang vien dang co don xin nghi cho khung nay. Admin can xem xet truoc khi luu.';
        } else {
            $context['leave_requests']['ok'] = true;
            $context['leave_requests']['message'] = 'Khong co don xin nghi nao trung voi khung day nay.';
        }

        $context['availability']['ok'] = $standardWindow['ok'] && $approvedLeaveRequests->isEmpty();
        $context['availability']['message'] = $approvedLeaveRequests->isNotEmpty()
            ? $context['leave_requests']['message']
            : ($pendingLeaveRequests->isNotEmpty()
                ? $standardWindow['message'] . ' ' . $context['leave_requests']['message']
                : $standardWindow['message']);
        $context['availability']['matched_slots'] = $blockingLeaveRequests
            ->map(fn (GiangVienDonXinNghi $item) => [
                'id' => $item->id,
                'label' => $item->trang_thai_label,
                'time' => $item->schedule_range_label,
                'schedule' => $item->schedule_range_label,
                'type' => 'Don xin nghi',
            ])
            ->values()
            ->all();
        $context['availability']['summary']['active_count'] = count($context['availability']['matched_slots']);

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
            $context['conflicts']['message'] = 'Khong phat hien lich day nao bi giao nhau voi khung nay.';
        }

        return $context;
    }
}

