<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\ModuleHoc;
use App\Models\GiangVien;
use App\Models\PhanCongModuleGiangVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ModuleHocController extends Controller
{
    /**
     * index()
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $khoaHocId = $request->get('khoa_hoc_id');

        // Phân trang theo KhoaHoc
        $khoaHocsPaginated = KhoaHoc::with(['nhomNganh', 'moduleHocs' => function($q) use ($search) {
                $q->when($search, function($q2) use ($search) {
                    $q2->where('ten_module', 'like', "%{$search}%")
                       ->orWhere('ma_module', 'like', "%{$search}%");
                })
                ->orderBy('thu_tu_module');
            }])
            ->withCount('moduleHocs')
            ->when($search, function($q) use ($search) {
                $q->whereHas('moduleHocs', function($q2) use ($search) {
                    $q2->where('ten_module', 'like', "%{$search}%")
                       ->orWhere('ma_module', 'like', "%{$search}%");
                });
            })
            ->when($khoaHocId, function($q) use ($khoaHocId) {
                $q->where('id', $khoaHocId);
            })
            ->orderBy('id', 'desc')
            ->paginate(3)
            ->appends($request->query());

        $khoaHocsAll = KhoaHoc::with('nhomNganh')->orderBy('ma_khoa_hoc')->get();

        return view('pages.admin.khoa-hoc.module-hoc.index', [
            'khoaHocsPaginated' => $khoaHocsPaginated,
            'khoaHocsAll' => $khoaHocsAll,
            'search' => $search,
            'khoaHocId' => $khoaHocId
        ]);
    }

    /**
     * create()
     */
    public function create(Request $request)
    {
        $khoaHocId = $request->get('khoa_hoc_id');
        $khoaHocs = KhoaHoc::with('nhomNganh')->active()->orderBy('ma_khoa_hoc')->get();
        
        $thuTuGoiY = 1;
        if ($khoaHocId) {
            $thuTuGoiY = ModuleHoc::where('khoa_hoc_id', $khoaHocId)->count() + 1;
        }

        return view('pages.admin.khoa-hoc.module-hoc.create', compact('khoaHocs', 'khoaHocId', 'thuTuGoiY'));
    }

    /**
     * store()
     */
    public function store(Request $request)
    {
        $request->validate([
            'khoa_hoc_id' => 'required|exists:khoa_hoc,id',
            'ten_module' => 'required|string|max:255',
            'thu_tu_module' => 'required|integer|min:1',
            'thoi_luong_du_kien' => 'nullable|integer|min:1',
        ]);

        try {
            $khoaHoc = KhoaHoc::findOrFail($request->khoa_hoc_id);
            $maModule = $khoaHoc->ma_khoa_hoc . 'M' . str_pad($request->thu_tu_module, 2, '0', STR_PAD_LEFT);

            if (ModuleHoc::where('ma_module', $maModule)->exists()) {
                $maModule .= '-' . time();
            }

            ModuleHoc::create(array_merge($request->all(), ['ma_module' => $maModule]));

            return redirect()->route('admin.module-hoc.index', ['khoa_hoc_id' => $request->khoa_hoc_id])
                ->with('success', 'Thêm module thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * show()
     */
    public function show($id)
    {
        $moduleHoc = ModuleHoc::with(['khoaHoc.nhomNganh', 'phanCongGiangViens.giangVien.nguoiDung'])->findOrFail($id);
        
        // Lấy các module khác của khóa học này để hiển thị danh sách bên dưới
        $cacModuleKhac = ModuleHoc::where('khoa_hoc_id', $moduleHoc->khoa_hoc_id)
            ->where('id', '!=', $id)
            ->orderBy('thu_tu_module')
            ->get();

        $giangViens = GiangVien::with('nguoiDung')
            ->whereHas('nguoiDung', fn($q) => $q->where('trang_thai', 1))
            ->get();

        return view('pages.admin.khoa-hoc.module-hoc.show', compact('moduleHoc', 'giangViens', 'cacModuleKhac'));
    }

    /**
     * edit()
     */
    public function edit($id)
    {
        $moduleHoc = ModuleHoc::with('khoaHoc.nhomNganh')->findOrFail($id);
        $khoaHocs = KhoaHoc::with('nhomNganh')->orderBy('ma_khoa_hoc')->get();

        return view('pages.admin.khoa-hoc.module-hoc.edit', compact('moduleHoc', 'khoaHocs'));
    }

    /**
     * update()
     */
    public function update(Request $request, $id)
    {
        $moduleHoc = ModuleHoc::findOrFail($id);

        $request->validate([
            'ten_module' => 'required|string|max:255',
            'thu_tu_module' => 'required|integer|min:1',
            'thoi_luong_du_kien' => 'nullable|integer|min:1',
        ]);

        try {
            $moduleHoc->update($request->all());
            return redirect()->route('admin.module-hoc.show', $id)->with('success', 'Cập nhật module thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * destroy()
     */
    public function destroy($id)
    {
        $moduleHoc = ModuleHoc::findOrFail($id);
        $khoaHocId = $moduleHoc->khoa_hoc_id;
        $moduleHoc->delete();

        return redirect()->route('admin.module-hoc.index', ['khoa_hoc_id' => $khoaHocId])
            ->with('success', 'Xóa module thành công!');
    }

    /**
     * toggleStatus()
     */
    public function toggleStatus($id)
    {
        $moduleHoc = ModuleHoc::findOrFail($id);
        $moduleHoc->update(['trang_thai' => !$moduleHoc->trang_thai]);

        return back()->with('success', 'Đã đổi trạng thái module.');
    }

    /**
     * assign() - Phân công nhanh
     */
    public function assign(Request $request, $moduleId)
    {
        $request->validate([
            'giao_vien_id' => 'required|exists:giang_vien,id',
            'ghi_chu' => 'nullable|string|max:500'
        ]);

        $module = ModuleHoc::findOrFail($moduleId);

        // Kiểm tra xem đã phân công chưa
        $exists = PhanCongModuleGiangVien::where('module_hoc_id', $moduleId)
            ->where('giao_vien_id', $request->giao_vien_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Giảng viên này đã được gán cho module này rồi.');
        }

        PhanCongModuleGiangVien::create([
            'khoa_hoc_id' => $module->khoa_hoc_id,
            'module_hoc_id' => $moduleId,
            'giao_vien_id' => $request->giao_vien_id,
            'ngay_phan_cong' => now(),
            'trang_thai' => 'cho_xac_nhan',
            'ghi_chu' => $request->ghi_chu,
            'created_by' => auth()->id()
        ]);

        return back()->with('success', 'Đã gửi yêu cầu phân công cho giảng viên.');
    }
}
