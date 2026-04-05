@php
    $lich = $timelineItem['lich'];
    $teacherLiveRoom = $timelineItem['teacherLiveRoom'];
    $teacherLiveLecture = $timelineItem['teacherLiveLecture'];
    $teachingStatus = $timelineItem['teachingStatus'];
    $isFocused = $focusedLichHocId === (int) $lich->id;
@endphp

<div id="session-{{ $lich->id }}" class="session-block mb-4 {{ $isFocused ? 'session-block-focused' : '' }}">
    <div class="session-shell shadow-sm">
        {{-- Header của buổi học --}}
        <div class="session-shell__header">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="session-shell__number">
                        {{ $lich->buoi_so }}
                    </div>
                    <div>
                        <div class="session-shell__title">
                            Buổi {{ $lich->buoi_so }} - {{ $phanCong->moduleHoc->ten_module }}
                        </div>
                        <div class="small text-muted d-flex align-items-center flex-wrap gap-2 mt-1">
                            <span><i class="far fa-calendar-alt me-1"></i>{{ $lich->ngay_hoc->format('d/m/Y') }} ({{ $lich->thu_label }})</span>
                            <span class="text-silver">|</span>
                            <span><i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($lich->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($lich->gio_ket_thuc)->format('H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <div class="btn-group shadow-sm">
                        <button type="button" class="btn btn-sm btn-outline-warning btn-edit-lich"
                                data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}"
                                data-hinhthuc="{{ $lich->hinh_thuc }}" data-nentang="{{ $lich->nen_tang }}"
                                data-link="{{ $lich->link_online }}" data-meetingid="{{ $lich->meeting_id }}"
                                data-pass="{{ $lich->mat_khau_cuoc_hop }}" data-phong="{{ $lich->phong_hoc }}">
                            <i class="fas fa-edit me-1"></i> Sửa buổi
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success btn-add-resource"
                                data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}">
                            <i class="fas fa-plus me-1"></i> Tài liệu
                        </button>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary shadow-sm btn-diem-danh"
                            data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}">
                        <i class="fas fa-user-check me-1"></i> Điểm danh HV
                    </button>
                </div>
            </div>
            
            <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top border-light">
                <span class="badge bg-{{ $lich->hinh_thuc_color }}-soft text-{{ $lich->hinh_thuc_color }} border border-{{ $lich->hinh_thuc_color }} rounded-pill">
                    <i class="fas {{ $lich->hinh_thuc === 'online' ? 'fa-video' : 'fa-door-open' }} me-1"></i>
                    {{ $lich->hinh_thuc === 'online' ? 'Dạy Online' : 'Dạy Trực tiếp' }}
                </span>
                <span class="badge bg-{{ $lich->trang_thai_color }}-soft text-{{ $lich->trang_thai_color }} border border-{{ $lich->trang_thai_color }} rounded-pill">
                    {{ $lich->trang_thai_label }}
                </span>
                <span class="badge bg-light text-secondary border rounded-pill">
                    {{ $lich->schedule_range_label }}
                </span>
            </div>
        </div>

        <div class="session-shell__body">
            <div class="row g-3">
                {{-- Cụm 1: Thông tin --}}
                <div class="col-xl-3 col-md-6">
                    <div class="session-cluster-card">
                        <div class="session-cluster-card__header">
                            <div class="session-cluster-card__eyebrow text-primary">Thông tin</div>
                            <i class="fas fa-info-circle text-primary opacity-50"></i>
                        </div>

                        <div class="session-info-list">
                            <div class="session-info-row">
                                <span>Thứ tự</span>
                                <strong>Buổi #{{ $lich->buoi_so }}</strong>
                            </div>
                            <div class="session-info-row">
                                <span>Ngày dạy</span>
                                <strong>{{ $lich->ngay_hoc->format('d/m/Y') }}</strong>
                            </div>
                            <div class="session-info-row">
                                <span>Ca học</span>
                                <strong>{{ \Carbon\Carbon::parse($lich->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($lich->gio_ket_thuc)->format('H:i') }}</strong>
                            </div>
                            <div class="session-info-row">
                                <span>Hình thức</span>
                                <strong>{{ $lich->hinh_thuc_label }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cụm 2: Dạy học & Phòng Live --}}
                <div class="col-xl-3 col-md-6">
                    <div class="session-cluster-card border border-{{ $teachingStatus['room_status_color'] }} border-opacity-25">
                        <div class="session-cluster-card__header">
                            <div class="session-cluster-card__eyebrow text-info">Giảng dạy</div>
                            <span class="badge bg-{{ $teachingStatus['room_status_color'] }}-soft text-{{ $teachingStatus['room_status_color'] }} border border-{{ $teachingStatus['room_status_color'] }}">
                                {{ $teachingStatus['room_status_label'] }}
                            </span>
                        </div>

                        @if($lich->hinh_thuc === 'online')
                            <div class="mb-3">
                                <div class="smaller text-muted mb-1">Link họp (Google Meet/Zoom):</div>
                                @if($lich->link_online)
                                    <a href="{{ $lich->link_online }}" target="_blank" class="small fw-bold text-primary text-decoration-none d-block text-truncate">
                                        <i class="fas fa-external-link-alt me-1"></i>{{ $lich->link_online }}
                                    </a>
                                @else
                                    <div class="small text-danger italic"><i class="fas fa-exclamation-triangle me-1"></i>Chưa cập nhật link</div>
                                @endif
                            </div>

                            <div class="session-note mb-3 p-2 bg-white">
                                <div class="fw-bold text-dark smaller mb-1">Phòng Live nội bộ</div>
                                @if($teacherLiveRoom)
                                    <div class="smaller text-muted">
                                        Mã: <code class="text-primary">{{ data_get($teacherLiveRoom->du_lieu_nen_tang_json, 'room_code', '---') }}</code>
                                    </div>
                                    <div class="smaller text-muted">
                                        Trạng thái: {{ $teacherLiveRoom->timeline_trang_thai_label }}
                                    </div>
                                @else
                                    <div class="smaller text-muted italic">Chưa có phòng live.</div>
                                @endif
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                @if($teachingStatus['can_create_room'])
                                    <form action="{{ route('giang-vien.live-room.schedule.create', $lich->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-primary fw-bold shadow-sm">
                                            <i class="fas fa-plus-circle me-1"></i> Tạo phòng
                                        </button>
                                    </form>
                                @endif

                                @if($teachingStatus['can_enter_room'] && $teacherLiveLecture)
                                    <a href="{{ route('giang-vien.live-room.schedule.show', $lich->id) }}" class="btn btn-xs btn-outline-primary fw-bold">
                                        <i class="fas fa-door-open me-1"></i> Vào phòng
                                    </a>
                                @endif

                                @if($teachingStatus['can_end_room'])
                                    <form action="{{ route('giang-vien.live-room.schedule.end', $lich->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-outline-danger fw-bold">
                                            <i class="fas fa-stop-circle me-1"></i> Đóng
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @else
                            <div class="session-note p-2 bg-white h-100">
                                <div class="fw-bold text-dark smaller mb-2"><i class="fas fa-map-marker-alt me-1 text-danger"></i>Địa điểm dạy</div>
                                <div class="small text-dark fw-semibold">
                                    {{ $lich->phong_hoc ?: $lich->ghi_chu ?: 'Chưa cập nhật phòng học.' }}
                                </div>
                                <div class="smaller text-muted mt-2">Vui lòng có mặt trước 15 phút để chuẩn bị.</div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Cụm 3: Điểm danh Giảng viên --}}
                @include('pages.giang-vien.phan-cong.partials.teacher-attendance-card', [
                    'lich' => $lich,
                    'phanCong' => $phanCong,
                    'timelineItem' => $timelineItem,
                ])

                {{-- Cụm 4: Tổng quan hoạt động --}}
                <div class="col-xl-3 col-md-6">
                    <div class="session-cluster-card">
                        <div class="session-cluster-card__header">
                            <div class="session-cluster-card__eyebrow text-secondary">Hoạt động</div>
                            <i class="fas fa-tasks text-secondary opacity-50"></i>
                        </div>

                        <div class="session-info-list">
                            <div class="session-info-row">
                                <span><i class="fas fa-file-download me-1"></i> Tài liệu</span>
                                <span class="badge bg-primary-soft text-primary">{{ $timelineItem['resourceCount'] }}</span>
                            </div>
                            <div class="session-info-row">
                                <span><i class="fas fa-edit me-1"></i> Kiểm tra</span>
                                <span class="badge bg-danger-soft text-danger">{{ $timelineItem['examCount'] }}</span>
                            </div>
                            <div class="session-info-row">
                                <span><i class="fas fa-user-check me-1"></i> Điểm danh GV</span>
                                <strong class="smaller text-{{ $timelineItem['attendanceStatus']['color'] }}">{{ $timelineItem['attendanceStatus']['label'] }}</strong>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <button type="button" class="btn btn-xs btn-outline-danger btn-add-test"
                                    data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}">
                                <i class="fas fa-file-signature me-1"></i> Tạo bài kiểm tra
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chi tiết Tài liệu & Bài kiểm tra --}}
            @if($lich->baiKiemTras->count() > 0 || $lich->taiNguyen->count() > 0)
                <div class="session-detail-grid">
                    <div class="row g-3">
                        {{-- Danh sách Tài liệu --}}
                        <div class="col-lg-6">
                            <div class="session-detail-card h-100 shadow-xs">
                                <div class="session-detail-card__title">
                                    <span class="bg-primary text-white p-1 rounded me-2" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                                        <i class="fas fa-folder-open"></i>
                                    </span>
                                    Tài liệu buổi học ({{ $lich->taiNguyen->count() }})
                                </div>

                                @if($lich->taiNguyen->count() > 0)
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($lich->taiNguyen->sortBy('thu_tu_hien_thi') as $tn)
                                            @php
                                                $taiNguyenUrl = $tn->link_ngoai ?: asset('storage/' . ltrim((string) $tn->duong_dan_file, '/'));
                                            @endphp
                                            <div class="resource-card">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="resource-card__icon bg-{{ $tn->loai_color }}-soft text-{{ $tn->loai_color }}">
                                                        <i class="fas {{ $tn->loai_icon }}"></i>
                                                    </div>
                                                    <div class="flex-grow-1 min-w-0">
                                                        <div class="fw-bold text-dark text-truncate small" title="{{ $tn->tieu_de }}">{{ $tn->tieu_de }}</div>
                                                        <div class="smaller text-muted mt-1">
                                                            {{ $tn->loai_label }} • STT {{ $tn->thu_tu_hien_thi }}
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-1 align-items-center ms-2 border-start ps-2">
                                                        <a href="{{ $taiNguyenUrl }}" target="_blank" class="btn btn-icon-xs text-primary" title="Mở link">
                                                            <i class="fas fa-external-link-alt"></i>
                                                        </a>
                                                        <form action="{{ route('giang-vien.buoi-hoc.tai-nguyen.toggle', $tn->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-icon-xs {{ $tn->trang_thai_hien_thi === 'hien' ? 'text-success' : 'text-secondary' }}" title="{{ $tn->trang_thai_hien_thi === 'hien' ? 'Đang hiện' : 'Đang ẩn' }}">
                                                                <i class="fas {{ $tn->trang_thai_hien_thi === 'hien' ? 'fa-toggle-on' : 'fa-toggle-off' }} fa-lg"></i>
                                                            </button>
                                                        </form>
                                                        <button type="button" class="btn btn-icon-xs text-warning btn-edit-resource"
                                                                data-id="{{ $tn->id }}" data-type="{{ $tn->loai_tai_nguyen }}"
                                                                data-title="{{ $tn->tieu_de }}" data-desc="{{ $tn->mo_ta }}"
                                                                data-link="{{ $tn->link_ngoai }}" data-status="{{ $tn->trang_thai_hien_thi }}"
                                                                data-order="{{ $tn->thu_tu_hien_thi }}" title="Sửa">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form action="{{ route('giang-vien.buoi-hoc.tai-nguyen.destroy', $tn->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa tài liệu này?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-icon-xs text-danger" title="Xóa">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted smaller italic">Chưa có tài liệu cho buổi học này.</div>
                                @endif
                            </div>
                        </div>

                        {{-- Danh sách Bài kiểm tra --}}
                        <div class="col-lg-6">
                            <div class="session-detail-card h-100 shadow-xs">
                                <div class="session-detail-card__title">
                                    <span class="bg-danger text-white p-1 rounded me-2" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                                        <i class="fas fa-file-alt"></i>
                                    </span>
                                    Bài kiểm tra ({{ $lich->baiKiemTras->count() }})
                                </div>

                                @if($lich->baiKiemTras->count() > 0)
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($lich->baiKiemTras as $test)
                                            <div class="exam-pill shadow-xs">
                                                <div class="min-w-0">
                                                    <div class="fw-bold text-danger text-truncate small" title="{{ $test->tieu_de }}">{{ $test->tieu_de }}</div>
                                                    <div class="smaller text-muted mt-1"><i class="far fa-clock me-1"></i>{{ $test->thoi_gian_lam_bai }} phút</div>
                                                </div>
                                                <div class="d-flex gap-1 border-start ps-2">
                                                    <a href="{{ route('giang-vien.bai-kiem-tra.edit', $test->id) }}" class="btn btn-icon-xs text-danger" title="Cấu hình">
                                                        <i class="fas fa-cog"></i>
                                                    </a>
                                                    <a href="{{ route('giang-vien.bai-kiem-tra.surveillance.edit', $test->id) }}" class="btn btn-icon-xs text-warning" title="Giám sát">
                                                        <i class="fas fa-shield-alt"></i>
                                                    </a>
                                                    <form action="{{ route('giang-vien.bai-kiem-tra.destroy', $test->id) }}" method="POST" onsubmit="return confirm('Xóa bài kiểm tra này?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-icon-xs text-secondary">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted smaller italic">Chưa có bài kiểm tra nào.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
