@include('pages.admin.partials._admin-page-styles')

<style>
    /* Override màu đề mục: đỏ */
    .prof-page .apx-section-head { background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; border-left-color: #dc2626; }
    .prof-page .apx-section-num { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
    .prof-page .apx-section-title h2 i { color: #dc2626; }

    /* Welcome banner xanh dương */
    .prof-welcome { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important; box-shadow: 0 16px 36px rgba(29,78,216,0.22) !important; }
    .prof-welcome .apx-welcome-icon { display: none; }
    .prof-tag-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .prof-loai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(6px); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; border-radius: 999px; }
    .prof-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        background: rgba(255,255,255,0.14);
        color: #fff; font-size: 0.72rem; font-weight: 700;
        border-radius: 999px;
    }
    .prof-status-badge.is-success { background: #dcfce7; color: #166534; }
    .prof-status-badge.is-warning { background: #fef3c7; color: #b45309; }
    .prof-status-badge.is-danger  { background: #fee2e2; color: #b91c1c; }
    .apx-welcome.prof-welcome p { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 0.85rem; }
    .apx-welcome.prof-welcome p i { color: #fef3c7; margin-right: 4px; }
    .prof-sep { opacity: 0.5; }

    /* Avatar lớn ở header */
    .prof-avatar-lg {
        flex-shrink: 0;
        width: 76px; height: 76px;
        border-radius: 50%;
        background: rgba(255,255,255,0.18);
        border: 3px solid rgba(255,255,255,0.4);
        backdrop-filter: blur(6px);
        overflow: hidden;
        display: grid; place-items: center;
        color: #fff;
        font-size: 2.1rem;
        font-weight: 800;
        box-shadow: 0 10px 24px rgba(0,0,0,0.18);
        z-index: 1;
    }
    .prof-avatar-lg img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .prof-avatar-lg span { line-height: 1; }

    /* Avatar card */
    .prof-avatar-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 22px 20px;
        text-align: center;
    }
    .prof-avatar-preview {
        width: 160px; height: 160px;
        margin: 0 auto 16px;
        border-radius: 50%;
        overflow: hidden;
        position: relative;
        border: 4px solid #fef2f2;
        box-shadow: 0 10px 26px rgba(220,38,38,0.12);
    }
    .prof-avatar-preview img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .prof-avatar-fallback {
        width: 100%; height: 100%;
        background: linear-gradient(135deg, #1d4ed8 0%, #4361ee 100%);
        color: #fff;
        font-size: 4rem;
        font-weight: 800;
        display: grid; place-items: center;
    }
    .prof-remove-row {
        display: inline-flex; align-items: center; gap: 6px;
        margin-bottom: 12px;
        padding: 6px 12px;
        background: #fef2f2;
        color: #dc2626;
        font-size: 0.78rem;
        font-weight: 700;
        border-radius: 8px;
        cursor: pointer;
    }
    .prof-remove-row input[type="checkbox"] { accent-color: #dc2626; }
    .prof-upload-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 18px;
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
        color: #fff;
        font-size: 0.86rem;
        font-weight: 800;
        border-radius: 10px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(29,78,216,0.22);
        transition: all 0.18s ease;
    }
    .prof-upload-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(29,78,216,0.32); }
    .prof-upload-hint {
        margin-top: 12px;
        font-size: 0.74rem;
        color: #94a3b8;
        font-weight: 600;
    }
    .prof-upload-hint i { color: #1d4ed8; margin-right: 5px; }

    /* Meta card */
    .prof-meta-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 18px;
    }
    .prof-meta-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 9px 0;
        border-bottom: 1px dashed #f1f5f9;
        flex-wrap: wrap;
    }
    .prof-meta-row:last-child { border-bottom: 0; }
    .prof-meta-label {
        font-size: 0.74rem;
        font-weight: 800;
        color: #7f1d1d;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .prof-meta-row strong {
        font-size: 0.86rem;
        font-weight: 800;
        color: #0f172a;
    }
    .prof-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px;
        font-size: 0.7rem; font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .prof-pill i { font-size: 0.6rem; }
    .prof-pill.is-success { background: #dcfce7; color: #166534; }
    .prof-pill.is-warning { background: #fef3c7; color: #b45309; }
    .prof-pill.is-info    { background: #cffafe; color: #0e7490; }
    .prof-pill.is-danger  { background: #fee2e2; color: #b91c1c; }

    /* Form card */
    .prof-form-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
    }
    .prof-label {
        display: block;
        font-weight: 800;
        color: #7f1d1d;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 6px;
    }
    .prof-input {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        padding: 9px 14px;
        font-size: 0.9rem;
        transition: all 0.18s ease;
    }
    .prof-input:focus {
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }

    /* Action buttons */
    .prof-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        padding: 16px 18px;
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border: 1px solid #fecaca;
        border-radius: 14px;
        position: sticky;
        bottom: 14px;
        backdrop-filter: blur(10px);
        z-index: 10;
    }
    .prof-btn-save {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 20px;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff;
        font-size: 0.9rem;
        font-weight: 800;
        border-radius: 10px;
        border: 0;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(220,38,38,0.25);
        transition: all 0.2s ease;
    }
    .prof-btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(220,38,38,0.35);
    }
    .prof-btn-cancel {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 24px;
        background: #fff;
        color: #475569;
        border: 1px solid #e2e8f0;
        font-size: 0.9rem;
        font-weight: 800;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .prof-btn-cancel:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
</style>
