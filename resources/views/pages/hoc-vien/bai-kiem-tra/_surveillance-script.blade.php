@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const config = {
        logUrl: @json(route('hoc-vien.bai-lam.giam-sat.log', $baiLam->id)),
        snapshotUrl: @json(route('hoc-vien.bai-lam.giam-sat.snapshot', $baiLam->id)),
        csrf: @json(csrf_token()),
        fullscreenRequired: @json((bool) $baiKiemTra->bat_buoc_fullscreen),
        cameraRequired: @json((bool) $baiKiemTra->bat_buoc_camera),
        snapshotInterval: @json((int) $baiKiemTra->chu_ky_snapshot_giay),
        blockCopyPaste: @json((bool) $baiKiemTra->chan_copy_paste),
        blockRightClick: @json((bool) $baiKiemTra->chan_chuot_phai),
        initialViolations: @json((int) $baiLam->tong_so_vi_pham),
        maxViolations: @json((int) $baiKiemTra->so_lan_vi_pham_toi_da),
    };

    const state = { stream: null, monitoring: false, autoSubmitted: false, lastEventAt: {}, snapshotTimer: null, cameraWatchTimer: null };
    const overlay = document.getElementById('surveillanceOverlay');
    const overlayError = document.getElementById('surveillanceOverlayError');
    const activateBtn = document.getElementById('activateSurveillanceBtn');
    const examForm = document.getElementById('examSubmissionForm');
    const autoSubmitInput = document.getElementById('autoSubmitInput');
    const liveViolationCount = document.getElementById('liveViolationCount');
    const currentViolationCount = document.getElementById('currentViolationCount');
    const fullscreenStatusText = document.getElementById('fullscreenStatusText');
    const cameraStatusText = document.getElementById('cameraStatusText');
    const cameraStatusBadge = document.getElementById('cameraStatusBadge');
    const cameraPreview = document.getElementById('surveillanceCameraPreview');
    const alertArea = document.getElementById('surveillanceAlertArea');

    function updateCount(value) {
        const next = Number(value || 0);
        if (liveViolationCount) liveViolationCount.textContent = String(next);
        if (currentViolationCount) currentViolationCount.textContent = String(next);
    }

    function showAlert(message, tone = 'warning') {
        if (!alertArea || !message) return;
        alertArea.innerHTML = `<div class="alert alert-${tone} alert-dismissible fade show"><div>${message}</div><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
    }

    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': config.csrf,
            },
            body: JSON.stringify(payload),
            keepalive: true,
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.message || 'Không thể gửi dữ liệu giám sát.');
        }

        return data;
    }

    function canSend(eventType, cooldownMs = 2500) {
        const now = Date.now();
        const last = state.lastEventAt[eventType] || 0;
        if (now - last < cooldownMs) return false;
        state.lastEventAt[eventType] = now;
        return true;
    }

    async function logEvent(eventType, description, meta = {}) {
        if (!canSend(eventType)) return null;
        const data = await postJson(config.logUrl, { event_type: eventType, description, meta });
        if (typeof data.violation_count !== 'undefined') updateCount(data.violation_count);
        if (data.warning_message) showAlert(data.warning_message, data.auto_submit_required ? 'danger' : 'warning');
        if (data.auto_submit_required) triggerAutoSubmit();
        return data;
    }

    function setFullscreenText() {
        if (!fullscreenStatusText) return;
        fullscreenStatusText.textContent = config.fullscreenRequired
            ? (document.fullscreenElement ? 'Đang bật' : 'Đã thoát')
            : 'Không bắt buộc';
    }

    function setCameraText(active) {
        if (cameraStatusText) cameraStatusText.textContent = config.cameraRequired ? (active ? 'Đang bật' : 'Đã tắt') : 'Không bắt buộc';
        if (cameraStatusBadge) {
            cameraStatusBadge.className = `badge ${active ? 'bg-success' : 'bg-secondary'}`;
            cameraStatusBadge.textContent = active ? 'Đang bật' : 'Đã tắt';
        }
    }

    async function ensureCamera() {
        if (!config.cameraRequired) return true;
        if (state.stream) return true;
        state.stream = await navigator.mediaDevices.getUserMedia({ video: { width: { ideal: 640 }, height: { ideal: 480 } }, audio: false });
        if (cameraPreview) cameraPreview.srcObject = state.stream;
        const track = state.stream.getVideoTracks()[0];
        if (track) track.addEventListener('ended', () => state.monitoring && logEvent('camera_off', 'Camera đã bị tắt trong lúc làm bài.'));
        setCameraText(true);
        return true;
    }

    async function ensureFullscreen() {
        if (!config.fullscreenRequired) return true;
        if (!document.fullscreenElement) await document.documentElement.requestFullscreen();
        setFullscreenText();
        return !!document.fullscreenElement;
    }

    async function captureSnapshot() {
        if (!state.monitoring || !config.cameraRequired || !state.stream || !cameraPreview) return;
        try {
            const canvas = document.createElement('canvas');
            const width = Math.min(cameraPreview.videoWidth || 640, 640);
            const height = Math.round(width * ((cameraPreview.videoHeight || 480) / (cameraPreview.videoWidth || 640)));
            canvas.width = width;
            canvas.height = height;
            canvas.getContext('2d').drawImage(cameraPreview, 0, 0, width, height);
            await postJson(config.snapshotUrl, { status: 'captured', image_data: canvas.toDataURL('image/jpeg', 0.72), meta: { width, height } });
        } catch (error) {
            await postJson(config.snapshotUrl, { status: 'failed', message: error.message || 'Không thể chụp snapshot.' }).catch(() => null);
        }
    }

    function stopMonitoring() {
        if (state.snapshotTimer) window.clearInterval(state.snapshotTimer);
        if (state.cameraWatchTimer) window.clearInterval(state.cameraWatchTimer);
        if (state.stream) state.stream.getTracks().forEach((track) => track.stop());
        state.snapshotTimer = null;
        state.cameraWatchTimer = null;
        state.stream = null;
    }

    function triggerAutoSubmit() {
        if (state.autoSubmitted || !examForm) return;
        state.autoSubmitted = true;
        if (autoSubmitInput) autoSubmitInput.value = '1';
        showAlert('Đã vượt ngưỡng vi phạm. Hệ thống đang tự động nộp bài.', 'danger');
        stopMonitoring();
        window.setTimeout(() => examForm.submit(), 1000);
    }

    async function activateMonitoring() {
        if (activateBtn) activateBtn.disabled = true;
        if (overlayError) { overlayError.classList.add('d-none'); overlayError.textContent = ''; }
        try {
            await ensureCamera();
            await ensureFullscreen();
            state.monitoring = true;
            setFullscreenText();
            if (config.cameraRequired) {
                state.snapshotTimer = window.setInterval(() => captureSnapshot().catch(() => null), config.snapshotInterval * 1000);
                state.cameraWatchTimer = window.setInterval(() => {
                    const track = state.stream?.getVideoTracks?.()[0];
                    const active = !!track && track.readyState === 'live' && !track.muted;
                    setCameraText(active);
                    if (!active && state.monitoring) logEvent('camera_off', 'Không phát hiện được tín hiệu camera ổn định.').catch(() => null);
                }, 5000);
            }
            if (overlay) overlay.classList.add('d-none');
        } catch (error) {
            if (overlayError) { overlayError.textContent = error.message || 'Không thể kích hoạt giám sát.'; overlayError.classList.remove('d-none'); }
        } finally {
            if (activateBtn) activateBtn.disabled = false;
        }
    }

    document.addEventListener('visibilitychange', () => state.monitoring && document.hidden && logEvent('tab_switch', 'Phát hiện chuyển tab hoặc ẩn cửa sổ bài thi.').catch(() => null));
    window.addEventListener('blur', () => state.monitoring && logEvent('window_blur', 'Phát hiện rời khỏi cửa sổ đang thi.').catch(() => null));
    window.addEventListener('focus', () => state.monitoring && logEvent('window_focus', 'Đã quay lại cửa sổ bài thi.').catch(() => null));
    document.addEventListener('fullscreenchange', () => {
        setFullscreenText();
        if (state.monitoring && config.fullscreenRequired && !document.fullscreenElement) logEvent('fullscreen_exit', 'Phát hiện thoát khỏi chế độ toàn màn hình.').catch(() => null);
    });

    if (config.blockCopyPaste) ['copy', 'cut', 'paste'].forEach((eventName) => document.addEventListener(eventName, (event) => {
        event.preventDefault();
        state.monitoring && logEvent('copy_paste_blocked', 'Thao tác copy/paste đã bị chặn.', { action: eventName }).catch(() => null);
    }));

    if (config.blockRightClick) document.addEventListener('contextmenu', (event) => {
        event.preventDefault();
        state.monitoring && logEvent('right_click_blocked', 'Chuột phải đã bị chặn.', { action: 'contextmenu' }).catch(() => null);
    });

    window.addEventListener('beforeunload', stopMonitoring);
    if (examForm) examForm.addEventListener('submit', stopMonitoring);
    if (activateBtn) activateBtn.addEventListener('click', () => activateMonitoring().catch(() => null));

    setFullscreenText();
    setCameraText(false);
    updateCount(config.initialViolations);
});
</script>
@endpush
