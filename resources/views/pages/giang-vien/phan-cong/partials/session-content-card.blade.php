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
                    <div class="fw-bold smaller mb-2 d-flex justify-content-between">
                        <span><i class="fas fa-folder-open me-1 text-success"></i> Tài liệu</span>
                        <span class="badge bg-success text-white rounded-pill">{{ $contentSummary['resource_count'] }}</span>
                    </div>
                    @if($lich->taiNguyen->isNotEmpty())
                        <div class="d-flex flex-column gap-1">
                            @foreach($lich->taiNguyen->sortBy('thu_tu_hien_thi')->take(3) as $taiNguyen)
                                @php $isHien = $taiNguyen->trang_thai_hien_thi === 'hien'; @endphp
                                <div class="d-flex align-items-center justify-content-between bg-white p-1 px-2 rounded border border-light smaller shadow-xs">
                                    <span class="text-truncate me-2 {{ !$isHien ? 'text-muted italic' : 'text-dark fw-semibold' }}" title="{{ $taiNguyen->tieu_de }}">{{ $taiNguyen->tieu_de }}</span>
                                    <a href="{{ $taiNguyen->link_ngoai ?: asset('storage/' . ltrim((string) $taiNguyen->duong_dan_file, '/')) }}" target="_blank" class="text-primary"><i class="fas fa-external-link-alt" style="font-size: 0.7rem;"></i></a>
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
