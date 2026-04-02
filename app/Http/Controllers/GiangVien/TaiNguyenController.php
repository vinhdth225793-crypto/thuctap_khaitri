<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaiNguyenRequest;
use App\Models\LichHoc;
use App\Models\PhanCongModuleGiangVien;
use App\Models\TaiNguyenBuoiHoc;
use App\Services\TeacherAssignmentResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TaiNguyenController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const LIBRARY_ALLOWED_EXTENSIONS = [
        'mp4', 'webm', 'mov', 'm4v',
        'pdf',
        'doc', 'docx',
        'ppt', 'pptx',
        'xls', 'xlsx', 'csv',
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'zip', 'rar', '7z',
        'txt',
    ];

    public function redirectToSession(Request $request, TeacherAssignmentResolver $assignmentResolver): RedirectResponse
    {
        $lichHoc = LichHoc::findOrFail((int) $request->query('lich_hoc_id'));

        $this->authorizeGiangVienForLichHoc($lichHoc);

        $giangVien = auth()->user()->giangVien;
        $assignmentId = $assignmentResolver->resolveForSchedule($giangVien->id, $lichHoc);

        abort_if($assignmentId === null, 403, 'Khong tim thay phan cong phu hop cho buoi hoc nay.');

        return redirect()->to(
            route('giang-vien.khoa-hoc.show', [
                'id' => $assignmentId,
                'focus_lich_hoc_id' => $lichHoc->id,
                'quick_action' => 'resources',
            ]) . '#session-' . $lichHoc->id
        );
    }

    public function index()
    {
        $giangVien = auth()->user()->giangVien;
        if (!$giangVien) {
            return redirect()->route('home')->with('error', 'Tài khoản chưa được liên kết với giảng viên.');
        }

        $taiNguyens = TaiNguyenBuoiHoc::where('nguoi_tao_id', auth()->user()->ma_nguoi_dung)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('pages.giang-vien.thu-vien.index', compact('taiNguyens'));
    }

    public function create()
    {
        return view('pages.giang-vien.thu-vien.create');
    }

    public function storeLibrary(Request $request)
    {
        $validated = $this->validateLibraryPayload($request);
        $data = $this->buildLibraryPayload($request, $validated);

        TaiNguyenBuoiHoc::create($data);

        return redirect()->route('giang-vien.thu-vien.index')->with('success', 'Đã thêm tài nguyên vào thư viện.');
    }

    public function edit($id)
    {
        $taiNguyen = TaiNguyenBuoiHoc::where('nguoi_tao_id', auth()->user()->ma_nguoi_dung)->findOrFail($id);

        return view('pages.giang-vien.thu-vien.edit', compact('taiNguyen'));
    }

    public function updateLibrary(Request $request, $id)
    {
        $taiNguyen = TaiNguyenBuoiHoc::where('nguoi_tao_id', auth()->user()->ma_nguoi_dung)->findOrFail($id);
        $validated = $this->validateLibraryPayload($request, $taiNguyen);
        $oldFilePath = $taiNguyen->duong_dan_file;
        $data = $this->buildLibraryPayload($request, $validated, $taiNguyen);

        if ($this->shouldResetLibraryApproval($taiNguyen, $validated, $request)) {
            $data = array_merge($data, [
                'trang_thai_duyet' => TaiNguyenBuoiHoc::STATUS_DUYET_NHAP,
                'ngay_gui_duyet' => null,
                'ngay_duyet' => null,
                'nguoi_duyet_id' => null,
                'ghi_chu_admin' => null,
            ]);
        }

        $taiNguyen->update($data);

        if ($request->hasFile('file_dinh_kem') && $oldFilePath) {
            Storage::disk('public')->delete($oldFilePath);
        }

        return redirect()->route('giang-vien.thu-vien.index')->with('success', 'Đã cập nhật tài nguyên.');
    }

    public function guiDuyet($id)
    {
        $taiNguyen = TaiNguyenBuoiHoc::where('nguoi_tao_id', auth()->user()->ma_nguoi_dung)->findOrFail($id);

        if ($taiNguyen->trang_thai_duyet !== TaiNguyenBuoiHoc::STATUS_DUYET_DA_DUYET) {
            $taiNguyen->update([
                'trang_thai_duyet' => TaiNguyenBuoiHoc::STATUS_DUYET_CHO,
                'ngay_gui_duyet' => now(),
            ]);

            return back()->with('success', 'Đã gửi yêu cầu duyệt tài nguyên.');
        }

        return back()->with('info', 'Tài nguyên đã được duyệt trước đó.');
    }

    public function destroyLibrary($id)
    {
        $taiNguyen = TaiNguyenBuoiHoc::where('nguoi_tao_id', auth()->user()->ma_nguoi_dung)->findOrFail($id);

        if ($taiNguyen->duong_dan_file) {
            Storage::disk('public')->delete($taiNguyen->duong_dan_file);
        }

        $taiNguyen->delete();

        return redirect()->route('giang-vien.thu-vien.index')->with('success', 'Đã xóa tài nguyên.');
    }

    private function storeUploadedFileSimple(UploadedFile $file): string
    {
        $fileName = now()->format('YmdHis') . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

        return $file->storeAs('uploads/thu-vien', $fileName, 'public');
    }

    public function store(StoreTaiNguyenRequest $request, int $lichHocId): RedirectResponse
    {
        $lichHoc = LichHoc::findOrFail($lichHocId);

        $this->authorizeGiangVienForLichHoc($lichHoc);

        $data = $request->safe()->only([
            'loai_tai_nguyen',
            'tieu_de',
            'mo_ta',
            'link_ngoai',
            'trang_thai_hien_thi',
            'thu_tu_hien_thi',
        ]);
        $data['lich_hoc_id'] = $lichHoc->id;

        if ($request->hasFile('file_dinh_kem')) {
            $data['duong_dan_file'] = $this->storeUploadedFile(
                $request->file('file_dinh_kem'),
                $lichHoc->id
            );
        }

        TaiNguyenBuoiHoc::create($data);

        return back()->with('success', 'Đã lưu tài nguyên buổi học thành công.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $taiNguyen = TaiNguyenBuoiHoc::with('lichHoc')->findOrFail($id);

        $this->authorizeGiangVienForLichHoc($taiNguyen->lichHoc);

        $validated = $request->validate([
            'loai_tai_nguyen' => 'required|in:bai_giang,tai_lieu,bai_tap',
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'link_ngoai' => 'nullable|url',
            'file_dinh_kem' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar,jpg,jpeg,png,gif,mp4,webm,mp3,wav|max:10240',
            'trang_thai_hien_thi' => 'nullable|in:an,hien',
            'thu_tu_hien_thi' => 'nullable|integer|min:0',
        ], [
            'file_dinh_kem.max' => 'Kich thuoc tep toi da 10MB.',
        ]);

        if (
            !$request->hasFile('file_dinh_kem')
            && blank($validated['link_ngoai'] ?? null)
            && blank($taiNguyen->duong_dan_file)
            && blank($taiNguyen->link_ngoai)
        ) {
            return back()
                ->withErrors(['file_dinh_kem' => 'Vui lòng tải lên file hoặc nhập link ngoài hợp lệ.'])
                ->withInput();
        }

        $data = collect($validated)->only([
            'loai_tai_nguyen',
            'tieu_de',
            'mo_ta',
            'link_ngoai',
            'trang_thai_hien_thi',
            'thu_tu_hien_thi',
        ])->all();

        if ($request->hasFile('file_dinh_kem')) {
            if ($taiNguyen->duong_dan_file) {
                Storage::disk('public')->delete($taiNguyen->duong_dan_file);
            }

            $data['duong_dan_file'] = $this->storeUploadedFile(
                $request->file('file_dinh_kem'),
                $taiNguyen->lichHoc->id
            );
        }

        $taiNguyen->update($data);

        return back()->with('success', 'Đã cập nhật tài nguyên buổi học thành công.');
    }

    public function toggleHienThi(int $id): RedirectResponse
    {
        $taiNguyen = TaiNguyenBuoiHoc::with('lichHoc')->findOrFail($id);

        $this->authorizeGiangVienForLichHoc($taiNguyen->lichHoc);

        $taiNguyen->trang_thai_hien_thi = $taiNguyen->trang_thai_hien_thi === 'hien'
            ? 'an'
            : 'hien';
        $taiNguyen->save();

        $message = $taiNguyen->trang_thai_hien_thi === 'hien'
            ? 'Đã hiển thị tài nguyên cho học viên.'
            : 'Đã ẩn tài nguyên với học viên.';

        return back()->with('success', $message);
    }

    public function destroy(int $id): RedirectResponse
    {
        $taiNguyen = TaiNguyenBuoiHoc::with('lichHoc')->findOrFail($id);

        $this->authorizeGiangVienForLichHoc($taiNguyen->lichHoc);

        if ($taiNguyen->duong_dan_file) {
            Storage::disk('public')->delete($taiNguyen->duong_dan_file);
        }

        $taiNguyen->delete();

        return back()->with('success', 'Đã xóa tài nguyên buổi học thành công.');
    }

    private function authorizeGiangVienForLichHoc(LichHoc $lichHoc): void
    {
        $giangVien = auth()->user()?->giangVien;

        abort_if(!$giangVien, 403, 'Bạn không có quyền thực hiện thao tác này.');

        $isAssigned = PhanCongModuleGiangVien::query()
            ->where('module_hoc_id', $lichHoc->module_hoc_id)
            ->where('giang_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->exists();

        abort_unless($isAssigned, 403, 'Bạn không được phân công dạy buổi học này.');
    }

    private function storeUploadedFile(UploadedFile $file, int $lichHocId): string
    {
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $sanitizedBaseName = Str::slug($baseName);

        $fileName = 'lich-hoc-'
            . $lichHocId
            . '-'
            . now()->format('YmdHis')
            . ($sanitizedBaseName !== '' ? '-' . $sanitizedBaseName : '')
            . ($extension !== '' ? '.' . $extension : '');

        return Storage::disk('public')->putFileAs(
            'tai-lieu-buoi-hoc',
            $file,
            $fileName,
            ['visibility' => 'public']
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validateLibraryPayload(Request $request, ?TaiNguyenBuoiHoc $existing = null): array
    {
        $validated = $request->validate([
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'loai_tai_nguyen' => 'required|in:video,pdf,word,powerpoint,excel,image,archive,link_ngoai,tai_lieu_khac',
            'link_ngoai' => 'nullable|url',
            'file_dinh_kem' => 'nullable|file|mimes:' . implode(',', self::LIBRARY_ALLOWED_EXTENSIONS) . '|max:51200',
            'pham_vi_su_dung' => 'required|in:ca_nhan,khoa_hoc,cong_khai',
        ], [
            'file_dinh_kem.max' => 'Kích thước tệp tối đa là 50MB.',
            'file_dinh_kem.mimes' => 'Định dạng tệp không được hỗ trợ cho thư viện.',
        ]);

        $validated['link_ngoai'] = $this->normalizeLink($validated['link_ngoai'] ?? null);

        $this->ensureLibrarySourceExists($request, $validated, $existing);
        $this->ensureLibraryTypeMatchesSource($request, $validated, $existing);

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function buildLibraryPayload(Request $request, array $validated, ?TaiNguyenBuoiHoc $existing = null): array
    {
        $data = [
            'tieu_de' => $validated['tieu_de'],
            'mo_ta' => $validated['mo_ta'] ?? null,
            'loai_tai_nguyen' => $validated['loai_tai_nguyen'],
            'link_ngoai' => $validated['link_ngoai'],
            'pham_vi_su_dung' => $validated['pham_vi_su_dung'],
        ];

        if ($existing === null) {
            $data['nguoi_tao_id'] = auth()->user()->ma_nguoi_dung;
            $data['vai_tro_nguoi_tao'] = 'giang_vien';
            $data['trang_thai_duyet'] = TaiNguyenBuoiHoc::STATUS_DUYET_NHAP;
            $data['trang_thai_xu_ly'] = TaiNguyenBuoiHoc::STATUS_XU_LY_NONE;
        }

        if ($request->hasFile('file_dinh_kem')) {
            $file = $request->file('file_dinh_kem');
            $path = $this->storeUploadedFileSimple($file);

            $data['duong_dan_file'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_extension'] = strtolower($file->getClientOriginalExtension());
            $data['file_size'] = $file->getSize();
            $data['mime_type'] = $file->getMimeType();
            $data['trang_thai_xu_ly'] = $this->resolveProcessingStatus($validated['loai_tai_nguyen']);
        } elseif ($existing === null) {
            $data['trang_thai_xu_ly'] = TaiNguyenBuoiHoc::STATUS_XU_LY_NONE;
        } elseif ($validated['loai_tai_nguyen'] === 'link_ngoai') {
            $data['trang_thai_xu_ly'] = TaiNguyenBuoiHoc::STATUS_XU_LY_NONE;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function ensureLibrarySourceExists(Request $request, array $validated, ?TaiNguyenBuoiHoc $existing = null): void
    {
        $hasUploadedFile = $request->hasFile('file_dinh_kem');
        $hasLink = filled($validated['link_ngoai']);
        $hasExistingFile = $existing !== null && filled($existing->duong_dan_file);

        if (!$hasUploadedFile && !$hasLink && !$hasExistingFile) {
            throw ValidationException::withMessages([
                'file_dinh_kem' => 'Vui lòng tải lên file hoặc nhập link ngoài hợp lệ.',
                'link_ngoai' => 'Vui lòng nhập link ngoài hợp lệ hoặc tải lên file.',
            ]);
        }

        if ($validated['loai_tai_nguyen'] === 'link_ngoai' && !$hasLink) {
            throw ValidationException::withMessages([
                'link_ngoai' => 'Loại tài nguyên liên kết ngoài bắt buộc phải có URL hợp lệ.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function ensureLibraryTypeMatchesSource(Request $request, array $validated, ?TaiNguyenBuoiHoc $existing = null): void
    {
        $loaiTaiNguyen = $validated['loai_tai_nguyen'];
        $hasUploadedFile = $request->hasFile('file_dinh_kem');

        if ($loaiTaiNguyen === 'link_ngoai') {
            if ($hasUploadedFile) {
                throw ValidationException::withMessages([
                    'file_dinh_kem' => 'Loại tài nguyên liên kết ngoài không hỗ trợ tải file đính kèm.',
                ]);
            }

            return;
        }

        if (filled($validated['link_ngoai'])) {
            return;
        }

        $extension = $hasUploadedFile
            ? strtolower((string) $request->file('file_dinh_kem')->getClientOriginalExtension())
            : strtolower((string) ($existing?->file_extension ?: pathinfo((string) $existing?->duong_dan_file, PATHINFO_EXTENSION)));

        if ($extension === '') {
            return;
        }

        $allowedExtensions = $this->allowedExtensionsForLibraryType($loaiTaiNguyen);

        if (!in_array($extension, $allowedExtensions, true)) {
            throw ValidationException::withMessages([
                'file_dinh_kem' => 'Tệp hiện tại không phù hợp với loại tài nguyên đã chọn.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function shouldResetLibraryApproval(TaiNguyenBuoiHoc $taiNguyen, array $validated, Request $request): bool
    {
        if ($taiNguyen->trang_thai_duyet !== TaiNguyenBuoiHoc::STATUS_DUYET_DA_DUYET) {
            return false;
        }

        $linkChanged = $this->normalizeLink($taiNguyen->link_ngoai) !== $validated['link_ngoai'];

        return $request->hasFile('file_dinh_kem')
            || $taiNguyen->loai_tai_nguyen !== $validated['loai_tai_nguyen']
            || $taiNguyen->pham_vi_su_dung !== $validated['pham_vi_su_dung']
            || $linkChanged;
    }

    /**
     * @return array<int, string>
     */
    private function allowedExtensionsForLibraryType(string $loaiTaiNguyen): array
    {
        return match ($loaiTaiNguyen) {
            'video' => ['mp4', 'webm', 'mov', 'm4v'],
            'pdf' => ['pdf'],
            'word' => ['doc', 'docx'],
            'powerpoint' => ['ppt', 'pptx'],
            'excel' => ['xls', 'xlsx', 'csv'],
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'archive' => ['zip', 'rar', '7z'],
            'tai_lieu_khac' => self::LIBRARY_ALLOWED_EXTENSIONS,
            default => self::LIBRARY_ALLOWED_EXTENSIONS,
        };
    }

    private function resolveProcessingStatus(string $loaiTaiNguyen): string
    {
        return $loaiTaiNguyen === 'video'
            ? TaiNguyenBuoiHoc::STATUS_XU_LY_SAN_SANG
            : TaiNguyenBuoiHoc::STATUS_XU_LY_NONE;
    }

    private function normalizeLink(?string $link): ?string
    {
        $normalized = trim((string) $link);

        return $normalized === '' ? null : $normalized;
    }
}
