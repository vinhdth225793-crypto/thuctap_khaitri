@extends('layouts.app', ['title' => 'Pre-check bài thi'])

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.bai-kiem-tra') }}">Bài kiểm tra</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id) }}">{{ $baiKiemTra->tieu_de }}</a></li>
                    <li class="breadcrumb-item active">Pre-check</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-1">Kiểm tra trước khi thi</h2>
            <p class="text-muted mb-0">{{ $baiKiemTra->tieu_de }} • {{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Bài thi giám sát' }}</p>
        </div>
        <a href="{{ route('hoc-vien.bai-kiem-tra.show', $baiKiemTra->id) }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại bài thi
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card vip-card">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Thiết bị và quyền truy cập</h5>
                </div>
                <div class="card-body">
                    <div class="check-item" id="check-browser">
                        <div>
                            <div class="fw-semibold">Hỗ trợ trình duyệt</div>
                            <div class="small text-muted">Kiểm tra API fullscreen, visibility và camera.</div>
                        </div>
                        <span class="badge bg-secondary">Đang chờ</span>
                    </div>
                    <div class="check-item" id="check-fullscreen">
                        <div>
                            <div class="fw-semibold">Toàn màn hình</div>
                            <div class="small text-muted">{{ $baiKiemTra->bat_buoc_fullscreen ? 'Bắt buộc cho bài thi này.' : 'Không bắt buộc nhưng vẫn kiểm tra khả năng hỗ trợ.' }}</div>
                        </div>
                        <span class="badge bg-secondary">Đang chờ</span>
                    </div>
                    <div class="check-item" id="check-camera">
                        <div>
                            <div class="fw-semibold">Camera</div>
                            <div class="small text-muted">{{ $baiKiemTra->bat_buoc_camera ? 'Camera phải hoạt động trước khi vào thi.' : 'Không bắt buộc cho bài thi này.' }}</div>
                        </div>
                        <span class="badge bg-secondary">Đang chờ</span>
                    </div>

                    <div class="camera-precheck mt-4 {{ $baiKiemTra->bat_buoc_camera ? '' : 'd-none' }}">
                        <div class="small text-muted mb-2">Xem trước camera</div>
                        <video id="precheckCameraPreview" autoplay muted playsinline></video>
                    </div>

                    <div id="precheckAlertArea" class="mt-4"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card vip-card">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Quy chế và xác nhận</h5>
                </div>
                <div class="card-body">
                    <ul class="small text-muted mb-4">
                        <li>Không chuyển tab hoặc rời khỏi cửa sổ khi đang làm bài.</li>
                        @if($baiKiemTra->bat_buoc_fullscreen)
                            <li>Không thoát khỏi chế độ toàn màn hình trong suốt bài thi.</li>
                        @endif
                        @if($baiKiemTra->bat_buoc_camera)
                            <li>Không tắt camera trong quá trình làm bài.</li>
                        @endif
                        <li>Hệ thống có thể chụp snapshot và lưu log để giảng viên/admin hậu kiểm.</li>
                    </ul>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" value="1" id="confirmRules">
                        <label class="form-check-label" for="confirmRules">
                            Tôi đã đọc và hiểu các quy định của bài thi giám sát.
                        </label>
                    </div>

                    <form action="{{ route('hoc-vien.bai-kiem-tra.precheck.submit', $baiKiemTra->id) }}" method="POST" id="precheckForm">
                        @csrf
                        <input type="hidden" name="precheck_payload" id="precheckPayload">
                        <button type="button" class="btn btn-primary w-100 mb-3" id="runPrecheckBtn">Chạy pre-check</button>
                        <button type="submit" class="btn btn-success w-100" id="continueBtn" disabled>Xác nhận và quay lại bài thi</button>
                    </form>

                    @if($precheckState)
                        <div class="alert alert-success mt-3 mb-0">
                            Bạn đã có một kết quả pre-check hợp lệ gần đây. Bạn vẫn có thể chạy lại để kiểm tra lần nữa.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .check-item { display:flex; justify-content:space-between; gap:1rem; padding:1rem 0; border-bottom:1px solid #eef2f7; }
    .check-item:last-child { border-bottom:none; }
    .camera-precheck { border:1px solid #e2e8f0; border-radius:18px; padding:1rem; background:#0f172a; }
    #precheckCameraPreview { width:100%; aspect-ratio:4/3; object-fit:cover; border-radius:14px; display:block; background:#020617; }
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
        const badge = document.querySelector(`#${id} .badge`);
        if (!badge) return;
        badge.className = `badge ${ok ? 'bg-success' : 'bg-danger'}`;
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
