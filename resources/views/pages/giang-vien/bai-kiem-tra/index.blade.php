@extends('layouts.app', ['title' => 'Danh sách bài kiểm tra'])

@section('content')
@php
    $scopeMap = [
        'module' => ['label' => 'Theo module', 'class' => 'info'],
        'buoi_hoc' => ['label' => 'Theo buổi học', 'class' => 'primary'],
        'cuoi_khoa' => ['label' => 'Cuối khóa', 'class' => 'dark'],
    ];

    $approvalMap = [
        'nhap' => ['label' => 'Nháp', 'class' => 'secondary'],
        'cho_duyet' => ['label' => 'Chờ duyệt', 'class' => 'warning'],
        'da_duyet' => ['label' => 'Đã duyệt', 'class' => 'success'],
        'tu_choi' => ['label' => 'Từ chối', 'class' => 'danger'],
    ];

    $publishMap = [
        'nhap' => ['label' => 'Chưa phát hành', 'class' => 'secondary'],
        'phat_hanh' => ['label' => 'Đang phát hành', 'class' => 'success'],
        'dong' => ['label' => 'Đã đóng', 'class' => 'dark'],
    ];
@endphp

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Danh sách bài kiểm tra</h2>
            <p class="text-muted mb-0">Theo dõi toàn bộ đề đang có, trạng thái duyệt và đường vào cấu hình đề của bạn.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('giang-vien.khoa-hoc') }}" class="btn btn-outline-primary">
                <i class="fas fa-plus-circle me-1"></i> Tạo đề từ lộ trình
            </a>
            <a href="{{ route('giang-vien.cham-diem.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-marker me-1"></i> Chấm tự luận
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="fw-bold mb-1">Đường vào tạo đề mới</div>
        <div class="small mb-0">
            Bạn vẫn có thể tạo đề mới từ <strong>Lộ trình giảng dạy</strong> -> <strong>Vào dạy</strong> -> <strong>Tạo bài kiểm tra</strong>.
            Trang này dùng để quản lý, cấu hình lại và chấm các đề đã có.
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-bold mb-2">Tổng đề</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="display-6 fw-bold mb-0">{{ $stats['tong'] }}</div>
                        <i class="fas fa-file-signature text-primary fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-bold mb-2">Cần cấu hình</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="display-6 fw-bold mb-0">{{ $stats['nhap'] }}</div>
                        <i class="fas fa-sliders-h text-secondary fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-bold mb-2">Chờ duyệt</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="display-6 fw-bold mb-0">{{ $stats['cho_duyet'] }}</div>
                        <i class="fas fa-hourglass-half text-warning fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-bold mb-2">Đang phát hành</div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="display-6 fw-bold mb-0">{{ $stats['phat_hanh'] }}</div>
                        <i class="fas fa-broadcast-tower text-success fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('giang-vien.bai-kiem-tra.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label fw-bold">Tìm kiếm</label>
                    <input type="text" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Tên đề, mã khóa học, tên module...">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label fw-bold">Phạm vi</label>
                    <select name="pham_vi" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="module" @selected($filters['pham_vi'] === 'module')>Theo module</option>
                        <option value="buoi_hoc" @selected($filters['pham_vi'] === 'buoi_hoc')>Theo buổi học</option>
                        <option value="cuoi_khoa" @selected($filters['pham_vi'] === 'cuoi_khoa')>Cuối khóa</option>
                    </select>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label fw-bold">Trạng thái duyệt</label>
                    <select name="trang_thai_duyet" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($approvalMap as $value => $meta)
                            <option value="{{ $value }}" @selected($filters['trang_thai_duyet'] === $value)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label fw-bold">Phát hành</label>
                    <select name="trang_thai_phat_hanh" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($publishMap as $value => $meta)
                            <option value="{{ $value }}" @selected($filters['trang_thai_phat_hanh'] === $value)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Lọc danh sách
                    </button>
                    <a href="{{ route('giang-vien.bai-kiem-tra.index') }}" class="btn btn-outline-secondary">
                        Đặt lại
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="fw-bold mb-0">Các đề đang có</h5>
            <span class="text-muted small">{{ $baiKiemTras->total() }} đề</span>
        </div>
        <div class="card-body p-0">
            @if($baiKiemTras->isEmpty())
                <div class="p-5 text-center text-muted">
                    <i class="fas fa-folder-open fa-3x mb-3 d-block opacity-25"></i>
                    <div class="fw-bold mb-2">Chưa có bài kiểm tra phù hợp</div>
                    <div class="small mb-3">Hãy tạo đề mới từ lớp học hoặc bỏ bớt điều kiện lọc để xem thêm đề.</div>
                    <a href="{{ route('giang-vien.khoa-hoc') }}" class="btn btn-primary btn-sm">
                        Đi đến lộ trình giảng dạy
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Đề kiểm tra</th>
                                <th>Phạm vi</th>
                                <th>Trạng thái</th>
                                <th>Dữ liệu đề</th>
                                <th>Lịch mở / đóng</th>
                                <th class="text-end pe-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($baiKiemTras as $baiKiemTra)
                                @php
                                    $scope = $scopeMap[$baiKiemTra->pham_vi] ?? ['label' => 'Khác', 'class' => 'secondary'];
                                    $approval = $approvalMap[$baiKiemTra->trang_thai_duyet] ?? ['label' => 'Không rõ', 'class' => 'secondary'];
                                    $publish = $publishMap[$baiKiemTra->trang_thai_phat_hanh] ?? ['label' => 'Không rõ', 'class' => 'secondary'];
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $baiKiemTra->tieu_de }}</div>
                                        <div class="small text-muted">{{ $baiKiemTra->khoaHoc->ma_khoa_hoc ?? 'KH' }} - {{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Không rõ khóa học' }}</div>
                                        <div class="small text-muted">
                                            @if($baiKiemTra->moduleHoc)
                                                {{ $baiKiemTra->moduleHoc->ma_module }} - {{ $baiKiemTra->moduleHoc->ten_module }}
                                            @elseif($baiKiemTra->lichHoc)
                                                Buổi {{ $baiKiemTra->lichHoc->buoi_so }} - {{ optional($baiKiemTra->lichHoc->ngay_hoc)->format('d/m/Y') }}
                                            @else
                                                Đề dùng chung toàn khóa
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $scope['class'] }}">{{ $scope['label'] }}</span>
                                        <div class="small text-muted mt-2">Số lần làm: {{ $baiKiemTra->so_lan_duoc_lam }}</div>
                                    </td>
                                    <td>
                                        <div class="mb-2">
                                            <span class="badge text-bg-{{ $approval['class'] }}">{{ $approval['label'] }}</span>
                                        </div>
                                        <span class="badge text-bg-{{ $publish['class'] }}">{{ $publish['label'] }}</span>
                                    </td>
                                    <td>
                                        <div class="small"><strong>{{ $baiKiemTra->chi_tiet_cau_hois_count }}</strong> câu hỏi</div>
                                        <div class="small"><strong>{{ number_format((float) $baiKiemTra->tong_diem, 2) }}</strong> điểm</div>
                                        <div class="small text-muted">{{ $baiKiemTra->bai_lams_count }} lượt làm bài</div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            Mở:
                                            <strong>{{ optional($baiKiemTra->ngay_mo)->format('d/m/Y H:i') ?? 'Chưa đặt' }}</strong>
                                        </div>
                                        <div class="small">
                                            Đóng:
                                            <strong>{{ optional($baiKiemTra->ngay_dong)->format('d/m/Y H:i') ?? 'Chưa đặt' }}</strong>
                                        </div>
                                        <div class="small text-muted mt-1">Cập nhật: {{ optional($baiKiemTra->updated_at)->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                                            <a href="{{ route('giang-vien.bai-kiem-tra.edit', $baiKiemTra->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-sliders-h me-1"></i> Cấu hình
                                            </a>
                                            @if($baiKiemTra->trang_thai_duyet === 'da_duyet' && $baiKiemTra->trang_thai_phat_hanh !== 'phat_hanh')
                                                <form action="{{ route('giang-vien.bai-kiem-tra.publish', $baiKiemTra->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Phát hành đề kiểm tra này cho học viên làm bài?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="fas fa-paper-plane me-1"></i> Phát hành
                                                    </button>
                                                </form>
                                            @endif
                                            @if($baiKiemTra->has_essay_questions && $baiKiemTra->bai_lams_count > 0)
                                                <a href="{{ route('giang-vien.cham-diem.index') }}" class="btn btn-sm btn-outline-secondary">
                                                    Chấm bài
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if($baiKiemTras->hasPages())
            <div class="card-footer bg-white">
                {{ $baiKiemTras->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
