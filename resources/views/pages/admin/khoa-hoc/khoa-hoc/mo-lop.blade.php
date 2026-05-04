@extends('layouts.app')

@section('title', 'Tạo lớp từ khóa học mẫu')

@section('content')
<div class="container-fluid admin-page-x mo-lop-page">
    {{-- ========== Welcome banner ========== --}}
    <div class="apx-welcome">
        <div class="apx-welcome-icon"><i class="fas fa-rocket"></i></div>
        <div class="apx-welcome-text">
            <h4>Tạo lớp học mới từ khóa mẫu</h4>
            <p>
                Bạn đang nhân bản khóa <strong>{{ $khoaHocMau->ten_khoa_hoc }}</strong>
                ({{ $khoaHocMau->ma_khoa_hoc }}) thành <strong>lớp hoạt động thứ {{ $soLanDaMo + 1 }}</strong>.
                Toàn bộ {{ $khoaHocMau->moduleHocs->count() }} module sẽ được sao chép. Bạn chỉ cần điền 3 mốc ngày
                và (tuỳ chọn) phân công giảng viên.
            </p>
        </div>
        <div class="apx-welcome-cta">
            <a href="{{ route('admin.khoa-hoc.show', $khoaHocMau->id) }}" class="btn btn-light text-primary fw-bold shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
            <a href="{{ route('admin.khoa-hoc.index', ['tab' => 'mau']) }}" class="apx-view-toggle">
                <i class="fas fa-folder-tree"></i>
                <span>Về danh sách mẫu</span>
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <i class="fas fa-circle-exclamation me-2"></i>{{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.khoa-hoc.mo-lop.store', $khoaHocMau->id) }}" method="POST" id="moLopForm">
        @csrf

        {{-- ========== ① Thông tin khóa mẫu (chỉ đọc) ========== --}}
        <section class="apx-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">1</span>
                    <div>
                        <h2><i class="fas fa-file-circle-check"></i> Thông tin khóa mẫu</h2>
                        <p>Dữ liệu chỉ đọc — tham chiếu để bạn xác nhận đúng khóa cần nhân bản.</p>
                    </div>
                </div>
                <div class="apx-section-meta">
                    <span class="apx-meta-pill"><strong>Khóa #{{ $soLanDaMo + 1 }}</strong> sắp tạo</span>
                </div>
            </header>

            <div class="ml-summary">
                <div class="ml-summary-main">
                    <div class="ml-name-block">
                        <span class="ml-label">Tên khóa</span>
                        <h3>{{ $khoaHocMau->ten_khoa_hoc }}</h3>
                    </div>
                    <div class="ml-meta-grid">
                        <div class="ml-meta">
                            <span class="ml-label"><i class="fas fa-barcode"></i> Mã mẫu</span>
                            <code class="ml-value">{{ $khoaHocMau->ma_khoa_hoc }}</code>
                        </div>
                        <div class="ml-meta">
                            <span class="ml-label"><i class="fas fa-shapes"></i> Nhóm ngành</span>
                            <strong class="ml-value">{{ $khoaHocMau->nhomNganh->ten_nhom_nganh ?? 'Chưa gán' }}</strong>
                        </div>
                        <div class="ml-meta">
                            <span class="ml-label"><i class="fas fa-signal"></i> Cấp độ</span>
                            <span class="ml-cap-badge cap-{{ $khoaHocMau->cap_do }}">
                                {{ ['co_ban'=>'Cơ bản','trung_binh'=>'Trung bình','nang_cao'=>'Nâng cao'][$khoaHocMau->cap_do] ?? 'Tổng hợp' }}
                            </span>
                        </div>
                        <div class="ml-meta">
                            <span class="ml-label"><i class="fas fa-cubes"></i> Module</span>
                            <strong class="ml-value">{{ $khoaHocMau->moduleHocs->count() }} module</strong>
                        </div>
                    </div>
                </div>
                <div class="ml-summary-side">
                    <div class="ml-side-row">
                        <span><i class="fas fa-clone"></i> Đã mở</span>
                        <strong>{{ $soLanDaMo }} lần</strong>
                    </div>
                    <div class="ml-side-row ml-side-highlight">
                        <span><i class="fas fa-tag"></i> Mã lớp mới</span>
                        <strong class="ml-new-code">{{ $maMoiDuKien }}</strong>
                    </div>
                </div>
            </div>

            {{-- Module list compact --}}
            <details class="ml-modules" {{ $khoaHocMau->moduleHocs->count() <= 3 ? 'open' : '' }}>
                <summary>
                    <i class="fas fa-list-ul"></i>
                    <strong>Danh sách {{ $khoaHocMau->moduleHocs->count() }} module sẽ sao chép</strong>
                    <small>Click để xem chi tiết</small>
                    <i class="fas fa-chevron-down ml-modules-toggle"></i>
                </summary>
                <div class="ml-modules-body">
                    <table class="ml-modules-table">
                        <thead>
                            <tr>
                                <th width="60">#</th>
                                <th>Tên module</th>
                                <th width="140" class="text-center">Thời lượng</th>
                                <th>Mô tả</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($khoaHocMau->moduleHocs as $index => $module)
                                <tr>
                                    <td class="text-center"><span class="ml-mod-num">{{ $index + 1 }}</span></td>
                                    <td><strong>{{ $module->ten_module }}</strong></td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">{{ $module->thoi_luong_du_kien_label }}</span>
                                    </td>
                                    <td class="text-muted small">{{ \Illuminate\Support\Str::limit($module->mo_ta, 60) ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="ml-modules-note">
                        <i class="fas fa-circle-info"></i>
                        Mỗi module sẽ có mã mới dạng <code>{{ $maMoiDuKien }}M01</code>, <code>{{ $maMoiDuKien }}M02</code>...
                    </div>
                </div>
            </details>
        </section>

        {{-- ========== ② Lịch học của lớp ========== --}}
        <section class="apx-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">2</span>
                    <div>
                        <h2><i class="fas fa-calendar-days"></i> Lịch học của lớp mới</h2>
                        <p>Ba mốc thời gian quan trọng — phải nhập theo đúng thứ tự thời gian.</p>
                    </div>
                </div>
                <span class="apx-meta-pill ml-required-pill"><strong>Bắt buộc</strong></span>
            </header>

            <div class="ml-form-card">
                <div class="ml-date-grid">
                    <div class="ml-date-field">
                        <span class="ml-step-num">1</span>
                        <label for="ngay_khai_giang" class="ml-field-label">
                            <i class="fas fa-flag-checkered"></i> Ngày khai giảng <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="ngay_khai_giang" id="ngay_khai_giang"
                               class="form-control vip-form-control ml-date-input @error('ngay_khai_giang') is-invalid @enderror"
                               value="{{ old('ngay_khai_giang') }}" min="{{ date('Y-m-d') }}" required>
                        <small class="ml-field-hint">Buổi lễ chào mừng, giới thiệu lớp.</small>
                        @error('ngay_khai_giang') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="ml-date-arrow"><i class="fas fa-arrow-right"></i></div>

                    <div class="ml-date-field">
                        <span class="ml-step-num">2</span>
                        <label for="ngay_mo_lop" class="ml-field-label">
                            <i class="fas fa-play-circle"></i> Ngày bắt đầu học <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="ngay_mo_lop" id="ngay_mo_lop"
                               class="form-control vip-form-control ml-date-input @error('ngay_mo_lop') is-invalid @enderror"
                               value="{{ old('ngay_mo_lop') }}" required>
                        <small class="ml-field-hint">Ngày bắt đầu học chính thức (≥ khai giảng).</small>
                        @error('ngay_mo_lop') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="ml-date-arrow"><i class="fas fa-arrow-right"></i></div>

                    <div class="ml-date-field">
                        <span class="ml-step-num">3</span>
                        <label for="ngay_ket_thuc" class="ml-field-label">
                            <i class="fas fa-flag"></i> Ngày kết thúc <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="ngay_ket_thuc" id="ngay_ket_thuc"
                               class="form-control vip-form-control ml-date-input @error('ngay_ket_thuc') is-invalid @enderror"
                               value="{{ old('ngay_ket_thuc') }}" required>
                        <small class="ml-field-hint">Ngày kết thúc toàn chương trình (sau ngày bắt đầu).</small>
                        @error('ngay_ket_thuc') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="ml-duration-display" id="durationDisplay" hidden>
                    <i class="fas fa-stopwatch"></i>
                    <span>Tổng thời lượng dự kiến: <strong id="durationText">—</strong></span>
                </div>
            </div>
        </section>

        {{-- ========== ③ Phân công giảng viên ========== --}}
        <section class="apx-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">3</span>
                    <div>
                        <h2><i class="fas fa-chalkboard-user"></i> Phân công giảng viên</h2>
                        <p>Tùy chọn — bạn có thể bỏ qua và phân công sau ở trang chi tiết lớp.</p>
                    </div>
                </div>
                <span class="apx-meta-pill"><strong>Tuỳ chọn</strong></span>
            </header>

            <div class="ml-form-card">
                <div class="ml-assign-grid">
                    @foreach($khoaHocMau->moduleHocs as $module)
                        @php $oldGvId = old("giang_vien_modules.{$module->id}"); @endphp
                        <div class="ml-assign-card" data-has-gv="{{ $oldGvId ? 'true' : 'false' }}">
                            <div class="ml-assign-header">
                                <span class="ml-assign-num">M{{ str_pad($module->thu_tu_module, 2, '0', STR_PAD_LEFT) }}</span>
                                <div class="ml-assign-info">
                                    <strong>{{ $module->ten_module }}</strong>
                                    <small><i class="far fa-clock"></i> {{ $module->thoi_luong_du_kien_label }}</small>
                                </div>
                                <span class="ml-assign-status">
                                    <i class="fas fa-check-circle"></i> Đã gán
                                </span>
                            </div>

                            <div class="ml-gv-preview">
                                <span class="ml-gv-avatar"><i class="fas fa-user"></i></span>
                                <div class="ml-gv-info">
                                    <strong class="ml-gv-name">— Chưa chọn giảng viên —</strong>
                                    <small class="ml-gv-meta">Click bên dưới để chọn</small>
                                </div>
                            </div>

                            <div class="ml-select-wrap">
                                <i class="fas fa-chalkboard-user ml-select-icon"></i>
                                <select name="giang_vien_modules[{{ $module->id }}]"
                                        class="form-select ml-gv-select"
                                        data-module-id="{{ $module->id }}">
                                    <option value="">— Chọn sau —</option>
                                    @foreach($giangViens as $gv)
                                        <option value="{{ $gv->id }}"
                                                data-name="{{ $gv->nguoiDung->ho_ten }}"
                                                data-init="{{ mb_strtoupper(mb_substr($gv->nguoiDung->ho_ten, 0, 1)) }}"
                                                data-specialty="{{ $gv->chuyen_nganh ?: '' }}"
                                                data-degree="{{ $gv->hoc_vi ?: '' }}"
                                                {{ $oldGvId == $gv->id ? 'selected' : '' }}>
                                            {{ $gv->nguoiDung->ho_ten }}{{ $gv->chuyen_nganh ? ' · ' . $gv->chuyen_nganh : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="ml-clear-btn" title="Bỏ chọn" tabindex="-1" hidden>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="ml-info-note">
                    <i class="fas fa-circle-info"></i>
                    Khi bạn phân công, hệ thống sẽ <strong>tự gửi thông báo</strong> tới giảng viên để xác nhận nhận lớp.
                </div>
            </div>
        </section>

        {{-- ========== ④ Ghi chú nội bộ ========== --}}
        <section class="apx-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">4</span>
                    <div>
                        <h2><i class="fas fa-pen-to-square"></i> Ghi chú nội bộ</h2>
                        <p>Chỉ admin nhìn thấy — dùng để theo dõi chi tiết riêng cho lớp này.</p>
                    </div>
                </div>
            </header>

            <div class="ml-form-card">
                <textarea name="ghi_chu_noi_bo" class="form-control vip-form-control"
                          rows="3"
                          placeholder="VD: Lớp sáng thứ 7 – chủ nhật, lớp doanh nghiệp ABC, học viên từ phòng nhân sự…">{{ old('ghi_chu_noi_bo') }}</textarea>
            </div>
        </section>

        {{-- ========== Submit bar ========== --}}
        <div class="ml-submit-bar">
            <a href="{{ route('admin.khoa-hoc.show', $khoaHocMau->id) }}" class="btn btn-light border fw-bold px-4">
                <i class="fas fa-times me-1"></i> Hủy
            </a>
            <button type="submit" class="btn btn-success fw-bold px-5 ml-submit-btn">
                <i class="fas fa-rocket me-2"></i>
                Tạo lớp ngay — Mã <strong>{{ $maMoiDuKien }}</strong>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const khaiGiangInput = document.getElementById('ngay_khai_giang');
    const moLopInput     = document.getElementById('ngay_mo_lop');
    const ketThucInput   = document.getElementById('ngay_ket_thuc');
    const durationBox    = document.getElementById('durationDisplay');
    const durationText   = document.getElementById('durationText');

    // Auto-set min của các trường thời gian
    khaiGiangInput.addEventListener('change', function() {
        if (this.value) {
            moLopInput.min = this.value;
            if (moLopInput.value && moLopInput.value < this.value) {
                moLopInput.value = this.value;
                moLopInput.dispatchEvent(new Event('change'));
            }
        }
        updateDuration();
    });

    moLopInput.addEventListener('change', function() {
        if (this.value) {
            const nextDay = new Date(this.value);
            nextDay.setDate(nextDay.getDate() + 1);
            ketThucInput.min = nextDay.toISOString().split('T')[0];
            if (ketThucInput.value && ketThucInput.value <= this.value) {
                ketThucInput.value = nextDay.toISOString().split('T')[0];
            }
        }
        updateDuration();
    });

    ketThucInput.addEventListener('change', updateDuration);

    // ===== Cập nhật preview giảng viên trong card phân công =====
    document.querySelectorAll('.ml-assign-card').forEach(card => {
        const select = card.querySelector('.ml-gv-select');
        const avatar = card.querySelector('.ml-gv-avatar');
        const nameEl = card.querySelector('.ml-gv-name');
        const metaEl = card.querySelector('.ml-gv-meta');
        const clearBtn = card.querySelector('.ml-clear-btn');
        if (!select) return;

        const gradients = [
            'linear-gradient(135deg, #4361ee 0%, #2f46c9 100%)',
            'linear-gradient(135deg, #16a34a 0%, #15803d 100%)',
            'linear-gradient(135deg, #d97706 0%, #b45309 100%)',
            'linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%)',
            'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
            'linear-gradient(135deg, #db2777 0%, #be185d 100%)',
        ];

        const update = () => {
            const opt = select.options[select.selectedIndex];
            const id = select.value;
            if (id) {
                const name = opt.dataset.name || opt.text;
                const init = opt.dataset.init || (name ? name[0].toUpperCase() : '?');
                const specialty = opt.dataset.specialty || '';
                const degree = opt.dataset.degree || '';
                const colorIdx = parseInt(id, 10) % gradients.length;

                avatar.innerHTML = init;
                avatar.style.background = gradients[colorIdx];
                avatar.classList.add('has-init');
                nameEl.textContent = name;
                metaEl.textContent = [degree, specialty].filter(Boolean).join(' · ') || 'Giảng viên';
                card.dataset.hasGv = 'true';
                clearBtn.removeAttribute('hidden');
            } else {
                avatar.innerHTML = '<i class="fas fa-user"></i>';
                avatar.style.background = '';
                avatar.classList.remove('has-init');
                nameEl.textContent = '— Chưa chọn giảng viên —';
                metaEl.textContent = 'Click bên dưới để chọn';
                card.dataset.hasGv = 'false';
                clearBtn.setAttribute('hidden', '');
            }
        };

        select.addEventListener('change', update);
        clearBtn.addEventListener('click', () => {
            select.value = '';
            update();
        });
        update(); // initial sync (cho trường hợp old() có sẵn)
    });

    function updateDuration() {
        if (moLopInput.value && ketThucInput.value) {
            const start = new Date(moLopInput.value);
            const end = new Date(ketThucInput.value);
            const days = Math.round((end - start) / (1000 * 60 * 60 * 24));
            if (days > 0) {
                const weeks = Math.floor(days / 7);
                const remDays = days % 7;
                let label = days + ' ngày';
                if (weeks > 0) label += ' (' + weeks + ' tuần' + (remDays ? ' + ' + remDays + ' ngày' : '') + ')';
                durationText.textContent = label;
                durationBox.removeAttribute('hidden');
            } else {
                durationBox.setAttribute('hidden', '');
            }
        } else {
            durationBox.setAttribute('hidden', '');
        }
    }
});
</script>

@include('pages.admin.partials._admin-page-styles')

<style>
    /* ===== Mở lớp page styles ===== */
    .ml-summary {
        display: grid;
        grid-template-columns: 1fr 280px;
        gap: 0;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }

    .ml-summary-main { padding: 20px 24px; }

    .ml-name-block { margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px dashed #e2e8f0; }
    .ml-name-block h3 { font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 4px 0 0; }

    .ml-label {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 0.7rem; font-weight: 700; color: #64748b;
        text-transform: uppercase; letter-spacing: 0.5px;
    }
    .ml-label i { color: #1d4ed8; font-size: 0.7rem; }

    .ml-meta-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }

    .ml-meta { display: flex; flex-direction: column; gap: 4px; }
    .ml-value { font-size: 0.92rem; color: #0f172a; font-weight: 700; }
    .ml-meta code.ml-value { background: #eef2ff; color: #1d4ed8; padding: 2px 8px; border-radius: 6px; display: inline-block; font-size: 0.85rem; }

    .ml-cap-badge {
        display: inline-block; padding: 3px 10px; border-radius: 999px;
        font-size: 0.74rem; font-weight: 800;
    }
    .ml-cap-badge.cap-co_ban { background: #dcfce7; color: #16a34a; }
    .ml-cap-badge.cap-trung_binh { background: #fef3c7; color: #c2410c; }
    .ml-cap-badge.cap-nang_cao { background: #fee2e2; color: #b91c1c; }

    .ml-summary-side {
        background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
        padding: 20px 22px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        justify-content: center;
        border-left: 1px solid #bfdbfe;
    }

    .ml-side-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 14px;
        background: #fff;
        border-radius: 10px;
        border: 1px solid rgba(29, 78, 216, 0.12);
    }
    .ml-side-row span { font-size: 0.78rem; color: #64748b; font-weight: 600; }
    .ml-side-row span i { color: #1d4ed8; margin-right: 5px; }
    .ml-side-row strong { font-size: 0.95rem; color: #0f172a; font-weight: 800; }

    .ml-side-highlight {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
        border-color: transparent !important;
    }
    .ml-side-highlight span,
    .ml-side-highlight strong { color: #fff !important; }
    .ml-side-highlight span i { color: #bbf7d0 !important; }
    .ml-side-highlight .ml-new-code {
        font-family: 'Consolas', monospace;
        font-size: 1.05rem;
        letter-spacing: 0.5px;
    }

    /* Module list collapsible */
    .ml-modules {
        margin-top: 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }
    .ml-modules summary {
        list-style: none;
        cursor: pointer;
        padding: 12px 18px;
        display: flex; align-items: center; gap: 10px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        transition: background 0.2s ease;
    }
    .ml-modules summary::-webkit-details-marker { display: none; }
    .ml-modules summary:hover { background: #eff6ff; }
    .ml-modules summary > i:first-child { color: #1d4ed8; }
    .ml-modules summary strong { font-size: 0.92rem; color: #0f172a; }
    .ml-modules summary small { color: #64748b; font-size: 0.78rem; flex: 1; }
    .ml-modules-toggle {
        font-size: 0.75rem; color: #94a3b8;
        transition: transform 0.25s ease;
    }
    .ml-modules[open] .ml-modules-toggle { transform: rotate(180deg); color: #1d4ed8; }
    .ml-modules[open] summary { border-bottom-color: #bfdbfe; }

    .ml-modules-body { padding: 0; }
    .ml-modules-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
        font-size: 0.85rem;
    }
    .ml-modules-table thead {
        background: #f8fafc;
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.5px;
    }
    .ml-modules-table th { padding: 8px 12px; font-weight: 700; }
    .ml-modules-table td { padding: 10px 12px; border-top: 1px solid #f1f5f9; }
    .ml-modules-table tbody tr:hover { background: #f8fafc; }

    .ml-mod-num {
        display: inline-grid; place-items: center;
        width: 26px; height: 26px;
        border-radius: 50%;
        background: #eef2ff;
        color: #1d4ed8;
        font-size: 0.78rem;
        font-weight: 800;
    }

    .ml-modules-note {
        padding: 10px 16px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        font-size: 0.78rem;
        color: #64748b;
        line-height: 1.45;
    }
    .ml-modules-note i { color: #1d4ed8; margin-right: 5px; }
    .ml-modules-note code { background: #eef2ff; color: #1d4ed8; padding: 1px 6px; border-radius: 4px; font-size: 0.78rem; }

    /* ===== Form card chung ===== */
    .ml-form-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 22px;
    }

    .ml-required-pill {
        background: #fee2e2 !important;
        border-color: #fca5a5 !important;
        color: #b91c1c !important;
    }
    .ml-required-pill strong { color: #b91c1c !important; }

    /* ===== Date grid (3 mốc) ===== */
    .ml-date-grid {
        display: grid;
        grid-template-columns: 1fr 32px 1fr 32px 1fr;
        gap: 14px;
        align-items: stretch;
    }

    .ml-date-field {
        position: relative;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 18px 14px;
        transition: all 0.25s ease;
    }
    .ml-date-field:hover {
        border-color: #1d4ed8;
        box-shadow: 0 8px 20px rgba(29, 78, 216, 0.08);
        background: #fff;
    }
    .ml-date-field:focus-within {
        border-color: #1d4ed8;
        box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.12);
        background: #fff;
    }

    .ml-step-num {
        position: absolute;
        top: -10px; left: 14px;
        width: 24px; height: 24px;
        background: linear-gradient(135deg, #1d4ed8 0%, #2f46c9 100%);
        color: #fff;
        border-radius: 50%;
        display: grid; place-items: center;
        font-weight: 900;
        font-size: 0.78rem;
        box-shadow: 0 4px 10px rgba(29, 78, 216, 0.3);
    }

    .ml-field-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 800;
        color: #0f172a;
        margin: 4px 0 8px;
    }
    .ml-field-label i { color: #1d4ed8; margin-right: 5px; font-size: 0.78rem; }

    .ml-date-input {
        font-size: 0.9rem;
        font-weight: 600;
        background: #fff;
    }

    .ml-field-hint {
        display: block;
        margin-top: 6px;
        font-size: 0.73rem;
        color: #94a3b8;
        font-style: italic;
    }

    .ml-date-arrow {
        display: grid;
        place-items: center;
        color: #94a3b8;
        font-size: 1.05rem;
    }

    .ml-duration-display {
        margin-top: 16px;
        padding: 10px 16px;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border: 1px solid #6ee7b7;
        border-radius: 10px;
        color: #047857;
        font-size: 0.86rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .ml-duration-display strong { color: #047857; font-weight: 800; }

    /* ===== Phân công giảng viên — combobox card ===== */
    .ml-assign-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 14px;
        margin-bottom: 14px;
    }

    .ml-assign-card {
        position: relative;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px;
        transition: all 0.25s ease;
        overflow: hidden;
    }

    .ml-assign-card::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, #cbd5e1 0%, #94a3b8 100%);
        transition: background 0.25s ease;
    }

    .ml-assign-card:hover {
        border-color: #bfdbfe;
        box-shadow: 0 8px 22px rgba(29, 78, 216, 0.08);
        transform: translateY(-1px);
    }

    .ml-assign-card[data-has-gv="true"] {
        border-color: #6ee7b7;
        background: linear-gradient(135deg, #fff 0%, #f0fdf4 100%);
    }
    .ml-assign-card[data-has-gv="true"]::before {
        background: linear-gradient(180deg, #16a34a 0%, #15803d 100%);
    }

    /* Header: số module + tên module + status */
    .ml-assign-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px dashed #e2e8f0;
    }

    .ml-assign-num {
        flex-shrink: 0;
        padding: 5px 12px;
        background: linear-gradient(135deg, #1d4ed8 0%, #2f46c9 100%);
        color: #fff;
        border-radius: 8px;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 10px rgba(29, 78, 216, 0.25);
    }

    .ml-assign-info { flex: 1; min-width: 0; }
    .ml-assign-info strong {
        display: block;
        font-size: 0.9rem;
        color: #0f172a;
        font-weight: 700;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .ml-assign-info small {
        display: block;
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 2px;
    }
    .ml-assign-info small i { color: #1d4ed8; margin-right: 3px; }

    .ml-assign-status {
        display: none;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        background: #dcfce7;
        color: #16a34a;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 800;
    }
    .ml-assign-card[data-has-gv="true"] .ml-assign-status { display: inline-flex; }

    /* Preview giảng viên */
    .ml-gv-preview {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        margin-bottom: 10px;
        transition: all 0.25s ease;
    }
    .ml-assign-card[data-has-gv="true"] .ml-gv-preview {
        background: #fff;
        border: 1px solid #6ee7b7;
        border-style: solid;
    }

    .ml-gv-avatar {
        flex-shrink: 0;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #e2e8f0;
        color: #94a3b8;
        display: grid;
        place-items: center;
        font-size: 1.1rem;
        font-weight: 800;
        letter-spacing: 0;
        transition: all 0.25s ease;
    }
    .ml-gv-avatar.has-init {
        color: #fff;
        font-size: 1.05rem;
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
    }

    .ml-gv-info { flex: 1; min-width: 0; }
    .ml-gv-name {
        display: block;
        font-size: 0.88rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .ml-assign-card:not([data-has-gv="true"]) .ml-gv-name { color: #94a3b8; font-style: italic; font-weight: 600; }

    .ml-gv-meta {
        display: block;
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Wrapper select + custom appearance */
    .ml-select-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .ml-select-icon {
        position: absolute;
        left: 14px;
        color: #1d4ed8;
        font-size: 0.85rem;
        pointer-events: none;
        z-index: 2;
        transition: all 0.2s ease;
    }

    .ml-gv-select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        width: 100%;
        padding: 10px 38px 10px 36px;
        background-color: #fff;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%231d4ed8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 600;
        color: #0f172a;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .ml-gv-select:hover {
        border-color: #93c5fd;
        background-color: #f8fafc;
    }

    .ml-gv-select:focus {
        outline: 0;
        border-color: #1d4ed8;
        box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.15);
        background-color: #fff;
    }

    .ml-assign-card[data-has-gv="true"] .ml-gv-select {
        border-color: #6ee7b7;
        background-color: #f0fdf4;
    }
    .ml-assign-card[data-has-gv="true"] .ml-gv-select:hover {
        border-color: #16a34a;
        background-color: #ecfdf5;
    }
    .ml-assign-card[data-has-gv="true"] .ml-select-icon {
        color: #16a34a;
    }

    .ml-gv-select option {
        padding: 8px;
        font-weight: 500;
    }
    .ml-gv-select option:checked { background: linear-gradient(0deg, #1d4ed8, #1d4ed8); color: #fff; }

    .ml-clear-btn {
        position: absolute;
        right: 36px;
        width: 24px;
        height: 24px;
        border: 0;
        border-radius: 50%;
        background: #fee2e2;
        color: #dc2626;
        cursor: pointer;
        display: grid;
        place-items: center;
        font-size: 0.7rem;
        z-index: 3;
        transition: all 0.2s ease;
    }
    .ml-clear-btn:hover { background: #dc2626; color: #fff; transform: scale(1.1); }
    .ml-clear-btn[hidden] { display: none; }

    .ml-info-note {
        padding: 10px 14px;
        background: #eff6ff;
        border-left: 3px solid #1d4ed8;
        border-radius: 8px;
        font-size: 0.82rem;
        color: #1e40af;
        line-height: 1.5;
    }
    .ml-info-note i { color: #1d4ed8; margin-right: 5px; }
    .ml-info-note strong { color: #1d4ed8; }

    /* ===== Submit bar ===== */
    .ml-submit-bar {
        display: flex;
        gap: 14px;
        justify-content: flex-end;
        align-items: center;
        padding: 18px 22px;
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .ml-submit-btn {
        position: relative;
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
        border: 0 !important;
        color: #fff !important;
        padding: 12px 28px !important;
        font-size: 0.95rem !important;
        overflow: hidden;
        transition: all 0.25s ease;
    }
    .ml-submit-btn::before {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 60%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: mlBtnShine 3s ease-in-out infinite;
    }
    @keyframes mlBtnShine {
        0%, 60% { left: -100%; }
        100%    { left: 130%; }
    }
    .ml-submit-btn:hover {
        background: linear-gradient(135deg, #15803d 0%, #14532d 100%) !important;
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(22, 163, 74, 0.4);
        color: #fff !important;
    }
    .ml-submit-btn strong { font-weight: 900; }

    /* ===== Responsive ===== */
    @media (max-width: 991.98px) {
        .ml-summary { grid-template-columns: 1fr; }
        .ml-summary-side { border-left: 0; border-top: 1px solid #bfdbfe; }
        .ml-date-grid { grid-template-columns: 1fr; gap: 10px; }
        .ml-date-arrow { transform: rotate(90deg); padding: 4px 0; }
        .ml-assign-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 720px) {
        .ml-meta-grid { grid-template-columns: 1fr; }
        .ml-assign-header { flex-wrap: wrap; }
        .ml-assign-status { order: 99; margin-left: auto; }
    }
</style>
@endsection
