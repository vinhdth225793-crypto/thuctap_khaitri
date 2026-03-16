{{-- Modal Thêm Buổi Lẻ --}}
<div class="modal fade shadow" id="modalThemBuoi" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold">Thêm buổi học lẻ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.khoa-hoc.lich-hoc.store', $khoaHoc->id) }}" method="POST">
                @csrf
                <input type="hidden" name="module_hoc_id" id="single-module-id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="smaller text-muted text-uppercase fw-bold mb-1">Module:</label>
                        <div id="single-module-name" class="fw-bold fs-5 text-dark border-bottom pb-2"></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Ngày học *</label>
                            <input type="date" name="ngay_hoc" class="form-control vip-form-control" required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Giờ bắt đầu *</label>
                            <input type="time" name="gio_bat_dau" class="form-control vip-form-control" required value="18:00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Giờ kết thúc *</label>
                            <input type="time" name="gio_ket_thuc" class="form-control vip-form-control" required value="20:00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Thứ trong tuần *</label>
                            <select name="thu_trong_tuan" class="form-select vip-form-control" required>
                                @foreach(\App\Models\LichHoc::$thuLabels as $val => $lbl)
                                    <option value="{{ $val }}">{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Hình thức *</label>
                            <select name="hinh_thuc" class="form-select vip-form-control" required id="single-hinh-thuc">
                                <option value="truc_tiep">Trực tiếp (Tại trung tâm)</option>
                                <option value="online">Online (Qua link họp)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Phòng / Link họp</label>
                            <input type="text" name="phong_hoc" id="single-location" class="form-control vip-form-control" placeholder="Phòng học hoặc URL...">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Giảng viên (Tùy chọn)</label>
                            <select name="giang_vien_id" class="form-select vip-form-control">
                                <option value="">-- Mặc định theo module --</option>
                                @foreach($giangViens as $gv)
                                    <option value="{{ $gv->id }}">{{ $gv->nguoiDung->ho_ten }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Lưu buổi học</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Sinh Lịch Tự Động --}}
<div class="modal fade shadow" id="modalSinhTuDong" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-magic me-2"></i> Sinh lịch tự động</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.khoa-hoc.lich-hoc.store-auto', $khoaHoc->id) }}" method="POST">
                @csrf
                <input type="hidden" name="module_hoc_id" id="auto-module-id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="smaller text-muted text-uppercase fw-bold mb-1">Module:</label>
                        <div id="auto-module-name" class="fw-bold fs-5 text-dark border-bottom pb-2"></div>
                    </div>
                    
                    <div class="alert alert-info border-0 smaller mb-4">
                        <i class="fas fa-info-circle me-1"></i> Hệ thống sẽ xóa các buổi có trạng thái <strong>"Chờ"</strong> cũ và tạo mới đủ số buổi quy định của module.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="bg-light p-3 rounded border mb-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="smaller text-muted fw-bold d-block text-uppercase">Số buổi cần tạo</span>
                                    <span id="auto-so-buoi-text" class="fw-bold fs-5 text-primary">0 buổi</span>
                                </div>
                                <div class="text-end">
                                    <span class="smaller text-muted fw-bold d-block text-uppercase">Dự kiến kết thúc</span>
                                    <span id="auto-end-date-text" class="fw-bold fs-5 text-dark">--/--/----</span>
                                </div>
                            </div>
                            <div id="auto-conflict-warning" class="alert alert-danger border-0 smaller py-2 d-none">
                                <i class="fas fa-exclamation-triangle me-1"></i> <strong>Cảnh báo:</strong> Lộ trình vượt quá ngày kết thúc khóa học (<span id="auto-course-end-date"></span>).
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Ngày bắt đầu áp dụng *</label>
                            <input type="date" name="ngay_bat_dau" id="auto-start-date" class="form-control vip-form-control" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Giờ bắt đầu *</label>
                            <input type="time" name="gio_bat_dau" class="form-control vip-form-control" required value="18:00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Giờ kết thúc *</label>
                            <input type="time" name="gio_ket_thuc" class="form-control vip-form-control" required value="20:00">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold d-block">Các thứ trong tuần *</label>
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
                                <span><span class="legend-box bg-danger"></span> Ngày bắt đầu</span>
                                <span><span class="legend-box bg-success"></span> Cùng tuần</span>
                                <span><span class="legend-box bg-warning"></span> Tuần mới</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Hình thức *</label>
                            <select name="hinh_thuc" class="form-select vip-form-control" required>
                                <option value="truc_tiep">Trực tiếp</option>
                                <option value="online">Online</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Phòng học (Nếu có)</label>
                            <input type="text" name="phong_hoc" class="form-control vip-form-control" placeholder="VD: Phòng 101">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 justify-content-center gap-2">
                    <button type="button" class="btn btn-light px-4 fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm">Bắt đầu sinh lịch</button>
                </div>
            </form>
        </div>
    </div>
</div>
