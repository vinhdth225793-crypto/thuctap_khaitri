<div class="col-12 course-card mb-4" data-mode="list">
    <div class="vip-card border-0 shadow-sm overflow-hidden">
        <div class="vip-card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="rounded-pill bg-primary-soft text-primary px-3 py-1 fw-bold smaller me-3 border border-primary">
                    {{ $khoaHoc->ma_khoa_hoc }}
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">{{ $khoaHoc->ten_khoa_hoc }}</h5>
                    <div class="smaller text-muted mt-1">
                        <i class="fas fa-layer-group me-1"></i> Ngành: {{ $khoaHoc->nhomNganh->ten_nhom_nganh ?? 'N/A' }}
                        <span class="mx-2 d-none d-md-inline">|</span>
                        <i class="fas fa-calendar-check me-1"></i> Khai giảng: {{ $khoaHoc->ngay_khai_giang?->format('d/m/Y') ?? 'Chưa định ngày' }}
                    </div>
                    <div class="smaller text-muted mt-1">
                        Tiến độ khóa học: <strong>{{ $khoaHoc->so_module_hoan_thanh }}/{{ $khoaHoc->moduleHocs->count() }}</strong> module
                        <span class="mx-2 d-none d-md-inline">|</span>
                        {{ $khoaHoc->tien_do_hoc_tap }}%
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-{{ $khoaHoc->badge_trang_thai }}-soft text-{{ $khoaHoc->badge_trang_thai }} border border-{{ $khoaHoc->badge_trang_thai }} px-3 shadow-xs d-none d-md-inline-block">
                    {{ $khoaHoc->label_trang_thai_van_hanh }}
                </span>
                <span class="badge bg-{{ $khoaHoc->trang_thai_hoc_tap_badge }}-soft text-{{ $khoaHoc->trang_thai_hoc_tap_badge }} border border-{{ $khoaHoc->trang_thai_hoc_tap_badge }} px-3 shadow-xs d-none d-md-inline-block">
                    {{ $khoaHoc->trang_thai_hoc_tap_label }}
                </span>
                {{-- Nút xem nhanh ở chế độ List --}}
                <div class="list-actions">
                    <a href="{{ route('giang-vien.khoa-hoc.show', $khoaHoc->moduleHocs->first()->phanCongGiangViens->first()->id) }}" class="btn btn-sm btn-primary fw-bold px-3 rounded-pill shadow-xs">
                        Vào dạy <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- PHẦN CHI TIẾT MODULE (Ẩn mặc định ở chế độ Gọn) --}}
        <div class="vip-card-body p-0 detail-section d-none">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light smaller text-muted text-uppercase">
                        <tr>
                            <th class="ps-4" width="60">STT</th>
                            <th>Tên bài dạy (Module)</th>
                            <th class="text-center">Tiến độ buổi</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="pe-4 text-center" width="180">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($khoaHoc->moduleHocs as $module)
                            @php 
                                $pc = $module->phanCongGiangViens->first();
                                $rowBg = match($pc->trang_thai) {
                                    'cho_xac_nhan' => 'bg-warning-soft',
                                    'da_nhan'      => 'bg-success-soft',
                                    'tu_choi'      => 'bg-danger-soft',
                                    default        => ''
                                };
                            @endphp
                            <tr class="{{ $rowBg }}">
                                <td class="ps-4 text-muted small fw-bold">#{{ $module->thu_tu_module }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $module->ten_module }}</div>
                                    <div class="smaller text-muted italic">{{ Str::limit($module->mo_ta, 80) }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="badge bg-light text-dark border">{{ $module->so_buoi_hoan_thanh }}/{{ $module->so_buoi_hop_le }} buổi</span>
                                        <span class="smaller text-muted">Sắp tới: {{ $module->learning_progress_snapshot['upcoming_schedules'] }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        @if($pc->trang_thai === 'cho_xac_nhan')
                                            <span class="badge bg-warning-soft text-warning border border-warning px-3">Chờ xác nhận</span>
                                        @elseif($pc->trang_thai === 'da_nhan')
                                            <span class="badge bg-success-soft text-success border border-success px-3">Đã nhận</span>
                                        @else
                                            <span class="badge bg-danger-soft text-danger border border-danger px-3">Từ chối</span>
                                        @endif
                                        <span class="badge bg-{{ $module->trang_thai_hoc_tap_badge }}-soft text-{{ $module->trang_thai_hoc_tap_badge }} border border-{{ $module->trang_thai_hoc_tap_badge }} px-3">
                                            {{ $module->trang_thai_hoc_tap_label }}
                                        </span>
                                    </div>
                                </td>
                                <td class="pe-4 text-center">
                                    @if($pc->trang_thai === 'cho_xac_nhan')
                                        <form action="{{ route('giang-vien.khoa-hoc.xac-nhan', $pc->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="hanh_dong" value="da_nhan">
                                            <button type="submit" class="btn btn-xs btn-success fw-bold px-2">Nhận</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('giang-vien.khoa-hoc.show', $pc->id) }}" class="btn btn-xs btn-outline-primary fw-bold px-2">Chi tiết</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>