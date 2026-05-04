@extends('layouts.app', ['title' => 'Chi tiết đề thi'])

@section('content')
@php
    $duyetClass = match($baiKiemTra->trang_thai_duyet) {
        'da_duyet'  => 'is-success',
        'cho_duyet' => 'is-warning',
        'tu_choi'   => 'is-danger',
        default     => 'is-secondary',
    };
    $duyetIcon = match($baiKiemTra->trang_thai_duyet) {
        'da_duyet'  => 'fa-check-circle',
        'cho_duyet' => 'fa-hourglass-half',
        'tu_choi'   => 'fa-times-circle',
        default     => 'fa-pen',
    };
    $approvalQuestionCount = $baiKiemTra->is_free_essay && filled($baiKiemTra->mo_ta)
        ? 1
        : $baiKiemTra->chiTietCauHois->count();
    $totalAttempts  = $baiKiemTra->baiLams->count();
    $violationSum   = (int) $baiKiemTra->baiLams->sum('tong_so_vi_pham');
@endphp

<div class="container-fluid admin-page-x rev-page">
    {{-- ========== Welcome banner xanh dương ========== --}}
    <div class="apx-welcome rev-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-clipboard-check"></i></div>
        <div class="apx-welcome-text">
            <div class="rev-tag-row">
                <span class="rev-loai-badge">
                    <i class="fas fa-shield-halved"></i> PHÊ DUYỆT ĐỀ THI
                </span>
                <span class="rev-status-pill {{ $duyetClass }}">
                    <i class="fas {{ $duyetIcon }}"></i> {{ $baiKiemTra->trang_thai_duyet_label }}
                </span>
                @if($baiKiemTra->co_giam_sat)
                    <span class="rev-watch-badge">
                        <i class="fas fa-shield-halved"></i> Giám sát nâng cao
                    </span>
                @endif
                <span class="rev-status-badge">
                    <i class="fas fa-graduation-cap"></i> {{ $baiKiemTra->khoaHoc->ma_khoa_hoc ?? 'KH' }}
                </span>
            </div>
            <h4>{{ $baiKiemTra->tieu_de }}</h4>
            <p>
                <span><i class="fas fa-book"></i> {{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? '—' }}</span>
                <span class="rev-sep">·</span>
                <span><i class="fas fa-cube"></i> {{ $baiKiemTra->moduleHoc->ten_module ?? 'Dùng chung' }}</span>
                <span class="rev-sep">·</span>
                <span><i class="fas fa-pen-fancy"></i> {{ $baiKiemTra->content_mode_label }}</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.kiem-tra-online.phe-duyet.index') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Danh sách</span>
            </a>
        </div>
    </div>

    @include('components.alert')

    {{-- Quick stats --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6">
            <div class="apx-stat tone-primary">
                <div class="aps-icon"><i class="fas fa-list-ol"></i></div>
                <div class="aps-text">
                    <strong>{{ $approvalQuestionCount }}</strong>
                    <small>Số câu hỏi trong đề</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="apx-stat tone-success">
                <div class="aps-icon"><i class="fas fa-star"></i></div>
                <div class="aps-text">
                    <strong>{{ number_format((float) $baiKiemTra->tong_diem, 2) }}</strong>
                    <small>Tổng điểm</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="apx-stat tone-info">
                <div class="aps-icon"><i class="fas fa-users"></i></div>
                <div class="aps-text">
                    <strong>{{ $totalAttempts }}</strong>
                    <small>Lượt làm bài</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="apx-stat tone-danger">
                <div class="aps-icon"><i class="fas fa-flag"></i></div>
                <div class="aps-text">
                    <strong>{{ $violationSum }}</strong>
                    <small>Tổng vi phạm</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- ===== Sidebar: Thông tin & duyệt ===== --}}
        <div class="col-lg-4">
            {{-- ① Thông tin --}}
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">1</span>
                        <div>
                            <h2><i class="fas fa-info-circle"></i> Thông tin đề</h2>
                            <p>Chi tiết khóa học, module và quy tắc giám sát.</p>
                        </div>
                    </div>
                </header>

                <div class="rev-info-card">
                    <div class="rev-info-row">
                        <span class="rev-info-label">Khóa học</span>
                        <span class="rev-info-value">{{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'N/A' }}</span>
                    </div>
                    <div class="rev-info-row">
                        <span class="rev-info-label">Module</span>
                        <span class="rev-info-value">{{ $baiKiemTra->moduleHoc->ten_module ?? 'Dùng chung' }}</span>
                    </div>
                    <div class="rev-info-row">
                        <span class="rev-info-label">Loại nội dung</span>
                        <span class="rev-info-value">{{ $baiKiemTra->content_mode_label }}</span>
                    </div>
                    <div class="rev-info-row">
                        <span class="rev-info-label">Chế độ thi</span>
                        @if($baiKiemTra->co_giam_sat)
                            <span class="rev-pill is-warning"><i class="fas fa-shield-halved"></i> Giám sát nâng cao</span>
                        @else
                            <span class="rev-pill is-secondary"><i class="fas fa-circle"></i> Bài thường</span>
                        @endif
                    </div>
                    <div class="rev-info-row">
                        <span class="rev-info-label">Người tạo</span>
                        <span class="rev-info-value">{{ $baiKiemTra->nguoiTao->ho_ten ?? '—' }}</span>
                    </div>
                    @if($baiKiemTra->nguoiDuyet)
                        <div class="rev-info-row">
                            <span class="rev-info-label">Người duyệt</span>
                            <span class="rev-info-value">{{ $baiKiemTra->nguoiDuyet->ho_ten }}</span>
                        </div>
                    @endif

                    @if($baiKiemTra->co_giam_sat)
                        <div class="rev-watch-block">
                            <div class="rev-watch-title">
                                <i class="fas fa-shield-halved"></i> Quy tắc giám sát
                            </div>
                            <div class="rev-watch-line {{ $baiKiemTra->bat_buoc_fullscreen ? 'is-on' : 'is-off' }}">
                                <i class="fas {{ $baiKiemTra->bat_buoc_fullscreen ? 'fa-check' : 'fa-xmark' }}"></i>
                                Yêu cầu fullscreen
                            </div>
                            <div class="rev-watch-line {{ $baiKiemTra->bat_buoc_camera ? 'is-on' : 'is-off' }}">
                                <i class="fas {{ $baiKiemTra->bat_buoc_camera ? 'fa-check' : 'fa-xmark' }}"></i>
                                Yêu cầu camera
                            </div>
                            <div class="rev-watch-line is-info">
                                <i class="fas fa-bell"></i>
                                Ngưỡng vi phạm: <strong>{{ $baiKiemTra->so_lan_vi_pham_toi_da }}</strong> lần
                            </div>
                            <div class="rev-watch-line is-info">
                                <i class="fas fa-camera"></i>
                                Snapshot mỗi <strong>{{ $baiKiemTra->chu_ky_snapshot_giay }}</strong> giây
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            {{-- ② Hành động phê duyệt --}}
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">2</span>
                        <div>
                            <h2><i class="fas fa-gavel"></i> Hành động phê duyệt</h2>
                            <p>Duyệt, từ chối, phát hành hoặc đóng đề thi.</p>
                        </div>
                    </div>
                </header>

                <div class="rev-action-card">
                    <form action="{{ route('admin.kiem-tra-online.phe-duyet.approve', $baiKiemTra->id) }}" method="POST" class="rev-action-block">
                        @csrf
                        <label class="rev-action-label"><i class="fas fa-check-circle"></i> Ghi chú duyệt</label>
                        <textarea name="ghi_chu_duyet" rows="3" class="form-control rev-textarea"
                                  placeholder="Ghi chú nội bộ (không bắt buộc)">{{ old('ghi_chu_duyet', $baiKiemTra->ghi_chu_duyet) }}</textarea>
                        <button type="submit" class="rev-btn-primary success">
                            <i class="fas fa-check"></i> Duyệt đề thi
                        </button>
                    </form>

                    <form action="{{ route('admin.kiem-tra-online.phe-duyet.reject', $baiKiemTra->id) }}" method="POST" class="rev-action-block">
                        @csrf
                        <label class="rev-action-label"><i class="fas fa-times-circle"></i> Lý do từ chối</label>
                        <textarea name="ghi_chu_duyet" rows="3" class="form-control rev-textarea"
                                  placeholder="Bắt buộc — giảng viên cần biết để chỉnh sửa" required>{{ old('ghi_chu_duyet', $baiKiemTra->ghi_chu_duyet) }}</textarea>
                        <button type="submit" class="rev-btn-primary danger">
                            <i class="fas fa-ban"></i> Từ chối đề thi
                        </button>
                    </form>

                    @if($baiKiemTra->trang_thai_duyet === 'da_duyet' && $baiKiemTra->trang_thai_phat_hanh !== 'phat_hanh')
                        <form action="{{ route('admin.kiem-tra-online.phe-duyet.publish', $baiKiemTra->id) }}" method="POST" class="rev-action-block">
                            @csrf
                            <button type="submit" class="rev-btn-primary primary">
                                <i class="fas fa-broadcast-tower"></i> Phát hành cho học viên
                            </button>
                        </form>
                    @endif
                    @if($baiKiemTra->trang_thai_phat_hanh === 'phat_hanh')
                        <form action="{{ route('admin.kiem-tra-online.phe-duyet.close', $baiKiemTra->id) }}" method="POST" class="rev-action-block">
                            @csrf
                            <button type="submit" class="rev-btn-primary dark"
                                    onclick="return confirm('Đóng đề thi sẽ ngăn học viên làm tiếp. Tiếp tục?')">
                                <i class="fas fa-lock"></i> Đóng đề thi
                            </button>
                        </form>
                    @endif
                </div>
            </section>
        </div>

        {{-- ===== Main: Câu hỏi & bài làm ===== --}}
        <div class="col-lg-8">
            {{-- ③ Danh sách câu hỏi --}}
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">3</span>
                        <div>
                            <h2><i class="fas fa-list-check"></i> Danh sách câu hỏi</h2>
                            <p>Xem từng câu hỏi để đánh giá chất lượng đề thi.</p>
                        </div>
                    </div>
                    <div class="apx-section-meta">
                        <span class="apx-meta-pill"><strong>{{ $approvalQuestionCount }}</strong> câu</span>
                    </div>
                </header>

                <div class="rev-questions-card">
                    @if($baiKiemTra->is_free_essay && filled($baiKiemTra->mo_ta))
                        <div class="rev-question-item is-essay">
                            <div class="rev-question-head">
                                <div>
                                    <span class="rev-question-num">Câu 1</span>
                                    <span class="rev-question-tag is-info">
                                        <i class="fas fa-pen-fancy"></i> Tự luận tự do
                                    </span>
                                </div>
                                <span class="rev-question-score">
                                    <i class="fas fa-star"></i> {{ number_format((float) $baiKiemTra->tong_diem, 2) }} điểm
                                </span>
                            </div>
                            <div class="rev-question-body">
                                <strong>Đề bài tự luận:</strong>
                                <div class="rev-question-note">Học viên nộp một bài viết tổng. Giảng viên chấm tay sau khi học viên nộp.</div>
                                <div class="rev-question-content">{!! nl2br(e($baiKiemTra->mo_ta)) !!}</div>
                            </div>
                        </div>
                    @else
                        @forelse($baiKiemTra->chiTietCauHois as $index => $chiTiet)
                            <div class="rev-question-item">
                                <div class="rev-question-head">
                                    <div>
                                        <span class="rev-question-num">Câu {{ $index + 1 }}</span>
                                        @if(($chiTiet->cauHoi->loai_cau_hoi ?? null) === 'tu_luan')
                                            <span class="rev-question-tag is-info"><i class="fas fa-pen-fancy"></i> Tự luận</span>
                                        @else
                                            <span class="rev-question-tag is-primary"><i class="fas fa-list-ul"></i> Trắc nghiệm</span>
                                        @endif
                                    </div>
                                    <span class="rev-question-score">
                                        <i class="fas fa-star"></i> {{ number_format((float) $chiTiet->diem_so, 2) }} điểm
                                    </span>
                                </div>
                                <div class="rev-question-body">{!! nl2br(e($chiTiet->cauHoi->noi_dung ?? 'Không rõ nội dung')) !!}</div>

                                @if(!empty($chiTiet->cauHoi->dapAns) && count($chiTiet->cauHoi->dapAns) > 0)
                                    <div class="rev-answers">
                                        @foreach($chiTiet->cauHoi->dapAns as $dapAn)
                                            <div class="rev-answer-line {{ $dapAn->dap_an_dung ? 'is-correct' : '' }}">
                                                <i class="fas {{ $dapAn->dap_an_dung ? 'fa-check-circle' : 'fa-circle' }}"></i>
                                                {{ $dapAn->noi_dung }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="rev-empty">
                                <div class="rev-empty-icon"><i class="fas fa-folder-open"></i></div>
                                <p>Đề thi hiện chưa có câu hỏi.</p>
                            </div>
                        @endforelse
                    @endif
                </div>
            </section>

            {{-- ④ Bài làm & hậu kiểm --}}
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">4</span>
                        <div>
                            <h2><i class="fas fa-users-viewfinder"></i> Bài làm &amp; hậu kiểm</h2>
                            <p>Theo dõi lượt làm và mức vi phạm giám sát của học viên.</p>
                        </div>
                    </div>
                    <div class="apx-section-meta">
                        <span class="apx-meta-pill"><strong>{{ $totalAttempts }}</strong> lượt</span>
                    </div>
                </header>

                <div class="rev-table-wrap">
                    @if($baiKiemTra->baiLams->isEmpty())
                        <div class="rev-empty">
                            <div class="rev-empty-icon"><i class="fas fa-user-clock"></i></div>
                            <p>Chưa có học viên nào làm đề này.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 rev-table">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Học viên</th>
                                        <th class="text-center">Lần làm</th>
                                        <th class="text-center">Giám sát</th>
                                        <th class="text-center">Vi phạm</th>
                                        <th class="pe-4 text-end">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($baiKiemTra->baiLams as $baiLam)
                                        @php
                                            $hoTen = $baiLam->hocVien->ho_ten ?? 'Học viên';
                                            $idColor = ($baiLam->hocVien->ma_nguoi_dung ?? $baiLam->id) % 6;
                                            $gradients = [
                                                'linear-gradient(135deg, #4361ee 0%, #2f46c9 100%)',
                                                'linear-gradient(135deg, #16a34a 0%, #15803d 100%)',
                                                'linear-gradient(135deg, #d97706 0%, #b45309 100%)',
                                                'linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%)',
                                                'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
                                                'linear-gradient(135deg, #db2777 0%, #be185d 100%)',
                                            ];
                                            $initial = mb_strtoupper(mb_substr(trim($hoTen), 0, 1, 'UTF-8'), 'UTF-8');
                                        @endphp
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="rev-avatar" style="background: {{ $gradients[$idColor] }};">
                                                        {{ $initial ?: 'H' }}
                                                    </div>
                                                    <div>
                                                        <div class="rev-name">{{ $hoTen }}</div>
                                                        <div class="rev-email">
                                                            <i class="fas fa-envelope"></i> {{ $baiLam->hocVien->email ?? '—' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="rev-attempt-pill">
                                                    <i class="fas fa-redo"></i> Lần {{ $baiLam->lan_lam_thu }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $baiLam->trang_thai_giam_sat_color }} px-3 py-2">
                                                    {{ $baiLam->trang_thai_giam_sat_label }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @php $viPham = (int) $baiLam->tong_so_vi_pham; @endphp
                                                <span class="rev-violation {{ $viPham > 0 ? 'is-danger' : 'is-success' }}">
                                                    <i class="fas {{ $viPham > 0 ? 'fa-flag' : 'fa-check' }}"></i>
                                                    {{ $viPham }}
                                                </span>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <a href="{{ route('admin.kiem-tra-online.phe-duyet.attempt.show', $baiLam->id) }}"
                                                   class="rev-detail-btn">
                                                    <i class="fas fa-magnifying-glass"></i> Xem hậu kiểm
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    /* ===== Override màu đề mục: đỏ ===== */
    .rev-page .apx-section-head {
        background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
        border-color: #fecaca;
        border-left-color: #dc2626;
    }
    .rev-page .apx-section-num {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }
    .rev-page .apx-section-title h2 i { color: #dc2626; }
    .rev-page .apx-meta-pill {
        border-color: #fecaca;
        color: #dc2626;
    }
    .rev-page .apx-meta-pill strong { color: #b91c1c; }

    /* ===== Welcome banner xanh dương ===== */
    .rev-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
    }
    .rev-tag-row {
        display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .rev-loai-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px;
        background: rgba(255,255,255,0.22);
        border: 1px solid rgba(255,255,255,0.4);
        backdrop-filter: blur(6px);
        color: #fff;
        font-size: 0.7rem; font-weight: 800;
        letter-spacing: 1px; border-radius: 999px;
    }
    .rev-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: rgba(255,255,255,0.14);
        color: #fff; font-size: 0.72rem; font-weight: 700;
        border-radius: 999px;
    }
    .rev-status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        font-size: 0.72rem; font-weight: 800;
        border-radius: 999px;
    }
    .rev-status-pill.is-success   { background: #dcfce7; color: #166534; }
    .rev-status-pill.is-warning   { background: #fef3c7; color: #b45309; animation: revPulse 1.6s ease-out infinite; }
    .rev-status-pill.is-danger    { background: #fee2e2; color: #b91c1c; }
    .rev-status-pill.is-secondary { background: #f1f5f9; color: #475569; }
    @keyframes revPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.6); }
        50%      { box-shadow: 0 0 0 6px rgba(251, 191, 36, 0); }
    }
    .rev-watch-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f; font-size: 0.72rem; font-weight: 800;
        border-radius: 999px;
    }
    .apx-welcome.rev-welcome p {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        font-size: 0.85rem;
    }
    .apx-welcome.rev-welcome p i { color: #fef3c7; margin-right: 4px; }
    .rev-sep { opacity: 0.5; }

    /* ===== Info card ===== */
    .rev-info-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
    }
    .rev-info-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 0;
        border-bottom: 1px dashed #f1f5f9;
        gap: 14px;
        flex-wrap: wrap;
    }
    .rev-info-row:last-child { border-bottom: 0; }
    .rev-info-label {
        font-size: 0.74rem; font-weight: 800;
        color: #7f1d1d;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .rev-info-value {
        font-size: 0.86rem; font-weight: 700;
        color: #0f172a; text-align: right;
    }
    .rev-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px;
        font-size: 0.7rem; font-weight: 800;
        border-radius: 999px;
    }
    .rev-pill.is-warning   { background: #fef3c7; color: #b45309; }
    .rev-pill.is-secondary { background: #f1f5f9; color: #475569; }

    .rev-watch-block {
        margin-top: 14px;
        padding: 14px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 12px;
    }
    .rev-watch-title {
        font-size: 0.78rem; font-weight: 800;
        color: #b45309;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 10px;
    }
    .rev-watch-title i { margin-right: 5px; }
    .rev-watch-line {
        font-size: 0.82rem; font-weight: 600;
        padding: 5px 0;
        display: flex; align-items: center; gap: 8px;
    }
    .rev-watch-line i { width: 16px; text-align: center; font-size: 0.74rem; }
    .rev-watch-line.is-on    { color: #166534; }
    .rev-watch-line.is-off   { color: #94a3b8; }
    .rev-watch-line.is-info  { color: #1e293b; }
    .rev-watch-line strong   { color: #b45309; }

    /* ===== Action card ===== */
    .rev-action-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .rev-action-block {
        margin: 0;
    }
    .rev-action-label {
        display: block;
        font-size: 0.74rem; font-weight: 800;
        color: #7f1d1d;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 6px;
    }
    .rev-action-label i { margin-right: 5px; }
    .rev-textarea {
        margin-bottom: 8px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.85rem;
    }
    .rev-textarea:focus {
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.08);
    }
    .rev-btn-primary {
        display: flex; align-items: center; justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 10px 14px;
        font-size: 0.86rem;
        font-weight: 800;
        border-radius: 10px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .rev-btn-primary.success {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: #fff;
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
    }
    .rev-btn-primary.success:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(22, 163, 74, 0.35); }
    .rev-btn-primary.danger {
        background: #fff;
        color: #dc2626;
        border-color: #fecaca;
    }
    .rev-btn-primary.danger:hover { background: #dc2626; color: #fff; border-color: #dc2626; transform: translateY(-1px); }
    .rev-btn-primary.primary {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
        color: #fff;
        box-shadow: 0 4px 12px rgba(29, 78, 216, 0.25);
    }
    .rev-btn-primary.primary:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(29, 78, 216, 0.35); }
    .rev-btn-primary.dark {
        background: #1e293b;
        color: #fff;
    }
    .rev-btn-primary.dark:hover { background: #0f172a; transform: translateY(-1px); }

    /* ===== Questions card ===== */
    .rev-questions-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 18px;
    }
    .rev-question-item {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e2e8f0;
        border-left: 3px solid #dc2626;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 12px;
    }
    .rev-question-item:last-child { margin-bottom: 0; }
    .rev-question-item.is-essay {
        background: linear-gradient(180deg, #fff1f2 0%, #fee2e2 100%);
        border-left-color: #dc2626;
        border-color: #fecaca;
    }
    .rev-question-head {
        display: flex; justify-content: space-between; align-items: center;
        gap: 10px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .rev-question-num {
        display: inline-block;
        padding: 3px 10px;
        background: #dc2626;
        color: #fff;
        font-size: 0.72rem;
        font-weight: 800;
        border-radius: 999px;
        margin-right: 6px;
    }
    .rev-question-tag {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        font-size: 0.7rem; font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .rev-question-tag.is-primary { background: #eff6ff; color: #1d4ed8; }
    .rev-question-tag.is-info    { background: #cffafe; color: #0e7490; }
    .rev-question-score {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        background: #fef3c7;
        color: #b45309;
        font-size: 0.74rem; font-weight: 800;
        border-radius: 999px;
    }
    .rev-question-body {
        font-size: 0.92rem;
        color: #0f172a;
        line-height: 1.55;
        margin-top: 6px;
        white-space: pre-wrap;
    }
    .rev-question-note {
        font-size: 0.78rem;
        color: #64748b;
        margin: 4px 0 8px;
        font-style: italic;
    }
    .rev-question-content {
        margin-top: 8px;
        padding: 10px 14px;
        background: #fff;
        border: 1px solid #fecaca;
        border-radius: 8px;
        font-size: 0.86rem;
        line-height: 1.65;
        color: #0f172a;
    }
    .rev-answers {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
    }
    .rev-answer-line {
        display: flex; align-items: flex-start; gap: 8px;
        padding: 5px 0;
        font-size: 0.85rem;
        color: #475569;
        line-height: 1.45;
    }
    .rev-answer-line i {
        color: #cbd5e1;
        font-size: 0.78rem;
        margin-top: 4px;
    }
    .rev-answer-line.is-correct {
        color: #166534;
        font-weight: 700;
    }
    .rev-answer-line.is-correct i { color: #16a34a; }

    /* ===== Table ===== */
    .rev-table-wrap {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }
    .rev-table thead {
        background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #7f1d1d;
        letter-spacing: 0.5px;
    }
    .rev-table thead th {
        padding: 12px 10px;
        font-weight: 800;
        border-bottom: 1px solid #fecaca;
    }
    .rev-table tbody td {
        padding: 14px 10px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .rev-table tbody tr:last-child td { border-bottom: 0; }
    .rev-table tbody tr:hover { background: #fafafa; }

    .rev-avatar {
        flex-shrink: 0;
        width: 42px; height: 42px;
        border-radius: 50%;
        color: #fff;
        font-weight: 800;
        font-size: 0.95rem;
        display: grid; place-items: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .rev-name {
        font-size: 0.9rem; font-weight: 800;
        color: #0f172a;
    }
    .rev-email {
        font-size: 0.74rem; color: #64748b;
        font-weight: 600; margin-top: 3px;
    }
    .rev-email i { color: #1d4ed8; margin-right: 4px; font-size: 0.66rem; }

    .rev-attempt-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 0.74rem; font-weight: 800;
        border-radius: 999px;
    }
    .rev-attempt-pill i { font-size: 0.66rem; }

    .rev-violation {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px;
        font-size: 0.78rem; font-weight: 800;
        border-radius: 999px;
    }
    .rev-violation.is-success { background: #dcfce7; color: #166534; }
    .rev-violation.is-danger  { background: #fee2e2; color: #b91c1c; }

    .rev-detail-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px;
        background: #fff;
        color: #dc2626;
        border: 1px solid #fecaca;
        font-size: 0.78rem; font-weight: 800;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .rev-detail-btn:hover {
        background: #dc2626; color: #fff; border-color: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(220, 38, 38, 0.25);
    }

    /* Empty */
    .rev-empty {
        padding: 50px 30px;
        text-align: center;
    }
    .rev-empty-icon {
        width: 78px; height: 78px;
        margin: 0 auto 14px;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-radius: 50%;
        display: grid; place-items: center;
        color: #dc2626;
        font-size: 1.7rem;
    }
    .rev-empty p {
        font-size: 0.88rem;
        color: #94a3b8;
        margin: 0;
    }

    @media (max-width: 991.98px) {
        .rev-table { font-size: 0.85rem; }
        .rev-table thead th, .rev-table tbody td { padding: 10px 8px; }
        .rev-avatar { width: 36px; height: 36px; font-size: 0.82rem; }
    }
</style>
@endsection
