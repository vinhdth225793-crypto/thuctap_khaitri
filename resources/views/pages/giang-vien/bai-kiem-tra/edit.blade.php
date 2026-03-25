@extends('layouts.app', ['title' => 'Cấu hình bài kiểm tra'])

@section('content')
@php
    $selectedQuestionIds = $baiKiemTra->chiTietCauHois->pluck('ngan_hang_cau_hoi_id')->map(fn ($id) => (int) $id)->all();
    $scoreByQuestionId = $baiKiemTra->chiTietCauHois->mapWithKeys(fn ($item) => [$item->ngan_hang_cau_hoi_id => $item->diem_so]);
@endphp

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ $baiKiemTra->tieu_de }}</h2>
            <p class="text-muted mb-0">{{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Không rõ khóa học' }} • {{ $baiKiemTra->moduleHoc->ten_module ?? 'Đề cuối khóa / dùng chung' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('giang-vien.khoa-hoc.show', $baiKiemTra->lich_hoc_id ? $baiKiemTra->lichHoc->module_hoc_id : ($baiKiemTra->module_hoc_id ?? 0)) }}" class="btn btn-outline-primary">Quay lại</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card vip-card h-100">
                <div class="card-body">
                    <div class="mb-3"><strong>Loại bài:</strong> {{ $baiKiemTra->loai_bai_kiem_tra_label }}</div>
                    <div class="mb-3"><strong>Trạng thái duyệt:</strong> {{ $baiKiemTra->trang_thai_duyet_label }}</div>
                    <div class="mb-3"><strong>Trạng thái phát hành:</strong> {{ $baiKiemTra->trang_thai_phat_hanh_label }}</div>
                    <div class="mb-3"><strong>Tổng điểm hiện tại:</strong> {{ number_format((float) $baiKiemTra->tong_diem, 2) }}</div>
                    <div class="mb-4"><strong>Câu hỏi đã chọn:</strong> {{ $baiKiemTra->chiTietCauHois->count() }}</div>

                    <form action="{{ route('giang-vien.bai-kiem-tra.submit', $baiKiemTra->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">Gửi admin duyệt</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card vip-card mb-4">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Thông tin bài kiểm tra</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('giang-vien.bai-kiem-tra.update', $baiKiemTra->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Tiêu đề</label>
                                <input type="text" name="tieu_de" value="{{ old('tieu_de', $baiKiemTra->tieu_de) }}" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Thời gian làm bài</label>
                                <input type="number" min="1" max="300" name="thoi_gian_lam_bai" value="{{ old('thoi_gian_lam_bai', $baiKiemTra->thoi_gian_lam_bai) }}" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ngày mở</label>
                                <input type="datetime-local" name="ngay_mo" value="{{ old('ngay_mo', optional($baiKiemTra->ngay_mo)->format('Y-m-d\\TH:i')) }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ngày đóng</label>
                                <input type="datetime-local" name="ngay_dong" value="{{ old('ngay_dong', optional($baiKiemTra->ngay_dong)->format('Y-m-d\\TH:i')) }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Số lần được làm</label>
                                <input type="number" min="1" max="10" name="so_lan_duoc_lam" value="{{ old('so_lan_duoc_lam', $baiKiemTra->so_lan_duoc_lam) }}" class="form-control" required>
                            </div>
                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check mt-4">
                                    <input type="checkbox" name="randomize_questions" value="1" class="form-check-input" @checked(old('randomize_questions', $baiKiemTra->randomize_questions))>
                                    <label class="form-check-label">Xáo trộn thứ tự câu hỏi</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Mô tả / hướng dẫn</label>
                                <textarea name="mo_ta" rows="4" class="form-control">{{ old('mo_ta', $baiKiemTra->mo_ta) }}</textarea>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-semibold">Chọn câu hỏi cho đề</h5>
                            <span class="badge bg-light text-dark">{{ $availableQuestions->count() }} câu hỏi khả dụng</span>
                        </div>

                        @forelse($availableQuestions as $question)
                            @php
                                $questionId = (int) $question->id;
                                $isSelected = in_array($questionId, old('question_ids', $selectedQuestionIds), true);
                                $scoreValue = old('question_scores.' . $questionId, $scoreByQuestionId[$questionId] ?? $question->diem_mac_dinh);
                            @endphp
                            <div class="border rounded-3 p-3 mb-3">
                                <div class="d-flex flex-wrap justify-content-between gap-3 mb-2">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="question-{{ $questionId }}" name="question_ids[]" value="{{ $questionId }}" @checked($isSelected)>
                                        <label class="form-check-label fw-semibold" for="question-{{ $questionId }}">
                                            [{{ $question->ma_cau_hoi }}] {{ \Illuminate\Support\Str::limit(strip_tags($question->noi_dung), 160) }}
                                        </label>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-light text-dark">{{ $question->loai_cau_hoi_label }}</span>
                                        <span class="badge bg-light text-dark">{{ $question->muc_do_label }}</span>
                                    </div>
                                </div>
                                <div class="row g-3 align-items-start">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Điểm câu này</label>
                                        <input type="number" step="0.25" min="0.25" name="question_scores[{{ $questionId }}]" value="{{ $scoreValue }}" class="form-control">
                                    </div>
                                    <div class="col-md-9">
                                        @if($question->loai_cau_hoi === 'trac_nghiem')
                                            <div class="small text-muted">
                                                @foreach($question->dapAns as $dapAn)
                                                    <div>{{ $dapAn->ky_hieu }}. {{ $dapAn->noi_dung }} @if($dapAn->is_dap_an_dung)<strong class="text-success">(Đúng)</strong>@endif</div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="small text-muted">{{ $question->goi_y_tra_loi ?: 'Câu tự luận, giảng viên sẽ chấm tay sau khi học viên nộp bài.' }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-warning">Chưa có câu hỏi sẵn sàng trong ngân hàng cho phạm vi bài kiểm tra này.</div>
                        @endforelse

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Lưu cấu hình đề</button>
                            <a href="{{ route('giang-vien.cham-diem.index') }}" class="btn btn-light border">Sang khu vực chấm điểm</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card vip-card">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Bài làm gần đây</h5>
                </div>
                <div class="card-body">
                    @forelse($baiKiemTra->baiLams as $baiLam)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="fw-semibold">{{ $baiLam->hocVien->ho_ten ?? 'Học viên' }}</div>
                            <div class="small text-muted">Lần {{ $baiLam->lan_lam_thu }} • {{ $baiLam->trang_thai_label }} • {{ $baiLam->nop_luc?->format('d/m/Y H:i') ?? 'Chưa nộp' }}</div>
                            <div class="small text-muted">Điểm: {{ $baiLam->diem_so !== null ? number_format((float) $baiLam->diem_so, 2) : 'Chưa chấm' }}</div>
                        </div>
                    @empty
                        <div class="text-muted">Chưa có bài làm nào cho đề này.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
