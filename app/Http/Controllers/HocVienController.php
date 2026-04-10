<?php

namespace App\Http\Controllers;

use App\Models\BaiGiang;
use App\Models\HocVienKhoaHoc;
use App\Models\KetQuaHocTap;
use App\Models\KhoaHoc;
use App\Models\YeuCauHocVien;
use App\Services\StudentLearningDashboardService;
use App\Services\StudentScheduleViewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HocVienController extends Controller
{
    public function __construct(
        private readonly StudentLearningDashboardService $dashboardService,
        private readonly StudentScheduleViewService $scheduleViewService,
    ) {
        $this->middleware(['auth', \App\Http\Middleware\CheckHocVien::class]);
    }

    public function dashboard()
    {
        return view('pages.hoc-vien.dashboard', $this->dashboardService->buildFor(auth()->user()));
    }

    public function hoatDongVaTienDo()
    {
        return view('pages.hoc-vien.hoat-dong-tien-do', $this->dashboardService->buildFor(auth()->user()));
    }

    public function ketQuaHocTap()
    {
        $user = auth()->user();
        
        $khoaHocThamGia = HocVienKhoaHoc::with(['khoaHoc.moduleHocs'])
            ->where('hoc_vien_id', $user->hocVien->id)
            ->get();

        $resultsByCourse = [];
        
        foreach ($khoaHocThamGia as $enrollment) {
            $khoaHoc = $enrollment->khoaHoc;
            
            // Lấy tất cả kết quả của học viên trong khóa học này (phân cấp)
            $allResults = KetQuaHocTap::with(['moduleHoc', 'baiKiemTra'])
                ->where('hoc_vien_id', $user->hocVien->id)
                ->where('khoa_hoc_id', $khoaHoc->id)
                ->get();

            $resultsByCourse[$khoaHoc->id] = [
                'khoa_hoc' => $khoaHoc,
                'course_result' => $allResults->whereNull('module_hoc_id')->whereNull('bai_kiem_tra_id')->first(),
                'module_results' => $allResults->whereNotNull('module_hoc_id')->whereNull('bai_kiem_tra_id')->values(),
                'exam_results' => $allResults->whereNotNull('bai_kiem_tra_id')->values(),
            ];
        }

        // Thống kê tổng quan
        $stats = [
            'tong_khoa_hoc' => count($resultsByCourse),
            'khoa_hoc_dat' => collect($resultsByCourse)->filter(fn($c) => optional($c['course_result'])->trang_thai === 'dat')->count(),
            'khoa_hoc_truot' => collect($resultsByCourse)->filter(fn($c) => optional($c['course_result'])->trang_thai === 'khong_dat')->count(),
            'diem_trung_binh_chung' => collect($resultsByCourse)->whereNotNull('course_result')->avg('course_result.diem_tong_ket'),
        ];

        return view('pages.hoc-vien.ket-qua.index', compact('resultsByCourse', 'stats'));
    }

    /**
     * Danh sach khoa hoc cua hoc vien.
     */
    public function khoaHocCuaToi()
    {
        $user = auth()->user();

        $baseQuery = HocVienKhoaHoc::query()
            ->where('hoc_vien_id', $user->hocVien->id)
            ->whereHas('khoaHoc');

        $stats = [
            'tong' => (clone $baseQuery)->count(),
            'dang_hoc' => (clone $baseQuery)->where('trang_thai', 'dang_hoc')->count(),
            'hoan_thanh' => (clone $baseQuery)->where('trang_thai', 'hoan_thanh')->count(),
            'ngung_hoc' => (clone $baseQuery)->where('trang_thai', 'ngung_hoc')->count(),
        ];

        $khoaHocs = $baseQuery
            ->with([
                'khoaHoc' => fn ($query) => $query->with('nhomNganh'),
            ])
            ->orderByRaw("
                CASE trang_thai
                    WHEN 'dang_hoc' THEN 1
                    WHEN 'hoan_thanh' THEN 2
                    WHEN 'ngung_hoc' THEN 3
                    ELSE 4
                END
            ")
            ->orderByDesc('ngay_tham_gia')
            ->orderByDesc('created_at')
            ->paginate(9);

        return view('pages.hoc-vien.khoa-hoc.index', compact('khoaHocs', 'stats'));
    }

    public function khoaHocCoTheThamGia()
    {
        $user = auth()->user();

        $daThamGiaIds = HocVienKhoaHoc::query()
            ->where('hoc_vien_id', $user->hocVien->id)
            ->pluck('khoa_hoc_id');

        $dangChoDuyetIds = YeuCauHocVien::query()
            ->where('hoc_vien_id', $user->hocVien->id)
            ->where('loai_yeu_cau', 'them')
            ->where('trang_thai', 'cho_duyet')
            ->pluck('khoa_hoc_id');

        $khoaHocs = KhoaHoc::query()
            ->active()
            ->hoatDong()
            ->whereIn('trang_thai_van_hanh', ['cho_giang_vien', 'san_sang', 'dang_day'])
            ->whereNotIn('id', $daThamGiaIds)
            ->with(['nhomNganh'])
            ->withCount([
                'moduleHocs',
                'hocVienKhoaHocs as hoc_vien_dang_hoc_count' => fn ($query) => $query->where('trang_thai', 'dang_hoc'),
            ])
            ->orderByRaw("
                CASE trang_thai_van_hanh
                    WHEN 'san_sang' THEN 1
                    WHEN 'dang_day' THEN 2
                    WHEN 'cho_giang_vien' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('ngay_khai_giang')
            ->paginate(9);

        $yeuCauDaGui = YeuCauHocVien::query()
            ->with(['khoaHoc.nhomNganh', 'admin'])
            ->where('hoc_vien_id', $user->hocVien->id)
            ->where('loai_yeu_cau', 'them')
            ->orderByRaw("
                CASE trang_thai
                    WHEN 'cho_duyet' THEN 1
                    WHEN 'tu_choi' THEN 2
                    WHEN 'da_duyet' THEN 3
                    ELSE 4
                END
            ")
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'co_the_tham_gia' => $khoaHocs->total(),
            'dang_cho_duyet' => $dangChoDuyetIds->count(),
            'da_gui' => $yeuCauDaGui->count(),
        ];

        return view('pages.hoc-vien.khoa-hoc.tham-gia', [
            'khoaHocs' => $khoaHocs,
            'yeuCauDaGui' => $yeuCauDaGui,
            'dangChoDuyetIds' => $dangChoDuyetIds->all(),
            'stats' => $stats,
        ]);
    }

    public function guiYeuCauThamGia(Request $request, int $khoaHocId)
    {
        $user = auth()->user();

        $request->validate([
            'ly_do' => 'required|string|max:1000',
        ]);

        $khoaHoc = KhoaHoc::query()
            ->active()
            ->hoatDong()
            ->whereIn('trang_thai_van_hanh', ['cho_giang_vien', 'san_sang', 'dang_day'])
            ->findOrFail($khoaHocId);

        $daThamGia = HocVienKhoaHoc::query()
            ->where('khoa_hoc_id', $khoaHoc->id)
            ->where('hoc_vien_id', $user->hocVien->id)
            ->exists();

        if ($daThamGia) {
            return back()->with('error', 'Bạn đã ở trong khóa học này rồi.');
        }

        $dangChoDuyet = YeuCauHocVien::query()
            ->where('khoa_hoc_id', $khoaHoc->id)
            ->where('hoc_vien_id', $user->hocVien->id)
            ->where('loai_yeu_cau', 'them')
            ->where('trang_thai', 'cho_duyet')
            ->exists();

        if ($dangChoDuyet) {
            return back()->with('error', 'Bạn đã gửi yêu cầu tham gia khóa học này và đang chờ duyệt.');
        }

        YeuCauHocVien::create([
            'khoa_hoc_id' => $khoaHoc->id,
            'giang_vien_id' => null,
            'hoc_vien_id' => $user->hocVien->id,
            'loai_yeu_cau' => 'them',
            'du_lieu_yeu_cau' => [
                'id' => $user->id,
                'ten' => $user->ho_ten,
                'email' => $user->email,
            ],
            'ly_do' => $request->ly_do,
            'trang_thai' => 'cho_duyet',
        ]);

        return redirect()
            ->route('hoc-vien.khoa-hoc-tham-gia')
            ->with('success', 'Đã gửi yêu cầu tham gia khóa học. Vui lòng chờ admin duyệt.');
    }

    /**
     * Chi tiet khoa hoc va lo trinh hoc tap.
     */
    public function chiTietKhoaHoc($id)
    {
        $data = $this->scheduleViewService->buildCourseDetail(auth()->user(), (int) $id);

        if (!$data) {
            return redirect()->route('hoc-vien.khoa-hoc-cua-toi')->with('error', 'Bạn không có quyền truy cập khóa học này.');
        }

        return view('pages.hoc-vien.khoa-hoc.show', $data);
    }

    public function chiTietBuoiHoc($id)
    {
        $data = $this->scheduleViewService->buildSessionDetail(auth()->user(), (int) $id);

        if (!$data) {
            return redirect()->route('hoc-vien.khoa-hoc-cua-toi')->with('error', 'Bạn không có quyền truy cập buổi học này.');
        }

        return view('pages.hoc-vien.buoi-hoc.show', $data);
    }

    public function chiTietBaiGiang($id)
    {
        $baiGiang = BaiGiang::with([
            'taiNguyenChinh',
            'taiNguyenPhu',
            'khoaHoc',
            'moduleHoc',
            'phongHocLive',
            'lichHoc',
        ])
            ->hienThiChoHocVien()
            ->findOrFail($id);

        $daGhiDanh = HocVienKhoaHoc::where('khoa_hoc_id', $baiGiang->khoa_hoc_id)
            ->where('hoc_vien_id', auth()->user()->hocVien->id)
            ->whereIn('trang_thai', ['dang_hoc', 'hoan_thanh'])
            ->exists();

                        if (!$daGhiDanh) {
            return redirect()->route('hoc-vien.khoa-hoc-cua-toi')->with('error', 'Bạn chưa đăng ký khóa học này.');
        }

        if ($baiGiang->isLive() && $baiGiang->phongHocLive) {
            return redirect()->route('hoc-vien.live-room.show', $baiGiang->id);
        }

        $backUrl = $baiGiang->lich_hoc_id
            ? route('hoc-vien.buoi-hoc.show', $baiGiang->lich_hoc_id)
            : route('hoc-vien.chi-tiet-khoa-hoc', $baiGiang->khoa_hoc_id);

        return view('pages.hoc-vien.bai-giang.show', compact('baiGiang', 'backUrl'));
    }

    public function profile()
    {
        $user = auth()->user();
        $user->load('hocVien');

        return view('pages.hoc-vien.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|email|unique:nguoi_dung,email,' . $user->id . ',id',
            'so_dien_thoai' => 'nullable|string|max:15',
            'ngay_sinh' => 'nullable|date|before:today',
            'dia_chi' => 'nullable|string|max:500',
            'anh_dai_dien' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'mat_khau' => 'nullable|min:8|confirmed',
            'lop' => 'nullable|string|max:50',
            'nganh' => 'nullable|string|max:255',
            'diem_trung_binh' => 'nullable|numeric|min:0|max:10',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['ho_ten', 'email', 'so_dien_thoai', 'ngay_sinh', 'dia_chi', 'trang_thai']);

        if ($request->filled('mat_khau')) {
            $data['mat_khau'] = Hash::make($request->mat_khau);
        }

        if ($request->hasFile('anh_dai_dien')) {
            if ($user->anh_dai_dien && Storage::disk('public')->exists($user->anh_dai_dien)) {
                Storage::disk('public')->delete($user->anh_dai_dien);
            }

            $data['anh_dai_dien'] = $request->file('anh_dai_dien')->store('avatars', 'public');
        }

        if ($request->has('xoa_anh_dai_dien') && $user->anh_dai_dien) {
            Storage::disk('public')->delete($user->anh_dai_dien);
            $data['anh_dai_dien'] = null;
        }

        $user->update($data);

        $hv = $user->hocVien;
        if (!$hv) {
            $hv = $user->hocVien()->create([]);
        }

        $hv->update($request->only(['lop', 'nganh', 'diem_trung_binh']));

        return redirect()->route('hoc-vien.profile')->with('success', 'Cập nhật thông tin thành công.');
    }
}
