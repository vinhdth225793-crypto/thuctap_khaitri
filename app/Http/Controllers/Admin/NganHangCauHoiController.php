<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\NganHangCauHoi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NganHangCauHoiController extends Controller
{
    /**
     * Lấy danh sách khóa học mà người dùng được phép thao tác
     */
    private function getAccessibleKhoaHocs()
    {
        $user = auth()->user();
        $query = KhoaHoc::query();

        if ($user->isGiangVien()) {
            $giangVien = $user->giangVien;
            $khoaHocIds = $giangVien->khoaHocDuocPhanCong()->pluck('khoa_hoc.id')->unique()->toArray();
            $query->whereIn('id', $khoaHocIds);
        }

        return $query->orderBy('ten_khoa_hoc')->get(['id', 'ten_khoa_hoc', 'ma_khoa_hoc']);
    }

    /**
     * Danh sách câu hỏi (Phân trang, Tìm kiếm, Lọc theo khóa học)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $khoaHocs = $this->getAccessibleKhoaHocs();
        $khoaHocIds = $khoaHocs->pluck('id')->toArray();

        // Query danh sách câu hỏi
        $query = NganHangCauHoi::with(['khoaHoc', 'nguoiTao']);

        // Phân quyền: Chỉ thấy câu hỏi của khóa học được phép
        if ($user->isGiangVien()) {
            $query->whereIn('khoa_hoc_id', $khoaHocIds);
        }

        // Lọc theo khóa học
        if ($request->filled('khoa_hoc_id')) {
            $query->where('khoa_hoc_id', $request->khoa_hoc_id);
        }

        // Tìm kiếm theo nội dung câu hỏi
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('noi_dung_cau_hoi', 'like', "%{$search}%");
        }

        $cauHois = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('pages.admin.kiem-tra-online.cau-hoi.index', compact('cauHois', 'khoaHocs'));
    }

    /**
     * Form thêm mới
     */
    public function create()
    {
        return view('pages.admin.kiem-tra-online.cau-hoi.form', [
            'cauHoi' => new NganHangCauHoi(),
            'khoaHocs' => $this->getAccessibleKhoaHocs(),
            'action' => route('admin.kiem-tra-online.cau-hoi.store'),
            'method' => 'POST',
            'title' => 'Thêm mới câu hỏi thủ công'
        ]);
    }

    /**
     * Lưu câu hỏi mới
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'khoa_hoc_id' => 'required|exists:khoa_hoc,id',
            'noi_dung_cau_hoi' => 'required|string',
            'dap_an_sai_1' => 'required|string',
            'dap_an_sai_2' => 'required|string',
            'dap_an_sai_3' => 'required|string',
            'dap_an_dung' => 'required|string',
        ]);

        // Kiểm tra phân quyền giảng viên
        if ($user->isGiangVien()) {
            $giangVien = $user->giangVien;
            $isAssigned = $giangVien->khoaHocDuocPhanCong()->where('khoa_hoc.id', $validated['khoa_hoc_id'])->exists();
            if (!$isAssigned) {
                abort(403, 'Bạn không được phân công cho khóa học này.');
            }
        }

        // Kiểm tra logic đáp án
        $this->validateAnswers($validated);

        // Kiểm tra trùng lặp câu hỏi trong cùng khóa học
        if (NganHangCauHoi::isDuplicate($validated['khoa_hoc_id'], $validated['noi_dung_cau_hoi'])) {
            throw ValidationException::withMessages([
                'noi_dung_cau_hoi' => 'Nội dung câu hỏi này đã tồn tại trong khóa học này.'
            ]);
        }

        $validated['nguoi_tao_id'] = $user->ma_nguoi_dung;
        
        NganHangCauHoi::create($validated);

        return redirect()
            ->route('admin.kiem-tra-online.cau-hoi.index')
            ->with('success', 'Đã thêm câu hỏi thành công.');
    }

    /**
     * Form chỉnh sửa
     */
    public function edit($id)
    {
        $user = auth()->user();
        $cauHoi = NganHangCauHoi::findOrFail($id);

        // Kiểm tra phân quyền: nếu là giảng viên thì phải được phân công khóa học này
        if ($user->isGiangVien()) {
            $giangVien = $user->giangVien;
            $isAssigned = $giangVien->khoaHocDuocPhanCong()->where('khoa_hoc.id', $cauHoi->khoa_hoc_id)->exists();
            if (!$isAssigned) {
                abort(403, 'Bạn không có quyền chỉnh sửa câu hỏi này.');
            }
        }

        return view('pages.admin.kiem-tra-online.cau-hoi.form', [
            'cauHoi' => $cauHoi,
            'khoaHocs' => $this->getAccessibleKhoaHocs(),
            'action' => route('admin.kiem-tra-online.cau-hoi.update', $cauHoi->id),
            'method' => 'PUT',
            'title' => 'Chỉnh sửa câu hỏi'
        ]);
    }

    /**
     * Cập nhật câu hỏi
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $cauHoi = NganHangCauHoi::findOrFail($id);

        $validated = $request->validate([
            'khoa_hoc_id' => 'required|exists:khoa_hoc,id',
            'noi_dung_cau_hoi' => 'required|string',
            'dap_an_sai_1' => 'required|string',
            'dap_an_sai_2' => 'required|string',
            'dap_an_sai_3' => 'required|string',
            'dap_an_dung' => 'required|string',
        ]);

        // Kiểm tra phân quyền
        if ($user->isGiangVien()) {
            $giangVien = $user->giangVien;
            // Kiểm tra khóa học cũ và khóa học mới (nếu thay đổi)
            $isAssignedOld = $giangVien->khoaHocDuocPhanCong()->where('khoa_hoc.id', $cauHoi->khoa_hoc_id)->exists();
            $isAssignedNew = $giangVien->khoaHocDuocPhanCong()->where('khoa_hoc.id', $validated['khoa_hoc_id'])->exists();
            
            if (!$isAssignedOld || !$isAssignedNew) {
                abort(403, 'Bạn không có quyền thao tác trên khóa học này.');
            }
        }

        // Kiểm tra logic đáp án
        $this->validateAnswers($validated);

        // Kiểm tra trùng lặp
        if (NganHangCauHoi::isDuplicate($validated['khoa_hoc_id'], $validated['noi_dung_cau_hoi'], $cauHoi->id)) {
            throw ValidationException::withMessages([
                'noi_dung_cau_hoi' => 'Nội dung câu hỏi này đã tồn tại trong khóa học này.'
            ]);
        }

        $cauHoi->update($validated);

        return redirect()
            ->route('admin.kiem-tra-online.cau-hoi.index')
            ->with('success', 'Đã cập nhật câu hỏi thành công.');
    }

    /**
     * Xóa câu hỏi
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $cauHoi = NganHangCauHoi::findOrFail($id);

        // Kiểm tra phân quyền
        if ($user->isGiangVien()) {
            $giangVien = $user->giangVien;
            $isAssigned = $giangVien->khoaHocDuocPhanCong()->where('khoa_hoc.id', $cauHoi->khoa_hoc_id)->exists();
            if (!$isAssigned) {
                abort(403, 'Bạn không có quyền xóa câu hỏi này.');
            }
        }

        $cauHoi->delete();

        return back()->with('success', 'Đã xóa câu hỏi.');
    }

    /**
     * Logic validate đáp án: các đáp án sai không trùng nhau, đáp án đúng không trùng đáp án sai
     */
    private function validateAnswers(array $data)
    {
        $sai1 = NganHangCauHoi::normalizeString($data['dap_an_sai_1']);
        $sai2 = NganHangCauHoi::normalizeString($data['dap_an_sai_2']);
        $sai3 = NganHangCauHoi::normalizeString($data['dap_an_sai_3']);
        $dung = NganHangCauHoi::normalizeString($data['dap_an_dung']);

        $saiSet = [$sai1, $sai2, $sai3];
        
        // Kiểm tra trùng nhau giữa 3 đáp án sai
        if (count(array_unique($saiSet)) < 3) {
            throw ValidationException::withMessages([
                'dap_an_sai_1' => 'Các đáp án sai không được trùng nhau.'
            ]);
        }

        // Kiểm tra đáp án đúng trùng đáp án sai
        if (in_array($dung, $saiSet)) {
            throw ValidationException::withMessages([
                'dap_an_dung' => 'Đáp án đúng không được trùng với bất kỳ đáp án sai nào.'
            ]);
        }
    }

    /**
     * Tải file mẫu CSV (Phase 2)
     */
    public function downloadTemplate()
    {
        $headers = [
            'cau_hoi',
            'dap_an_sai_1',
            'dap_an_sai_2',
            'dap_an_sai_3',
            'dap_an_dung',
        ];

        $sampleRows = [
            [
                'Laravel là gì?',
                'Một loại ngôn ngữ lập trình',
                'Một hệ điều hành',
                'Một phần mềm chỉnh sửa ảnh',
                'Một PHP Framework',
            ],
            [
                'PHP là ngôn ngữ kịch bản chạy ở đâu?',
                'Client-side',
                'Browser',
                'Mobile-side',
                'Server-side',
            ],
        ];

        return response()->streamDownload(function () use ($headers, $sampleRows) {
            $handle = fopen('php://output', 'wb');
            
            // Thêm BOM để Excel nhận diện được UTF-8
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($handle, $headers);

            foreach ($sampleRows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'mau-nhap-cau-hoi.csv', [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }

    /**
     * Upload và Preview (Phase 3 & 4)
     */
    public function import(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'khoa_hoc_id' => 'required|exists:khoa_hoc,id',
            'file_import' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $khoaHocId = $request->khoa_hoc_id;
        $khoaHoc = KhoaHoc::findOrFail($khoaHocId);
        
        // Kiểm tra phân quyền giảng viên
        if ($user->isGiangVien()) {
            $giangVien = $user->giangVien;
            $isAssigned = $giangVien->khoaHocDuocPhanCong()->where('khoa_hoc.id', $khoaHocId)->exists();
            if (!$isAssigned) {
                abort(403, 'Bạn không được phân công cho khóa học này.');
            }
        }

        // Đọc tệp CSV
        $path = $request->file('file_import')->getRealPath();
        $file = fopen($path, 'r');
        
        // Bỏ qua BOM nếu có
        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($file);
        }

        // Đọc header
        $header = fgetcsv($file);
        
        $previewData = [];
        $summary = [
            'total' => 0,
            'valid' => 0,
            'duplicate_file' => 0,
            'duplicate_db' => 0,
            'error' => 0,
        ];

        $contentSetInFile = [];

        while (($row = fgetcsv($file)) !== false) {
            if (empty(array_filter($row))) continue;

            $summary['total']++;
            
            $rawCauHoi = $row[0] ?? '';
            $rawSai1 = $row[1] ?? '';
            $rawSai2 = $row[2] ?? '';
            $rawSai3 = $row[3] ?? '';
            $rawDung = $row[4] ?? '';

            $status = 'hop_le';
            $note = '';

            // 1. Kiểm tra trống
            if (empty($rawCauHoi) || empty($rawSai1) || empty($rawSai2) || empty($rawSai3) || empty($rawDung)) {
                $status = 'loi_du_lieu';
                $note = 'Thiếu thông tin bắt buộc.';
            } 
            else {
                // 2. Kiểm tra logic đáp án
                $s1 = NganHangCauHoi::normalizeString($rawSai1);
                $s2 = NganHangCauHoi::normalizeString($rawSai2);
                $s3 = NganHangCauHoi::normalizeString($rawSai3);
                $d = NganHangCauHoi::normalizeString($rawDung);
                
                $saiSet = [$s1, $s2, $s3];
                if (count(array_unique($saiSet)) < 3) {
                    $status = 'loi_du_lieu';
                    $note = 'Các đáp án sai bị trùng nhau.';
                } elseif (in_array($d, $saiSet)) {
                    $status = 'loi_du_lieu';
                    $note = 'Đáp án đúng trùng với đáp án sai.';
                }
            }

            // 3. Kiểm tra trùng lặp (nếu chưa có lỗi dữ liệu)
            if ($status === 'hop_le') {
                $normalizedContent = NganHangCauHoi::normalizeString($rawCauHoi);

                // Kiểm tra trong file
                if (isset($contentSetInFile[$normalizedContent])) {
                    $status = 'trung_lap_trong_file';
                    $note = 'Trùng với câu hỏi tại dòng ' . ($contentSetInFile[$normalizedContent] + 1) . ' trong file.';
                } else {
                    $contentSetInFile[$normalizedContent] = $summary['total'];

                    // Kiểm tra trong database
                    if (NganHangCauHoi::isDuplicate($khoaHocId, $rawCauHoi)) {
                        $status = 'trung_lap_trong_he_thong';
                        $note = 'Câu hỏi đã tồn tại trong ngân hàng của khóa học này.';
                    }
                }
            }

            // Cập nhật thống kê
            $summary[$status]++;

            $previewData[] = [
                'noi_dung_cau_hoi' => $rawCauHoi,
                'dap_an_sai_1' => $rawSai1,
                'dap_an_sai_2' => $rawSai2,
                'dap_an_sai_3' => $rawSai3,
                'dap_an_dung' => $rawDung,
                'status' => $status,
                'note' => $note,
            ];
        }

        fclose($file);

        // Lưu session
        session(['import_preview' => [
            'khoa_hoc_id' => $khoaHocId,
            'khoa_hoc_ten' => $khoaHoc->ten_khoa_hoc,
            'data' => $previewData,
            'summary' => $summary,
            'user_id' => $user->id, // Lưu user_id để verify lúc confirm
        ]]);

        return redirect()->route('admin.kiem-tra-online.cau-hoi.preview');
    }

    /**
     * Hiển thị màn hình preview (Phase 3)
     */
    public function preview()
    {
        $preview = session('import_preview');
        if (!$preview) {
            return redirect()->route('admin.kiem-tra-online.cau-hoi.index')->with('error', 'Không tìm thấy dữ liệu xem trước.');
        }

        return view('pages.admin.kiem-tra-online.cau-hoi.preview', compact('preview'));
    }

    /**
     * Xác nhận lưu (Phase 5)
     */
    public function confirmImport(Request $request)
    {
        $preview = session('import_preview');
        if (!$preview || $preview['user_id'] != auth()->id()) {
            session()->forget('import_preview');
            return redirect()->route('admin.kiem-tra-online.cau-hoi.index')->with('error', 'Hết hạn phiên làm việc hoặc phiên không hợp lệ. Hãy thử import lại.');
        }

        $khoaHocId = $preview['khoa_hoc_id'];
        $data = $preview['data'];
        $createdCount = 0;

        DB::transaction(function () use ($khoaHocId, $data, &$createdCount) {
            foreach ($data as $item) {
                if ($item['status'] === 'hop_le') {
                    NganHangCauHoi::create([
                        'khoa_hoc_id' => $khoaHocId,
                        'noi_dung_cau_hoi' => $item['noi_dung_cau_hoi'],
                        'dap_an_sai_1' => $item['dap_an_sai_1'],
                        'dap_an_sai_2' => $item['dap_an_sai_2'],
                        'dap_an_sai_3' => $item['dap_an_sai_3'],
                        'dap_an_dung' => $item['dap_an_dung'],
                        'nguoi_tao_id' => auth()->id(),
                    ]);
                    $createdCount++;
                }
            }
        });

        session()->forget('import_preview');

        return redirect()
            ->route('admin.kiem-tra-online.cau-hoi.index')
            ->with('success', "Đã import thành công $createdCount câu hỏi.");
    }
}
