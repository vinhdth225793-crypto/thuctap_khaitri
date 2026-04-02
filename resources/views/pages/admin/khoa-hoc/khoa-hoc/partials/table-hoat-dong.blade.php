@php
    $groupedHoatDong = $data->getCollection()->groupBy(function($kh) {
        return $kh->nhomNganh->ten_nhom_nganh ?? 'Chưa phân loại';
    });
    $stt = ($data->currentPage() - 1) * $data->perPage() + 1;
@endphp

@forelse($groupedHoatDong as $tenNhomNganh => $items)
    <div class="px-4 py-2 bg-light border-bottom border-top d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-primary small text-uppercase">
            <i class="fas fa-layer-group me-2"></i> Nhóm ngành: {{ $tenNhomNganh }}
        </h6>
        <span class="badge bg-primary-soft text-primary rounded-pill smaller border border-primary">{{ $items->count() }} lớp học</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-white smaller text-muted text-uppercase">
                <tr>
                    <th class="ps-4 text-center" width="60">STT</th>
                    <th>Mã lớp</th>
                    <th>Tên lớp học</th>
                    <th class="text-center">Xác nhận GV</th>
                    <th class="text-center">Khai giảng</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="pe-4 text-center" width="{{ $tab === 'san_sang' ? '220' : '120' }}">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $kh)
                    <tr>
                        <td class="text-center ps-4 text-muted small">{{ $stt++ }}</td>
                        <td><code class="fw-bold text-primary">{{ $kh->ma_khoa_hoc }}</code></td>
                        <td>
                            <div class="fw-bold text-dark">{{ $kh->ten_khoa_hoc }}</div>
                            <div class="smaller text-muted italic">Mẫu: {{ $kh->khoaHocMau->ma_khoa_hoc ?? 'N/A' }}</div>
                            <div class="smaller text-muted mt-1">
                                Tiến độ học tập: <strong>{{ $kh->so_module_hoan_thanh }}/{{ $kh->moduleHocs->count() }}</strong> module
                                <span class="mx-1">•</span>
                                {{ $kh->tien_do_hoc_tap }}%
                            </div>
                        </td>
                        <td class="text-center">
                            @php
                                $total = $kh->tong_so_module;
                                $confirmed = $kh->module_xac_nhan_count ?? 0;
                                $percent = $total > 0 ? round(($confirmed / $total) * 100) : 0;
                                $badgeColor = $confirmed == $total ? 'success' : ($confirmed > 0 ? 'warning' : 'secondary');
                            @endphp
                            <div class="d-flex flex-column align-items-center">
                                <span class="badge bg-{{ $badgeColor }}-soft text-{{ $badgeColor }} border border-{{ $badgeColor }} mb-1">
                                    {{ $confirmed }}/{{ $total }} module
                                </span>
                                <div class="progress" style="height: 4px; width: 80px;">
                                    <div class="progress-bar bg-{{ $badgeColor }}" role="progressbar" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center small">
                            {{ $kh->ngay_khai_giang ? $kh->ngay_khai_giang->format('d/m/Y') : '—' }}
                        </td>
                        <td class="text-center">
                            <div class="d-flex flex-column align-items-center gap-1">
                                <span class="badge bg-{{ $kh->badge_trang_thai }}-soft text-{{ $kh->badge_trang_thai }} border border-{{ $kh->badge_trang_thai }} px-2 shadow-xs">
                                    {{ $kh->label_trang_thai_van_hanh }}
                                </span>
                                <span class="badge bg-{{ $kh->trang_thai_hoc_tap_badge }}-soft text-{{ $kh->trang_thai_hoc_tap_badge }} border border-{{ $kh->trang_thai_hoc_tap_badge }} px-2 shadow-xs">
                                    {{ $kh->trang_thai_hoc_tap_label }}
                                </span>
                            </div>
                        </td>
                        <td class="pe-4 text-center">
                            <div class="d-flex justify-content-center gap-1 flex-wrap">
                                <a href="{{ route('admin.khoa-hoc.hoc-vien.index', $kh->id) }}" class="btn btn-sm btn-outline-success action-btn" title="Quản lý học viên">
                                    <i class="fas fa-users"></i>
                                </a>
                                <a href="{{ route('admin.khoa-hoc.show', $kh->id) }}" class="btn btn-sm btn-outline-primary action-btn" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($tab === 'san_sang' && $kh->trang_thai_van_hanh === 'san_sang')
                                    <a href="{{ route('admin.khoa-hoc.show', $kh->id) }}#kich-hoat-khoa-hoc" class="btn btn-sm btn-primary fw-bold px-2" title="Kích hoạt khóa học">
                                        <i class="fas fa-play me-1"></i> Kích hoạt
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@empty
    <div class="text-center py-5 text-muted">
        <i class="fas fa-bolt fa-2x mb-2 d-block opacity-25"></i>
        Không có dữ liệu trong mục này.
    </div>
@endforelse

<div class="p-3 border-top d-flex justify-content-center">
    {{ $data->appends(['tab' => $tab, 'search' => $search])->links('pagination::bootstrap-5') }}
</div>
