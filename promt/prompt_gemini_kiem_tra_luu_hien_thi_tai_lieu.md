# Yêu cầu Agent: Xây dựng tính năng Quản lý Tài nguyên Buổi học cho Giảng viên (Laravel)

**Ngữ cảnh:** Đây là hệ thống quản lý trung tâm đào tạo (Laravel 12, PHP 8.2). Tôi cần bạn đóng vai trò là một Senior Laravel Developer. Nhiệm vụ của bạn là xây dựng hoàn chỉnh tính năng: "Giảng viên đăng tải bài giảng, tài liệu, bài tập cho từng buổi học cụ thể".

**Nguyên tắc làm việc:**
- Code phải tuân thủ chuẩn PSR-12, sử dụng Eloquent ORM và Form Request Validation.
- Thực hiện tuân thủ NGHIÊM NGẶT theo từng Phase dưới đây. 
- CHỈ KHI tôi phản hồi "Xong Phase [X]" hoặc "Next", bạn mới được chuyển sang Phase tiếp theo. 
- Ở mỗi Phase, chỉ cung cấp code của Phase đó, kèm hướng dẫn chạy lệnh (nếu có). KHÔNG code trước phần của Phase sau.

---

### PHASE 1: Database & Model
**Yêu cầu:** 1. Tạo migration cho bảng `tai_nguyen_buoi_hoc` với các trường: `id`, `lich_hoc_id` (foreign key), `loai_tai_nguyen` (enum: bai_giang, tai_lieu, bai_tap), `tieu_de` (string), `mo_ta` (text, nullable), `duong_dan_file` (string, nullable), `link_ngoai` (string, nullable), timestamps.
2. Tạo/Cập nhật Model `TaiNguyenBuoiHoc`: Khai báo `$fillable`, thiết lập quan hệ `belongsTo` với Model `LichHoc`.
3. Cập nhật Model `LichHoc`: Thêm quan hệ `hasMany` tới `TaiNguyenBuoiHoc`.
*(Đợi tôi xác nhận chạy migration thành công rồi mới qua Phase 2).*

### PHASE 2: Controller & Phân quyền bảo mật (TaiNguyenController)
**Yêu cầu:**
1. Viết `TaiNguyenController` cho Giảng viên.
2. Viết hàm `store(Request $request, $lichHocId)`:
   - Validate dữ liệu: file tối đa 10MB, link url hợp lệ.
   - *Bảo mật:* Kiểm tra xem giảng viên hiện tại (auth) có đúng là người được phân công dạy `module_hoc` chứa `lich_hoc` này không (kiểm tra qua bảng `phan_cong_module_giang_vien`). Nếu không, trả về 403.
   - Xử lý lưu file vào disk `public`, thư mục `tai-lieu-buoi-hoc`. Bắt buộc ép `visibility => public`.
3. Viết hàm `destroy($id)`:
   - Kiểm tra quyền sở hữu như hàm store.
   - Xóa file vật lý trong storage bằng `Storage::disk('public')->delete()`.
   - Xóa record trong DB.

### PHASE 3: Routes & Cấu hình Storage
**Yêu cầu:**
1. Viết định nghĩa Route cho `store` và `destroy` trong `routes/web.php` (nhóm middleware `giang_vien`).
2. Nhắc nhở và cung cấp câu lệnh cấu hình symlink thư mục storage cho Laravel để web có thể đọc được file (Lệnh artisan và lệnh cấp quyền chmod nếu cần).

### PHASE 4: Giao diện (Blade Views)
**Yêu cầu:**
1. Viết mã HTML/Blade cho Modal "Thêm tài liệu" (form upload file enctype="multipart/form-data").
2. Viết đoạn mã Blade hiển thị danh sách tài liệu bên dưới thông tin của từng buổi học (kèm icon phân biệt loại tài nguyên, nút xóa tài liệu, link bấm vào xem file bằng hàm `asset()`).
3. Viết đoạn JavaScript xử lý việc truyền `lich_hoc_id` vào action của form Modal khi bấm nút "Đăng tài liệu".

---
**Bắt đầu đi. Hãy cung cấp code cho PHASE 1.**