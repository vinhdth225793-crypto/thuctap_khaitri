<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\ModuleHoc;
use App\Models\NganHangCauHoi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NganHangCauHoiController extends Controller
{
    private function getAccessibleKhoaHocs()
    {
        $user = auth()->user();
        $query = KhoaHoc::query();

        if ($user->isGiangVien()) {
            $giangVien = $user->giangVien;
            $khoaHocIds = $giangVien
                ? $giangVien->khoaHocDuocPhanCong()->pluck('khoa_hoc.id')->unique()->toArray()
                : [];

            $query->whereIn('id', $khoaHocIds);
        }

        return $query->orderBy('ten_khoa_hoc')->get(['id', 'ten_khoa_hoc', 'ma_khoa_hoc']);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $khoaHocs = $this->getAccessibleKhoaHocs();
        $khoaHocIds = $khoaHocs->pluck('id')->all();

        $query = NganHangCauHoi::with(['khoaHoc', 'moduleHoc', 'nguoiTao', 'dapAns']);

        if ($user->isGiangVien()) {
            $query->whereIn('khoa_hoc_id', $khoaHocIds);
        }

        if ($request->filled('khoa_hoc_id')) {
            $query->where('khoa_hoc_id', $request->integer('khoa_hoc_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where('noi_dung', 'like', "%{$search}%");
        }

        $cauHois = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('pages.admin.kiem-tra-online.cau-hoi.index', compact('cauHois', 'khoaHocs'));
    }

    public function create()
    {
        return view('pages.admin.kiem-tra-online.cau-hoi.form', [
            'cauHoi' => new NganHangCauHoi(),
            'khoaHocs' => $this->getAccessibleKhoaHocs(),
            'action' => route('admin.kiem-tra-online.cau-hoi.store'),
            'method' => 'POST',
            'title' => 'Thêm mới câu hỏi thủ công',
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $payload = $this->buildQuestionPayload($request);

        $this->authorizeCourseAccess($user, $payload['khoa_hoc_id']);

        if (NganHangCauHoi::isDuplicate($payload['khoa_hoc_id'], $payload['noi_dung'])) {
            throw ValidationException::withMessages([
                'noi_dung_cau_hoi' => 'Nội dung câu hỏi này đã tồn tại trong khóa học này.',
            ]);
        }

        DB::transaction(function () use ($payload, $user) {
            $cauHoi = NganHangCauHoi::create([
                'khoa_hoc_id' => $payload['khoa_hoc_id'],
                'module_hoc_id' => $payload['module_hoc_id'],
                'nguoi_tao_id' => $user->ma_nguoi_dung,
                'ma_cau_hoi' => $payload['ma_cau_hoi'],
                'noi_dung' => $payload['noi_dung'],
                'loai_cau_hoi' => $payload['loai_cau_hoi'],
                'muc_do' => $payload['muc_do'],
                'diem_mac_dinh' => $payload['diem_mac_dinh'],
                'trang_thai' => $payload['trang_thai'],
                'co_the_tai_su_dung' => $payload['co_the_tai_su_dung'],
            ]);

            $this->syncAnswers($cauHoi, $payload['answers']);
        });

        return redirect()
            ->route('admin.kiem-tra-online.cau-hoi.index')
            ->with('success', 'Đã thêm câu hỏi thành công.');
    }

    public function edit($id)
    {
        $user = auth()->user();
        $cauHoi = NganHangCauHoi::with('dapAns')->findOrFail($id);

        $this->authorizeCourseAccess($user, (int) $cauHoi->khoa_hoc_id);

        return view('pages.admin.kiem-tra-online.cau-hoi.form', [
            'cauHoi' => $cauHoi,
            'khoaHocs' => $this->getAccessibleKhoaHocs(),
            'action' => route('admin.kiem-tra-online.cau-hoi.update', $cauHoi->id),
            'method' => 'PUT',
            'title' => 'Chỉnh sửa câu hỏi',
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $cauHoi = NganHangCauHoi::with('dapAns')->findOrFail($id);
        $payload = $this->buildQuestionPayload($request, $cauHoi);

        $this->authorizeCourseAccess($user, $payload['khoa_hoc_id'], (int) $cauHoi->khoa_hoc_id);

        if (NganHangCauHoi::isDuplicate($payload['khoa_hoc_id'], $payload['noi_dung'], $cauHoi->id)) {
            throw ValidationException::withMessages([
                'noi_dung_cau_hoi' => 'Nội dung câu hỏi này đã tồn tại trong khóa học này.',
            ]);
        }

        DB::transaction(function () use ($cauHoi, $payload) {
            $cauHoi->update([
                'khoa_hoc_id' => $payload['khoa_hoc_id'],
                'module_hoc_id' => $payload['module_hoc_id'],
                'ma_cau_hoi' => $payload['ma_cau_hoi'] ?: $cauHoi->ma_cau_hoi ?: NganHangCauHoi::generateQuestionCode(),
                'noi_dung' => $payload['noi_dung'],
                'loai_cau_hoi' => $payload['loai_cau_hoi'],
                'muc_do' => $payload['muc_do'],
                'diem_mac_dinh' => $payload['diem_mac_dinh'],
                'trang_thai' => $payload['trang_thai'],
                'co_the_tai_su_dung' => $payload['co_the_tai_su_dung'],
            ]);

            $this->syncAnswers($cauHoi, $payload['answers']);
        });

        return redirect()
            ->route('admin.kiem-tra-online.cau-hoi.index')
            ->with('success', 'Đã cập nhật câu hỏi thành công.');
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $cauHoi = NganHangCauHoi::findOrFail($id);

        $this->authorizeCourseAccess($user, (int) $cauHoi->khoa_hoc_id);

        $cauHoi->delete();

        return back()->with('success', 'Đã xóa câu hỏi.');
    }

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
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $headers);

            foreach ($sampleRows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'mau-nhap-cau-hoi.csv', [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }

    public function import(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'khoa_hoc_id' => 'required|exists:khoa_hoc,id',
            'file_import' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $khoaHocId = $request->integer('khoa_hoc_id');
        $this->authorizeCourseAccess($user, $khoaHocId);

        $khoaHoc = KhoaHoc::findOrFail($khoaHocId);
        $path = $request->file('file_import')->getRealPath();
        $file = fopen($path, 'r');

        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($file);
        }

        fgetcsv($file);

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
            if (empty(array_filter($row, fn ($value) => $value !== null && trim((string) $value) !== ''))) {
                continue;
            }

            $summary['total']++;

            $rawCauHoi = trim((string) ($row[0] ?? ''));
            $rawSai1 = trim((string) ($row[1] ?? ''));
            $rawSai2 = trim((string) ($row[2] ?? ''));
            $rawSai3 = trim((string) ($row[3] ?? ''));
            $rawDung = trim((string) ($row[4] ?? ''));

            $status = 'hop_le';
            $note = '';

            if ($rawCauHoi === '' || $rawSai1 === '' || $rawSai2 === '' || $rawSai3 === '' || $rawDung === '') {
                $status = 'loi_du_lieu';
                $note = 'Thiếu thông tin bắt buộc.';
            } else {
                $answers = [
                    ['ky_hieu' => 'A', 'noi_dung' => $rawDung, 'is_dap_an_dung' => true],
                    ['ky_hieu' => 'B', 'noi_dung' => $rawSai1, 'is_dap_an_dung' => false],
                    ['ky_hieu' => 'C', 'noi_dung' => $rawSai2, 'is_dap_an_dung' => false],
                    ['ky_hieu' => 'D', 'noi_dung' => $rawSai3, 'is_dap_an_dung' => false],
                ];

                try {
                    $this->validateAnswers($answers);
                } catch (ValidationException $exception) {
                    $status = 'loi_du_lieu';
                    $note = collect($exception->errors())->flatten()->first() ?? 'Dữ liệu đáp án không hợp lệ.';
                }
            }

            if ($status === 'hop_le') {
                $normalizedContent = NganHangCauHoi::normalizeString($rawCauHoi);

                if (isset($contentSetInFile[$normalizedContent])) {
                    $status = 'trung_lap_trong_file';
                    $note = 'Trùng với câu hỏi tại dòng ' . ($contentSetInFile[$normalizedContent] + 1) . ' trong file.';
                } else {
                    $contentSetInFile[$normalizedContent] = $summary['total'];

                    if (NganHangCauHoi::isDuplicate($khoaHocId, $rawCauHoi)) {
                        $status = 'trung_lap_trong_he_thong';
                        $note = 'Câu hỏi đã tồn tại trong ngân hàng của khóa học này.';
                    }
                }
            }

            match ($status) {
                'hop_le' => $summary['valid']++,
                'trung_lap_trong_file' => $summary['duplicate_file']++,
                'trung_lap_trong_he_thong' => $summary['duplicate_db']++,
                default => $summary['error']++,
            };

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

        session(['import_preview' => [
            'khoa_hoc_id' => $khoaHocId,
            'khoa_hoc_ten' => $khoaHoc->ten_khoa_hoc,
            'data' => $previewData,
            'summary' => $summary,
            'user_id' => auth()->id(),
        ]]);

        return redirect()->route('admin.kiem-tra-online.cau-hoi.preview');
    }

    public function preview()
    {
        $preview = session('import_preview');
        if (!$preview) {
            return redirect()->route('admin.kiem-tra-online.cau-hoi.index')->with('error', 'Không tìm thấy dữ liệu xem trước.');
        }

        return view('pages.admin.kiem-tra-online.cau-hoi.preview', compact('preview'));
    }

    public function confirmImport(Request $request)
    {
        $preview = session('import_preview');
        if (!$preview || $preview['user_id'] != auth()->id()) {
            session()->forget('import_preview');
            return redirect()->route('admin.kiem-tra-online.cau-hoi.index')->with('error', 'Hết hạn phiên làm việc hoặc phiên không hợp lệ. Hãy thử import lại.');
        }

        $khoaHocId = (int) $preview['khoa_hoc_id'];
        $data = $preview['data'];
        $createdCount = 0;
        $userId = auth()->user()->ma_nguoi_dung;

        DB::transaction(function () use ($khoaHocId, $data, &$createdCount, $userId) {
            foreach ($data as $item) {
                if ($item['status'] !== 'hop_le') {
                    continue;
                }

                $cauHoi = NganHangCauHoi::create([
                    'khoa_hoc_id' => $khoaHocId,
                    'nguoi_tao_id' => $userId,
                    'ma_cau_hoi' => NganHangCauHoi::generateQuestionCode(),
                    'noi_dung' => $item['noi_dung_cau_hoi'],
                    'loai_cau_hoi' => 'trac_nghiem',
                    'muc_do' => 'trung_binh',
                    'diem_mac_dinh' => 1,
                    'trang_thai' => 'san_sang',
                    'co_the_tai_su_dung' => true,
                ]);

                $this->syncAnswers($cauHoi, [
                    ['ky_hieu' => 'A', 'noi_dung' => $item['dap_an_dung'], 'is_dap_an_dung' => true],
                    ['ky_hieu' => 'B', 'noi_dung' => $item['dap_an_sai_1'], 'is_dap_an_dung' => false],
                    ['ky_hieu' => 'C', 'noi_dung' => $item['dap_an_sai_2'], 'is_dap_an_dung' => false],
                    ['ky_hieu' => 'D', 'noi_dung' => $item['dap_an_sai_3'], 'is_dap_an_dung' => false],
                ]);

                $createdCount++;
            }
        });

        session()->forget('import_preview');

        return redirect()
            ->route('admin.kiem-tra-online.cau-hoi.index')
            ->with('success', "Đã import thành công {$createdCount} câu hỏi.");
    }

    private function authorizeCourseAccess($user, int $khoaHocId, ?int $secondKhoaHocId = null): void
    {
        if (!$user->isGiangVien()) {
            return;
        }

        $giangVien = $user->giangVien;
        $courseIds = $giangVien
            ? $giangVien->khoaHocDuocPhanCong()->pluck('khoa_hoc.id')->unique()->map(fn ($id) => (int) $id)->all()
            : [];

        foreach (array_filter([$khoaHocId, $secondKhoaHocId]) as $courseId) {
            abort_unless(in_array((int) $courseId, $courseIds, true), 403, 'Bạn không được phân công cho khóa học này.');
        }
    }

    private function buildQuestionPayload(Request $request, ?NganHangCauHoi $existing = null): array
    {
        $validated = $request->validate([
            'khoa_hoc_id' => 'required|exists:khoa_hoc,id',
            'module_hoc_id' => 'nullable|exists:module_hoc,id',
            'ma_cau_hoi' => [
                'nullable',
                'string',
                'max:60',
                Rule::unique('ngan_hang_cau_hoi', 'ma_cau_hoi')->ignore($existing?->id),
            ],
            'noi_dung' => 'nullable|string',
            'noi_dung_cau_hoi' => 'nullable|string',
            'loai_cau_hoi' => 'nullable|in:trac_nghiem,tu_luan',
            'muc_do' => 'nullable|in:de,trung_binh,kho',
            'diem_mac_dinh' => 'nullable|numeric|min:0.25|max:100',
            'trang_thai' => 'nullable|string|max:50',
            'co_the_tai_su_dung' => 'nullable|boolean',
            'dap_an_dung' => 'nullable|string',
            'dap_an_sai_1' => 'nullable|string',
            'dap_an_sai_2' => 'nullable|string',
            'dap_an_sai_3' => 'nullable|string',
            'dap_ans' => 'nullable|array|min:2',
            'dap_ans.*.ky_hieu' => 'nullable|string|max:20',
            'dap_ans.*.noi_dung' => 'nullable|string',
        ]);

        $noiDung = trim((string) ($validated['noi_dung'] ?? $validated['noi_dung_cau_hoi'] ?? ''));
        if ($noiDung === '') {
            throw ValidationException::withMessages([
                'noi_dung_cau_hoi' => 'Nội dung câu hỏi là bắt buộc.',
            ]);
        }

        $moduleHocId = isset($validated['module_hoc_id']) && $validated['module_hoc_id'] !== null
            ? (int) $validated['module_hoc_id']
            : null;

        if ($moduleHocId !== null) {
            $moduleBelongsToCourse = ModuleHoc::query()
                ->where('id', $moduleHocId)
                ->where('khoa_hoc_id', (int) $validated['khoa_hoc_id'])
                ->exists();

            if (!$moduleBelongsToCourse) {
                throw ValidationException::withMessages([
                    'module_hoc_id' => 'Module không thuộc khóa học đã chọn.',
                ]);
            }
        }

        $loaiCauHoi = $validated['loai_cau_hoi'] ?? 'trac_nghiem';
        $answers = [];

        if ($loaiCauHoi === 'trac_nghiem') {
            $answers = !empty($validated['dap_ans'])
                ? $this->normalizeStructuredAnswers($validated)
                : $this->normalizeLegacyAnswers($validated);

            $this->validateAnswers($answers);
        }

        return [
            'khoa_hoc_id' => (int) $validated['khoa_hoc_id'],
            'module_hoc_id' => $moduleHocId,
            'ma_cau_hoi' => trim((string) ($validated['ma_cau_hoi'] ?? '')) ?: null,
            'noi_dung' => $noiDung,
            'loai_cau_hoi' => $loaiCauHoi,
            'muc_do' => $validated['muc_do'] ?? 'trung_binh',
            'diem_mac_dinh' => (float) ($validated['diem_mac_dinh'] ?? 1),
            'trang_thai' => $validated['trang_thai'] ?? 'san_sang',
            'co_the_tai_su_dung' => array_key_exists('co_the_tai_su_dung', $validated)
                ? (bool) $validated['co_the_tai_su_dung']
                : true,
            'answers' => $answers,
        ];
    }

    private function normalizeStructuredAnswers(array $validated): array
    {
        $correctToken = NganHangCauHoi::normalizeString($validated['dap_an_dung'] ?? '');
        $answers = [];

        foreach (array_values($validated['dap_ans'] ?? []) as $index => $answer) {
            $noiDung = trim((string) ($answer['noi_dung'] ?? ''));
            if ($noiDung === '') {
                continue;
            }

            $kyHieu = trim((string) ($answer['ky_hieu'] ?? ''));
            if ($kyHieu === '') {
                $kyHieu = chr(65 + $index);
            }

            $answers[] = [
                'ky_hieu' => $kyHieu,
                'noi_dung' => $noiDung,
                'is_dap_an_dung' => $correctToken !== '' && (
                    NganHangCauHoi::normalizeString($kyHieu) === $correctToken
                    || NganHangCauHoi::normalizeString($noiDung) === $correctToken
                ),
            ];
        }

        return $answers;
    }

    private function normalizeLegacyAnswers(array $validated): array
    {
        $requiredFields = ['dap_an_dung', 'dap_an_sai_1', 'dap_an_sai_2', 'dap_an_sai_3'];
        foreach ($requiredFields as $field) {
            if (blank($validated[$field] ?? null)) {
                throw ValidationException::withMessages([
                    $field => 'Vui lòng nhập đầy đủ 4 đáp án cho câu hỏi trắc nghiệm.',
                ]);
            }
        }

        return [
            ['ky_hieu' => 'A', 'noi_dung' => trim((string) $validated['dap_an_dung']), 'is_dap_an_dung' => true],
            ['ky_hieu' => 'B', 'noi_dung' => trim((string) $validated['dap_an_sai_1']), 'is_dap_an_dung' => false],
            ['ky_hieu' => 'C', 'noi_dung' => trim((string) $validated['dap_an_sai_2']), 'is_dap_an_dung' => false],
            ['ky_hieu' => 'D', 'noi_dung' => trim((string) $validated['dap_an_sai_3']), 'is_dap_an_dung' => false],
        ];
    }

    private function validateAnswers(array $answers): void
    {
        if (count($answers) < 2) {
            throw ValidationException::withMessages([
                'dap_ans' => 'Câu hỏi trắc nghiệm cần ít nhất 2 đáp án.',
            ]);
        }

        $normalizedContents = collect($answers)
            ->map(fn ($answer) => NganHangCauHoi::normalizeString($answer['noi_dung'] ?? ''));

        if ($normalizedContents->contains('')) {
            throw ValidationException::withMessages([
                'dap_ans' => 'Nội dung đáp án không được để trống.',
            ]);
        }

        if ($normalizedContents->unique()->count() !== $normalizedContents->count()) {
            throw ValidationException::withMessages([
                'dap_ans' => 'Các đáp án không được trùng nhau.',
            ]);
        }

        $correctCount = collect($answers)->where('is_dap_an_dung', true)->count();
        if ($correctCount !== 1) {
            throw ValidationException::withMessages([
                'dap_an_dung' => 'Phải xác định chính xác 1 đáp án đúng.',
            ]);
        }
    }

    private function syncAnswers(NganHangCauHoi $cauHoi, array $answers): void
    {
        $cauHoi->dapAns()->delete();

        if ($answers === []) {
            return;
        }

        $cauHoi->dapAns()->createMany(
            collect($answers)->values()->map(function (array $answer, int $index) {
                return [
                    'ky_hieu' => $answer['ky_hieu'] ?? chr(65 + $index),
                    'noi_dung' => $answer['noi_dung'],
                    'is_dap_an_dung' => (bool) ($answer['is_dap_an_dung'] ?? false),
                    'thu_tu' => $index + 1,
                ];
            })->all()
        );
    }
}
