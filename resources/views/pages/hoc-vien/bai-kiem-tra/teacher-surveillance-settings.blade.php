@extends('layouts.app', ['title' => 'Cấu hình giám sát bài kiểm tra'])

@section('content')
@php
    $approvalClass = match ($baiKiemTra->trang_thai_duyet) {
        'da_duyet' => 'success',
        'cho_duyet' => 'warning',
        'tu_choi' => 'danger',
        default => 'secondary',
    };

    $publishClass = match ($baiKiemTra->trang_thai_phat_hanh) {
        'phat_hanh' => 'success',
        'dong' => 'dark',
        default => 'secondary',
    };

    $surveillanceEnabled = old('co_giam_sat', $baiKiemTra->co_giam_sat);
    $viewErrors = $errors ?? new \Illuminate\Support\ViewErrorBag();
@endphp

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Cấu hình giám sát</h2>
            <p class="text-muted mb-0">{{ $baiKiemTra->tieu_de }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('giang-vien.bai-kiem-tra.edit', $baiKiemTra->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-sliders-h me-1"></i> Về cấu hình đề
            </a>
            <a href="{{ route('giang-vien.bai-kiem-tra.index') }}" class="btn btn-outline-secondary">
                Quay lại danh sách
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($viewErrors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-2">Có dữ liệu chưa hợp lệ:</div>
            <ul class="mb-0 small">
                @foreach($viewErrors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Tổng quan đề thi</h5>

                    <div class="mb-3">
                        <div class="small text-muted mb-1">Khóa học</div>
                        <div class="fw-semibold">{{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Không rõ khóa học' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted mb-1">Module / buổi học</div>
                        <div class="fw-semibold">
                            @if($baiKiemTra->moduleHoc)
                                {{ $baiKiemTra->moduleHoc->ten_module }}
                            @elseif($baiKiemTra->lichHoc)
                                Buổi {{ $baiKiemTra->lichHoc->buoi_so }} - {{ optional($baiKiemTra->lichHoc->ngay_hoc)->format('d/m/Y') }}
                            @else
                                Đề toàn khóa
                            @endif
                        </div>
                    </div>

                    <div class="mb-3 d-flex flex-wrap gap-2">
                        <span class="badge text-bg-{{ $approvalClass }}">{{ $baiKiemTra->trang_thai_duyet_label }}</span>
                        <span class="badge text-bg-{{ $publishClass }}">{{ $baiKiemTra->trang_thai_phat_hanh_label }}</span>
                        <span class="badge {{ $baiKiemTra->co_giam_sat ? 'text-bg-warning' : 'text-bg-light' }}">
                            {{ $baiKiemTra->che_do_giam_sat_label }}
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1">Câu hỏi</div>
                                <div class="fw-bold fs-4">{{ $baiKiemTra->chi_tiet_cau_hois_count }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1">Bài làm</div>
                                <div class="fw-bold fs-4">{{ $baiKiemTra->bai_lams_count }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1">Đang làm</div>
                                <div class="fw-bold fs-4 {{ $baiKiemTra->bai_lams_dang_lam_count > 0 ? 'text-danger' : '' }}">{{ $baiKiemTra->bai_lams_dang_lam_count }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1">Thời gian thi</div>
                                <div class="fw-bold fs-4">{{ $baiKiemTra->thoi_gian_lam_bai }}</div>
                                <div class="small text-muted">phút</div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4 mb-0 small">
                        Dùng màn này để chỉnh riêng quy tắc giám sát mà không phải đi qua toàn bộ màn builder câu hỏi.
                    </div>

                    @if($baiKiemTra->bai_lams_dang_lam_count > 0)
                        <div class="alert alert-warning mt-3 mb-0 small">
                            Đề này đang có học viên làm bài. Hệ thống sẽ chặn cập nhật cấu hình giám sát cho tới khi các phiên thi hiện tại kết thúc.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <h4 class="fw-bold mb-1">Quy tắc giám sát nâng cao</h4>
                            <p class="text-muted mb-0">Cấu hình các điều kiện học viên phải tuân thủ trước và trong khi làm bài.</p>
                        </div>
                        <div class="form-check form-switch fs-5 mb-0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                role="switch"
                                id="coGiamSat"
                                name="co_giam_sat_fake"
                                @checked($surveillanceEnabled)
                                form="teacherSurveillanceForm"
                                value="1"
                            >
                            <label class="form-check-label fw-semibold" for="coGiamSat">Bật giám sát</label>
                        </div>
                    </div>

                    <form action="{{ route('giang-vien.bai-kiem-tra.surveillance.update', $baiKiemTra->id) }}" method="POST" id="teacherSurveillanceForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="co_giam_sat" id="coGiamSatHidden" value="{{ $surveillanceEnabled ? 1 : 0 }}">

                        <div id="surveillanceConfigFields">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Ngưỡng vi phạm tối đa</label>
                                    <input
                                        type="number"
                                        name="so_lan_vi_pham_toi_da"
                                        class="form-control"
                                        min="1"
                                        max="20"
                                        value="{{ old('so_lan_vi_pham_toi_da', $baiKiemTra->so_lan_vi_pham_toi_da ?? 3) }}"
                                    >
                                    <div class="form-text">Khi vượt ngưỡng này, bài làm sẽ bị đánh dấu cần hậu kiểm.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Chu kỳ snapshot camera (giây)</label>
                                    <input
                                        type="number"
                                        name="chu_ky_snapshot_giay"
                                        class="form-control"
                                        min="10"
                                        max="300"
                                        value="{{ old('chu_ky_snapshot_giay', $baiKiemTra->chu_ky_snapshot_giay ?? 30) }}"
                                    >
                                    <div class="form-text">Áp dụng khi bài thi yêu cầu camera.</div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="setting-card h-100 w-100">
                                        <input type="checkbox" class="form-check-input me-2" name="bat_buoc_fullscreen" value="1" @checked(old('bat_buoc_fullscreen', $baiKiemTra->bat_buoc_fullscreen))>
                                        <span>
                                            <span class="d-block fw-semibold">Bắt buộc fullscreen</span>
                                            <span class="small text-muted">Học viên rời toàn màn hình sẽ bị ghi nhận vi phạm.</span>
                                        </span>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="setting-card h-100 w-100">
                                        <input type="checkbox" class="form-check-input me-2" name="bat_buoc_camera" value="1" @checked(old('bat_buoc_camera', $baiKiemTra->bat_buoc_camera))>
                                        <span>
                                            <span class="d-block fw-semibold">Bắt buộc camera</span>
                                            <span class="small text-muted">Bật camera khi pre-check và duy trì trong lúc thi.</span>
                                        </span>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="setting-card h-100 w-100">
                                        <input type="checkbox" class="form-check-input me-2" name="tu_dong_nop_khi_vi_pham" value="1" @checked(old('tu_dong_nop_khi_vi_pham', $baiKiemTra->tu_dong_nop_khi_vi_pham))>
                                        <span>
                                            <span class="d-block fw-semibold">Tự động nộp khi vượt ngưỡng</span>
                                            <span class="small text-muted">Cho phép client tự động chốt bài khi số vi phạm vượt ngưỡng.</span>
                                        </span>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="setting-card h-100 w-100">
                                        <input type="checkbox" class="form-check-input me-2" name="chan_copy_paste" value="1" @checked(old('chan_copy_paste', $baiKiemTra->chan_copy_paste))>
                                        <span>
                                            <span class="d-block fw-semibold">Chặn copy / paste</span>
                                            <span class="small text-muted">Ngăn thao tác sao chép và dán trong màn hình làm bài.</span>
                                        </span>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="setting-card h-100 w-100">
                                        <input type="checkbox" class="form-check-input me-2" name="chan_chuot_phai" value="1" @checked(old('chan_chuot_phai', $baiKiemTra->chan_chuot_phai))>
                                        <span>
                                            <span class="d-block fw-semibold">Chặn chuột phải</span>
                                            <span class="small text-muted">Hạn chế menu ngữ cảnh và một số hành vi rời luồng thi.</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="surveillanceDisabledNote" class="alert alert-light border mt-4 {{ $surveillanceEnabled ? 'd-none' : '' }}">
                            Khi tắt giám sát, bài thi sẽ quay về chế độ thông thường và bỏ các yêu cầu pre-check, camera, fullscreen.
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4 pt-3 border-top">
                            <div class="small text-muted">
                                Nếu đề đã phát hành và đã có bài làm, bạn nên thông báo rõ cho học viên trước khi thay đổi luật giám sát cho các lần thi tiếp theo.
                            </div>
                            <button type="submit" class="btn btn-primary" @disabled($baiKiemTra->bai_lams_dang_lam_count > 0)>
                                <i class="fas fa-save me-1"></i> Lưu cấu hình giám sát
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .setting-card {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        border: 1px solid #dbe4f0;
        border-radius: 1rem;
        padding: 1rem;
        background: #fff;
        cursor: pointer;
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    .setting-card:hover {
        border-color: #86b7fe;
        box-shadow: 0 0.5rem 1.25rem rgba(13, 110, 253, 0.08);
        transform: translateY(-1px);
    }

    .setting-card .form-check-input {
        margin-top: 0.15rem;
        flex-shrink: 0;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('coGiamSat');
    const hiddenInput = document.getElementById('coGiamSatHidden');
    const configFields = document.getElementById('surveillanceConfigFields');
    const disabledNote = document.getElementById('surveillanceDisabledNote');

    if (!toggle || !hiddenInput || !configFields || !disabledNote) {
        return;
    }

    const syncState = () => {
        hiddenInput.value = toggle.checked ? '1' : '0';
        configFields.classList.toggle('opacity-50', !toggle.checked);
        disabledNote.classList.toggle('d-none', toggle.checked);

        configFields.querySelectorAll('input[type="number"], input[type="checkbox"]').forEach((input) => {
            if (input === toggle) {
                return;
            }

            input.disabled = !toggle.checked;
        });
    };

    toggle.addEventListener('change', syncState);
    syncState();
});
</script>
@endpush
