<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\NhomNganh;
use App\Models\ModuleHoc;
use App\Models\GiangVien;
use App\Models\PhanCongModuleGiangVien;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KhoaHocManagementController extends Controller
{
    /**
     * index()
     */
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'dang_day');
        $search = trim($request->get('search', ''));
        $nhomNganhId = $request->filled('nhom_nganh_id') ? (int) $request->get('nhom_nganh_id') : null;

        $applySearch = fn ($q) => $q->when($search, function ($query) use ($search) {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('ten_khoa_hoc', 'like', "%{$search}%")
                    ->orWhere('ma_khoa_hoc', 'like', "%{$search}%");
            });
        });

        $applyGroupFilter = fn ($q) => $q->when($nhomNganhId, function ($query) use ($nhomNganhId) {
            $query->where('nhom_nganh_id', $nhomNganhId);
        });

        // 1. Khóa học mẫu
        $khoaHocMau = KhoaHoc::mau()
            ->with(['nhomNganh', 'moduleHocs', 'lopDaMo'])
            ->withCount('lopDaMo')
            ->tap($applyGroupFilter)
            ->tap($applySearch)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page_mau');

        // Lấy tất cả khóa học hoạt động để phân loại
        $allHoatDong = KhoaHoc::hoatDong()
            ->with(['nhomNganh', 'moduleHocs.lichHocs', 'khoaHocMau'])
            ->withCount(['moduleHocs as module_xac_nhan_count' => function($q) {
                $q->whereHas('phanCongGiangViens', fn($q2) => $q2->where('trang_thai', 'da_nhan'));
            }])
            ->tap($applyGroupFilter)
            ->tap($applySearch)
            ->get();

        // 2. Lớp đang giảng dạy (Chưa hoàn thành 100% và trạng thái là dang_day)
        $khoaHocDangDayRaw = $allHoatDong->filter(function($kh) {
            return $kh->trang_thai_van_hanh === 'dang_day' && (int)$kh->tien_do_hoc_tap < 100;
        });
        $khoaHocDangDay = $this->paginateCollection($khoaHocDangDayRaw, 10, 'page_dd');

        // 3. Lớp chờ giảng viên xác nhận
        $khoaHocChoGVRaw = $allHoatDong->filter(function($kh) {
            return $kh->trang_thai_van_hanh === 'cho_giang_vien';
        });
        $khoaHocChoGV = $this->paginateCollection($khoaHocChoGVRaw, 10, 'page_cgv');

        // 4. Lớp sẵn sàng khai giảng
        $khoaHocSanSangRaw = $allHoatDong->filter(function($kh) {
            return $kh->trang_thai_van_hanh === 'san_sang';
        });
        $khoaHocSanSang = $this->paginateCollection($khoaHocSanSangRaw, 10, 'page_ss');

        // 5. Lớp đã hoàn thành (Trạng thái ket_thuc HOẶC tiến độ 100%)
        $khoaHocHoanThanhRaw = $allHoatDong->filter(function($kh) {
            return $kh->trang_thai_van_hanh === 'ket_thuc' || (int)$kh->tien_do_hoc_tap === 100;
        });
        $khoaHocHoanThanh = $this->paginateCollection($khoaHocHoanThanhRaw, 10, 'page_ht');

        $nhomNganhs = NhomNganh::active()
            ->orderBy('ten_nhom_nganh')
            ->get();

        return view('pages.admin.khoa-hoc.khoa-hoc.index', compact(
            'khoaHocMau',
            'khoaHocDangDay',
            'khoaHocChoGV',
            'khoaHocSanSang',
            'khoaHocHoanThanh',
            'nhomNganhs',
            'activeTab',
            'search',
            'nhomNganhId'
        ));
    }

    /**
     * Paginate a collection
     */
    protected function paginateCollection($items, $perPage = 10, $pageName = 'page', $page = null)
    {
        $page = $page ?: (\Illuminate\Pagination\Paginator::resolveCurrentPage($pageName) ?: 1);
        $items = $items instanceof \Illuminate\Support\Collection ? $items : \Illuminate\Support\Collection::make($items);
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]
        );
    }

    /**
     * create()
     */
    public function create()
    {
        $nhomNganhs = NhomNganh::where('trang_thai', 1)->orderBy('ten_nhom_nganh')->get();
        return view('pages.admin.khoa-hoc.khoa-hoc.create', compact('nhomNganhs'));
    }

    /**
     * store()
     */
    public function store(Request $request)
    {
        $request->validate([
            'nhom_nganh_id'  => 'required|exists:nhom_nganh,id',
            'ma_khoa_hoc'    => 'required|string|max:50|unique:khoa_hoc,ma_khoa_hoc',
            'ten_khoa_hoc'   => 'required|string|max:200',
            'cap_do'         => 'required|in:co_ban,trung_binh,nang_cao',
            'mo_ta_ngan'     => 'nullable|string|max:500',
            'mo_ta_chi_tiet' => 'nullable|string',
            'hinh_anh'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'ghi_chu_noi_bo' => 'nullable|string',
            'modules'                       => 'required|array|min:1',
            'modules.*.ten_module'          => 'required|string|max:200',
            'modules.*.thoi_luong_du_kien'  => 'nullable|integer|min:1',
        ], [
            'nhom_nganh_id.required' => 'Vui lòng chọn nhóm ngành',
            'ma_khoa_hoc.required' => 'Mã khóa học là bắt buộc',
            'ma_khoa_hoc.unique' => 'Mã khóa học đã tồn tại',
            'ten_khoa_hoc.required' => 'Tên khóa học là bắt buộc',
            'modules.required' => 'Khóa học phải có ít nhất một module',
        ]);

        DB::beginTransaction();
        try {
            $hinh_anh = null;
            if ($request->hasFile('hinh_anh')) {
                $file = $request->file('hinh_anh');
                $filename = time() . '_' . Str::slug($request->ten_khoa_hoc) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/khoa-hoc'), $filename);
                $hinh_anh = 'images/khoa-hoc/' . $filename;
            }

            $khoaHoc = KhoaHoc::create([
                'nhom_nganh_id'       => $request->nhom_nganh_id,
                'ma_khoa_hoc'         => $request->ma_khoa_hoc,
                'ten_khoa_hoc'        => $request->ten_khoa_hoc,
                'mo_ta_ngan'          => $request->mo_ta_ngan,
                'mo_ta_chi_tiet'      => $request->mo_ta_chi_tiet,
                'hinh_anh'            => $hinh_anh,
                'cap_do'              => $request->cap_do,
                'loai'                => 'mau',
                'trang_thai_van_hanh' => 'cho_mo',
                'ghi_chu_noi_bo'      => $request->ghi_chu_noi_bo,
                'created_by'          => Auth::user()->id,
                'trang_thai'          => true,
            ]);

            foreach ($request->modules as $index => $modData) {
                $thuTu = $index + 1;
                $maModule = $khoaHoc->ma_khoa_hoc . 'M' . str_pad($thuTu, 2, '0', STR_PAD_LEFT);

                ModuleHoc::create([
                    'khoa_hoc_id'        => $khoaHoc->id,
                    'ma_module'          => $maModule,
                    'ten_module'         => $modData['ten_module'],
                    'mo_ta'              => $modData['mo_ta'] ?? null,
                    'thu_tu_module'      => $thuTu,
                    'thoi_luong_du_kien' => filled($modData['thoi_luong_du_kien'] ?? null)
                        ? (int) $modData['thoi_luong_du_kien']
                        : 90,
                    'trang_thai'         => true,
                ]);
            }

            $khoaHoc->update(['tong_so_module' => count($request->modules)]);
            DB::commit();

            return redirect()->route('admin.khoa-hoc.show', $khoaHoc->id)->with('success', 'Tạo khóa học mẫu thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            report($e);

            return redirect()->back()->with('error', 'Không thể tạo khóa học mẫu lúc này. Vui lòng thử lại.')->withInput();
        }
    }

    /**
     * show()
     */
    public function show($id)
    {
        $khoaHoc = KhoaHoc::with([
            'nhomNganh',
            'moduleHocs.lichHocs' => fn($q) => $q->orderBy('ngay_hoc')->orderBy('gio_bat_dau'),
            'moduleHocs.lichHocs.giangVien.nguoiDung',
            'moduleHocs.lichHocs.baiGiangs',
            'moduleHocs.lichHocs.taiNguyen',
            'moduleHocs.lichHocs.diemDanhs',
            'moduleHocs.phanCongGiangViens.giangVien.nguoiDung',
            'moduleHocs.phanCongGiangViens.giangVien.donXinNghis',
            'khoaHocMau',
            'lopDaMo.nhomNganh'
        ])->findOrFail($id);

        $tongModule = $khoaHoc->moduleHocs->count();
        $moduleCoGv = $khoaHoc->moduleHocs->filter(
            fn($m) => $m->phanCongGiangViens->where('trang_thai','da_nhan')->count() > 0
        )->count();

        $giangViens = GiangVien::with([
                'nguoiDung',
                'donXinNghis',
            ])
            ->whereHas('nguoiDung', fn($q) => $q->where('trang_thai', 1))
            ->get();

        return view('pages.admin.khoa-hoc.khoa-hoc.show', compact('khoaHoc', 'giangViens', 'tongModule', 'moduleCoGv'));
    }

    /**
     * showMoLop()
     */
    public function showMoLop($id)
    {
        $khoaHocMau = KhoaHoc::mau()
            ->with(['nhomNganh', 'moduleHocs'])
            ->findOrFail($id);

        $giangViens = GiangVien::with('nguoiDung')
            ->whereHas('nguoiDung', fn($q) => $q->where('trang_thai', 1))
            ->get();

        $soLanDaMo = $khoaHocMau->lopDaMo()->count();
        $maMoiDuKien = $khoaHocMau->ma_khoa_hoc . '-K' . str_pad($soLanDaMo + 1, 2, '0', STR_PAD_LEFT);

        return view('pages.admin.khoa-hoc.khoa-hoc.mo-lop', compact(
            'khoaHocMau', 'giangViens', 'soLanDaMo', 'maMoiDuKien'
        ));
    }

    /**
     * storeMoLop()
     */
    public function storeMoLop(Request $request, $id)
    {
        $khoaHocMau = KhoaHoc::mau()->with('moduleHocs')->findOrFail($id);

        $request->validate([
            'ngay_khai_giang' => 'required|date|after_or_equal:today',
            'ngay_mo_lop'     => 'required|date|after_or_equal:ngay_khai_giang',
            'ngay_ket_thuc'   => 'required|date|after:ngay_mo_lop',
            'ghi_chu_noi_bo'  => 'nullable|string',
            'giang_vien_modules'   => 'nullable|array',
            'giang_vien_modules.*' => 'nullable|exists:giang_vien,id',
        ], [
            'ngay_khai_giang.required' => 'Vui lòng chọn ngày khai giảng',
            'ngay_mo_lop.after_or_equal' => 'Ngày mở lớp phải sau hoặc bằng ngày khai giảng',
            'ngay_ket_thuc.after' => 'Ngày kết thúc phải sau ngày mở lớp',
        ]);

        DB::beginTransaction();
        try {
            $lanThu = $khoaHocMau->lopDaMo()->count() + 1;
            $maMoi = $khoaHocMau->ma_khoa_hoc . '-K' . str_pad($lanThu, 2, '0', STR_PAD_LEFT);

            if (KhoaHoc::where('ma_khoa_hoc', $maMoi)->exists()) {
                throw new \Exception('Mã khóa học ' . $maMoi . ' đã tồn tại. Vui lòng kiểm tra lại.');
            }

            $khoaMoi = KhoaHoc::create([
                'nhom_nganh_id'       => $khoaHocMau->nhom_nganh_id,
                'ma_khoa_hoc'         => $maMoi,
                'ten_khoa_hoc'        => $khoaHocMau->ten_khoa_hoc . ' (Khóa ' . $lanThu . ')',
                'mo_ta_ngan'          => $khoaHocMau->mo_ta_ngan,
                'mo_ta_chi_tiet'      => $khoaHocMau->mo_ta_chi_tiet,
                'hinh_anh'            => $khoaHocMau->hinh_anh,
                'cap_do'              => $khoaHocMau->cap_do,
                'loai'                => 'hoat_dong',
                'trang_thai_van_hanh' => 'cho_mo',
                'khoa_hoc_mau_id'     => $khoaHocMau->id,
                'lan_mo_thu'          => $lanThu,
                'ngay_khai_giang'     => $request->ngay_khai_giang,
                'ngay_mo_lop'         => $request->ngay_mo_lop,
                'ngay_ket_thuc'       => $request->ngay_ket_thuc,
                'ghi_chu_noi_bo'      => $request->ghi_chu_noi_bo,
                'created_by'          => Auth::user()->id,
                'trang_thai'          => true,
            ]);

            $hasGiangVien = false;
            foreach ($khoaHocMau->moduleHocs as $moduleMau) {
                $maModuleMoi = $maMoi . 'M' . str_pad($moduleMau->thu_tu_module, 2, '0', STR_PAD_LEFT);
                $moduleMoi = ModuleHoc::create([
                    'khoa_hoc_id'         => $khoaMoi->id,
                    'ma_module'           => $maModuleMoi,
                    'ten_module'          => $moduleMau->ten_module,
                    'mo_ta'               => $moduleMau->mo_ta,
                    'thu_tu_module'       => $moduleMau->thu_tu_module,
                    'thoi_luong_du_kien'  => $moduleMau->thoi_luong_du_kien,
                    'trang_thai'          => 1,
                ]);

                $giangVienId = $request->giang_vien_modules[$moduleMau->id] ?? null;
                if ($giangVienId) {
                    PhanCongModuleGiangVien::create([
                        'khoa_hoc_id'    => $khoaMoi->id,
                        'module_hoc_id'  => $moduleMoi->id,
                        'giang_vien_id'   => $giangVienId,
                        'ngay_phan_cong' => now(),
                        'trang_thai'     => 'cho_xac_nhan',
                        'created_by'     => Auth::user()->id,
                    ]);
                    $hasGiangVien = true;

                    // Gửi thông báo cho GV
                    $gv = GiangVien::find($giangVienId);
                    ThongBaoService::guiPhanCongGV($gv, $moduleMoi, $khoaMoi);
                }
            }

            if ($hasGiangVien) {
                $khoaMoi->update(['trang_thai_van_hanh' => 'cho_giang_vien']);
            }

            $khoaMoi->update(['tong_so_module' => $khoaHocMau->moduleHocs->count()]);
            DB::commit();

            return redirect()->route('admin.khoa-hoc.show', $khoaMoi->id)->with('success', 'Đã mở lớp thành công! Mã khóa học: ' . $maMoi);
        } catch (\Exception $e) {
            DB::rollback();
            report($e);

            return redirect()->back()->with('error', 'Không thể mở lớp từ khóa học mẫu lúc này. Vui lòng thử lại.')->withInput();
        }
    }

    /**
     * edit()
     */
    public function edit($id)
    {
        $khoaHoc = KhoaHoc::findOrFail($id);
        if ($khoaHoc->loai === 'hoat_dong') {
            return redirect()->back()->with('error', 'Không thể chỉnh sửa trực tiếp khóa học đang hoạt động. Hãy chỉnh sửa thông qua trang chi tiết.');
        }
        $nhomNganhs = NhomNganh::where('trang_thai', 1)->get();
        return view('pages.admin.khoa-hoc.khoa-hoc.edit', compact('khoaHoc', 'nhomNganhs'));
    }

    /**
     * update()
     */
    public function update(Request $request, $id)
    {
        $khoaHoc = KhoaHoc::findOrFail($id);
        if ($khoaHoc->loai === 'hoat_dong') {
            return redirect()->back()->with('error', 'Không thể chỉnh sửa khóa học đang hoạt động.');
        }

        $request->validate([
            'nhom_nganh_id' => 'required|exists:nhom_nganh,id',
            'ten_khoa_hoc'  => 'required|string|max:200',
            'cap_do'        => 'required|in:co_ban,trung_binh,nang_cao',
            'trang_thai'    => 'required|boolean',
        ]);

        $data = $request->only(['nhom_nganh_id', 'ten_khoa_hoc', 'cap_do', 'mo_ta_ngan', 'mo_ta_chi_tiet', 'trang_thai', 'ghi_chu_noi_bo']);

        if ($request->hasFile('hinh_anh')) {
            if ($khoaHoc->hinh_anh && file_exists(public_path($khoaHoc->hinh_anh))) {
                unlink(public_path($khoaHoc->hinh_anh));
            }
            $file = $request->file('hinh_anh');
            $filename = time() . '_' . Str::slug($request->ten_khoa_hoc) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/khoa-hoc'), $filename);
            $data['hinh_anh'] = 'images/khoa-hoc/' . $filename;
        }

        $khoaHoc->update($data);
        return redirect()->route('admin.khoa-hoc.show', $khoaHoc->id)->with('success', 'Cập nhật khóa học mẫu thành công.');
    }

    /**
     * destroy()
     */
    public function destroy($id)
    {
        $khoaHoc = KhoaHoc::findOrFail($id);
        if ($khoaHoc->loai === 'mau' && $khoaHoc->lopDaMo()->exists()) {
            return back()->with('error', 'Không thể xóa mẫu đã có lớp học. Hãy ẩn thay vì xóa.');
        }

        if ($khoaHoc->hinh_anh && file_exists(public_path($khoaHoc->hinh_anh))) {
            unlink(public_path($khoaHoc->hinh_anh));
        }

        $khoaHoc->delete();
        return redirect()->route('admin.khoa-hoc.index')->with('success', 'Xóa khóa học thành công.');
    }

    public function toggleStatus($id)
    {
        $khoaHoc = KhoaHoc::findOrFail($id);
        $khoaHoc->update(['trang_thai' => !$khoaHoc->trang_thai]);
        return redirect()->back()->with('success', 'Đã đổi trạng thái.');
    }

    public function xacNhanMoLop($id)
    {
        $khoaHoc = KhoaHoc::with(['moduleHocs.phanCongGiangViens'])->findOrFail($id);

        if ($khoaHoc->trang_thai_van_hanh !== 'san_sang') {
            return redirect()->route('admin.khoa-hoc.show', $id)
                ->with('error', 'Khóa học chưa sẵn sàng để mở lớp.');
        }

        DB::transaction(function () use ($khoaHoc) {
            $khoaHoc->update(['trang_thai_van_hanh' => 'dang_day']);
        });

        return redirect()->route('admin.khoa-hoc.show', $id)->with('success', 'Đã mở lớp học chính thức!');
    }
}
