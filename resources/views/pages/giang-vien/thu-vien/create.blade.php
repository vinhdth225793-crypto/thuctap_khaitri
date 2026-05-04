@extends('layouts.app')

@section('title', 'Thêm tài nguyên mới')

@section('content')
@php
    $typeOptions = [
        'video' => 'Video bài giảng',
        'pdf' => 'Tài liệu PDF',
        'word' => 'File Word',
        'powerpoint' => 'File PowerPoint',
        'excel' => 'File Excel',
        'image' => 'Hình ảnh',
        'archive' => 'File nén (Zip/Rar)',
        'link_ngoai' => 'Liên kết ngoài',
        'tai_lieu_khac' => 'Tài liệu khác',
    ];

    $scopeOptions = [
        'ca_nhan' => 'Cá nhân',
        'khoa_hoc' => 'Trong khóa học',
        'cong_khai' => 'Công khai hệ thống',
    ];
@endphp

<div class="container-fluid admin-page-x tv-page">
    <div class="apx-welcome tv-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-cloud-upload-alt"></i></div>
        <div class="apx-welcome-text">
            <div class="tv-tag-row">
                <span class="tv-library-badge">
                    <i class="fas fa-plus-circle"></i> THÊM TÀI NGUYÊN
                </span>
                <span class="tv-status-badge">
                    <i class="fas fa-folder-plus"></i> Tạo mới trong thư viện
                </span>
                <span class="tv-filter-badge">
                    <i class="fas fa-shield-alt"></i> Sẵn sàng gửi duyệt sau khi lưu
                </span>
            </div>
            <h4>Thêm tài nguyên mới vào thư viện</h4>
            <p>
                <span><i class="fas fa-link"></i> Hỗ trợ file tải lên hoặc liên kết ngoài</span>
                <span class="tv-sep">·</span>
                <span><i class="fas fa-users-viewfinder"></i> Chọn phạm vi dùng ngay từ đầu</span>
                <span class="tv-sep">·</span>
                <span><i class="fas fa-database"></i> Dễ tái sử dụng cho các khóa học sau</span>
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('giang-vien.thu-vien.index') }}" class="apx-view-toggle">
                <i class="fas fa-arrow-left"></i> <span>Về thư viện</span>
            </a>
        </div>
    </div>

    @include('components.alert')

    <form action="{{ route('giang-vien.thu-vien.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <section class="apx-section">
                    <header class="apx-section-head">
                        <div class="apx-section-title">
                            <span class="apx-section-num">1</span>
                            <div>
                                <h2><i class="fas fa-pen-ruler"></i> Thông tin cơ bản</h2>
                                <p>Khai báo tiêu đề, loại tài nguyên, phạm vi sử dụng và mô tả ngắn.</p>
                            </div>
                        </div>
                    </header>

                    <div class="tv-form-card">
                        <div class="mb-3">
                            <label class="tv-field-label"><i class="fas fa-heading"></i> Tiêu đề tài nguyên <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="tieu_de"
                                   class="form-control @error('tieu_de') is-invalid @enderror"
                                   value="{{ old('tieu_de') }}"
                                   placeholder="VD: Slide chương 1, tài liệu tham khảo, video hướng dẫn..."
                                   required>
                            @error('tieu_de') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="tv-field-label"><i class="fas fa-shapes"></i> Loại tài nguyên <span class="text-danger">*</span></label>
                                <select name="loai_tai_nguyen" class="form-select @error('loai_tai_nguyen') is-invalid @enderror" required>
                                    @foreach($typeOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('loai_tai_nguyen', 'video') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('loai_tai_nguyen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="tv-field-label"><i class="fas fa-users-cog"></i> Phạm vi sử dụng <span class="text-danger">*</span></label>
                                <select name="pham_vi_su_dung" class="form-select @error('pham_vi_su_dung') is-invalid @enderror" required>
                                    @foreach($scopeOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('pham_vi_su_dung', 'ca_nhan') === $key ? 'selected' : '' }}>
                                            {{ $label }}{{ $key === 'ca_nhan' ? ' (chỉ bạn thấy)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('pham_vi_su_dung') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="tv-field-label"><i class="fas fa-align-left"></i> Mô tả</label>
                            <textarea name="mo_ta"
                                      class="form-control @error('mo_ta') is-invalid @enderror"
                                      rows="5"
                                      placeholder="Mô tả ngắn gọn nội dung, mục đích sử dụng hoặc lưu ý cho tài nguyên này...">{{ old('mo_ta') }}</textarea>
                            @error('mo_ta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </section>

                <section class="apx-section">
                    <header class="apx-section-head">
                        <div class="apx-section-title">
                            <span class="apx-section-num">2</span>
                            <div>
                                <h2><i class="fas fa-file-arrow-up"></i> Nguồn tài nguyên</h2>
                                <p>Bạn có thể tải file trực tiếp hoặc dùng liên kết ngoài. Chỉ cần một trong hai.</p>
                            </div>
                        </div>
                    </header>

                    <div class="tv-form-card">
                        <div class="tv-help-card is-blue mb-3">
                            <div class="tv-help-title"><i class="fas fa-circle-info"></i> Lưu ý khi tải lên</div>
                            <p>Dung lượng tối đa là 50MB. Video có thể cần thời gian xử lý trước khi sẵn sàng dùng trong hệ thống.</p>
                        </div>

                        <div class="tv-upload-shell">
                            <label class="tv-field-label"><i class="fas fa-upload"></i> Tải lên tệp tin</label>
                            <input type="file" name="file_dinh_kem" class="form-control @error('file_dinh_kem') is-invalid @enderror">
                            <div class="form-text mt-2">Hỗ trợ PDF, Word, PowerPoint, Excel, hình ảnh, video, file nén và một số định dạng phổ biến khác.</div>
                            @error('file_dinh_kem') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="tv-or-divider">hoặc</div>

                        <div class="tv-upload-shell">
                            <label class="tv-field-label"><i class="fas fa-link"></i> Liên kết ngoài (URL)</label>
                            <input type="url"
                                   name="link_ngoai"
                                   class="form-control @error('link_ngoai') is-invalid @enderror"
                                   value="{{ old('link_ngoai') }}"
                                   placeholder="https://example.com/document-or-video">
                            <div class="form-text mt-2">Phù hợp khi bạn đang lưu tài nguyên ở Google Drive, YouTube, OneDrive hoặc một nguồn ngoài khác.</div>
                            @error('link_ngoai') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="tv-form-actions">
                            <a href="{{ route('giang-vien.thu-vien.index') }}" class="btn btn-light border fw-bold px-4">
                                <i class="fas fa-arrow-left me-1"></i> Hủy bỏ
                            </a>
                            <button type="submit" class="btn btn-primary fw-bold px-5">
                                <i class="fas fa-floppy-disk me-1"></i> Lưu vào thư viện
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-lg-4">
                <div class="tv-side-stack">
                    <aside class="tv-side-card">
                        <h3 class="tv-side-title">Quy trình gợi ý</h3>
                        <ul class="tv-side-list">
                            <li><i class="fas fa-check-circle"></i><span>Đặt tiêu đề rõ ràng để dễ tìm lại khi tái sử dụng.</span></li>
                            <li><i class="fas fa-check-circle"></i><span>Chọn đúng phạm vi nếu bạn muốn dùng cho nhiều lớp hoặc công khai toàn hệ thống.</span></li>
                            <li><i class="fas fa-check-circle"></i><span>Sau khi lưu, bạn có thể quay lại trang thư viện để gửi tài nguyên cho admin duyệt.</span></li>
                        </ul>
                    </aside>

                    <aside class="tv-side-card">
                        <h3 class="tv-side-title">Định dạng hay dùng</h3>
                        <div class="tv-info-grid">
                            <div class="tv-info-chip">
                                <small>PDF</small>
                                <strong>Tài liệu đọc</strong>
                            </div>
                            <div class="tv-info-chip">
                                <small>Video</small>
                                <strong>Bài giảng ghi hình</strong>
                            </div>
                            <div class="tv-info-chip">
                                <small>PowerPoint</small>
                                <strong>Slide giảng dạy</strong>
                            </div>
                            <div class="tv-info-chip">
                                <small>Link ngoài</small>
                                <strong>Nguồn tham chiếu</strong>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </form>
</div>

@include('pages.giang-vien.thu-vien.partials.shared-styles')
@endsection
