<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\YeuCauHocVien;
use App\Models\HocVienKhoaHoc;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class YeuCauHocVienController extends Controller
{
    public function index()
    {
        $statusOrder = [
            'cho_duyet' => 0,
            'da_duyet' => 1,
            'tu_choi' => 2,
        ];

        $yeuCaus = YeuCauHocVien::with(['khoaHoc', 'giangVien.nguoiDung', 'hocVienNguoiDung', 'admin'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->sortBy(fn (YeuCauHocVien $yeuCau) => $statusOrder[$yeuCau->trang_thai] ?? 99)
            ->values();

        return view('pages.admin.yeu-cau-hoc-vien.index', compact('yeuCaus'));
    }

    public function xacNhan(Request $request, $id)
    {
        $yeuCau = YeuCauHocVien::findOrFail($id);
        
        if ($yeuCau->trang_thai !== 'cho_duyet') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        $request->validate([
            'hanh_dong' => 'required|in:da_duyet,tu_choi',
            'phan_hoi'  => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $data = is_array($yeuCau->du_lieu_yeu_cau) 
                ? $yeuCau->du_lieu_yeu_cau 
                : json_decode($yeuCau->du_lieu_yeu_cau, true);

            if ($request->hanh_dong === 'da_duyet') {
                switch ($yeuCau->loai_yeu_cau) {
                    case 'them':
                        $hocVien = $yeuCau->hoc_vien_id
                            ? NguoiDung::where('id', $yeuCau->hoc_vien_id)->first()
                            : NguoiDung::where('email', $data['email'])->first();

                        if (!$hocVien) {
                            throw new \Exception('Học viên với email này chưa đăng ký tài khoản hệ thống.');
                        }

                        if (!$hocVien->isHocVien()) {
                            throw new \Exception('Tài khoản được yêu cầu không phải vai trò học viên.');
                        }
                        
                        // Thêm vào khóa học
                        HocVienKhoaHoc::updateOrCreate(
                            ['khoa_hoc_id' => $yeuCau->khoa_hoc_id, 'hoc_vien_id' => $hocVien->id],
                            ['trang_thai' => 'dang_hoc', 'ngay_tham_gia' => now(), 'created_by' => auth()->id()]
                        );
                        break;

                    case 'xoa':
                        HocVienKhoaHoc::where('khoa_hoc_id', $yeuCau->khoa_hoc_id)
                            ->where('hoc_vien_id', $data['id'])
                            ->delete();
                        break;

                    case 'sua':
                        HocVienKhoaHoc::where('khoa_hoc_id', $yeuCau->khoa_hoc_id)
                            ->where('hoc_vien_id', $data['id'])
                            ->update(['ghi_chu' => $yeuCau->ly_do]);
                        break;
                }
            }

            $yeuCau->update([
                'trang_thai'      => $request->hanh_dong,
                'admin_duyet_id'  => auth()->id(),
                'thoi_gian_duyet' => now(),
                'phan_hoi_admin'  => $request->phan_hoi
            ]);

            DB::commit();
            return back()->with('success', 'Đã xử lý yêu cầu thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return back()->with('error', 'Không thể xử lý yêu cầu học viên lúc này. Vui lòng thử lại.');
        }
    }
}
