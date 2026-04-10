<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NhomNganh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NhomNganhController extends Controller
{
    /**
     * Hiển thị danh sách nhóm ngành
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $trangThai = $request->get('trang_thai', '');
        $perPage = 10;

        $nhomNganhs = NhomNganh::withCount('khoaHocs')
            ->when($search, function ($q) use ($search) {
                return $q->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('ten_nhom_nganh', 'LIKE', "%{$search}%")
                        ->orWhere('ma_nhom_nganh', 'LIKE', "%{$search}%");
                });
            })
            ->when($trangThai !== '', function ($q) use ($trangThai) {
                return $q->where('trang_thai', (bool) $trangThai);
            })
            ->paginate($perPage);

        return view('pages.admin.khoa-hoc.mon-hoc.index', [
            'nhomNganhs' => $nhomNganhs,
            'search' => $search,
            'trangThai' => $trangThai,
        ]);
    }

    /**
     * Hiển thị form tạo nhóm ngành mới
     */
    public function create()
    {
        return view('pages.admin.khoa-hoc.mon-hoc.create');
    }

    /**
     * Lưu nhóm ngành mới
     */
    public function store(Request $request)
    {
        $messages = [
            'ten_nhom_nganh.required' => 'Tên nhóm ngành là bắt buộc',
            'ten_nhom_nganh.unique' => 'Tên nhóm ngành đã tồn tại trong hệ thống',
        ];

        $validator = Validator::make($request->all(), [
            'ten_nhom_nganh' => 'required|string|max:150|unique:nhom_nganh',
            'mo_ta' => 'nullable|string',
            'hinh_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Tự động tạo mã nhóm ngành
        $maNhomNganh = NhomNganh::generateMaNhomNganh();

        $data = [
            'ma_nhom_nganh' => $maNhomNganh,
            'ten_nhom_nganh' => $request->ten_nhom_nganh,
            'mo_ta' => $request->mo_ta,
        ];

        // Xử lý upload hình ảnh
        if ($request->hasFile('hinh_anh')) {
            $image = $request->file('hinh_anh');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/nhom-nganh'), $imageName);
            $data['hinh_anh'] = 'images/nhom-nganh/' . $imageName;
        }

        $nhomNganh = NhomNganh::create($data);

        return redirect()->route('admin.nhom-nganh.show', $nhomNganh->id)
            ->with('success', 'Thêm nhóm ngành thành công! Mã: ' . $maNhomNganh);
    }

    /**
     * Hiển thị chi tiết nhóm ngành
     */
    public function show($id)
    {
        $nhomNganh = NhomNganh::findOrFail($id);
        $khoaHocs = $nhomNganh->khoaHocs()->paginate(10);

        return view('pages.admin.khoa-hoc.mon-hoc.show', compact('nhomNganh', 'khoaHocs'));
    }

    /**
     * Hiển thị form chỉnh sửa nhóm ngành
     */
    public function edit($id)
    {
        $nhomNganh = NhomNganh::findOrFail($id);

        return view('pages.admin.khoa-hoc.mon-hoc.edit', [
            'nhomNganh' => $nhomNganh
        ]);
    }

    /**
     * Cập nhật nhóm ngành
     */
    public function update(Request $request, $id)
    {
        $nhomNganh = NhomNganh::findOrFail($id);

        $messages = [
            'ten_nhom_nganh.unique' => 'Tên nhóm ngành đã tồn tại trong hệ thống',
        ];

        $validator = Validator::make($request->all(), [
            'ten_nhom_nganh' => 'required|string|max:150|unique:nhom_nganh,ten_nhom_nganh,' . $id,
            'mo_ta' => 'nullable|string',
            'hinh_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = [
            'ten_nhom_nganh' => $request->ten_nhom_nganh,
            'mo_ta' => $request->mo_ta
        ];

        // Xử lý upload hình ảnh
        if ($request->hasFile('hinh_anh')) {
            if ($nhomNganh->hinh_anh && file_exists(public_path($nhomNganh->hinh_anh))) {
                unlink(public_path($nhomNganh->hinh_anh));
            }
            $image = $request->file('hinh_anh');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/nhom-nganh'), $imageName);
            $data['hinh_anh'] = 'images/nhom-nganh/' . $imageName;
        }

        $nhomNganh->update($data);

        return redirect()->route('admin.nhom-nganh.index')
            ->with('success', 'Cập nhật nhóm ngành thành công');
    }

    /**
     * Xóa nhóm ngành
     */
    public function destroy($id)
    {
        $nhomNganh = NhomNganh::with('khoaHocs')->findOrFail($id);

        // Xóa ảnh
        if ($nhomNganh->hinh_anh && file_exists(public_path($nhomNganh->hinh_anh))) {
            unlink(public_path($nhomNganh->hinh_anh));
        }

        $nhomNganh->delete();

        return redirect()->route('admin.nhom-nganh.index')
            ->with('success', 'Đã xóa nhóm ngành và tất cả dữ liệu liên quan.');
    }

    /**
     * Cập nhật trạng thái nhóm ngành
     */
    public function toggleStatus($id)
    {
        $nhomNganh = NhomNganh::findOrFail($id);
        $nhomNganh->update(['trang_thai' => !$nhomNganh->trang_thai]);

        $statusText = $nhomNganh->trang_thai ? 'kích hoạt' : 'tạm dừng';

        return redirect()->back()
            ->with('success', 'Nhóm ngành "' . $nhomNganh->ten_nhom_nganh . '" đã được ' . $statusText . '.');
    }
}
