# FLOW HỌC VIÊN THEO HỆ THỐNG HIỆN TẠI

## 1. Mục tiêu tài liệu

Tài liệu này mô tả flow của học viên dựa trên code và cấu trúc đang có trong hệ thống hiện tại.

Mục tiêu:
- mô tả rõ hành trình của học viên từ lúc đăng ký đến khi tham gia học
- phân biệt phần nào hệ thống đã có
- chỉ ra phần nào chưa có hoặc mới ở mức nền tảng
- làm cơ sở để tiếp tục code phần học viên theo phase sau

---

## 2. Vai trò liên quan

### 2.1. Học viên
- tự đăng ký tài khoản
- đăng nhập vào hệ thống
- xem các khóa học mình đang tham gia
- vào chi tiết khóa học để xem buổi học
- xem tài liệu giảng viên đã công khai
- vào lớp online khi buổi học đang diễn ra

### 2.2. Admin
- quản lý tài khoản học viên
- thêm học viên vào khóa học
- cập nhật hoặc xóa học viên khỏi khóa học
- duyệt yêu cầu liên quan đến học viên do giảng viên gửi lên

### 2.3. Giảng viên
- dạy các module/buổi học được phân công
- đăng tài liệu cho từng buổi học
- công khai hoặc ẩn tài liệu với học viên
- gửi yêu cầu thêm, xóa, sửa học viên lên admin

---

## 3. Flow tổng quát của học viên

### Flow tổng quan
1. Người dùng truy cập trang đăng ký.
2. Người dùng chọn vai trò học viên và tạo tài khoản.
3. Hệ thống tạo tài khoản học viên ngay lập tức và cho đăng nhập luôn.
4. Học viên đăng nhập và vào dashboard học viên.
5. Học viên chỉ nhìn thấy khóa học khi đã được ghi danh vào bảng `hoc_vien_khoa_hoc`.
6. Việc ghi danh hiện tại xảy ra theo 1 trong 2 cách:
   - admin thêm trực tiếp học viên vào khóa học
   - giảng viên gửi yêu cầu, admin duyệt rồi hệ thống thêm học viên vào khóa học
7. Sau khi đã tham gia khóa học, học viên vào mục “Khóa học của tôi”.
8. Học viên chọn một khóa học để xem chi tiết.
9. Trong chi tiết khóa học, học viên thấy danh sách buổi học theo module.
10. Ở từng buổi học, học viên thấy tài liệu đã được giảng viên công khai.
11. Nếu buổi học online đang diễn ra và có link, học viên có thể vào phòng học.

---

## 4. Flow chi tiết theo nghiệp vụ

## 4.1. Đăng ký tài khoản học viên

### Mô tả
- Người dùng vào trang `Đăng ký`.
- Chọn vai trò `hoc_vien`.
- Nhập họ tên, email, mật khẩu, số điện thoại, ngày sinh, địa chỉ.
- Hệ thống validate dữ liệu.
- Nếu hợp lệ, hệ thống tạo bản ghi trong bảng `nguoi_dung`.
- Trạng thái tài khoản học viên được bật ngay.
- Hệ thống tự động đăng nhập và chuyển về dashboard học viên.

### Kết quả
- học viên không phải chờ admin duyệt tài khoản
- có thể dùng ngay sau khi đăng ký

### Trạng thái hệ thống hiện tại
- Đã có

### Ghi chú
- Luồng này khác với giảng viên.
- Giảng viên hiện đi qua bảng chờ phê duyệt, còn học viên thì được tạo ngay.

---

## 4.2. Đăng nhập học viên

### Mô tả
- Học viên nhập email và mật khẩu tại trang đăng nhập.
- Hệ thống kiểm tra tài khoản trong bảng `nguoi_dung`.
- Nếu đăng nhập thành công và vai trò là `hoc_vien`, hệ thống chuyển về khu vực học viên.

### Kết quả
- học viên vào được dashboard học viên

### Trạng thái hệ thống hiện tại
- Đã có

---

## 4.3. Tham gia khóa học

### Mô tả nghiệp vụ mong muốn
Học viên tham gia lớp học hoặc khóa học theo một trong hai hướng:
- được admin thêm trực tiếp vào khóa học
- tự xin vào lớp học rồi chờ xác nhận

### Thực tế hệ thống hiện tại
Hiện tại hệ thống mới có 2 hướng thực tế:

#### Hướng 1. Admin thêm học viên trực tiếp vào khóa học
1. Admin vào màn quản lý học viên của một khóa học.
2. Hệ thống hiển thị danh sách học viên khả dụng chưa nằm trong khóa đó.
3. Admin chọn một hoặc nhiều học viên.
4. Hệ thống tạo bản ghi trong bảng `hoc_vien_khoa_hoc`.
5. Trạng thái mặc định là `dang_hoc`.
6. Từ thời điểm đó, học viên sẽ thấy khóa học trong khu vực của mình.

#### Hướng 2. Giảng viên gửi yêu cầu lên admin
1. Giảng viên đang phụ trách khóa học gửi yêu cầu thêm, xóa hoặc sửa học viên.
2. Hệ thống tạo bản ghi trong bảng `yeu_cau_hoc_vien`.
3. Admin vào màn duyệt yêu cầu học viên.
4. Nếu admin duyệt yêu cầu thêm:
   - hệ thống tìm học viên theo email
   - nếu học viên đã có tài khoản thì thêm vào `hoc_vien_khoa_hoc`
   - nếu chưa có tài khoản thì báo lỗi

### Kết quả
- học viên chỉ nhìn thấy khóa học sau khi đã có bản ghi ghi danh

### Trạng thái hệ thống hiện tại
- Admin thêm trực tiếp: Đã có
- Giảng viên gửi yêu cầu để admin duyệt: Đã có
- Học viên tự xin vào lớp từ giao diện học viên: Chưa có

### Ghi chú
- Nếu muốn đúng flow “học viên tự xin vào lớp”, cần bổ sung:
  - giao diện chọn lớp hoặc khóa học muốn tham gia
  - form gửi yêu cầu tham gia
  - bảng lưu yêu cầu tham gia từ học viên
  - màn admin duyệt yêu cầu này

---

## 4.4. Xem danh sách khóa học của tôi

### Mô tả
- Sau khi đăng nhập, học viên vào menu `Khóa học của tôi`.
- Hệ thống đọc bảng `hoc_vien_khoa_hoc` theo `hoc_vien_id`.
- Hệ thống nạp thêm thông tin khóa học từ bảng `khoa_hoc`.
- Giao diện hiển thị:
  - tên khóa học
  - ảnh khóa học
  - nhóm ngành
  - ngày khai giảng
  - trình độ
  - trạng thái ghi danh
- Học viên bấm `Vào học ngay` để đi vào chi tiết khóa học.

### Kết quả
- học viên thấy các khóa mình đã được ghi danh

### Trạng thái hệ thống hiện tại
- Đã có

### Ghi chú
- Nếu học viên chưa được ghi danh khóa nào thì giao diện hiện thông báo chưa tham gia khóa học nào.

---

## 4.5. Vào chi tiết khóa học

### Mô tả
1. Học viên bấm vào một khóa học trong danh sách.
2. Hệ thống kiểm tra học viên có thuộc khóa học đó không.
3. Nếu không thuộc khóa học:
   - từ chối truy cập
   - chuyển về danh sách khóa học của tôi
4. Nếu hợp lệ:
   - nạp thông tin khóa học
   - nạp danh sách module
   - nạp lịch học của khóa
   - nạp tài nguyên buổi học đã công khai

### Kết quả
- học viên chỉ xem được khóa học mình thực sự tham gia

### Trạng thái hệ thống hiện tại
- Đã có

---

## 4.6. Xem các buổi học trong khóa

### Mô tả
- Trong trang chi tiết khóa học, hệ thống hiển thị các buổi học theo từng module.
- Mỗi buổi học hiển thị:
  - số buổi
  - ngày học
  - thứ trong tuần
  - giờ bắt đầu và kết thúc
  - hình thức học: online hoặc trực tiếp
  - phòng học hoặc link online
  - trạng thái buổi học

### Kết quả
- học viên nắm được toàn bộ lịch học của khóa

### Trạng thái hệ thống hiện tại
- Đã có

---

## 4.7. Xem tài liệu giảng viên đăng theo từng buổi học

### Mô tả
- Ở mỗi buổi học, hệ thống hiển thị khu vực tài liệu học tập.
- Chỉ các tài nguyên có trạng thái `hien` mới được load cho học viên.
- Học viên thấy:
  - loại tài nguyên
  - tiêu đề
  - mô tả
  - nút xem chi tiết
  - nút tải về nếu là file tải được

### Quy tắc hiển thị
- Tài liệu chỉ hiện khi giảng viên đã công khai.
- Tài liệu bị ẩn sẽ không xuất hiện ở phía học viên.
- Nếu file không còn tồn tại, hệ thống hiện cảnh báo liên hệ giảng viên.

### Kết quả
- học viên tiếp cận được tài liệu đúng theo từng buổi học

### Trạng thái hệ thống hiện tại
- Đã có

---

## 4.8. Vào lớp học online

### Mô tả
- Nếu buổi học có:
  - `hinh_thuc = online`
  - có `link_online`
  - trạng thái buổi học là `dang_hoc`
- giao diện sẽ hiện nút `VÀO PHÒNG HỌC`
- học viên bấm vào để vào link học trực tuyến

### Kết quả
- học viên vào lớp online đúng buổi đang diễn ra

### Trạng thái hệ thống hiện tại
- Đã có

---

## 4.9. Theo dõi hoạt động học tập

### Mô tả nghiệp vụ mong muốn
Học viên nên theo dõi được:
- buổi học đã tham gia
- tài liệu đã xem
- bài tập hoặc bài kiểm tra đã làm
- tiến độ học tập

### Thực tế hệ thống hiện tại
- Dashboard học viên hiện chủ yếu là giao diện minh họa.
- Nhiều số liệu đang dùng dữ liệu mẫu hoặc ngẫu nhiên.
- Chưa thấy truy xuất thật từ dữ liệu học tập để phản ánh tiến độ thực.

### Trạng thái hệ thống hiện tại
- Mới có giao diện nền
- Chưa hoàn thiện nghiệp vụ thật

---

## 4.10. Theo dõi tiến độ học tập

### Mô tả nghiệp vụ mong muốn
Học viên cần biết:
- mình đã học bao nhiêu buổi
- còn bao nhiêu buổi chưa học
- tỷ lệ hoàn thành khóa học
- module nào đã hoàn tất

### Thực tế hệ thống hiện tại
- Chưa thấy logic tiến độ học tập thật dựa trên dữ liệu `hoc_vien_khoa_hoc`, `lich_hoc`, `diem_danh` hoặc `bai_kiem_tra`.
- Dashboard hiện mới là phần giao diện mẫu.

### Trạng thái hệ thống hiện tại
- Chưa hoàn thiện

---

## 4.11. Xem điểm và kết quả học tập

### Mô tả nghiệp vụ mong muốn
Học viên nên xem được:
- điểm kiểm tra
- kết quả từng bài
- điểm trung bình
- nhận xét hoặc trạng thái hoàn thành

### Thực tế hệ thống hiện tại
- Có model và nền tảng cho bài kiểm tra.
- Phía học viên chưa có route hoàn chỉnh cho trang `bài kiểm tra` và `kết quả`.
- Sidebar học viên đang gọi đến route `hoc-vien.bai-kiem-tra` và `hoc-vien.ket-qua`, nhưng route thật chưa thấy được khai báo.

### Trạng thái hệ thống hiện tại
- Chưa hoàn thiện

---

## 5. Bảng tóm tắt mức độ hoàn thiện

| Chức năng | Trạng thái hiện tại |
|---|---|
| Đăng ký tài khoản học viên | Đã có |
| Đăng nhập học viên | Đã có |
| Admin thêm học viên vào khóa học | Đã có |
| Giảng viên gửi yêu cầu thêm học viên để admin duyệt | Đã có |
| Học viên tự xin vào lớp | Chưa có |
| Xem khóa học của tôi | Đã có |
| Xem chi tiết khóa học | Đã có |
| Xem buổi học trong khóa | Đã có |
| Xem tài liệu giảng viên công khai | Đã có |
| Vào lớp học online | Đã có |
| Theo dõi hoạt động học tập thật | Chưa hoàn thiện |
| Theo dõi tiến độ học tập thật | Chưa hoàn thiện |
| Làm bài kiểm tra phía học viên | Chưa hoàn thiện |
| Xem điểm và kết quả học tập | Chưa hoàn thiện |

---

## 6. Flow đề xuất chuẩn cho học viên trong tương lai

Nếu muốn hoàn thiện đúng bài toán nghiệp vụ, flow lý tưởng nên là:

1. Học viên đăng ký tài khoản.
2. Học viên đăng nhập hệ thống.
3. Học viên tìm lớp hoặc khóa học muốn tham gia.
4. Học viên gửi yêu cầu xin vào lớp.
5. Admin duyệt yêu cầu.
6. Sau khi được duyệt, hệ thống tạo ghi danh vào `hoc_vien_khoa_hoc`.
7. Học viên vào `Khóa học của tôi`.
8. Học viên xem chi tiết khóa học.
9. Học viên xem buổi học theo module.
10. Học viên xem tài liệu giảng viên công khai.
11. Học viên vào lớp online hoặc tham dự buổi học trực tiếp.
12. Học viên theo dõi tiến độ học.
13. Học viên làm bài kiểm tra.
14. Học viên xem kết quả và điểm số.

---

## 7. Kết luận

Phần học viên trong hệ thống hiện tại đã có nền khá rõ ở các nhánh:
- đăng ký và đăng nhập
- ghi danh vào khóa học
- xem khóa học của tôi
- xem chi tiết buổi học
- xem tài liệu giảng viên công khai
- vào lớp online

Tuy nhiên, phần học viên vẫn còn thiếu ở các nhánh quan trọng:
- tự xin vào lớp
- theo dõi tiến độ học tập thật
- làm bài kiểm tra phía học viên
- xem điểm và kết quả học tập hoàn chỉnh

Vì vậy, nếu triển khai tiếp theo phase, nên ưu tiên:
1. flow học viên xin vào lớp
2. dashboard học viên dùng dữ liệu thật
3. bài kiểm tra cho học viên
4. màn hình điểm và kết quả học tập
