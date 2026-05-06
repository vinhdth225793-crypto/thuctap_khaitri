@php
    $contentSummary = $timelineItem['contentSummary'] ?? [
        'resource_count' => $lich->taiNguyen->count(),
        'lecture_count' => $lich->baiGiangs->count(),
        'exam_count' => $lich->baiKiemTras->count(),
        'has_report' => filled($lich->bao_cao_giang_vien),
    ];
@endphp

<div class="col-12 mt-2">
    <div class="p-3 rounded-4 border border-dashed bg-white">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="fw-bold smaller text-muted text-uppercase"><i class="fas fa-layer-group me-1 text-secondary"></i> Nội dung & Tài nguyên</div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-xs btn-outline-success fw-bold btn-add-resource" data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}">
                    <i class="fas fa-file-upload me-1"></i> + Tài liệu
                </button>
                <a href="{{ route('giang-vien.bai-giang.create', ['phan_cong_id' => $phanCong->id, 'lich_hoc_id' => $lich->id]) }}" class="btn btn-xs btn-outline-primary fw-bold">
                    <i class="fas fa-chalkboard-teacher me-1"></i> + Bài giảng
                </a>
                <button type="button" class="btn btn-xs btn-outline-danger fw-bold btn-add-test" data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}" data-module-id="{{ $phanCong->module_hoc_id }}">
                    <i class="fas fa-file-signature me-1"></i> + Kiểm tra
                </button>
            </div>
        </div>

        <div class="row g-3">
            {{-- Tài liệu --}}
            <div class="col-md-4">
                <div class="p-2 rounded bg-light h-100">
                    <div class="fw-bold smaller mb-2 d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-folder-open me-1 text-success"></i> Tài liệu</span>
                        @php
                            $publicCount = $lich->taiNguyen->where('trang_thai_hien_thi', 'hien')->count();
                            $draftCount = $lich->taiNguyen->where('trang_thai_hien_thi', '!=', 'hien')->count();
                        @endphp
                        <span class="d-inline-flex gap-1">
                            <span class="badge bg-success text-white rounded-pill" title="Đã công khai cho HV">{{ $publicCount }}</span>
                            <span class="badge bg-danger text-white rounded-pill" title="Đang ở dạng nháp">{{ $draftCount }}</span>
                        </span>
                    </div>
                    @if($lich->taiNguyen->isNotEmpty())
                        <div class="resource-list d-flex flex-column gap-2">
                            @foreach($lich->taiNguyen->sortBy('thu_tu_hien_thi') as $taiNguyen)
                                @php
                                    $isHien = $taiNguyen->trang_thai_hien_thi === 'hien';
                                    $iconMap = [
                                        'video' => 'fa-video', 'pdf' => 'fa-file-pdf',
                                        'word' => 'fa-file-word', 'powerpoint' => 'fa-file-powerpoint',
                                        'excel' => 'fa-file-excel', 'image' => 'fa-image',
                                        'audio' => 'fa-volume-up', 'archive' => 'fa-file-archive',
                                        'link_ngoai' => 'fa-link', 'tai_lieu_khac' => 'fa-file',
                                    ];
                                    $fileIcon = $iconMap[$taiNguyen->loai_tai_nguyen] ?? 'fa-file';
                                @endphp
                                @php
                                    $sesPreviewId = 'ses-preview-' . $lich->id . '-' . $taiNguyen->id;
                                @endphp
                                <div id="resource-item-{{ $taiNguyen->id }}"
                                     class="resource-row {{ $isHien ? 'is-public' : 'is-draft' }}">
                                    <div class="resource-row__head">
                                        <span class="resource-row__title" title="{{ $taiNguyen->tieu_de }}">
                                            <i class="fas {{ $fileIcon }} resource-row__icon"></i>
                                            <span class="resource-row__name">{{ $taiNguyen->tieu_de }}</span>
                                        </span>
                                        <span class="resource-row__pill">
                                            <i class="fas fa-{{ $isHien ? 'globe-asia' : 'pencil-alt' }}"></i>
                                            {{ $isHien ? 'CÔNG KHAI' : 'NHÁP' }}
                                        </span>
                                    </div>
                                    <div class="resource-row__actions">
                                        <button type="button"
                                                class="btn-toggle-resource-hien-thi resource-toggle-btn {{ $isHien ? 'is-on' : 'is-off' }}"
                                                data-id="{{ $taiNguyen->id }}"
                                                title="{{ $isHien ? 'Bấm để thu hồi (chuyển về nháp)' : 'Bấm để công khai cho học viên' }}">
                                            <i class="fas fa-{{ $isHien ? 'eye-slash' : 'globe' }}"></i>
                                            <span>{{ $isHien ? 'Thu hồi' : 'Công khai' }}</span>
                                        </button>
                                        <button type="button"
                                                class="resource-row__preview-btn"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#{{ $sesPreviewId }}"
                                                aria-expanded="false"
                                                aria-controls="{{ $sesPreviewId }}"
                                                title="Xem trước trong trang">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="{{ $taiNguyen->link_ngoai ?: asset('storage/' . ltrim((string) $taiNguyen->duong_dan_file, '/')) }}"
                                           target="_blank" class="resource-row__link" title="Mở tài liệu (tab mới)">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                    <div class="collapse" id="{{ $sesPreviewId }}">
                                        <div class="resource-row__preview">
                                            <iframe
                                                class="resource-row__frame"
                                                data-preview-frame
                                                data-preview-src="{{ route('giang-vien.buoi-hoc.tai-nguyen.preview', $taiNguyen->id) }}"
                                                title="Xem trước {{ $taiNguyen->tieu_de }}"
                                                loading="lazy">
                                            </iframe>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-2 smaller text-muted italic">Chưa có</div>
                    @endif
                </div>
            </div>

            {{-- Bài giảng --}}
            <div class="col-md-4">
                <div class="p-2 rounded bg-light h-100">
                    <div class="fw-bold smaller mb-2 d-flex justify-content-between">
                        <span><i class="fas fa-play-circle me-1 text-primary"></i> Bài giảng</span>
                        <span class="badge bg-primary text-white rounded-pill">{{ $contentSummary['lecture_count'] }}</span>
                    </div>
                    @if($lich->baiGiangs->isNotEmpty())
                        <div class="d-flex flex-column gap-1">
                            @foreach($lich->baiGiangs->sortBy('thu_tu_hien_thi')->take(3) as $lecture)
                                <div class="d-flex align-items-center justify-content-between bg-white p-1 px-2 rounded border border-light smaller shadow-xs">
                                    <span class="text-truncate me-2 text-dark fw-semibold" title="{{ $lecture->tieu_de }}">{{ $lecture->tieu_de }}</span>
                                    <a href="{{ route('giang-vien.bai-giang.edit', $lecture->id) }}" class="text-primary"><i class="fas fa-edit" style="font-size: 0.7rem;"></i></a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-2 smaller text-muted italic">Chưa có</div>
                    @endif
                </div>
            </div>

            {{-- Kiểm tra & Ghi chú --}}
            <div class="col-md-4">
                <div class="p-2 rounded bg-light h-100">
                    <div class="fw-bold smaller mb-2 d-flex justify-content-between">
                        <span><i class="fas fa-pen-nib me-1 text-danger"></i> Kiểm tra & Ghi chú</span>
                        @if($contentSummary['has_report'])
                            <i class="fas fa-check-circle text-success" title="Đã có báo cáo"></i>
                        @endif
                    </div>
                    <div class="d-flex flex-column gap-1">
                        @foreach($lich->baiKiemTras as $test)
                            <div class="d-flex align-items-center justify-content-between bg-white p-1 px-2 rounded border border-danger border-opacity-10 smaller shadow-xs">
                                <span class="text-truncate me-2 text-danger fw-bold" title="{{ $test->tieu_de }}">{{ $test->tieu_de }}</span>
                                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                    @if($test->trang_thai_duyet === 'da_duyet' && $test->trang_thai_phat_hanh !== 'phat_hanh')
                                        <form action="{{ route('giang-vien.bai-kiem-tra.publish', $test->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Phát hành đề kiểm tra này cho học viên làm bài?')">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success py-0 px-2" title="Phát hành cho học viên">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('giang-vien.bai-kiem-tra.edit', $test->id) }}" class="text-danger" title="Cấu hình đề"><i class="fas fa-cog" style="font-size: 0.7rem;"></i></a>
                                </div>
                            </div>
                        @endforeach
                        <div class="bg-white p-1 px-2 rounded border border-light smaller shadow-xs text-muted italic text-truncate">
                            {{ $lich->bao_cao_giang_vien ? \Illuminate\Support\Str::limit($lich->bao_cao_giang_vien, 40) : 'Chưa có ghi chú...' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
