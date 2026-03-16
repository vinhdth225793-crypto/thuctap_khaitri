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
                            <label class="form-label small fw-bold">Ngày bắt đầu áp dụng *</label>
                            <input type="date" name="ngay_bat_dau" class="form-control vip-form-control" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
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
                            <div class="d-flex flex-wrap gap-3 mt-1">
                                @foreach(\App\Models\LichHoc::$thuLabels as $val => $lbl)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="thu_trong_tuan[]" value="{{ $val }}" id="thu_auto_{{ $val }}" {{ in_array($val, [2, 4, 6]) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="thu_auto_{{ $val }}">{{ $lbl }}</label>
                                    </div>
                                @endforeach
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
