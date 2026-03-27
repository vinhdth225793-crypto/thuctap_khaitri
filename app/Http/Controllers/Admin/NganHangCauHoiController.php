<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpsertNganHangCauHoiRequest;
use App\Models\KhoaHoc;
use App\Models\ModuleHoc;
use App\Models\NganHangCauHoi;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NganHangCauHoiController extends Controller
{
    private function getAccessibleKhoaHocs(): Collection
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

    private function getAccessibleModules(Collection $khoaHocs): Collection
    {
        if ($khoaHocs->isEmpty()) {
            return collect();
        }

        return ModuleHoc::query()
            ->whereIn('khoa_hoc_id', $khoaHocs->pluck('id'))
            ->orderBy('khoa_hoc_id')
            ->orderBy('thu_tu_module')
            ->get(['id', 'khoa_hoc_id', 'ten_module', 'ma_module']);
    }

    private function getViewOptions(): array
    {
        return [
            'difficultyOptions' => [
                'de' => 'Dễ',
                'trung_binh' => 'Trung bình',
                'kho' => 'Khó',
            ],
            'statusOptions' => [
                NganHangCauHoi::TRANG_THAI_NHAP => 'Nháp',
                NganHangCauHoi::TRANG_THAI_SAN_SANG => 'Sẵn sàng',
                NganHangCauHoi::TRANG_THAI_TAM_AN => 'Tạm ẩn',
            ],
            'questionTypeOptions' => [
                NganHangCauHoi::LOAI_TRAC_NGHIEM => 'Trắc nghiệm',
                NganHangCauHoi::LOAI_TU_LUAN => 'Tự luận',
            ],
            'answerModeOptions' => [
                NganHangCauHoi::KIEU_MOT_DAP_AN => 'Một đáp án đúng',
                NganHangCauHoi::KIEU_NHIEU_DAP_AN => 'Nhiều đáp án đúng',
                NganHangCauHoi::KIEU_DUNG_SAI => 'Đúng / Sai',
            ],
        ];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $khoaHocs = $this->getAccessibleKhoaHocs();
        $modules = $this->getAccessibleModules($khoaHocs);
        $khoaHocIds = $khoaHocs->pluck('id')->all();

        $query = NganHangCauHoi::with(['khoaHoc', 'moduleHoc', 'nguoiTao', 'dapAns']);

        if ($user->isGiangVien()) {
            $query->whereIn('khoa_hoc_id', $khoaHocIds);
        }

        if ($request->filled('khoa_hoc_id')) {
            $query->where('khoa_hoc_id', $request->integer('khoa_hoc_id'));
        }

        if ($request->filled('module_hoc_id')) {
            $query->where('module_hoc_id', $request->integer('module_hoc_id'));
        }

        if ($request->filled('loai_cau_hoi')) {
            $query->where('loai_cau_hoi', (string) $request->string('loai_cau_hoi'));
        }

        if ($request->filled('kieu_dap_an')) {
            $query->where('kieu_dap_an', (string) $request->string('kieu_dap_an'));
        }

        if ($request->filled('muc_do')) {
            $query->where('muc_do', (string) $request->string('muc_do'));
        }

        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', (string) $request->string('trang_thai'));
        }

        if ($request->filled('co_the_tai_su_dung')) {
            $query->where('co_the_tai_su_dung', $request->string('co_the_tai_su_dung') === '1');
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->where(function ($nestedQuery) use ($search) {
                $nestedQuery->where('noi_dung', 'like', "%{$search}%")
                    ->orWhere('ma_cau_hoi', 'like', "%{$search}%");
            });
        }

        $cauHois = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('pages.admin.question-bank.index', array_merge(
            compact('cauHois', 'khoaHocs', 'modules'),
            $this->getViewOptions()
        ));
    }

    public function create()
    {
        $khoaHocs = $this->getAccessibleKhoaHocs();
        $modules = $this->getAccessibleModules($khoaHocs);

        return view('pages.admin.question-bank.form', array_merge([
            'cauHoi' => new NganHangCauHoi([
                'loai_cau_hoi' => NganHangCauHoi::LOAI_TRAC_NGHIEM,
                'kieu_dap_an' => NganHangCauHoi::KIEU_MOT_DAP_AN,
                'muc_do' => 'trung_binh',
                'diem_mac_dinh' => 1,
                'trang_thai' => NganHangCauHoi::TRANG_THAI_NHAP,
                'co_the_tai_su_dung' => true,
            ]),
            'khoaHocs' => $khoaHocs,
            'modules' => $modules,
            'action' => route('admin.kiem-tra-online.cau-hoi.store'),
            'method' => 'POST',
            'title' => 'Thêm mới câu hỏi',
        ], $this->getViewOptions()));
    }

    public function store(UpsertNganHangCauHoiRequest $request)
    {
        $user = auth()->user();
        $payload = $this->buildQuestionPayload($request->validated());

        $this->authorizeCourseAccess($user, $payload['khoa_hoc_id']);

        if (NganHangCauHoi::isDuplicate($payload['khoa_hoc_id'], $payload['noi_dung'])) {
            throw ValidationException::withMessages([
                'noi_dung_cau_hoi' => 'Nội dung câu hỏi này đã tồn tại trong khóa học đã chọn.',
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
                'kieu_dap_an' => $payload['kieu_dap_an'],
                'muc_do' => $payload['muc_do'],
                'diem_mac_dinh' => $payload['diem_mac_dinh'],
                'goi_y_tra_loi' => $payload['goi_y_tra_loi'],
                'giai_thich_dap_an' => $payload['giai_thich_dap_an'],
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
        $khoaHocs = $this->getAccessibleKhoaHocs();
        $modules = $this->getAccessibleModules($khoaHocs);

        $this->authorizeCourseAccess($user, (int) $cauHoi->khoa_hoc_id);

        return view('pages.admin.question-bank.form', array_merge([
            'cauHoi' => $cauHoi,
            'khoaHocs' => $khoaHocs,
            'modules' => $modules,
            'action' => route('admin.kiem-tra-online.cau-hoi.update', $cauHoi->id),
            'method' => 'PUT',
            'title' => 'Chỉnh sửa câu hỏi',
        ], $this->getViewOptions()));
    }

    public function update(UpsertNganHangCauHoiRequest $request, $id)
    {
        $user = auth()->user();
        $cauHoi = NganHangCauHoi::with('dapAns')->findOrFail($id);
        $payload = $this->buildQuestionPayload($request->validated(), $cauHoi);

        $this->authorizeCourseAccess($user, $payload['khoa_hoc_id'], (int) $cauHoi->khoa_hoc_id);

        if (NganHangCauHoi::isDuplicate($payload['khoa_hoc_id'], $payload['noi_dung'], $cauHoi->id)) {
            throw ValidationException::withMessages([
                'noi_dung_cau_hoi' => 'Nội dung câu hỏi này đã tồn tại trong khóa học đã chọn.',
            ]);
        }

        DB::transaction(function () use ($cauHoi, $payload) {
            $cauHoi->update([
                'khoa_hoc_id' => $payload['khoa_hoc_id'],
                'module_hoc_id' => $payload['module_hoc_id'],
                'ma_cau_hoi' => $payload['ma_cau_hoi'] ?: $cauHoi->ma_cau_hoi ?: NganHangCauHoi::generateQuestionCode(),
                'noi_dung' => $payload['noi_dung'],
                'loai_cau_hoi' => $payload['loai_cau_hoi'],
                'kieu_dap_an' => $payload['kieu_dap_an'],
                'muc_do' => $payload['muc_do'],
                'diem_mac_dinh' => $payload['diem_mac_dinh'],
                'goi_y_tra_loi' => $payload['goi_y_tra_loi'],
                'giai_thich_dap_an' => $payload['giai_thich_dap_an'],
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

    public function toggleStatus($id)
    {
        $cauHoi = NganHangCauHoi::findOrFail($id);
        $this->authorizeCourseAccess(auth()->user(), (int) $cauHoi->khoa_hoc_id);

        $nextStatus = match ($cauHoi->trang_thai) {
            NganHangCauHoi::TRANG_THAI_SAN_SANG => NganHangCauHoi::TRANG_THAI_TAM_AN,
            NganHangCauHoi::TRANG_THAI_TAM_AN => NganHangCauHoi::TRANG_THAI_SAN_SANG,
            default => NganHangCauHoi::TRANG_THAI_SAN_SANG,
        };

        $cauHoi->update(['trang_thai' => $nextStatus]);

        return back()->with('success', 'Đã cập nhật trạng thái hiển thị của câu hỏi.');
    }

    public function toggleReusable($id)
    {
        $cauHoi = NganHangCauHoi::findOrFail($id);
        $this->authorizeCourseAccess(auth()->user(), (int) $cauHoi->khoa_hoc_id);

        $cauHoi->update([
            'co_the_tai_su_dung' => !$cauHoi->co_the_tai_su_dung,
        ]);

        return back()->with('success', 'Đã cập nhật cờ tái sử dụng của câu hỏi.');
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
                    $this->validateAnswers($answers, NganHangCauHoi::KIEU_MOT_DAP_AN);
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
            return redirect()
                ->route('admin.kiem-tra-online.cau-hoi.index')
                ->with('error', 'Không tìm thấy dữ liệu xem trước.');
        }

        return view('pages.admin.question-bank.preview', compact('preview'));
    }

    public function confirmImport(Request $request)
    {
        $preview = session('import_preview');
        if (!$preview || $preview['user_id'] != auth()->id()) {
            session()->forget('import_preview');

            return redirect()
                ->route('admin.kiem-tra-online.cau-hoi.index')
                ->with('error', 'Phiên import không còn hợp lệ. Vui lòng thực hiện lại.');
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
                    'loai_cau_hoi' => NganHangCauHoi::LOAI_TRAC_NGHIEM,
                    'kieu_dap_an' => NganHangCauHoi::KIEU_MOT_DAP_AN,
                    'muc_do' => 'trung_binh',
                    'diem_mac_dinh' => 1,
                    'trang_thai' => NganHangCauHoi::TRANG_THAI_SAN_SANG,
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

    private function buildQuestionPayload(array $validated, ?NganHangCauHoi $existing = null): array
    {
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

        $loaiCauHoi = (string) $validated['loai_cau_hoi'];
        $kieuDapAn = $loaiCauHoi === NganHangCauHoi::LOAI_TU_LUAN
            ? null
            : (string) ($validated['kieu_dap_an'] ?? NganHangCauHoi::KIEU_MOT_DAP_AN);

        $answers = $this->buildAnswers($validated, $loaiCauHoi, $kieuDapAn);

        return [
            'khoa_hoc_id' => (int) $validated['khoa_hoc_id'],
            'module_hoc_id' => $moduleHocId,
            'ma_cau_hoi' => trim((string) ($validated['ma_cau_hoi'] ?? '')) ?: ($existing?->ma_cau_hoi ?: NganHangCauHoi::generateQuestionCode()),
            'noi_dung' => $noiDung,
            'loai_cau_hoi' => $loaiCauHoi,
            'kieu_dap_an' => $kieuDapAn,
            'muc_do' => (string) ($validated['muc_do'] ?? 'trung_binh'),
            'diem_mac_dinh' => (float) ($validated['diem_mac_dinh'] ?? 1),
            'goi_y_tra_loi' => trim((string) ($validated['goi_y_tra_loi'] ?? '')) ?: null,
            'giai_thich_dap_an' => trim((string) ($validated['giai_thich_dap_an'] ?? '')) ?: null,
            'trang_thai' => (string) ($validated['trang_thai'] ?? NganHangCauHoi::TRANG_THAI_NHAP),
            'co_the_tai_su_dung' => array_key_exists('co_the_tai_su_dung', $validated)
                ? (bool) $validated['co_the_tai_su_dung']
                : true,
            'answers' => $answers,
        ];
    }

    private function buildAnswers(array $validated, string $loaiCauHoi, ?string $kieuDapAn): array
    {
        if ($loaiCauHoi === NganHangCauHoi::LOAI_TU_LUAN) {
            return [];
        }

        if ($kieuDapAn === NganHangCauHoi::KIEU_DUNG_SAI) {
            $answers = $this->buildTrueFalseAnswers($validated);
            $this->validateAnswers($answers, NganHangCauHoi::KIEU_DUNG_SAI);

            return $answers;
        }

        $answers = !empty($validated['dap_ans'])
            ? $this->normalizeStructuredAnswers($validated, $kieuDapAn ?? NganHangCauHoi::KIEU_MOT_DAP_AN)
            : $this->normalizeLegacyAnswers($validated);

        $this->validateAnswers($answers, $kieuDapAn ?? NganHangCauHoi::KIEU_MOT_DAP_AN);

        return $answers;
    }

    private function buildTrueFalseAnswers(array $validated): array
    {
        $dapAnDungSai = (string) ($validated['dap_an_dung_sai'] ?? '');

        if (!in_array($dapAnDungSai, ['dung', 'sai'], true)) {
            throw ValidationException::withMessages([
                'dap_an_dung_sai' => 'Vui lòng chọn đáp án đúng cho câu hỏi Đúng / Sai.',
            ]);
        }

        return [
            [
                'ky_hieu' => 'A',
                'noi_dung' => 'Đúng',
                'is_dap_an_dung' => $dapAnDungSai === 'dung',
            ],
            [
                'ky_hieu' => 'B',
                'noi_dung' => 'Sai',
                'is_dap_an_dung' => $dapAnDungSai === 'sai',
            ],
        ];
    }

    private function normalizeStructuredAnswers(array $validated, string $kieuDapAn): array
    {
        $correctAnswerKey = (string) ($validated['correct_answer_key'] ?? '');
        $correctAnswerKeys = collect($validated['correct_answer_keys'] ?? [])
            ->map(fn ($key) => (string) $key)
            ->values()
            ->all();
        $legacyCorrectToken = NganHangCauHoi::normalizeString($validated['dap_an_dung'] ?? '');

        $answers = [];
        $answerOrder = 0;

        foreach (($validated['dap_ans'] ?? []) as $rawKey => $answer) {
            $noiDung = trim((string) ($answer['noi_dung'] ?? ''));
            if ($noiDung === '') {
                continue;
            }

            $kyHieu = trim((string) ($answer['ky_hieu'] ?? ''));
            if ($kyHieu === '') {
                $kyHieu = $this->resolveAnswerKey($answerOrder);
            }

            $normalizedRawKey = (string) $rawKey;
            $normalizedKyHieu = NganHangCauHoi::normalizeString($kyHieu);
            $normalizedNoiDung = NganHangCauHoi::normalizeString($noiDung);

            $isCorrect = match ($kieuDapAn) {
                NganHangCauHoi::KIEU_MOT_DAP_AN => ($correctAnswerKey !== '' && $normalizedRawKey === $correctAnswerKey)
                    || ($legacyCorrectToken !== '' && ($normalizedKyHieu === $legacyCorrectToken || $normalizedNoiDung === $legacyCorrectToken)),
                NganHangCauHoi::KIEU_NHIEU_DAP_AN => in_array($normalizedRawKey, $correctAnswerKeys, true),
                default => false,
            };

            $answers[] = [
                'ky_hieu' => $kyHieu,
                'noi_dung' => $noiDung,
                'is_dap_an_dung' => $isCorrect,
            ];

            $answerOrder++;
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

    private function validateAnswers(array $answers, string $kieuDapAn): void
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

        if ($kieuDapAn === NganHangCauHoi::KIEU_MOT_DAP_AN && $correctCount !== 1) {
            throw ValidationException::withMessages([
                'correct_answer_key' => 'Câu hỏi một đáp án đúng phải có chính xác 1 đáp án đúng.',
            ]);
        }

        if ($kieuDapAn === NganHangCauHoi::KIEU_NHIEU_DAP_AN && $correctCount < 2) {
            throw ValidationException::withMessages([
                'correct_answer_keys' => 'Câu hỏi nhiều đáp án đúng phải có ít nhất 2 đáp án đúng.',
            ]);
        }

        if ($kieuDapAn === NganHangCauHoi::KIEU_DUNG_SAI && (count($answers) !== 2 || $correctCount !== 1)) {
            throw ValidationException::withMessages([
                'dap_an_dung_sai' => 'Câu hỏi Đúng / Sai phải có đúng 2 đáp án và 1 đáp án đúng.',
            ]);
        }
    }

    private function resolveAnswerKey(int $index): string
    {
        return chr(65 + $index);
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
                    'ky_hieu' => $answer['ky_hieu'] ?? $this->resolveAnswerKey($index),
                    'noi_dung' => $answer['noi_dung'],
                    'is_dap_an_dung' => (bool) ($answer['is_dap_an_dung'] ?? false),
                    'thu_tu' => $index + 1,
                ];
            })->all()
        );
    }
}
