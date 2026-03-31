<?php

namespace App\Http\Controllers;

use App\Models\BaiGiang;
use App\Models\DiemDanh;
use App\Models\HocVienKhoaHoc;
use App\Models\KetQuaHocTap;
use App\Models\KhoaHoc;
use App\Models\LichHoc;
use App\Models\TaiNguyenBuoiHoc;
use App\Models\YeuCauHocVien;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HocVienController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\CheckHocVien::class]);
    }

    public function dashboard()
    {
        return view('pages.hoc-vien.dashboard', $this->buildHocTapData(auth()->user()));
    }

    public function hoatDongVaTienDo()
    {
        return view('pages.hoc-vien.hoat-dong-tien-do', $this->buildHocTapData(auth()->user()));
    }

    /**
     * Danh sach khoa hoc cua hoc vien (Phase 2)
     */
    public function khoaHocCuaToi()
    {
        $user = auth()->user();

        $baseQuery = HocVienKhoaHoc::query()
            ->where('hoc_vien_id', $user->ma_nguoi_dung)
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
            ->where('hoc_vien_id', $user->ma_nguoi_dung)
            ->pluck('khoa_hoc_id');

        $dangChoDuyetIds = YeuCauHocVien::query()
            ->where('hoc_vien_id', $user->ma_nguoi_dung)
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
            ->where('hoc_vien_id', $user->ma_nguoi_dung)
            ->where('loai_yeu_cau', 'them')
            ->orderByRaw("FIELD(trang_thai, 'cho_duyet', 'tu_choi', 'da_duyet')")
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
            ->where('hoc_vien_id', $user->ma_nguoi_dung)
            ->exists();

        if ($daThamGia) {
            return back()->with('error', 'Bạn đã ở trong khóa học này rồi.');
        }

        $dangChoDuyet = YeuCauHocVien::query()
            ->where('khoa_hoc_id', $khoaHoc->id)
            ->where('hoc_vien_id', $user->ma_nguoi_dung)
            ->where('loai_yeu_cau', 'them')
            ->where('trang_thai', 'cho_duyet')
            ->exists();

        if ($dangChoDuyet) {
            return back()->with('error', 'Bạn đã gửi yêu cầu tham gia khóa học này và đang chờ duyệt.');
        }

        YeuCauHocVien::create([
            'khoa_hoc_id' => $khoaHoc->id,
            'giang_vien_id' => null,
            'hoc_vien_id' => $user->ma_nguoi_dung,
            'loai_yeu_cau' => 'them',
            'du_lieu_yeu_cau' => [
                'id' => $user->ma_nguoi_dung,
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
     * Chi tiet khoa hoc va xem buoi hoc theo module (Phase 3)
     */
    public function chiTietKhoaHoc($id)
    {
        $user = auth()->user();

        $ghiDanh = HocVienKhoaHoc::where('khoa_hoc_id', $id)
            ->where('hoc_vien_id', $user->ma_nguoi_dung)
            ->first();

        if (!$ghiDanh || $ghiDanh->trang_thai === 'ngung_hoc') {
            return redirect()->route('hoc-vien.khoa-hoc-cua-toi')->with('error', 'Bạn không có quyền truy cập khóa học này.');
        }

        $khoaHoc = KhoaHoc::with([
            'nhomNganh',
            'moduleHocs' => function ($query) use ($id) {
                $query->orderBy('thu_tu_module')
                    ->with([
                        'lichHocs' => function ($lichHocQuery) use ($id) {
                            $lichHocQuery->where('khoa_hoc_id', $id)
                                ->with([
                                    'baiGiangs' => function ($bgQuery) {
                                        $bgQuery->hienThiChoHocVien()
                                            ->with('phongHocLive')
                                            ->orderBy('thu_tu_hien_thi');
                                    },
                                ])
                                ->orderBy('ngay_hoc')
                                ->orderBy('gio_bat_dau');
                        },
                    ]);
            },
        ])->findOrFail($id);

        $stats = [
            'tong_module' => $khoaHoc->moduleHocs->count(),
            'module_hoan_thanh' => $khoaHoc->so_module_hoan_thanh,
            'module_co_lich' => $khoaHoc->moduleHocs->filter(fn ($module) => $module->lichHocs->isNotEmpty())->count(),
            'tong_buoi_hoc' => $khoaHoc->moduleHocs->sum(fn ($module) => $module->lichHocs->count()),
            'buoi_hoan_thanh' => $khoaHoc->moduleHocs->sum(fn ($module) => $module->so_buoi_hoan_thanh),
            'buoi_online' => $khoaHoc->moduleHocs->sum(fn ($module) => $module->lichHocs->where('hinh_thuc', 'online')->count()),
        ];

        return view('pages.hoc-vien.khoa-hoc.show', compact('khoaHoc', 'ghiDanh', 'stats'));
    }

    public function chiTietBaiGiang($id)
    {
        $baiGiang = BaiGiang::with(['taiNguyenChinh', 'taiNguyenPhu', 'khoaHoc', 'moduleHoc', 'phongHocLive'])
            ->hienThiChoHocVien()
            ->findOrFail($id);

        // Kiểm tra học viên có đăng ký khóa học này không
        $daGhiDanh = HocVienKhoaHoc::where('khoa_hoc_id', $baiGiang->khoa_hoc_id)
            ->where('hoc_vien_id', auth()->user()->ma_nguoi_dung)
            ->whereIn('trang_thai', ['dang_hoc', 'hoan_thanh'])
            ->exists();

        if (!$daGhiDanh) {
            return redirect()->route('hoc-vien.khoa-hoc-cua-toi')->with('error', 'Bạn chưa đăng ký khóa học này.');
        }

        if ($baiGiang->isLive() && $baiGiang->phongHocLive) {
            return redirect()->route('hoc-vien.live-room.show', $baiGiang->id);
        }

        return view('pages.hoc-vien.bai-giang.show', compact('baiGiang'));
    }

    private function buildHocTapData($user): array
    {
        $user->loadMissing('hocVien');

        $ghiDanhKhoaHoc = HocVienKhoaHoc::query()
            ->where('hoc_vien_id', $user->ma_nguoi_dung)
            ->whereHas('khoaHoc')
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
            ->get();

        $tatCaKhoaHocIds = $ghiDanhKhoaHoc->pluck('khoa_hoc_id')->filter()->values();
        $khoaHocDangHocIds = $ghiDanhKhoaHoc
            ->where('trang_thai', 'dang_hoc')
            ->pluck('khoa_hoc_id')
            ->filter()
            ->values();
        $khoaHocDangHocIdSet = $khoaHocDangHocIds
            ->map(fn ($id) => (int) $id)
            ->all();

        $lichHocTheoKhoaHoc = collect();
        if ($tatCaKhoaHocIds->isNotEmpty()) {
            $lichHocTheoKhoaHoc = LichHoc::query()
                ->with([
                    'khoaHoc:id,ten_khoa_hoc,ma_khoa_hoc',
                    'moduleHoc:id,ten_module,ma_module',
                    'baiGiangs' => function ($query) {
                        $query->where('loai_bai_giang', BaiGiang::TYPE_LIVE)
                            ->where('trang_thai_duyet', BaiGiang::STATUS_DUYET_DA_DUYET)
                            ->where('trang_thai_cong_bo', BaiGiang::CONG_BO_DA_CONG_BO)
                            ->with('phongHocLive');
                    },
                ])
                ->whereIn('khoa_hoc_id', $tatCaKhoaHocIds->all())
                ->where('trang_thai', '!=', 'huy')
                ->get([
                    'id',
                    'khoa_hoc_id',
                    'module_hoc_id',
                    'ngay_hoc',
                    'gio_bat_dau',
                    'gio_ket_thuc',
                    'hinh_thuc',
                    'link_online',
                    'trang_thai',
                ])
                ->groupBy('khoa_hoc_id');
        }

        $homNay = now()->startOfDay();
        $hienTai = now();
        $tatCaLichHoc = $lichHocTheoKhoaHoc->flatten(1)->values();
        $lichHocCuaKhoaDangHoc = $tatCaLichHoc
            ->filter(fn (LichHoc $lichHoc) => in_array((int) $lichHoc->khoa_hoc_id, $khoaHocDangHocIdSet, true))
            ->values();
        $cacBuoiSapToi = $lichHocCuaKhoaDangHoc
            ->filter(fn (LichHoc $lichHoc) => $this->shouldShowInUpcomingList($lichHoc, $hienTai))
            ->sortBy(fn (LichHoc $lichHoc) => $lichHoc->starts_at?->getTimestamp() ?? PHP_INT_MAX)
            ->values();

        $taiLieuCongKhaiQuery = TaiNguyenBuoiHoc::query()
            ->hienThi()
            ->whereHas('lichHoc', function ($query) use ($tatCaKhoaHocIds) {
                $query->whereIn('khoa_hoc_id', $tatCaKhoaHocIds->all());
            });

        $diemDanhTheoKhoaHoc = collect();
        $diemDanhGanDay = collect();

        if ($tatCaKhoaHocIds->isNotEmpty()) {
            $diemDanhGanDay = DiemDanh::query()
                ->where('hoc_vien_id', $user->ma_nguoi_dung)
                ->whereHas('lichHoc', function ($query) use ($tatCaKhoaHocIds) {
                    $query->whereIn('khoa_hoc_id', $tatCaKhoaHocIds->all());
                })
                ->with([
                    'lichHoc:id,khoa_hoc_id,module_hoc_id,ngay_hoc,gio_bat_dau,trang_thai',
                    'lichHoc.khoaHoc:id,ten_khoa_hoc,ma_khoa_hoc',
                    'lichHoc.moduleHoc:id,ten_module,ma_module',
                ])
                ->orderByDesc('updated_at')
                ->orderByDesc('created_at')
                ->get();

            $diemDanhTheoKhoaHoc = $diemDanhGanDay->groupBy(fn ($item) => optional($item->lichHoc)->khoa_hoc_id);
        }

        $ketQuaHocTapTheoKhoaHoc = KetQuaHocTap::query()
            ->where('hoc_vien_id', $user->ma_nguoi_dung)
            ->whereIn('khoa_hoc_id', $tatCaKhoaHocIds->all())
            ->get()
            ->keyBy('khoa_hoc_id');

        $soCoMat = $diemDanhGanDay->where('trang_thai', 'co_mat')->count();
        $soVaoTre = $diemDanhGanDay->where('trang_thai', 'vao_tre')->count();
        $soVangMat = $diemDanhGanDay->where('trang_thai', 'vang_mat')->count();
        $tongLanDiemDanh = $diemDanhGanDay->count();
        $tyLeChuyenCan = $tongLanDiemDanh > 0
            ? (int) round((($soCoMat + $soVaoTre) / $tongLanDiemDanh) * 100)
            : 0;

        $yeuCauThamGiaDangChoDuyet = YeuCauHocVien::query()
            ->where('hoc_vien_id', $user->ma_nguoi_dung)
            ->where('loai_yeu_cau', 'them')
            ->where('trang_thai', 'cho_duyet')
            ->count();

        $tongBuoiHoc = $tatCaLichHoc->count();
        $tongBuoiHoanThanh = $tatCaLichHoc
            ->filter(fn (LichHoc $lichHoc) => $lichHoc->is_ended)
            ->count();
        $tienDoTongQuan = $tongBuoiHoc > 0
            ? (int) round(($tongBuoiHoanThanh / $tongBuoiHoc) * 100)
            : 0;

        $dashboardStats = [
            'khoa_hoc_dang_hoc' => $ghiDanhKhoaHoc->where('trang_thai', 'dang_hoc')->count(),
            'buoi_hoc_hom_nay' => $lichHocCuaKhoaDangHoc
                ->filter(fn (LichHoc $lichHoc) => $lichHoc->ngay_hoc && $lichHoc->ngay_hoc->isSameDay($homNay))
                ->count(),
            'buoi_hoc_sap_toi' => $cacBuoiSapToi->count(),
            'tai_lieu_cong_khai' => (clone $taiLieuCongKhaiQuery)->count(),
            'tai_lieu_moi_7_ngay' => (clone $taiLieuCongKhaiQuery)->where('created_at', '>=', now()->subDays(7))->count(),
            'buoi_co_tai_lieu' => (clone $taiLieuCongKhaiQuery)->distinct('lich_hoc_id')->count('lich_hoc_id'),
            'tien_do_tong_quan' => $tienDoTongQuan,
            'tong_buoi_hoc' => $tongBuoiHoc,
            'tong_buoi_hoan_thanh' => $tongBuoiHoanThanh,
            'yeu_cau_dang_cho_duyet' => $yeuCauThamGiaDangChoDuyet,
            'tong_lan_diem_danh' => $tongLanDiemDanh,
            'ty_le_chuyen_can' => $tyLeChuyenCan,
        ];

        $tienDoKhoaHoc = $ghiDanhKhoaHoc->map(function (HocVienKhoaHoc $ghiDanh) use ($lichHocTheoKhoaHoc, $diemDanhTheoKhoaHoc, $ketQuaHocTapTheoKhoaHoc, $hienTai) {
            $cacBuoiHoc = $lichHocTheoKhoaHoc->get($ghiDanh->khoa_hoc_id, collect());
            $tongBuoi = $cacBuoiHoc->count();
            $buoiHoanThanh = $cacBuoiHoc
                ->filter(fn (LichHoc $lichHoc) => $lichHoc->is_ended)
                ->count();
            $buoiOnline = $cacBuoiHoc->where('hinh_thuc', 'online')->count();
            $tienDo = $tongBuoi > 0
                ? (int) round(($buoiHoanThanh / $tongBuoi) * 100)
                : 0;

            $diemDanhTheoKhoa = $diemDanhTheoKhoaHoc->get($ghiDanh->khoa_hoc_id, collect());
            $coMat = $diemDanhTheoKhoa->where('trang_thai', 'co_mat')->count();
            $vaoTre = $diemDanhTheoKhoa->where('trang_thai', 'vao_tre')->count();
            $vangMat = $diemDanhTheoKhoa->where('trang_thai', 'vang_mat')->count();
            $tongDiemDanh = $diemDanhTheoKhoa->count();
            $tyLeThamDu = $tongDiemDanh > 0
                ? (int) round((($coMat + $vaoTre) / $tongDiemDanh) * 100)
                : null;

            $buoiSapToi = $cacBuoiHoc
                ->filter(fn (LichHoc $lichHoc) => $this->shouldShowInUpcomingList($lichHoc, $hienTai))
                ->sortBy(fn (LichHoc $lichHoc) => $lichHoc->starts_at?->getTimestamp() ?? PHP_INT_MAX)
                ->first();

            $ketQuaHocTap = $ketQuaHocTapTheoKhoaHoc->get($ghiDanh->khoa_hoc_id);

            return [
                'ghi_danh' => $ghiDanh,
                'khoa_hoc' => $ghiDanh->khoaHoc,
                'tong_buoi' => $tongBuoi,
                'buoi_hoan_thanh' => $buoiHoanThanh,
                'buoi_online' => $buoiOnline,
                'tien_do' => $tienDo,
                'buoi_sap_toi' => $buoiSapToi,
                'co_mat' => $coMat,
                'vao_tre' => $vaoTre,
                'vang_mat' => $vangMat,
                'tong_diem_danh' => $tongDiemDanh,
                'ty_le_tham_du' => $tyLeThamDu,
                'ket_qua_hoc_tap' => $ketQuaHocTap,
            ];
        });

        $buoiSapToi = $cacBuoiSapToi->take(5)->values();

        $taiLieuMoi = (clone $taiLieuCongKhaiQuery)
            ->with([
                'lichHoc.khoaHoc:id,ten_khoa_hoc,ma_khoa_hoc',
                'lichHoc.moduleHoc:id,ten_module,ma_module',
            ])
            ->latest()
            ->take(6)
            ->get();

        $buoiHoanThanhGanDay = $tatCaLichHoc
            ->filter(fn (LichHoc $lichHoc) => $lichHoc->is_ended)
            ->sortByDesc(fn (LichHoc $lichHoc) => $lichHoc->ends_at?->getTimestamp() ?? ($lichHoc->starts_at?->getTimestamp() ?? 0))
            ->take(6)
            ->values();

        $hoatDongGanDay = $this->buildHoatDongGanDay($diemDanhGanDay, $taiLieuMoi, $buoiHoanThanhGanDay);

        $chuyenCanTongQuan = [
            'co_mat' => $soCoMat,
            'vao_tre' => $soVaoTre,
            'vang_mat' => $soVangMat,
            'tong' => $tongLanDiemDanh,
            'ty_le_tham_du' => $tyLeChuyenCan,
        ];

        return [
            'dashboardStats' => $dashboardStats,
            'tienDoKhoaHoc' => $tienDoKhoaHoc,
            'buoiSapToi' => $buoiSapToi,
            'taiLieuMoi' => $taiLieuMoi,
            'diemDanhGanDay' => $diemDanhGanDay->take(6)->values(),
            'hoatDongGanDay' => $hoatDongGanDay,
            'chuyenCanTongQuan' => $chuyenCanTongQuan,
        ];
    }

    private function shouldShowInUpcomingList(LichHoc $lichHoc, CarbonInterface $referenceTime): bool
    {
        if ($lichHoc->trang_thai === 'huy') {
            return false;
        }

        if ($lichHoc->ends_at) {
            return $lichHoc->ends_at->greaterThanOrEqualTo($referenceTime);
        }

        return $lichHoc->ngay_hoc
            && $lichHoc->ngay_hoc->greaterThanOrEqualTo($referenceTime->copy()->startOfDay());
    }

    private function buildHoatDongGanDay($diemDanhGanDay, $taiLieuMoi, $buoiHoanThanhGanDay)
    {
        $hoatDongDiemDanh = $diemDanhGanDay->map(function ($diemDanh) {
            $trangThai = $diemDanh->trang_thai;

            return [
                'sort_at' => $diemDanh->updated_at ?? $diemDanh->created_at,
                'icon' => match ($trangThai) {
                    'co_mat' => 'fa-check-circle',
                    'vao_tre' => 'fa-clock',
                    'vang_mat' => 'fa-times-circle',
                    default => 'fa-circle',
                },
                'color' => match ($trangThai) {
                    'co_mat' => 'success',
                    'vao_tre' => 'warning',
                    'vang_mat' => 'danger',
                    default => 'secondary',
                },
                'title' => match ($trangThai) {
                    'co_mat' => 'Điểm danh: có mặt',
                    'vao_tre' => 'Điểm danh: vào trễ',
                    'vang_mat' => 'Điểm danh: vắng mặt',
                    default => 'Cập nhật điểm danh',
                },
                'description' => trim(collect([
                    optional(optional($diemDanh->lichHoc)->khoaHoc)->ten_khoa_hoc,
                    optional(optional($diemDanh->lichHoc)->moduleHoc)->ten_module,
                ])->filter()->implode(' • ')),
                'meta' => optional(optional($diemDanh->lichHoc)->ngay_hoc)->format('d/m/Y'),
            ];
        });

        $hoatDongTaiLieu = $taiLieuMoi->map(function ($taiLieu) {
            return [
                'sort_at' => $taiLieu->created_at,
                'icon' => 'fa-folder-open',
                'color' => 'info',
                'title' => 'Tài liệu mới được công khai',
                'description' => $taiLieu->tieu_de,
                'meta' => optional(optional($taiLieu->lichHoc)->khoaHoc)->ten_khoa_hoc,
            ];
        });

        $hoatDongBuoiHoc = $buoiHoanThanhGanDay->map(function ($lichHoc) {
            $sortAt = $lichHoc->ends_at
                ?? ($lichHoc->ngay_hoc
                    ? $lichHoc->ngay_hoc->copy()->setTimeFromTimeString($lichHoc->gio_bat_dau ?: '00:00:00')
                    : $lichHoc->updated_at);

            return [
                'sort_at' => $sortAt,
                'icon' => 'fa-calendar-check',
                'color' => 'primary',
                'title' => 'Hoàn thành buổi học',
                'description' => trim(collect([
                    optional($lichHoc->khoaHoc)->ten_khoa_hoc,
                    optional($lichHoc->moduleHoc)->ten_module,
                ])->filter()->implode(' • ')),
                'meta' => optional($lichHoc->ngay_hoc)->format('d/m/Y'),
            ];
        });

        return $hoatDongDiemDanh
            ->concat($hoatDongTaiLieu)
            ->concat($hoatDongBuoiHoc)
            ->sortByDesc('sort_at')
            ->take(10)
            ->values();
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
            'email' => 'required|email|unique:nguoi_dung,email,' . $user->ma_nguoi_dung . ',ma_nguoi_dung',
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

        return redirect()->route('hoc-vien.profile')->with('success', 'Cap nhat thong tin thanh cong');
    }
}
