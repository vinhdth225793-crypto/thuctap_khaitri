# Phân tích các luồng hiện có trong dự án

Ngày cập nhật: 2026-03-31

## 1. Phạm vi tài liệu

Tài liệu này mô tả các luồng đang tồn tại trong codebase hiện tại của dự án Laravel, dựa trên:

- `routes/web.php`
- các controller, service và view chính
- tài liệu trong thư mục `docs/`
- kết quả kiểm thử thực tế bằng `php artisan test`

Mục tiêu của tài liệu:

- bản đồ hóa các phân hệ đang có
- mô tả luồng nghiệp vụ đúng với implementation hiện tại
- chỉ ra mối liên kết giữa các phân hệ
- nêu mức độ hoàn thiện và các điểm còn hở của vòng đời nghiệp vụ

Tài liệu này không mô tả phiên bản lý tưởng của hệ thống, mà mô tả đúng trạng thái hệ thống đang chạy.

---

## 2. Bức tranh tổng quan

Đây là hệ thống quản lý đào tạo nội bộ theo chuỗi:

`Trang chủ / đăng ký -> quản trị tài khoản -> khóa học mẫu -> mở lớp hoạt động -> phân công giảng viên -> lịch học / buổi học -> tài nguyên / bài giảng / live room / bài kiểm tra -> học viên học tập -> điểm danh / chấm điểm -> kết quả học tập`

Hệ thống xoay quanh 3 vai trò chính:

- `Admin`
- `Giảng viên`
- `Học viên`

Các phân hệ lớn đang hoạt động:

- xác thực và phê duyệt tài khoản
- quản lý nhóm ngành, khóa học, module
- mở lớp từ khóa mẫu
- phân công giảng viên và xác nhận nhận lớp
- lập lịch học và theo dõi lịch giảng
- đơn xin nghỉ giảng
- thư viện tài nguyên và tài nguyên buổi học
- bài giảng và bài giảng live
- phòng học live
- ngân hàng câu hỏi và kiểm tra online
- học tập, tiến độ và kết quả của học viên
- thông báo và cấu hình hệ thống

---

## 3. Vai trò và ranh giới quyền

### 3.1. Khách

- xem trang chủ
- tìm kiếm khóa học công khai
- đăng ký tài khoản
- đăng nhập

### 3.2. Admin

- duyệt tài khoản giảng viên
- quản lý tài khoản người dùng
- quản lý nhóm ngành, khóa học mẫu, lớp hoạt động, module
- phân công giảng viên
- lập lịch học
- duyệt đơn xin nghỉ giảng
- duyệt tài nguyên và bài giảng
- quản lý ngân hàng câu hỏi
- duyệt và phát hành bài kiểm tra
- xem thống kê và cấu hình hệ thống

### 3.3. Giảng viên

- xem dashboard và lịch giảng
- xác nhận hoặc từ chối phân công
- cập nhật link buổi học online
- gửi yêu cầu thay đổi học viên trong khóa
- gửi đơn xin nghỉ
- tạo và quản lý thư viện tài nguyên
- tạo bài giảng, gửi duyệt
- tạo bài kiểm tra, cấu hình đề, gửi duyệt
- chấm bài tự luận
- quản lý phòng live do mình phụ trách

### 3.4. Học viên

- xem dashboard học tập
- xem khóa học của tôi
- xem khóa có thể tham gia và gửi yêu cầu tham gia
- vào chi tiết khóa học đã ghi danh
- học bài giảng, vào phòng live
- làm bài kiểm tra
- theo dõi tiến độ, chuyên cần và kết quả học tập

---

## 4. Các thực thể trung tâm

### 4.1. Danh tính và hồ sơ

- `nguoi_dung`
- `giang_vien`
- `hoc_vien`
- `tai_khoan_cho_phe_duyet`

### 4.2. Đào tạo

- `nhom_nganh`
- `khoa_hoc`
- `module_hoc`
- `phan_cong_module_giang_vien`
- `hoc_vien_khoa_hoc`
- `lich_hoc`

### 4.3. Nội dung học tập

- `tai_nguyen_buoi_hoc`
- `bai_giangs`
- `bai_giang_tai_nguyen`
- `phong_hoc_live`
- `phong_hoc_live_nguoi_tham_gia`
- `phong_hoc_live_ban_ghi`

### 4.4. Đánh giá

- `ngan_hang_cau_hoi`
- `dap_an_cau_hoi`
- `bai_kiem_tra`
- `chi_tiet_bai_kiem_tra`
- `bai_lam_bai_kiem_tra`
- `chi_tiet_bai_lam_bai_kiem_tra`
- `ket_qua_hoc_tap`

### 4.5. Vận hành

- `diem_danh`
- `giang_vien_don_xin_nghi`
- `yeu_cau_hoc_vien`
- `thong_bao`
- `system_settings`
- `banners`

---

## 5. Luồng chi tiết theo phân hệ

## 5.1. Luồng công khai ngoài hệ thống

### Trang chủ và khám phá khóa học

1. Người dùng truy cập trang chủ.
2. Hệ thống tải:
   - banner đang bật
   - khóa học công khai loại `hoat_dong`
   - chỉ các lớp có trạng thái vận hành `cho_giang_vien`, `san_sang`, `dang_day`
   - giảng viên nổi bật
   - nhóm ngành có khóa học công khai
3. Người dùng có thể tìm kiếm theo:
   - từ khóa
   - cấp độ
   - nhóm ngành
4. Kết quả tìm kiếm dùng cùng flow với trang chủ.

Ý nghĩa:

- trang chủ không chỉ là landing page, mà còn là điểm vào chính để khám phá lớp đang mở
- hệ thống chỉ lộ các lớp đang có khả năng tiếp nhận hoặc đã vận hành

---

## 5.2. Luồng đăng ký, đăng nhập và định tuyến theo vai trò

### Đăng ký học viên

1. Người dùng chọn vai trò `hoc_vien`.
2. Hệ thống tạo ngay:
   - `nguoi_dung`
   - hồ sơ `hoc_vien`
3. Hệ thống đăng nhập tự động.
4. Người dùng được chuyển sang dashboard học viên.

### Đăng ký giảng viên

1. Người dùng chọn vai trò `giang_vien`.
2. Hệ thống chưa tạo `nguoi_dung` hoạt động ngay.
3. Dữ liệu được lưu vào `tai_khoan_cho_phe_duyet`.
4. Admin vào màn hình phê duyệt để duyệt hoặc từ chối.
5. Khi được duyệt, hệ thống mới tạo `nguoi_dung` thật trong hệ thống.

### Đăng nhập

1. Hệ thống tìm `nguoi_dung` theo email.
2. Kiểm tra mật khẩu hash.
3. Kiểm tra thêm `trang_thai` của tài khoản.
4. Nếu hợp lệ, hệ thống điều hướng theo vai trò:
   - `admin -> admin.dashboard`
   - `giang_vien -> giang-vien.dashboard`
   - `hoc_vien -> hoc-vien.dashboard`

Đây là một điểm đã được khóa tốt ở hiện tại: tài khoản bị vô hiệu hóa sẽ bị chặn đăng nhập.

---

## 5.3. Luồng quản trị tài khoản

### Quản lý tài khoản chính thức

Admin có thể:

- tạo tài khoản mới
- xem chi tiết tài khoản
- sửa thông tin
- khóa / mở khóa
- xóa mềm

Luồng này áp dụng cho cả `admin`, `giang_vien`, `hoc_vien`.

### Phê duyệt tài khoản chờ

1. Admin xem danh sách `tai_khoan_cho_phe_duyet`.
2. Admin chọn:
   - duyệt
   - từ chối
   - hoàn tác duyệt
3. Khi duyệt:
   - hệ thống tạo `nguoi_dung`
   - nếu là giảng viên, tài khoản sẽ xuất hiện ở khu vực giảng viên quản trị

Luồng này là cửa vào chính cho nhóm giảng viên từ phía đăng ký công khai.

---

## 5.4. Luồng xây dựng cấu trúc đào tạo

### Tầng 1: Nhóm ngành

1. Admin tạo `nhom_nganh`.
2. Nhóm ngành là danh mục để phân loại khóa học.

### Tầng 2: Khóa học mẫu

1. Admin tạo `khoa_hoc` với `loai = mau`.
2. Trong lúc tạo, admin nhập luôn danh sách module.
3. Hệ thống sinh các `module_hoc` theo thứ tự.

### Tầng 3: Mở lớp hoạt động từ khóa mẫu

1. Admin chọn một khóa mẫu.
2. Hệ thống clone khóa mẫu sang `khoa_hoc` mới với `loai = hoat_dong`.
3. Hệ thống clone toàn bộ module tương ứng.
4. Admin có thể gán giảng viên cho từng module ngay lúc mở lớp.
5. Nếu có ít nhất một phân công, lớp được đưa sang `cho_giang_vien`.

### Tầng 4: Chuyển trạng thái vận hành

Các trạng thái vận hành chính của lớp hiện tại gồm:

- `cho_mo`
- `cho_giang_vien`
- `san_sang`
- `dang_day`
- `ket_thuc`

Ý nghĩa:

- lớp mẫu là khuôn thiết kế
- lớp hoạt động là thực thể triển khai thực tế
- phần vận hành được điều khiển bằng trạng thái, không chỉ bằng CRUD

---

## 5.5. Luồng phân công giảng viên

1. Admin phân công giảng viên theo từng `module_hoc`.
2. Mỗi phân công được lưu ở `phan_cong_module_giang_vien`.
3. Phân công mới có trạng thái `cho_xac_nhan`.
4. Hệ thống gửi thông báo cho giảng viên.
5. Giảng viên vào khu vực `khoa-hoc` hoặc `phan-cong` để phản hồi.
6. Giảng viên chọn:
   - `da_nhan`
   - `tu_choi`
7. Khi toàn bộ module cần thiết đã được xác nhận nhận dạy, lớp có thể chuyển sang `san_sang`.
8. Admin xác nhận mở lớp chính thức để lớp sang `dang_day`.

Điểm đáng chú ý:

- route `giang-vien.khoa-hoc.show` đang đóng vai trò trung tâm cho luồng vào lớp dạy
- controller hiện cho phép resolve theo `assignment id`, `khoa_hoc_id` hoặc `module_hoc_id`, tức là luồng điều hướng còn khá linh hoạt

---

## 5.6. Luồng lập lịch học và thời khóa biểu

### Tạo lịch học

1. Admin vào một lớp hoạt động.
2. Admin tạo lịch:
   - thủ công
   - tự động
3. Mỗi `lich_hoc` gắn với:
   - khóa học
   - module
   - giảng viên
   - ngày học
   - dải tiết / khung giờ
   - hình thức `truc_tiep` hoặc `online`
   - buổi số

### Ràng buộc lịch

Codebase hiện có service riêng cho:

- catalog khung tiết chuẩn
- kiểm tra xung đột giảng viên
- xem lịch giảng theo tuần
- gợi ý lịch

Feature test hiện có cho:

- không cho tạo ngoài giờ chuẩn
- không cho tạo cuối tuần
- không cho trùng lịch cùng giảng viên
- không cho mutate lịch qua sai khóa học

### Xem lịch theo vai trò

- giảng viên có màn hình `lich-giang` của riêng mình
- admin có màn hình xem lịch của từng giảng viên

Luồng này là trục vận hành chính của phân hệ giảng dạy.

---

## 5.7. Luồng vận hành buổi học

### Cập nhật thông tin buổi học

Giảng viên được phân công có thể:

- cập nhật link online
- cập nhật nền tảng
- cập nhật meeting id / mật khẩu
- cập nhật phòng học trực tiếp

### Điểm danh

1. Giảng viên mở điểm danh cho một `lich_hoc`.
2. Hệ thống chỉ lấy học viên đang ghi danh hợp lệ.
3. Giảng viên lưu trạng thái:
   - `co_mat`
   - `vang_mat`
   - `vao_tre`
4. Dữ liệu được lưu vào `diem_danh`.
5. Sau khi lưu, hệ thống đồng bộ lại `ket_qua_hoc_tap`.

### Báo cáo buổi học

Giảng viên có thể gửi báo cáo buổi học cho `lich_hoc`.

Luồng buổi học là điểm giao giữa:

- lịch học
- học viên ghi danh
- điểm danh
- tài nguyên buổi học
- bài giảng
- bài kiểm tra
- live room

---

## 5.8. Luồng đơn xin nghỉ giảng

1. Giảng viên vào khu vực `don-xin-nghi`.
2. Có 2 kiểu tạo đơn:
   - gắn trực tiếp với một `lich_hoc`
   - tạo đơn thủ công theo ngày và dải tiết
3. Đơn đi vào trạng thái `cho_duyet`.
4. Admin xem danh sách, xem chi tiết và xử lý:
   - duyệt
   - từ chối
5. Màn hình lịch giảng của admin và giảng viên đều có khối hiển thị đơn nghỉ gần đây.

Luồng này đã được nối khá chặt với phân hệ lịch học.

---

## 5.9. Luồng thư viện tài nguyên và tài nguyên buổi học

### Thư viện tài nguyên cá nhân / khóa học

1. Giảng viên vào `thu-vien`.
2. Tạo tài nguyên với:
   - tiêu đề
   - loại tài nguyên
   - file hoặc link
   - phạm vi sử dụng
3. Tài nguyên được lưu ở `tai_nguyen_buoi_hoc`, nhưng không nhất thiết gắn vào `lich_hoc`.
4. Giảng viên có thể gửi duyệt.
5. Admin xem danh sách thư viện và duyệt tài nguyên.

### Tài nguyên buổi học

Ngoài thư viện, giảng viên còn có thể:

- gắn tài nguyên trực tiếp vào một `lich_hoc`
- cập nhật tài nguyên
- bật / tắt hiển thị
- xóa tài nguyên khỏi buổi học

Ý nghĩa kiến trúc:

- cùng một bảng dữ liệu đang đóng 2 vai:
  - thư viện tái sử dụng
  - tài nguyên gắn trực tiếp vào buổi học

Đây là cách triển khai thực dụng, giúp giảm số bảng nhưng làm logic quyền và trạng thái phức tạp hơn.

---

## 5.10. Luồng bài giảng

1. Giảng viên vào khu vực `bai-giang`.
2. Chọn `phan_cong_module_giang_vien` của mình.
3. Có thể gắn bài giảng vào một `lich_hoc` cụ thể.
4. Giảng viên cấu hình:
   - tiêu đề
   - mô tả
   - loại bài giảng
   - tài nguyên chính
   - tài nguyên phụ
   - thứ tự hiển thị
   - thời điểm mở
5. Bài giảng được lưu ban đầu ở trạng thái nháp.
6. Giảng viên gửi duyệt.
7. Admin duyệt.
8. Admin công bố.
9. Học viên chỉ thấy bài giảng khi:
   - đã duyệt
   - đã công bố
   - và thỏa điều kiện hiển thị cho học viên

Điểm mạnh của flow này là đã tách được:

- tầng chuẩn bị tài nguyên
- tầng bài giảng
- tầng hiển thị thực tế cho học viên

---

## 5.11. Luồng bài giảng live và phòng học live

### Tạo và duyệt live lecture

1. Giảng viên tạo bài giảng loại live.
2. Hệ thống gắn với `phong_hoc_live`.
3. Bài giảng live đi qua vòng đời:
   - tạo
   - gửi duyệt
   - admin duyệt
   - công bố

### Vận hành phòng live phía giảng viên

Giảng viên hoặc người quản lý phòng có thể:

- xem trang phòng live
- bắt đầu buổi live
- mở phòng trong trang
- rời phòng
- kết thúc buổi live
- thêm bản ghi sau buổi học
- xóa bản ghi

### Truy cập phía học viên

1. Học viên chỉ truy cập được live room nếu:
   - bài giảng live đã duyệt và công bố
   - học viên đã ghi danh vào khóa
2. Học viên chỉ join khi phòng đủ điều kiện mở cho học viên.
3. Học viên vào phòng qua trang bài giảng / live room, không đi trực tiếp bằng link ngẫu nhiên.

Luồng live room là một điểm mạnh của dự án vì đã đi xa hơn bài giảng tĩnh.

---

## 5.12. Luồng ngân hàng câu hỏi

1. Admin vào khu vực `kiem-tra-online/cau-hoi`.
2. Tạo câu hỏi thủ công.
3. Câu hỏi có thể gắn:
   - theo khóa học
   - theo module
4. Hỗ trợ các loại:
   - trắc nghiệm
   - tự luận
5. Hỗ trợ các chế độ đáp án:
   - một đáp án đúng
   - nhiều đáp án đúng
   - đúng / sai
6. Có thể:
   - bật / tắt trạng thái
   - bật / tắt tái sử dụng
   - lọc theo loại khóa học, module, mức độ, trạng thái
7. Có flow import tài liệu:
   - tải file
   - preview
   - export preview
   - confirm import

Điểm đáng chú ý:

- controller đã có logic phân quyền theo khóa học cho giảng viên
- nhưng route hiện tại vẫn nằm ở nhóm `admin`

Tức là về mặt thiết kế, module này đang sẵn cho việc mở rộng quyền sau này.

---

## 5.13. Luồng tạo, duyệt và phát hành bài kiểm tra online

### Phía giảng viên

1. Giảng viên vào danh sách bài kiểm tra mình được phép truy cập.
2. Tạo khung bài kiểm tra.
3. Chọn phạm vi:
   - `module`
   - `buoi_hoc`
   - `cuoi_khoa`
4. Hệ thống kiểm tra giảng viên có được phân công đúng scope hay không.
5. Bài kiểm tra được tạo với trạng thái:
   - `trang_thai_duyet = nhap`
   - `trang_thai_phat_hanh = nhap`
6. Giảng viên vào màn hình cấu hình để:
   - chọn câu hỏi
   - import câu hỏi vào ngân hàng rồi chọn lại cho đề
   - cấu hình thời gian mở / đóng
   - cấu hình số lần làm
   - bật random câu hỏi / đáp án
   - chọn chế độ tính điểm
7. Khi đủ điều kiện, giảng viên gửi duyệt.

### Phía admin

1. Admin vào danh sách phê duyệt bài kiểm tra.
2. Có thể:
   - duyệt
   - từ chối
   - phát hành
   - đóng đề
3. Đề chỉ phát hành được khi đã duyệt và cấu hình hợp lệ.

### Phía học viên

1. Học viên chỉ thấy bài kiểm tra:
   - thuộc khóa mình đang học hoặc đã hoàn thành
   - đã duyệt
   - đã phát hành
   - đang ở trạng thái cho phép làm
2. Học viên bấm bắt đầu để tạo `bai_lam_bai_kiem_tra`.
3. Hệ thống sinh chi tiết trả lời theo đề.
4. Học viên nộp bài.
5. Trắc nghiệm được chấm tự động.
6. Nếu có tự luận, bài vào hàng `cho_cham`.

### Chấm tự luận

1. Giảng viên vào danh sách chấm điểm.
2. Chỉ thấy bài thuộc scope mình được phân công.
3. Giảng viên nhập điểm từng câu tự luận.
4. Hệ thống cộng điểm và cập nhật `ket_qua_hoc_tap`.

Đây là một trong các luồng hoàn chỉnh nhất của dự án.

---

## 5.14. Luồng học viên tham gia và học khóa học

### Xem khóa học có thể tham gia

1. Học viên vào `khoa-hoc-tham-gia`.
2. Hệ thống chỉ hiển thị lớp:
   - loại `hoat_dong`
   - đang active
   - ở một trong các trạng thái `cho_giang_vien`, `san_sang`, `dang_day`
3. Hệ thống loại trừ:
   - lớp đã ghi danh
   - lớp đã có yêu cầu đang chờ

### Gửi yêu cầu tham gia

1. Học viên nhập lý do.
2. Hệ thống tạo `yeu_cau_hoc_vien` với:
   - `loai_yeu_cau = them`
   - `trang_thai = cho_duyet`

### Xem khóa học của tôi

Học viên có thể thấy danh sách khóa theo trạng thái:

- `dang_hoc`
- `hoan_thanh`
- `ngung_hoc`

### Xem chi tiết khóa học

1. Học viên chỉ vào được nếu đã ghi danh và không ở trạng thái `ngung_hoc`.
2. Hệ thống tải:
   - module theo thứ tự
   - lịch học theo module
   - bài giảng hiển thị cho học viên
3. Học viên học theo cây:
   `Khóa học -> Module -> Lịch học -> Bài giảng / Live room / Tài liệu`

---

## 5.15. Luồng dashboard và tiến độ học tập của học viên

Dashboard học viên hiện không chỉ là màn hình chào mừng, mà là trung tâm tổng hợp dữ liệu học tập.

Hệ thống đang tổng hợp cho học viên:

- số khóa đang học
- số buổi học hôm nay
- số buổi sắp tới
- số tài liệu công khai
- tiến độ tổng quan
- chuyên cần
- hoạt động gần đây
- tiến độ theo từng khóa

Nguồn dữ liệu được ghép từ:

- `hoc_vien_khoa_hoc`
- `lich_hoc`
- `diem_danh`
- `tai_nguyen_buoi_hoc`
- `ket_qua_hoc_tap`

Đây là nơi kết nối nhiều phân hệ nhất ở phía học viên.

---

## 5.16. Luồng thông báo và cấu hình hệ thống

### Thông báo

Hệ thống có khu vực `thong-bao` cho user đã đăng nhập.

Các luồng tạo thông báo nổi bật hiện tại:

- phân công giảng viên
- lớp đã đủ điều kiện sẵn sàng

### Cấu hình hệ thống

Admin có thể chỉnh:

- thông tin liên hệ
- social
- danh sách giảng viên nổi bật
- banner
- một số `system_settings`

Luồng này ảnh hưởng trực tiếp tới trang chủ và nội dung public.

---

## 6. Chuỗi nghiệp vụ đầu-cuối của hệ thống

Nếu nhìn toàn dự án như một pipeline, flow hiện tại có thể tóm thành:

1. Khách vào trang chủ, đăng ký tài khoản.
2. Học viên được kích hoạt ngay, giảng viên đi qua hàng chờ phê duyệt.
3. Admin tạo nhóm ngành, khóa mẫu, module.
4. Admin mở lớp hoạt động từ khóa mẫu.
5. Admin phân công giảng viên cho module.
6. Giảng viên xác nhận nhận lớp.
7. Admin xác nhận mở lớp sang `dang_day`.
8. Admin lập lịch học.
9. Giảng viên vận hành buổi học:
   - cập nhật link
   - điểm danh
   - báo cáo
   - xin nghỉ nếu cần
10. Giảng viên chuẩn bị tài nguyên, bài giảng, live room, bài kiểm tra.
11. Admin duyệt và công bố nội dung / phát hành bài kiểm tra.
12. Học viên tham gia lớp, học bài, vào live room, làm bài kiểm tra.
13. Hệ thống tổng hợp chuyên cần và điểm thành `ket_qua_hoc_tap`.

---

## 7. Các trạng thái nghiệp vụ quan trọng

### 7.1. Khóa học

- `cho_mo`
- `cho_giang_vien`
- `san_sang`
- `dang_day`
- `ket_thuc`

### 7.2. Phân công giảng viên

- `cho_xac_nhan`
- `da_nhan`
- `tu_choi`

### 7.3. Lịch học

- `cho`
- `dang_hoc`
- `hoan_thanh`
- `huy`

### 7.4. Đơn xin nghỉ giảng

- `cho_duyet`
- `da_duyet`
- `tu_choi`

### 7.5. Tài nguyên và bài giảng

- tài nguyên thư viện đi qua luồng nháp / gửi duyệt / duyệt
- bài giảng đi qua luồng:
  - `nhap`
  - `cho_duyet`
  - `da_duyet`
  - công bố / ẩn

### 7.6. Bài kiểm tra

- duyệt:
  - `nhap`
  - `cho_duyet`
  - `da_duyet`
  - `tu_choi`
- phát hành:
  - `nhap`
  - `phat_hanh`
  - `dong`

### 7.7. Ghi danh học viên

- `dang_hoc`
- `hoan_thanh`
- `ngung_hoc`

---

## 8. Mức độ hoàn thiện hiện tại

## 8.1. Những luồng đã khá đầy đủ

- đăng ký / đăng nhập / phân vai
- mở lớp từ khóa mẫu
- phân công giảng viên và xác nhận
- lập lịch học có kiểm soát xung đột
- dashboard học viên và tiến độ học tập
- kiểm tra online từ tạo đề đến chấm điểm
- live room với start / join / end / recording

## 8.2. Những điểm đang có độ lệch giữa flow và wiring thực tế

### A. Luồng xử lý `yeu_cau_hoc_vien` chưa khép kín ở admin

- màn hình admin đã có form xử lý yêu cầu
- controller xử lý đã có method `xacNhan`
- nhưng route `admin.yeu-cau-hoc-vien.xac-nhan` hiện chưa được khai báo trong `routes/web.php`

Hệ quả:

- vòng đời yêu cầu tham gia / yêu cầu thêm học viên chưa khép kín trọn vẹn ở tầng routing

### B. Một số nút trong lịch giảng đang trỏ sai route name

- service dựng bảng thời khóa biểu đang gọi route không tồn tại cho điểm danh / tài nguyên
- lỗi này có thể chỉ nổ khi màn hình tuần có dữ liệu thật

Hệ quả:

- flow lịch giảng nhìn đúng về nghiệp vụ nhưng còn hở ở tầng điều hướng

### C. Một số màn hình “vào lớp” đang truyền `khoa_hoc_id` thay vì `assignment id`

Do controller cho phép resolve linh hoạt nên nhiều nơi vẫn chạy được, nhưng:

- ngữ cảnh có thể sai nếu một giảng viên dạy nhiều module trong cùng một khóa
- flow điều hướng chưa thật sự nhất quán

### D. Test lịch giảng đang fail do contract giao diện không còn khớp

Đây là lệch giữa:

- nội dung text trong view
- kỳ vọng text trong feature test

Nghĩa là flow tồn tại, nhưng bộ kiểm thử hồi quy cho phần giao diện đang bị đỏ.

---

## 9. Kiểm thử hiện có

Khi chạy `php artisan test` tại thời điểm cập nhật tài liệu này:

- `85` test pass
- `3` test fail

Các cụm đã có feature test rõ ràng:

- auth và truy cập học viên
- quản lý khóa học
- logic học tập
- lịch giảng và đơn xin nghỉ
- phân quyền nội dung giảng viên
- thư viện tài nguyên
- live room
- kiểm tra online
- import câu hỏi
- question bank

Nhìn từ góc độ đồ án, đây là điểm rất mạnh vì dự án không chỉ có flow trên giấy mà đã có kiểm thử bao phủ nhiều đường đi thực tế.

---

## 10. Đánh giá tổng quan

### 10.1. Điểm mạnh

- phạm vi nghiệp vụ rộng và có chiều sâu
- vòng đời dữ liệu khá sát hệ thống đào tạo thật
- có tách service ở các mảng khó như lịch giảng, live room, scoring, import
- phía học viên không bị làm sơ sài
- có test ở mức flow cho nhiều phân hệ

### 10.2. Điểm cần nâng cấp

- khép kín các vòng đời còn thiếu ở tầng route / wiring
- thống nhất điều hướng theo `assignment context`
- đưa toàn bộ test về trạng thái xanh
- tiếp tục chuẩn hóa tài liệu để bám sát code hơn nữa

### 10.3. Kết luận ngắn

Đây là một dự án LMS nội bộ có cấu trúc tốt, không còn ở mức CRUD đơn lẻ mà đã có nhiều flow liên phân hệ:

`quản trị đào tạo + vận hành giảng dạy + nội dung học tập + live room + kiểm tra + tổng hợp kết quả`

Giá trị lớn nhất của dự án nằm ở chỗ:

- đã hình thành được chuỗi nghiệp vụ tương đối hoàn chỉnh
- có sự liên kết giữa admin, giảng viên và học viên
- đã có nền tốt để nâng tiếp thành bản demo mạnh hoặc sản phẩm nội bộ ổn định hơn

---

## 11. Tóm tắt một câu

Flow lõi của hệ thống hiện tại là:

`Admin thiết kế và vận hành lớp học -> Giảng viên nhận phân công, dạy học, tạo nội dung và đánh giá -> Học viên ghi danh, học theo lộ trình, tham gia live room, làm bài và nhận kết quả tổng hợp`
