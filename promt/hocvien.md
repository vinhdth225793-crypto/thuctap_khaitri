Bạn là senior Laravel developer + system analyst + education workflow architect.

Tôi muốn bạn đọc code thật của project Laravel hiện tại và xây dựng / chuẩn hóa hoàn chỉnh **FLOW HỌC VIÊN** cho hệ thống học tập và kiểm tra online.

==================================================
1. YÊU CẦU CỰC KỲ QUAN TRỌNG
==================================================

- Không được code mù.
- Phải đọc code thật của repo hiện tại trước khi làm.
- Phải bám đúng model/controller/route/view/migration hiện có.
- Không làm lại từ đầu nếu repo đã có nền.
- Không phá flow đang chạy của:
  - auth học viên
  - khóa học có thể tham gia
  - khóa học của tôi
  - chi tiết khóa học
  - bài giảng
  - live room
  - bài kiểm tra
  - profile học viên
  - hoạt động / tiến độ
- Mỗi phase phải làm xong, test ổn rồi mới sang phase tiếp theo.
- Phải ưu tiên tận dụng những gì repo đã có:
  - `HocVienController`
  - `HocVien\BaiKiemTraController`
  - `HocVien\LiveRoomController`
  - `KhoaHoc`
  - `ModuleHoc`
  - `LichHoc`
  - `BaiGiang`
  - `TaiNguyenBuoiHoc`
  - `BaiKiemTra`
  - `KetQuaHocTap`
  - `HocVienKhoaHoc`
  - request / view / route hiện tại

==================================================
2. MỤC TIÊU TỔNG THỂ PHẦN HỌC VIÊN
==================================================

Tôi muốn phần học viên trở thành một flow học tập hoàn chỉnh, gồm các luồng:

1. đăng ký / đăng nhập học viên
2. xem khóa học có thể tham gia
3. gửi yêu cầu tham gia khóa học
4. xem khóa học của tôi
5. xem chi tiết khóa học
6. xem module học
7. xem thời khóa biểu / buổi học
8. xem tài liệu / bài giảng giảng viên đăng
9. tham gia phòng học live
10. tham gia bài kiểm tra
11. xem kết quả bài kiểm tra
12. xem tiến độ học tập
13. cập nhật hồ sơ cá nhân

Mục tiêu cuối cùng:
- học viên vào hệ thống là biết phải học gì
- xem được lịch học
- vào được bài giảng / tài liệu / live room
- làm được bài kiểm tra
- xem được tiến độ và kết quả học tập

==================================================
3. FLOW HỌC VIÊN CẦN TRIỂN KHAI
==================================================

A. Đăng ký / đăng nhập
- học viên tự đăng ký
- đăng nhập bằng email + mật khẩu
- vào dashboard học viên

B. Khóa học có thể tham gia
- xem danh sách khóa học
- xem chi tiết
- gửi yêu cầu tham gia

C. Khóa học của tôi
- xem các khóa học đã được duyệt tham gia
- xem trạng thái và tiến độ từng khóa

D. Chi tiết khóa học
- xem thông tin khóa học
- xem danh sách module
- xem danh sách buổi học
- xem bài giảng / tài liệu
- xem bài kiểm tra đã phát hành

E. Thời khóa biểu / buổi học
- xem ngày học
- giờ học
- module
- giảng viên
- hình thức học
- link học nếu online

F. Bài giảng / tài liệu
- xem tài liệu giảng viên công bố
- xem chi tiết bài giảng
- tải / xem file nếu được phép

G. Live room
- tham gia phòng học live
- rời phòng
- xem recording nếu có công bố

H. Bài kiểm tra
- xem danh sách bài kiểm tra
- xem chi tiết bài kiểm tra
- bắt đầu làm bài
- nộp bài
- xem trạng thái bài làm
- xem kết quả nếu có

I. Tiến độ học tập
- xem số module đã hoàn thành
- xem số buổi đã học
- xem trạng thái khóa học
- xem kết quả học tập

J. Hồ sơ cá nhân
- cập nhật thông tin cá nhân
- đổi mật khẩu
- cập nhật ảnh đại diện nếu hệ thống đã hỗ trợ

==================================================
4. NHỮNG GÌ PHẢI ĐỌC TRƯỚC
==================================================

Bắt buộc phải đọc kỹ code hiện tại ở các phần:

- `routes/web.php`
- `App\Http\Controllers\HocVienController`
- `App\Http\Controllers\HocVien\BaiKiemTraController`
- `App\Http\Controllers\HocVien\LiveRoomController`
- `App\Http\Controllers\AuthController`
- model `NguoiDung`
- model `HocVien`
- model `HocVienKhoaHoc`
- model `KhoaHoc`
- model `ModuleHoc`
- model `LichHoc`
- model `BaiGiang`
- model `TaiNguyenBuoiHoc`
- model `BaiKiemTra`
- model `KetQuaHocTap`
- các view hiện có của học viên:
  - dashboard
  - khóa học của tôi
  - khóa học tham gia
  - chi tiết khóa học
  - bài giảng
  - bài kiểm tra
  - profile
  - hoạt động tiến độ

Phải chỉ ra rõ:
- phần nào đã có nền tốt
- phần nào còn thiếu
- phần nào cần sửa UI
- phần nào cần bổ sung backend

==================================================
5. NGUYÊN TẮC THIẾT KẾ
==================================================

- Không làm rời rạc từng màn hình.
- Phần học viên phải được thiết kế như một “trung tâm học tập cá nhân”.
- Từ một khóa học, học viên phải có thể đi tới:
  - module
  - buổi học
  - tài liệu
  - live room
  - bài kiểm tra
  - tiến độ
- Từ một buổi học, học viên phải thấy:
  - thông tin buổi
  - link học
  - tài liệu
  - bài giảng liên quan
- Từ một bài kiểm tra, học viên phải thấy:
  - trạng thái
  - số lần làm
  - điểm / kết quả nếu có

==================================================
6. PHASE TRIỂN KHAI
==================================================

PHASE 1:
- đọc code hiện tại của phần học viên
- phân tích những route/controller/view/model đã có
- viết báo cáo hiện trạng:
  - cái gì đã có
  - cái gì thiếu
  - cái gì nên tận dụng
- đề xuất kiến trúc chuẩn hóa flow học viên

PHASE 2:
- chuẩn hóa Dashboard học viên
- hiển thị:
  - số khóa học đang học
  - buổi học sắp tới
  - bài kiểm tra sắp mở / đang mở
  - tiến độ tổng quan
  - bài giảng mới / thông báo quan trọng nếu phù hợp
- nếu UI hiện tại còn đơn giản, hãy cải thiện nhưng không phá layout chung

PHASE 3:
- chuẩn hóa flow “Khóa học có thể tham gia”
- danh sách khóa học có thể tham gia
- xem chi tiết khóa học
- gửi yêu cầu tham gia khóa học
- hiển thị trạng thái:
  - chưa tham gia
  - đã gửi yêu cầu
  - đã tham gia
- xử lý validate để không gửi trùng yêu cầu

PHASE 4:
- chuẩn hóa flow “Khóa học của tôi”
- hiển thị danh sách khóa học đã tham gia
- hiển thị tiến độ từng khóa
- từ mỗi khóa có thể đi vào:
  - chi tiết khóa học
  - module
  - lịch học
  - bài kiểm tra
- giao diện phải rõ hơn và dễ thao tác hơn

PHASE 5:
- chuẩn hóa màn “Chi tiết khóa học”
- thêm các khu / tab rõ ràng:
  - tổng quan
  - module
  - buổi học / lịch học
  - bài giảng / tài liệu
  - bài kiểm tra
  - tiến độ / kết quả
- đảm bảo dữ liệu lấy đúng theo học viên đang học khóa đó
- không cho học viên ngoài khóa truy cập

PHASE 6:
- chuẩn hóa flow “Module học”
- hiển thị danh sách module theo thứ tự
- hiển thị:
  - số buổi trong module
  - số buổi đã hoàn thành
  - trạng thái module:
    - chưa bắt đầu
    - đang học
    - hoàn thành
- nếu repo chưa có logic trạng thái module rõ ràng, phải tận dụng / phối hợp với logic tiến độ đã có hoặc service mới phù hợp

PHASE 7:
- chuẩn hóa flow “Thời khóa biểu / Buổi học”
- hiển thị danh sách buổi học theo khóa học
- nếu phù hợp, thêm 2 chế độ hiển thị:
  - danh sách
  - thời khóa biểu
- mỗi buổi học phải hiển thị:
  - ngày
  - giờ
  - module
  - giảng viên
  - hình thức học
  - trạng thái buổi học
- học viên bấm vào buổi học để xem chi tiết buổi

PHASE 8:
- chuẩn hóa flow “Chi tiết buổi học”
- hiển thị:
  - thông tin buổi học
  - link học nếu online
  - tài nguyên của buổi
  - bài giảng liên quan
  - bài kiểm tra liên quan nếu có
- từ đây học viên có thể:
  - xem tài liệu
  - tham gia live room
  - đi tới bài kiểm tra

PHASE 9:
- chuẩn hóa flow “Bài giảng / tài liệu”
- màn danh sách bài giảng
- màn chi tiết bài giảng
- chỉ hiển thị bài giảng / tài liệu đã được công bố cho học viên
- hỗ trợ xem file hoặc tải file nếu nghiệp vụ hiện tại cho phép
- đảm bảo đúng quyền của học viên thuộc khóa học

PHASE 10:
- chuẩn hóa flow “Live room”
- học viên có thể:
  - mở màn hình phòng học
  - tham gia
  - rời phòng
  - xem recording nếu được công bố
- kiểm tra quyền chặt chẽ:
  - chỉ học viên thuộc khóa học đó mới được vào
- hiển thị rõ trạng thái phòng:
  - chưa mở
  - đang diễn ra
  - đã kết thúc

PHASE 11:
- chuẩn hóa flow “Bài kiểm tra”
- xem danh sách bài kiểm tra
- xem chi tiết bài kiểm tra
- bắt đầu làm bài
- nộp bài
- hiển thị:
  - thời gian làm bài
  - thời gian mở/đóng
  - số lần làm
  - trạng thái có thể làm / đã nộp / chờ chấm / đã chấm
- đảm bảo chỉ bài kiểm tra đã phát hành mới hiển thị cho học viên

PHASE 12:
- chuẩn hóa flow “Kết quả bài kiểm tra”
- học viên có thể xem:
  - điểm số
  - trạng thái chấm
  - nhận xét nếu có
  - số câu đúng nếu loại bài hỗ trợ
- nếu bài có tự luận chưa chấm xong:
  - hiển thị trạng thái chờ chấm rõ ràng

PHASE 13:
- chuẩn hóa flow “Tiến độ học tập”
- hiển thị:
  - số buổi đã học
  - số buổi còn lại
  - số module đã hoàn thành
  - tiến độ khóa học
  - kết quả học tập
- nếu phù hợp, thêm trang hoặc tab riêng “Hoạt động & tiến độ”
- ưu tiên tận dụng `KetQuaHocTap` và logic buổi học/module/khóa học hiện có

PHASE 14:
- chuẩn hóa flow “Profile học viên”
- cập nhật thông tin cá nhân
- đổi mật khẩu
- cập nhật ảnh đại diện nếu đã có hỗ trợ
- validate ổn định
- không làm gãy auth hiện tại

PHASE 15:
- rà lại toàn bộ flow học viên
- kiểm tra điều hướng giữa các màn
- làm sạch UI / action button / breadcrumb nếu phù hợp
- test toàn bộ phần học viên
- đảm bảo không phá các luồng admin/giảng viên hiện có

==================================================
7. YÊU CẦU KỸ THUẬT
==================================================

Không được nhồi toàn bộ logic vào controller.

Nếu cần, hãy tách service hợp lý, ví dụ:
- `StudentLearningDashboardService`
- `StudentCourseProgressService`
- `StudentScheduleViewService`

Tên có thể khác, nhưng mục tiêu là:
- code sạch
- dễ bảo trì
- bám đúng repo hiện có

Phải tận dụng tối đa:
- relation Eloquent sẵn có
- scope/query sẵn có
- cấu trúc route hiện tại
- view hiện tại nếu có thể sửa nâng cấp thay vì làm lại

==================================================
8. ĐẦU RA TÔI MUỐN
==================================================

Tôi muốn bạn trả ra theo format:

PHẦN A - PHÂN TÍCH HIỆN TRẠNG
- route học viên hiện có
- controller học viên hiện có
- model liên quan
- view hiện có
- phần nào đã tốt
- phần nào thiếu
- phần nào cần chuẩn hóa

PHẦN B - THIẾT KẾ NGHIỆP VỤ
- flow dashboard học viên
- flow tham gia khóa học
- flow khóa học của tôi
- flow chi tiết khóa học
- flow module
- flow thời khóa biểu / buổi học
- flow tài liệu / bài giảng
- flow live room
- flow bài kiểm tra
- flow tiến độ học tập
- flow profile học viên

PHẦN C - THIẾT KẾ KỸ THUẬT
- model/service/controller/view nào cần sửa hoặc thêm
- logic nào tận dụng từ code cũ
- logic nào cần bổ sung

PHẦN D - TRIỂN KHAI CODE
- code theo từng phase
- giải thích rõ file nào được sửa / thêm

PHẦN E - TEST
- test đăng nhập học viên
- test xem khóa học có thể tham gia
- test gửi yêu cầu tham gia
- test xem khóa học của tôi
- test xem chi tiết khóa học
- test xem buổi học
- test xem bài giảng / tài liệu
- test tham gia live room
- test làm bài kiểm tra
- test xem tiến độ
- test cập nhật profile

==================================================
9. YÊU CẦU CUỐI
==================================================

Hãy bắt đầu bằng việc đọc code thật của repo hiện tại rồi mới làm.

Mục tiêu cuối cùng:
- phần học viên có flow hoàn chỉnh, rõ ràng, dễ dùng
- học viên xem được khóa học, lịch học, tài liệu, live room, bài kiểm tra, tiến độ
- không phá flow cũ đang chạy
- làm theo từng phase, xong phase nào chắc phase đó rồi mới sang phase tiếp theo

Ưu tiên làm chắc backend và quyền truy cập trước, sau đó mới tối ưu UI hiển thị và trải nghiệm học viên.

nhớ xem lỗi chính tả và chuẩn hóa utf8

Ưu tiên làm chắc backend và rule nghiệp vụ trước, sau đó mới tối ưu UI hiển thị.