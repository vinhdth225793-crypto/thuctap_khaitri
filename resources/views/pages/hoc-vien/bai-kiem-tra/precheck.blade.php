@extends('layouts.app', ['title' => 'Pre-check bài thi'])

@section('content')
<div class="container-fluid admin-page-x pc-page">
    {{-- ========== Welcome banner xanh dương ========== --}}
    <div class="apx-welcome pc-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-shield-halved"></i></div>
        <div class="apx-welcome-text">
            <div class="pc-tag-row">
                <span class="pc-loai-badge">
                    <i class="fas fa-circle-check"></i> KIỂM TRA TRƯỚC KHI THI
                </span>
                <span class="pc-status-badge">
                    <i class="fas fa-graduation-cap"></i>
                    {{ $baiKiemTra->khoaHoc->ma_khoa_hoc ?? 'KH' }}
                </span>
                <span class="pc-watch-badge">
                    <i class="fas fa-eye"></i> Bài thi giám sát
                </span>
                @if($precheckState)
                    <span class="pc-success-badge">
                        <i class="fas fa-check-double"></i> Đã pre-check
                    </span>
                @endif
            </div>
            <h4>{{ $baiKiemTra->tieu_de }}</h4>
            <p>
                <span><i class="fas fa-book"></i> {{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Bài thi giám sát' }}</span>
                <span class="pc-sep">·</span>
                <span><i class="fas fa-clock"></i> {{ $baiKiemTra->thoi_gian_lam_bai }} phút</span>
                <span class="pc-sep">·</span>
                <span><i class="fas fa-redo"></i> Lần thử {{ $baiKiemTra->baiLams->count() + 1 }}/{{ $baiKiemTra->so_lan_duoc_lam }}</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id) }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Quay lại bài thi</span>
            </a>
        </div>
    </div>

    @include('components.alert')

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="pc-breadcrumb mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('hoc-vien.dashboard') }}"><i class="fas fa-home me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('hoc-vien.bai-kiem-tra') }}">Bài kiểm tra</a></li>
            <li class="breadcrumb-item"><a href="{{ route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id) }}">{{ $baiKiemTra->tieu_de }}</a></li>
            <li class="breadcrumb-item active">Pre-check</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- ========== ① Thiết bị & quyền truy cập ========== --}}
        <div class="col-lg-7">
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">1</span>
                        <div>
                            <h2><i class="fas fa-microchip"></i> Thiết bị &amp; quyền truy cập</h2>
                            <p>Hệ thống sẽ kiểm tra trình duyệt, fullscreen và camera trước khi cho vào thi.</p>
                        </div>
                    </div>
                </header>

                <div class="pc-check-card">
                    <div class="pc-check-item" id="check-browser">
                        <div class="pc-check-icon"><i class="fas fa-globe"></i></div>
                        <div class="pc-check-info">
                            <div class="pc-check-title">Hỗ trợ trình duyệt</div>
                            <div class="pc-check-desc">Kiểm tra API fullscreen, visibility và camera.</div>
                        </div>
                        <span class="pc-check-status badge bg-secondary">Đang chờ</span>
                    </div>

                    <div class="pc-check-item" id="check-fullscreen">
                        <div class="pc-check-icon"><i class="fas fa-expand"></i></div>
                        <div class="pc-check-info">
                            <div class="pc-check-title">
                                Toàn màn hình
                                @if($baiKiemTra->bat_buoc_fullscreen)
                                    <span class="pc-required-tag"><i class="fas fa-asterisk"></i> Bắt buộc</span>
                                @endif
                            </div>
                            <div class="pc-check-desc">{{ $baiKiemTra->bat_buoc_fullscreen ? 'Bắt buộc cho bài thi này.' : 'Không bắt buộc nhưng vẫn kiểm tra khả năng hỗ trợ.' }}</div>
                        </div>
                        <span class="pc-check-status badge bg-secondary">Đang chờ</span>
                    </div>

                    <div class="pc-check-item" id="check-camera">
                        <div class="pc-check-icon"><i class="fas fa-video"></i></div>
                        <div class="pc-check-info">
                            <div class="pc-check-title">
                                Camera
                                @if($baiKiemTra->bat_buoc_camera)
                                    <span class="pc-required-tag"><i class="fas fa-asterisk"></i> Bắt buộc</span>
                                @endif
                            </div>
                            <div class="pc-check-desc">{{ $baiKiemTra->bat_buoc_camera ? 'Camera phải hoạt động trước khi vào thi.' : 'Không bắt buộc cho bài thi này.' }}</div>
                        </div>
                        <span class="pc-check-status badge bg-secondary">Đang chờ</span>
                    </div>

                    <div class="camera-precheck mt-4 {{ $baiKiemTra->bat_buoc_camera ? '' : 'd-none' }}">
                        <div class="pc-camera-label">
                            <i class="fas fa-camera"></i> Xem trước camera
                        </div>
                        <video id="precheckCameraPreview" autoplay muted playsinline></video>
                    </div>

                    <div id="precheckAlertArea" class="mt-4"></div>
                </div>
            </section>
        </div>

        {{-- ========== ② Quy chế & xác nhận ========== --}}
        <div class="col-lg-5">
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">2</span>
                        <div>
                            <h2><i class="fas fa-file-contract"></i> Quy chế &amp; xác nhận</h2>
                            <p>Đọc kỹ quy định, tích xác nhận và chạy pre-check để vào thi.</p>
                        </div>
                    </div>
                </header>

                <div class="pc-rules-card">
                    <div class="pc-rules-list">
                        <div class="pc-rule-line">
                            <i class="fas fa-ban"></i>
                            <span>Không chuyển tab hoặc rời khỏi cửa sổ khi đang làm bài.</span>
                        </div>
                        @if($baiKiemTra->bat_buoc_fullscreen)
                            <div class="pc-rule-line">
                                <i class="fas fa-expand"></i>
                                <span>Không thoát khỏi chế độ toàn màn hình trong suốt bài thi.</span>
                            </div>
                        @endif
                        @if($baiKiemTra->bat_buoc_camera)
                            <div class="pc-rule-line">
                                <i class="fas fa-video"></i>
                                <span>Không tắt camera trong quá trình làm bài.</span>
                            </div>
                        @endif
                        <div class="pc-rule-line">
                            <i class="fas fa-camera-retro"></i>
                            <span>Hệ thống có thể chụp snapshot và lưu log để giảng viên/admin hậu kiểm.</span>
                        </div>
                    </div>

                    <label class="pc-confirm-box">
                        <input class="form-check-input" type="checkbox" value="1" id="confirmRules">
                        <span class="pc-confirm-text">
                            Tôi đã đọc và hiểu các quy định của bài thi giám sát.
                        </span>
                    </label>

                    <form action="{{ route('hoc-vien.bai-kiem-tra.precheck.submit', $baiKiemTra->id) }}" method="POST" id="precheckForm">
                        @csrf
                        <input type="hidden" name="precheck_payload" id="precheckPayload">
                        <button type="button" class="pc-btn primary" id="runPrecheckBtn">
                            <i class="fas fa-play-circle"></i> Chạy pre-check
                        </button>
                        <button type="submit" class="pc-btn success" id="continueBtn" disabled>
                            <i class="fas fa-arrow-right-to-bracket"></i> Xác nhận và quay lại bài thi
                        </button>
                    </form>

                    @if($precheckState)
                        <div class="pc-prev-state">
                            <i class="fas fa-circle-check"></i>
                            <div>
                                <strong>Bạn đã có kết quả pre-check hợp lệ gần đây.</strong>
                                <small>Bạn vẫn có thể chạy lại để kiểm tra lần nữa.</small>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>

@include('pages.admin.partials._admin-page-styles')

@push('styles')
<style>
    /* ===== Override màu đề mục: đỏ ===== */
    .pc-page .apx-section-head {
        background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
        border-color: #fecaca;
        border-left-color: #dc2626;
    }
    .pc-page .apx-section-num {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }
    .pc-page .apx-section-title h2 i { color: #dc2626; }

    /* ===== Welcome banner xanh dương ===== */
    .pc-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
    }
    .pc-tag-row {
        display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .pc-loai-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px;
        background: rgba(255,255,255,0.22);
        border: 1px solid rgba(255,255,255,0.4);
        backdrop-filter: blur(6px);
        color: #fff;
        font-size: 0.7rem; font-weight: 800;
        letter-spacing: 1px; border-radius: 999px;
    }
    .pc-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px;
        background: rgba(255,255,255,0.14);
        color: #fff; font-size: 0.72rem; font-weight: 700;
        border-radius: 999px;
    }
    .pc-watch-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f; font-size: 0.72rem; font-weight: 800;
        border-radius: 999px;
    }
    .pc-success-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        background: #dcfce7;
        color: #166534;
        font-size: 0.72rem; font-weight: 800;
        border-radius: 999px;
    }
    .apx-welcome.pc-welcome p {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        font-size: 0.85rem;
    }
    .apx-welcome.pc-welcome p i { color: #fef3c7; margin-right: 4px; }
    .pc-sep { opacity: 0.5; }

    /* Breadcrumb */
    .pc-breadcrumb {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-left: 3px solid #dc2626;
        border-radius: 8px;
        padding: 8px 16px;
    }
    .pc-breadcrumb .breadcrumb { font-size: 0.82rem; }
    .pc-breadcrumb a { color: #dc2626; text-decoration: none; font-weight: 600; }
    .pc-breadcrumb a:hover { color: #b91c1c; text-decoration: underline; }
    .pc-breadcrumb .breadcrumb-item.active { color: #0f172a; font-weight: 700; }

    /* ===== Check card ===== */
    .pc-check-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
    }
    .pc-check-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 4px;
        border-bottom: 1px solid #f1f5f9;
    }
    .pc-check-item:last-of-type { border-bottom: none; }
    .pc-check-icon {
        flex-shrink: 0;
        width: 44px; height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        color: #dc2626;
        display: grid; place-items: center;
        font-size: 1.1rem;
    }
    .pc-check-info {
        flex: 1;
        min-width: 0;
    }
    .pc-check-title {
        font-size: 0.92rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.3;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .pc-check-desc {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 500;
        margin-top: 3px;
    }
    .pc-check-status {
        flex-shrink: 0;
        font-size: 0.74rem;
        font-weight: 800;
        padding: 6px 14px;
        border-radius: 999px;
    }
    .pc-required-tag {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px;
        background: #fee2e2;
        color: #b91c1c;
        font-size: 0.65rem;
        font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .pc-required-tag i { font-size: 0.5rem; }

    /* Camera preview */
    .camera-precheck {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px;
        background: #0f172a;
    }
    .pc-camera-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: #cbd5e1;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .pc-camera-label i { color: #fef3c7; margin-right: 5px; }
    #precheckCameraPreview {
        width: 100%;
        aspect-ratio: 4/3;
        object-fit: cover;
        border-radius: 10px;
        display: block;
        background: #020617;
    }

    /* ===== Rules card ===== */
    .pc-rules-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
    }
    .pc-rules-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 14px;
        padding-bottom: 14px;
        border-bottom: 1px dashed #e2e8f0;
    }
    .pc-rule-line {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 0.84rem;
        color: #1e293b;
        line-height: 1.5;
    }
    .pc-rule-line i {
        flex-shrink: 0;
        width: 24px; height: 24px;
        border-radius: 8px;
        background: #fef2f2;
        color: #dc2626;
        display: grid; place-items: center;
        font-size: 0.74rem;
        margin-top: 2px;
    }

    .pc-confirm-box {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 12px 14px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 10px;
        cursor: pointer;
        margin-bottom: 14px;
        transition: all 0.18s ease;
    }
    .pc-confirm-box:hover {
        background: #fee2e2;
        border-color: #fca5a5;
    }
    .pc-confirm-box .form-check-input {
        margin-top: 2px;
        flex-shrink: 0;
    }
    .pc-confirm-box .form-check-input:checked {
        background-color: #dc2626;
        border-color: #dc2626;
    }
    .pc-confirm-text {
        font-size: 0.86rem;
        font-weight: 600;
        color: #0f172a;
        line-height: 1.4;
    }

    .pc-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 11px 16px;
        font-size: 0.9rem;
        font-weight: 800;
        border-radius: 10px;
        border: 1px solid transparent;
        cursor: pointer;
        margin-bottom: 10px;
        transition: all 0.2s ease;
    }
    .pc-btn:last-child { margin-bottom: 0; }
    .pc-btn.primary {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
        color: #fff;
        box-shadow: 0 4px 12px rgba(29, 78, 216, 0.25);
    }
    .pc-btn.primary:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(29, 78, 216, 0.35);
    }
    .pc-btn.success {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: #fff;
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
    }
    .pc-btn.success:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(22, 163, 74, 0.35);
    }
    .pc-btn:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        box-shadow: none;
    }
    .pc-btn i { font-size: 0.86rem; }

    .pc-prev-state {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin-top: 14px;
        padding: 12px 14px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 10px;
    }
    .pc-prev-state i {
        flex-shrink: 0;
        font-size: 1.5rem;
        color: #16a34a;
        margin-top: 2px;
    }
    .pc-prev-state strong {
        display: block;
        font-size: 0.85rem;
        font-weight: 800;
        color: #166534;
        margin-bottom: 3px;
    }
    .pc-prev-state small {
        display: block;
        font-size: 0.78rem;
        color: #047857;
        font-weight: 500;
    }

    /* Status badges */
    .pc-check-item .badge.bg-success { background: #dcfce7 !important; color: #166534; }
    .pc-check-item .badge.bg-danger  { background: #fee2e2 !important; color: #b91c1c; }
    .pc-check-item .badge.bg-secondary { background: #f1f5f9 !important; color: #475569; }

    @media (max-width: 575.98px) {
        .pc-check-item { flex-wrap: wrap; }
        .pc-check-status { width: 100%; text-align: center; }
        .pc-breadcrumb .breadcrumb { font-size: 0.74rem; }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const runBtn = document.getElementById('runPrecheckBtn');
    const continueBtn = document.getElementById('continueBtn');
    const confirmRules = document.getElementById('confirmRules');
    const payloadInput = document.getElementById('precheckPayload');
    const preview = document.getElementById('precheckCameraPreview');
    const alertArea = document.getElementById('precheckAlertArea');
    let stream = null;

    const setStatus = (id, ok, text) => {
        const badge = document.querySelector(`#${id} .pc-check-status`);
        if (!badge) return;
        badge.className = `pc-check-status badge ${ok ? 'bg-success' : 'bg-danger'}`;
        badge.textContent = text;
    };

    const showAlert = (message, tone = 'warning') => {
        alertArea.innerHTML = `<div class="alert alert-${tone} mb-0">${message}</div>`;
    };

    async function runPrecheck() {
        runBtn.disabled = true;
        showAlert('Đang kiểm tra môi trường thi...', 'info');

        const payload = {
            browser_supported: !!document.addEventListener && !!window.fetch && !!window.FormData,
            visibility_supported: typeof document.hidden !== 'undefined',
            fullscreen_supported: !!document.documentElement.requestFullscreen,
            fullscreen_ok: !{{ $baiKiemTra->bat_buoc_fullscreen ? 'false' : 'true' }},
            camera_supported: !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia),
            camera_ok: !{{ $baiKiemTra->bat_buoc_camera ? 'false' : 'true' }},
            user_agent: navigator.userAgent,
            platform: navigator.platform,
            captured_at: new Date().toISOString(),
        };

        setStatus('check-browser', payload.browser_supported && payload.visibility_supported, payload.browser_supported && payload.visibility_supported ? 'Đạt' : 'Lỗi');

        if ({{ $baiKiemTra->bat_buoc_fullscreen ? 'true' : 'false' }}) {
            try {
                await document.documentElement.requestFullscreen();
                payload.fullscreen_ok = !!document.fullscreenElement;
                if (document.fullscreenElement) {
                    await document.exitFullscreen();
                }
            } catch (error) {
                payload.fullscreen_ok = false;
            }
        }
        setStatus('check-fullscreen', payload.fullscreen_supported && payload.fullscreen_ok, payload.fullscreen_supported && payload.fullscreen_ok ? 'Đạt' : 'Lỗi');

        if ({{ $baiKiemTra->bat_buoc_camera ? 'true' : 'false' }}) {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                payload.camera_ok = true;
                if (preview) preview.srcObject = stream;
            } catch (error) {
                payload.camera_ok = false;
            }
        }
        setStatus('check-camera', payload.camera_supported && payload.camera_ok, payload.camera_supported && payload.camera_ok ? 'Đạt' : 'Lỗi');

        payloadInput.value = JSON.stringify(payload);

        const passed = payload.browser_supported && payload.visibility_supported
            && (!{{ $baiKiemTra->bat_buoc_fullscreen ? 'true' : 'false' }} || payload.fullscreen_ok)
            && (!{{ $baiKiemTra->bat_buoc_camera ? 'true' : 'false' }} || payload.camera_ok);

        if (passed) {
            showAlert('Pre-check đã đạt. Hãy xác nhận quy chế để tiếp tục.', 'success');
            continueBtn.disabled = !confirmRules.checked;
        } else {
            showAlert('Pre-check chưa đạt. Vui lòng kiểm tra lại quyền camera hoặc fullscreen rồi thử lại.', 'danger');
            continueBtn.disabled = true;
        }

        runBtn.disabled = false;
    }

    confirmRules.addEventListener('change', () => {
        continueBtn.disabled = !confirmRules.checked || !payloadInput.value;
    });

    runBtn.addEventListener('click', () => {
        runPrecheck().catch((error) => {
            showAlert(error.message || 'Không thể chạy pre-check.', 'danger');
            runBtn.disabled = false;
        });
    });

    window.addEventListener('beforeunload', () => {
        if (stream) {
            stream.getTracks().forEach((track) => track.stop());
        }
    });
});
</script>
@endpush
@endsection
