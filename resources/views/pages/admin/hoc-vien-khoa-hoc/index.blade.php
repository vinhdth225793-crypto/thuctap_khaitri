@extends('layouts.app')
@section('title', 'Học viên — ' . $khoaHoc->ten_khoa_hoc)
@section('content')
<div class="container-fluid">

  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang chủ</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.index') }}">Khóa học</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}">{{ $khoaHoc->ma_khoa_hoc }}</a></li>
      <li class="breadcrumb-item active">Học viên</li>
    </ol>
  </nav>

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1">
        <i class="fas fa-users me-2 text-success"></i>
        Quản lý học viên — {{ $khoaHoc->ten_khoa_hoc }}
      </h4>
      <small class="text-muted">
        Mã: {{ $khoaHoc->ma_khoa_hoc }} |
        Trạng thái:
        @if($khoaHoc->trang_thai_van_hanh === 'dang_day')
          <span class="badge bg-success">Đang dạy</span>
        @else
          <span class="badge bg-secondary">{{ $khoaHoc->trang_thai_van_hanh }}</span>
        @endif
      </small>
    </div>
    <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->id) }}" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i> Quay lại khóa học
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  @endif

  <div class="row">
    <!-- Danh sách học viên hiện tại -->
    <div class="col-lg-8">
      <div class="vip-card mb-4 border-0 shadow-sm">
        <div class="vip-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
          <h5 class="vip-card-title small fw-bold text-uppercase mb-0">
            <i class="fas fa-list me-2"></i>
            Danh sách học viên ({{ $khoaHoc->hocVienKhoaHocs->count() }})
          </h5>
        </div>
        <div class="vip-card-body p-0">
          @if($khoaHoc->hocVienKhoaHocs->isEmpty())
            <div class="text-center py-5 text-muted">
              <i class="fas fa-user-slash fa-2x mb-2 opacity-25"></i>
              <p class="small">Chưa có học viên nào trong khóa học này.</p>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0 small">
                <thead class="bg-light smaller text-muted text-uppercase">
                  <tr>
                    <th class="ps-4 text-center" width="60">STT</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th class="text-center">Ngày tham gia</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="pe-4 text-center" width="120">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($khoaHoc->hocVienKhoaHocs as $i => $bghv)
                  <tr>
                    <td class="text-center ps-4 text-muted small">{{ $i + 1 }}</td>
                    <td>
                      <div class="fw-bold text-dark">{{ $bghv->hocVien->ho_ten ?? 'N/A' }}</div>
                      <div class="smaller text-muted">{{ $bghv->hocVien->so_dien_thoai ?? '' }}</div>
                    </td>
                    <td>{{ $bghv->hocVien->email ?? '' }}</td>
                    <td class="text-center">{{ $bghv->ngay_tham_gia?->format('d/m/Y') ?? '─' }}</td>
                    <td class="text-center">
                      <span class="badge {{ $bghv->trang_thai_badge }} shadow-xs">
                        {{ $bghv->trang_thai_label }}
                      </span>
                    </td>
                    <td class="pe-4 text-center">
                      <div class="d-flex justify-content-center gap-1">
                        <!-- Nút đổi trạng thái -->
                        <button class="btn btn-sm btn-outline-primary action-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#modalTrangThai{{ $bghv->id }}"
                                title="Đổi trạng thái">
                          <i class="fas fa-edit"></i>
                        </button>
                        <!-- Nút xóa -->
                        @if($khoaHoc->trang_thai_van_hanh === 'dang_day')
                        <form action="{{ route('admin.khoa-hoc.hoc-vien.destroy', [$khoaHoc->id, $bghv->id]) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Xóa học viên này khỏi khóa học?')">
                          @csrf @method('DELETE')
                          <button class="btn btn-sm btn-outline-danger action-btn" title="Xóa khỏi khóa"><i class="fas fa-times"></i></button>
                        </form>
                        @endif
                      </div>
                    </td>
                  </tr>

                  <!-- Modal đổi trạng thái -->
                  <div class="modal fade shadow" id="modalTrangThai{{ $bghv->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                      <div class="modal-content border-0">
                        <div class="modal-header bg-primary text-white border-0">
                          <h6 class="modal-title fw-bold">Trạng thái — {{ $bghv->hocVien->ho_ten ?? '' }}</h6>
                          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('admin.khoa-hoc.hoc-vien.update-trang-thai', [$khoaHoc->id, $bghv->id]) }}"
                              method="POST">
                          @csrf @method('PUT')
                          <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Chọn trạng thái *</label>
                                <select name="trang_thai" class="form-select vip-form-control" required>
                                  <option value="dang_hoc"   {{ $bghv->trang_thai==='dang_hoc'?'selected':'' }}>Đang học</option>
                                  <option value="hoan_thanh" {{ $bghv->trang_thai==='hoan_thanh'?'selected':'' }}>Hoàn thành</option>
                                  <option value="ngung_hoc"  {{ $bghv->trang_thai==='ngung_hoc'?'selected':'' }}>Ngừng học</option>
                                </select>
                            </div>
                            <div class="mb-0">
                                <label class="form-label small fw-bold text-dark">Ghi chú</label>
                                <textarea name="ghi_chu" class="form-control vip-form-control" rows="3" placeholder="Lý do đổi trạng thái...">{{ $bghv->ghi_chu }}</textarea>
                            </div>
                          </div>
                          <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                            <button type="button" class="btn btn-light px-4 fw-bold small" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary px-4 fw-bold small shadow-sm">Lưu thay đổi</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Form thêm học viên (sidebar phải) -->
    <div class="col-lg-4">
      @if($khoaHoc->trang_thai_van_hanh === 'dang_day')
      <div class="vip-card border-0 shadow-sm">
        <div class="vip-card-header bg-white border-bottom py-3">
          <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-success">
            <i class="fas fa-user-plus me-2"></i> Thêm học viên
          </h5>
        </div>
        <div class="vip-card-body p-4">
          <form action="{{ route('admin.khoa-hoc.hoc-vien.store', $khoaHoc->id) }}" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label small fw-bold text-dark">Chọn học viên chưa tham gia *</label>
              <select name="hoc_vien_ids[]" class="form-select vip-form-control shadow-xs" multiple size="10" required>
                @foreach($hocVienChuaThamGia as $hv)
                  <option value="{{ $hv->ma_nguoi_dung }}">
                    {{ $hv->ho_ten }} ({{ $hv->email }})
                  </option>
                @endforeach
              </select>
              <div class="form-text smaller italic mt-2"><i class="fas fa-info-circle me-1"></i> Giữ Ctrl (hoặc Cmd) để chọn nhiều học viên cùng lúc.</div>
              @error('hoc_vien_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="mb-4">
              <label class="form-label small fw-bold text-dark">Ghi chú đầu vào</label>
              <textarea name="ghi_chu" class="form-control vip-form-control" rows="3" placeholder="VD: Học viên cũ, Ưu đãi học phí..."></textarea>
            </div>
            <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
              <i class="fas fa-plus me-2"></i> THÊM VÀO KHÓA HỌC
            </button>
          </form>
        </div>
      </div>
      @else
      <div class="alert alert-warning border-0 shadow-sm p-4">
        <div class="d-flex gap-3">
            <i class="fas fa-lock fa-2x opacity-50 mt-1"></i>
            <div>
                <h6 class="fw-bold mb-1">Tính năng bị khóa</h6>
                <p class="small mb-0">Chỉ có thể thêm hoặc xóa học viên khi khóa học ở trạng thái <strong>Đang giảng dạy</strong>.</p>
            </div>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

<style>
    .smaller { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; padding: 0; }
    .vip-form-control:focus { box-shadow: none; border-color: #0d6efd; }
    .italic { font-style: italic; }
</style>
@endsection
