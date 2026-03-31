Bạn là senior Laravel developer + UX architect + business flow analyst.

Tôi muốn bạn đọc code thật của project Laravel hiện tại và CHUẨN HÓA lại toàn bộ khu vực GIẢNG VIÊN theo 4 mục tiêu chính dưới đây.

==================================================
1. 4 MỤC TIÊU CẦN LÀM
==================================================

Tôi muốn bạn tập trung vào 4 phần sau:

1. Hiển thị lịch dạy đẹp hơn
2. Hoàn thiện flow đơn xin nghỉ của giảng viên
3. Đồng bộ trạng thái hoàn thành module / khóa học theo buổi học
4. Gom UI giảng viên thành một “trung tâm điều hành” thống nhất

Tức là tôi không muốn khu vực giảng viên bị rời rạc nhiều màn hình lẻ tẻ nữa.
Tôi muốn giảng viên có một flow vận hành rõ ràng, đẹp, dễ dùng, bám đúng nghiệp vụ giảng dạy trong hệ thống.

==================================================
2. YÊU CẦU CỰC KỲ QUAN TRỌNG
==================================================

- Không được code mù.
- Phải đọc code thật của repo hiện tại trước khi sửa.
- Phải bám các model/controller/route/view hiện có.
- Không làm lại từ đầu nếu repo đã có nền.
- Không phá flow giảng viên đang hoạt động.
- Mọi thay đổi phải dựa trên code hiện tại của project.

Trước khi bắt đầu, bắt buộc phải đọc kỹ các phần liên quan tối thiểu:
- `GiangVienController`
- `App\Http\Controllers\GiangVien\PhanCongController`
- `App\Http\Controllers\GiangVien\TaiNguyenController`
- `App\Http\Controllers\GiangVien\BaiGiangController`
- `App\Http\Controllers\GiangVien\BaiKiemTraController`
- `App\Http\Controllers\Admin\LichHocController`
- model `LichHoc`
- model `ModuleHoc`
- model `KhoaHoc`
- model `PhanCongModuleGiangVien`
- model `GiangVienDonXinNghi` nếu đã có
- các view thuộc:
  - dashboard giảng viên
  - khóa học / phân công
  - bài giảng
  - thư viện
  - bài kiểm tra
  - lịch học
  - điểm danh
  - chấm điểm

==================================================
3. MỤC TIÊU CHUNG CỦA KHU VỰC GIẢNG VIÊN
==================================================

Tôi muốn khu vực giảng viên sau khi chuẩn hóa phải trở thành một “TRUNG TÂM ĐIỀU HÀNH GIẢNG DẠY”.

Nghĩa là từ đây giảng viên có thể:
- xem lớp / module đang dạy
- xem lịch dạy
- xem trạng thái buổi học
- xem tiến độ module / khóa học
- cập nhật link học
- đăng tài nguyên
- tạo bài giảng
- tạo bài kiểm tra
- điểm danh
- chấm điểm
- gửi đơn xin nghỉ
- theo dõi đơn xin nghỉ
- biết module nào đã hoàn thành
- biết khóa học nào đang diễn ra / đã hoàn thành

==================================================
4. PHẦN 1 - HIỂN THỊ LỊCH DẠY ĐẸP HƠN
==================================================

------------------------------------------
4.1. MỤC TIÊU
------------------------------------------

Hiện tại hệ thống đã có `LichHoc` và giảng viên đang xem các buổi học, nhưng tôi muốn phần lịch dạy:
- dễ nhìn hơn
- rõ hơn
- trực quan hơn
- gắn chặt với công việc giảng dạy

Tôi muốn có 2 kiểu hiển thị:

A. DẠNG THỜI KHÓA BIỂU
B. DẠNG DANH SÁCH

------------------------------------------
4.2. DẠNG THỜI KHÓA BIỂU
------------------------------------------

Hiển thị theo kiểu:
- trục ngang: ngày hoặc thứ
- trục dọc: tiết 1 đến tiết 12 hoặc khung buổi
- mỗi ô hiển thị:
  - khóa học
  - module
  - buổi số
  - trạng thái buổi
  - hình thức học
- màu sắc rõ ràng cho trạng thái:
  - sắp tới
  - đang học
  - hoàn thành
  - hủy
  - có đơn xin nghỉ liên quan

Giảng viên có thể click vào một buổi học để:
- xem chi tiết
- cập nhật link học
- mở khu vực tài nguyên
- mở bài kiểm tra
- mở điểm danh

------------------------------------------
4.3. DẠNG DANH SÁCH
------------------------------------------

Hiển thị list với các cột:
- ngày
- thứ
- buổi số
- tiết / khung giờ
- khóa học
- module
- hình thức học
- trạng thái buổi học
- trạng thái đơn xin nghỉ nếu có
- hành động nhanh

Có bộ lọc:
- theo khóa học
- theo module
- theo ngày
- theo trạng thái
- theo hình thức học

------------------------------------------
4.4. YÊU CẦU KỸ THUẬT
------------------------------------------

Phải tận dụng model `LichHoc` hiện có:
- `timeline_trang_thai`
- `trang_thai_label`
- `schedule_range_label`
- `hinh_thuc_label`
- các relation tới tài nguyên / bài giảng / bài kiểm tra / điểm danh / đơn xin nghỉ

Hãy tạo service hoặc presenter nếu cần để build dữ liệu lịch đẹp hơn thay vì nhồi logic vào blade.

==================================================
5. PHẦN 2 - HOÀN THIỆN FLOW ĐƠN XIN NGHỈ GIẢNG VIÊN
==================================================

------------------------------------------
5.1. MỤC TIÊU
------------------------------------------

Repo hiện tại đã có dấu hiệu của đơn xin nghỉ / teacher leave request.
Tôi muốn flow này hoàn chỉnh, rõ ràng và dễ dùng hơn.

Giảng viên phải có thể:
- xem lịch dạy của mình
- chọn một buổi học cụ thể
- gửi đơn xin nghỉ / xin off cho buổi đó
- hoặc gửi đơn xin nghỉ theo ngày / buổi / tiết nếu nghiệp vụ cho phép
- theo dõi trạng thái đơn
- biết đơn nào:
  - chờ duyệt
  - đã duyệt
  - bị từ chối

Admin phải có thể:
- xem danh sách đơn
- duyệt
- từ chối
- ghi chú phản hồi
- nhìn thấy buổi học nào bị ảnh hưởng

------------------------------------------
5.2. FLOW GIẢNG VIÊN
------------------------------------------

1. Giảng viên vào lịch dạy hoặc chi tiết buổi học
2. Chọn “Xin nghỉ / phản hồi lịch dạy”
3. Hệ thống hiển thị form:
   - buổi học
   - ngày
   - khung giờ
   - lý do xin nghỉ
4. Giảng viên gửi đơn
5. Hệ thống tạo đơn với trạng thái `cho_duyet`
6. Giảng viên có màn riêng để xem danh sách đơn đã gửi

------------------------------------------
5.3. FLOW ADMIN
------------------------------------------

1. Admin vào danh sách đơn xin nghỉ giảng viên
2. Xem:
   - giảng viên
   - buổi học
   - khóa học
   - module
   - ngày / tiết
   - lý do
3. Admin chọn:
   - duyệt
   - từ chối
4. Nếu duyệt:
   - hệ thống đánh dấu buổi học đang cần xử lý tiếp
   - admin có thể đổi lịch hoặc đổi giảng viên sau
5. Nếu từ chối:
   - lưu phản hồi

------------------------------------------
5.4. YÊU CẦU CHUẨN HÓA
------------------------------------------

Tôi muốn flow đơn xin nghỉ có:
- màn tạo đơn rõ ràng
- màn lịch sử đơn của giảng viên
- badge trạng thái trên lịch dạy
- liên kết từ `LichHoc` sang đơn xin nghỉ
- thông báo rõ ràng cho giảng viên và admin

Nếu trong repo đã có model / table cho đơn xin nghỉ thì tận dụng.
Nếu đang thiếu controller / view / service thì bổ sung theo cấu trúc hiện tại.

==================================================
6. PHẦN 3 - TRẠNG THÁI HOÀN THÀNH MODULE / KHÓA HỌC THEO BUỔI HỌC
==================================================

------------------------------------------
6.1. MỤC TIÊU
------------------------------------------

Tôi muốn hệ thống tự động tính tiến độ theo chuỗi:

Buổi học hoàn thành
→ Module hoàn thành khi hoàn thành hết các buổi
→ Khóa học hoàn thành khi hoàn thành hết các module

------------------------------------------
6.2. RULE NGHIỆP VỤ
------------------------------------------

A. BUỔI HỌC
- buổi học có thể ở trạng thái:
  - chờ
  - đang học
  - hoàn thành
  - hủy

B. MODULE
- module được xem là hoàn thành khi tất cả buổi học hợp lệ của module đã hoàn thành
- nếu module chưa có buổi nào thì không xem là hoàn thành
- nếu module mới học một phần thì hiển thị đang diễn ra

C. KHÓA HỌC
- khóa học được xem là hoàn thành khi tất cả module của khóa học đã hoàn thành
- nếu còn module chưa xong thì khóa vẫn là đang vận hành / đang học

------------------------------------------
6.3. HIỂN THỊ TIẾN ĐỘ
------------------------------------------

Tôi muốn ở giao diện giảng viên thấy được:

A. Trong từng module:
- tổng số buổi học
- số buổi đã hoàn thành
- số buổi còn lại
- trạng thái module

B. Trong từng khóa học:
- tổng số module
- số module đã hoàn thành
- trạng thái khóa học

Ví dụ:
- Module 1: 8/8 buổi → Đã hoàn thành
- Module 2: 3/8 buổi → Đang diễn ra
- Khóa học A: 4/4 module → Đã hoàn thành
- Khóa học B: 2/5 module → Đang học

------------------------------------------
6.4. YÊU CẦU KỸ THUẬT
------------------------------------------

Phải đọc code hiện tại để xác định:
- trạng thái `LichHoc` hiện đang tính kiểu gì
- `ModuleHoc` đã có cột trạng thái chưa
- `KhoaHoc` đã có `trang_thai_van_hanh` hay trường tương đương chưa

Hãy chọn giải pháp an toàn:
- tính động bằng accessor/service
hoặc
- lưu DB nếu thật sự cần

Phải giải thích rõ vì sao chọn cách đó.

Nếu cần, tạo service riêng ví dụ:
- `LearningProgressStatusService`
hoặc tên phù hợp

Service này có thể:
- syncModuleStatus()
- syncCourseStatus()
- buildProgressSummary()

==================================================
7. PHẦN 4 - GOM UI GIẢNG VIÊN THÀNH “TRUNG TÂM ĐIỀU HÀNH”
==================================================

------------------------------------------
7.1. MỤC TIÊU
------------------------------------------

Hiện tại khu vực giảng viên đang có nhiều phần:
- dashboard
- khóa học / phân công
- bài giảng
- thư viện
- bài kiểm tra
- chấm điểm
- điểm danh

Tôi muốn gom lại theo một UI/flow thống nhất hơn, không bị rời rạc.

------------------------------------------
7.2. Ý TƯỞNG CHUẨN HÓA
------------------------------------------

Tạo một “trung tâm điều hành giảng viên” theo logic:

TAB / NHÓM 1: TỔNG QUAN
- số lớp đang dạy
- số buổi sắp tới
- số đơn xin nghỉ chờ duyệt
- số bài kiểm tra chờ chấm
- thông báo quan trọng

TAB / NHÓM 2: LỊCH DẠY
- thời khóa biểu
- danh sách buổi học
- trạng thái buổi
- xin nghỉ

TAB / NHÓM 3: KHÓA HỌC ĐANG DẠY
- danh sách khóa học
- module phụ trách
- tiến độ module
- tiến độ khóa học

TAB / NHÓM 4: TÀI NGUYÊN / BÀI GIẢNG
- thư viện
- bài giảng
- live room
- tài nguyên theo buổi

TAB / NHÓM 5: KIỂM TRA / CHẤM ĐIỂM
- bài kiểm tra đã tạo
- bài kiểm tra chờ duyệt
- bài làm chờ chấm
- lịch sử chấm điểm

------------------------------------------
7.3. YÊU CẦU GIAO DIỆN
------------------------------------------

Không nhất thiết phải tạo một trang duy nhất nếu không phù hợp với repo hiện tại.
Nhưng UI phải được chuẩn hóa để:
- điều hướng rõ ràng hơn
- ít phân mảnh hơn
- từ một buổi học có thể đi nhanh tới:
  - tài nguyên
  - bài giảng
  - kiểm tra
  - điểm danh
  - xin nghỉ

Nếu phù hợp, có thể:
- cải tiến dashboard hiện tại
- thêm card action nhanh
- thêm các nút điều hướng liên quan trên chi tiết buổi học / chi tiết khóa học

==================================================
8. CÁC PHẦN CẦN KIỂM TRA VÀ TẬN DỤNG
==================================================

Bắt buộc phải kiểm tra repo hiện tại xem đã có gì để tận dụng:
- `LichHoc` có timeline status
- `teacherLeaveRequests`
- `AdminSchedulePlanningService`
- `BaiKiemTraController`
- `PhanCongController`
- `TaiNguyenController`
- `BaiGiangController`
- dashboard giảng viên
- view chi tiết khóa học / phân công
- view bài giảng / thư viện / chấm điểm

Không làm trùng logic nếu service/model đã có nền rồi.

==================================================
9. KIẾN TRÚC CODE MONG MUỐN
==================================================

Không được nhồi hết vào controller.

Ưu tiên tách rõ thành:
- service build lịch dạy
- service build trạng thái tiến độ
- service xử lý đơn xin nghỉ
- presenter / helper cho UI nếu cần
- partial blade / component để tái sử dụng card trạng thái, badge trạng thái, action nhanh

Ví dụ:
- TeacherTeachingDashboardService
- TeacherLeaveRequestService
- LearningProgressStatusService
- TeacherScheduleViewService

Tên có thể khác, nhưng phải có cấu trúc rõ ràng.

==================================================
10. CHIA THEO PHASE
==================================================

PHASE 1:
- đọc code hiện tại
- phân tích flow giảng viên đang có
- chỉ ra phần nào đã tốt, phần nào rời rạc
- đề xuất kiến trúc chuẩn hóa

PHASE 2:
- chuẩn hóa hiển thị lịch dạy:
  - thời khóa biểu
  - danh sách

PHASE 3:
- hoàn thiện flow đơn xin nghỉ:
  - giảng viên gửi đơn
  - xem đơn
  - admin xử lý
  - badge trạng thái trên lịch

PHASE 4:
- đồng bộ trạng thái hoàn thành:
  - buổi học
  - module
  - khóa học
- hiển thị tiến độ rõ ràng

PHASE 5:
- gom UI giảng viên thành trung tâm điều hành thống nhất hơn
- cải thiện dashboard / điều hướng / action nhanh

PHASE 6:
- test toàn bộ flow
- đảm bảo không phá chức năng cũ

==================================================
11. ĐẦU RA TÔI MUỐN
==================================================

Tôi muốn bạn trả ra theo format sau:

PHẦN A - PHÂN TÍCH HIỆN TRẠNG
- flow giảng viên hiện tại
- file nào liên quan
- phần nào đã có nền
- phần nào cần chuẩn hóa thêm

PHẦN B - THIẾT KẾ NGHIỆP VỤ
- flow lịch dạy đẹp hơn
- flow đơn xin nghỉ
- flow hoàn thành module / khóa học
- flow trung tâm điều hành giảng viên

PHẦN C - THIẾT KẾ KỸ THUẬT
- model/service/controller/view cần sửa hoặc thêm
- logic nào tận dụng từ code cũ
- logic nào cần viết mới

PHẦN D - TRIỂN KHAI CODE
- code theo từng phase
- giải thích file nào được sửa / thêm

PHẦN E - TEST
- test lịch dạy
- test đơn xin nghỉ
- test trạng thái module / khóa học
- test UI điều hướng giảng viên

==================================================
12. YÊU CẦU CUỐI
==================================================

Hãy bắt đầu bằng việc đọc code thật của repo hiện tại rồi mới sửa.

Mục tiêu cuối cùng là:
- lịch dạy giảng viên đẹp hơn và dễ dùng hơn
- đơn xin nghỉ hoàn chỉnh hơn
- trạng thái module / khóa học được đồng bộ theo buổi học
- khu vực giảng viên được gom thành một trung tâm điều hành thống nhất, rõ ràng, dễ thao tác

Không được code mù.
Không được làm trùng logic có sẵn.
Ưu tiên tích hợp chắc chắn với flow hiện tại của hệ thống.