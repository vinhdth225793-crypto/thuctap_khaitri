@extends('layouts.app')

@section('title', 'Thông báo của tôi')

@section('content')
<div class="container-fluid admin-page-x tb-page">
    <div class="apx-welcome tb-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-bell"></i></div>
        <div class="apx-welcome-text">
            <div class="tb-tag-row">
                <span class="tb-loai-badge"><i class="fas fa-bell"></i> THÔNG BÁO HỆ THỐNG</span>
                <span class="tb-status-badge"><i class="fas fa-layer-group"></i> {{ $tongTatCa }} thông báo</span>
                @if($tongChua > 0)
                    <span class="tb-pending-badge"><i class="fas fa-circle"></i> {{ $tongChua }} chưa đọc</span>
                @endif
            </div>
            <h4>Hộp thư thông báo</h4>
            <p>
                <span><i class="fas fa-circle-check"></i> {{ $tongTatCa - $tongChua }} đã đọc</span>
                <span class="tb-sep">·</span>
                <span><i class="fas fa-bell"></i> Cập nhật từ tất cả nghiệp vụ liên quan đến tài khoản</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            @if($tongChua > 0)
                <form action="{{ route('thong-bao.mark-all-read') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-light text-primary fw-bold shadow-sm tb-mark-btn">
                        <i class="fas fa-check-double me-1"></i> Đánh dấu tất cả đã đọc
                    </button>
                </form>
            @endif
        </div>
    </div>

    @include('components.alert')

    <section class="apx-section">
        <header class="apx-section-head">
            <div class="apx-section-title">
                <span class="apx-section-num">1</span>
                <div>
                    <h2><i class="fas fa-list"></i> Danh sách thông báo</h2>
                    <p>Bấm vào thông báo để mở liên kết tương ứng và đánh dấu đã đọc.</p>
                </div>
            </div>
            <div class="apx-section-meta">
                <a href="{{ route('thong-bao.index') }}" class="tb-filter-pill {{ !$filter ? 'is-active' : '' }}">
                    Tất cả
                </a>
                <a href="{{ route('thong-bao.index', ['loc' => 'chua_doc']) }}" class="tb-filter-pill {{ $filter === 'chua_doc' ? 'is-active' : '' }}">
                    Chưa đọc <span class="tb-filter-count">{{ $tongChua }}</span>
                </a>
            </div>
        </header>

        <div class="tb-list-wrap">
            @forelse($thongBaos as $tb)
                <div class="tb-item {{ !$tb->da_doc ? 'is-unread' : '' }} level-{{ $tb->level }}">
                    <a href="{{ route('thong-bao.read', $tb->id) }}" class="tb-item-link">
                        <div class="tb-item-icon">
                            <i class="{{ $tb->icon_class }}"></i>
                        </div>
                        <div class="tb-item-body">
                            <div class="tb-item-head">
                                @if(!$tb->da_doc)
                                    <span class="tb-new-badge">MỚI</span>
                                @endif
                                <h6 class="tb-item-title">{{ $tb->tieu_de }}</h6>
                                <span class="tb-item-time">
                                    <i class="far fa-clock"></i> {{ $tb->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <p class="tb-item-text">{{ $tb->noi_dung }}</p>
                            <div class="tb-item-meta">
                                <span class="tb-type-tag">
                                    <i class="fas fa-tag"></i> {{ str_replace('_', ' ', $tb->loai) }}
                                </span>
                                @if($tb->url)
                                    <span class="tb-go-link">
                                        <i class="fas fa-arrow-right"></i> Mở liên kết
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                    <form action="{{ route('thong-bao.destroy', $tb->id) }}" method="POST" class="tb-delete-form"
                          onsubmit="return confirm('Xóa thông báo này?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="tb-delete-btn" title="Xóa">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            @empty
                <div class="tb-empty">
                    <div class="tb-empty-icon"><i class="fas fa-bell-slash"></i></div>
                    <h5>{{ $filter === 'chua_doc' ? 'Không có thông báo chưa đọc' : 'Bạn chưa có thông báo nào' }}</h5>
                    <p>Các thông báo từ hệ thống sẽ xuất hiện ở đây.</p>
                </div>
            @endforelse

            @if($thongBaos->hasPages())
                <div class="tb-pagination">{{ $thongBaos->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </section>
</div>

@include('pages.admin.partials._admin-page-styles')

<style>
    .tb-page .apx-section-head { background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; border-left-color: #dc2626; }
    .tb-page .apx-section-num { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }
    .tb-page .apx-section-title h2 i { color: #dc2626; }

    .tb-welcome { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #4361ee 100%) !important; box-shadow: 0 16px 36px rgba(29,78,216,0.22) !important; }
    .tb-tag-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .tb-loai-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(6px); color: #fff; font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; border-radius: 999px; }
    .tb-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: rgba(255,255,255,0.14); color: #fff; font-size: 0.72rem; font-weight: 700; border-radius: 999px; }
    .tb-pending-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 12px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #78350f; font-size: 0.72rem; font-weight: 800; border-radius: 999px; animation: tbPulse 1.6s ease-out infinite; }
    .tb-pending-badge i { font-size: 0.5rem; }
    @keyframes tbPulse { 0%,100%{box-shadow:0 0 0 0 rgba(251,191,36,0.6);} 50%{box-shadow:0 0 0 6px rgba(251,191,36,0);} }
    .apx-welcome.tb-welcome p { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 0.85rem; }
    .apx-welcome.tb-welcome p i { color: #fef3c7; margin-right: 4px; }
    .tb-sep { opacity: 0.5; }
    .tb-mark-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,0.18); }

    .tb-filter-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 12px;
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 0.78rem; font-weight: 700;
        border-radius: 999px;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .tb-filter-pill:hover { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
    .tb-filter-pill.is-active { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: #fff; border-color: #dc2626; }
    .tb-filter-count {
        display: inline-grid; place-items: center;
        min-width: 18px; padding: 0 6px;
        background: #f1f5f9; color: #64748b;
        font-size: 0.66rem; font-weight: 800; border-radius: 999px;
    }
    .tb-filter-pill.is-active .tb-filter-count { background: rgba(255,255,255,0.25); color: #fff; }

    .tb-list-wrap {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
    }

    .tb-item {
        display: flex;
        align-items: stretch;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.18s ease;
    }
    .tb-item:last-child { border-bottom: 0; }
    .tb-item:hover { background: #fafafa; }
    .tb-item.is-unread { background: linear-gradient(90deg, #fef2f2 0%, #ffffff 50%); }
    .tb-item.is-unread:hover { background: linear-gradient(90deg, #fee2e2 0%, #fafafa 50%); }
    .tb-item.is-unread.level-info    { background: linear-gradient(90deg, #eff6ff 0%, #ffffff 50%); }
    .tb-item.is-unread.level-success { background: linear-gradient(90deg, #f0fdf4 0%, #ffffff 50%); }
    .tb-item.is-unread.level-warning { background: linear-gradient(90deg, #fffbeb 0%, #ffffff 50%); }
    .tb-item.is-unread.level-danger  { background: linear-gradient(90deg, #fef2f2 0%, #ffffff 50%); }

    .tb-item-link {
        flex: 1;
        display: flex;
        gap: 14px;
        padding: 16px 18px;
        text-decoration: none;
        color: inherit;
        min-width: 0;
    }
    .tb-item-icon {
        flex-shrink: 0;
        width: 42px; height: 42px;
        border-radius: 12px;
        display: grid; place-items: center;
        font-size: 1.05rem;
    }
    .level-info    .tb-item-icon { background: #dbeafe; color: #1d4ed8; }
    .level-success .tb-item-icon { background: #dcfce7; color: #16a34a; }
    .level-warning .tb-item-icon { background: #fef3c7; color: #b45309; }
    .level-danger  .tb-item-icon { background: #fee2e2; color: #dc2626; }

    .tb-item-body { flex: 1; min-width: 0; }
    .tb-item-head {
        display: flex; align-items: center; gap: 8px;
        margin-bottom: 5px;
        flex-wrap: wrap;
    }
    .tb-new-badge {
        padding: 2px 8px;
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #fff;
        font-size: 0.6rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        border-radius: 4px;
        flex-shrink: 0;
    }
    .tb-item-title {
        font-size: 0.92rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        flex: 1;
        min-width: 0;
    }
    .tb-item-time {
        font-size: 0.74rem;
        color: #94a3b8;
        font-weight: 600;
        flex-shrink: 0;
    }
    .tb-item-time i { color: #1d4ed8; margin-right: 3px; font-size: 0.66rem; }
    .tb-item-text {
        font-size: 0.85rem;
        color: #475569;
        line-height: 1.55;
        margin: 0 0 8px;
        white-space: pre-line;
    }
    .tb-item-meta {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    .tb-type-tag {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.66rem;
        font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .tb-type-tag i { font-size: 0.58rem; opacity: 0.7; }
    .tb-go-link {
        font-size: 0.74rem;
        color: #1d4ed8;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .tb-go-link i { font-size: 0.66rem; }

    .tb-delete-form {
        display: flex;
        align-items: center;
        padding-right: 14px;
    }
    .tb-delete-btn {
        width: 32px; height: 32px;
        border-radius: 8px;
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #94a3b8;
        cursor: pointer;
        display: grid; place-items: center;
        font-size: 0.78rem;
        transition: all 0.18s ease;
    }
    .tb-delete-btn:hover {
        background: #dc2626;
        color: #fff;
        border-color: #dc2626;
    }

    .tb-pagination {
        padding: 14px 18px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex; justify-content: center;
    }
    .tb-pagination nav { margin: 0; }

    .tb-empty {
        padding: 70px 30px;
        text-align: center;
    }
    .tb-empty-icon {
        width: 88px; height: 88px;
        margin: 0 auto 18px;
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border-radius: 50%;
        display: grid; place-items: center;
        color: #dc2626;
        font-size: 2rem;
    }
    .tb-empty h5 { font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .tb-empty p { font-size: 0.9rem; color: #94a3b8; max-width: 460px; margin: 0 auto; line-height: 1.55; }
</style>
@endsection
