# Hướng dẫn hệ thống và bản đồ nghiệp vụ cho AI đọc nhanh

## 1. Mục tiêu của file này

Đây là file onboarding cho AI hoặc người mới vào project.

Mục tiêu:

- đọc một lần là nắm được dự án làm gì
- hiểu actor nào có quyền gì
- hiểu flow đầu-cuối của đồ án
- biết các thực thể lõi và trạng thái quan trọng
- biết nên đọc file nào khi cần sửa một phân hệ cụ thể

Nguyên tắc:

- mô tả theo **implementation hiện tại**
- ưu tiên `routes`, controller, service, model, test
- không mô tả theo phiên bản “nên có” nếu code chưa thực sự làm như vậy

---

## 2. Dự án này là gì

`thuctap_khaitri` là một hệ thống quản lý đào tạo trực tuyến kiểu LMS nội bộ, có đủ cả 3 vai:

- `admin`
- `giang_vien`
- `hoc_vien`

Hệ thống không chỉ là CRUD khóa học, mà là một pipeline đào tạo đầy đủ:

```text
Trang chủ / đăng ký
-> quản trị tài khoản
-> tạo nhóm ngành và khóa học mẫu
-> mở lớp hoạt động
-> phân công giảng viên
-> lập lịch học
-> vận hành buổi học
-> tài nguyên / bài giảng / live room / bài kiểm tra
-> học viên học tập
-> điểm danh / chấm điểm
-> tổng hợp kết quả học tập
```

Các phân hệ lớn đang có:

- auth, đăng ký, đăng nhập, phân vai
- public home, banner, tìm kiếm khóa học
- quản lý tài khoản và phê duyệt tài khoản giảng viên
- nhóm ngành, khóa học, module
- mở lớp từ khóa học mẫu
- phân công giảng viên
- lịch học và quy tắc lịch
- đơn xin nghỉ giảng
- điểm danh giảng viên và học viên
- thư viện tài nguyên và tài nguyên buổi học
- bài giảng và bài giảng live
- live room
- ngân hàng câu hỏi
- kiểm tra online có giám sát
- dashboard, tiến độ và kết quả học tập của học viên
- thông báo và settings hệ thống

---

## 3. Stack kỹ thuật và cấu trúc dự án

### 3.1. Stack

- backend: Laravel 12
- PHP: `^8.2`
- frontend server-rendered: Blade
- asset build: Vite
- test: PHPUnit qua `php artisan test`

### 3.2. Thư mục quan trọng

- `routes/web.php`: route toàn hệ thống
- `app/Http/Controllers`: điều phối theo vai trò và phân hệ
- `app/Services`: business logic quan trọng
- `app/Models`: mô hình dữ liệu
- `database/migrations`: schema
- `resources/views`: giao diện Blade
- `tests/Feature`: nơi thể hiện nhiều rule nghiệp vụ thật nhất
- `docs`: ghi chú kỹ thuật và flow cũ
- `promt`: nơi đặt tài liệu/prompt để AI khác tiếp tục làm việc

### 3.3. Tư duy đọc code đúng

- route cho biết ai được vào màn nào
- controller cho biết flow request/response
- service cho biết rule nghiệp vụ thật
- model cho biết trạng thái, helper, scope và quan hệ
- feature test cho biết các edge case đã được khóa

---

## 4. Actor chính và quyền

### 4.1. Khách chưa đăng nhập

Có thể:

- vào trang chủ `/`
- tìm kiếm khóa học `/search`
- đăng ký
- đăng nhập

Không thể:

- truy cập khu admin
- truy cập khu giảng viên
- truy cập khu học viên

### 4.2. `admin`

Là actor vận hành toàn hệ thống.

Quyền chính:

- xem dashboard admin
- quản lý tài khoản người dùng
- duyệt tài khoản giảng viên đăng ký mới
- quản lý banner và settings
- quản lý `nhom_nganh`
- quản lý `khoa_hoc`
- mở lớp từ khóa học mẫu
- quản lý `module_hoc`
- phân công giảng viên
- quản lý học viên trong khóa học
- lập lịch học thủ công hoặc tự động
- xem điểm danh
- xử lý yêu cầu học viên
- duyệt đơn xin nghỉ giảng viên
- duyệt tài nguyên thư viện
- duyệt và công bố bài giảng
- quản lý question bank
- duyệt và phát hành bài kiểm tra online

### 4.3. `giang_vien`

Là actor vận hành phần giảng dạy trong phạm vi được phân công.

Quyền chính:

- xem dashboard giảng viên
- cập nhật profile
- xem lịch giảng
- xem danh sách khóa/module được phân công
- xác nhận hoặc từ chối phân công
- bắt đầu hoặc kết thúc buổi học
- cập nhật link học online
- điểm danh học viên
- check-in, check-out điểm danh giảng viên
- gửi đơn xin nghỉ
- quản lý thư viện tài nguyên
- gắn tài nguyên vào buổi học
- tạo và gửi duyệt bài giảng
- tạo live room cho buổi học
- tạo bài kiểm tra
- import câu hỏi vào question bank từ màn hình đề
- chấm tự luận
- gửi yêu cầu thêm/xóa/sửa học viên trong khóa

Giới hạn quan trọng:

- quyền sâu của giảng viên đi theo `phan_cong_module_giang_vien`
- chỉ assignment `da_nhan` mới được dùng cho các flow chính

### 4.4. `hoc_vien`

Là actor học tập và làm bài.

Quyền chính:

- xem dashboard học viên
- xem hoạt động và tiến độ
- xem kết quả học tập
- xem khóa học của tôi
- xem khóa học có thể tham gia
- gửi yêu cầu xin tham gia lớp
- xem chi tiết khóa học
- xem chi tiết buổi học
- xem bài giảng
- vào live room
- làm bài kiểm tra online
- cập nhật profile

Giới hạn quan trọng:

- học viên chỉ nhìn thấy nội dung nếu có ghi danh hợp lệ
- nội dung phải qua các cổng duyệt/công bố/mở hiển thị tương ứng

---

## 5. Thực thể lõi cần nắm trước

### 5.1. Danh tính và hồ sơ

- `NguoiDung`: tài khoản đăng nhập chung
- `GiangVien`: hồ sơ giảng viên
- `HocVien`: hồ sơ học viên
- `TaiKhoanChoPheDuyet`: nơi giữ đăng ký giảng viên chờ admin duyệt

### 5.2. Đào tạo

- `NhomNganh`: danh mục nhóm ngành
- `KhoaHoc`: khóa học, gồm `mau` và `hoat_dong`
- `ModuleHoc`: module thuộc khóa học
- `PhanCongModuleGiangVien`: assignment giảng viên cho module
- `HocVienKhoaHoc`: ghi danh học viên vào khóa
- `LichHoc`: từng buổi học cụ thể
- `YeuCauHocVien`: yêu cầu thêm/xóa/tham gia học viên

### 5.3. Nội dung học tập

- `TaiNguyenBuoiHoc`: vừa dùng cho thư viện, vừa dùng cho tài nguyên gắn buổi học
- `BaiGiang`: bài giảng thường hoặc live
- `PhongHocLive`: phòng học live
- `PhongHocLiveNguoiThamGia`
- `PhongHocLiveBanGhi`

### 5.4. Đánh giá

- `NganHangCauHoi`
- `DapAnCauHoi`
- `BaiKiemTra`
- `ChiTietBaiKiemTra`
- `BaiLamBaiKiemTra`
- `ChiTietBaiLamBaiKiemTra`
- `BaiLamViPhamGiamSat`
- `BaiLamSnapshotGiamSat`
- `KetQuaHocTap`

### 5.5. Vận hành hệ thống

- `DiemDanh`
- `DiemDanhGiangVien`
- `GiangVienDonXinNghi`
- `ThongBao`
- `SystemSetting`
- `Banner`

---

## 6. Luồng nghiệp vụ tổng thể đầu-cuối

Đây là phiên bản ngắn nhất nhưng đủ ý của toàn hệ thống:

1. khách vào trang chủ, tìm kiếm khóa học, đăng ký tài khoản
2. học viên đăng ký xong dùng ngay; giảng viên đăng ký xong đi vào hàng chờ duyệt
3. admin tạo `nhom_nganh`
4. admin tạo `khoa_hoc` mẫu và `module_hoc`
5. admin mở lớp hoạt động từ khóa mẫu
6. admin phân công giảng viên cho từng module
7. giảng viên xác nhận assignment
8. admin lập lịch học cho lớp
9. giảng viên vận hành buổi học, điểm danh, cập nhật link, xin nghỉ nếu cần
10. giảng viên chuẩn bị tài nguyên, bài giảng, live room, bài kiểm tra
11. admin duyệt nội dung và phát hành đề thi
12. học viên tham gia lớp, học bài, vào live room, làm bài kiểm tra
13. hệ thống tổng hợp điểm danh và điểm thi vào `ket_qua_hoc_tap`

Nếu cần nhìn dự án như một câu:

```text
Admin thiết kế và vận hành lớp học
-> giảng viên nhận phân công, dạy học, tạo nội dung và đánh giá
-> học viên ghi danh, học theo lộ trình, làm bài và nhận kết quả tổng hợp
```

---

## 7. Luồng chi tiết theo vai trò

## 7.1. Public, auth và định tuyến theo vai trò

### Trang chủ và tìm kiếm

Nguồn chính:

- `HomeController`

Trang chủ lấy:

- banner đang bật
- khóa học `hoat_dong`
- chỉ các khóa có `trang_thai_van_hanh` thuộc:
  - `cho_giang_vien`
  - `san_sang`
  - `dang_day`
- giảng viên nổi bật
- nhóm ngành có khóa public

Search hỗ trợ:

- `q`
- `level`
- `category`

### Đăng ký

Học viên:

- tạo `nguoi_dung`
- tạo hồ sơ `hoc_vien`
- đăng nhập ngay
- redirect về dashboard học viên

Giảng viên:

- không tạo tài khoản active ngay
- lưu vào `tai_khoan_cho_phe_duyet`
- chờ admin duyệt

### Đăng nhập

Flow:

- tìm user theo email
- check password hash
- check `trang_thai`
- redirect theo `vai_tro`

Redirect sau login:

- `admin -> /admin/dashboard`
- `giang_vien -> /giang-vien/dashboard`
- `hoc_vien -> /hoc-vien/dashboard`

---

## 7.2. Luồng nghiệp vụ của admin

### Dashboard, banner và settings

Admin có thể:

- xem dashboard
- quản lý banner
- quản lý thông tin liên hệ
- quản lý mạng xã hội
- quản lý cấu hình hiển thị giảng viên

### Quản lý tài khoản và phê duyệt

Admin có thể:

- CRUD tài khoản
- bật/tắt trạng thái
- xem riêng danh sách giảng viên
- xem riêng danh sách học viên
- duyệt đăng ký giảng viên mới

Phân biệt quan trọng:

- học viên đăng ký là active ngay
- giảng viên đăng ký phải qua `phe-duyet-tai-khoan`

### Nhóm ngành, khóa học, module

Admin quản lý:

- `nhom_nganh`
- `khoa_hoc`
- `module_hoc`

`KhoaHoc` có 2 loại:

- `mau`
- `hoat_dong`

Flow chuẩn:

1. tạo khóa mẫu
2. khai báo module
3. mở lớp từ khóa mẫu để tạo khóa hoạt động thực tế

### Mở lớp từ khóa mẫu

Luồng:

1. admin chọn khóa mẫu
2. hệ thống clone sang khóa `hoat_dong`
3. clone toàn bộ module
4. có thể phân công giảng viên luôn
5. khóa mới đi qua vòng đời vận hành:
   - `cho_mo`
   - `cho_giang_vien`
   - `san_sang`
   - `dang_day`
   - `ket_thuc`

### Phân công giảng viên

Assignment được lưu ở:

- `phan_cong_module_giang_vien`

Trạng thái chính:

- `cho_xac_nhan`
- `da_nhan`
- `tu_choi`

Ý nghĩa:

- giảng viên chỉ được thao tác sâu khi assignment đã `da_nhan`

### Quản lý học viên trong khóa

Admin có thể:

- thêm học viên vào lớp
- cập nhật trạng thái ghi danh
- xóa học viên khỏi lớp

`HocVienKhoaHoc.trang_thai` thường gặp:

- `dang_hoc`
- `hoan_thanh`
- `ngung_hoc`

### Lịch học

Admin có thể:

- tạo lịch thủ công
- tạo lịch tự động
- sửa, xóa, xóa hàng loạt
- xóa lịch theo module
- cập nhật số buổi module

`LichHoc` là trục của nhiều phân hệ:

- tiến độ học tập
- điểm danh
- tài nguyên buổi học
- live room
- bài kiểm tra theo buổi

### Yêu cầu học viên và đơn xin nghỉ

Admin có thể:

- xử lý `yeu_cau_hoc_vien`
- duyệt hoặc từ chối `giang_vien_don_xin_nghi`

### Duyệt thư viện, bài giảng, bài kiểm tra

Admin là điểm khóa cuối cho:

- tài nguyên thư viện
- bài giảng
- bài kiểm tra online

---

## 7.3. Luồng nghiệp vụ của giảng viên

### Dashboard, profile và lịch giảng

Giảng viên có:

- dashboard riêng
- profile riêng
- màn hình `lich-giang`

### Xác nhận phân công

Giảng viên vào danh sách khóa được giao và có thể:

- nhận assignment
- từ chối assignment

Đây là bước gatekeeper cho các nghiệp vụ sau.

### Vận hành khóa và buổi học

Giảng viên có thể:

- xem khóa học phụ trách
- bắt đầu buổi học
- kết thúc buổi học
- cập nhật link online cho buổi học

### Điểm danh

Có 2 nhánh riêng:

- điểm danh học viên
- điểm danh giảng viên

Điểm danh học viên:

- chỉ lấy học viên đang ghi danh hợp lệ
- không default tất cả là có mặt
- trạng thái thường dùng:
  - `co_mat`
  - `vao_tre`
  - `vang_mat`
  - `co_phep`

Điểm danh giảng viên:

- check-in
- check-out
- bắt đầu
- kết thúc

### Đơn xin nghỉ

Giảng viên có thể:

- tạo đơn nghỉ
- gắn với lịch học cụ thể hoặc tạo theo ngày/tiết

### Thư viện tài nguyên và tài nguyên buổi học

Giảng viên có 2 cách làm việc với tài nguyên:

1. làm việc ở `thu-vien`
2. gắn tài nguyên trực tiếp vào `lich_hoc`

`TaiNguyenBuoiHoc` đang đóng 2 vai cùng lúc:

- thư viện dùng lại
- tài nguyên của riêng buổi học

Trạng thái duyệt:

- `nhap`
- `cho_duyet`
- `da_duyet`
- `can_chinh_sua`
- `tu_choi`

Trạng thái xử lý:

- `khong_ap_dung`
- `cho_xu_ly`
- `dang_xu_ly`
- `san_sang`
- `loi_xu_ly`

Phạm vi sử dụng:

- `ca_nhan`
- `khoa_hoc`
- `cong_khai`

### Bài giảng

Giảng viên có thể:

- tạo bài giảng
- sửa bài giảng
- gửi duyệt
- xóa bài giảng

`BaiGiang` có:

- trạng thái duyệt:
  - `nhap`
  - `cho_duyet`
  - `da_duyet`
  - `can_chinh_sua`
  - `tu_choi`
- trạng thái công bố:
  - `an`
  - `da_cong_bo`

Học viên chỉ thấy bài giảng khi:

- đã duyệt
- đã công bố
- đã tới `thoi_diem_mo` nếu có

### Live room

Giảng viên có thể:

- tạo room cho `lich_hoc`
- xem room
- start room
- join room
- leave room
- end room
- thêm hoặc xóa recording

`PhongHocLive` hỗ trợ:

- `internal`
- `zoom`
- `google_meet`

State chính:

- `chua_mo`
- `sap_dien_ra`
- `dang_dien_ra`
- `da_ket_thuc`
- `da_huy`

### Bài kiểm tra và chấm điểm

Giảng viên có thể:

- tạo đề
- chọn scope theo module/buổi/cuối khóa
- import câu hỏi vào ngân hàng từ màn hình đề
- gửi duyệt
- cấu hình giám sát
- chấm tự luận
- hậu kiểm giám sát

Rule cốt lõi:

- chỉ được cấu hình đề trong phạm vi assignment đã `da_nhan`

---

## 7.4. Luồng nghiệp vụ của học viên

### Dashboard và tiến độ

Nguồn chính:

- `StudentLearningDashboardService`

Dashboard tổng hợp:

- số khóa đang học
- số buổi hôm nay
- số buổi sắp tới
- tài liệu mới
- bài kiểm tra cần chú ý
- chuyên cần
- hoạt động gần đây
- tiến độ theo từng khóa

### Khóa học có thể tham gia và yêu cầu tham gia

Học viên có thể:

- xem `khoa-hoc-tham-gia`
- gửi yêu cầu xin tham gia lớp

Rule:

- một khóa chỉ gửi yêu cầu tham gia một lần khi đang chờ duyệt

### Khóa học của tôi

Học viên có thể:

- xem danh sách khóa đã ghi danh
- vào chi tiết khóa nếu trạng thái ghi danh còn hợp lệ

Rule test đã khóa:

- `ngung_hoc` không được vào như học viên active
- `hoan_thanh` vẫn được vào xem

### Chi tiết khóa, buổi học, bài giảng

Cây nội dung:

```text
Khóa học -> Module -> Buổi học -> Bài giảng / Tài nguyên / Bài kiểm tra / Live room
```

Học viên vào chi tiết buổi học sẽ thấy:

- tài nguyên hợp lệ cho học viên
- bài giảng đã duyệt và công bố
- bài kiểm tra đủ điều kiện hiển thị

### Vào live room

Điều kiện chính:

- live room thuộc bài giảng đã duyệt và công bố
- học viên có quyền trên khóa học
- tùy config, có thể phải chờ moderator start trước

### Làm bài kiểm tra online

Học viên có thể:

- xem danh sách bài thi
- xem chi tiết đề
- pre-check nếu có giám sát
- bắt đầu làm bài
- nộp bài
- gửi log giám sát và snapshot

Sau khi làm xong:

- trắc nghiệm được chấm tự động
- tự luận chờ giảng viên chấm
- kết quả tổng hợp được refresh

---

## 8. Các phân hệ và rule quan trọng cần nhớ

## 8.1. `KhoaHoc`

Phân loại:

- `loai = mau`
- `loai = hoat_dong`

Trạng thái vận hành:

- `cho_mo`
- `cho_giang_vien`
- `san_sang`
- `dang_day`
- `ket_thuc`

Trạng thái học tập suy diễn:

- `chua_bat_dau`
- `dang_hoc`
- `hoan_thanh`

Điểm quan trọng:

- khóa hoạt động public ngoài trang chủ là khóa đang active và ở một số trạng thái vận hành nhất định

## 8.2. `ModuleHoc`

Trạng thái học tập suy diễn:

- `chua_bat_dau`
- `dang_dien_ra`
- `hoan_thanh`

Logic dựa trên:

- các `lich_hoc` hợp lệ
- bỏ qua buổi bị `huy`

## 8.3. `LichHoc`

Là trung tâm nối:

- tiến độ
- điểm danh
- bài giảng
- live room
- bài kiểm tra theo buổi

Rule từ test và service:

- có chuẩn catalog tiết học
- có validate xung đột giảng viên
- trạng thái buổi học được đồng bộ theo thời gian bởi command `lich-hoc:sync-status`

## 8.4. Điểm danh

Điểm danh học viên:

- ảnh hưởng trực tiếp tới `ket_qua_hoc_tap`

Điểm danh giảng viên:

- là flow riêng
- phục vụ vận hành và thống kê giảng dạy

## 8.5. Tài nguyên và bài giảng

`TaiNguyenBuoiHoc` là mô hình dễ gây nhầm nhất vì dùng chung cho:

- thư viện
- tài nguyên của buổi học

Học viên chỉ thấy tài nguyên nếu qua scope `hienThiChoHocVien()`:

- `trang_thai_hien_thi` phù hợp
- `ngay_mo_hien_thi` đã tới hoặc null
- `trang_thai_duyet` là `da_duyet` hoặc null
- `trang_thai_xu_ly` là `khong_ap_dung` hoặc `san_sang`

`BaiGiang` lại là một nhánh riêng:

- có duyệt
- có công bố
- có thời điểm mở

## 8.6. Live room

Điều kiện học viên join:

- room đã duyệt
- room đã công bố
- đang đúng trạng thái timeline
- có thể yêu cầu moderator start trước tùy config

Live room không chỉ là một link meeting, mà còn có:

- trạng thái room
- participants
- recordings
- quan hệ với bài giảng

## 8.7. Question bank và bài kiểm tra

Question bank:

- hỗ trợ nhiều kiểu câu hỏi
- import nhiều định dạng file
- chống trùng nội dung theo normalize string

Bài kiểm tra:

- duyệt riêng
- phát hành riêng
- có random câu hỏi, random đáp án
- có giám sát, pre-check, snapshot, review
- có cả đề tự luận thuần không cần chọn câu hỏi

## 8.8. `KetQuaHocTap`

Là service-driven data, không phải bảng để sửa tay tùy ý.

Nguồn đầu vào chính:

- điểm danh
- kết quả bài kiểm tra
- phương thức đánh giá của khóa
- trọng số chuyên cần và kiểm tra

---

## 9. Trạng thái nghiệp vụ quan trọng

### 9.1. `HocVienKhoaHoc`

- `dang_hoc`
- `hoan_thanh`
- `ngung_hoc`

### 9.2. `PhanCongModuleGiangVien`

- `cho_xac_nhan`
- `da_nhan`
- `tu_choi`

### 9.3. `LichHoc`

- `cho`
- `dang_hoc`
- `hoan_thanh`
- `huy`

### 9.4. `GiangVienDonXinNghi`

- `cho_duyet`
- `da_duyet`
- `tu_choi`

### 9.5. `TaiNguyenBuoiHoc`

`trang_thai_duyet`:

- `nhap`
- `cho_duyet`
- `da_duyet`
- `can_chinh_sua`
- `tu_choi`

`trang_thai_xu_ly`:

- `khong_ap_dung`
- `cho_xu_ly`
- `dang_xu_ly`
- `san_sang`
- `loi_xu_ly`

### 9.6. `BaiGiang`

`trang_thai_duyet`:

- `nhap`
- `cho_duyet`
- `da_duyet`
- `can_chinh_sua`
- `tu_choi`

`trang_thai_cong_bo`:

- `an`
- `da_cong_bo`

### 9.7. `PhongHocLive`

- `chua_mo`
- `sap_dien_ra`
- `dang_dien_ra`
- `da_ket_thuc`
- `da_huy`

### 9.8. `NganHangCauHoi`

- `nhap`
- `san_sang`
- `tam_an`

### 9.9. `BaiKiemTra`

`trang_thai_duyet`:

- `nhap`
- `cho_duyet`
- `da_duyet`
- `tu_choi`

`trang_thai_phat_hanh`:

- `nhap`
- `phat_hanh`
- `dong`

`access_status_key`:

- `an`
- `sap_mo`
- `dang_mo`
- `da_dong`

### 9.10. `BaiLamBaiKiemTra`

`trang_thai`:

- `dang_lam`
- `da_nop`
- `cho_cham`
- `da_cham`

`trang_thai_cham`:

- `chua_cham`
- `cho_cham`
- `da_cham`

`trang_thai_giam_sat`:

- `khong_ap_dung`
- `binh_thuong`
- `can_xem_xet`
- `da_xac_nhan`
- `nghi_ngo`

---

## 10. Route entrypoint chính theo vai trò

### 10.1. Public

- `/`
- `/search`
- `/dang-ky`
- `/dang-nhap`

### 10.2. Admin

- `/admin/dashboard`
- `/admin/tai-khoan/*`
- `/admin/phe-duyet-tai-khoan/*`
- `/admin/banner/*`
- `/admin/nhom-nganh/*`
- `/admin/khoa-hoc/*`
- `/admin/module-hoc/*`
- `/admin/phan-cong/*`
- `/admin/yeu-cau-hoc-vien/*`
- `/admin/giang-vien-don-xin-nghi/*`
- `/admin/diem-danh`
- `/admin/thu-vien/*`
- `/admin/bai-giang-phe-duyet/*`
- `/admin/kiem-tra-online/cau-hoi/*`
- `/admin/kiem-tra-online/phe-duyet/*`
- `/admin/settings/*`

### 10.3. Giảng viên

- `/giang-vien/dashboard`
- `/giang-vien/profile`
- `/giang-vien/lich-giang`
- `/giang-vien/khoa-hoc`
- `/giang-vien/don-xin-nghi/*`
- `/giang-vien/buoi-hoc/{id}/bat-dau`
- `/giang-vien/buoi-hoc/{id}/ket-thuc`
- `/giang-vien/buoi-hoc/{id}/diem-danh`
- `/giang-vien/thu-vien/*`
- `/giang-vien/bai-giang/*`
- `/giang-vien/live-room/*`
- `/giang-vien/bai-kiem-tra/*`
- `/giang-vien/cham-diem/*`

### 10.4. Học viên

- `/hoc-vien/dashboard`
- `/hoc-vien/hoat-dong-tien-do`
- `/hoc-vien/ket-qua-hoc-tap`
- `/hoc-vien/khoa-hoc-cua-toi`
- `/hoc-vien/khoa-hoc-tham-gia`
- `/hoc-vien/khoa-hoc/{id}`
- `/hoc-vien/buoi-hoc/{id}`
- `/hoc-vien/bai-giang/{id}`
- `/hoc-vien/live-room/{id}`
- `/hoc-vien/bai-kiem-tra`
- `/hoc-vien/profile`

---

## 11. AI hiểu nhanh và các hiểu nhầm phổ biến

- `KhoaHoc` có 2 loại là `mau` và `hoat_dong`; đừng mặc định mọi khóa là lớp thật đang chạy.
- Quyền giảng viên không mặc định theo cả khóa, mà bám vào `phan_cong_module_giang_vien` và trạng thái `da_nhan`.
- Học viên không thấy mọi nội dung chỉ vì có tài khoản; phải có ghi danh hợp lệ và nội dung phải qua các cổng hiển thị.
- `TaiNguyenBuoiHoc`, `BaiGiang`, `BaiKiemTra` là 3 nhánh nghiệp vụ khác nhau; đừng gộp logic của chúng vào nhau.
- `LichHoc` là trục trung tâm nối nhiều phân hệ; sửa lịch có thể ảnh hưởng tiến độ, điểm danh, live room và bài kiểm tra.
- `KetQuaHocTap` là dữ liệu tổng hợp bởi service; không nên chỉnh tay nếu chưa hiểu luồng refresh.
- Question bank hỗ trợ nhiều kiểu câu hỏi, nhưng builder đề hiện tại chỉ thật sự dùng một tập con.
- Live room không chỉ là một URL; nó có state, participant và recording.
- Học viên `ngung_hoc` bị chặn ở nhiều màn, còn `hoan_thanh` vẫn có thể được xem lại một số nội dung.
- Feature test là nguồn xác nhận rule cực mạnh; khi không chắc, đọc test trước.

---

## 12. File nguồn sự thật nên đọc khi sửa từng phân hệ

### 12.1. Auth và public

- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/HomeController.php`
- `routes/web.php`

### 12.2. Admin

- `app/Http/Controllers/Admin/AdminController.php`
- `app/Http/Controllers/Admin/KhoaHocManagementController.php`
- `app/Http/Controllers/Admin/ModuleHocController.php`
- `app/Http/Controllers/Admin/LichHocController.php`
- `app/Http/Controllers/Admin/PhanCongController.php`
- `app/Http/Controllers/Admin/TeacherLeaveRequestController.php`
- `app/Http/Controllers/Admin/AttendanceController.php`
- `app/Http/Controllers/Admin/ThuVienController.php`
- `app/Http/Controllers/Admin/BaiGiangController.php`
- `app/Http/Controllers/Admin/NganHangCauHoiController.php`
- `app/Http/Controllers/Admin/BaiKiemTraPheDuyetController.php`

### 12.3. Giảng viên

- `app/Http/Controllers/GiangVien/PhanCongController.php`
- `app/Http/Controllers/GiangVien/TeacherScheduleController.php`
- `app/Http/Controllers/GiangVien/TeacherLeaveRequestController.php`
- `app/Http/Controllers/GiangVien/DiemDanhController.php`
- `app/Http/Controllers/GiangVien/TeacherAttendanceController.php`
- `app/Http/Controllers/GiangVien/TaiNguyenController.php`
- `app/Http/Controllers/GiangVien/BaiGiangController.php`
- `app/Http/Controllers/GiangVien/LiveRoomController.php`
- `app/Http/Controllers/GiangVien/BaiKiemTraController.php`

### 12.4. Học viên

- `app/Http/Controllers/HocVienController.php`
- `app/Http/Controllers/HocVien/BaiKiemTraController.php`
- `app/Http/Controllers/HocVien/LiveRoomController.php`
- `app/Services/StudentLearningDashboardService.php`
- `app/Services/StudentScheduleViewService.php`

### 12.5. Service lõi

- `app/Services/KetQuaHocTapService.php`
- `app/Services/BaiKiemTraScoringService.php`
- `app/Services/ExamConfigurationService.php`
- `app/Services/ExamQuestionSelectionService.php`
- `app/Services/ExamSurveillanceService.php`
- `app/Services/ExamPrecheckService.php`
- `app/Services/LiveLectureService.php`
- `app/Services/LiveRoomPlatformService.php`
- `app/Services/TeacherAttendanceService.php`

### 12.6. Model lõi

- `app/Models/KhoaHoc.php`
- `app/Models/ModuleHoc.php`
- `app/Models/LichHoc.php`
- `app/Models/HocVienKhoaHoc.php`
- `app/Models/TaiNguyenBuoiHoc.php`
- `app/Models/BaiGiang.php`
- `app/Models/PhongHocLive.php`
- `app/Models/NganHangCauHoi.php`
- `app/Models/BaiKiemTra.php`
- `app/Models/BaiLamBaiKiemTra.php`
- `app/Models/KetQuaHocTap.php`

### 12.7. Test nên đọc khi mơ hồ nghiệp vụ

- `tests/Feature/StudentLearningFlowTest.php`
- `tests/Feature/AuthAndStudentAccessTest.php`
- `tests/Feature/KhoaHocManagementTest.php`
- `tests/Feature/TeacherAvailabilitySchedulingTest.php`
- `tests/Feature/TeacherAttendanceFlowTest.php`
- `tests/Feature/TeacherContentAuthorizationTest.php`
- `tests/Feature/TeacherLibraryResourceTest.php`
- `tests/Feature/LiveRoomWorkflowTest.php`
- `tests/Feature/OnlineExamFlowTest.php`
- `tests/Feature/LearningLogicTest.php`
- `tests/Feature/QuestionBankImportFlowTest.php`
- `tests/Feature/QuestionDocumentImportFlowTest.php`

---

## 13. Checklist hiểu hệ thống trong 5 phút

1. Đây là LMS có 3 actor chính: `admin`, `giang_vien`, `hoc_vien`.
2. Học viên đăng ký xong dùng ngay; giảng viên phải chờ admin duyệt.
3. `KhoaHoc` có `mau` và `hoat_dong`.
4. Luồng chuẩn là tạo khóa mẫu rồi mở lớp hoạt động.
5. Quyền sâu của giảng viên đi theo assignment module đã `da_nhan`.
6. `LichHoc` là trung tâm của nhiều flow.
7. `TaiNguyenBuoiHoc` đang kiêm cả thư viện và tài nguyên buổi học.
8. `BaiGiang` phải qua duyệt và công bố; `PhongHocLive` là nhánh riêng gắn với bài giảng live.
9. `BaiKiemTra` có duyệt, phát hành, pre-check, auto grade, chấm tay và hậu kiểm.
10. `KetQuaHocTap` là nơi tổng hợp kết quả cuối cùng, được refresh bởi service.

---

## 14. Tóm tắt một đoạn

Đồ án này là một hệ thống đào tạo trực tuyến có vòng đời nghiệp vụ tương đối hoàn chỉnh: admin tạo cấu trúc đào tạo và vận hành lớp học, giảng viên nhận phân công để dạy học và đánh giá, học viên ghi danh để học bài, vào live room, làm bài kiểm tra và nhận kết quả tổng hợp. Nếu AI khác cần tiếp tục làm việc trên project, hãy đọc file này trước, sau đó nhảy sang đúng controller, service, model và test của phân hệ đang sửa.
