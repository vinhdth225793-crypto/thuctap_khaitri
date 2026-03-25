<?php

namespace App\Http\Controllers\GiangVien;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaiNguyenRequest;
use App\Models\LichHoc;
use App\Models\PhanCongModuleGiangVien;
use App\Models\TaiNguyenBuoiHoc;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaiNguyenController extends Controller
{
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
        $validated = $request->validate([
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'loai_tai_nguyen' => 'required|string',
            'link_ngoai' => 'nullable|url',
            'file_dinh_kem' => 'nullable|file|max:51200', // 50MB
            'pham_vi_su_dung' => 'required|in:ca_nhan,khoa_hoc,cong_khai',
        ]);

        $data = [
            'tieu_de' => $validated['tieu_de'],
            'mo_ta' => $validated['mo_ta'],
            'loai_tai_nguyen' => $validated['loai_tai_nguyen'],
            'link_ngoai' => $validated['link_ngoai'],
            'pham_vi_su_dung' => $validated['pham_vi_su_dung'],
            'nguoi_tao_id' => auth()->user()->ma_nguoi_dung,
            'vai_tro_nguoi_tao' => 'giang_vien',
            'trang_thai_duyet' => TaiNguyenBuoiHoc::STATUS_DUYET_NHAP,
            'trang_thai_xu_ly' => TaiNguyenBuoiHoc::STATUS_XU_LY_NONE,
        ];

        if ($request->hasFile('file_dinh_kem')) {
            $file = $request->file('file_dinh_kem');
            $path = $this->storeUploadedFileSimple($file);
            
            $data['duong_dan_file'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_extension'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
            $data['mime_type'] = $file->getMimeType();
            
            if ($data['loai_tai_nguyen'] === 'video') {
                $data['trang_thai_xu_ly'] = TaiNguyenBuoiHoc::STATUS_XU_LY_SAN_SANG; // TODO: Implement transcoding
            }
        }

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

        $validated = $request->validate([
            'tieu_de' => 'required|string|max:255',
            'mo_ta' => 'nullable|string',
            'loai_tai_nguyen' => 'required|string',
            'link_ngoai' => 'nullable|url',
            'file_dinh_kem' => 'nullable|file|max:51200',
            'pham_vi_su_dung' => 'required|in:ca_nhan,khoa_hoc,cong_khai',
        ]);

        $data = [
            'tieu_de' => $validated['tieu_de'],
            'mo_ta' => $validated['mo_ta'],
            'loai_tai_nguyen' => $validated['loai_tai_nguyen'],
            'link_ngoai' => $validated['link_ngoai'],
            'pham_vi_su_dung' => $validated['pham_vi_su_dung'],
        ];

        if ($request->hasFile('file_dinh_kem')) {
            // Xóa file cũ
            if ($taiNguyen->duong_dan_file) {
                Storage::disk('public')->delete($taiNguyen->duong_dan_file);
            }

            $file = $request->file('file_dinh_kem');
            $path = $this->storeUploadedFileSimple($file);
            
            $data['duong_dan_file'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_extension'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
            $data['mime_type'] = $file->getMimeType();
        }

        $taiNguyen->update($data);

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
            ! $request->hasFile('file_dinh_kem')
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

        abort_if(! $giangVien, 403, 'Bạn không có quyền thực hiện thao tác này.');

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
}

