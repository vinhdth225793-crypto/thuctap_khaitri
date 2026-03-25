@extends('layouts.app')

@section('title', 'M? l?p t? kh�a h?c m?u')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12 text-muted small">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang ch?</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.index') }}">Kh�a h?c</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.khoa-hoc.show', $khoaHocMau->id) }}">{{ $khoaHocMau->ten_khoa_hoc }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">M? l?p</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold"><i class="fas fa-rocket me-2 text-success"></i> M? l?p t? kh�a h?c m?u</h4>
            <p class="text-muted small">Nh�n b?n chuong tr�nh h?c t? m?u v� thi?t l?p l?ch khai gi?ng th?c t? cho l?p m?i.</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
    @endif

    <div class="row">
        <!-- C?t tr�i: Th�ng tin m?u & C?u tr�c -->
        <div class="col-lg-7">
            {{-- CARD TH�NG TIN M?U (Ch? d?c) --}}
            <div class="vip-card mb-4 bg-light border-0 shadow-sm">
                <div class="vip-card-header bg-white border-bottom py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0 text-muted">?? Kh�a h?c m?u g?c (Ch? d?c)</h5>
                </div>
                <div class="vip-card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-7 border-end">
                            <div class="mb-2">
                                <span class="smaller text-muted fw-bold text-uppercase d-block">T�n kh�a h?c</span>
                                <span class="fw-bold text-dark fs-5">{{ $khoaHocMau->ten_khoa_hoc }}</span>
                            </div>
                            <div class="row g-2 mt-2">
                                <div class="col-6">
                                    <span class="smaller text-muted fw-bold text-uppercase d-block">M� m?u</span>
                                    <code class="fw-bold text-primary">{{ $khoaHocMau->ma_khoa_hoc }}</code>
                                </div>
                                <div class="col-6">
                                    <span class="smaller text-muted fw-bold text-uppercase d-block">Nh�m ng�nh</span>
                                    <span class="small fw-bold">{{ $khoaHocMau->nhomNganh->ten_nhom_nganh }}</span>
                                </div>
                                <div class="col-6 mt-2">
                                    <span class="smaller text-muted fw-bold text-uppercase d-block">C?p d?</span>
                                    <span class="badge bg-secondary smaller">
                                        {{ ['co_ban'=>'Co b?n','trung_binh'=>'Trung b�nh','nang_cao'=>'N�ng cao'][$khoaHocMau->cap_do] ?? 'N/A' }}
                                    </span>
                                </div>
                                <div class="col-6 mt-2">
                                    <span class="smaller text-muted fw-bold text-uppercase d-block">S? module</span>
                                    <span class="badge bg-dark rounded-pill">{{ $khoaHocMau->moduleHocs->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 text-center ps-4">
                            <div class="badge bg-info p-3 mb-3 shadow-sm w-100">
                                <div class="small text-uppercase opacity-75 mb-1">�� m?</div>
                                <div class="fs-4 fw-bold">{{ $soLanDaMo }} l?n</div>
                            </div>
                            <div class="alert alert-success border-0 py-2 mb-0 small">
                                M� l?p m?i d? ki?n:<br>
                                <strong class="fs-5">{{ $maMoiDuKien }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD DANH S�CH MODULE --}}
            <div class="vip-card mb-4 border-0 shadow-sm">
                <div class="vip-card-header py-3">
                    <h5 class="vip-card-title small fw-bold text-uppercase mb-0">?? Danh s�ch Module s? copy</h5>
                </div>
                <div class="vip-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="bg-light smaller">
                                <tr>
                                    <th class="text-center" width="50">#</th>
                                    <th>T�n module</th>
                                    <th class="text-center">Th?i lu?ng</th>
                                    <th>Ghi ch� m?u</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($khoaHocMau->moduleHocs as $index => $module)
                                    <tr>
                                        <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                                        <td class="fw-bold">{{ $module->ten_module }}</td>
                                        <td class="text-center">{{ $module->thoi_luong_du_kien }}p</td>
                                        <td class="text-muted italic small">{{ Str::limit($module->mo_ta, 50) ?: '�' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 bg-light border-top italic smaller text-muted">
                        <i class="fas fa-info-circle me-1"></i> C�c module n�y s? du?c sao ch�p nguy�n tr?ng sang l?p m?i v?i m� module m?i.
                    </div>
                </div>
            </div>
        </div>

        <!-- C?t ph?i: Form thi?t l?p l?p m?i -->
        <div class="col-lg-5">
            <form action="{{ route('admin.khoa-hoc.mo-lop.store', $khoaHocMau->id) }}" method="POST">
                @csrf
                {{-- SECTION: 3 M?C NG�Y --}}
                <div class="vip-card mb-4 border-primary border-top border-4 shadow">
                    <div class="vip-card-header bg-white py-3">
                        <h5 class="vip-card-title small fw-bold text-primary mb-0">
                            <i class="fas fa-calendar-alt me-2"></i> L?CH H?C C?A L?P M?I
                        </h5>
                    </div>
                    <div class="vip-card-body p-4">
                        <div class="alert alert-info border-0 shadow-sm mb-4 small" style="background-color: #f0f7ff;">
                            <i class="fas fa-lightbulb me-2 text-info"></i> <strong>Luu � v? 3 m?c ng�y:</strong>
                            <ul class="mb-0 mt-2 ps-3">
                                <li><strong>Khai gi?ng:</strong> Bu?i l? ch�o m?ng, gi?i thi?u.</li>
                                <li><strong>M? l?p:</strong> Ng�y b?t d?u h?c th?t (= khai gi?ng).</li>
                                <li><strong>K?t th�c:</strong> Ng�y k?t th�c to�n chuong tr�nh.</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Ng�y khai gi?ng *</label>
                            <input type="date" name="ngay_khai_giang" id="ngay_khai_giang" 
                                   class="form-control vip-form-control @error('ngay_khai_giang') is-invalid @enderror" 
                                   value="{{ old('ngay_khai_giang') }}" min="{{ date('Y-m-d') }}" required>
                            @error('ngay_khai_giang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Ng�y b?t d?u v�o h?c (M? l?p) *</label>
                            <input type="date" name="ngay_mo_lop" id="ngay_mo_lop" 
                                   class="form-control vip-form-control @error('ngay_mo_lop') is-invalid @enderror" 
                                   value="{{ old('ngay_mo_lop') }}" required>
                            <div class="form-text smaller italic">Ng�y n�y ph?i sau ho?c b?ng ng�y khai gi?ng.</div>
                            @error('ngay_mo_lop') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-0">
                            <label class="form-label small fw-bold">Ng�y k?t th�c d? ki?n *</label>
                            <input type="date" name="ngay_ket_thuc" id="ngay_ket_thuc" 
                                   class="form-control vip-form-control @error('ngay_ket_thuc') is-invalid @enderror" 
                                   value="{{ old('ngay_ket_thuc') }}" required>
                            <div class="form-text smaller italic">Ng�y n�y ph?i sau ng�y b?t d?u v�o h?c.</div>
                            @error('ngay_ket_thuc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- SECTION: PH�N C�NG GI?NG VI�N (Optional) --}}
                <div class="vip-card mb-4 shadow-sm">
                    <div class="vip-card-header bg-white py-3">
                        <h5 class="vip-card-title small fw-bold text-dark mb-0">
                            <i class="fas fa-user-tie me-2"></i> PH�N C�NG GI?NG VI�N (T�y ch?n)
                        </h5>
                    </div>
                    <div class="vip-card-body p-4">
                        <p class="smaller text-muted italic mb-3">
                            Ph�n c�ng ngay s? g?i th�ng b�o x�c nh?n t?i gi?ng vi�n. �? tr?ng n?u chua ch?n du?c GV, b?n c� th? g�n sau trong trang chi ti?t.
                        </p>

                        <div id="module-assignments">
                            @foreach($khoaHocMau->moduleHocs as $module)
                                <div class="mb-3 p-2 border-start border-3 border-info bg-light rounded">
                                    <label class="smaller fw-bold d-block mb-1">{{ $module->ten_module }} ({{ $module->thoi_luong_du_kien }}p)</label>
                                    <select name="giang_vien_modules[{{ $module->id }}]" class="form-select form-select-sm vip-form-control">
                                        <option value="">-- Ch?n sau --</option>
                                        @foreach($giangViens as $gv)
                                            <option value="{{ $gv->id }}" {{ old("giang_vien_modules.{$module->id}") == $gv->id ? 'selected' : '' }}>
                                                {{ $gv->nguoiDung->ho_ten }} ({{ $gv->chuyen_nganh ?: 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="vip-card mb-4 shadow-sm">
                    <div class="vip-card-header bg-white py-3 border-bottom">
                        <h5 class="vip-card-title small fw-bold text-dark mb-0">GHI CH� N?I B?</h5>
                    </div>
                    <div class="vip-card-body p-4">
                        <textarea name="ghi_chu_noi_bo" class="form-control vip-form-control" rows="3" placeholder="Ghi ch� v? l?p h?c n�y (ch? admin th?y)...">{{ old('ghi_chu_noi_bo') }}</textarea>
                    </div>
                </div>

                <div class="d-grid gap-2 mb-5">
                    <button type="submit" class="btn btn-success btn-lg py-3 fw-bold shadow border-0">
                        <i class="fas fa-rocket me-2"></i> M? L?P � M�: {{ $maMoiDuKien }}
                    </button>
                    <a href="{{ route('admin.khoa-hoc.show', $khoaHocMau->id) }}" class="btn btn-outline-secondary py-2 fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> QUAY L?I
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const khaiGiangInput = document.getElementById('ngay_khai_giang');
    const moLopInput     = document.getElementById('ngay_mo_lop');
    const ketThucInput   = document.getElementById('ngay_ket_thuc');

    // Ch?n logic ng�y
    khaiGiangInput.addEventListener('change', function() {
        if (this.value) {
            moLopInput.min = this.value;
            if (moLopInput.value && moLopInput.value < this.value) {
                moLopInput.value = this.value;
                moLopInput.dispatchEvent(new Event('change'));
            }
        }
    });

    moLopInput.addEventListener('change', function() {
        if (this.value) {
            // Ng�y k?t th�c ph?i sau ng�y m? l?p �t nh?t 1 ng�y
            const nextDay = new Date(this.value);
            nextDay.setDate(nextDay.getDate() + 1);
            ketThucInput.min = nextDay.toISOString().split('T')[0];
            
            if (ketThucInput.value && ketThucInput.value <= this.value) {
                ketThucInput.value = nextDay.toISOString().split('T')[0];
            }
        }
    });
});
</script>

<style>
    .vip-form-control:focus { box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.1); border-color: #28a745; }
    .shadow-sm { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important; }
    .italic { font-style: italic; }
</style>
@endsection
