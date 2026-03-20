@extends('layouts.app', ['title' => 'Lam bai kiem tra'])

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.bai-kiem-tra') }}">Bai kiem tra</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $baiKiemTra->tieu_de }}</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-1">{{ $baiKiemTra->tieu_de }}</h2>
            <p class="text-muted mb-0">{{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Chua xac dinh khoa hoc' }}</p>
        </div>

        <a href="{{ route('hoc-vien.bai-kiem-tra') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Danh sach bai kiem tra
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card vip-card h-100">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Thong tin bai kiem tra</h5>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="label">Trang thai mo bai</span>
                        <span class="badge bg-{{ $baiKiemTra->access_status_color }}">{{ $baiKiemTra->access_status_label }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Loai bai</span>
                        <strong>{{ $baiKiemTra->loai_bai_kiem_tra_label }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Loai noi dung</span>
                        <strong>{{ $baiKiemTra->loai_noi_dung_label }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Thoi gian lam bai</span>
                        <strong>{{ $baiKiemTra->thoi_gian_lam_bai }} phut</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Tong diem</span>
                        <strong>{{ number_format((float) $baiKiemTra->tong_diem, 2) }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">So cau hoi</span>
                        <strong>{{ $baiKiemTra->question_count }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">So lan duoc lam</span>
                        <strong>{{ $baiKiemTra->so_lan_duoc_lam }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Da su dung</span>
                        <strong>{{ $attemptsUsed }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Con lai</span>
                        <strong>{{ $remainingAttempts }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Ngay mo</span>
                        <strong>{{ $baiKiemTra->ngay_mo ? $baiKiemTra->ngay_mo->format('d/m/Y H:i') : 'Mo ngay' }}</strong>
                    </div>
                    <div class="info-row">
                        <span class="label">Ngay dong</span>
                        <strong>{{ $baiKiemTra->ngay_dong ? $baiKiemTra->ngay_dong->format('d/m/Y H:i') : 'Chua dat lich dong' }}</strong>
                    </div>

                    @if($baiLam)
                        <hr>
                        <div class="info-row">
                            <span class="label">Trang thai bai lam</span>
                            <span class="badge bg-{{ $baiLam->trang_thai_color }}">{{ $baiLam->trang_thai_label }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Lan lam</span>
                            <strong>{{ $baiLam->lan_lam_thu }}</strong>
                        </div>
                        <div class="info-row">
                            <span class="label">Bat dau luc</span>
                            <strong>{{ $baiLam->bat_dau_luc ? $baiLam->bat_dau_luc->format('d/m/Y H:i') : 'Chua bat dau' }}</strong>
                        </div>
                        <div class="info-row">
                            <span class="label">Nop luc</span>
                            <strong>{{ $baiLam->nop_luc ? $baiLam->nop_luc->format('d/m/Y H:i') : 'Chua nop' }}</strong>
                        </div>
                        <div class="info-row">
                            <span class="label">Diem hien tai</span>
                            <strong>{{ $baiLam->diem_so !== null ? number_format((float) $baiLam->diem_so, 2) : 'Dang cho cham' }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card vip-card">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Noi dung bai kiem tra</h5>
                    @if(!$baiLam && $baiKiemTra->can_student_start)
                        <form action="{{ route('hoc-vien.bai-kiem-tra.bat-dau', $baiKiemTra->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">Bat dau lam bai</button>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    @if($baiKiemTra->mo_ta)
                        <div class="description-box mb-4">
                            {!! nl2br(e($baiKiemTra->mo_ta)) !!}
                        </div>
                    @endif

                    @if($baiLam && $baiLam->is_submitted)
                        <div class="alert alert-success">
                            Ban da nop bai vao luc {{ $baiLam->nop_luc?->format('d/m/Y H:i') }}.
                            @if($baiLam->need_manual_grading)
                                Bai lam dang cho giang vien cham tu luan.
                            @endif
                        </div>
                    @elseif(!$baiLam && !$baiKiemTra->can_student_start)
                        <div class="alert alert-secondary">
                            Bai kiem tra nay hien {{ strtolower($baiKiemTra->access_status_label) }}. Ban se co the bat dau khi bai kiem tra den thoi gian mo.
                        </div>
                    @elseif(!$baiLam)
                        <div class="alert alert-primary">
                            Ban chua bat dau bai kiem tra nay. Khi bam bat dau, he thong se tao lan lam bai moi cho ban.
                        </div>
                    @endif

                    @if($baiLam && $baiLam->can_resume)
                        <form action="{{ route('hoc-vien.bai-kiem-tra.nop', $baiKiemTra->id) }}" method="POST">
                            @csrf

                            @if($cauHoiHienThi->isEmpty())
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Noi dung bai lam</label>
                                    <textarea name="noi_dung_bai_lam" rows="14" class="form-control" required>{{ old('noi_dung_bai_lam', $baiLam->noi_dung_bai_lam) }}</textarea>
                                </div>
                            @else
                                @foreach($cauHoiHienThi as $index => $chiTiet)
                                    @php
                                        $answerDetail = $baiLam->chiTietTraLois->firstWhere('chi_tiet_bai_kiem_tra_id', $chiTiet->id);
                                        $question = $chiTiet->cauHoi;
                                    @endphp
                                    <div class="question-card mb-4">
                                        <div class="d-flex justify-content-between gap-3 mb-3">
                                            <div class="fw-semibold">Cau {{ $index + 1 }}. {!! nl2br(e($question->noi_dung ?? 'Khong ro noi dung')) !!}</div>
                                            <span class="badge bg-light text-dark">{{ number_format((float) $chiTiet->diem_so, 2) }} diem</span>
                                        </div>

                                        @if($question->loai_cau_hoi === 'trac_nghiem')
                                            @foreach($question->dapAns as $dapAn)
                                                <label class="answer-option">
                                                    <input type="radio" name="answers[{{ $chiTiet->id }}][dap_an_cau_hoi_id]" value="{{ $dapAn->id }}" @checked(old('answers.' . $chiTiet->id . '.dap_an_cau_hoi_id', $answerDetail->dap_an_cau_hoi_id ?? null) == $dapAn->id)>
                                                    <span><strong>{{ $dapAn->ky_hieu }}.</strong> {{ $dapAn->noi_dung }}</span>
                                                </label>
                                            @endforeach
                                        @else
                                            <textarea name="answers[{{ $chiTiet->id }}][cau_tra_loi_text]" rows="6" class="form-control" placeholder="Nhap cau tra loi cua ban...">{{ old('answers.' . $chiTiet->id . '.cau_tra_loi_text', $answerDetail->cau_tra_loi_text ?? '') }}</textarea>
                                        @endif
                                    </div>
                                @endforeach
                            @endif

                            <div class="d-flex justify-content-between align-items-center gap-3">
                                <div class="text-muted small">
                                    Hay kiem tra lai cau tra loi truoc khi nop bai. Sau khi nop, bai lam se duoc chuyen sang trang thai cham diem.
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane me-2"></i>Nop bai
                                </button>
                            </div>
                        </form>
                    @elseif($baiLam && $baiLam->is_submitted)
                        @if($baiLam->chiTietTraLois->isNotEmpty())
                            @foreach($baiLam->chiTietTraLois as $index => $chiTiet)
                                <div class="question-card mb-4">
                                    <div class="d-flex justify-content-between gap-3 mb-3">
                                        <div class="fw-semibold">Cau {{ $index + 1 }}. {!! nl2br(e($chiTiet->cauHoi->noi_dung ?? 'Khong ro noi dung')) !!}</div>
                                        <span class="badge bg-light text-dark">{{ number_format((float) ($chiTiet->chiTietBaiKiemTra->diem_so ?? 0), 2) }} diem</span>
                                    </div>
                                    @if($chiTiet->cauHoi->loai_cau_hoi === 'trac_nghiem')
                                        <div class="submitted-box">
                                            Dap an da chon: {{ $chiTiet->dapAn->ky_hieu ?? 'Chua chon' }} - {{ $chiTiet->dapAn->noi_dung ?? 'Khong co' }}<br>
                                            Ket qua: {{ $chiTiet->is_dung ? 'Dung' : 'Sai / chua co dap an' }}<br>
                                            Diem: {{ number_format((float) ($chiTiet->diem_tu_dong ?? 0), 2) }}
                                        </div>
                                    @else
                                        <div class="submitted-box mb-2">{!! nl2br(e($chiTiet->cau_tra_loi_text ?: 'Khong co cau tra loi.')) !!}</div>
                                        <div class="small text-muted">Diem tu luan: {{ $chiTiet->diem_tu_luan !== null ? number_format((float) $chiTiet->diem_tu_luan, 2) : 'Dang cho cham' }}</div>
                                        @if($chiTiet->nhan_xet)
                                            <div class="small text-muted">Nhan xet: {{ $chiTiet->nhan_xet }}</div>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="submitted-box">
                                {!! nl2br(e($baiLam->noi_dung_bai_lam ?: 'Khong co noi dung bai lam.')) !!}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .info-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.85rem 0;
        border-bottom: 1px solid #eef2f7;
    }

    .info-row:first-child {
        padding-top: 0;
    }

    .info-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .info-row .label {
        color: #64748b;
        font-size: 0.92rem;
    }

    .description-box,
    .submitted-box,
    .question-card {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #f8fafc;
        padding: 1.25rem;
        line-height: 1.8;
    }

    .submitted-box {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .answer-option {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 0.85rem 1rem;
        margin-bottom: 0.75rem;
        background: #fff;
    }
</style>
@endpush
@endsection
