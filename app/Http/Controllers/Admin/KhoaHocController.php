<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonHoc;
use App\Models\KhoaHoc;
use App\Models\ModuleHoc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KhoaHocController extends Controller
{
    /**
     * Hiển thị danh sách môn học
     */
    public function indexMonHoc()
    {
        $page = request('page', 1);
        $search = request('search', '');
        $perPage = 10;

        if ($search) {
            $monHocs = MonHoc::search($search)
                ->paginate($perPage, ['*'], 'page', $page);
        } else {
            $monHocs = MonHoc::paginate($perPage, ['*'], 'page', $page);
        }

        return view('pages.admin.khoa-hoc.mon-hoc.index', compact('monHocs', 'search'));
    }

    /**
     * Hiển thị form tạo môn học mới
     */
    public function createMonHoc()
    {
        return view('pages.admin.khoa-hoc.mon-hoc.create');
    }

    /**
     * Lưu môn học mới
     */
    public function storeMonHoc(Request $request)
    {
        $messages = [
            'ten_mon_hoc.required' => 'Tên môn học là bắt buộc',
            'ten_mon_hoc.unique' => 'Tên môn học đã tồn tại trong hệ thống',
        ];

        $validator = Validator::make($request->all(), [
            'ten_mon_hoc' => 'required|string|max:150|unique:mon_hoc',
            'mo_ta' => 'nullable|string',
            'hinh_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Tự động tạo mã môn học
        $maMonHoc = MonHoc::generateMaMonHoc($request->ten_mon_hoc);

        $data = [
            'ma_mon_hoc' => $maMonHoc,
            'ten_mon_hoc' => $request->ten_mon_hoc,
            'mo_ta' => $request->mo_ta,
        ];

        // Xử lý upload hình ảnh
        if ($request->hasFile('hinh_anh')) {
            $image = $request->file('hinh_anh');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/mon-hoc'), $imageName);
            $data['hinh_anh'] = 'images/mon-hoc/' . $imageName;
        }

        MonHoc::create($data);

        return redirect()->route('admin.mon-hoc.index')
            ->with('success', 'Thêm môn học thành công với mã: ' . $maMonHoc);
    }

    /**
     * Hiển thị chi tiết môn học
     */
    public function showMonHoc($id)
    {
        $monHoc = MonHoc::findOrFail($id);
        $khoaHocs = $monHoc->khoaHocs()->paginate(10);

        return view('pages.admin.khoa-hoc.mon-hoc.show', compact('monHoc', 'khoaHocs'));
    }

    /**
     * Hiển thị form chỉnh sửa môn học
     */
    public function editMonHoc($id)
    {
        $monHoc = MonHoc::findOrFail($id);

        return view('pages.admin.khoa-hoc.mon-hoc.edit', compact('monHoc'));
    }

    /**
     * Cập nhật môn học
     */
    public function updateMonHoc(Request $request, $id)
    {
        $monHoc = MonHoc::findOrFail($id);

        $messages = [
            'ten_mon_hoc.unique' => 'Tên môn học đã tồn tại trong hệ thống',
        ];

        $validator = Validator::make($request->all(), [
            'ten_mon_hoc' => 'required|string|max:150|unique:mon_hoc,ten_mon_hoc,' . $id,
            'mo_ta' => 'nullable|string',
            'hinh_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only('ten_mon_hoc', 'mo_ta');

        // Xử lý upload hình ảnh
        if ($request->hasFile('hinh_anh')) {
            if ($monHoc->hinh_anh && file_exists(public_path($monHoc->hinh_anh))) {
                unlink(public_path($monHoc->hinh_anh));
            }
            $image = $request->file('hinh_anh');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/mon-hoc'), $imageName);
            $data['hinh_anh'] = 'images/mon-hoc/' . $imageName;
        }

        $monHoc->update($data);

        return redirect()->route('admin.mon-hoc.index')
            ->with('success', 'Cập nhật môn học thành công');
    }

    /**
     * Xóa môn học
     */
    public function destroyMonHoc($id)
    {
        $monHoc = MonHoc::findOrFail($id);

        // Xóa hình ảnh
        if ($monHoc->hinh_anh && file_exists(public_path($monHoc->hinh_anh))) {
            unlink(public_path($monHoc->hinh_anh));
        }

        // Xóa tất cả khóa học liên quan
        $monHoc->khoaHocs()->delete();

        $monHoc->delete();

        return redirect()->route('admin.mon-hoc.index')
            ->with('success', 'Xóa môn học thành công');
    }

    /**
     * Cập nhật trạng thái môn học
     */
    public function toggleStatusMonHoc($id)
    {
        $monHoc = MonHoc::findOrFail($id);
        $monHoc->update(['trang_thai' => !$monHoc->trang_thai]);

        return redirect()->back()
            ->with('success', 'Cập nhật trạng thái thành công');
    }

    /**
     * Hiển thị danh sách khóa học
     */
    public function indexKhoaHoc()
    {
        $monHocs = MonHoc::all();
        $khoaHocs = KhoaHoc::with('monHoc')->paginate(10);

        return view('pages.admin.khoa-hoc.khoa-hoc.index', compact('khoaHocs', 'monHocs'));
    }

    /**
     * Hiển thị danh sách module
     */
    public function indexModuleHoc()
    {
        $moduleHocs = ModuleHoc::with('khoaHoc')->paginate(10);

        return view('pages.admin.khoa-hoc.module-hoc.index', compact('moduleHocs'));
    }
}
