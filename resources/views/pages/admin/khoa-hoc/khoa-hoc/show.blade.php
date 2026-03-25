@extends('layouts.app')

@section('title', 'Chi ti?t: ' . $khoaHoc->ma_khoa_hoc)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang ch?</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.index') }}">Kh�a h?c</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $khoaHoc->ma_khoa_hoc }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header & Actions -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-7">
            <div class="d-flex align-items-center">
                <span class="badge bg-{{ $khoaHoc->loai === 'mau' ? 'info' : 'primary' }} me-3 px-3 py-2">
                    {{ $khoaHoc->loai === 'mau' ? 'KH�A M?U' : 'L?P HO?T �?NG' }}
                </span>
                <h3 class="fw-bold mb-0">{{ $khoaHoc->ten_khoa_hoc }}</h3>
            </div>
            <div class="mt-2 text-muted small">
                <i class="fas fa-barcode me-1"></i> M�: <code class="fw-bold">{{ $khoaHoc->ma_khoa_hoc }}</code>
                <span class="mx-2">|</span>
                <i class="fas fa-layer-group me-1"></i> Nh�m ng�nh: <span class="fw-bold text-dark">{{ $khoaHoc->nhomNganh->ten_nhom_nganh ?? 'N/A' }}</span>
            </div>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            @if($khoaHoc->loai === 'mau')
                <a href="{{ route('admin.khoa-hoc.mo-lop', $khoaHoc->id) }}" class="btn btn-success fw-bold shadow-sm px-4">
                    <i class="fas fa-rocket me-2"></i> M? L?P T? M?U N�Y
                </a>
                <a href="{{ route('admin.khoa-hoc.edit', $khoaHoc->id) }}" class="btn btn-outline-warning fw-bold ms-2">
                    <i class="fas fa-edit me-1"></i> S?a m?u
                </a>
            @else
                <button class="btn btn-outline-secondary fw-bold shadow-sm" disabled>
                    <i class="fas fa-lock me-2"></i> �� m? l?p (K{{ str_pad($khoaHoc->lan_mo_thu, 2, '0', STR_PAD_LEFT) }})
                </button>
            @endif
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Left: Core Info & Modules -->
        <div class="col-lg-8">
            {{-- TH�NG TIN CHI TI?T --}}
            <div class="vip-card mb-4 shadow-sm border-0">
                <div class="vip-card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="image-box rounded border bg-light overflow-hidden shadow-xs" style="height: 180px;">
                                <img src="{{ $khoaHoc->hinh_anh ? asset($khoaHoc->hinh_anh) : asset('images/default-course.svg') }}" 
                                     class="img-fluid w-100 h-100 object-fit-cover">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <h6 class="smaller fw-bold text-muted text-uppercase mb-2">Mô tả ngắn chương trình</h6>
                                <p class="mb-0 text-dark">{{ $khoaHoc->mo_ta_ngan ?: 'Chưa có mô tả ngắn.' }}</p>
                            </div>
                            <div class="row g-3">
                                <div class="col-6 col-md-4">
                                    <span class="smaller text-muted d-block">C?p d?</span>
                                    <span class="fw-bold">{{ ['co_ban'=>'Co b?n','trung_binh'=>'Trung b�nh','nang_cao'=>'N�ng cao'][$khoaHoc->cap_do] ?? 'N/A' }}</span>
                                </div>
                                <div class="col-6 col-md-4">
                                    <span class="smaller text-muted d-block">T?ng Module</span>
                                    <span class="fw-bold">{{ $khoaHoc->tong_so_module }} b�i h?c</span>
                                </div>
                                <div class="col-6 col-md-4">
                                    <span class="smaller text-muted d-block">Tr?ng th�i</span>
                                    <span class="badge bg-{{ $khoaHoc->badge_trang_thai }}">{{ $khoaHoc->label_trang_thai_van_hanh }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($khoaHoc->mo_ta_chi_tiet)
                        <hr class="my-4">
                        <h6 class="smaller fw-bold text-muted text-uppercase mb-2">N?i dung chi ti?t & L? tr�nh</h6>
                        <div class="text-dark small lh-lg">{!! nl2br(e($khoaHoc->mo_ta_chi_tiet)) !!}</div>
                    @endif
                </div>
            </div>

            {{-- QU?N L� H?C VI�N & L?CH H?C (D?i l�n d�y cho l?p ho?t d?ng) --}}
            @if($khoaHoc->loai === 'hoat_dong')
                <div class="row mb-4">
                    <div class="col-md-6">
                        {{-- CARD QU?N L� H?C VI�N --}}
                        <div class="vip-card shadow-sm border-0 h-100">
                            <div class="vip-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-success">
                                    <i class="fas fa-users me-2"></i> H?c vi�n
                                </h5>
                                <a href="{{ route('admin.khoa-hoc.hoc-vien.index', $khoaHoc->id) }}" class="btn btn-success btn-sm fw-bold">
                                    <i class="fas fa-cog me-1"></i> Qu?n l�
                                </a>
                            </div>
                            <div class="vip-card-body p-4">
                                <div class="row text-center g-2">
                                    <div class="col-4">
                                        <div class="fw-bold fs-4 text-success">
                                            {{ $khoaHoc->hocVienKhoaHocs()->where('trang_thai','dang_hoc')->count() }}
                                        </div>
                                        <div class="smaller text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">�ang h?c</div>
                                    </div>
                                    <div class="col-4 border-start border-end">
                                        <div class="fw-bold fs-4 text-primary">
                                            {{ $khoaHoc->hocVienKhoaHocs()->where('trang_thai','hoan_thanh')->count() }}
                                        </div>
                                        <div class="smaller text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Xong</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="fw-bold fs-4 text-danger">
                                            {{ $khoaHoc->hocVienKhoaHocs()->where('trang_thai','ngung_hoc')->count() }}
                                        </div>
                                        <div class="smaller text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Ngh?</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        {{-- CARD QU?N L� L?CH H?C --}}
                        <div class="vip-card shadow-sm border-0 h-100">
                            <div class="vip-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-info">
                                    <i class="fas fa-calendar-alt me-2"></i> L?ch h?c
                                </h5>
                                <a href="{{ route('admin.khoa-hoc.lich-hoc.index', $khoaHoc->id) }}" class="btn btn-info btn-sm fw-bold text-white">
                                    <i class="fas fa-edit me-1"></i> Qu?n l�
                                </a>
                            </div>
                            <div class="vip-card-body p-4">
                                @php 
                                    $tongLich = $khoaHoc->lichHocs()->count(); 
                                    $tongBuoiReq = $khoaHoc->moduleHocs()->sum('so_buoi'); 
                                @endphp
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="small text-muted mb-1">Ti?n d?: <strong>{{ $tongLich }} / {{ $tongBuoiReq }} bu?i</strong></div>
                                    </div>
                                    <div>
                                        @if($tongLich < $tongBuoiReq)
                                            <span class="badge bg-warning text-dark px-2" style="font-size: 0.65rem;">Thi?u {{ $tongBuoiReq - $tongLich }} bu?i</span>
                                        @else
                                            <span class="badge bg-success px-2" style="font-size: 0.65rem;">�� d?</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    @php $prog = $tongBuoiReq > 0 ? min(100, ($tongLich / $tongBuoiReq) * 100) : 0; @endphp
                                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $prog }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- DANH S�CH MODULE & GI?NG VI�N --}}
            <div class="vip-card mb-4 shadow-sm border-0">
                <div class="vip-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0">?? C?u tr�c Module h?c t?p</h5>
                    @if($khoaHoc->loai === 'mau')
                        <a href="{{ route('admin.module-hoc.create', ['khoa_hoc_id' => $khoaHoc->id]) }}" class="btn btn-primary btn-sm px-3 fw-bold shadow-xs">
                            <i class="fas fa-plus me-1"></i> Th�m module
                        </a>
                    @endif
                </div>
                <div class="vip-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light smaller">
                                <tr>
                                    <th class="ps-4 text-center" width="60">STT</th>
                                    <th>T�n Module h?c</th>
                                    <th class="text-center">TL (ph�t)</th>
                                    @if($khoaHoc->loai === 'hoat_dong')
                                        <th>Gi?ng vi�n ph? tr�ch</th>
                                        <th class="text-center">X�c nh?n</th>
                                    @endif
                                    <th class="pe-4 text-center" width="100">H�nh d?ng</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($khoaHoc->moduleHocs as $index => $module)
                                    <tr>
                                        <td class="text-center ps-4 text-muted small fw-bold">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $module->ten_module }}</div>
                                            <div class="smaller text-muted italic">{{ Str::limit($module->mo_ta, 60) }}</div>
                                        </td>
                                        <td class="text-center">{{ $module->thoi_luong_du_kien }}p</td>
                                        
                                        @if($khoaHoc->loai === 'hoat_dong')
                                            @php $pc = $module->phanCongGiangViens->first(); @endphp
                                            <td>
                                                @if($pc)
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-mini rounded-circle bg-light me-2 border text-center" style="width: 30px; height: 30px; line-height: 30px;">
                                                                <i class="fas fa-user-tie text-primary small"></i>
                                                            </div>
                                                            <div class="lh-1">
                                                                <div class="small fw-bold text-primary">{{ $pc->giangVien->nguoiDung->ho_ten }}</div>
                                                                <div class="smaller text-muted mt-1">{{ $pc->giangVien->chuyen_nganh ?: 'Chuy�n gia' }}</div>
                                                            </div>
                                                        </div>
                                                        {{-- N�t thay d?i nhanh --}}
                                                        <button type="button" class="btn btn-xs btn-outline-warning border-0 btn-replace-gv" 
                                                                data-pc-id="{{ $pc->id }}" 
                                                                data-module-name="{{ $module->ten_module }}"
                                                                data-current-gv="{{ $pc->giangVien->nguoiDung->ho_ten }}"
                                                                title="Thay d?i GV">
                                                            <i class="fas fa-exchange-alt"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="badge bg-light text-muted border">Chưa gán GV</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($pc)
                                                    @php
                                                        $statusMap = [
                                                            'cho_xac_nhan' => ['bg'=>'warning','text'=>'Ch?'],
                                                            'da_nhan'     => ['bg'=>'success','text'=>'�?ng �'],
                                                            'tu_choi'     => ['bg'=>'danger','text'=>'T? ch?i']
                                                        ];
                                                        $s = $statusMap[$pc->trang_thai] ?? ['bg'=>'secondary','text'=>'?'];
                                                    @endphp
                                                    <span class="badge bg-{{ $s['bg'] }} smaller shadow-xs">{{ $s['text'] }}</span>
                                                @else � @endif
                                            </td>
                                        @endif

                                        <td class="pe-4 text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('admin.module-hoc.show', $module->id) }}" class="btn btn-sm btn-outline-info border-0" title="Chi ti?t"><i class="fas fa-info-circle"></i></a>
                                                @if($khoaHoc->loai === 'mau')
                                                    <a href="{{ route('admin.module-hoc.edit', $module->id) }}" class="btn btn-sm btn-outline-warning border-0" title="S?a"><i class="fas fa-edit"></i></a>
                                                    <form action="{{ route('admin.module-hoc.destroy', $module->id) }}" method="POST" class="d-inline" onsubmit="return confirm('X�a module n�y?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline-primary border-0 btn-phan-cong" 
                                                            data-module-id="{{ $module->id }}" 
                                                            data-module-name="{{ $module->ten_module }}"
                                                            title="Ph�n c�ng GV">
                                                        <i class="fas fa-user-plus"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5 text-muted small">Chưa có module nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Lifecycle & Meta -->
        <div class="col-lg-4">
            @if($khoaHoc->loai === 'hoat_dong')
                {{-- CARD L?CH TR�NH L?P --}}
                <div class="vip-card mb-4 border-0 shadow-sm">
                    <div class="vip-card-header bg-primary text-white py-3">
                        <h5 class="vip-card-title small fw-bold text-uppercase mb-0">?? L?ch tr�nh l?p h?c</h5>
                    </div>
                    <div class="vip-card-body p-4">
                        <div class="timeline-simple">
                            <div class="timeline-item mb-4 pb-1 border-start ps-4 position-relative">
                                <div class="timeline-point bg-info"></div>
                                <span class="smaller text-muted text-uppercase fw-bold d-block">Ng�y khai gi?ng</span>
                                <span class="fw-bold fs-5">{{ $khoaHoc->ngay_khai_giang->format('d/m/Y') }}</span>
                            </div>
                            <div class="timeline-item mb-4 pb-1 border-start ps-4 position-relative">
                                <div class="timeline-point bg-success"></div>
                                <span class="smaller text-muted text-uppercase fw-bold d-block">Ng�y ch�nh th?c m? l?p</span>
                                <span class="fw-bold fs-5">{{ $khoaHoc->ngay_mo_lop->format('d/m/Y') }}</span>
                            </div>
                            <div class="timeline-item pb-1 border-start ps-4 position-relative">
                                <div class="timeline-point bg-danger"></div>
                                <span class="smaller text-muted text-uppercase fw-bold d-block">D? ki?n k?t th�c</span>
                                <span class="fw-bold fs-5">{{ $khoaHoc->ngay_ket_thuc->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        
                        @if($khoaHoc->trang_thai_van_hanh === 'san_sang')
                            <hr class="my-4">
                            <form action="{{ route('admin.khoa-hoc.xac-nhan-mo-lop', $khoaHoc->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm">
                                    <i class="fas fa-play me-2"></i> K�CH HO?T D?Y NGAY
                                </button>
                                <p class="smaller text-muted text-center mt-2 italic">T?t c? gi?ng vi�n d� x�c nh?n d?ng �.</p>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- NGU?N G?C --}}
                <div class="vip-card mb-4 shadow-sm border-0 bg-light">
                    <div class="vip-card-body p-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-link fa-2x text-muted me-3 opacity-50"></i>
                            <div>
                                <span class="smaller text-muted fw-bold d-block text-uppercase">G?c t? kh�a m?u</span>
                                @if($khoaHoc->khoaHocMau)
                                    <a href="{{ route('admin.khoa-hoc.show', $khoaHoc->khoa_hoc_mau_id) }}" class="fw-bold text-decoration-none">
                                        {{ $khoaHoc->khoaHocMau->ten_khoa_hoc }}
                                    </a>
                                @else
                                    <span class="fw-bold text-dark">Kh�a tr?c ti?p</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- STATS CHO KH�A M?U --}}
                <div class="vip-card mb-4 shadow-sm border-0">
                    <div class="vip-card-header py-3">
                        <h5 class="vip-card-title small fw-bold text-uppercase mb-0">?? Hi?u qu? d�o t?o</h5>
                    </div>
                    <div class="vip-card-body p-4 text-center">
                        <div class="row g-0">
                            <div class="col-12 mb-3">
                                <div class="p-3 border rounded bg-light">
                                    <h2 class="fw-bold text-success mb-0">{{ $khoaHoc->lop_da_mo_count }}</h2>
                                    <span class="smaller text-muted text-uppercase fw-bold">L?n m? l?p th?c t?</span>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info border-0 small text-start mb-0">
                            <i class="fas fa-info-circle me-1"></i> Kh�a m?u gi�p chu?n h�a quy tr�nh d?y cho t?t c? c�c l?p sau n�y.
                        </div>
                    </div>
                </div>
            @endif

            {{-- GHI CH� N?I B? --}}
            <div class="vip-card mb-4 shadow-sm border-0">
                <div class="vip-card-header py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0">?? Ghi ch� n?i b?</h5>
                </div>
                <div class="vip-card-body p-4">
                    <p class="text-dark small lh-base mb-0 italic">
                        {{ $khoaHoc->ghi_chu_noi_bo ?: 'Kh�ng c� ghi ch� n�o d�nh cho qu?n tr? vi�n.' }}
                    </p>
                </div>
            </div>

            {{-- META INFO --}}
            <div class="vip-card shadow-sm border-0 mb-4 bg-light">
                <div class="vip-card-body p-3 smaller">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Ng�y kh?i t?o:</span>
                        <span class="fw-bold">{{ $khoaHoc->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">C?p nh?t cu?i:</span>
                        <span class="fw-bold">{{ $khoaHoc->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Ngu?i t?o:</span>
                        <span class="fw-bold text-primary">{{ $khoaHoc->creator->ho_ten ?? 'Admin' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PH�N C�NG GI?NG VI�N --}}
<div class="modal fade shadow" id="modalPhanCong" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i> Ph�n c�ng gi?ng vi�n</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="modalPhanCongForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold mb-1">Module dang ch?n:</label>
                        <div id="phanCong-moduleName" class="fw-bold fs-5 text-dark"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Ch?n gi?ng vi�n *</label>
                        <select name="giang_vien_id" class="form-select vip-form-control" required>
                            <option value="">-- Ch?n gi?ng vi�n --</option>
                            @foreach($giangViens as $gv)
                                <option value="{{ $gv->id }}">
                                    {{ $gv->nguoiDung->ho_ten }} ({{ $gv->chuyen_nganh ?: 'Chuy�n gia' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Ghi ch� ph�n c�ng</label>
                        <textarea name="ghi_chu" class="form-control vip-form-control" rows="3" placeholder="Ghi ch� v? y�u c?u d?y, t�i li?u..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">H?y b?</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">G?i y�u c?u</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL THAY TH? GI?NG VI�N --}}
<div class="modal fade shadow" id="modalReplaceGV" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-warning text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-exchange-alt me-2"></i> Thay d?i gi?ng vi�n</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="modalReplaceGVForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0 smaller mb-4">
                        B?n dang thay th? gi?ng vi�n cho module: <strong id="replace-moduleName"></strong>.
                        <br>Gi?ng vi�n hi?n t?i: <strong id="replace-currentGV"></strong>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Ch?n gi?ng vi�n thay th? *</label>
                        <select name="giang_vien_id" class="form-select vip-form-control" required>
                            <option value="">-- Ch?n gi?ng vi�n m?i --</option>
                            @foreach($giangViens as $gv)
                                <option value="{{ $gv->id }}">
                                    {{ $gv->nguoiDung->ho_ten }} ({{ $gv->chuyen_nganh ?: 'Chuy�n gia' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">L� do thay d?i / Ghi ch�</label>
                        <textarea name="ghi_chu" class="form-control vip-form-control" rows="3" placeholder="Ghi ch� cho GV m?i..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">H?y b?</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold shadow-sm text-white">X�c nh?n thay th?</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal Ph�n c�ng
        const modalPC = new bootstrap.Modal(document.getElementById('modalPhanCong'));
        const formPC = document.getElementById('modalPhanCongForm');
        const moduleNamePC = document.getElementById('phanCong-moduleName');

        document.querySelectorAll('.btn-phan-cong').forEach(btn => {
            btn.addEventListener('click', function() {
                const moduleId = this.dataset.moduleId;
                moduleNamePC.textContent = this.dataset.moduleName;
                formPC.action = `/admin/module-hoc/${moduleId}/assign`;
                modalPC.show();
            });
        });

        // Modal Thay th?
        const modalRep = new bootstrap.Modal(document.getElementById('modalReplaceGV'));
        const formRep = document.getElementById('modalReplaceGVForm');
        const moduleNameRep = document.getElementById('replace-moduleName');
        const currentGVRep = document.getElementById('replace-currentGV');

        document.querySelectorAll('.btn-replace-gv').forEach(btn => {
            btn.addEventListener('click', function() {
                const pcId = this.dataset.pcId;
                moduleNameRep.textContent = this.dataset.moduleName;
                currentGVRep.textContent = this.dataset.currentGv;
                formRep.action = `/admin/phan-cong/${pcId}/replace`;
                modalRep.show();
            });
        });
    });
</script>
@endpush

<style>
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    .timeline-point {
        position: absolute; left: -5px; top: 0;
        width: 10px; height: 10px; border-radius: 50%;
    }
    .avatar-mini { font-size: 14px; }
    .object-fit-cover { object-fit: cover; }
    .italic { font-style: italic; }
</style>
@endsection

