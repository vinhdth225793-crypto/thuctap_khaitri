<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('thu_tu')->orderByDesc('created_at')->get();

        return view('pages.admin.settings.banners.index', compact('banners'));
    }

    public function create()
    {
        $suggestedOrder = $this->nextAvailableOrder();

        return view('pages.admin.settings.banners.create', compact('suggestedOrder'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'nullable|string|max:500',
            'anh_banner' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'link' => 'nullable|string|max:500',
            'thu_tu' => 'required|integer|min:0',
            'trang_thai' => 'boolean',
        ], $this->validationMessages());

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $file = $request->file('anh_banner');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('images/banners'), $filename);

        Banner::create([
            'tieu_de' => $request->tieu_de,
            'mo_ta' => $request->mo_ta,
            'duong_dan_anh' => 'images/banners/' . $filename,
            'link' => $request->link,
            'thu_tu' => $request->thu_tu,
            'trang_thai' => $request->boolean('trang_thai'),
        ]);

        return redirect()->route('admin.settings.banners.index')
            ->with('success', 'Thêm banner thành công!');
    }

    public function edit($id)
    {
        $banner = Banner::findOrFail($id);

        return view('pages.admin.settings.banners.edit', compact('banner'));
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'nullable|string|max:500',
            'anh_banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'link' => 'nullable|string|max:500',
            'thu_tu' => 'required|integer|min:0',
            'trang_thai' => 'boolean',
        ], $this->validationMessages());

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = [
            'tieu_de' => $request->tieu_de,
            'mo_ta' => $request->mo_ta,
            'link' => $request->link,
            'thu_tu' => $request->thu_tu,
            'trang_thai' => $request->boolean('trang_thai'),
        ];

        if ($request->hasFile('anh_banner')) {
            if ($banner->duong_dan_anh && file_exists(public_path($banner->duong_dan_anh))) {
                unlink(public_path($banner->duong_dan_anh));
            }

            $file = $request->file('anh_banner');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/banners'), $filename);
            $data['duong_dan_anh'] = 'images/banners/' . $filename;
        }

        $banner->update($data);

        return redirect()->route('admin.settings.banners.index')
            ->with('success', 'Cập nhật banner thành công!');
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);

        if ($banner->duong_dan_anh && file_exists(public_path($banner->duong_dan_anh))) {
            unlink(public_path($banner->duong_dan_anh));
        }

        $banner->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa banner.']);
    }

    public function toggleStatus($id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $banner->trang_thai = ! $banner->trang_thai;
            $banner->save();

            return response()->json([
                'success' => true,
                'trang_thai' => $banner->trang_thai,
                'message' => $banner->trang_thai ? 'Banner đã hiển thị.' : 'Banner đã ẩn.',
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật trạng thái banner lúc này. Vui lòng thử lại.',
            ], 500);
        }
    }

    public function updateOrder(Request $request)
    {
        try {
            $bannerTable = (new Banner())->getTable();

            $validator = Validator::make($request->all(), [
                'order' => 'required|array',
                'order.*' => ['integer', 'distinct', Rule::exists($bannerTable, 'id')],
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.'], 400);
            }

            foreach ($request->order as $thuTu => $id) {
                Banner::where('id', $id)->update(['thu_tu' => $thuTu]);
            }

            return response()->json(['success' => true, 'message' => 'Đã cập nhật thứ tự.']);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật thứ tự banner lúc này. Vui lòng thử lại.',
            ], 500);
        }
    }

    private function nextAvailableOrder(): int
    {
        $orders = Banner::query()
            ->whereNotNull('thu_tu')
            ->orderBy('thu_tu')
            ->pluck('thu_tu')
            ->map(fn ($order) => (int) $order)
            ->filter(fn ($order) => $order >= 0)
            ->unique()
            ->values();

        $nextOrder = 0;

        foreach ($orders as $order) {
            if ($order === $nextOrder) {
                $nextOrder++;
                continue;
            }

            if ($order > $nextOrder) {
                break;
            }
        }

        return $nextOrder;
    }

    private function validationMessages(): array
    {
        return [
            'anh_banner.required' => 'Vui lòng chọn hình ảnh banner',
            'anh_banner.image' => 'Tệp tải lên phải là hình ảnh',
            'anh_banner.mimes' => 'Ảnh banner phải có định dạng jpeg, png, jpg hoặc webp',
            'anh_banner.max' => 'Ảnh tối đa 2MB',
            'tieu_de.required' => 'Vui lòng nhập tiêu đề',
        ];
    }
}
