@extends('layouts.app', ['title' => 'Chi tiết câu hỏi'])

@section('content')
<div class="container-fluid admin-page-x nhch-page">
    <div class="apx-welcome nhch-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-{{ $cauHoi->is_essay ? 'pen-fancy' : 'list-check' }}"></i></div>
        <div class="apx-welcome-text">
            <div class="nhch-tag-row">
                <span class="nhch-loai-badge"><i class="fas fa-hashtag"></i> {{ $cauHoi->ma_cau_hoi }}</span>
                <span class="nhch-status-badge"><i class="fas fa-tag"></i> {{ $cauHoi->loai_cau_hoi_label }}</span>
                <span class="nhch-status-badge"><i class="fas fa-gauge"></i> {{ $cauHoi->muc_do_label }}</span>
                @if($cauHoi->is_cong_bo)
                    <span class="nhch-status-badge" style="background: #d1fae5; color: #065f46;"><i class="fas fa-globe-asia"></i> Đã công bố</span>
                @else
                    <span class="nhch-status-badge"><i class="fas fa-lock"></i> Riêng tư</span>
                @endif
            </div>
            <h4>Chi tiết câu hỏi</h4>
            <p>
                <span><i class="fas fa-graduation-cap"></i> {{ $cauHoi->khoaHoc?->ten_khoa_hoc ?? 'Chưa gắn khoá' }}</span>
                @if($cauHoi->moduleHoc)
                    <span class="lr-sep">·</span>
                    <span><i class="fas fa-cube"></i> {{ $cauHoi->moduleHoc->ten_module }}</span>
                @endif
                <span class="lr-sep">·</span>
                <span><i class="far fa-user"></i> {{ $cauHoi->nguoiTao->ho_ten ?? 'N/A' }}</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('giang-vien.ngan-hang-cau-hoi.index') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Ngân hàng</span>
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="ch-detail-card">
                <div class="ch-detail-card__head">
                    <i class="fas fa-question-circle"></i> Nội dung câu hỏi
                </div>
                <div class="ch-detail-card__body">
                    <div class="ch-content">{!! nl2br(e($cauHoi->noi_dung)) !!}</div>
                </div>
            </div>

            @if(!$cauHoi->is_essay && $cauHoi->dapAns->isNotEmpty())
                <div class="ch-detail-card mt-3">
                    <div class="ch-detail-card__head">
                        <i class="fas fa-list"></i> Các đáp án ({{ $cauHoi->dapAns->count() }})
                    </div>
                    <div class="ch-detail-card__body">
                        @foreach($cauHoi->dapAns as $dapAn)
                            <div class="ch-answer {{ $dapAn->is_dap_an_dung ? 'is-correct' : '' }}">
                                <span class="ch-answer__key">{{ $dapAn->ky_hieu }}</span>
                                <span class="ch-answer__content">{{ $dapAn->noi_dung }}</span>
                                @if($dapAn->is_dap_an_dung)
                                    <span class="ch-answer__badge"><i class="fas fa-check"></i> Đúng</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($cauHoi->is_essay && $cauHoi->dap_an_mau)
                <div class="ch-detail-card mt-3">
                    <div class="ch-detail-card__head">
                        <i class="fas fa-pen"></i> Đáp án mẫu (tự luận)
                    </div>
                    <div class="ch-detail-card__body">
                        <div class="ch-content">{!! nl2br(e($cauHoi->dap_an_mau)) !!}</div>
                    </div>
                </div>
            @endif

            @if($cauHoi->giai_thich_dap_an)
                <div class="ch-detail-card mt-3">
                    <div class="ch-detail-card__head">
                        <i class="fas fa-lightbulb"></i> Giải thích đáp án
                    </div>
                    <div class="ch-detail-card__body">
                        <div class="ch-content">{!! nl2br(e($cauHoi->giai_thich_dap_an)) !!}</div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="ch-detail-card">
                <div class="ch-detail-card__head">
                    <i class="fas fa-circle-info"></i> Thông tin
                </div>
                <div class="ch-detail-card__body">
                    <div class="ch-stat-row"><span>Mã câu hỏi</span><strong>{{ $cauHoi->ma_cau_hoi }}</strong></div>
                    <div class="ch-stat-row"><span>Loại</span><strong>{{ $cauHoi->loai_cau_hoi_label }}</strong></div>
                    <div class="ch-stat-row"><span>Mức độ</span><strong>{{ $cauHoi->muc_do_label }}</strong></div>
                    <div class="ch-stat-row"><span>Điểm mặc định</span><strong>{{ $cauHoi->diem_mac_dinh }}</strong></div>
                    <div class="ch-stat-row"><span>Phạm vi</span>
                        <strong class="{{ $cauHoi->is_cong_bo ? 'text-success' : 'text-secondary' }}">
                            {{ $cauHoi->pham_vi_label }}
                        </strong>
                    </div>
                    @if($cauHoi->is_cong_bo)
                        <div class="ch-stat-row"><span>Công bố lúc</span><strong>{{ $cauHoi->cong_bo_luc?->format('d/m/Y H:i') ?? '—' }}</strong></div>
                        <div class="ch-stat-row"><span>Công bố bởi</span><strong>{{ $cauHoi->nguoiCongBo->ho_ten ?? 'Admin' }}</strong></div>
                    @endif
                    <div class="ch-stat-row"><span>Tạo lúc</span><strong>{{ $cauHoi->created_at?->format('d/m/Y') }}</strong></div>
                </div>
            </div>

            @if((int) $cauHoi->nguoi_tao_id !== (int) auth()->user()->id)
                <div class="alert alert-info mt-3 small mb-0">
                    <i class="fas fa-circle-info me-1"></i>
                    Câu hỏi này do giảng viên khác tạo và đã được admin công bố. Bạn có thể dùng nó cho bài kiểm tra của mình nhưng không thể sửa hoặc xoá.
                </div>
            @else
                <div class="alert alert-warning mt-3 small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Đây là câu hỏi của bạn. Bạn có thể xoá nó khỏi ngân hàng nếu không cần thiết.
                </div>
            @endif
        </div>
    </div>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    .nhch-page { padding-bottom: 24px; }
    .nhch-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
    }
    .nhch-tag-row { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 8px; }
    .nhch-loai-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 12px; background: #fff; color: #1d4ed8;
        font-size: 0.72rem; font-weight: 800; border-radius: 999px;
        font-family: monospace;
    }
    .nhch-status-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 11px; background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.4); color: #fff;
        font-size: 0.74rem; font-weight: 700; border-radius: 999px;
    }

    .ch-detail-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }
    .ch-detail-card__head {
        background: linear-gradient(135deg, #fff 0%, #fef2f2 100%);
        border-bottom: 1px solid #fecaca;
        padding: 12px 16px;
        font-weight: 800;
        color: #b91c1c;
        font-size: 0.92rem;
    }
    .ch-detail-card__body { padding: 16px; }

    .ch-content {
        font-size: 0.95rem;
        color: #1e293b;
        line-height: 1.6;
    }

    .ch-answer {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        margin-bottom: 8px;
    }
    .ch-answer:last-child { margin-bottom: 0; }
    .ch-answer.is-correct {
        background: #ecfdf5;
        border-color: #10b981;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.12);
    }
    .ch-answer__key {
        flex-shrink: 0;
        width: 32px; height: 32px;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        display: grid;
        place-items: center;
        font-weight: 800;
        color: #475569;
    }
    .ch-answer.is-correct .ch-answer__key {
        background: #10b981;
        color: #fff;
        border-color: #10b981;
    }
    .ch-answer__content { flex: 1; line-height: 1.5; padding-top: 4px; }
    .ch-answer__badge {
        flex-shrink: 0;
        background: #10b981;
        color: #fff;
        padding: 4px 10px;
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase;
    }

    .ch-stat-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 6px 0;
        font-size: 0.86rem;
        border-bottom: 1px dashed #e2e8f0;
    }
    .ch-stat-row:last-child { border-bottom: none; }
    .ch-stat-row span { color: #64748b; }
    .ch-stat-row strong { color: #0f172a; font-weight: 700; text-align: right; }
</style>
@endsection
