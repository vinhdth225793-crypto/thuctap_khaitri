# Đánh giá và Flow tổng thể đồ án

## 1. Phạm vi tài liệu

Tài liệu này được viết dựa trên code hiện tại của dự án Laravel trong repository, không dựa trên mô tả lý tưởng.

Mục tiêu:

- tóm tắt bài toán mà đồ án đang giải quyết
- mô tả flow nghiệp vụ đúng theo hệ thống hiện có
- chỉ ra điểm mạnh, điểm còn thiếu và các rủi ro kỹ thuật

---

## 2. Bài toán đồ án đang giải quyết

Đây là hệ thống quản lý học tập nội bộ theo mô hình:

`Quản trị đào tạo -> Khóa học -> Module -> Lịch học/Buổi học -> Tài nguyên/Bài giảng/Bài kiểm tra -> Học viên -> Kết quả học tập`

Hệ thống có 3 vai trò chính:

- `Admin`
- `Giảng viên`
- `Học viên`

Các phân hệ nổi bật:

- quản lý tài khoản và phê duyệt đăng ký
- quản lý nhóm ngành, khóa học mẫu, mở lớp đang hoạt động
- phân công giảng viên theo module
- quản lý lịch học và lớp online
- điểm danh và báo cáo buổi học
- thư viện tài nguyên và bài giảng
- kiểm tra online, chấm tự động, chấm tự luận
- tổng hợp kết quả học tập
- thông báo nội bộ

---

## 3. Thực thể dữ liệu cốt lõi

### 3.1. Người dùng

- `nguoi_dung`: tài khoản gốc, chứa vai trò `admin`, `giang_vien`, `hoc_vien`
- `giang_vien`: hồ sơ riêng cho giảng viên
- `hoc_vien`: hồ sơ riêng cho học viên
- `tai_khoan_cho_phe_duyet`: nơi lưu đăng ký giảng viên trước khi admin duyệt

### 3.2. Đào tạo

- `nhom_nganh`: danh mục ngành/nhóm môn
- `khoa_hoc`: vừa dùng cho khóa mẫu, vừa dùng cho lớp đang hoạt động
- `module_hoc`: đơn vị nội dung thuộc khóa học
- `phan_cong_module_giang_vien`: phân công giảng viên cho module
- `hoc_vien_khoa_hoc`: bảng ghi danh học viên vào khóa học
- `lich_hoc`: từng buổi học cụ thể

### 3.3. Nội dung học tập

- `tai_nguyen_buoi_hoc`: vừa đóng vai trò thư viện tài nguyên, vừa là tài nguyên gắn vào buổi học
- `bai_giangs`: bài giảng gắn với khóa học/module/buổi học
- `bai_giang_tai_nguyen`: pivot liên kết bài giảng với tài nguyên phụ

### 3.4. Kiểm tra online

- `ngan_hang_cau_hoi`
- `dap_an_cau_hoi`
- `bai_kiem_tra`
- `chi_tiet_bai_kiem_tra`
- `bai_lam_bai_kiem_tra`
- `chi_tiet_bai_lam_bai_kiem_tra`
- `ket_qua_hoc_tap`

### 3.5. Vận hành

- `diem_danh`
- `yeu_cau_hoc_vien`
- `thong_bao`

---

## 4. Flow tổng thể của đồ án

## 4.1. Flow tài khoản và xác thực

### Khách

1. Người dùng truy cập trang chủ.
2. Người dùng có thể đăng ký hoặc đăng nhập.

### Học viên đăng ký

1. Người dùng chọn vai trò `học viên`.
2. Hệ thống tạo bản ghi trong `nguoi_dung`.
3. Hệ thống tạo luôn hồ sơ `hoc_vien`.
4. Hệ thống đăng nhập tự động và chuyển vào dashboard học viên.

### Giảng viên đăng ký

1. Người dùng chọn vai trò `giảng viên`.
2. Hệ thống không tạo tài khoản hoạt động ngay.
3. Dữ liệu được lưu vào `tai_khoan_cho_phe_duyet`.
4. Admin vào màn hình phê duyệt để duyệt hoặc từ chối.
5. Khi được duyệt, tài khoản giảng viên mới được đưa vào hệ thống chính.

### Đăng nhập

1. Hệ thống tìm `nguoi_dung` theo email.
2. Kiểm tra mật khẩu.
3. Nếu đúng thì điều hướng theo vai trò:
   - `admin -> /admin/dashboard`
   - `giang_vien -> /giang-vien/dashboard`
   - `hoc_vien -> /hoc-vien/dashboard`

---

## 4.2. Flow xây dựng cấu trúc đào tạo

### Bước 1: Admin tạo nhóm ngành

1. Admin tạo dữ liệu `nhom_nganh`.
2. Nhóm ngành là nền để gắn khóa học.

### Bước 2: Admin tạo khóa học mẫu

1. Admin tạo `khoa_hoc` với `loai = mau`.
2. Khi tạo khóa học mẫu, admin nhập luôn danh sách module.
3. Hệ thống sinh `module_hoc` theo thứ tự.

### Bước 3: Admin mở lớp từ khóa mẫu

1. Admin chọn một khóa mẫu.
2. Hệ thống clone khóa mẫu thành một khóa đang hoạt động với `loai = hoat_dong`.
3. Hệ thống clone toàn bộ module của khóa mẫu sang lớp mới.
4. Admin có thể gán giảng viên cho từng module ngay khi mở lớp.
5. Nếu đã có phân công, trạng thái vận hành chuyển sang `cho_giang_vien`.

### Bước 4: Giảng viên xác nhận phân công

1. Giảng viên vào danh sách khóa học được phân công.
2. Giảng viên chọn `da_nhan` hoặc `tu_choi`.
3. Nếu tất cả module đã có giảng viên xác nhận, khóa học được cập nhật sang `san_sang`.
4. Hệ thống gửi thông báo cho admin.

### Bước 5: Admin xác nhận mở lớp chính thức

1. Admin kiểm tra khóa học ở trạng thái `san_sang`.
2. Admin bấm xác nhận mở lớp.
3. Trạng thái vận hành chuyển sang `dang_day`.

---

## 4.3. Flow học viên tham gia khóa học

### Bước 1: Học viên xem khóa học có thể tham gia

1. Học viên vào màn hình `khóa học tham gia`.
2. Hệ thống chỉ hiển thị khóa đang hoạt động, chưa ghi danh và đang ở trạng thái phù hợp như `cho_giang_vien`, `san_sang`, `dang_day`.

### Bước 2: Học viên gửi yêu cầu tham gia

1. Học viên nhập lý do.
2. Hệ thống tạo bản ghi `yeu_cau_hoc_vien` với:
   - `loai_yeu_cau = them`
   - `trang_thai = cho_duyet`

### Bước 3: Admin xử lý yêu cầu

1. Admin vào danh sách yêu cầu học viên.
2. Nếu duyệt:
   - hệ thống thêm hoặc cập nhật bản ghi `hoc_vien_khoa_hoc`
   - trạng thái ghi danh là `dang_hoc`
3. Nếu từ chối:
   - chỉ cập nhật trạng thái yêu cầu

---

## 4.4. Flow lập lịch học

### Admin

1. Admin vào từng khóa học hoạt động.
2. Admin tạo lịch học thủ công hoặc tạo tự động.
3. Mỗi `lich_hoc` gắn với:
   - khóa học
   - module
   - ngày học
   - giờ học
   - hình thức `online` hoặc `truc_tiep`
   - buổi số

### Giảng viên

1. Giảng viên được phân công có thể cập nhật link online cho buổi học.
2. Hệ thống kiểm tra quyền dựa trên module đã được giao và đã xác nhận.

### Đồng bộ trạng thái

1. Hệ thống có command để đồng bộ trạng thái buổi học theo thời gian.
2. Buổi học có thể chuyển giữa `cho`, `dang_hoc`, `hoan_thanh`, `huy`.

---

## 4.5. Flow điểm danh

### Bước 1: Mở danh sách điểm danh

1. Giảng viên mở một buổi học.
2. Hệ thống chỉ trả về học viên có trạng thái ghi danh `dang_hoc`.
3. Hệ thống không tự mặc định là có mặt.

### Bước 2: Lưu điểm danh

1. Giảng viên gửi danh sách trạng thái:
   - `co_mat`
   - `vang_mat`
   - `vao_tre`
2. Hệ thống `updateOrCreate` vào bảng `diem_danh`.
3. Sau khi lưu, hệ thống cập nhật lại `ket_qua_hoc_tap` của từng học viên bị ảnh hưởng.

### Bước 3: Gửi báo cáo buổi học

1. Giảng viên nhập báo cáo.
2. Hệ thống lưu vào `lich_hoc`:
   - `bao_cao_giang_vien`
   - `thoi_gian_bao_cao`
   - `trang_thai_bao_cao`

---

## 4.6. Flow thư viện tài nguyên

### Mục tiêu

Giảng viên chuẩn bị tài nguyên trước khi đưa vào bài giảng hoặc buổi học.

### Bước 1: Giảng viên tạo tài nguyên thư viện

1. Giảng viên vào `thư viện`.
2. Tạo tài nguyên với các thông tin:
   - tiêu đề
   - mô tả
   - loại tài nguyên
   - file hoặc link ngoài
   - phạm vi sử dụng
3. Hệ thống lưu vào `tai_nguyen_buoi_hoc` nhưng `lich_hoc_id` có thể để trống.

### Bước 2: Gửi admin duyệt

1. Giảng viên bấm gửi duyệt.
2. Trạng thái duyệt chuyển sang `cho_duyet`.

### Bước 3: Admin duyệt

1. Admin vào màn hình thư viện.
2. Admin xem chi tiết tài nguyên.
3. Admin cập nhật trạng thái:
   - `da_duyet`
   - `can_chinh_sua`
   - `tu_choi`

---

## 4.7. Flow bài giảng

### Bước 1: Giảng viên tạo bài giảng

1. Giảng viên chọn một `phan_cong_module_giang_vien`.
2. Giảng viên chọn buổi học tương ứng nếu muốn gắn bài giảng vào buổi cụ thể.
3. Giảng viên chọn:
   - tiêu đề
   - mô tả
   - loại bài giảng
   - tài nguyên chính
   - tài nguyên phụ
   - thứ tự hiển thị
   - thời điểm mở

### Bước 2: Gửi bài giảng duyệt

1. Bài giảng ban đầu ở trạng thái `nhap`.
2. Giảng viên bấm gửi duyệt.
3. Trạng thái chuyển sang `cho_duyet`.

### Bước 3: Admin duyệt và công bố

1. Admin duyệt bài giảng.
2. Sau khi duyệt, admin có thể công bố.
3. Bài giảng chỉ hiển thị cho học viên khi đồng thời:
   - `trang_thai_duyet = da_duyet`
   - `trang_thai_cong_bo = da_cong_bo`
   - `thoi_diem_mo` đã tới hoặc để trống

---

## 4.8. Flow tài nguyên buổi học

Ngoài thư viện, giảng viên còn có thể gắn tài nguyên trực tiếp vào `lich_hoc`.

Flow:

1. Giảng viên mở buổi học mình được phân công.
2. Tạo tài nguyên cho buổi học.
3. Có thể bật/tắt `trang_thai_hien_thi`.
4. Học viên nhìn thấy tài nguyên nếu tài nguyên đã được mở hiển thị.

---

## 4.9. Flow kiểm tra online

### A. Ngân hàng câu hỏi

1. Admin quản lý câu hỏi.
2. Có thể thêm thủ công hoặc import CSV.
3. Mỗi câu hỏi có thể thuộc khóa học hoặc module.
4. Câu hỏi có loại:
   - `trac_nghiem`
   - `tu_luan`

### B. Giảng viên tạo đề

1. Giảng viên tạo khung bài kiểm tra.
2. Chọn phạm vi:
   - theo module
   - theo buổi học
   - cuối khóa
3. Hệ thống tạo `bai_kiem_tra` ở trạng thái:
   - `trang_thai_duyet = nhap`
   - `trang_thai_phat_hanh = nhap`
4. Giảng viên vào màn hình sửa đề để:
   - chọn câu hỏi
   - cấu hình thời gian
   - chọn số lần làm
   - random câu hỏi

### C. Giảng viên gửi admin duyệt

1. Giảng viên bấm gửi duyệt.
2. Trạng thái đề chuyển sang `cho_duyet`.

### D. Admin duyệt và phát hành

1. Admin vào khu vực phê duyệt bài kiểm tra.
2. Admin duyệt hoặc từ chối.
3. Nếu duyệt xong, admin có thể phát hành cho học viên.
4. Khi phát hành:
   - `trang_thai_duyet = da_duyet`
   - `trang_thai_phat_hanh = phat_hanh`

### E. Học viên làm bài

1. Học viên chỉ thấy bài kiểm tra đã duyệt và đã phát hành.
2. Học viên bấm bắt đầu.
3. Hệ thống tạo `bai_lam_bai_kiem_tra`.
4. Hệ thống tạo sẵn chi tiết trả lời cho từng câu.
5. Học viên nộp bài.

### F. Chấm điểm

1. Trắc nghiệm được chấm tự động.
2. Nếu có câu tự luận, bài chuyển sang `cho_cham`.
3. Giảng viên vào danh sách chấm điểm.
4. Giảng viên nhập điểm tự luận.
5. Hệ thống cộng điểm và chuyển bài sang `da_cham`.

### G. Tổng hợp kết quả

1. Sau khi nộp bài hoặc sau khi chấm xong, hệ thống gọi `KetQuaHocTapService`.
2. Dữ liệu tổng hợp gồm:
   - điểm điểm danh
   - điểm kiểm tra
   - điểm tổng kết
   - tỷ lệ tham dự
   - số bài kiểm tra hoàn thành

---

## 4.10. Flow học tập của học viên

### Dashboard học viên

Học viên có thể xem:

- khóa học đang học
- buổi học hôm nay
- buổi học sắp tới
- tài liệu mới
- hoạt động gần đây
- tỷ lệ chuyên cần
- tiến độ tổng quan

### Chi tiết khóa học

1. Học viên đã ghi danh vào khóa học mới được vào chi tiết.
2. Hệ thống tải:
   - module
   - lịch học
   - bài giảng đã duyệt và đã công bố
3. Học viên học theo cấu trúc:
   `Khóa học -> Module -> Buổi học -> Bài giảng`

### Kết quả học tập

1. Điểm danh và kiểm tra đều ảnh hưởng đến `ket_qua_hoc_tap`.
2. Cách tính phụ thuộc `phuong_thuc_danh_gia` của khóa học:
   - `theo_module`
   - `cuoi_khoa`

---

## 4.11. Flow thông báo

Hệ thống hiện dùng thông báo nội bộ cho ít nhất 2 tình huống:

1. Admin phân công giảng viên dạy module.
2. Khi lớp đã đủ giảng viên xác nhận, hệ thống báo cho admin biết lớp đã sẵn sàng.

---

## 5. Điểm mạnh của đồ án

### 5.1. Phân tách nghiệp vụ tương đối rõ

Đồ án không dồn hết logic vào một màn hình, mà đã tách được các cụm nghiệp vụ:

- quản trị đào tạo
- vận hành lớp học
- thư viện và bài giảng
- kiểm tra online
- tổng hợp kết quả học tập

### 5.2. Có vòng đời dữ liệu khá thực tế

Nhiều thực thể đã có trạng thái riêng:

- khóa học có trạng thái vận hành
- phân công có trạng thái chờ xác nhận hoặc đã nhận
- bài giảng có trạng thái duyệt và công bố
- tài nguyên có trạng thái duyệt và xử lý
- bài kiểm tra có trạng thái duyệt và phát hành

Điều này làm đồ án giống một hệ thống thật hơn là chỉ CRUD đơn thuần.

### 5.3. Có tư duy service layer

Hai phần quan trọng đã được tách thành service:

- `BaiKiemTraScoringService`
- `KetQuaHocTapService`

Đây là một điểm mạnh vì phần chấm điểm và tổng hợp học tập vốn dễ phình logic nếu để trong controller.

### 5.4. Có test cho các flow nhạy cảm

Repository đã có feature test cho:

- quyền điểm danh
- điều kiện truy cập khóa học
- đồng bộ trạng thái lịch học
- flow kiểm tra online và tổng hợp điểm

Việc đã có test ở mức flow là một điểm cộng lớn cho đồ án.

### 5.5. Flow học viên được làm khá đầy đủ

Không chỉ có phần quản trị, mà phía học viên cũng đã có:

- dashboard
- khóa học của tôi
- xin tham gia khóa học
- xem bài giảng
- làm bài kiểm tra
- theo dõi tiến độ

Đây là điểm tốt vì đồ án đã đi hết vòng đời từ backend đến trải nghiệm người dùng cuối.

---

## 6. Điểm còn yếu và rủi ro kỹ thuật

## 6.1. Chuẩn hóa cột phân công chưa hoàn tất

Code hiện tại đang bị lệch giữa:

- cột mới `giang_vien_id`
- cột cũ `giao_vien_id`

Hậu quả:

- test đang fail
- tạo hoặc truy vấn phân công có thể sai dữ liệu
- các module như phân công, điểm danh, tài nguyên, bài giảng, bài kiểm tra đều bị ảnh hưởng

Đây là lỗi có mức độ nghiêm trọng cao vì nó đụng đúng trục chính của hệ thống.

## 6.2. Module ngân hàng câu hỏi đang lệch giữa schema mới và controller cũ

Phần migration mới đã tách câu hỏi và đáp án sang cấu trúc mới, nhưng controller ngân hàng câu hỏi vẫn đang thao tác với các cột cũ như:

- `noi_dung_cau_hoi`
- `dap_an_sai_1`
- `dap_an_sai_2`
- `dap_an_sai_3`
- `dap_an_dung`

Hậu quả:

- test tạo câu hỏi đang fail
- CRUD câu hỏi và import CSV không đồng bộ với schema mới
- logic trùng lặp câu hỏi cũng đang bám vào trường cũ

## 6.3. Đăng nhập chưa kiểm tra trạng thái hoạt động của tài khoản

Hiện tại luồng login mới kiểm tra:

- có user
- đúng mật khẩu

Nhưng chưa chặn tài khoản bị tắt `trang_thai = false`.

Đây là rủi ro nghiệp vụ và bảo mật, vì admin có thể đã khóa tài khoản nhưng user vẫn đăng nhập được.

## 6.4. Một số flow mới được mô tả tốt nhưng chưa hoàn thiện ở mức triển khai

Ví dụ tài nguyên video trong thư viện đã có trường trạng thái xử lý, nhưng hiện tại code vẫn set video thành `san_sang` ngay và để comment TODO cho phần transcoding.

Nghĩa là thiết kế đã tốt, nhưng implementation chưa đi hết đến nơi.

## 6.5. Có chỗ thiếu import hoặc lệch namespace

Trong `HocVienController`, method xem chi tiết bài giảng đang gọi `BaiGiang::...` nhưng phần import model chưa có.

Đây là kiểu lỗi nhỏ nhưng dễ gây lỗi runtime khi đi vào nhánh chức năng thật.

## 6.6. Tính năng random câu hỏi chưa khóa thứ tự theo từng lần làm

Hiện tại nếu bật random câu hỏi, màn hình làm bài đang `shuffle()` mỗi lần hiển thị trong lúc bài còn dang dở.

Điều đó có thể khiến thứ tự câu hỏi thay đổi giữa các lần refresh, gây rối cho học viên nếu giao diện không khóa thứ tự theo attempt.

## 6.7. Quyền ngân hàng câu hỏi đang chưa nhất quán với ý tưởng nghiệp vụ

Controller phần câu hỏi có nhánh kiểm tra quyền cho giảng viên, nhưng route thực tế lại đang nằm trong nhóm `admin`.

Điều này cho thấy thiết kế có ý định cho giảng viên tham gia quản lý câu hỏi, nhưng routing hiện tại chưa phản ánh đúng ý đó.

---

## 7. Kết quả kiểm thử hiện tại

Khi chạy `php artisan test`, hệ thống hiện có:

- 6 test pass
- 7 test fail

Hai nhóm lỗi nổi bật:

1. lỗi `NOT NULL constraint failed: phan_cong_module_giang_vien.giang_vien_id`
2. lỗi validate/nghiệp vụ ở module ngân hàng câu hỏi do controller vẫn dùng field cũ

Nói ngắn gọn:

- bộ khung hệ thống tốt
- nhưng đang có nợ kỹ thuật sau các lần refactor schema

---

## 8. Đánh giá tổng quan đồ án

Nếu đánh giá theo góc nhìn đồ án tốt nghiệp hoặc đồ án môn học, đây là một đồ án có chiều sâu tốt vì:

- phạm vi nghiệp vụ rộng
- có mô hình vai trò rõ ràng
- có dữ liệu và trạng thái sát hệ thống thật
- có cả phần quản trị, giảng dạy, học tập và đánh giá kết quả
- đã có tư duy test và service

Nếu đánh giá theo góc nhìn sản phẩm sẵn sàng triển khai, dự án vẫn cần thêm một vòng làm sạch kỹ thuật:

- chốt tên cột và quan hệ sau refactor
- đồng bộ controller với schema mới
- khóa các lỗi runtime nhỏ
- tăng test hồi quy cho các flow vừa refactor

### Kết luận ngắn

Đây là đồ án tốt ở phần ý tưởng, phạm vi và cách chia module.

Điểm cần nâng cấp mạnh nhất không phải là thêm tính năng mới, mà là:

- làm sạch tính nhất quán dữ liệu
- ổn định các flow sau refactor
- chốt lại ranh giới giữa thiết kế và code thực tế

Nếu xử lý tốt 3 điểm này, chất lượng đồ án sẽ tăng lên rất rõ.

---

## 9. Thứ tự ưu tiên nếu muốn nâng cấp tiếp

1. sửa dứt điểm `giao_vien_id` và `giang_vien_id`
2. refactor lại toàn bộ module ngân hàng câu hỏi theo schema mới
3. thêm chặn đăng nhập với tài khoản bị vô hiệu hóa
4. sửa lỗi import model và các lỗi runtime nhỏ
5. cố định thứ tự câu hỏi random theo từng lần làm bài
6. hoàn thiện hàng đợi xử lý video nếu muốn bám đúng flow thư viện đã thiết kế
7. cập nhật README thành README riêng của dự án thay vì README mặc định của Laravel

---

## 10. Tóm tắt flow một câu

Flow lõi của đồ án là:

`Admin tạo cấu trúc đào tạo và mở lớp -> Giảng viên nhận phân công, dạy học, quản lý tài nguyên, bài giảng, bài kiểm tra -> Học viên xin vào lớp, học theo lộ trình, làm bài và nhận kết quả -> Hệ thống tổng hợp điểm danh và điểm kiểm tra thành kết quả học tập`
