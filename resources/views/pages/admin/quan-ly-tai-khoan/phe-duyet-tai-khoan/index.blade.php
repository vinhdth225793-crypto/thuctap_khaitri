@extends('layouts.app')

@section('title', 'Phê duyệt tài khoản')

@section('content')
<div class="container">
    <h1>Danh sách tài khoản chờ phê duyệt</h1>

    <form method="GET" class="form-inline mb-3">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control mr-2" placeholder="Tìm kiếm theo tên, email, số điện thoại">
        <button class="btn btn-primary">Tìm kiếm</button>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Ngày sinh</th>
                    <th>Địa chỉ</th>
                    <th>Ngày đăng ký</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($taiKhoanChoPheDuyet as $taiKhoan)
                <tr>
                    <td>{{ $taiKhoan->id }}</td>
                    <td>{{ $taiKhoan->ho_ten }}</td>
                    <td>{{ $taiKhoan->email }}</td>
                    <td>{{ $taiKhoan->so_dien_thoai ?? 'Chưa cập nhật' }}</td>
                    <td>{{ $taiKhoan->ngay_sinh ? $taiKhoan->ngay_sinh->format('d/m/Y') : 'Chưa cập nhật' }}</td>
                    <td>{{ $taiKhoan->dia_chi ?? 'Chưa cập nhật' }}</td>
                    <td>{{ $taiKhoan->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <button class="btn btn-success btn-sm approve-btn" data-id="{{ $taiKhoan->id }}" data-name="{{ $taiKhoan->ho_ten }}" data-status="pending">
                            <i class="fas fa-check"></i> <span class="btn-text">Phê duyệt</span>
                        </button>
                        <button class="btn btn-danger btn-sm reject-btn" data-id="{{ $taiKhoan->id }}" data-name="{{ $taiKhoan->ho_ten }}">
                            <i class="fas fa-times"></i> Từ chối
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">
                        <i class="fas fa-info-circle"></i> Không có tài khoản nào chờ phê duyệt.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $taiKhoanChoPheDuyet->links() }}
</div>

<!-- Modal xác nhận phê duyệt -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận phê duyệt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn phê duyệt tài khoản <strong id="approveName"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success" id="confirmApprove">Phê duyệt</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận từ chối -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận từ chối</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn từ chối tài khoản <strong id="rejectName"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmReject">Từ chối</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let currentId = null;

    // Phê duyệt tài khoản
    $(document).on('click', '.approve-btn', function() {
        const $btn = $(this);
        const status = $btn.data('status');
        
        // Nếu đã phê duyệt rồi, bấm lần này là undo
        if (status === 'approved') {
            currentId = $btn.data('id');
            const name = $btn.data('name');
            if (confirm(`Bạn có chắc chắn muốn hủy phê duyệt tài khoản "${name}"?`)) {
                $.post(`{{ route('admin.phe-duyet-tai-khoan.undo', ':id') }}`.replace(':id', currentId), {
                    _token: '{{ csrf_token() }}'
                })
                .done(function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        // Quay lại trạng thái phê duyệt
                        $btn.data('status', 'pending').removeClass('btn-warning').addClass('btn-success');
                        $btn.find('.btn-text').text('Phê duyệt');
                    } else {
                        toastr.error(response.message || 'Có lỗi xảy ra!');
                    }
                })
                .fail(function() {
                    toastr.error('Có lỗi xảy ra!');
                });
            }
        } else {
            // Chưa phê duyệt, hiển thị modal
            currentId = $btn.data('id');
            const name = $btn.data('name');
            $('#approveName').text(name);
            $('#approveModal').modal('show');
        }
    });

    $('#confirmApprove').on('click', function() {
        if (currentId) {
            $.post(`{{ route('admin.phe-duyet-tai-khoan.approve', ':id') }}`.replace(':id', currentId), {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    // Đổi nút phê duyệt thành "Hủy phê duyệt"
                    const $approveBtn = $('[data-id="' + currentId + '"].approve-btn');
                    $approveBtn.data('status', 'approved').removeClass('btn-success').addClass('btn-warning');
                    $approveBtn.find('.btn-text').text('Hủy phê duyệt');
                    
                    // Sau 2 giây, xóa hàng và reload
                    setTimeout(function() {
                        $approveBtn.closest('tr').fadeOut(300, function() {
                            $(this).remove();
                            // Kiểm tra nếu không còn hàng nào
                            if ($('tbody tr').length === 0) {
                                $('tbody').html('<tr><td colspan="8" class="text-center text-muted"><i class="fas fa-info-circle"></i> Không có tài khoản nào chờ phê duyệt.</td></tr>');
                            }
                            // Chuyển hướng sang trang danh sách tương ứng
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            }
                        });
                    }, 2000);
                } else {
                    toastr.error('Có lỗi xảy ra!');
                }
            })
            .fail(function() {
                toastr.error('Có lỗi xảy ra!');
            });

            $('#approveModal').modal('hide');
        }
    });

    // Từ chối tài khoản
    $(document).on('click', '.reject-btn', function() {
        currentId = $(this).data('id');
        const name = $(this).data('name');
        $('#rejectName').text(name);
        $('#rejectModal').modal('show');
    });

    $('#confirmReject').on('click', function() {
        if (currentId) {
            $.post(`{{ route('admin.phe-duyet-tai-khoan.reject', ':id') }}`.replace(':id', currentId), {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    // Xóa hàng từ bảng mà không cần reload
                    $('tbody tr').each(function() {
                        if ($(this).find('.reject-btn').data('id') == currentId) {
                            $(this).fadeOut(300, function() {
                                $(this).remove();
                                // Kiểm tra nếu không còn hàng nào
                                if ($('tbody tr').length === 0) {
                                    $('tbody').html('<tr><td colspan="8" class="text-center text-muted"><i class="fas fa-info-circle"></i> Không có tài khoản nào chờ phê duyệt.</td></tr>');
                                }
                            });
                        }
                    });
                } else {
                    toastr.error('Có lỗi xảy ra!');
                }
            })
            .fail(function() {
                toastr.error('Có lỗi xảy ra!');
            });

            $('#rejectModal').modal('hide');
        }
    });
});
</script>
@endpush
@endsection