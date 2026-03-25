@extends('layouts.app', ['title' => 'Phê duyệt bài kiểm tra'])

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Phê duyệt bài kiểm tra</h2>
            <p class="text-muted mb-0">Admin kiểm soát chất lượng đề thi trước khi phát hành cho học viên.</p>
        </div>
    </div>

    <div class="card vip-card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tìm kiếm</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tiêu đề, mô tả...">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Trạng thái duyệt</label>
                    <select name="trang_thai_duyet" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach(['nhap' => 'Nháp', 'cho_duyet' => 'Chờ duyệt', 'da_duyet' => 'Đã duyệt', 'tu_choi' => 'Từ chối'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('trang_thai_duyet') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Trạng thái phát hành</label>
                    <select name="trang_thai_phat_hanh" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach(['nhap' => 'Nháp', 'phat_hanh' => 'Phát hành', 'dong' => 'Đóng'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('trang_thai_phat_hanh') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Lọc dữ liệu</button>
                    <a href="{{ route('admin.kiem-tra-online.phe-duyet.index') }}" class="btn btn-light border">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card vip-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Đề thi</th>
                        <th>Khóa / module</th>
                        <th>GV tạo</th>
                        <th>Loại</th>
                        <th>Câu hỏi</th>
                        <th>Duyệt</th>
                        <th>Phát hành</th>
                        <th class="text-end">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($baiKiemTras as $baiKiemTra)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $baiKiemTra->tieu_de }}</div>
                                <div class="small text-muted">{{ $baiKiemTra->thoi_gian_lam_bai }} phút</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Không rõ khóa học' }}</div>
                                <div class="small text-muted">{{ $baiKiemTra->moduleHoc->ten_module ?? 'Đề cuối khóa / dùng chung' }}</div>
                            </td>
                            <td>{{ $baiKiemTra->nguoiTao->ho_ten ?? 'Không rõ' }}</td>
                            <td>{{ $baiKiemTra->loai_noi_dung_label }}</td>
                            <td>{{ $baiKiemTra->chi_tiet_cau_hois_count }}</td>
                            <td>{{ $baiKiemTra->trang_thai_duyet_label }}</td>
                            <td>{{ $baiKiemTra->trang_thai_phat_hanh_label }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.kiem-tra-online.phe-duyet.show', $baiKiemTra->id) }}" class="btn btn-sm btn-outline-primary">Xem</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Chưa có bài kiểm tra nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($baiKiemTras->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $baiKiemTras->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
