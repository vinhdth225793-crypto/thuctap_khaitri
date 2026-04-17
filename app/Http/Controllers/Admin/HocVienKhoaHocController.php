<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HocVienKhoaHoc;
use App\Models\HocVien;
use App\Models\KhoaHoc;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;

class HocVienKhoaHocController extends Controller
{
    public function index(int $khoaHocId)
    {
        $khoaHoc = KhoaHoc::with(['nhomNganh'])->findOrFail($khoaHocId);

        $hocViens = HocVienKhoaHoc::with(['hocVien.nguoiDung'])
            ->where('khoa_hoc_id', $khoaHocId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'tong' => $hocViens->total(),
            'dang_hoc' => HocVienKhoaHoc::where('khoa_hoc_id', $khoaHocId)->where('trang_thai', 'dang_hoc')->count(),
            'hoan_thanh' => HocVienKhoaHoc::where('khoa_hoc_id', $khoaHocId)->where('trang_thai', 'hoan_thanh')->count(),
        ];

        return view('pages.admin.hoc-vien-khoa-hoc.index', compact('khoaHoc', 'hocViens', 'stats'));
    }

    public function search(Request $request, int $khoaHocId)
    {
        KhoaHoc::query()->findOrFail($khoaHocId);

        $keyword = trim((string) $request->query('q', ''));
        $limit = max(5, min(20, (int) $request->query('limit', 12)));

        if ($keyword === '') {
            return response()->json([
                'data' => [],
                'meta' => [
                    'keyword' => $keyword,
                    'count' => 0,
                ],
            ]);
        }

        $keywordLower = mb_strtolower($keyword, 'UTF-8');
        $startsWith = $keywordLower . '%';
        $contains = '%' . $keywordLower . '%';

        $students = NguoiDung::query()
            ->where('vai_tro', 'hoc_vien')
            ->where('trang_thai', 1)
            ->whereDoesntHave('khoaHocs', function ($query) use ($khoaHocId) {
                $query->where('khoa_hoc_id', $khoaHocId);
            })
            ->where(function ($query) use ($keyword, $contains) {
                $query->whereRaw('LOWER(ho_ten) LIKE ?', [$contains])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$contains])
                    ->orWhereRaw('LOWER(COALESCE(so_dien_thoai, \'\')) LIKE ?', [$contains]);

                if (is_numeric($keyword)) {
                    $query->orWhere('ma_nguoi_dung', (int) $keyword);
                }
            })
            ->orderByRaw(
                "CASE
                    WHEN LOWER(ho_ten) LIKE ? THEN 0
                    WHEN LOWER(ho_ten) LIKE ? THEN 1
                    WHEN LOWER(email) LIKE ? THEN 2
                    WHEN LOWER(email) LIKE ? THEN 3
                    WHEN LOWER(COALESCE(so_dien_thoai, '')) LIKE ? THEN 4
                    ELSE 5
                END",
                [$startsWith, $contains, $startsWith, $contains, $contains]
            )
            ->orderBy('ho_ten')
            ->limit($limit)
            ->get([
                'ma_nguoi_dung',
                'ho_ten',
                'email',
                'so_dien_thoai',
            ]);

        return response()->json([
            'data' => $students->map(function (NguoiDung $student) {
                return [
                    'id' => $student->ma_nguoi_dung, // Sử dụng ma_nguoi_dung thay vì id
                    'name' => $student->ho_ten,
                    'email' => $student->email,
                    'phone' => $student->so_dien_thoai,
                ];
            })->values(),
            'meta' => [
                'keyword' => $keyword,
                'count' => $students->count(),
            ],
        ]);
    }

    public function store(Request $request, int $khoaHocId)
    {
        $request->validate([
            'hoc_vien_ids' => 'required|array|min:1',
            'hoc_vien_ids.*' => 'exists:nguoi_dung,ma_nguoi_dung', // Sử dụng ma_nguoi_dung thay vì id
            'ngay_tham_gia' => 'nullable|date',
            'ghi_chu' => 'nullable|string|max:500',
        ]);

        KhoaHoc::findOrFail($khoaHocId);

        DB::beginTransaction();

        try {
            $count = 0;

            foreach ($request->hoc_vien_ids as $hvMaNguoiDung) {
                // Kiểm tra sự tồn tại của học viên trong bảng hoc_vien
                $hocVien = HocVien::where('nguoi_dung_id', $hvMaNguoiDung)->first();
                if (!$hocVien) continue;

                // Lưu ma_nguoi_dung vào cột hoc_vien_id (theo quy ước của dự án này)
                $exists = HocVienKhoaHoc::where('khoa_hoc_id', $khoaHocId)
                    ->where('hoc_vien_id', $hvMaNguoiDung)
                    ->exists();

                if (!$exists) {
                    HocVienKhoaHoc::create([
                        'khoa_hoc_id' => $khoaHocId,
                        'hoc_vien_id' => $hvMaNguoiDung,
                        'ngay_tham_gia' => $request->ngay_tham_gia ?? now(),
                        'trang_thai' => 'dang_hoc',
                        'ghi_chu' => $request->ghi_chu,
                        'created_by' => Auth::user()->ma_nguoi_dung,
                    ]);

                    $count++;
                }
            }

            DB::commit();

            return back()->with('success', "Đã thêm thành công {$count} học viên vào khóa học.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi thêm học viên vào khóa học: " . $e->getMessage(), [
                'khoa_hoc_id' => $khoaHocId,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            report($e);

            return back()->with('error', 'Không thể thêm học viên vào khóa học lúc này. Vui lòng thử lại.');
        }
    }

    public function update(Request $request, int $khoaHocId, int $id)
    {
        $request->validate([
            'ngay_tham_gia' => 'required|date',
            'trang_thai' => 'required|in:dang_hoc,hoan_thanh,ngung_hoc',
            'ghi_chu' => 'nullable|string|max:500',
        ]);

        $enrollment = HocVienKhoaHoc::where('khoa_hoc_id', $khoaHocId)->findOrFail($id);
        $enrollment->update($request->only(['ngay_tham_gia', 'trang_thai', 'ghi_chu']));

        return back()->with('success', 'Cập nhật thông tin ghi danh thành công.');
    }

    public function destroy(int $khoaHocId, int $id)
    {
        $enrollment = HocVienKhoaHoc::where('khoa_hoc_id', $khoaHocId)->findOrFail($id);
        $enrollment->delete();

        return back()->with('success', 'Đã xóa học viên khỏi khóa học.');
    }
}
