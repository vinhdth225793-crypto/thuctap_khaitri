# Tài liệu kỹ thuật: Module Ngân hàng câu hỏi trắc nghiệm

## 1. Giới thiệu
Module này cho phép quản lý ngân hàng câu hỏi trắc nghiệm theo từng khóa học. Hỗ trợ CRUD thủ công và import hàng loạt từ file CSV (tương thích Excel).

## 2. Cấu trúc bảng dữ liệu (`ngan_hang_cau_hoi`)
| Cột | Kiểu | Mô tả |
|---|---|---|
| `id` | BigInt | Khóa chính |
| `khoa_hoc_id` | Foreign Key | Liên kết bảng `khoa_hoc` |
| `noi_dung_cau_hoi` | Text | Nội dung câu hỏi |
| `dap_an_sai_1` | Text | Đáp án sai thứ nhất |
| `dap_an_sai_2` | Text | Đáp án sai thứ hai |
| `dap_an_sai_3` | Text | Đáp án sai thứ ba |
| `dap_an_dung` | Text | Đáp án đúng |
| `nguoi_tao_id` | Foreign Key | Liên kết bảng `nguoi_dung` (`ma_nguoi_dung`) |
| `deleted_at` | Timestamp | Hỗ trợ Soft Delete |

## 3. Các tính năng chính
- **CRUD thủ công**: Thêm, sửa, xóa câu hỏi với giao diện trực quan.
- **Kiểm tra trùng lặp**: Hệ thống tự động chuẩn hóa chuỗi (trim, lowercase, gộp khoảng trắng) để phát hiện câu hỏi trùng trong cùng một khóa học.
- **Tải file mẫu**: Cung cấp file CSV mẫu có sẵn BOM để hiển thị đúng tiếng Việt trong Excel.
- **Import & Preview**: 
    - Upload file CSV.
    - Hiển thị preview dữ liệu trước khi lưu.
    - Kiểm tra lỗi dữ liệu (thiếu trường, đáp án trùng nhau).
    - Kiểm tra trùng lặp trong file và trùng lặp với hệ thống.
- **Xác nhận Import**: Chỉ lưu các dòng hợp lệ vào database.

## 4. Hướng dẫn cài đặt & Seed
### Migrate
```bash
php artisan migrate
```
### Seed dữ liệu mẫu
```bash
php artisan db:seed --class=NganHangCauHoiSeeder
```

## 5. Hướng dẫn Test Import
1. Truy cập: **Quản lý -> Kiểm tra Online -> Ngân hàng câu hỏi**.
2. Bấm **Tải file mẫu** để lấy file `mau-nhap-cau-hoi.csv`.
3. Mở file bằng Excel, nhập liệu và lưu lại (giữ định dạng CSV).
4. Bấm **Import Excel**, chọn khóa học và chọn file vừa lưu.
5. Xem màn hình **Preview** để kiểm tra các dòng Hợp lệ/Lỗi/Trùng.
6. Bấm **Xác nhận lưu** để hoàn tất.

## 6. Phân quyền
- **Admin**: Toàn quyền quản lý mọi câu hỏi.
- **Giảng viên**: Chỉ quản lý câu hỏi thuộc các khóa học mà mình được phân công dạy.
- **Học viên**: Không có quyền truy cập.
