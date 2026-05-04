@include('pages.admin.partials._admin-page-styles')

<style>
    .tv-welcome {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important;
        box-shadow: 0 16px 36px rgba(29, 78, 216, 0.22) !important;
    }

    .tv-tag-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .tv-library-badge,
    .tv-status-badge,
    .tv-filter-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        font-weight: 800;
    }

    .tv-library-badge {
        padding: 4px 12px;
        background: rgba(255, 255, 255, 0.22);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: #fff;
        font-size: 0.7rem;
        letter-spacing: 1px;
    }

    .tv-status-badge {
        padding: 3px 10px;
        background: rgba(255, 255, 255, 0.14);
        color: #fff;
        font-size: 0.72rem;
    }

    .tv-filter-badge {
        padding: 3px 10px;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        font-size: 0.72rem;
    }

    .apx-welcome.tv-welcome p {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        font-size: 0.85rem;
    }

    .apx-welcome.tv-welcome p i {
        color: #dbeafe;
        margin-right: 4px;
    }

    .tv-sep { opacity: 0.5; }
    .smaller { font-size: 0.75rem; }
    .shadow-xs { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); }
    .last-child-no-border:last-child { border-bottom: none !important; margin-bottom: 0 !important; padding-bottom: 0 !important; }

    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .bg-soft-secondary { background-color: rgba(108, 117, 125, 0.1); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); }
    .bg-soft-danger { background-color: rgba(231, 74, 59, 0.1); }

    .tv-page .breadcrumb-item + .breadcrumb-item::before {
        content: "\f105";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        font-size: 0.7rem;
        color: #adb5bd;
    }

    .tv-resource-list-scroll {
        max-height: 72vh;
        overflow-y: auto;
        scroll-behavior: smooth;
    }

    .tv-resource-list-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .tv-resource-list-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }

    .tv-page .apx-section-head {
        background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
        border-color: #fecaca;
        border-left-color: #dc2626;
    }

    .tv-page .apx-section-num {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }

    .tv-page .apx-section-title h2,
    .tv-page .apx-section-title h2 i {
        color: #b91c1c;
    }

    .tv-page .apx-meta-pill {
        border-color: #fecaca;
        color: #b91c1c;
    }

    .tv-page .apx-meta-pill strong {
        color: #b91c1c;
    }

    .tv-filter-card,
    .tv-form-card,
    .tv-side-card,
    .tv-current-card,
    .tv-pagination-wrap,
    .tv-empty-state {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }

    .tv-filter-card,
    .tv-form-card,
    .tv-side-card,
    .tv-current-card,
    .tv-pagination-wrap {
        padding: 18px;
    }

    .tv-form-card,
    .tv-side-card,
    .tv-current-card {
        height: 100%;
    }

    .tv-field-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.76rem;
        font-weight: 800;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .tv-field-label i {
        color: #1d4ed8;
        font-size: 0.78rem;
    }

    .tv-input-group .input-group-text,
    .tv-filter-card .form-control,
    .tv-filter-card .form-select,
    .tv-form-card .form-control,
    .tv-form-card .form-select,
    .tv-side-card .form-control,
    .tv-side-card .form-select {
        border-radius: 12px;
        border: 1.5px solid #dbe3ef;
        min-height: 46px;
    }

    .tv-form-card textarea.form-control,
    .tv-side-card textarea.form-control {
        min-height: auto;
    }

    .tv-input-group .input-group-text {
        border-right: 0;
        background: #f8fafc;
        color: #64748b;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .tv-input-group .form-control {
        border-left: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .tv-filter-card .form-control:focus,
    .tv-filter-card .form-select:focus,
    .tv-form-card .form-control:focus,
    .tv-form-card .form-select:focus,
    .tv-side-card .form-control:focus,
    .tv-side-card .form-select:focus {
        border-color: #1d4ed8;
        box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.1);
    }

    .tv-filter-actions,
    .tv-form-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .tv-form-actions {
        justify-content: space-between;
        align-items: center;
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px dashed #e2e8f0;
    }

    .tv-mini-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-top: 16px;
    }

    .tv-mini-metric {
        padding: 12px 14px;
        border-radius: 12px;
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .tv-mini-metric span {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 700;
    }

    .tv-mini-metric strong {
        font-size: 1rem;
        color: #0f172a;
        font-weight: 900;
    }

    .tv-resource-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-left: 4px solid var(--tv-accent);
        border-radius: 18px;
        padding: 18px;
        height: 100%;
        display: flex;
        flex-direction: column;
        gap: 14px;
        transition: all 0.25s ease;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }

    .tv-resource-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.08);
        border-color: #cbd5e1;
    }

    .tv-resource-top {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }

    .tv-resource-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: var(--tv-accent-soft);
        color: var(--tv-accent);
        display: grid;
        place-items: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .tv-resource-main {
        flex: 1;
        min-width: 0;
    }

    .tv-resource-badges {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }

    .tv-inline-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        background: var(--tv-accent-soft);
        color: var(--tv-accent);
        font-size: 0.72rem;
        font-weight: 800;
    }

    .tv-status-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
        border: 1px solid transparent;
    }

    .tv-status-pill.is-success { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
    .tv-status-pill.is-warning { background: #fef3c7; color: #92400e; border-color: #fde68a; }
    .tv-status-pill.is-danger  { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
    .tv-status-pill.is-info    { background: #e0f2fe; color: #075985; border-color: #bae6fd; }
    .tv-status-pill.is-primary { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
    .tv-status-pill.is-secondary,
    .tv-status-pill.is-slate   { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
    .tv-status-pill.is-blue    { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .tv-status-pill.is-green   { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }

    .tv-resource-title,
    .tv-side-title {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 8px;
        line-height: 1.35;
    }

    .tv-resource-desc,
    .tv-side-text {
        margin: 0;
        color: #64748b;
        font-size: 0.84rem;
        line-height: 1.55;
    }

    .tv-resource-source,
    .tv-resource-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 14px;
        color: #64748b;
        font-size: 0.78rem;
    }

    .tv-resource-source,
    .tv-upload-shell,
    .tv-current-file,
    .tv-help-card {
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px solid #eef2f7;
        border-radius: 12px;
    }

    .tv-resource-source {
        align-items: center;
        font-weight: 600;
    }

    .tv-resource-source i,
    .tv-resource-meta i {
        color: var(--tv-accent);
    }

    .tv-feedback-card {
        padding: 12px 14px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 12px;
    }

    .tv-feedback-title {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        font-weight: 800;
        color: #c2410c;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 6px;
    }

    .tv-feedback-card p {
        margin: 0;
        color: #9a3412;
        font-size: 0.82rem;
        line-height: 1.55;
    }

    .tv-resource-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: auto;
        padding-top: 14px;
        border-top: 1px dashed #e2e8f0;
    }

    .tv-help-card {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .tv-help-card.is-warning {
        background: #fff7ed;
        border-color: #fed7aa;
    }

    .tv-help-card.is-blue {
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .tv-help-title {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #0f172a;
    }

    .tv-help-card.is-warning .tv-help-title { color: #9a3412; }
    .tv-help-card.is-blue .tv-help-title { color: #1d4ed8; }

    .tv-help-card p {
        margin: 0;
        color: #64748b;
        font-size: 0.84rem;
        line-height: 1.6;
    }

    .tv-side-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .tv-side-list li {
        display: flex;
        gap: 10px;
        color: #475569;
        font-size: 0.84rem;
        line-height: 1.55;
    }

    .tv-side-list i {
        color: #dc2626;
        margin-top: 3px;
        flex-shrink: 0;
    }

    .tv-side-stack {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .tv-current-file {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .tv-current-file-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: rgba(29, 78, 216, 0.12);
        color: #1d4ed8;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .tv-current-file-body {
        flex: 1;
        min-width: 0;
    }

    .tv-current-file-body strong {
        display: block;
        color: #0f172a;
        font-size: 0.9rem;
        font-weight: 800;
        line-height: 1.35;
    }

    .tv-current-file-body span {
        display: block;
        color: #64748b;
        font-size: 0.78rem;
        margin-top: 4px;
        line-height: 1.5;
        word-break: break-word;
    }

    .tv-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .tv-info-chip {
        padding: 10px 12px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #eef2f7;
    }

    .tv-info-chip small {
        display: block;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .tv-info-chip strong {
        display: block;
        color: #0f172a;
        font-size: 0.82rem;
        font-weight: 800;
        line-height: 1.4;
    }

    .tv-or-divider {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 12px 0;
        color: #94a3b8;
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }

    .tv-or-divider::before,
    .tv-or-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }

    .tv-pagination-wrap .pagination {
        margin-bottom: 0;
        justify-content: center;
    }

    .tv-empty-state {
        padding: 42px 24px;
        text-align: center;
    }

    .tv-empty-state i {
        font-size: 2.8rem;
        color: #cbd5e1;
        margin-bottom: 14px;
        display: block;
    }

    .tv-empty-state h3 {
        font-size: 1.1rem;
        color: #0f172a;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .tv-empty-state p {
        color: #64748b;
        font-size: 0.9rem;
        max-width: 620px;
        margin: 0 auto 18px;
        line-height: 1.65;
    }

    @media (max-width: 991.98px) {
        .tv-mini-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 767.98px) {
        .tv-resource-top { align-items: stretch; }
        .tv-resource-icon,
        .tv-current-file-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
        }

        .tv-filter-actions,
        .tv-form-actions {
            flex-direction: column;
        }

        .tv-form-actions > * {
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        .tv-mini-grid,
        .tv-info-grid {
            grid-template-columns: 1fr;
        }

        .tv-resource-actions .btn,
        .tv-resource-actions form {
            width: 100%;
        }

        .tv-resource-actions form .btn {
            width: 100%;
        }
    }
</style>
