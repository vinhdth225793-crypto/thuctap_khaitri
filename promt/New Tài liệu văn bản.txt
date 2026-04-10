# HƯỚNG DẪN SỬ DỤNG VÀ BẢN ĐỒ NGHIỆP VỤ DỰ ÁN `thuctap_khaitri`

## 1. Mục tiêu của tài liệu này

Tài liệu này được viết để:

- Giúp người mới hoặc AI khác chỉ cần đọc 1 file là hiểu dự án từ tổng quan đến chi tiết.
- Mô tả đúng flow nghiệp vụ đang có trong code, không chỉ mô tả ý tưởng.
- Chỉ ra vai trò, luồng thao tác, trạng thái dữ liệu và quy tắc nghiệp vụ quan trọng.
- Làm tài liệu nền khi cần sửa code, debug, viết test, tạo tính năng mới hoặc giải thích đồ án.

Nếu AI khác cần tiếp tục làm việc trên project này, hãy ưu tiên xem file này trước, sau đó đối chiếu với:

- `routes/web.php`
- `app/Http/Controllers/*`
- `app/Services/*`
- `app/Models/*`
- `tests/Feature/*`
- `docs/*`

## 2. Dự án này là gì

Đây là một hệ thống quản lý đào tạo trực tuyến theo mô hình:

- Quản trị viên tạo cấu trúc đào tạo.
- Giảng viên được phân công phụ trách module và buổi học.
- Học viên tham gia khóa học, học tài nguyên, vào lớp live, làm bài kiểm tra.
- Hệ thống tổng hợp kết quả học tập dựa trên điểm danh và bài kiểm tra.

Về bản chất, đây không phải chỉ là website CRUD khóa học, mà là một LMS mini có các phân hệ:

- Quản lý người dùng và phân quyền.
- Quản lý nhóm ngành, khóa học, module.
- Mở lớp từ khóa học mẫu.
- Phân công giảng viên.
- Lập lịch học.
- Điểm danh giảng viên và học viên.
- Thư viện tài nguyên.
- Bài giảng thường và bài giảng live.
- Phòng học live.
- Ngân hàng câu hỏi.
- Bài kiểm tra online có giám sát.
- Chấm điểm và tổng hợp kết quả học tập.

## 3. Công nghệ và cấu trúc kỹ thuật

- Framework: Laravel 12
- PHP: `^8.2`
- Giao diện: Blade
- Test: PHPUnit qua `php artisan test`
- Phân quyền hiện tại xoay quanh 3 vai trò chính:
  - `admin`
  - `giang_vien`
  - `hoc_vien`

Các thư mục chính:

- `routes/web.php`: định nghĩa route toàn hệ thống.
- `app/Http/Controllers`: logic theo vai trò và theo phân hệ.
- `app/Services`: nơi chứa nghiệp vụ tổng hợp, tính tiến độ, lịch, điểm danh, live room, kết quả học tập.
- `app/Models`: mô hình dữ liệu.
- `resources/views`: giao diện Blade.
- `database/migrations`: cấu trúc database.
- `tests/Feature`: nơi thể hiện rất rõ các rule nghiệp vụ quan trọng.
- `docs`: một số ghi chú phân tích nghiệp vụ và schema.
- `promt/huong dan su dung.md`: file tài liệu tổng hợp này.

## 4. Cách nhìn nhanh toàn hệ thống

### 4.1 Public và đăng nhập

- Người dùng chưa đăng nhập có thể vào trang chủ.
- Có thể tìm kiếm khóa học công khai.
- Học viên đăng ký xong có tài khoản dùng ngay.
- Giảng viên đăng ký xong chưa dùng ngay, phải chờ admin duyệt.

### 4.2 Vòng đời nghiệp vụ chính

Flow chuẩn của hệ thống:

1. Admin tạo nhóm ngành.
2. Admin tạo khóa học mẫu.
3. Admin tạo module thuộc khóa học mẫu.
4. Admin mở lớp từ khóa học mẫu để tạo khóa học đang vận hành.
5. Admin phân công giảng viên cho từng module.
6. Giảng viên xác nhận hoặc từ chối phân công.
7. Khi khóa học đủ điều kiện, admin lập lịch học.
8. Giảng viên chuẩn bị tài nguyên, bài giảng, live room, bài kiểm tra.
9. Admin duyệt và công bố các nội dung cần duyệt.
10. Học viên xin tham gia hoặc được thêm vào lớp.
11. Học viên học theo buổi, xem tài nguyên, vào lớp live, làm bài kiểm tra.
12. Hệ thống tổng hợp điểm danh, điểm kiểm tra và cập nhật kết quả học tập.
13. Khi đủ điều kiện hoàn thành, học viên chuyển sang trạng thái hoàn thành khóa học.

## 5. Vai trò và quyền chính

### 5.1 Khách chưa đăng nhập

- Xem trang chủ.
- Tìm kiếm khóa học đang mở cho public.
- Đăng ký.
- Đăng nhập.

### 5.2 Admin

Admin là vai trò kiểm soát toàn bộ vận hành, gồm:

- Dashboard và thống kê.
- Quản lý tài khoản.
- Duyệt tài khoản giảng viên đăng ký mới.
- Quản lý banner và cấu hình giao diện.
- Quản lý nhóm ngành.
- Quản lý khóa học và module.
- Mở lớp từ mẫu.
- Phân công giảng viên.
- Quản lý học viên trong khóa học.
- Lập và chỉnh lịch học.
- Xử lý yêu cầu học viên.
- Duyệt đơn xin nghỉ giảng viên.
- Theo dõi điểm danh.
- Duyệt thư viện tài nguyên.
- Duyệt bài giảng.
- Quản lý ngân hàng câu hỏi.
- Duyệt và phát hành bài kiểm tra.

### 5.3 Giảng viên

Giảng viên phụ trách vận hành học thuật ở phần mình được phân công:

- Xem dashboard, hồ sơ, lịch giảng.
- Xác nhận hoặc từ chối phân công.
- Xem khóa học và module mình phụ trách.
- Bắt đầu và kết thúc buổi học.
- Cập nhật link học online.
- Điểm danh học viên.
- Check-in, check-out điểm danh giảng viên.
- Tạo đơn xin nghỉ.
- Quản lý thư viện tài nguyên cá nhân.
- Đăng tài nguyên cho buổi học.
- Tạo bài giảng.
- Tạo và vận hành phòng học live.
- Tạo bài kiểm tra.
- Import câu hỏi.
- Chấm tự luận.
- Gửi yêu cầu thêm, xóa, sửa học viên trong khóa học.

### 5.4 Học viên

Học viên là người học, có thể:

- Xem dashboard học tập.
- Xem hoạt động và tiến độ.
- Xem kết quả học tập.
- Xem các khóa học của mình.
- Xem khóa học có thể tham gia.
- Gửi yêu cầu xin tham gia lớp.
- Xem chi tiết khóa học, module, buổi học.
- Xem bài giảng.
- Vào phòng học live.
- Làm bài kiểm tra online.
- Thực hiện pre-check giám sát nếu bài thi yêu cầu.
- Cập nhật hồ sơ cá nhân.

## 6. Các thực thể dữ liệu quan trọng

Đây là các model và bảng nghiệp vụ mà AI nên nắm đầu tiên.

### 6.1 Người dùng và hồ sơ

- `NguoiDung`: tài khoản đăng nhập chung.
- `HocVien`: hồ sơ học viên.
- `GiangVien`: hồ sơ giảng viên.
- `TaiKhoanChoPheDuyet`: nơi lưu đăng ký giảng viên chờ admin duyệt.

### 6.2 Đào tạo

- `NhomNganh`: nhóm ngành hoặc nhóm chuyên môn.
- `KhoaHoc`: khóa học.
- `ModuleHoc`: module bên trong khóa học.
- `LichHoc`: từng buổi học cụ thể.
- `HocVienKhoaHoc`: liên kết học viên với khóa học.
- `YeuCauHocVien`: yêu cầu liên quan đến học viên.
- `PhanCongModuleGiangVien` hoặc bảng tương ứng `phan_cong_module_giang_vien`: phân công giảng viên cho module.

### 6.3 Nội dung học tập

- `TaiNguyenBuoiHoc`: dùng cho cả tài nguyên thư viện và tài nguyên gắn với buổi học.
- `BaiGiang`: bài giảng thường hoặc bài giảng live.
- `PhongHocLive`: phòng học live và phiên học live.

### 6.4 Kiểm tra và đánh giá

- `NganHangCauHoi`: ngân hàng câu hỏi.
- `BaiKiemTra`: đề kiểm tra.
- Các bảng bài làm, đáp án, log giám sát, snapshot.
- `KetQuaHocTap` và các bảng kết quả liên quan: tổng hợp tiến độ, điểm module, điểm khóa.

## 7. Trạng thái nghiệp vụ quan trọng

Đây là phần rất quan trọng. Nhiều bug trong dự án sẽ liên quan trực tiếp đến việc cập nhật sai trạng thái.

### 7.1 Trạng thái khóa học

`KhoaHoc` có các trạng thái vận hành chính:

- `cho_mo`
- `cho_giang_vien`
- `san_sang`
- `dang_day`
- `ket_thuc`

Ý nghĩa:

- `cho_mo`: mới tạo hoặc chưa đủ điều kiện mở.
- `cho_giang_vien`: đã mở lớp nhưng còn chờ giảng viên xác nhận.
- `san_sang`: đã đủ điều kiện để admin xác nhận mở lớp chính thức.
- `dang_day`: đang vận hành giảng dạy.
- `ket_thuc`: khóa học đã hoàn tất theo tiến độ thực tế.

Ngoài ra còn có phân loại:

- `loai = mau`: khóa học mẫu.
- `loai = hoat_dong`: khóa học đang vận hành thực tế.

### 7.2 Trạng thái module và tiến độ học

Trạng thái tiến độ của module và khóa học không chỉ do CRUD, mà còn được suy ra từ lịch học:

- Module:
  - `chua_bat_dau`
  - `dang_dien_ra`
  - `hoan_thanh`
- Khóa học:
  - `chua_bat_dau`
  - `dang_hoc`
  - `hoan_thanh`

Hệ thống có logic tự đồng bộ tiến độ khi:

- Tạo lịch học.
- Sửa lịch học.
- Xóa lịch học.
- Buổi học được đánh dấu hoàn thành.

### 7.3 Trạng thái phân công giảng viên

- `cho_xac_nhan`
- `da_nhan`
- `tu_choi`

Rule quan trọng:

- Chỉ khi giảng viên đã `da_nhan` thì mới được thao tác nghiệp vụ giảng dạy sâu hơn.
- Khi các module của lớp đủ điều kiện và giảng viên xác nhận hợp lệ, khóa có thể chuyển sang `san_sang`.

### 7.4 Trạng thái ghi danh học viên vào khóa

`HocVienKhoaHoc` dùng ít nhất các trạng thái thực tế:

- `dang_hoc`
- `hoan_thanh`

Ngoài ra hệ thống cũng có các tình huống học viên bị dừng hoặc không còn hợp lệ, và test đã bảo vệ:

- Học viên không còn trạng thái học hợp lệ sẽ không được xem các màn nội dung học tập như bình thường.

### 7.5 Trạng thái yêu cầu học viên

`YeuCauHocVien`:

- `cho_duyet`
- `da_duyet`
- `tu_choi`

Loại yêu cầu có thể gồm:

- Học viên tự xin vào lớp.
- Giảng viên gửi yêu cầu thêm học viên.
- Giảng viên gửi yêu cầu xóa học viên.
- Giảng viên gửi yêu cầu sửa thông tin ghi chú học viên.

### 7.6 Trạng thái đơn xin nghỉ giảng viên

- `cho_duyet`
- `da_duyet`
- `tu_choi`

### 7.7 Trạng thái tài nguyên thư viện

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

### 7.8 Trạng thái bài giảng

Trạng thái duyệt:

- `nhap`
- `cho_duyet`
- `da_duyet`
- `can_chinh_sua`
- `tu_choi`

Trạng thái công bố:

- `an`
- `da_cong_bo`

Học viên chỉ thấy bài giảng khi:

- Đã được duyệt.
- Đã công bố.
- Đến thời điểm mở nếu có đặt `thoi_diem_mo`.

### 7.9 Trạng thái phòng live

Nền tảng:

- `internal`
- `zoom`
- `google_meet`

Trạng thái phòng:

- `chua_mo`
- `sap_dien_ra`
- `dang_dien_ra`
- `da_ket_thuc`
- `da_huy`

### 7.10 Trạng thái bài kiểm tra

Trạng thái duyệt:

- `nhap`
- `cho_duyet`
- `da_duyet`
- `tu_choi`

Trạng thái phát hành:

- `nhap`
- `phat_hanh`
- `dong`

Trạng thái truy cập suy diễn cho học viên:

- `an`
- `sap_mo`
- `dang_mo`
- `da_dong`

### 7.11 Trạng thái điểm danh

Điểm danh giảng viên:

- `chua_diem_danh`
- `da_checkin`
- `da_checkout`
- `hoan_thanh`

Điểm danh học viên:

- `co_mat`
- `vao_tre`
- `vang_mat`
- `co_phep`

## 8. Luồng public, đăng ký, đăng nhập

### 8.1 Trang chủ

Trang chủ lấy dữ liệu từ `HomeController`.

Chức năng chính:

- Hiển thị banner.
- Hiển thị khóa học nổi bật.
- Hiển thị giảng viên nổi bật.
- Hiển thị nhóm ngành.
- Hiển thị thống kê tổng quan.

Khóa học xuất hiện ngoài public phải là khóa học đang hoạt động hợp lệ, không phải khóa học mẫu.

### 8.2 Tìm kiếm khóa học

Search hỗ trợ:

- Từ khóa.
- Trình độ.
- Nhóm ngành.

### 8.3 Đăng ký học viên

Flow:

1. Người dùng chọn đăng ký học viên.
2. Hệ thống tạo `NguoiDung`.
3. Hệ thống tạo hồ sơ `HocVien`.
4. Hệ thống đăng nhập ngay.
5. Điều hướng đến dashboard học viên.

### 8.4 Đăng ký giảng viên

Flow:

1. Người dùng chọn đăng ký giảng viên.
2. Hệ thống chưa tạo tài khoản active ngay.
3. Dữ liệu được lưu ở `TaiKhoanChoPheDuyet`.
4. Admin vào màn duyệt tài khoản để duyệt hoặc từ chối.
5. Nếu được duyệt thì tài khoản giảng viên mới được sinh và kích hoạt.

### 8.5 Đăng nhập

Đăng nhập kiểm tra:

- Tài khoản có tồn tại không.
- Mật khẩu có đúng không.
- Tài khoản có bị vô hiệu hóa không.

Sau khi đăng nhập, hệ thống điều hướng theo vai trò:

- Admin về khu admin.
- Giảng viên về khu giảng viên.
- Học viên về khu học viên.

## 9. Luồng nghiệp vụ chi tiết của admin

### 9.1 Dashboard admin

Admin có dashboard tổng quan để xem:

- Tình hình người dùng.
- Tình hình giảng viên.
- Tình hình học viên.
- Các thành phần quản trị khác tùy giao diện.

### 9.2 Quản lý tài khoản

Admin có thể:

- Xem danh sách tài khoản.
- Tạo tài khoản.
- Xem chi tiết.
- Sửa.
- Bật hoặc tắt trạng thái.
- Xóa.

Ngoài tài khoản chung, admin còn có các màn:

- Danh sách học viên.
- Danh sách giảng viên.
- Lịch giảng của một giảng viên cụ thể.

### 9.3 Duyệt tài khoản giảng viên

Route nhóm `admin.phe-duyet-tai-khoan.*`.

Admin có thể:

- Xem danh sách đăng ký chờ duyệt.
- Duyệt.
- Từ chối.
- Hoàn tác duyệt trong một số tình huống.

Đây là điểm phân biệt quan trọng:

- Học viên đăng ký là dùng ngay.
- Giảng viên đăng ký phải qua duyệt.

### 9.4 Quản lý banner và settings

Admin có các màn:

- Quản lý banner.
- Quản lý thông tin liên hệ.
- Quản lý mạng xã hội.
- Cấu hình hiển thị giảng viên.
- Một số settings tổng quan khác.

### 9.5 Quản lý nhóm ngành

Admin CRUD `NhomNganh`:

- Tạo.
- Sửa.
- Xóa.
- Bật tắt trạng thái.

Nhóm ngành được dùng để phân loại khóa học.

### 9.6 Quản lý khóa học

Đây là một trong những nghiệp vụ trung tâm nhất.

Admin có thể:

- Xem danh sách khóa học.
- Tạo khóa học.
- Sửa khóa học.
- Xóa khóa học.
- Bật tắt trạng thái.
- Kích hoạt mẫu.
- Mở lớp từ mẫu.
- Xác nhận mở lớp.

### 9.7 Tư duy đúng về khóa học

Hệ thống phân biệt rõ:

- Khóa học mẫu: dùng như template.
- Khóa học hoạt động: lớp đang vận hành thật.

Flow chuẩn:

1. Admin tạo khóa học mẫu.
2. Admin tạo module cho khóa học mẫu.
3. Khi muốn triển khai thực tế, admin dùng chức năng mở lớp.
4. Hệ thống clone khóa học mẫu và module sang bản hoạt động.
5. Admin tiếp tục phân công giảng viên, xếp lịch, đưa học viên vào.

Rule rất quan trọng:

- Không nên hiểu mọi khóa học đều là lớp đang học thật.
- Nhiều thao tác chỉ có ý nghĩa trên khóa học hoạt động.

### 9.8 Mở lớp từ khóa học mẫu

Khi mở lớp:

- Hệ thống sao chép dữ liệu khóa học mẫu.
- Hệ thống sao chép module.
- Có thể đồng thời tạo phân công giảng viên ở trạng thái chờ xác nhận.

Sau mở lớp:

- Nếu đã có giảng viên được gán, khóa có thể vào trạng thái `cho_giang_vien`.
- Khi đủ điều kiện hơn nữa, khóa có thể chuyển sang `san_sang`.

### 9.9 Xác nhận mở lớp

Admin chỉ xác nhận mở lớp khi khóa đang ở trạng thái phù hợp, điển hình là `san_sang`.

Khi xác nhận:

- Khóa học chuyển sang vận hành thực tế `dang_day`.

### 9.10 Quản lý module

Admin CRUD module:

- Tạo.
- Sửa.
- Xóa.
- Bật tắt trạng thái.

Mỗi module gắn với khóa học.

Module còn là đơn vị để:

- Phân công giảng viên.
- Gắn lịch học.
- Xây bài kiểm tra theo module.

### 9.11 Phân công giảng viên

Admin phân công giảng viên theo module.

Admin có thể:

- Gán giảng viên vào module.
- Hủy phân công.
- Thay thế giảng viên.

Giảng viên sau đó phải phản hồi:

- Nhận.
- Từ chối.

Rule quan trọng:

- Giảng viên chưa nhận phân công thì chưa được xem là chủ thể hợp lệ để tạo bài giảng, chấm thi hoặc thao tác học thuật sâu.

### 9.12 Quản lý học viên trong khóa học

Admin có thể thao tác trực tiếp học viên của từng khóa:

- Xem danh sách.
- Tìm kiếm.
- Thêm.
- Cập nhật.
- Xóa.

### 9.13 Quản lý yêu cầu học viên

Admin có màn xử lý `YeuCauHocVien`.

Nguồn yêu cầu có thể là:

- Học viên xin vào lớp.
- Giảng viên xin thêm học viên.
- Giảng viên xin xóa học viên.
- Giảng viên xin sửa thông tin.

Khi admin duyệt:

- Duyệt thêm học viên: tạo hoặc cập nhật `HocVienKhoaHoc`.
- Duyệt xóa học viên: bỏ liên kết học viên khỏi khóa học.
- Duyệt sửa: cập nhật thông tin liên quan.

Lưu ý:

- Trong code hiện tại đã có route xử lý xác nhận yêu cầu học viên, không phải tính năng dang dở.

### 9.14 Quản lý lịch học

Admin quản lý lịch học theo từng khóa hoạt động.

Chức năng:

- Xem danh sách lịch.
- Tạo lịch thủ công.
- Tạo lịch tự động.
- Sửa lịch.
- Xóa một lịch.
- Xóa hàng loạt.
- Xóa lịch theo module.
- Cập nhật số buổi cho module.
- Lấy context giảng viên để tránh xung đột.

Rule nghiệp vụ mạnh:

- Có kiểm tra khung giờ dạy hợp lệ.
- Có kiểm tra xung đột lịch cho giảng viên.
- Có chuẩn hóa tiết học và giờ học.
- Có bảo vệ route để không sửa nhầm lịch của khóa khác.

### 9.15 Điểm danh

Admin có màn báo cáo điểm danh:

- Tab giảng viên.
- Tab học viên.

Admin có thể xem:

- Báo cáo điểm danh giảng viên theo buổi.
- Tình hình điểm danh học viên.

### 9.16 Duyệt đơn xin nghỉ giảng viên

Admin xem danh sách đơn xin nghỉ, chi tiết đơn và:

- Duyệt.
- Từ chối.

Rule cần nhớ:

- Duyệt đơn nghỉ không tự động xếp lịch lại.
- Việc đổi lịch hoặc thay giảng viên vẫn là nghiệp vụ vận hành tiếp theo.

### 9.17 Duyệt thư viện tài nguyên

Admin có thể:

- Xem tài nguyên giảng viên gửi duyệt.
- Xem chi tiết.
- Duyệt.
- Xóa.

### 9.18 Duyệt bài giảng

Admin có thể:

- Xem danh sách bài giảng chờ duyệt hoặc đã tạo.
- Tạo bài giảng trực tiếp nếu cần.
- Sửa.
- Duyệt.
- Công bố.

Vì vậy trong hệ thống, admin không chỉ duyệt mà còn có thể tạo nội dung chủ động.

### 9.19 Quản lý ngân hàng câu hỏi

Admin có thể:

- Tạo câu hỏi.
- Sửa.
- Xóa.
- Ẩn hoặc hiện.
- Bật tắt khả năng tái sử dụng.
- Import từ file.
- Xem preview import.
- Xác nhận import.
- Tải template import.

### 9.20 Duyệt bài kiểm tra

Admin quản lý vòng đời phát hành bài kiểm tra:

- Xem danh sách chờ duyệt.
- Xem chi tiết.
- Xem chi tiết bài làm.
- Xem và cập nhật trạng thái giám sát bài làm khi cần.
- Duyệt.
- Từ chối.
- Phát hành.
- Đóng bài kiểm tra.

## 10. Luồng nghiệp vụ chi tiết của giảng viên

### 10.1 Dashboard, profile và lịch giảng

Giảng viên có:

- Dashboard.
- Hồ sơ cá nhân.
- Lịch giảng.

Root `/giang-vien` được điều hướng vào khu chức năng giảng viên, trong đó trọng tâm là các khóa được phân công.

### 10.2 Xác nhận phân công

Sau khi admin phân công, giảng viên vào danh sách khóa học hoặc phân công để phản hồi.

Giảng viên có thể:

- Nhận phân công.
- Từ chối phân công.

Khi giảng viên nhận phân công:

- Hệ thống cập nhật trạng thái phân công.
- Nếu các điều kiện toàn khóa đã đủ, khóa có thể được đẩy sang `san_sang`.

### 10.3 Xem khóa học mình phụ trách

Giảng viên vào:

- Danh sách khóa học.
- Chi tiết từng khóa.
- Kết quả học tập của học viên trong phạm vi mình được phép.

Chi tiết khóa giảng viên thường là màn trung tâm để thao tác:

- Theo dõi timeline lịch.
- Mở buổi học.
- Tạo tài nguyên.
- Tạo live room.
- Tạo bài giảng.
- Tạo bài kiểm tra.

### 10.4 Điều hành buổi học

Giảng viên có thể:

- Bắt đầu buổi học.
- Kết thúc buổi học.
- Cập nhật link online của buổi.

Với buổi học online, hệ thống có quy định thời điểm được join sớm.

### 10.5 Điểm danh học viên

Tại từng buổi học, giảng viên:

- Mở màn điểm danh.
- Ghi nhận trạng thái từng học viên.
- Gửi báo cáo điểm danh.

Rule quan trọng:

- Chỉ học viên đang học hợp lệ mới được điểm danh.
- Học viên đã dừng hoặc không còn hợp lệ không được chấm như bình thường.

### 10.6 Điểm danh giảng viên

Ngoài điểm danh học viên, giảng viên còn có điểm danh riêng cho mình:

- Check-in.
- Check-out.
- Bắt đầu.
- Kết thúc.

Logic này được bảo vệ bởi `TeacherAttendanceService`.

Service kiểm tra:

- Giảng viên có thực sự phụ trách buổi đó không.
- Buổi đã hoàn thành hoặc hủy thì không được check-in bừa.
- Không được check-out trước check-in.
- Có thể đồng bộ từ dữ liệu live room nội bộ nếu có.

### 10.7 Gửi yêu cầu học viên

Giảng viên có thể gửi yêu cầu lên admin để:

- Thêm học viên vào khóa.
- Xóa học viên khỏi khóa.
- Sửa thông tin liên quan đến học viên.

Đây không phải thao tác tự phê duyệt.

### 10.8 Đơn xin nghỉ

Giảng viên có thể tạo đơn xin nghỉ:

- Từ một buổi học thực tế đã có lịch.
- Hoặc nhập tay theo ngày và tiết.

Rule:

- Hệ thống chặn đơn trùng thời gian đã xin trước đó.
- Sau khi admin duyệt, vẫn cần vận hành lại lịch nếu buổi bị ảnh hưởng.

### 10.9 Thư viện tài nguyên

Giảng viên có khu `thu-vien` để quản lý tài nguyên cá nhân.

Các loại tài nguyên hỗ trợ gồm:

- `video`
- `pdf`
- `word`
- `powerpoint`
- `excel`
- `image`
- `archive`
- `link_ngoai`
- `tai_lieu_khac`

Giảng viên có thể:

- Tạo mới.
- Sửa.
- Xóa.
- Gửi duyệt.

Rule quan trọng:

- Nếu tài nguyên đã được duyệt mà thay file hoặc đổi loại hoặc đổi phạm vi quan trọng thì trạng thái duyệt có thể bị reset.
- Nếu chỉ sửa metadata không làm đổi bản chất thì có thể giữ trạng thái cũ.
- Với video, code hiện tại đang đánh dấu sẵn sàng ngay, chưa có pipeline xử lý video bất đồng bộ thực sự.

### 10.10 Tài nguyên buổi học

Ngoài thư viện cá nhân, giảng viên còn có thể gắn tài nguyên trực tiếp cho buổi học:

- Upload hoặc gắn tài nguyên.
- Sửa.
- Bật tắt hiển thị.
- Xóa.

Đây là phần phục vụ học viên xem theo từng buổi.

### 10.11 Bài giảng

Giảng viên có thể:

- Tạo bài giảng.
- Sửa bài giảng.
- Gửi duyệt.
- Xóa bài giảng nháp.

Bài giảng có thể:

- Gắn với khóa học.
- Gắn với module.
- Gắn với buổi học cụ thể.
- Gắn với tài nguyên thư viện đã duyệt.

Rule rất quan trọng:

- Giảng viên chỉ được tạo bài giảng trong phạm vi phân công đã nhận.
- Không được gắn tài nguyên thư viện của người khác nếu không hợp lệ.
- Học viên chỉ xem được khi bài giảng đã được admin duyệt và công bố.

### 10.12 Phòng học live

Giảng viên có thể:

- Tạo phòng live cho lịch học.
- Xem phòng của buổi.
- Bắt đầu phòng.
- Tham gia phòng.
- Rời phòng.
- Kết thúc phòng.
- Quản lý recording.

Nếu dùng phòng nội bộ:

- Việc start room có thể đồng bộ trạng thái buổi học sang `dang_hoc`.
- Có thể tự động check-in điểm danh giảng viên.

Khi end room:

- Có thể đồng bộ buổi sang `hoan_thanh`.
- Có thể tự động check-out hoặc hoàn tất điểm danh giảng viên.

### 10.13 Bài kiểm tra

Giảng viên có thể:

- Tạo bài kiểm tra.
- Sửa cấu hình.
- Sửa giám sát.
- Import câu hỏi vào bài kiểm tra.
- Gửi duyệt.
- Xóa khi chưa phù hợp.
- Chấm điểm tự luận.
- Xem log giám sát và cập nhật trạng thái review cho bài làm.

Các phạm vi bài kiểm tra:

- Theo module.
- Theo buổi học.
- Cuối khóa.

Loại nội dung:

- Trắc nghiệm.
- Tự luận.
- Hỗn hợp.

Rule nghiệp vụ:

- Giảng viên chỉ được thao tác với bài thi thuộc phạm vi mình có phân công hợp lệ.
- Nếu có bài làm đang active thì một số cấu hình giám sát không được sửa tùy tiện.
- Gửi duyệt chỉ thành công khi đề thi đủ cấu hình theo `ExamConfigurationService`.

### 10.14 Chấm điểm tự luận

Giảng viên vào khu chấm điểm để:

- Xem danh sách cần chấm.
- Xem từng bài làm.
- Nhập điểm tự luận.
- Ghi nhận review giám sát nếu cần.

Sau khi chấm xong:

- Hệ thống cập nhật tổng hợp kết quả học tập qua service.

## 11. Luồng nghiệp vụ chi tiết của học viên

### 11.1 Dashboard học viên

Dashboard học viên tổng hợp từ `StudentLearningDashboardService`.

Nội dung có thể bao gồm:

- Khóa học đang tham gia.
- Buổi học hôm nay và sắp tới.
- Tài nguyên công khai có thể xem.
- Bài kiểm tra đang mở hoặc sắp mở.
- Tỷ lệ điểm danh.
- Hoạt động gần đây.
- Tiến độ theo từng khóa.

### 11.2 Xem hoạt động và tiến độ

Học viên có màn theo dõi:

- Tiến độ học.
- Hoạt động học gần đây.
- Các mốc liên quan đến bài học, tài nguyên, bài kiểm tra.

### 11.3 Kết quả học tập

Học viên xem:

- Kết quả tổng quan theo khóa.
- Kết quả theo module.
- Kết quả theo bài kiểm tra.

### 11.4 Xem khóa học của tôi

Học viên có màn `khoa-hoc-cua-toi`.

Màn này hiển thị các khóa mà học viên đã được ghi danh hợp lệ.

### 11.5 Xem khóa học có thể tham gia

Học viên có thể xem danh sách khóa có thể xin tham gia.

Sau đó:

- Gửi yêu cầu xin tham gia.

Rule:

- Không được spam gửi trùng một yêu cầu đang chờ duyệt.

### 11.6 Chi tiết khóa học

Khi đã tham gia hợp lệ, học viên có thể vào chi tiết khóa:

- Xem module.
- Xem lịch học.
- Xem tài nguyên nhìn thấy được.
- Xem bài giảng nhìn thấy được.
- Xem bài kiểm tra liên quan.

Service `StudentScheduleViewService` là nơi tổng hợp rất nhiều dữ liệu cho màn này.

### 11.7 Chi tiết buổi học

Học viên có thể xem:

- Thông tin buổi học.
- Buổi trước và buổi sau.
- Tài nguyên được mở.
- Bài giảng liên quan.
- Bài kiểm tra liên quan.
- Recording nếu có.

### 11.8 Xem bài giảng

Học viên chỉ xem được bài giảng khi đủ điều kiện:

- Thuộc khóa mình đang học hoặc đã hoàn thành.
- Bài giảng đã duyệt.
- Bài giảng đã công bố.
- Đến thời điểm được mở.

Nếu bài giảng là live lecture:

- Có thể được điều hướng sang màn live room.

### 11.9 Vào phòng học live

Học viên có thể:

- Xem phòng live.
- Join.
- Leave.

Rule:

- Phải thuộc khóa học liên quan với trạng thái hợp lệ.
- Nếu cấu hình yêu cầu moderator mở trước thì học viên chưa được vào cho đến khi giảng viên mở phòng.

### 11.10 Làm bài kiểm tra online

Học viên có thể:

- Xem danh sách bài kiểm tra được phép làm.
- Xem chi tiết bài kiểm tra.
- Nếu bài thi có giám sát thì làm pre-check.
- Bắt đầu làm bài.
- Nộp bài.
- Gửi log giám sát.
- Gửi snapshot giám sát.

Học viên chỉ thấy bài kiểm tra khi:

- Bài thi đang active.
- Đã được duyệt.
- Đã phát hành.
- Thuộc khóa mình đang học hoặc đã hoàn thành.

### 11.11 Pre-check giám sát

Nếu bài thi bật giám sát, học viên phải qua pre-check:

- Trình duyệt.
- Camera.
- Fullscreen.
- Các điều kiện kỹ thuật khác.

Chỉ khi pre-check đạt yêu cầu thì mới được bắt đầu.

### 11.12 Hồ sơ học viên

Học viên có thể cập nhật thông tin cá nhân ở màn profile.

## 12. Phân hệ lịch học và quy tắc vận hành

### 12.1 Cấu trúc lịch học

`LichHoc` là trung tâm của nhiều nghiệp vụ:

- Tiến độ học tập.
- Điểm danh.
- Tài nguyên buổi học.
- Live room.
- Bài kiểm tra theo buổi.

### 12.2 Quy tắc tiết học

Hệ thống có catalog tiết học chuẩn.

Khi lưu lịch:

- Chuẩn hóa `tiet_bat_dau`.
- Chuẩn hóa `tiet_ket_thuc`.
- Chuẩn hóa `buoi_hoc`.
- Chuẩn hóa `gio_bat_dau`.
- Chuẩn hóa `gio_ket_thuc`.

### 12.3 Tạo lịch tự động

Admin có thể tạo lịch tự động dựa trên:

- Số buổi.
- Ngày bắt đầu.
- Các thứ trong tuần.
- Context giảng viên để tránh xung đột.

### 12.4 Rule được test bảo vệ

- Lịch phải nằm trong khung giờ dạy chuẩn.
- Lịch có thể học cả Chủ nhật nếu config hợp lệ.
- Không được chồng lịch cho cùng giảng viên.
- Không được sửa hoặc xóa lịch của khóa khác bằng route sai context.

## 13. Phân hệ điểm danh

### 13.1 Điểm danh học viên

Điểm danh học viên gắn chặt với từng `LichHoc`.

Dữ liệu này tác động tới:

- Tỷ lệ tham gia.
- Điểm chuyên cần.
- Kết quả tổng hợp cuối khóa.

### 13.2 Điểm danh giảng viên

Điểm danh giảng viên là một flow riêng, không nên nhầm với điểm danh học viên.

Điểm danh giảng viên được dùng để:

- Theo dõi việc vào lớp.
- Đồng bộ với live room.
- Phục vụ dashboard điểm danh admin.

## 14. Phân hệ thư viện tài nguyên và bài giảng

### 14.1 Một bảng cho hai mục đích

`TaiNguyenBuoiHoc` hiện đang phục vụ cả:

- Thư viện tài nguyên cá nhân hoặc công khai.
- Tài nguyên gắn trực tiếp vào một buổi học.

Khi sửa logic phần này phải rất cẩn thận vì dễ ảnh hưởng chéo.

### 14.2 Điều kiện học viên được thấy tài nguyên

Thông thường tài nguyên được coi là nhìn thấy được khi:

- `trang_thai_hien_thi` là `hien` hoặc null.
- `ngay_mo_hien_thi` đã tới hoặc null.
- `trang_thai_duyet` là null hoặc `da_duyet`.
- `trang_thai_xu_ly` là null hoặc đã sẵn sàng phù hợp.

### 14.3 Bài giảng thường và live lecture

`BaiGiang` có thể là:

- Bài giảng thường.
- Bài giảng live, khi đó có liên hệ với `PhongHocLive`.

Điều kiện hiển thị với học viên vẫn xoay quanh:

- Được duyệt.
- Được công bố.
- Đến thời điểm mở.

## 15. Phân hệ live room

### 15.1 Mục đích

Phòng live là nơi giảng viên và học viên vào học trực tuyến theo lịch.

### 15.2 Các nền tảng

- Internal room của hệ thống.
- Zoom.
- Google Meet.

### 15.3 Rule quan trọng

- Không tạo room cho buổi học đã hoàn thành.
- Học viên chưa chắc được vào ngay nếu moderator chưa start.
- Start hoặc end room nội bộ có thể kéo theo thay đổi trạng thái lịch và điểm danh.

### 15.4 Recording

Giảng viên có thể:

- Thêm recording bằng link hoặc file.
- Xóa recording.

Recording sau đó có thể hiển thị ở chi tiết buổi học hoặc bài giảng live tùy flow.

## 16. Phân hệ ngân hàng câu hỏi và bài kiểm tra

### 16.1 Ngân hàng câu hỏi

`NganHangCauHoi` hỗ trợ:

- Câu hỏi trắc nghiệm.
- Câu hỏi tự luận.

Mode đáp án:

- Một đáp án đúng.
- Nhiều đáp án đúng.
- Đúng sai.

Tuy nhiên cần nhớ:

- Builder bài thi hiện tại thực tế tập trung vào tự luận, một đáp án đúng và đúng sai.
- Dạng nhiều đáp án đúng không phải là đường chính của UI builder hiện tại.

### 16.2 Chống trùng

Import và tạo câu hỏi có logic chuẩn hóa nội dung để chống trùng:

- Trim.
- Gộp khoảng trắng.
- Lowercase unicode.

### 16.3 Import câu hỏi

Flow:

1. Tải template.
2. Import file để preview.
3. Preview được lưu session.
4. Có thể export preview.
5. Xác nhận import.
6. Hệ thống kiểm tra lại trùng trước khi ghi thật.

### 16.4 Bài kiểm tra

`BaiKiemTra` hỗ trợ:

- Phạm vi theo module.
- Phạm vi theo buổi.
- Phạm vi cuối khóa.

Loại nội dung:

- `trac_nghiem`
- `tu_luan`
- `hon_hop`

Các tính năng quan trọng:

- Random câu hỏi.
- Random đáp án.
- Giới hạn số lần làm.
- Mở và đóng theo thời gian.
- Cấu hình giám sát.

### 16.5 Chấm điểm

Flow chấm điểm:

- Trắc nghiệm được chấm tự động khi nộp.
- Tự luận vào trạng thái chờ chấm.
- Giảng viên chấm tay.
- Sau khi chấm, kết quả tổng hợp được refresh.

### 16.6 Ngưỡng đạt

Theo logic hiện tại:

- Bài kiểm tra đạt khi điểm đạt ít nhất 50 phần trăm tổng điểm.

## 17. Phân hệ giám sát bài thi

### 17.1 Cấu hình giám sát có thể bật

Các field quan trọng:

- `co_giam_sat`
- `bat_buoc_fullscreen`
- `bat_buoc_camera`
- `so_lan_vi_pham_toi_da`
- `chu_ky_snapshot_giay`
- `tu_dong_nop_khi_vi_pham`
- `chan_copy_paste`
- `chan_chuot_phai`

### 17.2 Sự kiện giám sát

Hệ thống có log các event như:

- `tab_switch`
- `window_blur`
- `window_focus`
- `fullscreen_exit`
- `camera_off`
- `snapshot_captured`
- `snapshot_failed`
- `warning_issued`
- `auto_submit`
- `copy_paste_blocked`
- `right_click_blocked`

### 17.3 Review giám sát

Bài làm có thể bị đánh dấu cần xem xét nếu vi phạm.

Giảng viên hoặc admin có thể vào xem và cập nhật trạng thái review giám sát.

## 18. Phân hệ kết quả học tập

### 18.1 Service trung tâm

`KetQuaHocTapService` là service quan trọng nhất của phần đánh giá.

Nó có trách nhiệm:

- Refresh kết quả theo bài kiểm tra.
- Refresh kết quả theo module.
- Refresh kết quả theo khóa học.

### 18.2 Cách tính tổng quát

Kết quả khóa học kết hợp từ:

- Điểm danh.
- Điểm kiểm tra hoặc điểm module.

Khóa học có các trọng số như:

- `ty_trong_diem_danh`
- `ty_trong_kiem_tra`

### 18.3 Quy tắc nổi bật

- Lấy bài làm đã chấm đầy đủ tốt nhất cho một bài kiểm tra.
- Điểm danh được quy đổi thành điểm trên thang 10 theo tỷ lệ tham gia.
- Nếu kết quả khóa là đạt thì ghi danh học viên có thể được chuyển từ `dang_hoc` sang `hoan_thanh`.

## 19. Những rule nghiệp vụ mà AI khác rất dễ hiểu sai

### 19.1 Khóa học mẫu khác khóa học đang chạy

Đừng mặc định mọi `KhoaHoc` đều là lớp thật.

Phải xem:

- `loai = mau`
- hay `loai = hoat_dong`

### 19.2 Không phải giảng viên nào cũng có quyền trên mọi module của khóa

Quyền của giảng viên phụ thuộc vào:

- Có được phân công không.
- Phân công đã được nhận chưa.
- Module nào được phụ trách.

### 19.3 Học viên không phải cứ có tài khoản là xem được mọi nội dung

Quyền học viên phụ thuộc vào:

- Có được ghi danh vào khóa không.
- Trạng thái ghi danh còn hợp lệ không.
- Nội dung đã duyệt chưa.
- Nội dung đã công bố chưa.
- Đã đến thời điểm mở chưa.

### 19.4 Bài giảng, tài nguyên, bài kiểm tra là ba phân hệ khác nhau

Tuy liên quan đến học tập, nhưng đây là 3 dòng nghiệp vụ tách biệt:

- Tài nguyên phục vụ tải hoặc xem tài liệu.
- Bài giảng phục vụ cấu trúc nội dung học.
- Bài kiểm tra phục vụ đánh giá.

### 19.5 Live room không chỉ là link meeting

Với room nội bộ, nó còn ảnh hưởng:

- Trạng thái buổi học.
- Điểm danh giảng viên.
- Trải nghiệm join của học viên.

### 19.6 Tests là nguồn sự thật rất mạnh

Nếu gặp chỗ mơ hồ, hãy đọc test trước, đặc biệt:

- `tests/Feature/StudentLearningFlowTest.php`
- `tests/Feature/OnlineExamFlowTest.php`
- `tests/Feature/TeacherAvailabilitySchedulingTest.php`
- `tests/Feature/LiveRoomWorkflowTest.php`
- `tests/Feature/TeacherAttendanceFlowTest.php`
- `tests/Feature/TeacherContentAuthorizationTest.php`
- `tests/Feature/TeacherLibraryResourceTest.php`
- `tests/Feature/LearningLogicTest.php`

## 20. Route entrypoint chính theo vai trò

### 20.1 Public

- `/`
- `/search`
- `/dang-ky`
- `/dang-nhap`

### 20.2 Admin

Các cụm route chính:

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
- `/admin/thu-vien/*`
- `/admin/bai-giang-phe-duyet/*`
- `/admin/kiem-tra-online/cau-hoi/*`
- `/admin/kiem-tra-online/phe-duyet/*`
- `/admin/diem-danh`
- `/admin/settings/*`

### 20.3 Giảng viên

- `/giang-vien/dashboard`
- `/giang-vien/profile`
- `/giang-vien/lich-giang`
- `/giang-vien/khoa-hoc`
- `/giang-vien/buoi-hoc/{id}/bat-dau`
- `/giang-vien/buoi-hoc/{id}/ket-thuc`
- `/giang-vien/buoi-hoc/{id}/diem-danh`
- `/giang-vien/thu-vien/*`
- `/giang-vien/bai-giang/*`
- `/giang-vien/live-room/*`
- `/giang-vien/bai-kiem-tra/*`
- `/giang-vien/cham-diem/*`
- `/giang-vien/don-xin-nghi/*`

### 20.4 Học viên

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

## 21. Map file nên đọc khi cần sửa từng phân hệ

### 21.1 Auth và public

- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/HomeController.php`
- `routes/web.php`

### 21.2 Admin

- `app/Http/Controllers/Admin/AdminController.php`
- `app/Http/Controllers/Admin/KhoaHocManagementController.php`
- `app/Http/Controllers/Admin/ModuleHocController.php`
- `app/Http/Controllers/Admin/LichHocController.php`
- `app/Http/Controllers/Admin/PhanCongController.php`
- `app/Http/Controllers/Admin/YeuCauHocVienController.php`
- `app/Http/Controllers/Admin/TeacherLeaveRequestController.php`
- `app/Http/Controllers/Admin/AttendanceController.php`
- `app/Http/Controllers/Admin/ThuVienController.php`
- `app/Http/Controllers/Admin/BaiGiangController.php`
- `app/Http/Controllers/Admin/NganHangCauHoiController.php`
- `app/Http/Controllers/Admin/BaiKiemTraPheDuyetController.php`

### 21.3 Giảng viên

- `app/Http/Controllers/GiangVien/PhanCongController.php`
- `app/Http/Controllers/GiangVien/TeacherScheduleController.php`
- `app/Http/Controllers/GiangVien/TeacherLeaveRequestController.php`
- `app/Http/Controllers/GiangVien/DiemDanhController.php`
- `app/Http/Controllers/GiangVien/TeacherAttendanceController.php`
- `app/Http/Controllers/GiangVien/TaiNguyenController.php`
- `app/Http/Controllers/GiangVien/BaiGiangController.php`
- `app/Http/Controllers/GiangVien/LiveRoomController.php`
- `app/Http/Controllers/GiangVien/BaiKiemTraController.php`

### 21.4 Học viên

- `app/Http/Controllers/HocVienController.php`
- `app/Http/Controllers/HocVien/BaiKiemTraController.php`
- `app/Http/Controllers/HocVien/LiveRoomController.php`
- `app/Services/StudentLearningDashboardService.php`
- `app/Services/StudentScheduleViewService.php`

### 21.5 Service nghiệp vụ lõi

- `app/Services/KetQuaHocTapService.php`
- `app/Services/TeacherAttendanceService.php`
- `app/Services/LiveLectureService.php`

## 22. Checklist hiểu hệ thống trong 5 phút

Nếu là AI khác mới vào project, hãy nắm 10 ý này trước:

1. Đây là LMS có 3 vai trò: admin, giảng viên, học viên.
2. Học viên đăng ký dùng ngay, giảng viên đăng ký phải chờ admin duyệt.
3. `KhoaHoc` có 2 loại: mẫu và hoạt động.
4. Admin thường tạo khóa mẫu rồi mở lớp thành khóa hoạt động.
5. Giảng viên thao tác sâu chỉ khi đã nhận phân công.
6. `LichHoc` là trung tâm kéo theo điểm danh, tiến độ, live room và tài nguyên buổi học.
7. Học viên chỉ thấy nội dung đã duyệt, đã công bố và đúng quyền.
8. Bài kiểm tra có giám sát, pre-check và review vi phạm.
9. Kết quả học tập được service tổng hợp, không nên sửa tay bừa ở một chỗ.
10. Tests đang là nơi phản ánh rule nghiệp vụ đáng tin nhất.

## 23. Những điểm cần cẩn thận khi bảo trì hoặc mở rộng

- Đừng sửa logic trạng thái khóa học mà không kiểm tra test tiến độ học.
- Đừng gom chung quyền giảng viên theo khóa nếu chưa kiểm tra quyền theo module.
- Đừng coi tài nguyên thư viện và tài nguyên buổi học là hai hệ hoàn toàn tách biệt, vì đang dùng chung model hoặc bảng.
- Đừng cho học viên xem nội dung nếu chưa kiểm tra chuỗi điều kiện duyệt, công bố, thời điểm mở và ghi danh.
- Đừng sửa bài kiểm tra mà quên luồng giám sát và chấm tự luận.
- Đừng chỉ đọc controller, hãy đọc cả service và test.

## 24. Kết luận ngắn gọn

Tóm lại, đồ án này là một hệ thống đào tạo trực tuyến có đầy đủ luồng:

- Quản trị vận hành đào tạo bởi admin.
- Tác nghiệp giảng dạy và đánh giá bởi giảng viên.
- Học tập, thi và theo dõi tiến độ bởi học viên.

Trung tâm nghiệp vụ của hệ thống xoay quanh:

- `KhoaHoc`
- `ModuleHoc`
- `LichHoc`
- `PhanCong giảng viên`
- `HocVienKhoaHoc`
- `TaiNguyen`
- `BaiGiang`
- `PhongHocLive`
- `BaiKiemTra`
- `KetQuaHocTap`

Nếu cần mở rộng hoặc nhờ AI khác hỗ trợ, chỉ cần đưa file này trước, sau đó chỉ định phân hệ cần làm là AI đã có thể nắm bối cảnh nhanh và đi đúng hướng hơn rất nhiều.
