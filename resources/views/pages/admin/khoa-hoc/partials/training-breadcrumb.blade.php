@php
    $icon = $icon ?? 'fas fa-compass';
    $current = $current ?? 'Trang hiện tại';
    $accent = $accent ?? '#2563eb';
    $soft = $soft ?? 'rgba(37, 99, 235, 0.12)';
    $chip = $chip ?? 'Danh sách';
    $note = $note ?? null;
@endphp

<div class="training-breadcrumb-card mb-4" style="--training-breadcrumb-accent: {{ $accent }}; --training-breadcrumb-soft: {{ $soft }};">
    <div class="training-breadcrumb-card__icon">
        <i class="{{ $icon }}"></i>
    </div>

    <div class="training-breadcrumb-card__content">
        <div class="training-breadcrumb-card__eyebrow">Dieu huong quan ly dao tao</div>
        <nav aria-label="breadcrumb">
            <ol class="training-breadcrumb-list mb-0">
                <li class="training-breadcrumb-list__item">
                    <a href="{{ route('admin.dashboard') }}">Admin</a>
                </li>
                <li class="training-breadcrumb-list__item">
                    <span>Quản lý đào tạo</span>
                </li>
                <li class="training-breadcrumb-list__item training-breadcrumb-list__item--active" aria-current="page">
                    {{ $current }}
                </li>
            </ol>
        </nav>
    </div>

    <div class="training-breadcrumb-card__summary">
        <span class="training-breadcrumb-card__chip">{{ $chip }}</span>
        @if($note)
            <div class="training-breadcrumb-card__note">{{ $note }}</div>
        @endif
    </div>
</div>

@once
    <style>
        .training-breadcrumb-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 18px 22px;
            border-radius: 18px;
            border: 1px solid rgba(148, 163, 184, 0.18);
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.95), transparent 34%),
                linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .training-breadcrumb-card::after {
            content: '';
            position: absolute;
            inset: auto -40px -50px auto;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, var(--training-breadcrumb-soft) 0%, transparent 68%);
            pointer-events: none;
        }

        .training-breadcrumb-card__icon {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--training-breadcrumb-soft);
            color: var(--training-breadcrumb-accent);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.75);
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .training-breadcrumb-card__content {
            min-width: 0;
            flex: 1;
        }

        .training-breadcrumb-card__eyebrow {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 8px;
        }

        .training-breadcrumb-list {
            list-style: none;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            padding: 0;
        }

        .training-breadcrumb-list__item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .training-breadcrumb-list__item:not(:last-child)::after {
            content: '\f054';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 0.68rem;
            color: #cbd5e1;
        }

        .training-breadcrumb-list__item a {
            color: #475569;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .training-breadcrumb-list__item a:hover {
            color: var(--training-breadcrumb-accent);
        }

        .training-breadcrumb-list__item--active {
            color: #0f172a;
        }

        .training-breadcrumb-card__summary {
            position: relative;
            z-index: 1;
            text-align: right;
            max-width: 290px;
            flex-shrink: 0;
        }

        .training-breadcrumb-card__chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: var(--training-breadcrumb-soft);
            color: var(--training-breadcrumb-accent);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .training-breadcrumb-card__note {
            margin-top: 10px;
            color: #64748b;
            font-size: 0.83rem;
            line-height: 1.5;
        }

        @media (max-width: 991.98px) {
            .training-breadcrumb-card {
                align-items: flex-start;
                flex-wrap: wrap;
            }

            .training-breadcrumb-card__summary {
                width: 100%;
                max-width: none;
                text-align: left;
                padding-left: 72px;
            }
        }

        @media (max-width: 575.98px) {
            .training-breadcrumb-card {
                padding: 16px;
                gap: 14px;
            }

            .training-breadcrumb-card__icon {
                width: 46px;
                height: 46px;
                border-radius: 14px;
            }

            .training-breadcrumb-card__summary {
                padding-left: 0;
            }

            .training-breadcrumb-list {
                gap: 6px;
            }

            .training-breadcrumb-list__item {
                font-size: 0.88rem;
            }
        }
    </style>
@endonce
