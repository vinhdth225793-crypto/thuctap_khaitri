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

        // Query chung lấy tất cả khóa học (cả mẫu và hoạt động)
        $query = KhoaHoc::with(['nhomNganh', 'moduleHocs' => function($q) use ($search) {
                $q->when($search, function($q2) use ($search) {
                    $q2->where('ten_module', 'like', "%{$search}%")
                       ->orWhere('ma_module', 'like', "%{$search}%");
                })
                ->orderBy('thu_tu_module');
            }])
            ->withCount('moduleHocs')
            ->when($search, function($q) use ($search) {
                $q->where(function($sub) use ($search) {
                    $sub->where('ten_khoa_hoc', 'like', "%{$search}%")
                        ->orWhere('ma_khoa_hoc', 'like', "%{$search}%")
                        ->orWhereHas('moduleHocs', function($q2) use ($search) {
                            $q2->where('ten_module', 'like', "%{$search}%")
                               ->orWhere('ma_module', 'like', "%{$search}%");
                        });
                });
            })
            ->when($khoaHocId, function($q) use ($khoaHocId) {
                $q->where('id', $khoaHocId);
            })
            ->orderBy('id', 'desc');

        $allResult = $query->get();

        // PHÂN LOẠI Y HỆT TRANG KHOA HỌC
        // 1. Khóa học mẫu
        $khoaHocsMau = $allResult->filter(fn($kh) => $kh->loai === 'mau');

        // 2. Đang giảng dạy (Phải là hoat_dong, trang thái dang_day và TIẾN ĐỘ < 100)
        $khoaHocsDangDay = $allResult->filter(function($kh) {
            return $kh->loai === 'hoat_dong' && $kh->trang_thai_van_hanh === 'dang_day' && (int)$kh->tien_do_hoc_tap < 100;
        });

        // 3. Chờ GV xác nhận
        $khoaHocsChoXacNhan = $allResult->filter(function($kh) {
            return $kh->loai === 'hoat_dong' && $kh->trang_thai_van_hanh === 'cho_giang_vien';
        });

        // 4. Sẵn sàng mở
        $khoaHocsSanSang = $allResult->filter(function($kh) {
            return $kh->loai === 'hoat_dong' && $kh->trang_thai_van_hanh === 'san_sang';
        });

        // 5. Đã hoàn thành (ket_thuc HOẶC tiến độ 100%)
        $khoaHocsHoanThanh = $allResult->filter(function($kh) {
            if ($kh->loai === 'mau') return false;
            return $kh->trang_thai_van_hanh === 'ket_thuc' || (int)$kh->tien_do_hoc_tap === 100;
        });

        $khoaHocsAll = KhoaHoc::with('nhomNganh')->orderBy('ma_khoa_hoc')->get();

        return view('pages.admin.khoa-hoc.module-hoc.index', [
            'khoaHocsMau'        => $khoaHocsMau,
            'khoaHocsDangDay'    => $khoaHocsDangDay,
            'khoaHocsChoXacNhan' => $khoaHocsChoXacNhan,
            'khoaHocsSanSang'    => $khoaHocsSanSang,
            'khoaHocsHoanThanh'  => $khoaHocsHoanThanh,
            'khoaHocsAll'        => $khoaHocsAll,
            'search'             => $search,
            'khoaHocId'          => $khoaHocId
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
            report($e);

            return back()->with('error', 'Không thể tạo module lúc này. Vui lòng thử lại.')->withInput();
        }
    }

    /**
     * show()
     */
    public function show($id)
    {
        $moduleHoc = ModuleHoc::with([
            'khoaHoc.nhomNganh',
            'khoaHoc.moduleHocs.lichHocs',
            'phanCongGiangViens.giangVien.nguoiDung',
            'lichHocs',
        ])->findOrFail($id);

        $cacModuleKhac = $moduleHoc->khoaHoc->moduleHocs
            ->where('id', '!=', $moduleHoc->id)
            ->sortBy('thu_tu_module')
            ->values();

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
            report($e);

            return back()->with('error', 'Không thể cập nhật module lúc này. Vui lòng thử lại.');
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
            'giang_vien_id' => 'required|exists:giang_vien,id',
            'ghi_chu' => 'nullable|string|max:500'
        ]);

        $module = ModuleHoc::findOrFail($moduleId);

        // Kiểm tra xem đã phân công chưa
        $exists = PhanCongModuleGiangVien::where('module_hoc_id', $moduleId)
            ->where('giang_vien_id', $request->giang_vien_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Giảng viên này đã được gán cho module này rồi.');
        }

        PhanCongModuleGiangVien::create([
            'khoa_hoc_id' => $module->khoa_hoc_id,
            'module_hoc_id' => $moduleId,
            'giang_vien_id' => $request->giang_vien_id,
            'ngay_phan_cong' => now(),
            'trang_thai' => 'cho_xac_nhan',
            'ghi_chu' => $request->ghi_chu,
            'created_by' => auth()->id()
        ]);

        return back()->with('success', 'Đã gửi yêu cầu phân công cho giảng viên.');
    }
}

