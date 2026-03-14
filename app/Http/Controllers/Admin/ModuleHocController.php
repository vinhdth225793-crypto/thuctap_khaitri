<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuleHoc;
use App\Models\KhoaHoc;
use App\Models\GiangVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ModuleHocController extends Controller
{
    /**
     * index()
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $khoaHocId = $request->get('khoa_hoc_id');

        $moduleHocs = ModuleHoc::with(['khoaHoc.monHoc'])
            ->when($search, function($q) use ($search) {
                $q->where('ten_module', 'like', "%{$search}%")
                  ->orWhere('ma_module', 'like', "%{$search}%");
            })
            ->when($khoaHocId, function($q) use ($khoaHocId) {
                $q->where('khoa_hoc_id', $khoaHocId);
            })
            ->orderBy('khoa_hoc_id')
            ->orderBy('thu_tu_module')
            ->paginate(10)
            ->appends($request->query());

        $khoaHocs = KhoaHoc::with('monHoc')->orderBy('ma_khoa_hoc')->get();

        return view('pages.admin.khoa-hoc.module-hoc.index', compact('moduleHocs', 'khoaHocs', 'search', 'khoaHocId'));
    }

    /**
     * create()
     */
    public function create(Request $request)
    {
        $khoaHocId = $request->get('khoa_hoc_id');
        $khoaHocs = KhoaHoc::with('monHoc')->active()->orderBy('ma_khoa_hoc')->get();
        
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
            'khoa_hoc_id'       => 'required|exists:khoa_hoc,id',
            'ten_module'        => 'required|string|max:200',
            'mo_ta'             => 'nullable|string',
            'thu_tu_module'     => [
                'required', 'integer', 'min:1',
                Rule::unique('module_hoc')->where('khoa_hoc_id', $request->khoa_hoc_id)
            ],
            'thoi_luong_du_kien' => 'nullable|integer|min:1|max:600',
            'trang_thai'        => 'nullable|boolean',
        ], [
            'khoa_hoc_id.required'   => 'Vui lòng chọn khóa học.',
            'ten_module.required'    => 'Tên module không được để trống.',
            'thu_tu_module.required' => 'Thứ tự module là bắt buộc.',
            'thu_tu_module.unique'   => 'Thứ tự này đã tồn tại trong khóa học.',
        ]);

        DB::beginTransaction();
        try {
            $khoaHoc = KhoaHoc::findOrFail($request->khoa_hoc_id);
            $maModule = $khoaHoc->ma_khoa_hoc . 'M' . str_pad($request->thu_tu_module, 2, '0', STR_PAD_LEFT);

            if (ModuleHoc::where('ma_module', $maModule)->exists()) {
                throw new \Exception('Mã module ' . $maModule . ' đã tồn tại. Vui lòng chọn thứ tự khác.');
            }

            $moduleHoc = ModuleHoc::create([
                'khoa_hoc_id'        => $request->khoa_hoc_id,
                'ma_module'          => $maModule,
                'ten_module'         => $request->ten_module,
                'mo_ta'              => $request->mo_ta,
                'thu_tu_module'      => $request->thu_tu_module,
                'thoi_luong_du_kien' => $request->thoi_luong_du_kien,
                'trang_thai'         => $request->has('trang_thai') ? $request->trang_thai : true,
            ]);

            DB::commit();
            return redirect()->route('admin.module-hoc.show', $moduleHoc->id)->with('success', 'Thêm module thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Lỗi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * show()
     */
    public function show($id)
    {
        $moduleHoc = ModuleHoc::with([
            'khoaHoc.monHoc',
            'phanCongGiangViens.giangVien.nguoiDung'
        ])->findOrFail($id);

        $giangViens = GiangVien::with('nguoiDung')
            ->whereHas('nguoiDung', fn($q) => $q->where('trang_thai', true))
            ->get();

        return view('pages.admin.khoa-hoc.module-hoc.show', compact('moduleHoc', 'giangViens'));
    }

    /**
     * edit()
     */
    public function edit($id)
    {
        $moduleHoc = ModuleHoc::with('khoaHoc.monHoc')->findOrFail($id);
        $khoaHocs = KhoaHoc::with('monHoc')->orderBy('ma_khoa_hoc')->get();

        return view('pages.admin.khoa-hoc.module-hoc.edit', compact('moduleHoc', 'khoaHocs'));
    }

    /**
     * update()
     */
    public function update(Request $request, $id)
    {
        $moduleHoc = ModuleHoc::findOrFail($id);

        $request->validate([
            'ten_module'        => 'required|string|max:200',
            'mo_ta'             => 'nullable|string',
            'thu_tu_module'     => [
                'required', 'integer', 'min:1',
                Rule::unique('module_hoc')->where('khoa_hoc_id', $moduleHoc->khoa_hoc_id)->ignore($id)
            ],
            'thoi_luong_du_kien' => 'nullable|integer|min:1|max:600',
            'trang_thai'        => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only(['ten_module', 'mo_ta', 'thu_tu_module', 'thoi_luong_du_kien', 'trang_thai']);
            
            // Nếu đổi thứ tự → sinh lại mã
            if ($moduleHoc->thu_tu_module != $request->thu_tu_module) {
                $maMoi = $moduleHoc->khoaHoc->ma_khoa_hoc . 'M' . str_pad($request->thu_tu_module, 2, '0', STR_PAD_LEFT);
                if (ModuleHoc::where('ma_module', $maMoi)->where('id', '!=', $id)->exists()) {
                    throw new \Exception('Mã module ' . $maMoi . ' đã tồn tại. Vui lòng chọn thứ tự khác.');
                }
                $data['ma_module'] = $maMoi;
            }

            $moduleHoc->update($data);

            DB::commit();
            return redirect()->route('admin.module-hoc.show', $id)->with('success', 'Cập nhật module thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Lỗi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * destroy()
     */
    public function destroy($id)
    {
        $moduleHoc = ModuleHoc::findOrFail($id);

        // Không cho xóa nếu có GV đang nhận hoặc chờ
        $coPhanCongActive = $moduleHoc->phanCongGiangViens()
            ->whereIn('trang_thai', ['da_nhan', 'cho_xac_nhan'])
            ->exists();

        if ($coPhanCongActive) {
            return back()->with('error', 'Không thể xóa module đã được phân công cho giảng viên.');
        }

        $khoaHocId = $moduleHoc->khoa_hoc_id;
        $moduleHoc->delete();

        return redirect()->route('admin.khoa-hoc.show', $khoaHocId)->with('success', 'Xóa module thành công.');
    }

    /**
     * toggleStatus()
     */
    public function toggleStatus($id)
    {
        $moduleHoc = ModuleHoc::findOrFail($id);
        $moduleHoc->update(['trang_thai' => !$moduleHoc->trang_thai]);
        
        $msg = $moduleHoc->trang_thai ? 'Đã kích hoạt module.' : 'Đã tạm dừng module.';
        return back()->with('success', $msg);
    }
}
