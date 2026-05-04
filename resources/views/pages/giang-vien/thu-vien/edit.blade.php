@extends('layouts.app')

@section('title', 'Chỉnh sửa tài nguyên')

@section('content')
@php
    $typeOptions = [
        'video' => 'Video bài giảng',
        'pdf' => 'Tài liệu PDF',
        'word' => 'File Word',
        'powerpoint' => 'File PowerPoint',
        'excel' => 'File Excel',
        'image' => 'Hình ảnh',
        'archive' => 'File nén',
        'link_ngoai' => 'Liên kết ngoài',
        'tai_lieu_khac' => 'Tài liệu khác',
    ];

    $scopeOptions = [
        'ca_nhan' => 'Cá nhân',
        'khoa_hoc' => 'Trong khóa học',
        'cong_khai' => 'Công khai hệ thống',
    ];

    $approvalMeta = [
        'nhap' => ['label' => 'Nháp', 'class' => 'is-secondary'],
        'cho_duyet' => ['label' => 'Chờ duyệt', 'class' => 'is-warning'],
        'da_duyet' => ['label' => 'Đã duyệt', 'class' => 'is-success'],
        'can_chinh_sua' => ['label' => 'Cần chỉnh sửa', 'class' => 'is-info'],
        'tu_choi' => ['label' => 'Từ chối', 'class' => 'is-danger'],
    ];

    $scopeMeta = [
        'ca_nhan' => ['label' => 'Cá nhân', 'class' => 'is-slate'],
        'khoa_hoc' => ['label' => 'Trong khóa học', 'class' => 'is-blue'],
        'cong_khai' => ['label' => 'Công khai hệ thống', 'class' => 'is-green'],
    ];

    $approvalKey = $taiNguyen->trang_thai_duyet ?: 'nhap';
    $approval = $approvalMeta[$approvalKey] ?? ['label' => 'Khác', 'class' => 'is-secondary'];

    $scopeKey = $taiNguyen->pham_vi_su_dung ?: 'ca_nhan';
    $scope = $scopeMeta[$scopeKey] ?? ['label' => 'Khác', 'class' => 'is-slate'];

    $processing = match($taiNguyen->trang_thai_xu_ly) {
        'san_sang' => ['label' => 'Sẵn sàng', 'class' => 'is-success'],
        'dang_xu_ly' => ['label' => 'Đang xử lý', 'class' => 'is-primary'],
        'cho_xu_ly' => ['label' => 'Chờ xử lý', 'class' => 'is-warning'],
        'loi_xu_ly' => ['label' => 'Lỗi xử lý', 'class' => 'is-danger'],
        default => ['label' => 'Không cần xử lý', 'class' => 'is-secondary'],
    };

    $iconColor = match($taiNguyen->loai_color) {
        'primary' => '#1d4ed8',
        'success' => '#16a34a',
        'warning' => '#d97706',
        'danger' => '#dc2626',
        'info' => '#0891b2',
        'dark' => '#1e293b',
        default => '#64748b',
    };

    $iconSoft = match($taiNguyen->loai_color) {
        'primary' => 'rgba(29, 78, 216, 0.12)',
        'success' => 'rgba(22, 163, 74, 0.12)',
        'warning' => 'rgba(217, 119, 6, 0.12)',
        'danger' => 'rgba(220, 38, 38, 0.12)',
        'info' => 'rgba(8, 145, 178, 0.12)',
        'dark' => 'rgba(30, 41, 59, 0.12)',
        default => 'rgba(100, 116, 139, 0.12)',
    };

    $sizeLabel = null;
    if ($taiNguyen->file_size) {
        if ($taiNguyen->file_size >= 1048576) {
            $sizeLabel = number_format($taiNguyen->file_size / 1048576, 1) . ' MB';
        } elseif ($taiNguyen->file_size >= 1024) {
            $sizeLabel = number_format($taiNguyen->file_size / 1024, 0) . ' KB';
        } else {
            $sizeLabel = $taiNguyen->file_size . ' B';
        }
    }
@endphp

<div class="container-fluid admin-page-x tv-page">
    <div class="apx-welcome tv-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-pen-to-square"></i></div>
        <div class="apx-welcome-text">
            <div class="tv-tag-row">
                <span class="tv-library-badge">
                    <i class="fas fa-wrench"></i> CHỈNH SỬA TÀI NGUYÊN
                </span>
                <span class="tv-status-pill {{ $approval['class'] }}">{{ $approval['label'] }}</span>
                <span class="tv-status-pill {{ $scope['class'] }}">{{ $scope['label'] }}</span>
                @if($taiNguyen->trang_thai_xu_ly && $taiNguyen->trang_thai_xu_ly !== 'khong_ap_dung')
                    <span class="tv-status-pill {{ $processing['class'] }}">{{ $processing['label'] }}</span>
                @endif
            </div>
            <h4>{{ $taiNguyen->tieu_de }}</h4>
            <p>
                <span><i class="fas {{ $taiNguyen->loai_icon }}"></i> {{ $taiNguyen->loai_label }}</span>
                <span class="tv-sep">·</span>
                <span><i class="fas fa-database"></i> {{ $taiNguyen->nguon_hien_thi_label }}</span>
                <span class="tv-sep">·</span>
                <span><i class="far fa-calendar-plus"></i> {{ $taiNguyen->created_at->format('d/m/Y H:i') }}</span>
                <span class="tv-sep">·</span>
                <span><i class="far fa-clock"></i> Cập nhật {{ $taiNguyen->updated_at->format('d/m/Y H:i') }}</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('giang-vien.thu-vien.index') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Về thư viện</span>
            </a>
            @if($taiNguyen->file_url)
                <a href="{{ $taiNguyen->file_url }}" target="_blank" class="btn btn-light text-primary fw-bold shadow-sm">
                    <i class="fas fa-eye me-1"></i> Xem hiện tại
                </a>
            @endif
        </div>
    </div>

    @include('components.alert')

    <form action="{{ route('giang-vien.thu-vien.update', $taiNguyen->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">
                <section class="apx-section">
                    <header class="apx-section-head">
                        <div class="apx-section-title">
                            <span class="apx-section-num">1</span>
                            <div>
                                <h2><i class="fas fa-pen-ruler"></i> Cập nhật thông tin</h2>
                                <p>Chỉnh sửa tiêu đề, loại tài nguyên, phạm vi sử dụng và mô tả.</p>
                            </div>
                        </div>
                    </header>

                    <div class="tv-form-card">
                        <div class="mb-3">
                            <label class="tv-field-label"><i class="fas fa-heading"></i> Tiêu đề tài nguyên <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="tieu_de"
                                   class="form-control @error('tieu_de') is-invalid @enderror"
                                   value="{{ old('tieu_de', $taiNguyen->tieu_de) }}"
                                   required>
                            @error('tieu_de') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="tv-field-label"><i class="fas fa-shapes"></i> Loại tài nguyên <span class="text-danger">*</span></label>
                                <select name="loai_tai_nguyen" class="form-select @error('loai_tai_nguyen') is-invalid @enderror" required>
                                    @foreach($typeOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('loai_tai_nguyen', $taiNguyen->loai_tai_nguyen) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('loai_tai_nguyen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="tv-field-label"><i class="fas fa-users-cog"></i> Phạm vi sử dụng <span class="text-danger">*</span></label>
                                <select name="pham_vi_su_dung" class="form-select @error('pham_vi_su_dung') is-invalid @enderror" required>
                                    @foreach($scopeOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('pham_vi_su_dung', $taiNguyen->pham_vi_su_dung) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('pham_vi_su_dung') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="tv-field-label"><i class="fas fa-align-left"></i> Mô tả</label>
                            <textarea name="mo_ta"
                                      class="form-control @error('mo_ta') is-invalid @enderror"
                                      rows="5">{{ old('mo_ta', $taiNguyen->mo_ta) }}</textarea>
                            @error('mo_ta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </section>

                <section class="apx-section">
                    <header class="apx-section-head">
                        <div class="apx-section-title">
                            <span class="apx-section-num">2</span>
                            <div>
                                <h2><i class="fas fa-file-pen"></i> Thay đổi file hoặc liên kết</h2>
                                <p>Để trống nếu bạn chỉ muốn sửa metadata mà không thay nguồn tài nguyên.</p>
                            </div>
                        </div>
                    </header>

                    <div class="tv-form-card">
                        <div class="tv-current-file mb-3">
                            <div class="tv-current-file-icon" style="background: {{ $iconSoft }}; color: {{ $iconColor }};">
                                <i class="fas {{ $taiNguyen->loai_icon }}"></i>
                            </div>
                            <div class="tv-current-file-body">
                                <strong>{{ $taiNguyen->is_external ? 'Liên kết ngoài hiện tại' : ($taiNguyen->file_name ?: 'Tệp hiện tại') }}</strong>
                                <span>{{ $taiNguyen->is_external ? $taiNguyen->link_ngoai : $taiNguyen->file_status_message }}</span>
                            </div>
                        </div>

                        <div class="tv-help-card is-warning mb-3">
                            <div class="tv-help-title"><i class="fas fa-triangle-exclamation"></i> Khi nào nên thay file?</div>
                            <p>Nếu bạn cập nhật nội dung tài liệu hoặc sửa nhầm file cũ, hãy tải file mới lên. Nếu không, hệ thống sẽ giữ nguyên tài nguyên hiện tại.</p>
                        </div>

                        <div class="tv-upload-shell">
                            <label class="tv-field-label"><i class="fas fa-upload"></i> Thay tệp đính kèm</label>
                            <input type="file" name="file_dinh_kem" class="form-control @error('file_dinh_kem') is-invalid @enderror">
                            @error('file_dinh_kem') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="tv-or-divider">hoặc</div>

                        <div class="tv-upload-shell">
                            <label class="tv-field-label"><i class="fas fa-link"></i> Cập nhật liên kết ngoài</label>
                            <input type="url"
                                   name="link_ngoai"
                                   class="form-control @error('link_ngoai') is-invalid @enderror"
                                   value="{{ old('link_ngoai', $taiNguyen->link_ngoai) }}"
                                   placeholder="https://example.com/document-or-video">
                            @error('link_ngoai') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="tv-form-actions">
                            <a href="{{ route('giang-vien.thu-vien.index') }}" class="btn btn-light border fw-bold px-4">
                                <i class="fas fa-arrow-left me-1"></i> Hủy bỏ
                            </a>
                            <button type="submit" class="btn btn-primary fw-bold px-5">
                                <i class="fas fa-floppy-disk me-1"></i> Cập nhật tài nguyên
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-lg-4">
                <div class="tv-side-stack">
                    <aside class="tv-current-card">
                        <h3 class="tv-side-title">Tài nguyên hiện tại</h3>
                        <div class="tv-current-file mb-3">
                            <div class="tv-current-file-icon" style="background: {{ $iconSoft }}; color: {{ $iconColor }};">
                                <i class="fas {{ $taiNguyen->loai_icon }}"></i>
                            </div>
                            <div class="tv-current-file-body">
                                <strong>{{ $taiNguyen->tieu_de }}</strong>
                                <span>{{ $taiNguyen->loai_label }}</span>
                            </div>
                        </div>

                        <div class="tv-info-grid">
                            <div class="tv-info-chip">
                                <small>Phạm vi</small>
                                <strong>{{ $scope['label'] }}</strong>
                            </div>
                            <div class="tv-info-chip">
                                <small>Duyệt</small>
                                <strong>{{ $approval['label'] }}</strong>
                            </div>
                            <div class="tv-info-chip">
                                <small>Nguồn</small>
                                <strong>{{ $taiNguyen->nguon_hien_thi_label }}</strong>
                            </div>
                            <div class="tv-info-chip">
                                <small>Kích thước</small>
                                <strong>{{ $sizeLabel ?: 'N/A' }}</strong>
                            </div>
                        </div>
                    </aside>

                    @if($taiNguyen->ghi_chu_admin)
                        <aside class="tv-side-card">
                            <div class="tv-feedback-card">
                                <div class="tv-feedback-title"><i class="fas fa-message"></i> Phản hồi từ admin</div>
                                <p>{{ $taiNguyen->ghi_chu_admin }}</p>
                            </div>
                        </aside>
                    @endif

                    <aside class="tv-side-card">
                        <h3 class="tv-side-title">Gợi ý khi cập nhật</h3>
                        <ul class="tv-side-list">
                            <li><i class="fas fa-check-circle"></i><span>Nếu sửa nội dung quan trọng, bạn nên kiểm tra lại trạng thái duyệt sau khi lưu.</span></li>
                            <li><i class="fas fa-check-circle"></i><span>Giữ tiêu đề ngắn gọn, rõ chủ đề để dễ tìm kiếm trong thư viện.</span></li>
                            <li><i class="fas fa-check-circle"></i><span>Với link ngoài, hãy dùng URL ổn định để tránh lỗi truy cập về sau.</span></li>
                        </ul>
                    </aside>
                </div>
            </div>
        </div>
    </form>
</div>

@include('pages.giang-vien.thu-vien.partials.shared-styles')
@endsection
