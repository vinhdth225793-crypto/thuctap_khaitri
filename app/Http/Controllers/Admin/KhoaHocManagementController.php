<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\MonHoc;
use App\Models\ModuleHoc;
use App\Models\GiangVien;
use App\Models\PhanCongModuleGiangVien;
use App\Services\ThongBaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KhoaHocManagementController extends Controller
{
    /**
     * index() — Phase 1
     */
    public function index(Request $request)
    {
        $tab    = $request->get('tab', 'mau');
        $search = trim($request->get('search', ''));

        $applySearch = fn($q) => $q->when($search,
            fn($q) => $q->where('ten_khoa_hoc','like',"%{$search}%")
                        ->orWhere('ma_khoa_hoc','like',"%{$search}%")
        );

        // Khóa học mẫu - Sắp xếp theo tên môn học trước
        $khoaHocMau = KhoaHoc::with('monHoc')
            ->select('khoa_hoc.*')
            ->leftJoin('mon_hoc', 'khoa_hoc.mon_hoc_id', '=', 'mon_hoc.id')
            ->mau()->tap($applySearch)
            ->orderBy('mon_hoc.ten_mon_hoc')
            ->orderByDesc('khoa_hoc.created_at')
            ->paginate(12, ['*'], 'mau_page')
            ->appends($request->query());

        // Khóa học đang hoạt động - Sắp xếp theo tên môn học trước
        $khoaHocHoatDong = KhoaHoc::with('monHoc')
            ->select('khoa_hoc.*')
            ->leftJoin('mon_hoc', 'khoa_hoc.mon_hoc_id', '=', 'mon_hoc.id')
            ->dangHoatDong()->tap($applySearch)
            ->withCount([
                'moduleHocs as tong_module',
                'moduleHocs as module_da_nhan' => fn($q) =>
                    $q->whereHas('phanCongGiangViens',
                        fn($q) => $q->where('trang_thai','da_nhan')),
            ])
            ->orderBy('mon_hoc.ten_mon_hoc')
            ->orderByDesc('khoa_hoc.updated_at')
            ->paginate(12, ['*'], 'hd_page')
            ->appends($request->query());

        $stats = [
            'tong_mau'  => KhoaHoc::mau()->count(),
            'cho_mo'    => KhoaHoc::where('trang_thai_van_hanh','cho_mo')->count(),
            'hoat_dong' => KhoaHoc::dangHoatDong()->count(),
            'san_sang'  => KhoaHoc::where('trang_thai_van_hanh','san_sang')->count(),
        ];

        return view('pages.admin.khoa-hoc.khoa-hoc.index',
            compact('khoaHocMau','khoaHocHoatDong','stats','tab','search'));
    }

    /**
     * create() — Phase 2
     */
    public function create(Request $request)
    {
        $loai = in_array($request->get('loai'),['mau','truc_tiep'])
                    ? $request->get('loai') : 'mau';

        $monHocs = MonHoc::where('trang_thai',true)->orderBy('ten_mon_hoc')->get();

        $khoaHocMauCoSan = KhoaHoc::with(
                'moduleHocs:id,khoa_hoc_id,ten_module,thu_tu_module,thoi_luong_du_kien,mo_ta'
            )
            ->mau()->where('tong_so_module','>',0)->orderBy('ten_khoa_hoc')
            ->get(['id','ten_khoa_hoc','ma_khoa_hoc','tong_so_module']);

        $giangViens = GiangVien::with('nguoiDung:ma_nguoi_dung,ho_ten')
            ->whereHas('nguoiDung', fn($q) => $q->where('trang_thai',true))->get();

        $preselectedMonHocId = $request->get('mon_hoc_id');

        return view('pages.admin.khoa-hoc.khoa-hoc.create',
            compact('loai','monHocs','khoaHocMauCoSan','giangViens','preselectedMonHocId'));
    }

    /**
     * store() — Phase 2
     */
    public function store(Request $request)
    {
        $loai = $request->get('loai', 'mau');

        $rules = [
            'loai'           => 'required|in:mau,truc_tiep',
            'mon_hoc_id'     => 'required|exists:mon_hoc,id',
            'ten_khoa_hoc'   => 'required|string|max:200',
            'cap_do'         => 'required|in:co_ban,trung_binh,nang_cao',
            'mo_ta_ngan'     => 'nullable|string|max:500',
            'mo_ta_chi_tiet' => 'nullable|string',
            'hinh_anh'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'ghi_chu_noi_bo' => 'nullable|string|max:1000',
            'modules'                       => 'required|array|min:1',
            'modules.*.ten_module'          => 'required|string|max:200',
            'modules.*.thoi_luong_du_kien'  => 'nullable|integer|min:1|max:600',
            'modules.*.mo_ta'               => 'nullable|string',
        ];

        if ($loai === 'truc_tiep') {
            $rules['ngay_khai_giang'] = 'required|date|after_or_equal:today';
            $rules['ngay_ket_thuc_du_kien'] = 'required|date|after:ngay_khai_giang';
            $rules['modules.*.giang_vien_id'] = 'required|exists:giang_vien,id';
        }

        $validator = Validator::make($request->all(), $rules, [
            'mon_hoc_id.required' => 'Vui lòng chọn môn học',
            'ten_khoa_hoc.required' => 'Tên khóa học là bắt buộc',
            'ngay_khai_giang.required' => 'Ngày khai giảng là bắt buộc với khóa trực tiếp',
            'ngay_khai_giang.after_or_equal' => 'Ngày khai giảng phải từ hôm nay trở đi',
            'ngay_ket_thuc_du_kien.after' => 'Ngày kết thúc phải sau ngày khai giảng',
            'modules.required' => 'Khóa học phải có ít nhất một module',
            'modules.*.ten_module.required' => 'Tên module không được để trống',
            'modules.*.giang_vien_id.required' => 'Vui lòng chọn giảng viên cho từng module',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $hinh_anh = null;
            if ($request->hasFile('hinh_anh')) {
                $file = $request->file('hinh_anh');
                $filename = time() . '_' . Str::slug($request->ten_khoa_hoc) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/khoa-hoc'), $filename);
                $hinh_anh = 'images/khoa-hoc/' . $filename;
            }

            $maKhoaHoc = $this->generateMaKhoaHoc($request->mon_hoc_id);

            $khoaHoc = KhoaHoc::create([
                'mon_hoc_id'          => $request->mon_hoc_id,
                'ma_khoa_hoc'         => $maKhoaHoc,
                'ten_khoa_hoc'        => $request->ten_khoa_hoc,
                'mo_ta_ngan'          => $request->mo_ta_ngan,
                'mo_ta_chi_tiet'      => $request->mo_ta_chi_tiet,
                'hinh_anh'            => $hinh_anh,
                'cap_do'              => $request->cap_do,
                'loai'                => $loai,
                'trang_thai_van_hanh' => ($loai === 'mau') ? 'cho_mo' : 'cho_giang_vien',
                'ngay_khai_giang'     => $request->ngay_khai_giang,
                'ngay_ket_thuc_du_kien' => $request->ngay_ket_thuc_du_kien,
                'ghi_chu_noi_bo'      => $request->ghi_chu_noi_bo,
                'trang_thai'          => true,
            ]);

            foreach ($request->modules as $index => $modData) {
                $thuTu = $index + 1;
                $maModule = $maKhoaHoc . 'M' . str_pad($thuTu, 2, '0', STR_PAD_LEFT);

                $module = ModuleHoc::create([
                    'khoa_hoc_id'        => $khoaHoc->id,
                    'ma_module'          => $maModule,
                    'ten_module'         => $modData['ten_module'],
                    'mo_ta'              => $modData['mo_ta'] ?? null,
                    'thu_tu_module'      => $thuTu,
                    'thoi_luong_du_kien' => $modData['thoi_luong_du_kien'] ?? null,
                    'trang_thai'         => true,
                ]);

                if (!empty($modData['giang_vien_id'])) {
                    PhanCongModuleGiangVien::create([
                        'khoa_hoc_id'    => $khoaHoc->id,
                        'module_hoc_id'  => $module->id,
                        'giao_vien_id'   => $modData['giang_vien_id'],
                        'ngay_phan_cong' => now(),
                        'trang_thai'     => 'cho_xac_nhan',
                        'created_by'     => auth()->user()->ma_nguoi_dung,
                    ]);
                }
            }

            $khoaHoc->update(['tong_so_module' => count($request->modules)]);
            DB::commit();

            return redirect()->route('admin.khoa-hoc.show', $khoaHoc->id)->with('success', "Thao tác thành công.");
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Hiển thị chi tiết khóa học (Phase 3 Redesign - Phase A Upgrade)
     */
    public function show($id)
    {
        $khoaHoc = KhoaHoc::with([
            'monHoc',
            'moduleHocs' => fn($q) => $q->orderBy('thu_tu_module'),
            'moduleHocs.phanCongGiangViens' => fn($q) => $q->orderByDesc('updated_at'),
            'moduleHocs.phanCongGiangViens.giangVien.nguoiDung',
        ])->findOrFail($id);

        $tongModule = $khoaHoc->moduleHocs->count();
        $moduleCoGv = $khoaHoc->moduleHocs->filter(
            fn($m) => $m->phanCongGiangViens->where('trang_thai','da_nhan')->count() > 0
        )->count();

        // Load danh sách GV cho form kích hoạt (kèm chuyên ngành + trình độ)
        $giangViens = GiangVien::with('nguoiDung')
            ->whereHas('nguoiDung', fn($q) => $q->where('trang_thai', 1))
            ->orderBy('id')
            ->get();

        return view('pages.admin.khoa-hoc.khoa-hoc.show',
            compact('khoaHoc','giangViens','tongModule','moduleCoGv'));
    }

    /**
     * Kích hoạt khóa học mẫu thành lớp học (Phase 3 Redesign - Phase A Upgrade)
     */
    public function kichHoatMau(Request $request, $id)
    {
        $khoaHoc = KhoaHoc::with('moduleHocs')->findOrFail($id);

        // Guard: chỉ KH mẫu + đang chờ mở
        if ($khoaHoc->loai !== 'mau' || $khoaHoc->trang_thai_van_hanh !== 'cho_mo') {
            return redirect()->route('admin.khoa-hoc.show', $id)
                ->with('error', 'Khóa học này không thể kích hoạt.');
        }

        $tongModule = $khoaHoc->moduleHocs->count();
        if ($tongModule === 0)
            return redirect()->back()->with('error','Khóa học chưa có module. Vui lòng thêm module trước.');

        // Validate
        $validated = $request->validate([
            'ngay_khai_giang'       => 'required|date|after_or_equal:today',
            'ngay_ket_thuc_du_kien' => 'required|date|after:ngay_khai_giang',
            'giang_viens'           => 'required|array',
            'giang_viens.*'         => 'required|exists:giang_vien,id',
        ], [
            'ngay_khai_giang.required'       => 'Vui lòng chọn ngày khai giảng.',
            'ngay_khai_giang.after_or_equal' => 'Ngày khai giảng phải từ hôm nay trở đi.',
            'ngay_ket_thuc_du_kien.required' => 'Vui lòng chọn ngày kết thúc dự kiến.',
            'ngay_ket_thuc_du_kien.after'    => 'Ngày kết thúc phải sau ngày khai giảng.',
            'giang_viens.required'           => 'Vui lòng chọn giảng viên cho tất cả module.',
            'giang_viens.*.required'         => 'Mỗi module phải có giảng viên.',
            'giang_viens.*.exists'           => 'Giảng viên không hợp lệ.',
        ]);

        // Kiểm tra tất cả module đều có GV được chọn
        foreach ($khoaHoc->moduleHocs as $module) {
            if (empty($validated['giang_viens'][$module->id])) {
                return back()
                    ->withInput()
                    ->with('error', "Module \"{$module->ten_module}\" chưa được chọn giảng viên.");
            }
        }

        DB::transaction(function () use ($khoaHoc, $validated) {
            // Cập nhật ngày + trạng thái khóa học
            $khoaHoc->update([
                'ngay_khai_giang'       => $validated['ngay_khai_giang'],
                'ngay_ket_thuc_du_kien' => $validated['ngay_ket_thuc_du_kien'],
                'trang_thai_van_hanh'   => 'cho_giang_vien',
            ]);

            // Tạo phân công cho từng module
            foreach ($khoaHoc->moduleHocs as $module) {
                $giangVienId = $validated['giang_viens'][$module->id];

                // Xóa phân công cũ nếu có (tránh duplicate)
                PhanCongModuleGiangVien::where('module_hoc_id', $module->id)->delete();

                PhanCongModuleGiangVien::create([
                    'khoa_hoc_id'   => $khoaHoc->id,
                    'module_hoc_id' => $module->id,
                    'giao_vien_id'  => $giangVienId,
                    'ngay_phan_cong'=> now(),
                    'trang_thai'    => 'cho_xac_nhan',
                    'created_by'    => auth()->user()->ma_nguoi_dung,
                ]);

                // Gửi thông báo cho GV
                $giangVienObj = GiangVien::find($giangVienId);
                ThongBaoService::guiPhanCongGV($giangVienObj, $module, $khoaHoc);
            }
        });

        return redirect()->route('admin.khoa-hoc.show', $id)
            ->with('success', 'Đã kích hoạt lớp học! Đang chờ giảng viên xác nhận.');
    }

    /**
     * Các phương thức khác giữ nguyên
     */
    public function edit($id) { 
        $khoaHoc = KhoaHoc::findOrFail($id);
        $monHocs = MonHoc::active()->get();
        return view('pages.admin.khoa-hoc.khoa-hoc.edit', compact('khoaHoc', 'monHocs'));
    }

    public function update(Request $request, $id)
    {
        $khoaHoc = KhoaHoc::findOrFail($id);
        
        $rules = [
            'mon_hoc_id'     => 'required|exists:mon_hoc,id',
            'ten_khoa_hoc'   => 'required|string|max:200',
            'cap_do'         => 'required|in:co_ban,trung_binh,nang_cao',
            'mo_ta_ngan'     => 'nullable|string|max:500',
            'mo_ta_chi_tiet' => 'nullable|string',
            'hinh_anh'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'trang_thai'     => 'required|boolean',
        ];

        $request->validate($rules);

        try {
            $data = $request->only(['mon_hoc_id', 'ten_khoa_hoc', 'cap_do', 'mo_ta_ngan', 'mo_ta_chi_tiet', 'trang_thai']);
            
            if ($request->hasFile('hinh_anh')) {
                // Xóa ảnh cũ nếu có
                if ($khoaHoc->hinh_anh && file_exists(public_path($khoaHoc->hinh_anh))) {
                    unlink(public_path($khoaHoc->hinh_anh));
                }
                
                $file = $request->file('hinh_anh');
                $filename = time() . '_' . Str::slug($request->ten_khoa_hoc) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/khoa-hoc'), $filename);
                $data['hinh_anh'] = 'images/khoa-hoc/' . $filename;
            }

            $khoaHoc->update($data);

            return redirect()->route('admin.khoa-hoc.show', $khoaHoc->id)->with('success', 'Cập nhật khóa học thành công.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())->withInput();
        }
    }
    
    public function destroy($id) {
        $khoaHoc = KhoaHoc::findOrFail($id);
        
        // Xóa hình ảnh nếu có
        if ($khoaHoc->hinh_anh && file_exists(public_path($khoaHoc->hinh_anh))) {
            unlink(public_path($khoaHoc->hinh_anh));
        }
        
        $khoaHoc->delete();
        return redirect()->route('admin.khoa-hoc.index')->with('success', 'Xóa khóa học thành công.');
    }

    public function toggleStatus($id) {
        $khoaHoc = KhoaHoc::findOrFail($id);
        $khoaHoc->update(['trang_thai' => !$khoaHoc->trang_thai]);
        return redirect()->back()->with('success', 'Đã đổi trạng thái.');
    }

    public function xacNhanMoLop($id)
    {
        $khoaHoc = KhoaHoc::with([
            'moduleHocs.phanCongGiangViens'
        ])->findOrFail($id);

        // Guard: chỉ KH đang ở trạng thái 'san_sang'
        if ($khoaHoc->trang_thai_van_hanh !== 'san_sang') {
            return redirect()->route('admin.khoa-hoc.show', $id)
                ->with('error', 'Khóa học chưa sẵn sàng để mở lớp. Vui lòng chờ giảng viên xác nhận.');
        }

        // Kiểm tra lại: tất cả module đều có GV da_nhan
        $tongModule = $khoaHoc->moduleHocs->count();
        $daXacNhan  = $khoaHoc->moduleHocs->filter(
            fn($m) => $m->phanCongGiangViens->where('trang_thai', 'da_nhan')->count() > 0
        )->count();

        if ($daXacNhan < $tongModule) {
            return redirect()->route('admin.khoa-hoc.show', $id)
                ->with('error', 'Vẫn còn module chưa được giảng viên xác nhận.');
        }

        DB::transaction(function () use ($khoaHoc) {
            $khoaHoc->update(['trang_thai_van_hanh' => 'dang_day']);

            // Thông báo cho tất cả GV được phân công
            $giangVienIds = $khoaHoc->moduleHocs
                ->flatMap(fn($m) => $m->phanCongGiangViens->where('trang_thai', 'da_nhan'))
                ->pluck('giao_vien_id')
                ->unique();

            $giangViens = GiangVien::whereIn('id', $giangVienIds)->get();
            foreach ($giangViens as $gv) {
                \App\Models\ThongBao::create([
                    'nguoi_nhan_id' => $gv->nguoi_dung_id,
                    'tieu_de'       => "🎉 Lớp học đã mở: {$khoaHoc->ten_khoa_hoc}",
                    'noi_dung'      => "Admin đã xác nhận mở lớp \"{$khoaHoc->ten_khoa_hoc}\". "
                                     . "Lớp bắt đầu từ "
                                     . ($khoaHoc->ngay_khai_giang ? $khoaHoc->ngay_khai_giang->format('d/m/Y') : '—') . ".",
                    'loai'          => 'mo_lop',
                    'url'           => route('giang-vien.khoa-hoc'),
                ]);
            }
        });

        return redirect()->route('admin.khoa-hoc.show', $id)
            ->with('success', 'Đã mở lớp học chính thức! Bạn có thể bắt đầu thêm học sinh.');
    }

    private function generateMaKhoaHoc($monHocId) {
        $monHoc = MonHoc::find($monHocId);
        $prefix = strtoupper(substr($monHoc->ma_mon_hoc, 0, 3));
        $lastKhoaHoc = KhoaHoc::where('ma_khoa_hoc', 'LIKE', $prefix . '%')->orderBy('ma_khoa_hoc', 'desc')->first();
        if ($lastKhoaHoc) {
            $lastNumber = intval(substr($lastKhoaHoc->ma_khoa_hoc, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
