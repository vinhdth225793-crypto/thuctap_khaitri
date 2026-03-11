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

class KhoaHocManagementController extends Controller
{
    /**
     * Hiển thị danh sách khóa học
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $monHocId = $request->get('mon_hoc_id', '');

        $query = KhoaHoc::with(['monHoc', 'moduleHocs']);

        if ($search) {
            $query->search($search);
        }

        if ($monHocId) {
            $query->where('mon_hoc_id', $monHocId);
        }

        $khoaHocs = $query->paginate(10);
        $monHocs = MonHoc::active()->get();

        return view('pages.admin.khoa-hoc.khoa-hoc.index', compact('khoaHocs', 'monHocs', 'search', 'monHocId'));
    }

    /**
     * Hiển thị form tạo khóa học mới
     */
    public function create()
    {
        $monHocs = MonHoc::active()->get();
        $giangViens = GiangVien::with('nguoiDung')->get();

        // Lấy tất cả module hiện có để hiển thị
        $existingModules = ModuleHoc::with(['khoaHoc.monHoc', 'phanCongGiangViens.giangVien.nguoiDung'])
                                   ->whereHas('khoaHoc', function($q) {
                                       $q->where('trang_thai', true);
                                   })
                                   ->get()
                                   ->groupBy('ten_module');

        return view('pages.admin.khoa-hoc.khoa-hoc.create', compact('monHocs', 'giangViens', 'existingModules'));
    }

    /**
     * Lưu khóa học mới
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mon_hoc_id' => 'required|exists:mon_hoc,id',
            'ten_khoa_hoc' => 'required|string|max:200',
            'mo_ta_ngan' => 'nullable|string|max:500',
            'mo_ta_chi_tiet' => 'nullable|string',
            'hinh_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cap_do' => 'required|in:co_ban,trung_binh,nang_cao',
            'modules' => 'required|array|min:1',
            'modules.*.ten_module' => 'required|string|max:200',
            'modules.*.mo_ta' => 'nullable|string',
            'modules.*.thoi_luong_du_kien' => 'nullable|integer|min:1',
            'lecturer_modules' => 'nullable|string',
        ], [
            'mon_hoc_id.required' => 'Vui lòng chọn môn học',
            'ten_khoa_hoc.required' => 'Tên khóa học là bắt buộc',
            'cap_do.required' => 'Vui lòng chọn cấp độ',
            'modules.required' => 'Phải có ít nhất một module',
            'modules.*.ten_module.required' => 'Tên module là bắt buộc',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed in store method:', $validator->errors()->toArray());
            \Log::error('Request data:', $request->all());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Tự động tạo mã khóa học trước
            $maKhoaHoc = $this->generateMaKhoaHoc($request->mon_hoc_id);

            // Chuẩn bị dữ liệu tạo khóa học
            $data = [
                'mon_hoc_id' => $request->mon_hoc_id,
                'ma_khoa_hoc' => $maKhoaHoc,
                'ten_khoa_hoc' => $request->ten_khoa_hoc,
                'mo_ta_ngan' => $request->mo_ta_ngan,
                'mo_ta_chi_tiet' => $request->mo_ta_chi_tiet,
                'cap_do' => $request->cap_do,
                'tong_so_module' => count($request->modules),
                'trang_thai' => true, // Thêm trạng thái mặc định
            ];

            // Xử lý upload hình ảnh (nếu có)
            if ($request->hasFile('hinh_anh')) {
                $image = $request->file('hinh_anh');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images/khoa-hoc'), $imageName);
                $data['hinh_anh'] = 'images/khoa-hoc/' . $imageName;
            }

            // Tạo khóa học trước để có ID
            $khoaHoc = KhoaHoc::create($data);

            // Tạo modules
            foreach ($request->modules as $index => $moduleData) {
                $maModule = $maKhoaHoc . 'M' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);

                ModuleHoc::create([
                    'khoa_hoc_id' => $khoaHoc->id,
                    'ma_module' => $maModule,
                    'ten_module' => $moduleData['ten_module'],
                    'mo_ta' => $moduleData['mo_ta'] ?? null,
                    'thu_tu_module' => $index + 1,
                    'thoi_luong_du_kien' => $moduleData['thoi_luong_du_kien'] ?? null,
                    'trang_thai' => true,
                ]);
            }

            // Phân công giảng viên cho các module
            $modules = $khoaHoc->moduleHocs;
            
            // Parse dữ liệu lecturer_modules từ JSON
            $lecturerModulesJson = $request->input('lecturer_modules', '{}');
            $lecturerModules = json_decode($lecturerModulesJson, true) ?? [];

            foreach ($lecturerModules as $lecturerId => $moduleIndices) {
                foreach ($moduleIndices as $moduleIndex) {
                    // Lấy module theo index
                    $module = $modules->get($moduleIndex);

                    if ($module) {
                        PhanCongModuleGiangVien::create([
                            'khoa_hoc_id' => $khoaHoc->id,
                            'module_hoc_id' => $module->id,
                            'giao_vien_id' => $lecturerId,
                            'ngay_phan_cong' => now(),
                            'trang_thai' => 'da_nhan',
                            'created_by' => 1, // Giả sử admin có ID = 1
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.khoa-hoc.index')
                ->with('success', 'Thêm khóa học thành công với mã: ' . $maKhoaHoc);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Hiển thị chi tiết khóa học
     */
    public function show($id)
    {
        $khoaHoc = KhoaHoc::with(['monHoc', 'moduleHocs.phanCongGiangViens.giangVien.nguoiDung'])->findOrFail($id);

        return view('pages.admin.khoa-hoc.khoa-hoc.show', compact('khoaHoc'));
    }

    /**
     * Hiển thị form chỉnh sửa khóa học
     */
    public function edit($id)
    {
        $khoaHoc = KhoaHoc::with(['moduleHocs.phanCongGiangViens.giangVien'])->findOrFail($id);
        $monHocs = MonHoc::active()->get();
        $giangViens = GiangVien::with('nguoiDung')->get();

        return view('pages.admin.khoa-hoc.khoa-hoc.edit', compact('khoaHoc', 'monHocs', 'giangViens'));
    }

    /**
     * Cập nhật khóa học
     */
    public function update(Request $request, $id)
    {
        $khoaHoc = KhoaHoc::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'mon_hoc_id' => 'required|exists:mon_hoc,id',
            'ten_khoa_hoc' => 'required|string|max:200',
            'mo_ta_ngan' => 'nullable|string|max:500',
            'mo_ta_chi_tiet' => 'nullable|string',
            'hinh_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cap_do' => 'required|in:co_ban,trung_binh,nang_cao',
            'trang_thai' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only(['mon_hoc_id', 'ten_khoa_hoc', 'mo_ta_ngan', 'mo_ta_chi_tiet', 'cap_do', 'trang_thai']);

        // Xử lý upload hình ảnh
        if ($request->hasFile('hinh_anh')) {
            if ($khoaHoc->hinh_anh && file_exists(public_path($khoaHoc->hinh_anh))) {
                unlink(public_path($khoaHoc->hinh_anh));
            }
            $image = $request->file('hinh_anh');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/khoa-hoc'), $imageName);
            $data['hinh_anh'] = 'images/khoa-hoc/' . $imageName;
        }

        $khoaHoc->update($data);

        return redirect()->route('admin.khoa-hoc.index')
            ->with('success', 'Cập nhật khóa học thành công');
    }

    /**
     * Thay đổi trạng thái khóa học
     */
    public function toggleStatus($id)
    {
        $khoaHoc = KhoaHoc::findOrFail($id);
        $khoaHoc->update(['trang_thai' => !$khoaHoc->trang_thai]);

        $statusText = $khoaHoc->trang_thai ? 'kích hoạt' : 'tạm dừng';

        return redirect()->back()
            ->with('success', "Khóa học đã được {$statusText}");
    }

    /**
     * Tự động tạo mã khóa học
     */
    private function generateMaKhoaHoc($monHocId)
    {
        $monHoc = MonHoc::find($monHocId);
        $prefix = strtoupper(substr($monHoc->ma_mon_hoc, 0, 3));

        $lastKhoaHoc = KhoaHoc::where('ma_khoa_hoc', 'LIKE', $prefix . '%')
                              ->orderBy('ma_khoa_hoc', 'desc')
                              ->first();

        if ($lastKhoaHoc) {
            $lastNumber = intval(substr($lastKhoaHoc->ma_khoa_hoc, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
