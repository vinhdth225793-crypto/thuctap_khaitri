<section class="role-panel" aria-label="Bảng điều khiển nhanh cho quản trị viên">
    <div class="role-panel-heading">
        <span class="eyebrow">Quản trị</span>
        <h2>Chào {{ auth()->user()->ho_ten }}, hôm nay cần xử lý gì?</h2>
        <p>Theo dõi các yêu cầu quan trọng và mở nhanh khu vực quản trị.</p>
    </div>

    <div class="role-grid">
        <a href="{{ route('admin.phe-duyet-tai-khoan.index') }}" class="role-tile">
            <span class="role-icon info"><i class="fas fa-user-check"></i></span>
            <strong>{{ number_format($dashboardData['taiKhoanChoDuyet'] ?? 0) }}</strong>
            <span>Tài khoản chờ duyệt</span>
        </a>

        <a href="{{ route('admin.phan-cong.index') }}" class="role-tile">
            <span class="role-icon warm"><i class="fas fa-handshake"></i></span>
            <strong>{{ number_format($dashboardData['phanCongChoXN'] ?? 0) }}</strong>
            <span>Phân công chờ xác nhận</span>
        </a>

        <a href="{{ route('admin.hoc-vien.index') }}" class="role-tile">
            <span class="role-icon good"><i class="fas fa-users"></i></span>
            <strong>{{ number_format($dashboardData['hocVienMoiHomNay'] ?? 0) }}</strong>
            <span>Học viên mới hôm nay</span>
        </a>
    </div>
</section>
