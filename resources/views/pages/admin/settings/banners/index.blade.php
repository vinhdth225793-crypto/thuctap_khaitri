@extends('layouts.app')

@section('title', 'Quản lý Banner')

@push('styles')
<style>
    .banner-sort-row {
        transition: background-color 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
    }

    .banner-sort-row.is-dragging {
        opacity: 0.45;
    }

    .banner-sort-row.drag-over {
        background: #f8fbff;
        box-shadow: inset 4px 0 0 #0d6efd;
    }

    .drag-handle {
        border-radius: 8px;
        cursor: grab;
        padding: 0.35rem 0.55rem;
        user-select: none;
    }

    .drag-handle:active {
        cursor: grabbing;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center py-3">
                    <h4 class="mb-0 fw-bold">
                        <i class="fas fa-images me-2"></i> Quản lý Banner
                    </h4>
                    <div class="d-flex gap-2">
                        <span class="badge bg-white text-warning d-flex align-items-center px-3 rounded-pill shadow-sm">
                            <i class="fas fa-eye me-2"></i>{{ $banners->where('trang_thai', true)->count() }} Banner đang bật
                        </span>
                        <a href="{{ route('admin.settings.banners.create') }}" class="btn btn-light btn-sm fw-bold px-3 rounded-pill shadow-sm">
                            <i class="fas fa-plus me-1 text-warning"></i> Thêm banner mới
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="alert alert-info border-0 rounded-0 mb-0 px-4 py-3">
                        <div class="fw-bold mb-1"><i class="fas fa-info-circle me-2"></i>Quy ước hiển thị banner trang chủ</div>
                        <div class="small mb-0">
                            Thứ tự <strong>0</strong> là ảnh thẻ đầu tiên ở đầu trang chủ. Thứ tự từ <strong>1 trở đi</strong> là banner hiển thị dạng slide ngay bên dưới thẻ đầu.
                        </div>
                        <div class="small mt-2 fw-semibold text-primary">
                            <i class="fas fa-grip-vertical me-2"></i>Kéo biểu tượng ở cột thứ tự để đổi vị trí banner, hệ thống sẽ tự lưu ngay.
                        </div>
                    </div>

                    @if($banners->where('trang_thai', true)->count() > 0)
                    <!-- LIVE PREVIEW SECTION (Nhỏ gọn hơn) -->
                    <div class="bg-light border-bottom p-3">
                        <h6 class="small fw-bold text-muted mb-3"><i class="fas fa-play-circle me-2"></i>Xem trước Banner trên trang chủ</h6>
                        <div id="bannerPreview" class="carousel slide rounded-3 overflow-hidden shadow-sm mx-auto" data-bs-ride="carousel" style="max-width: 900px;">
                            <div class="carousel-inner">
                                @foreach($banners->where('trang_thai', true) as $index => $banner)
                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                    <img src="{{ asset($banner->duong_dan_anh) }}" class="d-block w-100" style="height: 300px; object-fit: cover;" alt="{{ $banner->tieu_de }}">
                                    <div class="carousel-caption d-none d-md-block text-start bg-dark bg-opacity-50 p-2 rounded" style="left: 5%; bottom: 15px; max-width: 60%;">
                                        <h5 class="fw-bold mb-1">{{ $banner->tieu_de }}</h5>
                                        <div class="small opacity-75">{!! Str::limit(strip_tags($banner->mo_ta), 100) !!}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#bannerPreview" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                            <button class="carousel-control-next" type="button" data-bs-target="#bannerPreview" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                        </div>
                    </div>
                    @endif

                    <!-- BANNER TABLE LIST -->
                    <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-white">
                        <div class="small text-muted">
                            <i class="fas fa-arrows-alt-v me-2"></i>Kéo thả các dòng banner để sắp xếp nhanh.
                        </div>
                        <div id="banner-order-status" class="small fw-bold text-muted"></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light border-bottom text-muted">
                                <tr>
                                    <th class="ps-4" width="110">Thứ tự</th>
                                    <th width="150">Hình ảnh</th>
                                    <th>Thông tin Banner</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="pe-4 text-end" width="180">Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="banners-list">
                                @forelse($banners as $banner)
                                <tr data-id="{{ $banner->id }}" class="banner-sort-row">
                                    <td class="ps-4 text-muted handle">
                                        <span class="drag-handle d-inline-flex align-items-center gap-2" title="Kéo để đổi thứ tự" draggable="true">
                                            <i class="fas fa-grip-vertical opacity-50"></i>
                                            <span class="order-number">{{ $banner->thu_tu }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="rounded-3 overflow-hidden shadow-sm border" style="width: 120px; height: 60px; cursor: pointer;" onclick="viewImage('{{ asset($banner->duong_dan_anh) }}')">
                                            <img src="{{ asset($banner->duong_dan_anh) }}" class="w-100 h-100" style="object-fit: cover;">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $banner->tieu_de }}</div>
                                        <div class="mt-1">
                                            @if((int) $banner->thu_tu === 0)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle banner-position-badge">Ảnh thẻ đầu trang</span>
                                            @else
                                                <span class="badge bg-info-subtle text-info border border-info-subtle banner-position-badge">Slide banner #{{ $banner->thu_tu }}</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted text-truncate" style="max-width: 350px;">{!! strip_tags($banner->mo_ta) !!}</div>
                                        @if($banner->link)
                                            <div class="small"><a href="{{ $banner->link }}" target="_blank" class="text-primary"><i class="fas fa-link me-1"></i>Link</a></div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input toggle-status" type="checkbox" data-id="{{ $banner->id }}" 
                                                   {{ $banner->trang_thai ? 'checked' : '' }} style="cursor: pointer; width: 2.5em; height: 1.25em;">
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <button onclick="window.location.reload()" class="btn btn-sm btn-outline-secondary rounded-circle me-1" title="Tải lại trang">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <a href="{{ route('admin.settings.banners.edit', $banner->id) }}" class="btn btn-sm btn-outline-warning rounded-circle me-1" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-circle delete-banner" data-id="{{ $banner->id }}" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="opacity-25 mb-2"><i class="fas fa-images fa-4x"></i></div>
                                        <p class="text-muted fw-bold">Chưa có banner nào được tạo.</p>
                                        <a href="{{ route('admin.settings.banners.create') }}" class="btn btn-sm btn-primary px-4 rounded-pill">Thêm ngay</a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-3">
                    <a href="{{ route('admin.settings') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i> Quay lại Tổng quan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xem ảnh lớn -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-body p-0 text-center position-relative">
                <img id="fullImage" src="" class="img-fluid rounded shadow-lg border border-3 border-white">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // View image modal
    function viewImage(url) {
        document.getElementById('fullImage').src = url;
        new bootstrap.Modal(document.getElementById('imageModal')).show();
    }

    // Toggle status AJAX - Sử dụng Event Delegation để bắt sự kiện chính xác hơn
    const bannersList = document.getElementById('banners-list');
    if (bannersList) {
        bannersList.addEventListener('change', function(e) {
            if (e.target.classList.contains('toggle-status')) {
                const id = e.target.dataset.id;
                const isChecked = e.target.checked;
                
                // Sử dụng template literal cho ID nhưng route() cho base path
                const url = `{{ route('admin.settings.banners.toggle-status', ':id') }}`.replace(':id', id);
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        // Tùy chọn: hiển thị thông báo toast nếu có
                    } else {
                        e.target.checked = !isChecked;
                        alert('Có lỗi xảy ra: ' + (data.message || 'Không thể cập nhật trạng thái'));
                    }
                })
                .catch(err => {
                    e.target.checked = !isChecked;
                    console.error('Fetch error:', err);
                    alert('Lỗi kết nối máy chủ');
                });
            }
        });

        // Delete banner AJAX
        bannersList.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.delete-banner');
            if (deleteBtn) {
                if(confirm('Bạn có chắc chắn muốn xóa banner này không?')) {
                    const id = deleteBtn.dataset.id;
                    const row = deleteBtn.closest('tr');
                    
                    const url = `{{ route('admin.settings.banners.destroy', ':id') }}`.replace(':id', id);
                    
                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            row.style.transition = 'all 0.3s ease';
                            row.style.opacity = '0';
                            setTimeout(() => {
                                row.remove();
                                persistBannerOrder();
                            }, 300);
                        } else {
                            alert('Không thể xóa: ' + data.message);
                        }
                    })
                    .catch(err => {
                        console.error('Delete error:', err);
                        alert('Lỗi khi xóa banner');
                    });
                }
            }
        });
    }

    // Drag & Drop Sorting - dùng HTML5 Drag and Drop để không phụ thuộc CDN.
    const orderStatus = document.getElementById('banner-order-status');
    const sortableRows = () => Array.from(bannersList?.querySelectorAll('tr[data-id]') || []);
    let draggingRow = null;
    let savedOrder = sortableRows().map(row => row.dataset.id).join(',');

    function setOrderStatus(message, className = 'text-muted') {
        if (!orderStatus) return;

        orderStatus.className = `small fw-bold ${className}`;
        orderStatus.textContent = message;
    }

    function updateOrderLabels() {
        sortableRows().forEach((row, index) => {
            const number = row.querySelector('.order-number');
            const badge = row.querySelector('.banner-position-badge');

            if (number) {
                number.textContent = index;
            }

            if (!badge) {
                return;
            }

            if (index === 0) {
                badge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle banner-position-badge';
                badge.textContent = 'Ảnh thẻ đầu trang';
            } else {
                badge.className = 'badge bg-info-subtle text-info border border-info-subtle banner-position-badge';
                badge.textContent = `Slide banner #${index}`;
            }
        });
    }

    function rowAfterPointer(container, y) {
        return sortableRows()
            .filter(row => row !== draggingRow)
            .reduce((closest, row) => {
                const box = row.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;

                if (offset < 0 && offset > closest.offset) {
                    return { offset, element: row };
                }

                return closest;
            }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
    }

    function restoreSavedOrder() {
        const rowsById = new Map(sortableRows().map(row => [row.dataset.id, row]));

        savedOrder.split(',')
            .map(id => rowsById.get(id))
            .filter(Boolean)
            .forEach(row => bannersList.appendChild(row));

        updateOrderLabels();
    }

    function persistBannerOrder() {
        const order = sortableRows().map(row => row.dataset.id);
        const nextOrder = order.join(',');

        updateOrderLabels();

        if (!order.length || nextOrder === savedOrder) {
            return;
        }

        setOrderStatus('Đang lưu thứ tự...', 'text-primary');

        fetch(`{{ route('admin.settings.banners.update-order') }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ order })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Không thể cập nhật thứ tự');
            }

            savedOrder = nextOrder;
            setOrderStatus('Đã lưu thứ tự', 'text-success');
            setTimeout(() => {
                if (orderStatus?.textContent === 'Đã lưu thứ tự') {
                    setOrderStatus('');
                }
            }, 1800);
        })
        .catch(err => {
            console.error('Sort error:', err);
            restoreSavedOrder();
            setOrderStatus('Lưu thứ tự thất bại', 'text-danger');
            alert('Lỗi cập nhật thứ tự: ' + err.message);
        });
    }

    if (bannersList && sortableRows().length > 0) {
        bannersList.addEventListener('dragstart', function(e) {
            const handle = e.target.closest('.drag-handle');
            const row = e.target.closest('tr[data-id]');

            if (!handle || !row) {
                e.preventDefault();
                return;
            }

            draggingRow = row;
            row.classList.add('is-dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', row.dataset.id);
        });

        bannersList.addEventListener('dragover', function(e) {
            if (!draggingRow) {
                return;
            }

            e.preventDefault();
            const afterRow = rowAfterPointer(bannersList, e.clientY);

            sortableRows().forEach(row => row.classList.remove('drag-over'));

            if (afterRow) {
                afterRow.classList.add('drag-over');
                bannersList.insertBefore(draggingRow, afterRow);
            } else {
                bannersList.appendChild(draggingRow);
            }
        });

        bannersList.addEventListener('dragend', function() {
            if (!draggingRow) {
                return;
            }

            draggingRow.classList.remove('is-dragging');
            sortableRows().forEach(row => row.classList.remove('drag-over'));
            draggingRow = null;
            persistBannerOrder();
        });
    }
</script>
@endpush
