@php
    $groupedMau = $data->getCollection()->groupBy(function($kh) {
        return $kh->nhomNganh->ten_nhom_nganh ?? 'Chưa phân loại';
    });
    $stt = ($data->currentPage() - 1) * $data->perPage() + 1;
@endphp

@forelse($groupedMau as $tenNhomNganh => $items)
    <div class="px-4 py-2 bg-light border-bottom border-top d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-info small text-uppercase">
            <i class="fas fa-layer-group me-2"></i> Nhóm ngành: {{ $tenNhomNganh }}
        </h6>
        <span class="badge bg-info-soft text-info rounded-pill smaller border border-info">{{ $items->count() }} khóa học mẫu</span>
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
                        <td class="text-center ps-4 text-muted small">{{ $stt++ }}</td>
                        <td><code class="fw-bold text-primary">{{ $kh->ma_khoa_hoc }}</code></td>
                        <td><span class="fw-bold text-dark">{{ $kh->ten_khoa_hoc }}</span></td>
                        <td class="text-center">
                            <span class="badge bg-{{ $kh->badge_cap_do ?? 'secondary' }}-soft text-dark border smaller">{{ $kh->cap_do_label ?? $kh->cap_do }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary-soft text-secondary rounded-pill px-2 border border-secondary">{{ $kh->tong_so_module }}</span>
                        </td>
                        <td class="text-center">
                            @if($kh->lop_da_mo_count > 0)
                                <span class="badge bg-info-soft text-info border border-info shadow-xs">{{ $kh->lop_da_mo_count }} lần</span>
                            @else
                                <span class="badge bg-light text-muted border smaller">Chưa mở</span>
                            @endif
                        </td>
                        <td class="pe-4 text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('admin.khoa-hoc.mo-lop', $kh->id) }}" class="btn btn-sm btn-outline-success action-btn" title="Mở lớp mới">
                                    <i class="fas fa-rocket"></i>
                                </a>
                                <a href="{{ route('admin.khoa-hoc.show', $kh->id) }}" class="btn btn-sm btn-outline-primary action-btn" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.khoa-hoc.edit', $kh->id) }}" class="btn btn-sm btn-outline-warning action-btn" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.khoa-hoc.destroy', $kh->id) }}" method="POST" onsubmit="return confirm('Xóa mẫu này?')" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger action-btn" title="Xóa">
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
        Không tìm thấy khóa học mẫu nào.
    </div>
@endforelse

<div class="p-3 border-top d-flex justify-content-center">
    {{ $data->appends(['tab' => $tab, 'search' => $search])->links('pagination::bootstrap-5') }}
</div>
