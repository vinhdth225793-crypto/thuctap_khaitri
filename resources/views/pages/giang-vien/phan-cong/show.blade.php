@extends('layouts.app')

@section('title', 'Chi ti?t b�i gi?ng: ' . $phanCong->moduleHoc->ten_module)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('giang-vien.khoa-hoc') }}">L? tr�nh d?y</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Chi ti?t b�i d?y</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm me-3" style="width: 50px; height: 50px;">
                    <i class="fas fa-book-open fa-lg"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $phanCong->moduleHoc->ten_module }}</h3>
                    <div class="text-muted small mt-1">
                        Thu?c kh�a h?c: <span class="fw-bold text-primary">{{ $khoaHoc->ten_khoa_hoc }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            @if($phanCong->trang_thai === 'cho_xac_nhan')
                <form action="{{ route('giang-vien.khoa-hoc.xac-nhan', $phanCong->id) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="hanh_dong" value="da_nhan">
                    <button type="submit" class="btn btn-success fw-bold px-4 shadow-sm">X�C NH?N D?Y</button>
                </form>
            @else
                <div class="badge bg-success-soft text-success border border-success px-3 py-2 shadow-sm">
                    <i class="fas fa-check-circle me-1"></i> B?n d� nh?n b�i d?y n�y
                </div>
            @endif
        </div>
    </div>

    @include('components.alert')

    <div class="row">
        <!-- C?t tr�i: L?ch d?y & N?i dung -->
        <div class="col-lg-8">
            {{-- L?CH D?Y CHI TI?T (D?NG KH?I) --}}
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="small fw-bold text-uppercase mb-0 text-primary">
                        <i class="fas fa-calendar-check me-2"></i> L? tr�nh gi?ng d?y
                    </h5>
                    <span class="badge bg-white text-primary border border-primary px-3 shadow-sm">{{ $lichDays->count() }} bu?i d?y</span>
                </div>

                @forelse($lichDays as $index => $lich)
                    <div class="session-block mb-4 shadow-sm border border-2 border-light-subtle rounded-3 overflow-hidden bg-white" style="border-left: 5px solid #0d6efd !important;">
                        {{-- Header c?a bu?i h?c --}}
                        <div class="session-header p-3 d-flex flex-wrap align-items-center justify-content-between bg-light border-bottom border-primary border-3 border-top-0 border-end-0 border-start-0">
                            <div class="d-flex align-items-center gap-3">
                                <div class="session-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                                    <span class="fw-bold fs-5">#{{ $lich->buoi_so }}</span>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark fs-6">{{ $lich->ngay_hoc->format('d/m/Y') }} ({{ $lich->thu_label }})</div>
                                    <div class="smaller text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        <span class="text-primary fw-bold">{{ \Carbon\Carbon::parse($lich->gio_bat_dau)->format('H:i') }} - {{ \Carbon\Carbon::parse($lich->gio_ket_thuc)->format('H:i') }}</span>
                                        <span class="mx-2 text-silver">|</span>
                                        <span class="badge bg-{{ $lich->trang_thai_color }}-soft text-dark border-0">
                                            {{ $lich->trang_thai_label }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-1 mt-2 mt-md-0">
                                <button type="button" class="btn btn-sm btn-outline-warning btn-icon-custom btn-edit-lich" 
                                        data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}"
                                        data-hinhthuc="{{ $lich->hinh_thuc }}" data-nentang="{{ $lich->nen_tang }}"
                                        data-link="{{ $lich->link_online }}" data-meetingid="{{ $lich->meeting_id }}"
                                        data-pass="{{ $lich->mat_khau_cuoc_hop }}" data-phong="{{ $lich->phong_hoc }}"
                                        title="C?p nh?t Link/Ph�ng">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <button type="button" class="btn btn-sm btn-outline-success btn-icon-custom btn-add-resource"
                                        data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}"
                                        title="�ang t�i li?u">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </button>

                                <button type="button" class="btn btn-sm btn-outline-primary btn-icon-custom btn-diem-danh"
                                        data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}"
                                        title="�i?m danh">
                                    <i class="fas fa-user-check"></i>
                                </button>

                                <button type="button" class="btn btn-sm btn-outline-danger btn-icon-custom btn-add-test"
                                        data-id="{{ $lich->id }}" data-buoi="{{ $lich->buoi_so }}"
                                        title="T?o b�i ki?m tra">
                                    <i class="fas fa-file-signature"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Body c?a bu?i h?c --}}
                        <div class="session-body p-3">
                            <div class="row g-3">
                                {{-- C?t tr�i: Th�ng tin d?a di?m --}}
                                <div class="col-md-12">
                                    <div class="p-2 rounded bg-light border-start border-4 border-info">
                                        @if($lich->hinh_thuc === 'online')
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-info fw-bold">ONLINE</span>
                                                    @if($lich->link_online)
                                                        <a href="{{ $lich->link_online }}" target="_blank" class="text-info fw-bold text-decoration-none small text-truncate d-block" style="max-width: 300px;">
                                                            <i class="fas fa-video me-1"></i> {{ $lich->link_online }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted italic small">Chưa cập nhật link họp</span>
                                                    @endif
                                                </div>
                                                @if($lich->link_online)
                                                    <button type="button" class="btn btn-xs btn-white border shadow-xs btn-copy-link" data-link="{{ $lich->link_online }}">
                                                        <i class="far fa-copy me-1"></i> Sao chép link
                                                    </button>
                                                @endif
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-dark fw-bold">TRỰC TIẾP</span>
                                                <span class="text-dark fw-bold small"><i class="fas fa-door-open me-1 text-muted"></i>Phòng: {{ $lich->phong_hoc ?: 'Chưa gán phòng' }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Khu v?c B�i ki?m tra --}}
                                @if($lich->baiKiemTras->count() > 0)
                                    <div class="col-12 mt-3">
                                        <div class="fw-bold small mb-2 text-danger text-uppercase"><i class="fas fa-file-alt me-1"></i> B�i ki?m tra</div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($lich->baiKiemTras as $test)
                                                <div class="p-2 rounded border border-danger border-opacity-25 bg-danger bg-opacity-10 d-flex align-items-center justify-content-between" style="min-width: 250px;">
                                                    <div class="me-3">
                                                        <div class="fw-bold smaller text-danger">{{ $test->tieu_de }}</div>
                                                        <div class="smaller text-muted">{{ $test->thoi_gian_lam_bai }} ph�t</div>
                                                    </div>
                                                    <div class="d-flex gap-1">
                                                        <a href="{{ route('giang-vien.bai-kiem-tra.edit', $test->id) }}" class="btn btn-xs btn-outline-danger p-1" title="Cau hinh de"><i class="fas fa-tasks"></i></a>
                                                        <form action="{{ route('giang-vien.bai-kiem-tra.destroy', $test->id) }}" method="POST" onsubmit="return confirm('X�a b�i ki?m tra n�y?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-xs btn-link p-1 text-danger"><i class="fas fa-trash-alt"></i></button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Khu v?c T�i li?u --}}
                                <div class="col-12 mt-3">
                                    <div class="fw-bold small mb-2 text-primary text-uppercase border-top pt-3"><i class="fas fa-folder-open me-1"></i> T�i li?u h?c t?p ({{ $lich->taiNguyen->count() }})</div>
                                    @if($lich->taiNguyen->count() > 0)
                                        <div class="row g-2">
                                            @foreach($lich->taiNguyen->sortBy('thu_tu_hien_thi') as $tn)
                                                <div class="col-md-6">
                                                    @php
                                                        $taiNguyenUrl = $tn->link_ngoai ?: asset('storage/' . ltrim((string) $tn->duong_dan_file, '/'));
                                                    @endphp
                                                    <div class="resource-card p-2 rounded border bg-white shadow-xs d-flex align-items-center hover-bg-light transition-all h-100">
                                                        {{-- Icon lo?i t�i li?u --}}
                                                        <div class="bg-{{ $tn->loai_color }}-soft text-{{ $tn->loai_color }} rounded d-flex align-items-center justify-content-center me-3 shadow-xs" style="width: 40px; height: 40px; flex-shrink: 0;">
                                                            <i class="fas {{ $tn->loai_icon }} fa-lg"></i>
                                                        </div>

                                                        {{-- N?i dung ti�u d? --}}
                                                        <div class="flex-grow-1 min-w-0 me-2">
                                                            <div class="fw-bold smaller text-dark text-truncate" title="{{ $tn->tieu_de }}">
                                                                {{ $tn->tieu_de }}
                                                            </div>
                                                            <div class="smaller d-flex align-items-center gap-2 mt-1">
                                                                @if($tn->trang_thai_hien_thi === 'an')
                                                                    <span class="badge bg-secondary-soft text-secondary border-0 px-2" style="font-size: 0.6rem;">
                                                                        <i class="fas fa-eye-slash me-1"></i> ?n
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-success-soft text-success border-0 px-2" style="font-size: 0.6rem;">
                                                                        <i class="fas fa-eye me-1"></i> Hi?n
                                                                    </span>
                                                                @endif
                                                                <span class="text-muted" style="font-size: 0.6rem;">
                                                                    <i class="fas fa-sort-numeric-down me-1"></i> STT: {{ $tn->thu_tu_hien_thi }}
                                                                </span>
                                                            </div>
                                                        </div>

                                                        {{-- Nh�m n�t ch?c nang --}}
                                                        <div class="d-flex gap-1 align-items-center flex-shrink-0 border-start ps-2">
                                                            <a href="{{ $taiNguyenUrl }}" target="_blank" class="btn btn-icon-xs text-primary" title="Xem file">
                                                                <i class="fas fa-link"></i>
                                                            </a>

                                                            {{-- Toggle Status --}}
                                                            <form action="{{ route('giang-vien.buoi-hoc.tai-nguyen.toggle', $tn->id) }}" method="POST" class="d-inline">
                                                                @csrf @method('PATCH')
                                                                <button type="submit" class="btn btn-icon-xs {{ $tn->trang_thai_hien_thi === 'hien' ? 'text-success' : 'text-secondary' }}" 
                                                                        title="{{ $tn->trang_thai_hien_thi === 'hien' ? '?n' : 'Hi?n' }}">
                                                                    <i class="fas {{ $tn->trang_thai_hien_thi === 'hien' ? 'fa-toggle-on' : 'fa-toggle-off' }} fa-lg"></i>
                                                                </button>
                                                            </form>

                                                            <button type="button" class="btn btn-icon-xs text-primary btn-preview-file" 
                                                                    data-url="{{ $taiNguyenUrl }}" data-title="{{ $tn->tieu_de }}" title="Xem">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            
                                                            <button type="button" class="btn btn-icon-xs text-warning btn-edit-resource"
                                                                    data-id="{{ $tn->id }}" data-type="{{ $tn->loai_tai_nguyen }}"
                                                                    data-title="{{ $tn->tieu_de }}" data-desc="{{ $tn->mo_ta }}"
                                                                    data-link="{{ $tn->link_ngoai }}" data-status="{{ $tn->trang_thai_hien_thi }}"
                                                                    data-order="{{ $tn->thu_tu_hien_thi }}" title="S?a">
                                                                <i class="fas fa-edit"></i>
                                                            </button>

                                                            <form action="{{ route('giang-vien.buoi-hoc.tai-nguyen.destroy', $tn->id) }}" method="POST" class="d-inline" onsubmit="return confirm('X�a t�i li?u n�y?')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-icon-xs text-danger" title="X�a">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-muted italic smaller py-2 ps-2 border-start border-3 ms-1">Chưa có tài liệu cho buổi học này.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="vip-card p-5 text-center text-muted border-0 shadow-sm">
                        <i class="fas fa-calendar-times fa-3x mb-3 opacity-25"></i>
                        <p class="mb-0">Chưa có lịch dạy cụ thể cho bài dạy này.</p>
                    </div>
                @endforelse
            </div>

           

            {{-- M� T? MODULE --}}
            <div class="vip-card mb-4 border-0 shadow-sm">
                <div class="vip-card-header bg-white border-bottom py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-dark">
                        <i class="fas fa-info-circle me-2 text-info"></i> M� t? n?i dung b�i d?y
                    </h5>
                </div>
                <div class="vip-card-body p-4">
                    <div class="bg-light p-3 rounded border border-dashed text-dark lh-lg">
                        {!! $phanCong->moduleHoc->mo_ta ? nl2br(e($phanCong->moduleHoc->mo_ta)) : '<span class="text-muted italic">Chưa có mô tả chi tiết cho bài học này.</span>' !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- C?t ph?i: Th�ng tin Kh�a h?c & H?c vi�n -->
        <div class="col-lg-4">
            {{-- CARD KH�A H?C --}}
            <div class="vip-card mb-4 shadow-sm border-0 overflow-hidden">
                <div class="vip-card-header bg-primary text-white py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0">Th�ng tin kh�a h?c</h5>
                </div>
                <div class="vip-card-body p-4">
                    <div class="text-center mb-3">
                        <div class="rounded border bg-light overflow-hidden mx-auto shadow-xs" style="width: 120px; height: 120px;">
                            <img src="{{ $khoaHoc->hinh_anh ? asset($khoaHoc->hinh_anh) : asset('images/default-course.svg') }}" 
                                 class="img-fluid w-100 h-100 object-fit-cover">
                        </div>
                    </div>
                    <div class="mb-3 text-center">
                        <span class="badge bg-info-soft text-info border border-info mb-2">{{ $khoaHoc->nhomNganh->ten_nhom_nganh ?? 'N/A' }}</span>
                        <h6 class="fw-bold text-dark mb-0">{{ $khoaHoc->ten_khoa_hoc }}</h6>
                        <code class="smaller">{{ $khoaHoc->ma_khoa_hoc }}</code>
                    </div>
                    <hr class="my-3">
                    <div class="row g-2 small">
                        <div class="col-6 text-muted">Tr�nh d?:</div>
                        <div class="col-6 text-end fw-bold">{{ ['co_ban'=>'Co b?n','trung_binh'=>'Trung b�nh','nang_cao'=>'N�ng cao'][$khoaHoc->cap_do] ?? 'N/A' }}</div>
                        
                        <div class="col-6 text-muted">Khai gi?ng:</div>
                        <div class="col-6 text-end fw-bold">{{ $khoaHoc->ngay_khai_giang?->format('d/m/Y') ?? '�' }}</div>
                        
                        <div class="col-6 text-muted">K?t th�c d? ki?n:</div>
                        <div class="col-6 text-end fw-bold">{{ $khoaHoc->ngay_ket_thuc?->format('d/m/Y') ?? '�' }}</div>
                    </div>
                </div>
            </div>

            {{-- CARD H?C VI�N (FLOW 3 - PHASE 1) --}}
            <div class="vip-card mb-4 shadow-sm border-0">
                <div class="vip-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-success">
                        <i class="fas fa-users me-2"></i> Danh s�ch h?c vi�n
                    </h5>
                    <span class="badge bg-success-soft text-success rounded-pill px-3">{{ $khoaHoc->hocVienKhoaHocs->count() }} HV</span>
                </div>
                <div class="vip-card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                        @forelse($khoaHoc->hocVienKhoaHocs as $bghv)
                            <div class="list-group-item px-3 py-3 hover-bg-light">
                                <div class="d-flex align-items-start">
                                    <div class="avatar-mini rounded-circle bg-info-soft text-info d-flex align-items-center justify-content-center me-3 shadow-xs" style="width: 40px; height: 40px; flex-shrink: 0;">
                                        <span class="fw-bold">{{ substr($bghv->hocVien->ho_ten, 0, 1) }}</span>
                                    </div>
                                    <div class="flex-fill min-w-0">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <div class="fw-bold text-dark text-truncate" title="{{ $bghv->hocVien->ho_ten }}">
                                                {{ $bghv->hocVien->ho_ten }}
                                            </div>
                                            <code class="smaller text-muted">#{{ $bghv->hocVien->ma_nguoi_dung }}</code>
                                        </div>
                                        
                                        <div class="smaller text-muted d-flex flex-column gap-1">
                                            <span class="text-truncate"><i class="far fa-envelope me-1"></i>{{ $bghv->hocVien->email }}</span>
                                            <span><i class="fas fa-phone-alt me-1"></i>{{ $bghv->hocVien->so_dien_thoai ?: 'Chưa cập nhật' }}</span>
                                            <span class="fw-bold"><i class="far fa-calendar-alt me-1"></i>Tham gia: {{ $bghv->ngay_tham_gia?->format('d/m/Y') ?? 'N/A' }}</span>
                                            
                                            {{-- Th?ng k� chuy�n c?n (Phase 2) --}}
                                            @php
                                                $myDiemDanh = $bghv->hocVien->diemDanhs->whereIn('lich_hoc_id', $lichHocIds);
                                                $coMat = $myDiemDanh->where('trang_thai', 'co_mat')->count();
                                                $vang  = $myDiemDanh->where('trang_thai', 'vang_mat')->count();
                                                $tre   = $myDiemDanh->where('trang_thai', 'vao_tre')->count();
                                            @endphp
                                            <div class="d-flex gap-2 mt-1 flex-wrap">
                                                <span class="badge bg-success-soft text-success border-0 px-2" style="font-size: 0.6rem;" title="C� m?t">
                                                    <i class="fas fa-check-circle me-1"></i>{{ $coMat }}
                                                </span>
                                                <span class="badge bg-danger-soft text-danger border-0 px-2" style="font-size: 0.6rem;" title="V?ng m?t">
                                                    <i class="fas fa-times-circle me-1"></i>{{ $vang }}
                                                </span>
                                                <span class="badge bg-warning-soft text-warning border-0 px-2" style="font-size: 0.6rem;" title="V�o tr?">
                                                    <i class="fas fa-clock me-1"></i>{{ $tre }}
                                                </span>
                                            </div>

                                            @if($bghv->ghi_chu)
                                                <span class="text-warning italic mt-1 border-start border-warning ps-2">
                                                    <i class="fas fa-comment-dots me-1"></i> {{ $bghv->ghi_chu }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="ms-2 text-end">
                                        <span class="badge {{ $bghv->trang_thai_badge }} shadow-xs mb-1" style="font-size: 0.65rem;">
                                            {{ $bghv->trang_thai_label }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small italic">
                                <i class="fas fa-users-slash fa-2x mb-2 d-block opacity-25"></i>
                                Chưa có học viên nào ghi danh vào lớp này.
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="vip-card-footer bg-light p-3 text-center border-top">
                    <button type="button" class="btn btn-sm btn-outline-primary fw-bold w-100" data-bs-toggle="modal" data-bs-target="#modalRequestStudent">
                        <i class="fas fa-user-edit me-1"></i> Y�u c?u thay d?i h?c vi�n
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL �I?M DANH (PHASE 7) --}}
<div class="modal fade shadow" id="modalDiemDanh" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-check me-2"></i> �i?m danh bu?i <span id="dd-buoi-label"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <!-- Nav Tabs trong Modal -->
            <ul class="nav nav-tabs nav-fill bg-light" id="diemDanhModalTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold small py-3" id="tab-attendance-list" data-bs-toggle="tab" data-bs-target="#attendance-pane" type="button" role="tab">
                        <i class="fas fa-list me-1"></i> DANH S�CH �I?M DANH
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold small py-3" id="tab-report-admin" data-bs-toggle="tab" data-bs-target="#report-pane" type="button" role="tab">
                        <i class="fas fa-paper-plane me-1"></i> B�O C�O G?I ADMIN
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Tab 1: Danh s�ch di?m danh -->
                <div class="tab-pane fade show active" id="attendance-pane" role="tabpanel">
                    <form id="formDiemDanh" method="POST" action="">
                        @csrf
                        <div class="modal-body p-0">
                            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                                <span class="small text-muted fw-bold">Ng�y d?y: <span id="dd-ngay-label" class="text-dark"></span></span>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-xs btn-outline-success" onclick="checkAllAttendance('co_mat')">T?t c? C� m?t</button>
                                </div>
                            </div>
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light sticky-top shadow-xs">
                                        <tr>
                                            <th class="ps-4">H?c vi�n</th>
                                            <th class="text-center" width="120">Tr?ng th�i</th>
                                            <th class="pe-4">Ghi ch�</th>
                                        </tr>
                                    </thead>
                                    <tbody id="attendance-list">
                                        {{-- Load b?ng JS --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-3 justify-content-center gap-2 bg-light">
                            <button type="button" class="btn btn-light px-4 fw-bold shadow-xs" data-bs-dismiss="modal">H?Y B?</button>
                            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">LUU �I?M DANH</button>
                        </div>
                    </form>
                </div>

                <!-- Tab 2: B�o c�o g?i Admin -->
                <div class="tab-pane fade" id="report-pane" role="tabpanel">
                    <form id="formBaoCao" method="POST" action="">
                        @csrf
                        <div class="modal-body p-4">
                            <div id="report-status-badge" class="mb-3 text-center"></div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nội dung báo cáo buổi dạy *</label>
                                <textarea name="bao_cao_giang_vien" id="dd-bao-cao-content" class="form-control vip-form-control" rows="8" 
                                          placeholder="Nhập nội dung báo cáo cho Admin (VD: Tình hình lớp học, các vấn đề phát sinh, nhận xét chung về buổi học...)" required></textarea>
                            </div>
                            
                            <div class="alert alert-warning border-0 small mb-0">
                                <i class="fas fa-info-circle me-1"></i> 
                                <b>Lưu ý:</b> Báo cáo này sẽ được gửi trực tiếp đến Ban quản lý. Vui lòng kiểm tra kỹ nội dung trước khi gửi.
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-3 justify-content-center gap-2 bg-light">
                            <button type="button" class="btn btn-light px-4 fw-bold shadow-xs" data-bs-dismiss="modal">H?Y B?</button>
                            <button type="submit" id="btn-submit-report" class="btn btn-success px-4 fw-bold shadow-sm">G?I B�O C�O CHO ADMIN</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL Y�U C?U H?C VI�N (PHASE 6) --}}
<div class="modal fade shadow" id="modalRequestStudent" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2"></i> G?i y�u c?u v? h?c vi�n</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('giang-vien.khoa-hoc.gui-yeu-cau-hoc-vien', $khoaHoc->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Lo?i y�u c?u *</label>
                        <select name="loai_yeu_cau" id="req-type" class="form-select vip-form-control" required>
                            <option value="them">Th�m h?c vi�n m?i v�o l?p</option>
                            <option value="xoa">X�a h?c vi�n kh?i l?p</option>
                            <option value="sua">Thay d?i tr?ng th�i h?c vi�n</option>
                        </select>
                    </div>

                    <div id="req-group-add">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">H? t�n h?c vi�n *</label>
                            <input type="text" name="ten_hoc_vien" class="form-control vip-form-control" placeholder="Nh?p d?y d? h? t�n">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Email h?c vi�n *</label>
                            <input type="email" name="email_hoc_vien" class="form-control vip-form-control" placeholder="Nh?p email d? h? th?ng d?nh danh">
                        </div>
                    </div>

                    <div id="req-group-select" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Ch?n h?c vi�n t? danh s�ch l?p *</label>
                            <select name="hoc_vien_id" class="form-select vip-form-control">
                                <option value="">-- Ch?n h?c vi�n --</option>
                                @foreach($khoaHoc->hocVienKhoaHocs as $bghv)
                                    <option value="{{ $bghv->hocVien->ma_nguoi_dung }}">
                                        {{ $bghv->hocVien->ho_ten }} (#{{ $bghv->hocVien->ma_nguoi_dung }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold">L� do & N?i dung chi ti?t *</label>
                        <textarea name="ly_do" class="form-control vip-form-control" rows="4" placeholder="Gi?i th�ch chi ti?t y�u c?u c?a b?n..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">H?y b?</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm border-0">G?I Y�U C?U</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL �ANG T�I NGUY�N (PHASE 4) --}}
<div class="modal fade shadow" id="modalAddResource" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-cloud-upload-alt me-2"></i> �ang t�i li?u bu?i <span id="res-buoi-label"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAddResource" method="POST" action="" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="selected_lich_hoc_id" id="selected-lich-hoc-id">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Lo?i t�i nguy�n *</label>
                            <select name="loai_tai_nguyen" class="form-select vip-form-control" required>
                                <option value="bai_giang">B�i gi?ng (Slide/Video)</option>
                                <option value="tai_lieu">T�i li?u tham kh?o</option>
                                <option value="bai_tap">B�i t?p v? nh�</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Th? t? hi?n th?</label>
                            <input type="number" name="thu_tu_hien_thi" class="form-control vip-form-control" value="0" min="0">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">Ti�u d? t�i li?u *</label>
                        <input type="text" name="tieu_de" class="form-control vip-form-control" placeholder="VD: Slide bu?i 1 - Gi?i thi?u Laravel" required>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">M� t? ng?n</label>
                        <textarea name="mo_ta" class="form-control vip-form-control" rows="2" placeholder="T�m t?t n?i dung t�i li?u..."></textarea>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">Link ngoài (Youtube/Drive/...)</label>
                        <input type="url" name="link_ngoai" class="form-control vip-form-control" placeholder="https://...">
                        <div class="smaller text-muted mt-1 italic">Nhập link ngoài nếu không tải file lên.</div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">Tải lên file (Tối đa 10MB)</label>
                        <input type="file" name="file_dinh_kem" id="add-res-file" class="form-control vip-form-control">
                        <div class="smaller text-muted mt-1 italic">Hỗ trợ: PDF, Word, PowerPoint, Excel, ZIP, RAR</div>
                    </div>

                    <div class="mt-3 p-3 bg-light rounded border border-primary-soft">
                        <label class="form-label small fw-bold d-block mb-2 text-primary"><i class="fas fa-cog me-1"></i> T�y ch?n luu tr?</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="save_format" id="save_original" value="original" checked>
                            <label class="form-check-label small" for="save_original">
                                <b>Gi? nguy�n file g?c:</b> Luu d�ng d?nh d?ng b?n t?i l�n.
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="save_format" id="save_pdf" value="pdf">
                            <label class="form-check-label small" for="save_pdf">
                                <b>Chuy?n sang PDF:</b> Gi�p h?c vi�n xem tr?c ti?p ngay tr�n web (Khuy�n d�ng).
                            </label>
                        </div>
                        <div id="pdf-warning" class="mt-2 smaller text-danger d-none italic">
                            <i class="fas fa-exclamation-triangle me-1"></i> 
                            H? th?ng dang ch?y Local, b?n n�n t? chuy?n file sang PDF tru?c khi t?i l�n d? d?m b?o hi?n th? t?t nh?t.
                        </div>
                    </div>

                    <div class="mt-3 p-3 bg-light rounded border">
                        <label class="form-label small fw-bold d-block mb-2">Tr?ng th�i c�ng khai</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="trang_thai_hien_thi" id="status_an" value="an" checked>
                            <label class="form-check-label small" for="status_an">Luu nh�p (?n v?i h?c vi�n)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="trang_thai_hien_thi" id="status_hien" value="hien">
                            <label class="form-check-label small" for="status_hien">C�ng khai ngay</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold shadow-xs" data-bs-dismiss="modal">H?Y B?</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm">�ANG T�I NGUY�N</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL S?A T�I NGUY�N (PHASE 4) --}}
<div class="modal fade shadow" id="modalEditResource" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-warning text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i> Ch?nh s?a t�i nguy�n</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditResource" method="POST" action="" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Lo?i t�i nguy�n *</label>
                            <select name="loai_tai_nguyen" id="edit-res-type" class="form-select vip-form-control" required>
                                <option value="bai_giang">B�i gi?ng (Slide/Video)</option>
                                <option value="tai_lieu">T�i li?u tham kh?o</option>
                                <option value="bai_tap">B�i t?p v? nh�</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Th? t? hi?n th?</label>
                            <input type="number" name="thu_tu_hien_thi" id="edit-res-order" class="form-control vip-form-control" min="0">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">Ti�u d? t�i li?u *</label>
                        <input type="text" name="tieu_de" id="edit-res-title" class="form-control vip-form-control" required>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">M� t? ng?n</label>
                        <textarea name="mo_ta" id="edit-res-desc" class="form-control vip-form-control" rows="2"></textarea>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">Link ngo�i (Youtube/Drive/...)</label>
                        <input type="url" name="link_ngoai" id="edit-res-link" class="form-control vip-form-control">
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold">Thay d?i file (�? tr?ng n?u gi? nguy�n)</label>
                        <input type="file" name="file_dinh_kem" id="edit-res-file" class="form-control vip-form-control">
                    </div>

                    <div class="mt-3 p-3 bg-light rounded border border-warning-soft">
                        <label class="form-label small fw-bold d-block mb-2 text-warning"><i class="fas fa-cog me-1"></i> T�y ch?n luu tr?</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="save_format" id="edit_save_original" value="original" checked>
                            <label class="form-check-label small" for="edit_save_original">
                                <b>Gi? nguy�n file g?c</b>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="save_format" id="edit_save_pdf" value="pdf">
                            <label class="form-check-label small" for="edit_save_pdf">
                                <b>Chuy?n sang PDF</b>
                            </label>
                        </div>
                        <div id="edit-pdf-warning" class="mt-2 smaller text-danger d-none italic">
                            <i class="fas fa-exclamation-triangle me-1"></i> 
                            B?n n�n t? chuy?n file sang PDF tru?c khi t?i l�n.
                        </div>
                    </div>

                    <div class="mt-3 p-3 bg-light rounded border">
                        <label class="form-label small fw-bold d-block mb-2">Tr?ng th�i c�ng khai</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="trang_thai_hien_thi" id="edit-status-an" value="an">
                            <label class="form-check-label small" for="edit-status-an">Luu nh�p</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="trang_thai_hien_thi" id="edit-status-hien" value="hien">
                            <label class="form-check-label small" for="edit-status-hien">C�ng khai</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold shadow-xs" data-bs-dismiss="modal">H?Y B?</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold shadow-sm text-white">LUU THAY �?I</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL C?P NH?T LINK ONLINE / PH�NG H?C --}}
<div class="modal fade shadow" id="modalEditLich" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-warning text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i> C?p nh?t bu?i h?c <span id="edit-buoi-label"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditLich" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">H�nh th?c d?y h?c *</label>
                        <select name="hinh_thuc" id="edit-hinh-thuc" class="form-select vip-form-control" required>
                            <option value="truc_tiep">Tr?c ti?p (T?i trung t�m)</option>
                            <option value="online">Online (Qua link h?p)</option>
                        </select>
                    </div>

                    {{-- Nh�m tru?ng cho Online --}}
                    <div id="group-online" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">N?n t?ng h?c Online</label>
                            <select name="nen_tang" id="edit-nen-tang" class="form-select vip-form-control">
                                <option value="">-- Ch?n n?n t?ng --</option>
                                <option value="Zoom">Zoom</option>
                                <option value="Google Meet">Google Meet</option>
                                <option value="Microsoft Teams">Microsoft Teams</option>
                                <option value="Kh�c">Kh�c</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Link h?c Online (URL) *</label>
                            <input type="url" name="link_online" id="edit-link" class="form-control vip-form-control" placeholder="https://zoom.us/j/...">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Meeting ID</label>
                                <input type="text" name="meeting_id" id="edit-meeting-id" class="form-control vip-form-control" placeholder="123 456 789">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">M?t kh?u cu?c h?p</label>
                                <input type="text" name="mat_khau_cuoc_hop" id="edit-pass" class="form-control vip-form-control" placeholder="abcdef">
                            </div>
                        </div>
                    </div>

                    <div id="group-offline" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Phòng học *</label>
                            <input type="text" name="phong_hoc" id="edit-phong" class="form-control vip-form-control" placeholder="VD: Phòng 101, Tầng 2">
                        </div>
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="apply_to_all_online" value="1" id="applyAll">
                        <label class="form-check-label small text-primary fw-bold" for="applyAll">
                            �p d?ng link n�y cho t?t c? bu?i Online c?a kh�a
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">H?y b?</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold shadow-sm text-white border-0">C?P NH?T</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL T?O B�I KI?M TRA (PHASE 8) --}}
<div class="modal fade shadow" id="modalAddTest" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-signature me-2"></i> T?o b�i ki?m tra bu?i <span id="test-buoi-label"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('giang-vien.bai-kiem-tra.store') }}" method="POST">
                @csrf
                <input type="hidden" name="khoa_hoc_id" value="{{ $khoaHoc->id }}">
                <input type="hidden" name="module_hoc_id" value="{{ $phanCong->module_hoc_id }}">
                <input type="hidden" name="lich_hoc_id" id="test-lich-id">
                <input type="hidden" name="pham_vi" value="buoi_hoc">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Ti�u d? b�i ki?m tra *</label>
                        <input type="text" name="tieu_de" class="form-control vip-form-control" placeholder="VD: Ki?m tra nhanh bu?i 1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Th?i gian l�m b�i (Ph�t) *</label>
                        <input type="number" name="thoi_gian_lam_bai" class="form-control vip-form-control" value="15" min="1" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Ghi ch� / M� t?</label>
                        <textarea name="mo_ta" class="form-control vip-form-control" rows="2" placeholder="N?i dung t�m t?t..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">H?y b?</button>
                    <button type="submit" class="btn btn-danger px-4 fw-bold shadow-sm">T?O B�I KI?M TRA</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL PREVIEW T�I LI?U (PHASE 4 UPGRADE) --}}
<div class="modal fade shadow" id="modalPreviewResource" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                        <i class="fas fa-eye fa-sm"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="preview-title">Xem tru?c t�i li?u</h5>
                        <small class="text-light opacity-75" id="preview-filename"></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-secondary bg-opacity-10" style="height: 80vh; overflow: hidden;">
                <div id="preview-container" class="w-100 h-100 d-flex align-items-center justify-content-center">
                    {{-- N?i dung preview s? du?c load b?ng JS --}}
                    <div class="text-center p-5" id="preview-loading">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="text-muted fw-bold">�ang t?i n?i dung...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-0 py-3 justify-content-between">
                <div class="text-muted small italic">
                    <i class="fas fa-info-circle me-1"></i> 
                    N?u kh�ng xem du?c tr?c ti?p, vui l�ng b?m <b>"T?i v?"</b> ho?c <b>"M? tab m?i"</b>.
                </div>
                <div class="d-flex gap-2">
                    <a id="preview-open-btn" href="#" target="_blank" class="btn btn-outline-dark fw-bold px-4">
                        <i class="fas fa-external-link-alt me-1"></i> M? TRONG TAB M?I
                    </a>
                    <a id="preview-download-btn" href="#" download class="btn btn-success fw-bold px-4 shadow-sm">
                        <i class="fas fa-download me-1"></i> T?I V? M�Y
                    </a>
                    <button type="button" class="btn btn-light border fw-bold px-4" data-bs-dismiss="modal">��NG</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CHI TI?T T�I LI?U (PHASE 4) --}}
<div class="modal fade shadow" id="modalViewResource" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div id="res-detail-header" class="modal-header text-white border-0">
                <h5 class="modal-title fw-bold"><i id="res-detail-icon" class="fas me-2"></i> Chi ti?t t�i li?u</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <label class="smaller text-muted d-block mb-1">Ti�u d?</label>
                    <h5 id="res-detail-title" class="fw-bold text-dark mb-0"></h5>
                    <span id="res-detail-badge" class="badge mt-2 border-0 smaller py-1 px-2"></span>
                </div>

                <div class="mb-4">
                    <label class="smaller text-muted d-block mb-1">M� t?</label>
                    <div id="res-detail-desc" class="p-3 bg-light rounded border small text-dark lh-base"></div>
                </div>

                <div class="mb-4">
                    <label class="smaller text-muted d-block mb-1">URL truy c?p (Public URL)</label>
                    <div class="input-group">
                        <input type="text" id="res-detail-url" class="form-control form-control-sm bg-white" readonly>
                        <button class="btn btn-sm btn-outline-primary fw-bold" type="button" id="btn-copy-res-url">
                            <i class="far fa-copy me-1"></i> Copy
                        </button>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="smaller text-muted d-block mb-1">�u?ng d?n th?c t? tr�n Server (Debug)</label>
                    <code id="res-detail-path" class="d-block p-2 bg-dark text-light rounded smaller text-break"></code>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 justify-content-center gap-2 bg-light">
                <a id="res-detail-open" href="#" target="_blank" class="btn btn-primary px-4 fw-bold shadow-sm">
                    <i class="fas fa-external-link-alt me-1"></i> M? T�I LI?U
                </a>
                <a id="res-detail-download" href="#" download class="btn btn-success px-4 fw-bold shadow-sm">
                    <i class="fas fa-download me-1"></i> T?I V?
                </a>
                <button type="button" class="btn btn-light px-4 fw-bold shadow-xs border" data-bs-dismiss="modal">��NG</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Logic c?nh b�o PDF cho Modal Th�m
    const addFileInp = document.getElementById('add-res-file');
    const addPdfRadio = document.getElementById('save_pdf');
    const addPdfWarning = document.getElementById('pdf-warning');

    function checkAddPdfSupport() {
        if (addPdfRadio.checked && addFileInp.files.length > 0) {
            const ext = addFileInp.files[0].name.split('.').pop().toLowerCase();
            if (ext !== 'pdf' && !['jpg', 'jpeg', 'png'].includes(ext)) {
                addPdfWarning.classList.remove('d-none');
            } else {
                addPdfWarning.classList.add('d-none');
            }
        } else {
            addPdfWarning.classList.add('d-none');
        }
    }

    if (addFileInp) {
        addFileInp.addEventListener('change', checkAddPdfSupport);
        document.querySelectorAll('input[name="save_format"]').forEach(r => r.addEventListener('change', checkAddPdfSupport));
    }

    // Logic c?nh b�o PDF cho Modal S?a
    const editFileInp = document.getElementById('edit-res-file');
    const editPdfRadio = document.getElementById('edit_save_pdf');
    const editPdfWarning = document.getElementById('edit-pdf-warning');

    function checkEditPdfSupport() {
        if (editPdfRadio.checked && editFileInp.files.length > 0) {
            const ext = editFileInp.files[0].name.split('.').pop().toLowerCase();
            if (ext !== 'pdf' && !['jpg', 'jpeg', 'png'].includes(ext)) {
                editPdfWarning.classList.remove('d-none');
            } else {
                editPdfWarning.classList.add('d-none');
            }
        } else {
            editPdfWarning.classList.add('d-none');
        }
    }

    if (editFileInp) {
        editFileInp.addEventListener('change', checkEditPdfSupport);
        document.querySelectorAll('#modalEditResource input[name="save_format"]').forEach(r => r.addEventListener('change', checkEditPdfSupport));
    }

    // X? l� Modal Xem chi ti?t t�i nguy�n
    const modalViewRes = new bootstrap.Modal(document.getElementById('modalViewResource'));
    const btnCopyUrl = document.getElementById('btn-copy-res-url');

    document.querySelectorAll('.btn-view-resource').forEach(btn => {
        btn.addEventListener('click', function() {
            const d = this.dataset;
            
            // �? d? li?u
            document.getElementById('res-detail-title').textContent = d.title;
            document.getElementById('res-detail-desc').innerHTML = d.desc.replace(/\n/g, '<br>');
            document.getElementById('res-detail-url').value = d.url;
            document.getElementById('res-detail-path').textContent = d.path;
            
            // UI
            const badge = document.getElementById('res-detail-badge');
            badge.textContent = d.loai;
            badge.className = `badge mt-2 border-0 smaller py-1 px-2 bg-${d.color}-soft text-${d.color}`;
            
            const header = document.getElementById('res-detail-header');
            header.className = `modal-header text-white border-0 bg-${d.color}`;
            
            const icon = document.getElementById('res-detail-icon');
            icon.className = `fas ${d.icon} me-2`;

            // Link actions
            const openBtn = document.getElementById('res-detail-open');
            openBtn.href = d.url;
            
            const downloadBtn = document.getElementById('res-detail-download');
            if (d.downloadable === 'true') {
                downloadBtn.style.display = 'inline-block';
                downloadBtn.href = d.url;
                downloadBtn.setAttribute('download', d.filename);
            } else {
                downloadBtn.style.display = 'none';
            }

            modalViewRes.show();
        });
    });

    // Copy URL trong modal
    if (btnCopyUrl) {
        btnCopyUrl.addEventListener('click', function() {
            const urlInput = document.getElementById('res-detail-url');
            urlInput.select();
            document.execCommand('copy');
            
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check me-1"></i> �� Copy';
            this.classList.replace('btn-outline-primary', 'btn-success');
            
            setTimeout(() => {
                this.innerHTML = originalText;
                this.classList.replace('btn-success', 'btn-outline-primary');
            }, 2000);
        });
    }

    // X? l� Copy Link
    document.querySelectorAll('.btn-copy-link').forEach(btn => {
        btn.addEventListener('click', function() {
            const link = this.dataset.link;
            if (!link) return;
            navigator.clipboard.writeText(link).then(() => {
                const icon = this.querySelector('i');
                const originalClass = icon.className;
                icon.className = 'fas fa-check text-success';
                this.classList.add('btn-success', 'bg-opacity-10');
                setTimeout(() => {
                    icon.className = originalClass;
                    this.classList.remove('btn-success', 'bg-opacity-10');
                }, 2000);
            });
        });
    });

    // X? l� Modal S?a l?ch
    const modalEdit = new bootstrap.Modal(document.getElementById('modalEditLich'));
    const formEdit = document.getElementById('formEditLich');
    const hinhThucSelect = document.getElementById('edit-hinh-thuc');
    const groupOnline = document.getElementById('group-online');
    const groupOffline = document.getElementById('group-offline');

    function toggleHinhThuc() {
        if (hinhThucSelect.value === 'online') {
            groupOnline.style.display = 'block';
            groupOffline.style.display = 'none';
        } else {
            groupOnline.style.display = 'none';
            groupOffline.style.display = 'block';
        }
    }

    hinhThucSelect.addEventListener('change', toggleHinhThuc);

    document.querySelectorAll('.btn-edit-lich').forEach(btn => {
        btn.addEventListener('click', function() {
            const d = this.dataset;
            document.getElementById('edit-buoi-label').textContent = `#${d.buoi}`;
            document.getElementById('edit-hinh-thuc').value = d.hinhthuc;
            document.getElementById('edit-nen-tang').value = d.nentang || '';
            document.getElementById('edit-link').value = d.link || '';
            document.getElementById('edit-meeting-id').value = d.meetingid || '';
            document.getElementById('edit-pass').value = d.pass || '';
            document.getElementById('edit-phong').value = d.phong || '';
            
            formEdit.action = "{{ route('giang-vien.buoi-hoc.update-link', ':id') }}".replace(':id', d.id);
            toggleHinhThuc();
            modalEdit.show();
        });
    });

    // X? l� Modal �ang t�i nguy�n
    const modalRes = new bootstrap.Modal(document.getElementById('modalAddResource'));
    const formRes = document.getElementById('formAddResource');
    const selectedLichHocInput = document.getElementById('selected-lich-hoc-id');

    document.querySelectorAll('.btn-add-resource').forEach(btn => {
        btn.addEventListener('click', function() {
            const d = this.dataset;
            formRes.reset();
            document.getElementById('res-buoi-label').textContent = d.buoi;
            if (selectedLichHocInput) {
                selectedLichHocInput.value = d.id;
            }
            formRes.action = "{{ route('giang-vien.buoi-hoc.tai-nguyen.store', ':id') }}".replace(':id', d.id);
            modalRes.show();
        });
    });

    // X? l� Modal S?a t�i nguy�n (PHASE 4)
    const modalEditRes = new bootstrap.Modal(document.getElementById('modalEditResource'));
    const formEditRes = document.getElementById('formEditResource');

    document.querySelectorAll('.btn-edit-resource').forEach(btn => {
        btn.addEventListener('click', function() {
            const d = this.dataset;
            document.getElementById('edit-res-type').value = d.type;
            document.getElementById('edit-res-title').value = d.title;
            document.getElementById('edit-res-desc').value = d.desc || '';
            document.getElementById('edit-res-link').value = d.link || '';
            document.getElementById('edit-res-order').value = d.order || 0;
            
            // Set radio status
            if (d.status === 'hien') {
                document.getElementById('edit-status-hien').checked = true;
            } else {
                document.getElementById('edit-status-an').checked = true;
            }

            formEditRes.action = "{{ route('giang-vien.buoi-hoc.tai-nguyen.update', ':id') }}".replace(':id', d.id);
            modalEditRes.show();
        });
    });

    // X? l� Modal Y�u c?u h?c vi�n
    const reqTypeSelect = document.getElementById('req-type');
    const groupAdd = document.getElementById('req-group-add');
    const groupSelect = document.getElementById('req-group-select');

    if (reqTypeSelect) {
        reqTypeSelect.addEventListener('change', function() {
            if (this.value === 'them') {
                groupAdd.style.display = 'block';
                groupSelect.style.display = 'none';
            } else {
                groupAdd.style.display = 'none';
                groupSelect.style.display = 'block';
            }
        });
    }

    const modalDD = new bootstrap.Modal(document.getElementById('modalDiemDanh'));
    const formDD = document.getElementById('formDiemDanh');
    const attendanceList = document.getElementById('attendance-list');
    const btnSaveDD = document.querySelector('#modalDiemDanh button[type="submit"]');

    document.querySelectorAll('.btn-diem-danh').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            document.getElementById('dd-buoi-label').textContent = this.dataset.buoi;
            formDD.action = "{{ route('giang-vien.buoi-hoc.diem-danh.store', ':id') }}".replace(':id', id);
            
            // Hi?n th? loading
            attendanceList.innerHTML = '<tr><td colspan="3" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div> �ang t?i danh s�ch...</td></tr>';
            if (btnSaveDD) btnSaveDD.style.display = 'none';
            modalDD.show();

            // Load danh s�ch di?m danh hi?n t?i b?ng AJAX
            const fetchUrl = "{{ route('giang-vien.buoi-hoc.diem-danh.show', ':id') }}".replace(':id', id);
            
            // C?p nh?t action cho form b�o c�o
            document.getElementById('formBaoCao').action = "{{ route('giang-vien.buoi-hoc.diem-danh.report', ':id') }}".replace(':id', id);

            fetch(fetchUrl)
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        document.getElementById('dd-ngay-label').textContent = res.ngay;
                        
                        // �? d? li?u b�o c�o
                        const baoCaoContent = document.getElementById('dd-bao-cao-content');
                        const statusBadge = document.getElementById('report-status-badge');
                        const btnSubmitReport = document.getElementById('btn-submit-report');
                        
                        baoCaoContent.value = res.bao_cao || '';
                        
                        if (res.trang_thai_bao_cao === 'da_bao_cao') {
                            statusBadge.innerHTML = '<span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 fw-bold"><i class="fas fa-check-circle me-1"></i> ĐÃ GỬI BÁO CÁO</span>';
                            btnSubmitReport.innerHTML = '<i class="fas fa-sync-alt me-1"></i> CẬP NHẬT BÁO CÁO';
                            btnSubmitReport.classList.replace('btn-success', 'btn-warning');
                        } else {
                            statusBadge.innerHTML = '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-3 py-2 fw-bold"><i class="fas fa-clock me-1"></i> CHƯA GỬI BÁO CÁO</span>';
                            btnSubmitReport.innerHTML = 'GỬI BÁO CÁO CHO ADMIN';
                            btnSubmitReport.classList.replace('btn-warning', 'btn-success');
                        }

                        if (res.data.length > 0) {
                            if (btnSaveDD) btnSaveDD.style.display = 'block';
                            let html = '';
                            res.data.forEach((hv, i) => {
                                html += `
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark small">${hv.ho_ten}</div>
                                            <code class="smaller">#${hv.ma_nguoi_dung}</code>
                                            <input type="hidden" name="attendance[${i}][hoc_vien_id]" value="${hv.ma_nguoi_dung}">
                                        </td>
                                        <td class="text-center">
                                            <select name="attendance[${i}][trang_thai]" class="form-select form-select-sm att-select">
                                                <option value="" ${!hv.trang_thai ? 'selected' : ''}>Chon trang thai</option>
                                                <option value="co_mat" ${hv.trang_thai === 'co_mat' ? 'selected' : ''}>C� m?t</option>
                                                <option value="vang_mat" ${hv.trang_thai === 'vang_mat' ? 'selected' : ''}>V?ng</option>
                                                <option value="vao_tre" ${hv.trang_thai === 'vao_tre' ? 'selected' : ''}>Tr?</option>
                                            </select>
                                        </td>
                                        <td class="pe-4">
                                            <input type="text" name="attendance[${i}][ghi_chu]" value="${hv.ghi_chu || ''}" class="form-control form-control-sm" placeholder="...">
                                        </td>
                                    </tr>
                                `;
                            });
                            attendanceList.innerHTML = html;
                        } else {
                            attendanceList.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted">Kh�ng c� h?c vi�n n�o trong kh�a h?c n�y.</td></tr>';
                            if (btnSaveDD) btnSaveDD.style.display = 'none';
                        }
                    }
                })
                .catch(err => {
                    console.error('Attendance Load Error:', err);
                    attendanceList.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-danger">Kh�ng th? t?i danh s�ch. Vui l�ng th? l?i.</td></tr>';
                    if (btnSaveDD) btnSaveDD.style.display = 'none';
                });
        });
    });

    // H�m ch?n t?t c? c� m?t
    window.checkAllAttendance = function(status) {
        document.querySelectorAll('.att-select').forEach(select => {
            select.value = status;
        });
    }

    // X? l� Modal T?O B�I KI?M TRA
    const modalAddTestElement = document.getElementById('modalAddTest');
    if (modalAddTestElement) {
        const modalTest = new bootstrap.Modal(modalAddTestElement);
        document.querySelectorAll('.btn-add-test').forEach(btn => {
            btn.addEventListener('click', function() {
                const d = this.dataset;
                document.getElementById('test-buoi-label').textContent = d.buoi;
                document.getElementById('test-lich-id').value = d.id;
                modalTest.show();
            });
        });
    }

    // ==========================================
    // X? L� PREVIEW T�I LI?U (PHASE 4 UPGRADE)
    // ==========================================
    const modalPreviewElement = document.getElementById('modalPreviewResource');
    if (modalPreviewElement) {
        const modalPreview = new bootstrap.Modal(modalPreviewElement);
        const previewContainer = document.getElementById('preview-container');
        const previewTitle = document.getElementById('preview-title');
        const previewFilename = document.getElementById('preview-filename');
        const previewOpenBtn = document.getElementById('preview-open-btn');
        const previewDownloadBtn = document.getElementById('preview-download-btn');
        const loadingHtml = document.getElementById('preview-loading') ? document.getElementById('preview-loading').outerHTML : '<p>Loading...</p>';

        document.querySelectorAll('.btn-preview-file').forEach(btn => {
            btn.addEventListener('click', function() {
                const url = this.dataset.url;
                const title = this.dataset.title;
                let ext = this.dataset.extension ? this.dataset.extension.toLowerCase() : '';
                
                if (!ext) {
                    ext = url.split('.').pop().split(/\#|\?/)[0].toLowerCase();
                }

                console.log('Previewing File:', { title, url, ext });

                previewTitle.textContent = title;
                previewFilename.textContent = url.split('/').pop();
                previewOpenBtn.href = url;
                previewDownloadBtn.href = url;
                previewDownloadBtn.setAttribute('download', title + '.' + ext);

                previewContainer.innerHTML = loadingHtml;
                modalPreview.show();

                setTimeout(() => {
                    let content = '';
                    if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext)) {
                        content = `<div class="p-3 text-center w-100 h-100 d-flex align-items-center justify-content-center">
                                    <img src="${url}" class="img-fluid shadow-sm rounded bg-white" style="max-height: 100%; object-fit: contain;">
                                   </div>`;
                    } else if (ext === 'pdf') {
                        content = `<embed src="${url}#toolbar=0&navpanes=0&scrollbar=0" type="application/pdf" width="100%" height="100%" />`;
                    } else if (['mp4', 'webm', 'ogg'].includes(ext)) {
                        content = `<div class="p-3 w-100 h-100 d-flex align-items-center justify-content-center">
                                    <video controls class="mw-100 mh-100 shadow rounded bg-black" autoplay><source src="${url}" type="video/${ext === 'mp4' ? 'mp4' : ext}">Tr�nh duy?t kh�ng h? tr?.</video>
                                   </div>`;
                    } else if (['mp3', 'wav', 'ogg'].includes(ext)) {
                        content = `<div class="text-center p-5"><i class="fas fa-file-audio fa-6x text-primary mb-4 d-block"></i><audio controls class="w-100 shadow-sm" style="max-width: 500px;"><source src="${url}" type="audio/mpeg"></audio></div>`;
                    } 
                    // 5. File Van b?n (Word, Excel, WPS...)
                    else if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'wps', 'txt'].includes(ext)) {
                        content = `
                            <div class="text-center p-5 bg-white rounded shadow-sm border m-3" style="max-width: 600px;">
                                <div class="mb-4">
                                    <span class="fa-stack fa-4x">
                                        <i class="fas fa-file fa-stack-2x text-primary opacity-10"></i>
                                        <i class="fas fa-file-word fa-stack-1x text-primary"></i>
                                    </span>
                                </div>
                                <h4 class="fw-bold text-dark">T�i li?u .${ext.toUpperCase()}</h4>
                                <p class="text-muted mb-4">
                                    Tr�nh duy?t kh�ng h? tr? xem tr?c ti?p d?nh d?ng <b>.${ext}</b>.<br>
                                    Vui l�ng t?i v? m�y d? m? b?ng <b>WPS Office</b> ho?c <b>Microsoft Office</b>.
                                </p>
                                <div class="d-grid gap-3">
                                    <a href="${url}" download class="btn btn-primary btn-lg fw-bold shadow-sm">
                                        <i class="fas fa-download me-2"></i> T?I XU?NG NGAY
                                    </a>
                                    <div class="text-muted small italic">
                                        <i class="fas fa-lightbulb me-1 text-warning"></i> 
                                        <b>M?o:</b> B?n n�n chuy?n file sang d?nh d?ng <b>PDF</b> tru?c khi dang d? h?c vi�n c� th? xem tr?c ti?p tr�n web.
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    else {
                        content = `<div class="text-center p-5 bg-white rounded shadow-sm border m-3" style="max-width: 500px;"><div class="mb-4"><span class="fa-stack fa-3x"><i class="fas fa-file fa-stack-2x text-light"></i><i class="fas fa-file-download fa-stack-1x text-primary"></i></span></div><h5 class="fw-bold text-dark">�?nh d?ng .${ext.toUpperCase()}</h5><p class="text-muted small mb-4">Tr�nh duy?t kh�ng h? tr? xem tr?c ti?p ho?c dang ch?y Local.</p><div class="d-grid gap-2"><a href="${url}" download class="btn btn-success fw-bold py-2 shadow-sm"><i class="fas fa-download me-2"></i>T?I V?</a><a href="${url}" target="_blank" class="btn btn-outline-primary fw-bold py-2"><i class="fas fa-external-link-alt me-2"></i>M? TAB M?I</a></div></div>`;
                    }
                    previewContainer.innerHTML = content;
                }, 500);
            });
        });
    }
});</script>
@endpush

<style>
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    .border-dashed { border-style: dashed !important; }
    .object-fit-cover { object-fit: cover; }
    .btn-xs { padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px; }
    .smaller { font-size: 0.75rem; }
    .italic { font-style: italic; }
    .hover-bg-light:hover { background-color: #f8f9fa; }
    .btn-icon-custom {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 6px;
    }
</style>
@endsection
