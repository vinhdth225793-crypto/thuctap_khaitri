@include('pages.admin.partials._admin-page-styles')

<style>
    .approval-page .breadcrumb-item + .breadcrumb-item::before {
        content: "\f105";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        font-size: 0.7rem;
        color: #adb5bd;
    }

    .approval-tag-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 8px;
    }

    .approval-chip,
    .approval-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        font-weight: 800;
    }

    .approval-chip {
        padding: 4px 12px;
        background: rgba(255, 255, 255, 0.22);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: #fff;
        font-size: 0.7rem;
        letter-spacing: 1px;
    }

    .approval-status-pill {
        padding: 4px 10px;
        font-size: 0.72rem;
        border: 1px solid transparent;
    }

    .approval-status-pill.is-success { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
    .approval-status-pill.is-warning { background: #fef3c7; color: #92400e; border-color: #fde68a; }
    .approval-status-pill.is-danger { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
    .approval-status-pill.is-info { background: #e0f2fe; color: #075985; border-color: #bae6fd; }
    .approval-status-pill.is-blue { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .approval-status-pill.is-secondary { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }

    .approval-page .apx-welcome-text p {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        font-size: 0.85rem;
    }

    .approval-page .apx-welcome-text p i {
        color: #dbeafe;
        margin-right: 4px;
    }

    .approval-sep { opacity: 0.55; }
    .smaller { font-size: 0.75rem; }

    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); }
    .bg-soft-danger { background-color: rgba(231, 74, 59, 0.1); }

    .approval-page .apx-section-body {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .approval-info-card,
    .approval-action-card,
    .approval-free-essay,
    .approval-table-wrap {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }

    .approval-info-card,
    .approval-action-card,
    .approval-free-essay {
        padding: 18px;
    }

    .approval-card-title {
        margin: 0 0 14px;
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
    }

    .approval-kv-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .approval-kv {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding-bottom: 12px;
        border-bottom: 1px solid #eef2f7;
    }

    .approval-kv:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .approval-kv span {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .approval-kv strong {
        max-width: 62%;
        text-align: right;
        color: #0f172a;
        font-size: 0.88rem;
        font-weight: 800;
        line-height: 1.45;
    }

    .approval-status-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .approval-status-box {
        padding: 14px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
        text-align: center;
    }

    .approval-status-box small {
        display: block;
        margin-bottom: 6px;
        color: #64748b;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 800;
    }

    .approval-status-box strong {
        color: #0f172a;
        font-size: 0.95rem;
        font-weight: 900;
        line-height: 1.35;
    }

    .approval-status-box.is-success strong { color: #166534; }
    .approval-status-box.is-warning strong { color: #92400e; }
    .approval-status-box.is-danger strong { color: #b91c1c; }
    .approval-status-box.is-info strong { color: #075985; }
    .approval-status-box.is-blue strong { color: #1d4ed8; }
    .approval-status-box.is-secondary strong { color: #475569; }

    .approval-form-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 8px;
        font-size: 0.74rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.45px;
    }

    .approval-action-card .form-control {
        min-height: 46px;
        border-radius: 12px;
        border: 1.5px solid #dbe3ef;
        background: #f8fafc;
    }

    .approval-action-card textarea.form-control,
    .approval-reject-shell textarea.form-control,
    .approval-free-content {
        min-height: auto;
    }

    .approval-action-card .form-control:focus,
    .approval-reject-shell .form-control:focus {
        border-color: #1d4ed8;
        box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.1);
    }

    .approval-reject-shell {
        padding: 14px;
        background: #fff5f5;
        border: 1px solid #fecaca;
        border-radius: 14px;
    }

    .approval-action-divider {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px dashed #dbe3ef;
    }

    .approval-question-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .approval-scroll {
        max-height: 72vh;
        overflow-y: auto;
        padding-right: 4px;
        scroll-behavior: smooth;
    }

    .approval-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .approval-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }

    .approval-question-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 18px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }

    .approval-question-head {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 12px;
    }

    .approval-question-num {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        background: linear-gradient(135deg, #1d4ed8 0%, #2f46c9 100%);
        color: #fff;
        display: grid;
        place-items: center;
        flex-shrink: 0;
        font-weight: 900;
        box-shadow: 0 6px 18px rgba(29, 78, 216, 0.2);
    }

    .approval-question-main {
        flex: 1;
        min-width: 0;
    }

    .approval-question-title {
        margin: 0;
        color: #0f172a;
        font-size: 0.98rem;
        font-weight: 800;
        line-height: 1.55;
    }

    .approval-question-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .approval-answer-card {
        display: flex;
        align-items: center;
        gap: 12px;
        height: 100%;
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px solid #eef2f7;
        border-radius: 14px;
        transition: all 0.2s ease;
    }

    .approval-answer-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.06);
    }

    .approval-answer-card.is-correct {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .approval-answer-key {
        width: 30px;
        height: 30px;
        border-radius: 999px;
        background: #fff;
        border: 1px solid #dbe3ef;
        color: #64748b;
        display: grid;
        place-items: center;
        flex-shrink: 0;
        font-weight: 800;
    }

    .approval-answer-body {
        flex: 1;
        min-width: 0;
    }

    .approval-answer-text {
        color: #334155;
        font-size: 0.85rem;
        line-height: 1.5;
    }

    .approval-answer-card.is-correct .approval-answer-text {
        color: #166534;
        font-weight: 700;
    }

    .approval-free-head,
    .approval-note {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .approval-free-head {
        margin-bottom: 16px;
    }

    .approval-free-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: linear-gradient(135deg, #1d4ed8 0%, #2f46c9 100%);
        color: #fff;
        display: grid;
        place-items: center;
        flex-shrink: 0;
        box-shadow: 0 8px 18px rgba(29, 78, 216, 0.18);
    }

    .approval-free-head h3 {
        margin: 0 0 4px;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 800;
    }

    .approval-free-head p {
        margin: 0;
        color: #64748b;
        font-size: 0.84rem;
        line-height: 1.55;
    }

    .approval-free-body {
        padding: 16px;
        background: #f8fafc;
        border: 1px solid #eef2f7;
        border-radius: 14px;
        margin-bottom: 16px;
    }

    .approval-free-content {
        color: #0f172a;
        font-size: 0.92rem;
        line-height: 1.8;
        white-space: pre-wrap;
    }

    .approval-note {
        padding: 14px;
        border-radius: 14px;
        border: 1px solid transparent;
    }

    .approval-note i {
        margin-top: 2px;
        flex-shrink: 0;
        font-size: 1rem;
    }

    .approval-note strong {
        display: block;
        font-size: 0.88rem;
        font-weight: 800;
    }

    .approval-note span {
        display: block;
        font-size: 0.82rem;
        line-height: 1.5;
        margin-top: 2px;
    }

    .approval-note.is-success {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .approval-note.is-success i,
    .approval-note.is-success strong {
        color: #166534;
    }

    .approval-note.is-success span {
        color: #15803d;
    }

    .approval-note.is-info {
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .approval-note.is-info i,
    .approval-note.is-info strong {
        color: #1d4ed8;
    }

    .approval-note.is-info span {
        color: #1e40af;
    }

    .approval-table-wrap {
        overflow: hidden;
    }

    .approval-empty {
        padding: 42px 24px;
        text-align: center;
        background: #f8fafc;
        border: 1px dashed #dbe3ef;
        border-radius: 16px;
    }

    .approval-empty i {
        display: block;
        margin-bottom: 14px;
        font-size: 2.8rem;
        color: #cbd5e1;
    }

    .approval-empty h3 {
        margin: 0 0 8px;
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 800;
    }

    .approval-empty p {
        margin: 0 auto;
        max-width: 620px;
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.65;
    }

    @media (max-width: 991.98px) {
        .approval-kv strong {
            max-width: 55%;
        }
    }

    @media (max-width: 767.98px) {
        .approval-status-grid {
            grid-template-columns: 1fr;
        }

        .approval-kv {
            flex-direction: column;
            gap: 6px;
        }

        .approval-kv strong {
            max-width: 100%;
            text-align: left;
        }

        .approval-question-head,
        .approval-free-head {
            flex-direction: column;
        }
    }
</style>
