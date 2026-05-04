<style>
    /* ============================================================
       Admin page shared styles — welcome banner + section heading
       Dùng class prefix .apx- (admin-page-extension) để tránh đụng độ
       ============================================================ */

    .admin-page-x { display: flex; flex-direction: column; gap: 6px; }

    /* ===== Welcome banner — xanh dương chủ đạo ===== */
    .apx-welcome {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 22px 26px;
        margin-bottom: 18px;
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%);
        color: #fff;
        border-radius: 16px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22);
    }

    .apx-welcome::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 220px; height: 220px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
    }

    .apx-welcome::after {
        content: '';
        position: absolute;
        bottom: -90px; right: 80px;
        width: 180px; height: 180px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
    }

    .apx-welcome-icon {
        flex-shrink: 0;
        width: 60px; height: 60px;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.28);
        backdrop-filter: blur(8px);
        color: #fff;
        border-radius: 14px;
        display: grid; place-items: center;
        font-size: 1.5rem;
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.18);
        z-index: 1;
    }

    .apx-welcome-text { flex: 1; min-width: 0; z-index: 1; }
    .apx-welcome-text h4 { font-size: 1.15rem; font-weight: 800; color: #fff; margin: 0 0 6px; }
    .apx-welcome-text p { margin: 0; font-size: 0.88rem; color: rgba(255, 255, 255, 0.92); line-height: 1.6; }
    .apx-welcome-text strong { color: #fef3c7; font-weight: 700; }

    .apx-welcome-cta { flex-shrink: 0; z-index: 1; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

    /* Nút "Thu gọn / Tổng thể" */
    .apx-view-toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(8px);
        color: #fff;
        font-size: 0.85rem;
        font-weight: 700;
        border-radius: 999px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .apx-view-toggle:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.6);
        transform: translateY(-1px);
    }
    .apx-view-toggle i { font-size: 0.78rem; }

    /* ===== Section ===== */
    .apx-section { margin-bottom: 18px; }

    .apx-section-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        margin-bottom: 12px;
        background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
        border: 1px solid #bfdbfe;
        border-left: 4px solid #1d4ed8;
        border-radius: 10px;
        transition: all 0.25s ease;
    }

    .apx-section-title { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }

    .apx-section-num {
        flex-shrink: 0;
        width: 32px; height: 32px;
        border-radius: 10px;
        background: linear-gradient(135deg, #1d4ed8 0%, #2f46c9 100%);
        color: #fff;
        display: grid; place-items: center;
        font-weight: 900; font-size: 0.9rem;
        box-shadow: 0 4px 12px rgba(29, 78, 216, 0.25);
    }

    .apx-section-title h2 {
        font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0 0 2px;
        display: inline-flex; align-items: center; gap: 8px;
    }
    .apx-section-title h2 i { font-size: 0.9rem; color: #1d4ed8; }
    .apx-section-title p { margin: 0; font-size: 0.78rem; color: #64748b; }

    .apx-section-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .apx-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        background: #fff;
        border: 1px solid #c7d2fe;
        border-radius: 999px;
        color: #4361ee;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .apx-meta-pill strong { font-weight: 800; color: #1d4ed8; }

    .apx-section-body { animation: apxFadeIn 0.25s ease; }

    @keyframes apxFadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Trạng thái thu gọn */
    .apx-section.is-collapsed .apx-section-body { display: none; }
    .apx-section.is-collapsed .apx-section-head { margin-bottom: 0; opacity: 0.85; }

    /* ===== Stats card ===== */
    .apx-stat {
        display: flex; align-items: center; gap: 14px;
        padding: 16px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        height: 100%;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .apx-stat::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 4px;
        background: var(--aps-color, #4361ee);
        transform: scaleY(0);
        transform-origin: top center;
        transition: transform 0.2s ease;
    }

    .apx-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
        border-color: var(--aps-color, #4361ee);
    }
    .apx-stat:hover::before { transform: scaleY(1); }

    .apx-stat.tone-primary  { --aps-color: #1d4ed8; }
    .apx-stat.tone-info     { --aps-color: #0ea5e9; }
    .apx-stat.tone-success  { --aps-color: #16a34a; }
    .apx-stat.tone-warning  { --aps-color: #d97706; }
    .apx-stat.tone-danger   { --aps-color: #dc2626; }
    .apx-stat.tone-dark     { --aps-color: #1e293b; }
    .apx-stat.tone-violet   { --aps-color: #7c3aed; }

    .aps-icon {
        flex-shrink: 0;
        width: 44px; height: 44px;
        border-radius: 12px;
        display: grid; place-items: center;
        background: color-mix(in srgb, var(--aps-color) 12%, white);
        color: var(--aps-color);
        font-size: 1.05rem;
        transition: all 0.2s ease;
    }

    .apx-stat:hover .aps-icon {
        background: var(--aps-color);
        color: #fff;
        transform: scale(1.05);
    }

    .aps-text strong {
        display: block;
        font-size: 1.5rem;
        font-weight: 900;
        color: #0f172a;
        line-height: 1;
    }
    .aps-text small {
        display: block;
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
    }

    /* ===== Filter card ===== */
    .apx-filter-card { border-radius: 12px; }

    .apx-field-label {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    /* ===== Responsive ===== */
    @media (max-width: 991.98px) {
        .apx-welcome { flex-direction: column; text-align: center; }
        .apx-welcome::before { display: none; }
    }

    @media (max-width: 720px) {
        .apx-welcome-icon { width: 48px; height: 48px; font-size: 1.2rem; }
        .apx-welcome-text h4 { font-size: 0.95rem; }
        .apx-welcome-text p { font-size: 0.78rem; }
        .aps-text strong { font-size: 1.2rem; }
    }
</style>
