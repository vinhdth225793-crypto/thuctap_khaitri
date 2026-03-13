<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\MonHoc;
use App\Models\ModuleHoc;
use App\Models\GiangVien;
use App\Models\PhanCongModuleGiangVien;
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

        $khoaHocMau = KhoaHoc::with('monHoc')
            ->mau()->tap($applySearch)
            ->orderByDesc('created_at')
            ->paginate(12, ['*'], 'mau_page')
            ->appends($request->query());

        $khoaHocHoatDong = KhoaHoc::with('monHoc')
            ->dangHoatDong()->tap($applySearch)
            ->withCount([
                'moduleHocs as tong_module',
                'moduleHocs as module_da_nhan' => fn($q) =>
                    $q->whereHas('phanCongGiangViens',
                        fn($q) => $q->where('trang_thai','da_nhan')),
            ])
            ->orderByDesc('updated_at')
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
     * Hiển thị chi tiết khóa học (Phase 3 Redesign)
     */
    public function show($id)
    {
        $khoaHoc = KhoaHoc::with([
            'monHoc',
            'moduleHocs' => fn($q) => $q->orderBy('thu_tu_module'),
            'moduleHocs.phanCongGiangViens' => fn($q) => $q->orderByDesc('updated_at'),
            'moduleHocs.phanCongGiangViens.giangVien.nguoiDung',
        ])->findOrFail($id);

        $giangViens = GiangVien::with('nguoiDung:ma_nguoi_dung,ho_ten')
            ->whereHas('nguoiDung', fn($q) => $q->where('trang_thai',true))->get();

        $tongModule = $khoaHoc->moduleHocs->count();
        $moduleCoGv = $khoaHoc->moduleHocs->filter(
            fn($m) => $m->phanCongGiangViens->where('trang_thai','da_nhan')->count() > 0
        )->count();

        return view('pages.admin.khoa-hoc.khoa-hoc.show',
            compact('khoaHoc','giangViens','tongModule','moduleCoGv'));
    }

    /**
     * Kích hoạt khóa học mẫu thành lớp học (Phase 3 Redesign)
     */
    public function kichHoatMau(Request $request, $id)
    {
        $khoaHoc = KhoaHoc::with('moduleHocs')->findOrFail($id);

        if ($khoaHoc->loai !== 'mau')
            return redirect()->back()->with('error','Chỉ kích hoạt được khóa học mẫu.');
        if ($khoaHoc->trang_thai_van_hanh !== 'cho_mo')
            return redirect()->back()->with('error','Khóa học đã được kích hoạt trước đó.');

        $tongModule = $khoaHoc->moduleHocs->count();
        if ($tongModule === 0)
            return redirect()->back()->with('error','Khóa học chưa có module. Vui lòng thêm module trước.');

        $moduleIds = $khoaHoc->moduleHocs->pluck('id')->toArray();
        $gvRules = [];
        foreach ($moduleIds as $mid) {
            $gvRules["giang_viens.{$mid}"] = 'required|exists:giang_vien,id';
        }

        $request->validate(array_merge([
            'ngay_khai_giang'       => 'required|date|after_or_equal:today',
            'ngay_ket_thuc_du_kien' => 'required|date|after:ngay_khai_giang',
        ], $gvRules), [
            'ngay_khai_giang.required'       => 'Vui lòng chọn ngày khai giảng',
            'ngay_khai_giang.after_or_equal' => 'Ngày khai giảng phải từ hôm nay',
            'ngay_ket_thuc_du_kien.required' => 'Vui lòng chọn ngày kết thúc',
            'ngay_ket_thuc_du_kien.after'    => 'Ngày kết thúc phải sau ngày khai giảng',
            'giang_viens.*.required'         => 'Vui lòng chọn giảng viên cho tất cả module',
        ]);

        DB::transaction(function () use ($khoaHoc, $request, $moduleIds) {
            $khoaHoc->update([
                'ngay_khai_giang'       => $request->ngay_khai_giang,
                'ngay_ket_thuc_du_kien' => $request->ngay_ket_thuc_du_kien,
                'trang_thai_van_hanh'   => 'cho_giang_vien',
            ]);
            foreach ($moduleIds as $mid) {
                PhanCongModuleGiangVien::updateOrCreate(
                    ['module_hoc_id' => $mid, 'giao_vien_id' => $request->giang_viens[$mid]],
                    [
                        'khoa_hoc_id'    => $khoaHoc->id,
                        'trang_thai'     => 'cho_xac_nhan',
                        'ngay_phan_cong' => now(),
                        'created_by'     => auth()->user()->ma_nguoi_dung,
                    ]
                );
            }
        });

        return redirect()->route('admin.khoa-hoc.show', $id)
            ->with('success', "Đã kích hoạt! Đang chờ {$tongModule} giảng viên xác nhận.");
    }

    /**
     * Các phương thức khác giữ nguyên
     */
    public function edit($id) { 
        $khoaHoc = KhoaHoc::findOrFail($id);
        $monHocs = MonHoc::active()->get();
        return view('pages.admin.khoa-hoc.khoa-hoc.edit', compact('khoaHoc', 'monHocs'));
    }
    
    public function destroy($id) {
        $khoaHoc = KhoaHoc::findOrFail($id);
        $khoaHoc->delete();
        return redirect()->route('admin.khoa-hoc.index')->with('success', 'Xóa khóa học thành công.');
    }

    public function toggleStatus($id) {
        $khoaHoc = KhoaHoc::findOrFail($id);
        $khoaHoc->update(['trang_thai' => !$khoaHoc->trang_thai]);
        return redirect()->back()->with('success', 'Đã đổi trạng thái.');
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
