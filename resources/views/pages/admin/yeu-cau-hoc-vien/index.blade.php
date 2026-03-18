@extends('layouts.app')

@section('title', 'Quản lý yêu cầu thay đổi học viên')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold text-dark"><i class="fas fa-user-edit me-2"></i> Yêu cầu từ Giảng viên</h3>
            <p class="text-muted">Danh sách các yêu cầu thêm, xóa hoặc cập nhật học viên từ giảng viên.</p>
        </div>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light smaller text-muted text-uppercase">
                        <tr>
                            <th class="ps-4">Thời gian</th>
                            <th>Giảng viên</th>
                            <th>Khóa học</th>
                            <th>Loại yêu cầu</th>
                            <th>Nội dung yêu cầu</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="pe-4 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($yeuCaus as $yc)
                            <tr>
                                <td class="ps-4 small">{{ $yc->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $yc->giangVien->ho_ten }}</div>
                                    <code class="smaller text-muted">#{{ $yc->giangVien->id }}</code>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $yc->khoaHoc->ten_khoa_hoc }}">
                                        {{ $yc->khoaHoc->ten_khoa_hoc }}
                                    </div>
                                    <code class="smaller">{{ $yc->khoaHoc->ma_khoa_hoc }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $yc->loai_yeu_cau === 'them' ? 'success' : ($yc->loai_yeu_cau === 'xoa' ? 'danger' : 'warning') }}-soft text-dark smaller">
                                        {{ $yc->loai_label }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small">
                                        @php
                                            $data = is_array($yc->du_lieu_yeu_cau) ? $yc->du_lieu_yeu_cau : json_decode($yc->du_lieu_yeu_cau, true);
                                        @endphp
                                        @if($yc->loai_yeu_cau === 'them')
                                            <strong>HV:</strong> {{ $data['ten'] ?? 'N/A' }} ({{ $data['email'] ?? 'N/A' }})
                                        @else
                                            <strong>Mã HV:</strong> #{{ $data['id'] ?? 'N/A' }}
                                        @endif
                                        <div class="text-muted italic mt-1">Lý do: {{ $yc->ly_do }}</div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $yc->trang_thai === 'cho_duyet' ? 'warning' : ($yc->trang_thai === 'da_duyet' ? 'success' : 'danger') }} rounded-pill">
                                        {{ $yc->trang_thai === 'cho_duyet' ? 'Chờ duyệt' : ($yc->trang_thai === 'da_duyet' ? 'Đã duyệt' : 'Từ chối') }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    @if($yc->trang_thai === 'cho_duyet')
                                        <button class="btn btn-sm btn-primary fw-bold px-3" data-bs-toggle="modal" data-bs-target="#modalApprove{{ $yc->id }}">
                                            Xử lý
                                        </button>

                                        {{-- Modal Xử lý --}}
                                        <div class="modal fade shadow" id="modalApprove{{ $yc->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content border-0 text-start">
                                                    <div class="modal-header bg-dark text-white border-0">
                                                        <h5 class="modal-title fw-bold">Xử lý yêu cầu #{{ $yc->id }}</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('admin.yeu-cau-hoc-vien.xac-nhan', $yc->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body p-4">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">Hành động *</label>
                                                                <select name="hanh_dong" class="form-select" required>
                                                                    <option value="da_duyet">Chấp nhận yêu cầu</option>
                                                                    <option value="tu_choi">Từ chối yêu cầu</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-0">
                                                                <label class="form-label small fw-bold">Phản hồi của Admin</label>
                                                                <textarea name="phan_hoi" class="form-control" rows="3" placeholder="Nhập lý do từ chối hoặc lời nhắn cho giảng viên..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                                                            <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                                                            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">XÁC NHẬN</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="smaller text-muted">
                                            Duyệt bởi: {{ $yc->admin->ho_ten ?? 'Admin' }}<br>
                                            {{ $yc->thoi_gian_duyet?->format('d/m H:i') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted italic">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                    Hiện chưa có yêu cầu nào cần xử lý.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .smaller { font-size: 0.75rem; }
    .italic { font-style: italic; }
</style>
@endsection
