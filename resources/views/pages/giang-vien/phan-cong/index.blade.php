@extends('layouts.app')

@section('title', 'Xác nhận phân công giảng dạy')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i> Xác nhận phân công giảng dạy</h3>
            <p class="text-muted small">Xem và xác nhận các module bạn được phân công phụ trách.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
        </div>
    @endif

    @php
        $choXacNhan = $phanCongs->where('trang_thai', 'cho_xac_nhan');
        $daNhan     = $phanCongs->where('trang_thai', 'da_nhan');
        $tuChoi     = $phanCongs->where('trang_thai', 'tu_choi');
    @endphp

    <div class="vip-card">
        <div class="vip-card-header p-0 border-bottom">
            <ul class="nav nav-tabs border-bottom-0" id="phanCongTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold px-4 py-3" id="cho-xn-tab" data-bs-toggle="tab" data-bs-target="#cho-xn" type="button" role="tab">
                        Chờ xác nhận 
                        @if($choXacNhan->count() > 0)
                            <span class="badge bg-danger ms-1">{{ $choXacNhan->count() }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold px-4 py-3" id="da-nhan-tab" data-bs-toggle="tab" data-bs-target="#da-nhan" type="button" role="tab">
                        Đã xác nhận
                        <span class="badge bg-secondary ms-1">{{ $daNhan->count() }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold px-4 py-3" id="tu-choi-tab" data-bs-toggle="tab" data-bs-target="#tu-choi" type="button" role="tab">
                        Đã từ chối
                        <span class="badge bg-secondary ms-1">{{ $tuChoi->count() }}</span>
                    </button>
                </li>
            </ul>
        </div>
        <div class="vip-card-body p-0">
            <div class="tab-content" id="phanCongTabsContent">
                <!-- Tab: Chờ xác nhận -->
                <div class="tab-pane fade show active" id="cho-xn" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light smaller">
                                <tr>
                                    <th class="text-center" width="50">#</th>
                                    <th>Khóa học</th>
                                    <th>Module</th>
                                    <th class="text-center">Khai giảng dự kiến</th>
                                    <th class="text-center">Ngày phân công</th>
                                    <th class="text-center" width="300">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($choXacNhan as $index => $pc)
                                    <tr>
                                        <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $pc->moduleHoc->khoaHoc->ten_khoa_hoc }}</div>
                                            <span class="badge bg-light text-dark border smaller">{{ $pc->moduleHoc->khoaHoc->ma_khoa_hoc }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $pc->moduleHoc->ten_module }}</div>
                                            <code class="smaller text-muted">{{ $pc->moduleHoc->ma_module }}</code>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-bold text-dark">{{ $pc->moduleHoc->khoaHoc->ngay_khai_giang ? $pc->moduleHoc->khoaHoc->ngay_khai_giang->format('d/m/Y') : '—' }}</span>
                                        </td>
                                        <td class="text-center small text-muted">
                                            {{ $pc->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="p-3">
                                            <form action="{{ route('giang-vien.khoa-hoc.xac-nhan', $pc->id) }}" method="POST">
                                                @csrf
                                                <textarea name="ghi_chu" class="form-control form-control-sm mb-2" rows="1" placeholder="Ghi chú (nếu có)..."></textarea>
                                                <div class="d-flex gap-2">
                                                    <button type="submit" name="hanh_dong" value="da_nhan" class="btn btn-success btn-sm w-50 fw-bold" onclick="return confirm('Xác nhận dạy module này?')">
                                                        <i class="fas fa-check me-1"></i> Xác nhận dạy
                                                    </button>
                                                    <button type="submit" name="hanh_dong" value="tu_choi" class="btn btn-outline-danger btn-sm w-50 fw-bold" onclick="return confirm('Bạn chắc chắn từ chối phân công này?')">
                                                        <i class="fas fa-times me-1"></i> Từ chối
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-5 text-muted italic">Hiện tại không có phân công nào đang chờ xác nhận.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Đã xác nhận -->
                <div class="tab-pane fade" id="da-nhan" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light smaller">
                                <tr>
                                    <th class="text-center" width="50">#</th>
                                    <th>Khóa học</th>
                                    <th>Module</th>
                                    <th class="text-center">Ngày khai giảng</th>
                                    <th class="text-center">Ngày xác nhận</th>
                                    <th class="text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($daNhan as $index => $pc)
                                    <tr>
                                        <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                        <td>{{ $pc->moduleHoc->khoaHoc->ten_khoa_hoc }}</td>
                                        <td class="fw-bold">{{ $pc->moduleHoc->ten_module }}</td>
                                        <td class="text-center">{{ $pc->moduleHoc->khoaHoc->ngay_khai_giang ? $pc->moduleHoc->khoaHoc->ngay_khai_giang->format('d/m/Y') : '—' }}</td>
                                        <td class="text-center small text-muted">{{ $pc->updated_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('giang-vien.khoa-hoc.show', $pc->id) }}" class="btn btn-sm btn-outline-primary border-0" title="Xem chi tiết">
                                                    <i class="fas fa-eye me-1"></i> Chi tiết
                                                </a>
                                                <span class="badge bg-success shadow-sm px-3"><i class="fas fa-check-circle me-1"></i> Đã xác nhận</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-5 text-muted italic">Bạn chưa xác nhận module nào.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Đã từ chối -->
                <div class="tab-pane fade" id="tu-choi" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light smaller">
                                <tr>
                                    <th class="text-center" width="50">#</th>
                                    <th>Khóa học</th>
                                    <th>Module</th>
                                    <th>Lý do từ chối</th>
                                    <th class="text-center">Ngày xử lý</th>
                                    <th class="text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tuChoi as $index => $pc)
                                    <tr>
                                        <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                        <td>{{ $pc->moduleHoc->khoaHoc->ten_khoa_hoc }}</td>
                                        <td>{{ $pc->moduleHoc->ten_module }}</td>
                                        <td class="small italic text-danger">{{ $pc->ghi_chu ?: '(Không để lại lý do)' }}</td>
                                        <td class="text-center small text-muted">{{ $pc->updated_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-danger shadow-sm px-3"><i class="fas fa-times-circle me-1"></i> Đã từ chối</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-5 text-muted italic">Chưa có phân công nào bị từ chối.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .nav-tabs .nav-link { color: #6c757d; border-top: 3px solid transparent; }
    .nav-tabs .nav-link.active { color: #0d6efd; border-top-color: #0d6efd; background: #fff; }
    .smaller { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
</style>
@endsection
