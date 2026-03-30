@php
    use App\Support\Scheduling\TeachingPeriodCatalog;

    $sessionOptions = TeachingPeriodCatalog::sessions();
    $periodDefinitions = TeachingPeriodCatalog::periods();
@endphp

{{-- Modal them buoi hoc le --}}
<div class="modal fade shadow" id="modalThemBuoi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white border-0">
                <div>
                    <h5 class="modal-title fw-bold mb-1">Them buoi hoc le</h5>
                    <div class="small opacity-75">Chon ngay hoc, ca hoc hoac khung tiet de he thong planning context tu kiem tra.</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.khoa-hoc.lich-hoc.store', $khoaHoc->id) }}" method="POST" id="single-schedule-form">
                @csrf
                <input type="hidden" name="module_hoc_id" id="single-module-id">
                <input type="hidden" name="tiet_bat_dau" id="single-tiet-bat-dau">
                <input type="hidden" name="tiet_ket_thuc" id="single-tiet-ket-thuc">
                <input type="hidden" name="buoi_hoc" id="single-buoi-hoc">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold mb-1 d-block">Module</label>
                        <div id="single-module-name" class="fw-bold fs-5 text-dark border-bottom pb-2"></div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Ngay hoc *</label>
                            <input type="date" name="ngay_hoc" id="single-date" class="form-control vip-form-control" required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Khung gio map tu dong</label>
                            <input type="text" id="single-time-preview" class="form-control vip-form-control" readonly>
                            <input type="hidden" name="gio_bat_dau" id="single-start-time">
                            <input type="hidden" name="gio_ket_thuc" id="single-end-time">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold d-block">Chon nhanh theo buoi</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($sessionOptions as $session => $definition)
                                    <button type="button" class="btn btn-outline-primary schedule-session-btn" data-prefix="single" data-session="{{ $session }}">
                                        {{ $definition['label'] }} (Tiet {{ $definition['start'] }}-{{ $definition['end'] }})
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold d-block">Hoac tick tung tiet</label>
                            <div class="d-flex flex-wrap gap-2" id="single-period-grid">
                                @foreach($periodDefinitions as $period => $definition)
                                    <div class="form-check p-0 m-0">
                                        <input class="form-check-input d-none" type="checkbox" name="selected_tiets[]" value="{{ $period }}" id="single_period_{{ $period }}">
                                        <label class="period-box" for="single_period_{{ $period }}">
                                            <span class="fw-bold d-block">Tiet {{ $period }}</span>
                                            <span class="small">{{ $definition['start'] }} - {{ $definition['end'] }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Hinh thuc *</label>
                            <select name="hinh_thuc" class="form-select vip-form-control" required id="single-hinh-thuc">
                                <option value="truc_tiep">Truc tiep</option>
                                <option value="online">Online</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Phong / Link hoc</label>
                            <input type="text" name="phong_hoc" id="single-location" class="form-control vip-form-control" placeholder="Phong hoc hoac link hop">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Giang vien *</label>
                            <select name="giang_vien_id" id="single-teacher-id" class="form-select vip-form-control" required>
                                <option value="">Chon giang vien da nhan module</option>
                            </select>
                            <div class="small text-muted mt-2">
                                Danh sach duoc loc theo cac giang vien da nhan phan cong cho module nay.
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Ghi chu</label>
                            <textarea name="ghi_chu" id="single-note" rows="3" class="form-control vip-form-control" placeholder="Ghi chu noi bo cho admin neu can"></textarea>
                        </div>

                        <div class="col-12">
                            <div
                                id="single-planning-panel"
                                class="planning-panel border rounded-3 p-3 bg-light"
                                data-endpoint="{{ route('admin.khoa-hoc.lich-hoc.teacher-context', $khoaHoc->id) }}"
                            >
                                <div class="small text-muted mb-0">Chon giang vien, ngay va khung tiet de he thong kiem tra planning context.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Huy</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Luu buoi hoc</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal sinh lich tu dong --}}
<div class="modal fade shadow" id="modalSinhTuDong" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header bg-success text-white border-0">
                <div>
                    <h5 class="modal-title fw-bold mb-1"><i class="fas fa-magic me-2"></i>Sinh lich tu dong</h5>
                    <div class="small opacity-75">Chon thu, khung tiet va giang vien. He thong se preview buoi dau tien truoc khi sinh lo trinh.</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.khoa-hoc.lich-hoc.store-auto', $khoaHoc->id) }}" method="POST" id="auto-schedule-form">
                @csrf
                <input type="hidden" name="module_hoc_id" id="auto-module-id">
                <input type="hidden" name="tiet_bat_dau" id="auto-tiet-bat-dau">
                <input type="hidden" name="tiet_ket_thuc" id="auto-tiet-ket-thuc">
                <input type="hidden" name="buoi_hoc" id="auto-buoi-hoc">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold mb-1 d-block">Module</label>
                        <div id="auto-module-name" class="fw-bold fs-5 text-dark border-bottom pb-2"></div>
                    </div>

                    <div class="alert alert-info border-0 smaller mb-4">
                        <i class="fas fa-info-circle me-1"></i>
                        He thong se xoa cac buoi dang o trang thai <strong>Cho</strong> cua module nay va tao lai du so buoi quy dinh.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="bg-light p-3 rounded border mb-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="small text-muted fw-bold d-block text-uppercase">So buoi can tao</span>
                                    <span id="auto-so-buoi-text" class="fw-bold fs-5 text-primary">0 buoi</span>
                                </div>
                                <div class="text-end">
                                    <span class="small text-muted fw-bold d-block text-uppercase">Du kien ket thuc</span>
                                    <span id="auto-end-date-text" class="fw-bold fs-5 text-dark">--/--/----</span>
                                </div>
                            </div>
                            <div id="auto-conflict-warning" class="alert alert-danger border-0 smaller py-2 d-none">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Lo trinh du kien vuot qua ngay ket thuc khoa hoc (<span id="auto-course-end-date"></span>).
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Ngay bat dau *</label>
                            <input type="date" name="ngay_bat_dau" id="auto-start-date" class="form-control vip-form-control" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Giang vien *</label>
                            <select name="giang_vien_id" id="auto-teacher-id" class="form-select vip-form-control" required>
                                <option value="">Chon giang vien da nhan module</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Khung gio map tu dong</label>
                            <input type="text" id="auto-time-preview" class="form-control vip-form-control" readonly>
                            <input type="hidden" name="gio_bat_dau" id="auto-start-time">
                            <input type="hidden" name="gio_ket_thuc" id="auto-end-time">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Hinh thuc *</label>
                            <select name="hinh_thuc" id="auto-hinh-thuc" class="form-select vip-form-control" required>
                                <option value="truc_tiep">Truc tiep</option>
                                <option value="online">Online</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold d-block">Chon nhanh theo buoi</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($sessionOptions as $session => $definition)
                                    <button type="button" class="btn btn-outline-success schedule-session-btn" data-prefix="auto" data-session="{{ $session }}">
                                        {{ $definition['label'] }} (Tiet {{ $definition['start'] }}-{{ $definition['end'] }})
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold d-block">Hoac tick tung tiet</label>
                            <div class="d-flex flex-wrap gap-2" id="auto-period-grid">
                                @foreach($periodDefinitions as $period => $definition)
                                    <div class="form-check p-0 m-0">
                                        <input class="form-check-input d-none" type="checkbox" name="selected_tiets[]" value="{{ $period }}" id="auto_period_{{ $period }}">
                                        <label class="period-box" for="auto_period_{{ $period }}">
                                            <span class="fw-bold d-block">Tiet {{ $period }}</span>
                                            <span class="small">{{ $definition['start'] }} - {{ $definition['end'] }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold d-block">Cac thu trong tuan *</label>
                            <div class="d-flex flex-wrap gap-2 mt-1" id="container-thu-auto">
                                @foreach(\App\Models\LichHoc::$thuLabels as $val => $lbl)
                                    <div class="form-check p-0 m-0">
                                        <input class="form-check-input d-none" type="checkbox" name="thu_trong_tuan[]" value="{{ $val }}" id="thu_auto_{{ $val }}" {{ in_array($val, [2, 4, 6]) ? 'checked' : '' }}>
                                        <label class="thu-label-box" for="thu_auto_{{ $val }}" data-thu="{{ $val }}">
                                            {{ $lbl }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-2 smaller d-flex gap-3">
                                <span><span class="legend-box bg-danger"></span>Ngay bat dau</span>
                                <span><span class="legend-box bg-success"></span>Cung tuan</span>
                                <span><span class="legend-box bg-warning"></span>Tuan moi</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Phong / Link hoc</label>
                            <input type="text" name="phong_hoc" id="auto-location" class="form-control vip-form-control" placeholder="Phong hoc hoac link hop">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Ghi chu</label>
                            <textarea name="ghi_chu" id="auto-note" rows="3" class="form-control vip-form-control" placeholder="Ghi chu noi bo cho ca lo trinh"></textarea>
                        </div>

                        <div class="col-12">
                            <div
                                id="auto-planning-panel"
                                class="planning-panel border rounded-3 p-3 bg-light"
                                data-endpoint="{{ route('admin.khoa-hoc.lich-hoc.teacher-context', $khoaHoc->id) }}"
                            >
                                <div class="small text-muted mb-0">Preview buoi dau tien de xem assignment, khung day chuan, don nghi va xung dot.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Huy</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm">Bat dau sinh lich</button>
                </div>
            </form>
        </div>
    </div>
</div>

