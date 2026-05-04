@php
    $isEdit = isset($user) && $user->exists;
    $avatarUrl = ($isEdit && $user->anh_dai_dien) ? asset('storage/' . $user->anh_dai_dien) : null;
    $initial = $isEdit && $user->ho_ten
        ? mb_strtoupper(mb_substr(trim($user->ho_ten), 0, 1, 'UTF-8'), 'UTF-8')
        : '?';
@endphp

<div class="row g-4">
    {{-- Sidebar trái: avatar + meta --}}
    <div class="col-lg-4">
        <section class="apx-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">1</span>
                    <div>
                        <h2><i class="fas fa-camera"></i> Ảnh đại diện</h2>
                        <p>Tải lên ảnh để hiển thị trên hồ sơ.</p>
                    </div>
                </div>
            </header>

            <div class="prof-avatar-card">
                <div class="prof-avatar-preview">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="avatar" id="profAvatarPreview">
                    @else
                        <div class="prof-avatar-fallback" id="profAvatarPreview">{{ $initial }}</div>
                    @endif
                </div>
                @if($isEdit && $user->anh_dai_dien)
                    <label class="prof-remove-row">
                        <input type="checkbox" name="xoa_anh_dai_dien" value="1">
                        <span><i class="fas fa-trash"></i> Xóa ảnh hiện tại</span>
                    </label>
                @endif
                <label for="anh_dai_dien" class="prof-upload-btn">
                    <i class="fas fa-upload"></i> Tải ảnh mới
                    <input type="file" name="anh_dai_dien" id="anh_dai_dien" accept="image/*" hidden onchange="previewProfAvatar(this)">
                </label>
                @error('anh_dai_dien')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                <div class="prof-upload-hint">
                    <i class="fas fa-circle-info"></i> JPG, PNG, GIF — tối đa 2 MB
                </div>
            </div>
        </section>

        @if($isEdit)
            <section class="apx-section">
                <header class="apx-section-head">
                    <div class="apx-section-title">
                        <span class="apx-section-num">2</span>
                        <div>
                            <h2><i class="fas fa-id-badge"></i> Thông tin tài khoản</h2>
                            <p>Tóm tắt nhanh tài khoản hiện tại.</p>
                        </div>
                    </div>
                </header>

                <div class="prof-meta-card">
                    <div class="prof-meta-row">
                        <span class="prof-meta-label">Mã NV</span>
                        <strong>#{{ $user->ma_nguoi_dung }}</strong>
                    </div>
                    <div class="prof-meta-row">
                        <span class="prof-meta-label">Vai trò hiện tại</span>
                        @php
                            $roleClass = match($user->vai_tro){'admin'=>'is-danger','giang_vien'=>'is-info',default=>'is-success'};
                            $roleIcon  = match($user->vai_tro){'admin'=>'fa-shield-halved','giang_vien'=>'fa-chalkboard-teacher',default=>'fa-user-graduate'};
                            $roleLabel = match($user->vai_tro){'admin'=>'Admin','giang_vien'=>'Giảng viên',default=>'Học viên'};
                        @endphp
                        <span class="prof-pill {{ $roleClass }}"><i class="fas {{ $roleIcon }}"></i> {{ $roleLabel }}</span>
                    </div>
                    <div class="prof-meta-row">
                        <span class="prof-meta-label">Trạng thái</span>
                        @if($user->trang_thai)
                            <span class="prof-pill is-success"><i class="fas fa-check"></i> Hoạt động</span>
                        @else
                            <span class="prof-pill is-warning"><i class="fas fa-pause"></i> Khóa</span>
                        @endif
                    </div>
                    <div class="prof-meta-row">
                        <span class="prof-meta-label">Tạo lúc</span>
                        <strong>{{ optional($user->created_at)->format('d/m/Y') }}</strong>
                    </div>
                    @if($user->trashed())
                        <div class="prof-meta-row">
                            <span class="prof-meta-label">Đã xóa lúc</span>
                            <span class="prof-pill is-danger"><i class="fas fa-trash"></i> {{ optional($user->deleted_at)->format('d/m/Y') }}</span>
                        </div>
                    @endif
                </div>
            </section>
        @endif
    </div>

    {{-- Main: form fields --}}
    <div class="col-lg-8">
        <section class="apx-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">{{ $isEdit ? '3' : '2' }}</span>
                    <div>
                        <h2><i class="fas fa-user-pen"></i> Thông tin cơ bản</h2>
                        <p>Họ tên, email, số điện thoại, ngày sinh, địa chỉ.</p>
                    </div>
                </div>
            </header>

            <div class="prof-form-card">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="prof-label">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" name="ho_ten" class="form-control prof-input @error('ho_ten') is-invalid @enderror" value="{{ old('ho_ten', $user->ho_ten) }}" required>
                        @error('ho_ten')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="prof-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control prof-input @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="prof-label">Số điện thoại</label>
                        <input type="text" name="so_dien_thoai" class="form-control prof-input @error('so_dien_thoai') is-invalid @enderror" value="{{ old('so_dien_thoai', $user->so_dien_thoai) }}">
                        @error('so_dien_thoai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="prof-label">Ngày sinh</label>
                        <input type="date" name="ngay_sinh" class="form-control prof-input @error('ngay_sinh') is-invalid @enderror" value="{{ old('ngay_sinh', optional($user->ngay_sinh)->format('Y-m-d')) }}">
                        @error('ngay_sinh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="prof-label">Địa chỉ</label>
                        <textarea name="dia_chi" rows="3" class="form-control prof-input @error('dia_chi') is-invalid @enderror">{{ old('dia_chi', $user->dia_chi) }}</textarea>
                        @error('dia_chi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </section>

        {{-- Phân quyền & trạng thái (admin-only) --}}
        <section class="apx-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">{{ $isEdit ? '4' : '3' }}</span>
                    <div>
                        <h2><i class="fas fa-user-shield"></i> Phân quyền &amp; trạng thái</h2>
                        <p>Chọn vai trò và trạng thái tài khoản.</p>
                    </div>
                </div>
            </header>

            <div class="prof-form-card">
                <label class="prof-label"><i class="fas fa-user-tag"></i> Vai trò <span class="text-danger">*</span></label>
                <div class="qltk-role-grid">
                    @php
                        $roles = [
                            'admin'      => ['icon' => 'fa-shield-halved',     'label' => 'Quản trị viên', 'desc' => 'Toàn quyền hệ thống', 'class' => 'is-danger'],
                            'giang_vien' => ['icon' => 'fa-chalkboard-teacher','label' => 'Giảng viên',     'desc' => 'Soạn bài, chấm điểm', 'class' => 'is-info'],
                            'hoc_vien'   => ['icon' => 'fa-user-graduate',     'label' => 'Học viên',       'desc' => 'Tham gia khóa học',   'class' => 'is-success'],
                        ];
                        $currentRole = old('vai_tro', $user->vai_tro ?? 'hoc_vien');
                    @endphp
                    @foreach($roles as $value => $meta)
                        <label class="qltk-role-item {{ $meta['class'] }}">
                            <input type="radio" name="vai_tro" value="{{ $value }}" @checked($currentRole === $value)>
                            <div class="qltk-role-icon"><i class="fas {{ $meta['icon'] }}"></i></div>
                            <div class="qltk-role-info">
                                <strong>{{ $meta['label'] }}</strong>
                                <small>{{ $meta['desc'] }}</small>
                            </div>
                            <i class="fas fa-check qltk-role-check"></i>
                        </label>
                    @endforeach
                </div>
                @error('vai_tro')<div class="text-danger small mt-2">{{ $message }}</div>@enderror

                <hr class="my-4">

                <label class="prof-label"><i class="fas fa-toggle-on"></i> Trạng thái tài khoản</label>
                <div class="qltk-status-grid">
                    @php $currentStatus = (int) old('trang_thai', $user->trang_thai ?? 1); @endphp
                    <label class="qltk-status-item is-success">
                        <input type="radio" name="trang_thai" value="1" @checked($currentStatus === 1)>
                        <div class="qltk-status-icon"><i class="fas fa-circle-check"></i></div>
                        <div>
                            <strong>Hoạt động</strong>
                            <small>Cho phép đăng nhập và sử dụng hệ thống</small>
                        </div>
                    </label>
                    <label class="qltk-status-item is-warning">
                        <input type="radio" name="trang_thai" value="0" @checked($currentStatus === 0)>
                        <div class="qltk-status-icon"><i class="fas fa-circle-pause"></i></div>
                        <div>
                            <strong>Tạm khóa</strong>
                            <small>Ngăn không cho đăng nhập</small>
                        </div>
                    </label>
                </div>
                @error('trang_thai')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            </div>
        </section>

        {{-- Mật khẩu --}}
        <section class="apx-section">
            <header class="apx-section-head">
                <div class="apx-section-title">
                    <span class="apx-section-num">{{ $isEdit ? '5' : '4' }}</span>
                    <div>
                        <h2><i class="fas fa-key"></i> {{ $isEdit ? 'Đặt lại mật khẩu' : 'Mật khẩu khởi tạo' }}</h2>
                        <p>{{ $isEdit ? 'Để trống nếu không muốn đổi. Tối thiểu 8 ký tự.' : 'Bắt buộc khi tạo mới. Tối thiểu 8 ký tự.' }}</p>
                    </div>
                </div>
            </header>

            <div class="prof-form-card">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="prof-label">Mật khẩu {{ $isEdit ? 'mới' : '' }} @if(!$isEdit)<span class="text-danger">*</span>@endif</label>
                        <input type="password" name="mat_khau" class="form-control prof-input @error('mat_khau') is-invalid @enderror" placeholder="Tối thiểu 8 ký tự" {{ $isEdit ? '' : 'required' }}>
                        @error('mat_khau')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="prof-label">Xác nhận mật khẩu @if(!$isEdit)<span class="text-danger">*</span>@endif</label>
                        <input type="password" name="mat_khau_confirmation" class="form-control prof-input" placeholder="Nhập lại mật khẩu" {{ $isEdit ? '' : 'required' }}>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<style>
    /* Role grid */
    .qltk-role-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
    }
    .qltk-role-item {
        display: grid;
        grid-template-columns: 42px 1fr auto;
        gap: 12px;
        align-items: center;
        padding: 14px;
        background: #fff;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        cursor: pointer;
        position: relative;
        transition: all 0.18s ease;
    }
    .qltk-role-item input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .qltk-role-icon {
        width: 42px; height: 42px;
        border-radius: 12px;
        display: grid; place-items: center;
        font-size: 1.1rem;
    }
    .qltk-role-item.is-danger  .qltk-role-icon { background: #fee2e2; color: #dc2626; }
    .qltk-role-item.is-info    .qltk-role-icon { background: #cffafe; color: #0e7490; }
    .qltk-role-item.is-success .qltk-role-icon { background: #dcfce7; color: #16a34a; }

    .qltk-role-info { min-width: 0; }
    .qltk-role-info strong {
        display: block;
        font-size: 0.92rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
        margin-bottom: 2px;
    }
    .qltk-role-info small {
        font-size: 0.74rem;
        color: #64748b;
        font-weight: 500;
    }
    .qltk-role-check {
        color: transparent;
        font-size: 1rem;
        transition: all 0.18s ease;
    }

    .qltk-role-item:hover { background: #fafafa; }
    .qltk-role-item.is-danger:has(input:checked)  { background: #fef2f2; border-color: #dc2626; box-shadow: 0 4px 12px rgba(220,38,38,0.15); }
    .qltk-role-item.is-info:has(input:checked)    { background: #ecfeff; border-color: #0e7490; box-shadow: 0 4px 12px rgba(14,116,144,0.15); }
    .qltk-role-item.is-success:has(input:checked) { background: #f0fdf4; border-color: #16a34a; box-shadow: 0 4px 12px rgba(22,163,74,0.15); }
    .qltk-role-item.is-danger:has(input:checked)  .qltk-role-check { color: #dc2626; }
    .qltk-role-item.is-info:has(input:checked)    .qltk-role-check { color: #0e7490; }
    .qltk-role-item.is-success:has(input:checked) .qltk-role-check { color: #16a34a; }

    /* Status grid */
    .qltk-status-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 10px;
    }
    .qltk-status-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        background: #fff;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        cursor: pointer;
        position: relative;
        transition: all 0.18s ease;
    }
    .qltk-status-item input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .qltk-status-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: grid; place-items: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .qltk-status-item.is-success .qltk-status-icon { background: #dcfce7; color: #16a34a; }
    .qltk-status-item.is-warning .qltk-status-icon { background: #fef3c7; color: #b45309; }

    .qltk-status-item strong {
        display: block;
        font-size: 0.9rem; font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .qltk-status-item small {
        font-size: 0.74rem;
        color: #64748b;
    }
    .qltk-status-item:hover { background: #fafafa; }
    .qltk-status-item.is-success:has(input:checked) { background: #f0fdf4; border-color: #16a34a; box-shadow: 0 4px 12px rgba(22,163,74,0.15); }
    .qltk-status-item.is-warning:has(input:checked) { background: #fffbeb; border-color: #f59e0b; box-shadow: 0 4px 12px rgba(245,158,11,0.15); }
</style>
