<?php

namespace App\Services;

use App\Models\NguoiDung;
use App\Models\ThongBao;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * NotificationService — gửi thông báo cho user dựa trên nghiệp vụ.
 *
 * Quy ước loại thông báo (string code, snake_case):
 *   account_pending           — Tài khoản mới đăng ký (admin nhận)
 *   account_approved          — Tài khoản được duyệt (chủ nhận)
 *   account_rejected          — Tài khoản bị từ chối
 *
 *   library_submitted         — GV gửi tài nguyên duyệt (admin nhận)
 *   library_approved          — Tài nguyên được duyệt (GV nhận)
 *   library_rejected          — Tài nguyên bị từ chối / cần sửa
 *
 *   lecture_submitted         — GV gửi bài giảng duyệt (admin nhận)
 *   lecture_approved          — Bài giảng được duyệt
 *   lecture_rejected          — Bài giảng bị từ chối / cần sửa
 *   lecture_published         — Bài giảng đã công bố (HV nhận)
 *
 *   exam_submitted            — GV gửi đề duyệt (admin nhận)
 *   exam_approved             — Đề được duyệt
 *   exam_rejected             — Đề bị từ chối
 *   exam_published            — Đề được phát hành (HV nhận)
 *   exam_graded               — Bài làm đã chấm xong (HV nhận)
 *   exam_submission           — HV nộp bài tự luận (GV nhận)
 *
 *   leave_submitted           — GV gửi đơn xin nghỉ (admin nhận)
 *   leave_approved            — Đơn được duyệt
 *   leave_rejected            — Đơn bị từ chối
 *
 *   result_ticket_submitted   — GV gửi phiếu xét duyệt (admin nhận)
 *   result_ticket_approved    — Phiếu được duyệt
 *   result_ticket_rejected    — Phiếu bị từ chối
 *   result_finalized          — Kết quả đã được chốt (HV nhận)
 *
 *   student_request_new       — Yêu cầu HV mới (admin nhận)
 *   student_request_approved  — Yêu cầu HV được duyệt (GV/HV nhận)
 *   student_request_rejected  — Yêu cầu HV bị từ chối
 *   student_enrolled          — HV được thêm vào khóa (HV nhận)
 *
 *   course_assignment_new     — Phân công GV mới (GV nhận)
 *   course_assignment_confirm — GV xác nhận phân công (admin nhận)
 *
 *   class_opened              — Lớp được mở (HV trong lớp nhận)
 *   schedule_session_soon     — Buổi học sắp diễn ra (HV/GV nhận)
 *
 *   he_thong                  — Thông báo hệ thống chung
 */
class NotificationService
{
    public function send(int $userId, string $title, string $body, array $opts = []): ThongBao
    {
        return ThongBao::create([
            'nguoi_nhan_id' => $userId,
            'tieu_de'       => $title,
            'noi_dung'      => $body,
            'loai'          => $opts['loai']     ?? 'he_thong',
            'level'         => $opts['level']    ?? 'info',
            'icon'          => $opts['icon']     ?? null,
            'url'           => $opts['url']      ?? null,
            'metadata'      => $opts['metadata'] ?? null,
            'da_doc'        => false,
        ]);
    }

    /**
     * @param  iterable<int>  $userIds
     */
    public function sendMany(iterable $userIds, string $title, string $body, array $opts = []): int
    {
        $count = 0;
        foreach ($userIds as $uid) {
            if (!$uid) continue;
            try {
                $this->send((int) $uid, $title, $body, $opts);
                $count++;
            } catch (\Throwable $e) {
                Log::warning('NotificationService: failed to notify user ' . $uid . ' — ' . $e->getMessage());
            }
        }
        return $count;
    }

    /** Gửi cho mọi user theo vai trò. */
    public function sendToRole(string $role, string $title, string $body, array $opts = []): int
    {
        $userIds = NguoiDung::where('vai_tro', $role)->pluck('ma_nguoi_dung');
        return $this->sendMany($userIds, $title, $body, $opts);
    }

    /* ============================================================
       SHORTCUTS — gọi từ controller cho từng nghiệp vụ
       ============================================================ */

    public function notifyLibrarySubmitted(string $tieuDe, int $resourceId, ?string $tenGV = null): int
    {
        return $this->sendToRole(
            'admin',
            'Tài nguyên thư viện cần duyệt',
            ($tenGV ? $tenGV . ' đã ' : 'Giảng viên đã ') . 'gửi tài nguyên "' . $tieuDe . '" để duyệt.',
            [
                'loai'  => 'library_submitted',
                'level' => 'warning',
                'icon'  => 'fa-folder-tree',
                'url'   => route('admin.thu-vien.show', $resourceId),
            ]
        );
    }

    public function notifyLibraryApproved(int $userId, string $tieuDe, int $resourceId, bool $approved, ?string $note = null): void
    {
        $this->send(
            $userId,
            $approved ? 'Tài nguyên đã được duyệt' : 'Tài nguyên cần chỉnh sửa / từ chối',
            'Tài nguyên "' . $tieuDe . '"' .
                ($approved ? ' đã được admin duyệt.' : ' bị admin từ chối / yêu cầu chỉnh sửa.') .
                ($note ? ' Ghi chú: ' . $note : ''),
            [
                'loai'  => $approved ? 'library_approved' : 'library_rejected',
                'level' => $approved ? 'success' : 'danger',
                'icon'  => $approved ? 'fa-circle-check' : 'fa-circle-xmark',
                'url'   => route('giang-vien.thu-vien.edit', $resourceId),
            ]
        );
    }

    public function notifyLectureSubmitted(string $tieuDe, int $lectureId): int
    {
        return $this->sendToRole(
            'admin',
            'Bài giảng cần duyệt',
            'Có bài giảng mới "' . $tieuDe . '" đang chờ admin duyệt.',
            [
                'loai'  => 'lecture_submitted',
                'level' => 'warning',
                'icon'  => 'fa-chalkboard-teacher',
                'url'   => route('admin.bai-giang.show', $lectureId),
            ]
        );
    }

    public function notifyLectureApproved(int $userId, string $tieuDe, int $lectureId, bool $approved, ?string $note = null): void
    {
        $this->send(
            $userId,
            $approved ? 'Bài giảng đã được duyệt' : 'Bài giảng cần chỉnh sửa / từ chối',
            'Bài giảng "' . $tieuDe . '"' . ($approved ? ' đã được duyệt.' : ' bị từ chối / cần sửa.') .
                ($note ? ' Ghi chú: ' . $note : ''),
            [
                'loai'  => $approved ? 'lecture_approved' : 'lecture_rejected',
                'level' => $approved ? 'success' : 'danger',
                'icon'  => $approved ? 'fa-circle-check' : 'fa-circle-xmark',
                'url'   => route('giang-vien.bai-giang.edit', $lectureId),
            ]
        );
    }

    public function notifyLecturePublishedToCourse(int $khoaHocId, string $tieuDe, int $lectureId): int
    {
        // HV trong khóa
        $userIds = \App\Models\HocVienKhoaHoc::where('khoa_hoc_id', $khoaHocId)
            ->whereIn('trang_thai', ['dang_hoc'])
            ->pluck('hoc_vien_id');

        return $this->sendMany($userIds, 'Bài giảng mới', 'Bài giảng "' . $tieuDe . '" vừa được công bố cho lớp của bạn.', [
            'loai'  => 'lecture_published',
            'level' => 'info',
            'icon'  => 'fa-book-open',
            'url'   => route('hoc-vien.bai-giang.show', $lectureId),
        ]);
    }

    public function notifyExamSubmitted(string $tieuDe, int $examId): int
    {
        return $this->sendToRole(
            'admin',
            'Đề thi cần duyệt',
            'Có đề thi mới "' . $tieuDe . '" đang chờ admin duyệt.',
            [
                'loai'  => 'exam_submitted',
                'level' => 'warning',
                'icon'  => 'fa-file-signature',
                'url'   => route('admin.kiem-tra-online.phe-duyet.show', $examId),
            ]
        );
    }

    public function notifyExamApproved(int $userId, string $tieuDe, int $examId, bool $approved, ?string $note = null): void
    {
        $this->send(
            $userId,
            $approved ? 'Đề thi đã được duyệt' : 'Đề thi bị từ chối',
            'Đề thi "' . $tieuDe . '"' . ($approved ? ' đã được duyệt.' : ' bị từ chối.') .
                ($note ? ' Ghi chú: ' . $note : ''),
            [
                'loai'  => $approved ? 'exam_approved' : 'exam_rejected',
                'level' => $approved ? 'success' : 'danger',
                'icon'  => $approved ? 'fa-circle-check' : 'fa-circle-xmark',
                'url'   => route('giang-vien.bai-kiem-tra.edit', $examId),
            ]
        );
    }

    public function notifyExamPublishedToCourse(int $khoaHocId, string $tieuDe, int $examId): int
    {
        $userIds = \App\Models\HocVienKhoaHoc::where('khoa_hoc_id', $khoaHocId)
            ->whereIn('trang_thai', ['dang_hoc'])
            ->pluck('hoc_vien_id');

        return $this->sendMany($userIds, 'Đề thi mới được phát hành', 'Đề thi "' . $tieuDe . '" đã được phát hành cho lớp của bạn.', [
            'loai'  => 'exam_published',
            'level' => 'info',
            'icon'  => 'fa-file-signature',
            'url'   => route('hoc-vien.bai-kiem-tra.show', $examId),
        ]);
    }

    public function notifyExamGraded(int $userId, string $tieuDe, int $examId, ?float $diem = null): void
    {
        $this->send(
            $userId,
            'Bài làm đã được chấm',
            'Bài thi "' . $tieuDe . '"' . ($diem !== null ? ' đạt ' . number_format($diem, 2) . ' điểm.' : ' đã được giảng viên chấm xong.'),
            [
                'loai'  => 'exam_graded',
                'level' => 'success',
                'icon'  => 'fa-star',
                'url'   => route('hoc-vien.bai-kiem-tra.show', $examId),
            ]
        );
    }

    public function notifyExamSubmissionToTeacher(int $teacherUserId, string $hocVienTen, string $examTitle, int $baiLamId): void
    {
        $this->send(
            $teacherUserId,
            'Có bài tự luận mới chờ chấm',
            $hocVienTen . ' vừa nộp bài "' . $examTitle . '".',
            [
                'loai'  => 'exam_submission',
                'level' => 'warning',
                'icon'  => 'fa-pen-fancy',
                'url'   => route('giang-vien.cham-diem.show', $baiLamId),
            ]
        );
    }

    public function notifyLeaveSubmitted(string $tenGV, int $leaveId): int
    {
        return $this->sendToRole(
            'admin',
            'Đơn xin nghỉ mới',
            $tenGV . ' đã gửi đơn xin nghỉ.',
            [
                'loai'  => 'leave_submitted',
                'level' => 'warning',
                'icon'  => 'fa-calendar-xmark',
                'url'   => route('admin.giang-vien-don-xin-nghi.show', $leaveId),
            ]
        );
    }

    public function notifyLeaveDecided(int $teacherUserId, int $leaveId, bool $approved, ?string $note = null): void
    {
        $this->send(
            $teacherUserId,
            $approved ? 'Đơn xin nghỉ được duyệt' : 'Đơn xin nghỉ bị từ chối',
            'Đơn xin nghỉ của bạn đã ' . ($approved ? 'được duyệt.' : 'bị từ chối.') .
                ($note ? ' Ghi chú: ' . $note : ''),
            [
                'loai'  => $approved ? 'leave_approved' : 'leave_rejected',
                'level' => $approved ? 'success' : 'danger',
                'icon'  => $approved ? 'fa-circle-check' : 'fa-circle-xmark',
                'url'   => route('giang-vien.don-xin-nghi.index'),
            ]
        );
    }

    public function notifyResultTicketSubmitted(string $tenKhoa, int $ticketId): int
    {
        return $this->sendToRole(
            'admin',
            'Phiếu xét duyệt kết quả mới',
            'GV đã gửi phiếu xét duyệt kết quả cho khóa ' . $tenKhoa . '.',
            [
                'loai'  => 'result_ticket_submitted',
                'level' => 'warning',
                'icon'  => 'fa-stamp',
                'url'   => route('admin.xet-duyet-ket-qua.show', $ticketId),
            ]
        );
    }

    public function notifyResultTicketDecided(int $teacherUserId, string $tenKhoa, int $ticketId, bool $approved, ?string $note = null): void
    {
        $this->send(
            $teacherUserId,
            $approved ? 'Phiếu xét duyệt kết quả được duyệt' : 'Phiếu xét duyệt bị từ chối',
            'Phiếu xét duyệt khóa ' . $tenKhoa . ($approved ? ' đã được admin duyệt.' : ' bị admin từ chối.') .
                ($note ? ' Ghi chú: ' . $note : ''),
            [
                'loai'  => $approved ? 'result_ticket_approved' : 'result_ticket_rejected',
                'level' => $approved ? 'success' : 'danger',
                'icon'  => $approved ? 'fa-circle-check' : 'fa-circle-xmark',
            ]
        );
    }

    public function notifyResultFinalized(int $hocVienUserId, string $tenKhoa, int $khoaHocId): void
    {
        $this->send(
            $hocVienUserId,
            'Kết quả khóa học đã được chốt',
            'Kết quả khóa "' . $tenKhoa . '" đã được chốt chính thức.',
            [
                'loai'  => 'result_finalized',
                'level' => 'success',
                'icon'  => 'fa-trophy',
                'url'   => route('hoc-vien.chi-tiet-khoa-hoc', $khoaHocId),
            ]
        );
    }

    public function notifyStudentRequestNew(string $loaiLabel, string $tenGV, int $requestId): int
    {
        return $this->sendToRole(
            'admin',
            'Yêu cầu liên quan học viên',
            $tenGV . ' gửi yêu cầu ' . $loaiLabel . ' học viên.',
            [
                'loai'  => 'student_request_new',
                'level' => 'warning',
                'icon'  => 'fa-user-edit',
                'url'   => route('admin.yeu-cau-hoc-vien.index'),
            ]
        );
    }

    public function notifyStudentRequestDecided(int $teacherUserId, bool $approved, string $loaiLabel, ?string $note = null): void
    {
        $this->send(
            $teacherUserId,
            $approved ? 'Yêu cầu được duyệt' : 'Yêu cầu bị từ chối',
            'Yêu cầu ' . $loaiLabel . ' của bạn đã ' . ($approved ? 'được duyệt.' : 'bị từ chối.') .
                ($note ? ' Ghi chú: ' . $note : ''),
            [
                'loai'  => $approved ? 'student_request_approved' : 'student_request_rejected',
                'level' => $approved ? 'success' : 'danger',
                'icon'  => $approved ? 'fa-circle-check' : 'fa-circle-xmark',
            ]
        );
    }

    public function notifyStudentEnrolled(int $hocVienUserId, string $tenKhoa, int $khoaHocId): void
    {
        $this->send(
            $hocVienUserId,
            'Bạn đã được thêm vào khóa học',
            'Bạn vừa được thêm vào khóa "' . $tenKhoa . '".',
            [
                'loai'  => 'student_enrolled',
                'level' => 'info',
                'icon'  => 'fa-user-graduate',
                'url'   => route('hoc-vien.chi-tiet-khoa-hoc', $khoaHocId),
            ]
        );
    }

    public function notifyAccountPending(string $hoTen, string $email): int
    {
        return $this->sendToRole(
            'admin',
            'Tài khoản mới chờ duyệt',
            $hoTen . ' (' . $email . ') vừa đăng ký, chờ admin duyệt.',
            [
                'loai'  => 'account_pending',
                'level' => 'warning',
                'icon'  => 'fa-user-plus',
                'url'   => route('admin.phe-duyet-tai-khoan.index'),
            ]
        );
    }

    public function notifyAccountDecided(int $userId, bool $approved, ?string $note = null): void
    {
        $this->send(
            $userId,
            $approved ? 'Tài khoản được phê duyệt' : 'Tài khoản bị từ chối',
            $approved ? 'Tài khoản của bạn đã được phê duyệt. Bạn có thể đăng nhập và sử dụng hệ thống.' : ('Tài khoản của bạn đã bị từ chối.' . ($note ? ' Lý do: ' . $note : '')),
            [
                'loai'  => $approved ? 'account_approved' : 'account_rejected',
                'level' => $approved ? 'success' : 'danger',
                'icon'  => $approved ? 'fa-user-check' : 'fa-user-xmark',
            ]
        );
    }

    public function notifyCourseAssignment(int $teacherUserId, string $tenKhoa, int $assignmentId): void
    {
        $this->send(
            $teacherUserId,
            'Bạn được phân công giảng dạy',
            'Admin đã phân công bạn cho khóa "' . $tenKhoa . '". Vui lòng xác nhận.',
            [
                'loai'  => 'course_assignment_new',
                'level' => 'info',
                'icon'  => 'fa-clipboard-user',
                'url'   => route('giang-vien.khoa-hoc.show', $assignmentId),
            ]
        );
    }

    public function notifyCourseAssignmentConfirmed(string $tenGV, string $tenKhoa, bool $accepted): int
    {
        return $this->sendToRole(
            'admin',
            $accepted ? 'GV xác nhận phân công' : 'GV từ chối phân công',
            $tenGV . ' đã ' . ($accepted ? 'xác nhận' : 'từ chối') . ' phân công khóa "' . $tenKhoa . '".',
            [
                'loai'  => 'course_assignment_confirm',
                'level' => $accepted ? 'success' : 'danger',
                'icon'  => $accepted ? 'fa-circle-check' : 'fa-circle-xmark',
            ]
        );
    }

    public function notifyClassOpened(int $khoaHocId, string $tenKhoa): int
    {
        $userIds = \App\Models\HocVienKhoaHoc::where('khoa_hoc_id', $khoaHocId)
            ->whereIn('trang_thai', ['dang_hoc'])
            ->pluck('hoc_vien_id');

        return $this->sendMany($userIds, 'Lớp đã được mở', 'Khóa "' . $tenKhoa . '" đã sẵn sàng — bạn có thể bắt đầu học.', [
            'loai'  => 'class_opened',
            'level' => 'info',
            'icon'  => 'fa-door-open',
            'url'   => route('hoc-vien.chi-tiet-khoa-hoc', $khoaHocId),
        ]);
    }

    /* ===== READ APIs ===== */
    public function unreadCount(int $userId): int
    {
        return ThongBao::ofUser($userId)->chuaDoc()->count();
    }

    public function recent(int $userId, int $limit = 8): Collection
    {
        return ThongBao::ofUser($userId)->moiNhat()->limit($limit)->get();
    }
}
