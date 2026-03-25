@extends('layouts.app', ['title' => 'Chấm bài tự luận'])

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Chấm bài của {{ $baiLam->hocVien->ho_ten ?? 'Học viên' }}</h2>
            <p class="text-muted mb-0">{{ $baiLam->baiKiemTra->tieu_de }} • Lần làm {{ $baiLam->lan_lam_thu }}</p>
        </div>
        <a href="{{ route('giang-vien.cham-diem.index') }}" class="btn btn-outline-primary">Quay lại danh sách</a>
    </div>

    <div class="card vip-card">
        <div class="card-body">
            <form action="{{ route('giang-vien.cham-diem.store', $baiLam->id) }}" method="POST">
                @csrf

                @foreach($baiLam->chiTietTraLois as $index => $chiTiet)
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between gap-3 mb-2">
                            <div class="fw-semibold">Câu {{ $index + 1 }}. {!! nl2br(e($chiTiet->cauHoi->noi_dung ?? 'Không rõ nội dung')) !!}</div>
                            <span class="badge bg-light text-dark">{{ number_format((float) ($chiTiet->chiTietBaiKiemTra->diem_so ?? 0), 2) }} điểm</span>
                        </div>

                        @if($chiTiet->cauHoi->loai_cau_hoi === 'trac_nghiem')
                            <div class="small text-muted mb-2">Trả lời của học viên: {{ $chiTiet->dapAn->ky_hieu ?? 'Chưa chọn' }} - {{ $chiTiet->dapAn->noi_dung ?? 'Không có' }}</div>
                            <div class="small {{ $chiTiet->is_dung ? 'text-success' : 'text-danger' }}">
                                {{ $chiTiet->is_dung ? 'Đã đúng' : 'Sai / chưa có đáp án' }} • {{ number_format((float) ($chiTiet->diem_tu_dong ?? 0), 2) }} điểm tự động
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Câu trả lời của học viên</label>
                                <div class="border rounded-3 bg-light p-3">{!! nl2br(e($chiTiet->cau_tra_loi_text ?: 'Học viên chưa trả lời.')) !!}</div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Điểm tự luận</label>
                                    <input type="number" step="0.25" min="0" max="{{ $chiTiet->chiTietBaiKiemTra->diem_so ?? 0 }}" name="grades[{{ $chiTiet->id }}][diem_tu_luan]" value="{{ old('grades.' . $chiTiet->id . '.diem_tu_luan', $chiTiet->diem_tu_luan) }}" class="form-control">
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label small fw-semibold">Nhận xét</label>
                                    <textarea name="grades[{{ $chiTiet->id }}][nhan_xet]" rows="3" class="form-control">{{ old('grades.' . $chiTiet->id . '.nhan_xet', $chiTiet->nhan_xet) }}</textarea>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach

                <button type="submit" class="btn btn-primary">Lưu kết quả chấm</button>
            </form>
        </div>
    </div>
</div>
@endsection
