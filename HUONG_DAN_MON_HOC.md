# Hướng dẫn Chạy Migrations và Sử dụng Tính năng Quản lý Môn học

## 1. Chạy Migrations

Mở terminal/cmd trong thư mục dự án `thuctap_khaitri` và chạy lệnh:

```bash
php artisan migrate
```

Lệnh này sẽ tạo 3 bảng trong database:
- `mon_hoc` - Bảng lưu trữ các môn học
- `khoa_hoc` - Bảng lưu trữ các khóa học (thuộc về môn học)
- `module_hoc` - Bảng lưu trữ các module (thuộc về khóa học)

## 2. Truy cập Tính năng

Sau khi chạy migrations:

1. Đăng nhập vào hệ thống với tài khoản admin
2. Trong sidebar menu, bạn sẽ thấy **"Quản lý khóa học"** với dropdown menu
3. Click vào **"Môn học"** để vào trang quản lý môn học

## 3. Các Tính năng Có Sẵn

### Quản lý Môn học
- ✅ Xem danh sách môn học
- ✅ Tìm kiếm môn học theo tên hoặc mã
- ✅ Thêm môn học mới
- ✅ Chỉnh sửa thông tin môn học
- ✅ Xem chi tiết môn học (bao gồm các khóa học thuộc môn)
- ✅ Xóa môn học (sẽ xóa tất cả khóa học liên quan)
- ✅ Cập nhật trạng thái (hoạt động/tạm dừng)
- ✅ Upload hình ảnh cho môn học

### Quản lý Khóa học
- 🔄 Đang phát triển (sắp ra mắt)

### Quản lý Module
- 🔄 Đang phát triển (sắp ra mắt)

## 4. Cấu trúc Dữ liệu

### Mon Hoc (Môn học)
```
- id: Mã số tự động
- ma_mon_hoc: Mã môn học (duy nhất, VD: PYTHON, JAVA)
- ten_mon_hoc: Tên môn học
- mo_ta: Mô tả chi tiết
- hinh_anh: Đường dẫn hình ảnh
- trang_thai: Trạng thái (1=hoạt động, 0=tạm dừng)
```

### Khoa Hoc (Khóa học)
```
- id: Mã số tự động
- mon_hoc_id: Mã môn học (khóa ngoài)
- ma_khoa_hoc: Mã khóa học (duy nhất)
- ten_khoa_hoc: Tên khóa học
- mo_ta_ngan: Mô tả ngắn
- mo_ta_chi_tiet: Mô tả chi tiết
- hinh_anh: Hình ảnh khóa học
- cap_do: Cấp độ (co_ban, trung_binh, nang_cao)
- tong_so_module: Tổng số module
- trang_thai: Trạng thái
```

### Module Hoc (Module)
```
- id: Mã số tự động
- khoa_hoc_id: Mã khóa học (khóa ngoài)
- ma_module: Mã module (duy nhất)
- ten_module: Tên module
- mo_ta: Mô tả
- thu_tu_module: Thứ tự module
- thoi_luong_du_kien: Thời lượng dự kiến (phút)
- trang_thai: Trạng thái
```

## 5. Ghi Chú

- Khi xóa một môn học, tất cả khóa học thuộc môn đó cũng sẽ bị xóa
- Bạn cần tạo môn học trước khi có thể tạo khóa học
- Bạn cần tạo khóa học trước khi có thể tạo module
- Hình ảnh được tự động tổ chức trong thư mục `public/images/mon-hoc/`

## 6. Lỗi Phổ Biến

Nếu gặp lỗi "Bảng không tồn tại", hãy:
1. Kiểm tra `.env` file có cấu hình database đúng không
2. Chạy lại: `php artisan migrate:fresh` (⚠️ Cảnh báo: Sẽ xóa tất cả dữ liệu)
3. Hoặc tạo database mới và cấu hình trong `.env`

---

**Được tạo bởi:** GitHub Copilot
**Ngày:** 10/03/2026
