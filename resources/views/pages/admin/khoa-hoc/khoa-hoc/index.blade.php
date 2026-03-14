@extends('layouts.app')

@section('title', 'Quản lý Khóa học')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold mb-0">
                <i class="fas fa-graduation-cap me-2 text-primary"></i>
                Quản lý Khóa học
            </h4>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.khoa-hoc.create') }}" class="btn btn-outline-primary fw-bold shadow-sm">
                <i class="fas fa-plus me-1"></i> Tạo khóa học mẫu
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Search Bar -->
    <div class="vip-card mb-4 border-0 shadow-sm">
        <div class="vip-card-body p-3">
            <form method="GET" action="{{ route('admin.khoa-hoc.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 vip-form-control" 
                               placeholder="Tìm theo tên, mã khóa học..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100 fw-bold">Tìm kiếm</button>
                </div>
                @if($search)
                    <div class="col-md-1">
                        <a href="{{ route('admin.khoa-hoc.index', ['tab' => $activeTab]) }}" class="btn btn-link text-muted small p-0 text-decoration-none">Xóa lọc</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Nav Tabs -->
    <ul class="nav nav-tabs border-bottom-0 mb-0" id="khoaHocTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'mau' ? 'active fw-bold' : 'text-muted' }}" 
                    id="mau-tab" data-bs-toggle="tab" data-bs-target="#mau" type="button" role="tab" data-tab="mau">
                <i class="fas fa-copy me-1"></i> 📋 Khóa học mẫu
                <span class="badge bg-info ms-1">{{ $khoaHocMau->total() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'hoat_dong' ? 'active fw-bold' : 'text-muted' }}" 
                    id="hoat_dong-tab" data-bs-toggle="tab" data-bs-target="#hoat_dong" type="button" role="tab" data-tab="hoat_dong">
                <i class="fas fa-bolt me-1"></i> ⚡ Đang hoạt động
                <span class="badge bg-primary ms-1">{{ $khoaHocHoatDong->total() }}</span>
            </button>
        </li>
    </ul>

    <div class="vip-card border-top-0 shadow-sm" style="border-top-left-radius: 0;">
        <div class="vip-card-body p-0">
            <div class="tab-content" id="khoaHocTabsContent">
                <!-- Tab 1: Khóa học mẫu -->
                <div class="tab-pane fade {{ $activeTab === 'mau' ? 'show active' : '' }}" id="mau" role="tabpanel">
                    @php
                        $groupedMau = $khoaHocMau->getCollection()->groupBy(function($kh) {
                            return $kh->monHoc->ten_mon_hoc ?? 'Chưa phân loại';
                        });
                        $sttMau = ($khoaHocMau->currentPage() - 1) * $khoaHocMau->perPage() + 1;
                    @endphp

                    @forelse($groupedMau as $tenMonHoc => $items)
                        <div class="px-4 py-2 bg-light border-bottom border-top d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-bold text-info small text-uppercase">
                                <i class="fas fa-book me-2"></i> Môn học: {{ $tenMonHoc }}
                            </h6>
                            <span class="badge bg-info rounded-pill smaller">{{ $items->count() }} khóa học mẫu</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-white smaller text-muted text-uppercase">
                                    <tr>
                                        <th class="ps-4 text-center" width="60">STT</th>
                                        <th>Mã mẫu</th>
                                        <th>Tên khóa học</th>
                                        <th class="text-center">Cấp độ</th>
                                        <th class="text-center">Số module</th>
                                        <th class="text-center">Đã mở</th>
                                        <th class="pe-4 text-center" width="220">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $kh)
                                        <tr>
                                            <td class="text-center ps-4 text-muted small">{{ $sttMau++ }}</td>
                                            <td><code class="fw-bold text-primary">{{ $kh->ma_khoa_hoc }}</code></td>
                                            <td><span class="fw-bold text-dark">{{ $kh->ten_khoa_hoc }}</span></td>
                                            <td class="text-center">
                                                @php
                                                    $capDo = [
                                                        'co_ban' => ['text' => 'Cơ bản', 'class' => 'success'],
                                                        'trung_binh' => ['text' => 'Trung bình', 'class' => 'warning text-dark'],
                                                        'nang_cao' => ['text' => 'Nâng cao', 'class' => 'danger'],
                                                    ];
                                                    $cd = $capDo[$kh->cap_do] ?? ['text' => 'N/A', 'class' => 'secondary'];
                                                @endphp
                                                <span class="badge bg-{{ $cd['class'] }} smaller">{{ $cd['text'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary rounded-pill px-2">{{ $kh->tong_so_module }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($kh->lop_da_mo_count > 0)
                                                    <span class="badge bg-info shadow-xs">{{ $kh->lop_da_mo_count }} lần</span>
                                                @else
                                                    <span class="badge bg-light text-muted border smaller">Chưa mở</span>
                                                @endif
                                            </td>
                                            <td class="pe-4 text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="{{ route('admin.khoa-hoc.mo-lop', $kh->id) }}" 
                                                       class="btn btn-sm btn-success action-btn" 
                                                       title="Mở lớp mới từ mẫu này">
                                                        <i class="fas fa-rocket"></i>
                                                    </a>
                                                    <a href="{{ route('admin.khoa-hoc.show', $kh->id) }}" 
                                                       class="btn btn-sm btn-primary action-btn" 
                                                       title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.khoa-hoc.edit', $kh->id) }}" 
                                                       class="btn btn-sm btn-warning text-white action-btn" 
                                                       title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.khoa-hoc.destroy', $kh->id) }}" method="POST" 
                                                          onsubmit="return confirm('Bạn chắc chắn muốn xóa khóa học mẫu này?')" class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger action-btn" title="Xóa">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                            Chưa có khóa học mẫu nào phù hợp.
                        </div>
                    @endforelse
                    <div class="p-3 border-top d-flex justify-content-center">
                        {{ $khoaHocMau->appends(['tab' => 'mau', 'search' => $search])->links('pagination::bootstrap-5') }}
                    </div>
                </div>

                <!-- Tab 2: Đang hoạt động -->
                <div class="tab-pane fade {{ $activeTab === 'hoat_dong' ? 'show active' : '' }}" id="hoat_dong" role="tabpanel">
                    @php
                        $groupedHoatDong = $khoaHocHoatDong->getCollection()->groupBy(function($kh) {
                            return $kh->monHoc->ten_mon_hoc ?? 'Chưa phân loại';
                        });
                        $sttHD = ($khoaHocHoatDong->currentPage() - 1) * $khoaHocHoatDong->perPage() + 1;
                    @endphp

                    @forelse($groupedHoatDong as $tenMonHoc => $items)
                        <div class="px-4 py-2 bg-light border-bottom border-top d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-bold text-primary small text-uppercase">
                                <i class="fas fa-book me-2"></i> Môn học: {{ $tenMonHoc }}
                            </h6>
                            <span class="badge bg-primary rounded-pill smaller">{{ $items->count() }} lớp đang dạy</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-white smaller text-muted text-uppercase">
                                    <tr>
                                        <th class="ps-4 text-center" width="60">STT</th>
                                        <th>Mã lớp</th>
                                        <th>Tên lớp học</th>
                                        <th class="text-center">Lần thứ</th>
                                        <th class="text-center">Ngày khai giảng</th>
                                        <th class="text-center">Trạng thái</th>
                                        <th class="pe-4 text-center" width="120">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $kh)
                                        <tr>
                                            <td class="text-center ps-4 text-muted small">{{ $sttHD++ }}</td>
                                            <td><code class="fw-bold text-primary">{{ $kh->ma_khoa_hoc }}</code></td>
                                            <td><span class="fw-bold text-dark">{{ $kh->ten_khoa_hoc }}</span></td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary">K{{ str_pad($kh->lan_mo_thu, 2, '0', STR_PAD_LEFT) }}</span>
                                            </td>
                                            <td class="text-center small">
                                                {{ $kh->ngay_khai_giang ? $kh->ngay_khai_giang->format('d/m/Y') : '—' }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $kh->badge_trang_thai }} px-2 shadow-xs">
                                                    {{ $kh->label_trang_thai_van_hanh }}
                                                </span>
                                            </td>
                                            <td class="pe-4 text-center">
                                                <a href="{{ route('admin.khoa-hoc.show', $kh->id) }}" class="btn btn-sm btn-primary px-3 shadow-sm" title="Chi tiết">
                                                    <i class="fas fa-eye me-1"></i> Xem
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-bolt fa-2x mb-2 d-block opacity-25"></i>
                            Hiện không có lớp học nào đang hoạt động.
                        </div>
                    @endforelse
                    <div class="p-3 border-top d-flex justify-content-center">
                        {{ $khoaHocHoatDong->appends(['tab' => 'hoat_dong', 'search' => $search])->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Handle tab switching in URL
        const tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabButtons.forEach(btn => {
            btn.addEventListener('shown.bs.tab', (e) => {
                const tab = e.target.getAttribute('data-tab');
                const url = new URL(window.location);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url);
                
                // Also update hidden inputs in search form
                document.querySelectorAll('input[name="tab"]').forEach(input => {
                    input.value = tab;
                });
            });
        });
    });
</script>

<style>
    .smaller { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .nav-tabs .nav-link { color: #6c757d; border-top: 3px solid transparent; border-bottom: 1px solid #dee2e6; }
    .nav-tabs .nav-link.active { color: #0d6efd; border-top-color: #0d6efd; border-bottom-color: transparent; background-color: #fff; }
    .nav-tabs .nav-link:hover:not(.active) { background-color: #f8f9fa; border-top-color: #eee; }
    .vip-form-control:focus { box-shadow: none; border-color: #0d6efd; }
</style>
@endsection
