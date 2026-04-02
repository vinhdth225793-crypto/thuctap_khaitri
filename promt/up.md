Bạn là senior Laravel developer + system analyst + education workflow architect.

Tôi đang phát triển một project Laravel cho hệ thống học tập và kiểm tra online. Dựa trên code hiện tại của project, tôi muốn bạn tiếp tục CHUẨN HÓA và HOÀN THIỆN hệ thống theo các hướng dưới đây.

==================================================
1. YÊU CẦU CỰC KỲ QUAN TRỌNG
==================================================

- Không được code mù.
- Phải đọc code thật của repo hiện tại trước khi sửa.
- Phải bám vào model/controller/route/view/migration hiện có.
- Không làm lại từ đầu nếu repo đã có nền.
- Không phá flow hiện tại đang hoạt động.
- Mọi phần đề xuất phải ưu tiên tận dụng:
  - `KhoaHoc`
  - `ModuleHoc`
  - `LichHoc`
  - `PhanCongModuleGiangVien`
  - `NganHangCauHoi`
  - `BaiKiemTra`
  - `BaiGiang`
  - `TaiNguyenBuoiHoc`
  - `GiangVienDonXinNghi`
  - các controller admin / giảng viên / học viên đang có

==================================================
2. MỤC TIÊU TỔNG HỢP CẦN LÀM
==================================================

Tôi muốn bạn tiếp tục hoàn thiện hệ thống theo các nhóm lớn sau:

A. Chuẩn hóa tiến độ học tập
- buổi học hoàn thành
- module hoàn thành khi hoàn thành hết buổi học
- khóa học hoàn thành khi hoàn thành hết module

B. Chuẩn hóa khu vực giảng viên
- lịch dạy đẹp hơn
- đơn xin nghỉ hoàn chỉnh hơn
- khu vực giảng viên thành trung tâm điều hành thống nhất

C. Chuẩn hóa ngân hàng câu hỏi
- tách rõ câu hỏi thuộc khóa học mẫu
- và câu hỏi thuộc khóa học hoạt động

D. Chuẩn hóa chức năng bài kiểm tra
- kiểm tra theo buổi học
- cuối module
- toàn khóa
- chọn câu hỏi từ ngân hàng
- import câu hỏi từ file ngoài
- setup thời gian
- chọn gói điểm hoặc setup điểm thủ công

E. Hoàn thiện flow học viên
- xem khóa học
- tham gia khóa học
- xem thời khóa biểu
- xem buổi học
- xem tài liệu giảng viên đăng
- tham gia phòng học live
- tham gia bài kiểm tra
- xem tiến độ học tập

==================================================
3. NHÓM 1 - TIẾN ĐỘ BUỔI HỌC → MODULE → KHÓA HỌC
==================================================

Tôi muốn hệ thống có logic rõ ràng:

- buổi học có trạng thái:
  - chờ
  - đang học
  - hoàn thành
  - hủy
- module hoàn thành khi tất cả buổi học hợp lệ của module đã hoàn thành
- khóa học hoàn thành khi tất cả module của khóa học đã hoàn thành

Yêu cầu:
- đọc logic hiện tại của `LichHoc`
- đọc `KhoaHoc.trang_thai_van_hanh`
- kiểm tra `ModuleHoc` đã có trạng thái chưa
- chọn cách an toàn:
  - tính động
  - hoặc sync trạng thái vào DB nếu thật sự cần

Tôi muốn hiển thị được:
- số buổi đã hoàn thành / tổng số buổi
- số module đã hoàn thành / tổng số module
- trạng thái module
- trạng thái khóa học

==================================================
4. NHÓM 2 - KHU VỰC GIẢNG VIÊN
==================================================

Tôi muốn chuẩn hóa lại khu vực giảng viên thành “trung tâm điều hành giảng dạy”.

Cần làm:
1. lịch dạy đẹp hơn
   - dạng thời khóa biểu
   - dạng danh sách
2. hoàn thiện đơn xin nghỉ
   - giảng viên gửi đơn
   - xem lịch sử đơn
   - admin duyệt / từ chối
   - badge trạng thái trên lịch dạy
3. từ một buổi học có thể đi nhanh tới:
   - cập nhật link học
   - tài nguyên
   - bài giảng
   - bài kiểm tra
   - điểm danh
4. dashboard giảng viên rõ hơn:
   - lớp đang dạy
   - buổi sắp tới
   - bài chờ chấm
   - đơn xin nghỉ
   - module gần hoàn thành

==================================================
5. NHÓM 3 - NGÂN HÀNG CÂU HỎI
==================================================

Tôi muốn chuẩn hóa lại question bank theo đúng mô hình khóa học hiện có:

- câu hỏi thuộc khóa học mẫu
- câu hỏi thuộc khóa học hoạt động

Yêu cầu:
- tận dụng `khoa_hoc.loai = mau / hoat_dong`
- không thêm cột thừa nếu chỉ cần dùng `khoa_hoc_id + khoa_hoc.loai`
- khi thêm câu hỏi:
  - chọn đối tượng:
    - khóa học mẫu
    - khóa học hoạt động
  - dropdown khóa học phải lọc đúng loại
- import câu hỏi cũng phải theo logic đó
- danh sách câu hỏi phải filter và hiển thị badge loại khóa học

==================================================
6. NHÓM 4 - BÀI KIỂM TRA
==================================================

Tôi muốn hoàn thiện chức năng bài kiểm tra theo đúng nghiệp vụ:

A. Phạm vi bài kiểm tra:
- theo buổi học
- cuối module
- toàn khóa

B. Nguồn câu hỏi:
- chọn từ ngân hàng câu hỏi thuộc khóa đó
- hoặc import từ file ngoài giống chức năng import của ngân hàng câu hỏi

C. Cấu hình bài kiểm tra:
- thời gian làm bài
- ngày mở
- ngày đóng
- số lần được làm
- randomize câu hỏi
- randomize đáp án nếu phù hợp

D. Cấu hình điểm:
- chọn gói điểm tự động
- hoặc setup thủ công từng câu

Rule gói điểm:
- giảng viên nhập tổng điểm
- nhập số câu
- hệ thống tự chia điểm
- nếu chia lẻ:
  - làm tròn hợp lý
  - câu cuối điều chỉnh để tổng điểm khớp chính xác

E. Flow duyệt:
- giảng viên tạo đề
- gửi duyệt
- admin duyệt / từ chối
- admin phát hành
- học viên làm bài
- giảng viên chấm tự luận
- hệ thống cập nhật kết quả học tập

==================================================
7. NHÓM 5 - FLOW HỌC VIÊN
==================================================

Tôi muốn bạn chuẩn hóa và hoàn thiện flow học viên theo hệ thống hiện tại.

Flow học viên mong muốn gồm:

1. xem khóa học có thể tham gia
2. gửi yêu cầu tham gia khóa học
3. xem khóa học của tôi
4. xem chi tiết khóa học
5. xem thời khóa biểu học
6. xem danh sách buổi học
7. xem buổi học chi tiết
8. xem tài liệu / bài giảng giảng viên đăng
9. tham gia phòng học live
10. tham gia bài kiểm tra
11. xem tiến độ học tập
12. xem kết quả học tập / điểm số nếu có

==================================================
8. FLOW HỌC VIÊN CHI TIẾT MONG MUỐN
==================================================

------------------------------------------
8.1. XEM KHÓA HỌC CÓ THỂ THAM GIA
------------------------------------------
Học viên vào danh sách khóa học mở cho học viên đăng ký.
Có thể:
- xem thông tin khóa học
- xem mô tả
- xem lịch khai giảng
- gửi yêu cầu tham gia

------------------------------------------
8.2. GỬI YÊU CẦU THAM GIA KHÓA HỌC
------------------------------------------
1. Học viên mở khóa học
2. Bấm “Xin tham gia”
3. Hệ thống tạo yêu cầu
4. Admin xử lý yêu cầu đó
5. Nếu được duyệt, học viên được ghi nhận vào khóa học

------------------------------------------
8.3. XEM KHÓA HỌC CỦA TÔI
------------------------------------------
Học viên có màn “Khóa học của tôi”.
Hiển thị:
- các khóa đang học
- trạng thái khóa học
- tiến độ khóa học
- số module đã hoàn thành
- bài kiểm tra liên quan

------------------------------------------
8.4. XEM CHI TIẾT KHÓA HỌC
------------------------------------------
Trong chi tiết khóa học, học viên phải thấy:
- thông tin khóa học
- danh sách module
- danh sách buổi học
- tài liệu / bài giảng được công bố
- bài kiểm tra đã phát hành
- trạng thái tiến độ

------------------------------------------
8.5. XEM THỜI KHÓA BIỂU / BUỔI HỌC
------------------------------------------
Học viên phải có thể xem:
- thời khóa biểu học
- hoặc danh sách buổi học
Mỗi buổi học hiển thị:
- ngày học
- giờ học
- module
- giảng viên
- hình thức học
- link học nếu online
- trạng thái buổi học

------------------------------------------
8.6. XEM TÀI LIỆU GIẢNG VIÊN ĐĂNG
------------------------------------------
Ở từng buổi học hoặc bài giảng, học viên có thể:
- xem tài liệu
- tải tài liệu nếu được phép
- xem bài giảng
- xem tài nguyên chính / phụ

------------------------------------------
8.7. THAM GIA PHÒNG HỌC LIVE
------------------------------------------
Nếu buổi học hoặc bài giảng có live room:
- học viên có thể vào phòng live
- tham gia
- rời phòng
- xem recording nếu được công bố

------------------------------------------
8.8. THAM GIA BÀI KIỂM TRA
------------------------------------------
Học viên có thể:
- xem danh sách bài kiểm tra
- xem chi tiết bài kiểm tra
- bắt đầu làm bài
- nộp bài
- xem trạng thái bài làm

------------------------------------------
8.9. XEM TIẾN ĐỘ HỌC TẬP
------------------------------------------
Học viên nên xem được:
- số module đã hoàn thành
- số buổi đã học
- tiến độ khóa học
- điểm số / kết quả học tập nếu phù hợp

==================================================
9. GIAO DIỆN HỌC VIÊN MONG MUỐN
==================================================

Tôi muốn khu vực học viên có các màn chính:

- Dashboard học viên
- Khóa học có thể tham gia
- Khóa học của tôi
- Chi tiết khóa học
- Thời khóa biểu học
- Chi tiết bài giảng / tài liệu
- Danh sách bài kiểm tra
- Chi tiết bài kiểm tra
- Hồ sơ học viên
- Tiến độ học tập / hoạt động

Nếu repo hiện tại đã có một phần các màn này, hãy chuẩn hóa tiếp chứ không làm lại từ đầu.

==================================================
10. KIẾN TRÚC CODE MONG MUỐN
==================================================

Không được nhồi toàn bộ logic vào controller.

Ưu tiên tách thành service nếu cần:
- `LearningProgressStatusService`
- `TeacherScheduleViewService`
- `TeacherLeaveRequestService`
- `ExamScoringPackageService`
- `StudentLearningDashboardService`
- `StudentCourseProgressService`

Tên có thể khác, nhưng phải giữ code sạch và đúng repo hiện tại.

==================================================
11. CHIA THEO PHASE
==================================================

PHASE 1:
- đọc code hiện tại
- phân tích các luồng đang có của:
  - admin
  - giảng viên
  - học viên
- chỉ ra phần nào đã có nền
- chỉ ra phần nào cần chuẩn hóa

PHASE 2:
- chuẩn hóa tiến độ:
  - buổi học
  - module
  - khóa học

PHASE 3:
- chuẩn hóa khu vực giảng viên:
  - lịch dạy
  - đơn xin nghỉ
  - trung tâm điều hành giảng viên

PHASE 4:
- chuẩn hóa ngân hàng câu hỏi:
  - khóa mẫu
  - khóa hoạt động

PHASE 5:
- chuẩn hóa bài kiểm tra:
  - phạm vi buổi/module/toàn khóa
  - import câu hỏi từ file
  - gói điểm / điểm thủ công
  - setup thời gian
  - gửi duyệt / phát hành

PHASE 6:
- chuẩn hóa flow học viên:
  - khóa học
  - thời khóa biểu
  - buổi học
  - tài liệu
  - live room
  - bài kiểm tra
  - tiến độ học tập

PHASE 7:
- test toàn bộ flow
- đảm bảo không phá các chức năng cũ

==================================================
12. ĐẦU RA TÔI MUỐN
==================================================

Tôi muốn bạn trả ra theo format sau:

PHẦN A - PHÂN TÍCH HIỆN TRẠNG
- luồng admin hiện có
- luồng giảng viên hiện có
- luồng học viên hiện có
- file nào liên quan
- phần nào đã tốt
- phần nào cần chuẩn hóa

PHẦN B - THIẾT KẾ NGHIỆP VỤ
- tiến độ buổi học/module/khóa học
- khu vực giảng viên
- ngân hàng câu hỏi
- bài kiểm tra
- flow học viên

PHẦN C - THIẾT KẾ KỸ THUẬT
- model/service/controller/view cần sửa hoặc thêm
- logic nào tận dụng
- logic nào cần viết mới

PHẦN D - TRIỂN KHAI CODE
- code theo từng phase
- giải thích file nào được sửa / thêm

PHẦN E - TEST
- test luồng admin
- test luồng giảng viên
- test luồng học viên
- test tiến độ học tập
- test ngân hàng câu hỏi
- test bài kiểm tra
- test live room / tài liệu / buổi học nếu liên quan

==================================================
13. YÊU CẦU CUỐI
==================================================

Hãy bắt đầu bằng việc đọc code thật của repo hiện tại rồi mới sửa.

Mục tiêu cuối cùng:
- chuẩn hóa các phần tôi đề xuất
- làm rõ flow học viên trong hệ thống
- giữ đúng logic của khóa học online trong project hiện tại
- không phá flow cũ đang chạy được
- ưu tiên tích hợp chắc chắn, đúng nghiệp vụ, dễ mở rộng

nhớ xem lỗi chính tả và chuẩn hóa utf8

Ưu tiên làm chắc backend và rule nghiệp vụ trước, sau đó mới tối ưu UI hiển thị.