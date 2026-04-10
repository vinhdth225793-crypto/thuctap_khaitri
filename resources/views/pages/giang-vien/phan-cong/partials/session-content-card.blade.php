@php
    $contentSummary = $timelineItem['contentSummary'] ?? [
        'resource_count' => $lich->taiNguyen->count(),
        'lecture_count' => $lich->baiGiangs->count(),
        'exam_count' => $lich->baiKiemTras->count(),
        'has_report' => filled($lich->bao_cao_giang_vien),
    ];
    $lecturePreview = collect($timelineItem['lecturePreview'] ?? [])->values();
@endphp

<div class="col-12">
    <div class="session-cluster-card">
        <div class="session-cluster-card__header align-items-start border-bottom pb-3 mb-4">
            <div>
                <div class="session-cluster-card__eyebrow text-secondary">Cụm 4</div>
                <div class="session-card-title mt-2">
                    <i class="fas fa-layer-group text-secondary opacity-50"></i>
                    Nội dung & Tài nguyên buổi học
                </div>
                <div class="small text-muted">
                    Hệ thống hóa học liệu, bài giảng và bài kiểm tra cho buổi học này.
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-sm btn-success fw-bold shadow-sm px-3 py-2 btn-add-resource"
                        data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}">
                    <i class="fas fa-file-upload me-1"></i> Tải tài liệu
                </button>
                <a href="{{ route('giang-vien.bai-giang.create', ['phan_cong_id' => $phanCong->id, 'lich_hoc_id' => $lich->id]) }}"
                   class="btn btn-sm btn-primary fw-bold shadow-sm px-3 py-2">
                    <i class="fas fa-chalkboard-teacher me-1"></i> Thêm bài giảng
                </a>
                <button type="button" class="btn btn-sm btn-danger fw-bold shadow-sm px-3 py-2 btn-add-test"
                        data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}" data-module-id="{{ $phanCong->module_hoc_id }}">
                    <i class="fas fa-file-signature me-1"></i> Tạo kiểm tra
                </button>
            </div>
        </div>

        <div class="session-metric-grid mb-4">
            <div class="session-metric bg-success bg-opacity-10 border-success border-opacity-10">
                <div class="session-metric__label text-success">Tài liệu tham khảo</div>
                <div class="session-metric__value fs-5">{{ $contentSummary['resource_count'] }} <small class="fw-normal">tệp</small></div>
            </div>
            <div class="session-metric bg-primary bg-opacity-10 border-primary border-opacity-10">
                <div class="session-metric__label text-primary">Bài giảng điện tử</div>
                <div class="session-metric__value fs-5">{{ $contentSummary['lecture_count'] }} <small class="fw-normal">bài</small></div>
            </div>
            <div class="session-metric bg-danger bg-opacity-10 border-danger border-opacity-10">
                <div class="session-metric__label text-danger">Bài kiểm tra</div>
                <div class="session-metric__value fs-5">{{ $contentSummary['exam_count'] }} <small class="fw-normal">đề</small></div>
            </div>
            <div class="session-metric bg-info bg-opacity-10 border-info border-opacity-10">
                <div class="session-metric__label text-info">Báo cáo buổi dạy</div>
                <div class="session-metric__value fs-5">
                    @if($contentSummary['has_report'])
                        <i class="fas fa-check-circle text-success"></i> <small class="fw-normal text-success">Đã chốt</small>
                    @else
                        <i class="fas fa-clock text-warning"></i> <small class="fw-normal text-warning">Chờ chốt</small>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="session-note h-100 p-0 border-0 bg-transparent">
                    <div class="fw-bold text-dark mb-3 d-flex align-items-center gap-2 px-1">
                        <span class="bg-success text-white p-1 rounded smaller"><i class="fas fa-folder-open"></i></span>
                        Danh sách tài liệu
                    </div>

                    @if($lich->taiNguyen->isNotEmpty())
                        <div class="d-flex flex-column gap-2">
                            @foreach($lich->taiNguyen->sortBy('thu_tu_hien_thi')->take(3) as $taiNguyen)
                                @php
                                    $taiNguyenUrl = $taiNguyen->link_ngoai ?: asset('storage/' . ltrim((string) $taiNguyen->duong_dan_file, '/'));
                                @endphp
                                <div class="resource-card shadow-xs">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="resource-card__icon bg-{{ $taiNguyen->loai_color }}-soft text-{{ $taiNguyen->loai_color }}">
                                            <i class="fas {{ $taiNguyen->loai_icon }}"></i>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-bold text-dark text-truncate small" title="{{ $taiNguyen->tieu_de }}">
                                                {{ $taiNguyen->tieu_de }}
                                            </div>
                                            <div class="smaller text-muted mt-1">
                                                {{ $taiNguyen->loai_label }} • #{{ $taiNguyen->thu_tu_hien_thi }}
                                            </div>
                                        </div>
                                        <a href="{{ $taiNguyenUrl }}" target="_blank" class="btn btn-icon-xs text-primary border rounded-circle" title="Mở">
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                            @if($contentSummary['resource_count'] > 3)
                                <div class="text-center mt-2">
                                    <span class="smaller text-muted italic">Và {{ $contentSummary['resource_count'] - 3 }} tài liệu khác...</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="session-note session-note--soft text-center py-4">
                            <i class="fas fa-folder-open fa-2x opacity-10 mb-2 d-block"></i>
                            Chưa có tài liệu
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">
                <div class="session-note h-100 p-0 border-0 bg-transparent">
                    <div class="fw-bold text-dark mb-3 d-flex align-items-center gap-2 px-1">
                        <span class="bg-primary text-white p-1 rounded smaller"><i class="fas fa-play-circle"></i></span>
                        Bài giảng điện tử
                    </div>

                    @if($lecturePreview->isNotEmpty())
                        <div class="d-flex flex-column gap-2">
                            @foreach($lecturePreview as $lecture)
                                <div class="resource-card shadow-xs">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="resource-card__icon bg-primary-soft text-primary">
                                            <i class="fas {{ $lecture['is_live'] ? 'fa-video' : 'fa-book-reader' }}"></i>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-bold text-dark text-truncate small" title="{{ $lecture['title'] }}">
                                                {{ $lecture['title'] }}
                                            </div>
                                            <div class="smaller text-muted mt-1">
                                                {{ $lecture['type_label'] }}
                                                @if($lecture['has_internal_room'])
                                                    • <span class="text-info">Có phòng Live</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @if($contentSummary['lecture_count'] > $lecturePreview->count())
                                <div class="text-center mt-2">
                                    <span class="smaller text-muted italic">Và {{ $contentSummary['lecture_count'] - $lecturePreview->count() }} bài học khác...</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="session-note session-note--soft text-center py-4">
                            <i class="fas fa-chalkboard-teacher fa-2x opacity-10 mb-2 d-block"></i>
                            Chưa có bài giảng
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">
                <div class="session-note h-100 p-0 border-0 bg-transparent">
                    <div class="fw-bold text-dark mb-3 d-flex align-items-center gap-2 px-1">
                        <span class="bg-danger text-white p-1 rounded smaller"><i class="fas fa-stopwatch"></i></span>
                        Bài kiểm tra & Ghi chú
                    </div>

                    @if($lich->baiKiemTras->isNotEmpty())
                        <div class="d-flex flex-column gap-2 mb-3">
                            @foreach($lich->baiKiemTras->take(3) as $baiKiemTra)
                                <div class="exam-pill shadow-xs border-danger border-opacity-10 bg-white">
                                    <div class="min-w-0">
                                        <div class="fw-bold text-danger text-truncate small" title="{{ $baiKiemTra->tieu_de }}">
                                            {{ $baiKiemTra->tieu_de }}
                                        </div>
                                        <div class="smaller text-muted mt-1">
                                            <i class="far fa-clock me-1"></i>{{ $baiKiemTra->thoi_gian_lam_bai }} phút
                                        </div>
                                    </div>
                                    <a href="{{ route('giang-vien.bai-kiem-tra.edit', $baiKiemTra->id) }}" class="btn btn-icon-xs text-danger border rounded-circle" title="Cấu hình">
                                        <i class="fas fa-cog"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="session-note session-note--soft text-center py-3 mb-3">
                            Chưa có bài kiểm tra
                        </div>
                    @endif

                    <div class="session-note bg-light border-0 shadow-none p-3">
                        <div class="fw-bold text-dark smaller mb-2 d-flex align-items-center justify-content-between">
                            <span>Ghi chú sau buổi học</span>
                            <i class="fas fa-pen-nib text-muted smaller"></i>
                        </div>
                        <div class="smaller text-muted lh-base">
                            @if(filled($lich->bao_cao_giang_vien))
                                {!! nl2br(e(\Illuminate\Support\Str::limit($lich->bao_cao_giang_vien, 150))) !!}
                            @else
                                <span class="italic">Giảng viên chưa chốt báo cáo cho buổi học này.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
