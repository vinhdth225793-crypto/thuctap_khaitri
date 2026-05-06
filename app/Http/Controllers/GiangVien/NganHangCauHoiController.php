<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\ModuleHoc;
use App\Models\NganHangCauHoi;
use App\Models\PhanCongModuleGiangVien;
use Illuminate\Http\Request;

class NganHangCauHoiController extends Controller
{
    /**
     * Trang ngân hàng câu hỏi của giảng viên — 2 tab:
     *   - "Của tôi" (default): câu hỏi do GV này tạo
     *   - "Đã công bố": câu hỏi admin đã công bố cho mọi GV
     */
    public function index(Request $request)
    {
        $userId = (int) auth()->user()->id;
        $tab = in_array($request->query('tab'), ['cua-toi', 'cong-bo'], true)
            ? $request->query('tab')
            : 'cua-toi';

        $keyword = trim((string) $request->query('keyword', ''));
        $loaiCauHoi = $request->query('loai_cau_hoi');
        $mucDo = $request->query('muc_do');
        $khoaHocId = $request->query('khoa_hoc_id');

        $query = NganHangCauHoi::query()
            ->with(['khoaHoc', 'moduleHoc', 'nguoiTao', 'dapAns', 'nguoiCongBo']);

        if ($tab === 'cua-toi') {
            $query->cuaToi($userId);
        } else {
            $query->daCongBo();
        }

        if (filled($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('noi_dung', 'like', '%' . $keyword . '%')
                    ->orWhere('ma_cau_hoi', 'like', '%' . $keyword . '%');
            });
        }

        if (filled($loaiCauHoi)) {
            $query->where('loai_cau_hoi', $loaiCauHoi);
        }

        if (filled($mucDo)) {
            $query->where('muc_do', $mucDo);
        }

        if (filled($khoaHocId)) {
            $query->where('khoa_hoc_id', (int) $khoaHocId);
        }

        $cauHois = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        // Bộ đếm cho 2 tab
        $countCuaToi = NganHangCauHoi::cuaToi($userId)->count();
        $countCongBo = NganHangCauHoi::daCongBo()->count();

        // Khoá học GV được phân công (để filter)
        $giangVienId = auth()->user()->giangVien?->id;
        $khoaHocs = collect();
        if ($giangVienId) {
            $khoaHocIds = PhanCongModuleGiangVien::query()
                ->where('giang_vien_id', $giangVienId)
                ->pluck('khoa_hoc_id')
                ->unique()
                ->values();

            if ($khoaHocIds->isNotEmpty()) {
                $khoaHocs = KhoaHoc::whereIn('id', $khoaHocIds)->get(['id', 'ten_khoa_hoc']);
            }
        }

        return view('pages.giang-vien.ngan-hang-cau-hoi.index', [
            'cauHois' => $cauHois,
            'tab' => $tab,
            'countCuaToi' => $countCuaToi,
            'countCongBo' => $countCongBo,
            'keyword' => $keyword,
            'loaiCauHoi' => $loaiCauHoi,
            'mucDo' => $mucDo,
            'khoaHocId' => $khoaHocId,
            'khoaHocs' => $khoaHocs,
            'difficultyOptions' => [
                'de' => 'Dễ',
                'trung_binh' => 'Trung bình',
                'kho' => 'Khó',
            ],
            'questionTypeOptions' => [
                NganHangCauHoi::LOAI_TRAC_NGHIEM => 'Trắc nghiệm',
                NganHangCauHoi::LOAI_TU_LUAN => 'Tự luận',
            ],
        ]);
    }

    /**
     * Xem chi tiết 1 câu hỏi (dùng cho preview).
     * Giảng viên chỉ xem được câu hỏi của mình hoặc đã công bố.
     */
    public function show($id)
    {
        $userId = (int) auth()->user()->id;
        $cauHoi = NganHangCauHoi::with(['khoaHoc', 'moduleHoc', 'nguoiTao', 'dapAns', 'nguoiCongBo'])
            ->truyCapBoiGiangVien($userId)
            ->findOrFail($id);

        return view('pages.giang-vien.ngan-hang-cau-hoi.show', compact('cauHoi'));
    }

    /**
     * Xoá câu hỏi (chỉ GV tạo ra mới được xoá).
     */
    public function destroy($id)
    {
        $userId = (int) auth()->user()->id;
        $cauHoi = NganHangCauHoi::cuaToi($userId)->findOrFail($id);

        $cauHoi->dapAns()->delete();
        $cauHoi->delete();

        return back()->with('success', 'Đã xoá câu hỏi khỏi ngân hàng của bạn.');
    }

    /**
     * GV tự công bố câu hỏi của mình cho mọi giảng viên cùng dùng.
     * Chỉ áp dụng được cho câu hỏi do GV này tạo ra.
     */
    public function togglePublic($id)
    {
        $userId = (int) auth()->user()->id;
        $cauHoi = NganHangCauHoi::cuaToi($userId)->findOrFail($id);

        if ($cauHoi->is_cong_bo) {
            $cauHoi->update([
                'pham_vi' => NganHangCauHoi::PHAM_VI_RIENG_TU,
                'cong_bo_luc' => null,
                'cong_bo_boi_id' => null,
            ]);
            $message = 'Đã thu hồi công bố — câu hỏi trở về riêng tư.';
        } else {
            $cauHoi->update([
                'pham_vi' => NganHangCauHoi::PHAM_VI_CONG_BO,
                'cong_bo_luc' => now(),
                'cong_bo_boi_id' => $userId,
            ]);
            $message = 'Đã công bố — mọi giảng viên đều có thể dùng câu hỏi này.';
        }

        return back()->with('success', $message);
    }

    /**
     * Bulk toggle public cho nhiều câu hỏi cùng lúc (chỉ câu hỏi của chính GV).
     */
    public function bulkTogglePublic(Request $request)
    {
        $userId = (int) auth()->user()->id;
        $action = $request->input('action'); // publish | unpublish
        $ids = $request->input('ids', []);

        if (! is_array($ids) || empty($ids) || ! in_array($action, ['publish', 'unpublish'], true)) {
            return back()->with('error', 'Yêu cầu không hợp lệ.');
        }

        $ids = array_map('intval', $ids);
        $query = NganHangCauHoi::cuaToi($userId)->whereIn('id', $ids);

        if ($action === 'publish') {
            $count = $query->update([
                'pham_vi' => NganHangCauHoi::PHAM_VI_CONG_BO,
                'cong_bo_luc' => now(),
                'cong_bo_boi_id' => $userId,
            ]);
            return back()->with('success', "Đã công bố {$count} câu hỏi cho mọi giảng viên.");
        }

        $count = $query->update([
            'pham_vi' => NganHangCauHoi::PHAM_VI_RIENG_TU,
            'cong_bo_luc' => null,
            'cong_bo_boi_id' => null,
        ]);
        return back()->with('success', "Đã thu hồi công bố {$count} câu hỏi.");
    }
}
