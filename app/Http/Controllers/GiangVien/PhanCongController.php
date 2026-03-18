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

        // Lấy danh sách khóa học mà GV có tham gia dạy (ít nhất 1 module)
        $khoaHocs = KhoaHoc::with(['nhomNganh', 'moduleHocs' => function($q) use ($giangVien) {
                // Chỉ lấy những module mà GV này được phân công trong khóa học đó
                $q->whereHas('phanCongGiangViens', function($q2) use ($giangVien) {
                    $q2->where('giao_vien_id', $giangVien->id);
                })->with(['phanCongGiangViens' => function($q2) use ($giangVien) {
                    $q2->where('giao_vien_id', $giangVien->id);
                }]);
            }])
            ->whereHas('moduleHocs.phanCongGiangViens', function($q) use ($giangVien) {
                $q->where('giao_vien_id', $giangVien->id);
            })
            ->orderBy('id', 'desc')
            ->get();

        // Đếm số phân công mới để hiển thị badge thông báo
        $phanCongChoXacNhan = PhanCongModuleGiangVien::where('giao_vien_id', $giangVien->id)
            ->where('trang_thai', 'cho_xac_nhan')
            ->count();

        return view('pages.giang-vien.phan-cong.index', compact('khoaHocs', 'phanCongChoXacNhan'));
    }

    public function show($id)
    {
        $giangVien = auth()->user()->giangVien;
        
        $phanCong = PhanCongModuleGiangVien::with([
            'khoaHoc.nhomNganh', 
            'moduleHoc',
            'khoaHoc.hocVienKhoaHocs.hocVien' => function($q) {
                $q->with(['diemDanhs']);
            }
        ])
        ->where('giao_vien_id', $giangVien->id)
        ->findOrFail($id);

        $khoaHoc = $phanCong->khoaHoc;
        
        // Lấy danh sách ID buổi học của khóa học này để lọc điểm danh
        $lichHocIds = LichHoc::where('khoa_hoc_id', $khoaHoc->id)->pluck('id');
        
        // Lấy TOÀN BỘ lịch dạy của Module này (để GV thấy lộ trình đầy đủ)
        $lichDays = LichHoc::with(['taiNguyen', 'baiKiemTras'])
            ->where('module_hoc_id', $phanCong->module_hoc_id)
            ->orderBy('ngay_hoc')
            ->get();

        return view('pages.giang-vien.phan-cong.show', compact('phanCong', 'khoaHoc', 'lichDays', 'lichHocIds'));
    }

    /**
     * Phase 3: Cập nhật link học Online
     */
    public function updateLinkOnline(Request $request, $id)
    {
        $giangVien = auth()->user()->giangVien;
        $lichHoc = LichHoc::findOrFail($id);

        // Kiểm tra quyền: Chỉ GV được gán cho module chứa buổi học này mới được sửa
        $isAssigned = PhanCongModuleGiangVien::where('module_hoc_id', $lichHoc->module_hoc_id)
            ->where('giao_vien_id', $giangVien->id)
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
            'hinh_thuc', 'nen_tang', 'link_online', 'meeting_id', 'mat_khau_cuoc_hop', 'phong_hoc'
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

        $request->validate([
            'loai_yeu_cau' => 'required|in:them,xoa,sua',
            'ly_do'        => 'required|string|max:1000',
            'email_hoc_vien' => 'required_if:loai_yeu_cau,them|nullable|email',
            'ten_hoc_vien'   => 'required_if:loai_yeu_cau,them|nullable|string|max:255',
            'hoc_vien_id'    => 'required_if:loai_yeu_cau,xoa,sua|nullable|exists:nguoi_dung,ma_nguoi_dung',
        ]);

        $duLieu = [
            'loai'  => $request->loai_yeu_cau,
            'email' => $request->email_hoc_vien,
            'ten'   => $request->ten_hoc_vien,
            'id'    => $request->hoc_vien_id,
        ];

        YeuCauHocVien::create([
            'khoa_hoc_id'     => $khoaHocId,
            'giang_vien_id'   => $giangVien->id,
            'loai_yeu_cau'    => $request->loai_yeu_cau,
            'du_lieu_yeu_cau' => json_encode($duLieu),
            'ly_do'           => $request->ly_do,
            'trang_thai'      => 'cho_duyet',
        ]);

        return back()->with('success', 'Yêu cầu của bạn đã được gửi đến ban quản trị để xem xét.');
    }

    public function xacNhan(Request $request, $id)
    {
        $giangVien = auth()->user()->giangVien;
        $phanCong  = PhanCongModuleGiangVien::where('id', $id)
            ->where('giao_vien_id', $giangVien->id)
            ->firstOrFail();

        // Chỉ được xử lý nếu đang ở trạng thái chờ
        if ($phanCong->trang_thai !== 'cho_xac_nhan') {
            return back()->with('error', 'Phân công này đã được xử lý hoặc không còn khả dụng.');
        }

        $validated = $request->validate([
            'hanh_dong' => 'required|in:da_nhan,tu_choi',
            'ghi_chu'   => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $phanCong->update([
                'trang_thai' => $validated['hanh_dong'],
                'ghi_chu'    => $validated['ghi_chu'] ?? $phanCong->ghi_chu,
            ]);

            // Logic tự động cập nhật trạng thái Khóa học khi ĐỦ giảng viên (chỉ khi Chấp nhận)
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
}
