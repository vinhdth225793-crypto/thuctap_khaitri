<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuleHoc;
use App\Models\KhoaHoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ModuleHocController extends Controller
{
    /**
     * Hiển thị danh sách module có phân trang và lọc
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $khoaHocId = $request->get('khoa_hoc_id', '');

        $query = ModuleHoc::with('khoaHoc.monHoc');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('ten_module', 'LIKE', "%{$search}%")
                  ->orWhere('ma_module', 'LIKE', "%{$search}%");
            });
        }

        if ($khoaHocId) {
            $query->where('khoa_hoc_id', $khoaHocId);
        }

        $moduleHocs = $query->orderBy('khoa_hoc_id')
                            ->orderBy('thu_tu_module')
                            ->paginate(10)
                            ->appends($request->query());

        $khoaHocs = KhoaHoc::with('monHoc')->active()->get();

        return view('pages.admin.khoa-hoc.module-hoc.index', compact('moduleHocs', 'khoaHocs', 'search', 'khoaHocId'));
    }

    /**
     * Form thêm module mới
     */
    public function create(Request $request)
    {
        $khoaHocId = $request->get('khoa_hoc_id');
        $khoaHocs = KhoaHoc::with('monHoc')->active()->orderBy('ma_khoa_hoc')->get();
        
        $thuTuGoiY = null;
        if ($khoaHocId) {
            $thuTuGoiY = ModuleHoc::where('khoa_hoc_id', $khoaHocId)->count() + 1;
        }

        return view('pages.admin.khoa-hoc.module-hoc.create', compact('khoaHocs', 'khoaHocId', 'thuTuGoiY'));
    }

    /**
     * Lưu module mới vào DB
     */
    public function store(Request $request)
    {
        $messages = [
            'khoa_hoc_id.required' => 'Vui lòng chọn khóa học',
            'ten_module.required' => 'Tên module là bắt buộc',
            'thu_tu_module.required' => 'Thứ tự module là bắt buộc',
            'thu_tu_module.unique' => 'Thứ tự này đã tồn tại trong khóa học này',
        ];

        $validator = Validator::make($request->all(), [
            'khoa_hoc_id' => 'required|exists:khoa_hoc,id',
            'ten_module' => 'required|string|max:200',
            'mo_ta' => 'nullable|string',
            'thu_tu_module' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('module_hoc')->where('khoa_hoc_id', $request->khoa_hoc_id)
            ],
            'thoi_luong_du_kien' => 'nullable|integer|min:1|max:600',
            'trang_thai' => 'nullable|boolean',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $khoaHoc = KhoaHoc::findOrFail($request->khoa_hoc_id);
            $thuTu = $request->thu_tu_module;
            
            // Sinh mã module: {ma_khoa_hoc}M{thu_tu:02d}
            $maModule = $khoaHoc->ma_khoa_hoc . 'M' . str_pad($thuTu, 2, '0', STR_PAD_LEFT);

            // Kiểm tra mã module chưa tồn tại (phòng race condition)
            if (ModuleHoc::where('ma_module', $maModule)->exists()) {
                throw new \Exception("Mã module {$maModule} đã tồn tại trong hệ thống.");
            }

            $module = ModuleHoc::create([
                'khoa_hoc_id' => $request->khoa_hoc_id,
                'ma_module' => $maModule,
                'ten_module' => $request->ten_module,
                'mo_ta' => $request->mo_ta,
                'thu_tu_module' => $thuTu,
                'thoi_luong_du_kien' => $request->thoi_luong_du_kien,
                'trang_thai' => $request->has('trang_thai'),
            ]);

            DB::commit();

            return redirect()->route('admin.module-hoc.show', $module->id)
                ->with('success', "Thêm module \"{$module->ten_module}\" thành công. Mã: {$maModule}");

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Chi tiết module
     */
    public function show($id)
    {
        $moduleHoc = ModuleHoc::with([
            'khoaHoc.monHoc',
            'phanCongGiangViens.giangVien.nguoiDung'
        ])->findOrFail($id);

        $giangViens = \App\Models\GiangVien::with('nguoiDung')
            ->whereHas('nguoiDung', fn($q) => $q->where('trang_thai', true))
            ->get();

        return view('pages.admin.khoa-hoc.module-hoc.show', compact('moduleHoc', 'giangViens'));
    }

    /**
     * Form chỉnh sửa module
     */
    public function edit($id)
    {
        $moduleHoc = ModuleHoc::with('khoaHoc.monHoc')->findOrFail($id);
        $khoaHocs = KhoaHoc::with('monHoc')->active()->get();
        
        return view('pages.admin.khoa-hoc.module-hoc.edit', compact('moduleHoc', 'khoaHocs'));
    }

    /**
     * Cập nhật module
     */
    public function update(Request $request, $id)
    {
        $module = ModuleHoc::findOrFail($id);

        $messages = [
            'ten_module.required' => 'Tên module là bắt buộc',
            'thu_tu_module.required' => 'Thứ tự module là bắt buộc',
            'thu_tu_module.unique' => 'Thứ tự này đã tồn tại trong khóa học này',
        ];

        $validator = Validator::make($request->all(), [
            'ten_module' => 'required|string|max:200',
            'mo_ta' => 'nullable|string',
            'thu_tu_module' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('module_hoc')->where('khoa_hoc_id', $module->khoa_hoc_id)->ignore($id)
            ],
            'thoi_luong_du_kien' => 'nullable|integer|min:1|max:600',
            'trang_thai' => 'nullable|boolean',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['ten_module', 'mo_ta', 'thu_tu_module', 'thoi_luong_du_kien']);
        $data['trang_thai'] = $request->has('trang_thai');

        // Nếu thay đổi thứ tự → sinh lại ma_module
        if ($module->thu_tu_module != $request->thu_tu_module) {
            $data['ma_module'] = $module->khoaHoc->ma_khoa_hoc . 'M' . str_pad($request->thu_tu_module, 2, '0', STR_PAD_LEFT);
        }

        $module->update($data);

        return redirect()->route('admin.module-hoc.show', $module->id)
            ->with('success', 'Cập nhật module thành công');
    }

    /**
     * Xóa module
     */
    public function destroy($id)
    {
        $moduleHoc = ModuleHoc::findOrFail($id);

        // Không cho xóa nếu có phan_cong đang da_nhan hoặc cho_xac_nhan
        $hasActiveAssignment = $moduleHoc->phanCongGiangViens()
            ->whereIn('trang_thai', ['da_nhan', 'cho_xac_nhan'])
            ->exists();

        if ($hasActiveAssignment) {
            return redirect()->back()->with('error', 'Không thể xóa module này vì đang có giảng viên được phân công giảng dạy (Trạng thái: Đã nhận hoặc Chờ xác nhận).');
        }

        $khoaHocId = $moduleHoc->khoa_hoc_id;
        $moduleHoc->delete();

        return redirect()->route('admin.khoa-hoc.show', $khoaHocId)
            ->with('success', 'Đã xóa module thành công.');
    }

    /**
     * Toggle trang_thai
     */
    public function toggleStatus($id)
    {
        $module = ModuleHoc::findOrFail($id);
        $module->update(['trang_thai' => !$module->trang_thai]);

        $statusText = $module->trang_thai ? 'kích hoạt' : 'tạm dừng';

        return redirect()->back()
            ->with('success', "Module \"{$module->ten_module}\" đã được {$statusText}.");
    }
}
