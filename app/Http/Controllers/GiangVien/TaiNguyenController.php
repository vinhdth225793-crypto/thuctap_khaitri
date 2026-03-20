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

        return back()->with('success', 'Da luu tai nguyen buoi hoc thanh cong.');
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
                ->withErrors(['file_dinh_kem' => 'Vui long tai len file hoac nhap link ngoai hop le.'])
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

        return back()->with('success', 'Da cap nhat tai nguyen buoi hoc thanh cong.');
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
            ? 'Da hien tai nguyen cho hoc vien.'
            : 'Da an tai nguyen voi hoc vien.';

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

        return back()->with('success', 'Da xoa tai nguyen buoi hoc thanh cong.');
    }

    private function authorizeGiangVienForLichHoc(LichHoc $lichHoc): void
    {
        $giangVien = auth()->user()?->giangVien;

        abort_if(! $giangVien, 403, 'Ban khong co quyen thuc hien thao tac nay.');

        $isAssigned = PhanCongModuleGiangVien::query()
            ->where('module_hoc_id', $lichHoc->module_hoc_id)
            ->where('giao_vien_id', $giangVien->id)
            ->where('trang_thai', 'da_nhan')
            ->exists();

        abort_unless($isAssigned, 403, 'Ban khong duoc phan cong day buoi hoc nay.');
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
