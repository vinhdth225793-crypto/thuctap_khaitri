<div class="form-group">
    <label for="ho_ten">Họ tên</label>
    <input type="text" name="ho_ten" id="ho_ten" class="form-control" value="{{ old('ho_ten', $user->ho_ten) }}">
</div>
<div class="form-group">
    <label for="email">Email</label>
    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}">
</div>
<div class="form-group">
    <label for="mat_khau">Mật khẩu</label>
    <input type="password" name="mat_khau" id="mat_khau" class="form-control">
</div>
<div class="form-group">
    <label for="mat_khau_confirmation">Xác nhận mật khẩu</label>
    <input type="password" name="mat_khau_confirmation" id="mat_khau_confirmation" class="form-control">
</div>
<div class="form-group">
    <label for="vai_tro">Vai trò</label>
    <select name="vai_tro" id="vai_tro" class="form-control">
        <option value="admin" {{ old('vai_tro', $user->vai_tro)== 'admin' ? 'selected' : '' }}>Admin</option>
        <option value="giang_vien" {{ old('vai_tro', $user->vai_tro)== 'giang_vien' ? 'selected' : '' }}>Giảng viên</option>
        <option value="hoc_vien" {{ old('vai_tro', $user->vai_tro)== 'hoc_vien' ? 'selected' : '' }}>Học viên</option>
    </select>
</div>
<div class="form-group">
    <label for="so_dien_thoai">Số điện thoại</label>
    <input type="text" name="so_dien_thoai" id="so_dien_thoai" class="form-control" value="{{ old('so_dien_thoai', $user->so_dien_thoai) }}">
</div>
<div class="form-group">
    <label for="ngay_sinh">Ngày sinh</label>
    <input type="date" name="ngay_sinh" id="ngay_sinh" class="form-control" value="{{ old('ngay_sinh', optional($user->ngay_sinh)->format('Y-m-d')) }}">
</div>
<div class="form-group">
    <label for="dia_chi">Địa chỉ</label>
    <textarea name="dia_chi" id="dia_chi" class="form-control">{{ old('dia_chi', $user->dia_chi) }}</textarea>
</div>
<div class="form-group">
    <label for="anh_dai_dien">Ảnh đại diện</label>
    <input type="file" name="anh_dai_dien" id="anh_dai_dien" class="form-control-file" accept="image/*">
    @if($user->anh_dai_dien)
        <div class="mt-2">
            <img src="{{ asset('images/'.$user->anh_dai_dien) }}" alt="Ảnh đại diện" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #ddd;">
        </div>
    @endif
    <small class="form-text text-muted">Chọn ảnh mới để thay đổi ảnh đại diện (không bắt buộc)</small>
</div>
<div class="form-group">
    <label for="trang_thai">Trạng thái</label>
    <select name="trang_thai" id="trang_thai" class="form-control">
        <option value="1" {{ old('trang_thai', $user->trang_thai) ? 'selected' : '' }}>Hoạt động</option>
        <option value="0" {{ !old('trang_thai', $user->trang_thai) ? 'selected' : '' }}>Khóa</option>
    </select>
</div>