@extends('layouts.app')

@section('title', 'Ngân hàng câu hỏi')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="text-muted small mb-1">
                        <i class="fas fa-home me-1"></i> Admin > Kiểm tra Online > Ngân hàng câu hỏi
                    </div>
                    <h3 class="fw-bold mb-0">Ngân hàng câu hỏi</h3>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.kiem-tra-online.cau-hoi.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Thêm câu hỏi
                    </a>
                    <a href="{{ route('admin.kiem-tra-online.cau-hoi.template') }}" class="btn btn-outline-success">
                        <i class="fas fa-file-download me-1"></i> Tải file mẫu
                    </a>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fas fa-file-import me-1"></i> Import CSV
                    </button>
                </div>
            </div>
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

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('admin.kiem-tra-online.cau-hoi.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Từ khóa</label>
                    <input type="text" name="search" class="form-control" placeholder="Mã câu hỏi, nội dung câu hỏi..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Khóa học</label>
                    <select name="khoa_hoc_id" id="filter-khoa-hoc" class="form-select">
                        <option value="">Tất cả khóa học</option>
                        @foreach($khoaHocs as $khoaHoc)
                            <option value="{{ $khoaHoc->id }}" @selected((string) request('khoa_hoc_id') === (string) $khoaHoc->id)>
                                [{{ $khoaHoc->ma_khoa_hoc }}] {{ $khoaHoc->ten_khoa_hoc }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Module</label>
                    <select name="module_hoc_id" id="filter-module-hoc" class="form-select">
                        <option value="">Tất cả module</option>
                        @foreach($modules as $module)
                            <option value="{{ $module->id }}" data-course-id="{{ $module->khoa_hoc_id }}" @selected((string) request('module_hoc_id') === (string) $module->id)>
                                [{{ $module->ma_module }}] {{ $module->ten_module }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Loại câu hỏi</label>
                    <select name="loai_cau_hoi" class="form-select">
                        <option value="">Tất cả loại</option>
                        @foreach($questionTypeOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('loai_cau_hoi') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Kiểu đáp án</label>
                    <select name="kieu_dap_an" class="form-select">
                        <option value="">Tất cả kiểu</option>
                        @foreach($answerModeOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('kieu_dap_an') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Mức độ</label>
                    <select name="muc_do" class="form-select">
                        <option value="">Tất cả mức độ</option>
                        @foreach($difficultyOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('muc_do') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select name="trang_thai" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('trang_thai') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Tái sử dụng</label>
                    <select name="co_the_tai_su_dung" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="1" @selected(request('co_the_tai_su_dung') === '1')>Cho phép</option>
                        <option value="0" @selected(request('co_the_tai_su_dung') === '0')>Không</option>
                    </select>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Lọc dữ liệu
                    </button>
                    <a href="{{ route('admin.kiem-tra-online.cau-hoi.index') }}" class="btn btn-light border">
                        Đặt lại
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="70" class="text-center">STT</th>
                            <th width="140">Mã</th>
                            <th>Nội dung câu hỏi</th>
                            <th width="220">Phạm vi</th>
                            <th width="170">Phân loại</th>
                            <th width="120">Mức độ</th>
                            <th width="120">Trạng thái</th>
                            <th width="120">Tái sử dụng</th>
                            <th width="220">Đáp án đúng</th>
                            <th width="220" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cauHois as $index => $item)
                            <tr>
                                <td class="text-center">{{ $cauHois->firstItem() + $index }}</td>
                                <td>
                                    <div class="fw-semibold text-primary">{{ $item->ma_cau_hoi }}</div>
                                    <div class="small text-muted">{{ $item->created_at?->format('d/m/Y') }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark mb-1">{{ \Illuminate\Support\Str::limit(strip_tags($item->noi_dung), 180) }}</div>
                                    <div class="small text-muted">Người tạo: {{ $item->nguoiTao->ho_ten ?? 'Không rõ' }}</div>
                                    @if(!$item->supports_current_exam_builder)
                                        <div class="small text-warning mt-1">Câu hỏi này đã lưu trong ngân hàng nhưng chưa mở cho flow ra đề hiện tại.</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $item->khoaHoc->ten_khoa_hoc ?? 'Không rõ khóa học' }}</div>
                                    <div class="small text-muted">{{ $item->moduleHoc->ten_module ?? 'Dùng chung toàn khóa' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border mb-1">{{ $item->loai_cau_hoi_label }}</span>
                                    @if($item->loai_cau_hoi !== \App\Models\NganHangCauHoi::LOAI_TU_LUAN)
                                        <div class="small text-muted">{{ $item->kieu_dap_an_label }}</div>
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $item->muc_do_label }}</span></td>
                                <td><span class="badge bg-{{ $item->trang_thai_color }}">{{ $item->trang_thai_label }}</span></td>
                                <td>
                                    @if($item->co_the_tai_su_dung)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Cho phép</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Tắt</span>
                                    @endif
                                </td>
                                <td><div class="small text-dark">{{ \Illuminate\Support\Str::limit($item->correct_answer_summary, 120) }}</div></td>
                                <td>
                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        <a href="{{ route('admin.kiem-tra-online.cau-hoi.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.kiem-tra-online.cau-hoi.toggle-status', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="{{ $item->trang_thai === \App\Models\NganHangCauHoi::TRANG_THAI_SAN_SANG ? 'Tạm ẩn' : 'Hiện lại' }}">
                                                <i class="fas fa-eye{{ $item->trang_thai === \App\Models\NganHangCauHoi::TRANG_THAI_SAN_SANG ? '-slash' : '' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.kiem-tra-online.cau-hoi.toggle-reusable', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="{{ $item->co_the_tai_su_dung ? 'Tắt tái sử dụng' : 'Bật tái sử dụng' }}">
                                                <i class="fas fa-recycle"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.kiem-tra-online.cau-hoi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa câu hỏi này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">Chưa có câu hỏi nào phù hợp với bộ lọc hiện tại.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($cauHois->hasPages())
                <div class="mt-4">{{ $cauHois->links() }}</div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.kiem-tra-online.cau-hoi.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import câu hỏi từ CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Khóa học</label>
                        <select name="khoa_hoc_id" class="form-select" required>
                            <option value="">Chọn khóa học</option>
                            @foreach($khoaHocs as $khoaHoc)
                                <option value="{{ $khoaHoc->id }}">[{{ $khoaHoc->ma_khoa_hoc }}] {{ $khoaHoc->ten_khoa_hoc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">File CSV</label>
                        <input type="file" name="file_import" class="form-control" accept=".csv" required>
                        <div class="form-text mt-2">Hệ thống sẽ xem trước dữ liệu trước khi lưu chính thức.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-eye me-1"></i> Xem trước
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const courseSelect = document.getElementById('filter-khoa-hoc');
        const moduleSelect = document.getElementById('filter-module-hoc');

        if (!courseSelect || !moduleSelect) {
            return;
        }

        const syncModules = () => {
            const selectedCourse = courseSelect.value;

            Array.from(moduleSelect.options).forEach((option) => {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }

                option.hidden = selectedCourse !== '' && option.dataset.courseId !== selectedCourse;
            });

            const selectedOption = moduleSelect.options[moduleSelect.selectedIndex];
            if (selectedOption && selectedOption.hidden) {
                moduleSelect.value = '';
            }
        };

        courseSelect.addEventListener('change', syncModules);
        syncModules();
    });
</script>
@endpush
@endsection
