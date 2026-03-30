Bạn là senior Laravel developer + system analyst + scheduling workflow architect.

Tôi muốn bạn CHỈNH SỬA lại chức năng lịch giảng viên trong project Laravel của tôi theo FLOW MỚI dưới đây.

==================================================
1. THAY ĐỔI NGHIỆP VỤ CỐT LÕI
==================================================

Flow cũ:
- giảng viên tự đăng ký lịch rảnh / lịch giảng
- admin dựa trên lịch rảnh đó để xếp lịch

Flow mới:
- BỎ cơ chế giảng viên đăng ký lịch rảnh chủ động
- hệ thống mặc định quy định KHUNG GIỜ DẠY CHUẨN cho giảng viên
- admin là người chủ động xếp lịch dạy
- nếu giảng viên có việc bận / muốn nghỉ thì giảng viên sẽ gửi đơn phản hồi xin nghỉ cho admin

Nói cách khác:

A. GIẢNG VIÊN KHÔNG CẦN ĐĂNG KÝ LỊCH RẢNH NỮA
B. HỆ THỐNG MẶC ĐỊNH GIỜ DẠY CHUẨN
C. ADMIN XẾP LỊCH DẠY
D. GIẢNG VIÊN CÓ THỂ XIN OFF THEO NGÀY / BUỔI / TIẾT

==================================================
2. KHUNG GIỜ DẠY CHUẨN MỚI
==================================================

Quy ước toàn hệ thống:

- mỗi ngày dạy từ 08:00 đến 20:00
- chia thành 12 tiết
- thứ 2 đến thứ 6 được phép xếp lịch
- thứ 7 và chủ nhật mặc định nghỉ
- nếu sau này cần mở rộng cuối tuần thì có thể cấu hình sau, nhưng phase hiện tại mặc định KHÔNG xếp lịch thứ 7/chủ nhật

Hãy chuẩn hóa hệ thống theo quy ước này.

Nếu hệ thống cần bảng cấu hình tiết học, hãy thiết kế rõ ràng.
Nếu đã có cấu trúc lịch học, hãy tích hợp theo cách ít phá code nhất.

==================================================
3. MỤC TIÊU CHỨC NĂNG SAU KHI SỬA
==================================================

Sau khi sửa, hệ thống phải hoạt động như sau:

1. Admin tạo lịch dạy cho giảng viên trong khung:
   - thứ 2 đến thứ 6
   - từ 08:00 đến 20:00
   - theo 12 tiết trong ngày

2. Hệ thống tự kiểm tra:
   - không xếp ngoài khung giờ chuẩn
   - không xếp thứ 7/chủ nhật
   - không đụng lịch giảng viên
   - giảng viên phải được phân công đúng module / khóa học nếu nghiệp vụ hiện tại yêu cầu

3. Giảng viên xem lịch dạy của mình:
   - dạng thời khóa biểu
   - dạng danh sách

4. Nếu giảng viên có việc bận:
   - giảng viên tạo đơn xin nghỉ / phản hồi xin off
   - chọn ngày
   - chọn buổi hoặc tiết
   - nhập lý do
   - gửi admin duyệt

5. Admin xem đơn xin nghỉ:
   - duyệt
   - từ chối
   - và xử lý điều chỉnh lịch nếu cần

==================================================
4. PHẦN CẦN BỎ / THAY THẾ
==================================================

Hãy rà lại code hiện tại và chỉnh sửa đúng các phần sau:

- bỏ flow giảng viên đăng ký lịch rảnh
- nếu đã có migration/model/controller/view cho lịch rảnh thì:
  - không dùng nữa
  - hoặc chuyển đổi mục đích nếu phù hợp
  - hoặc loại bỏ nếu còn chưa dùng thật
- thay bằng flow mới:
  - lịch chuẩn mặc định của hệ thống
  - đơn xin nghỉ / phản hồi xin off

Không được xóa bừa.
Phải đọc code hiện tại trước rồi mới quyết định:
- giữ
- sửa
- bỏ
- tái sử dụng

==================================================
5. FLOW MỚI - ADMIN XẾP LỊCH DẠY
==================================================

Flow chính:

1. Admin vào quản lý lịch học
2. Chọn khóa học
3. Chọn module
4. Chọn giảng viên
5. Chọn ngày học
6. Chọn tiết bắt đầu / tiết kết thúc hoặc chọn buổi
7. Hệ thống kiểm tra:
   - ngày đó có phải thứ 2 đến thứ 6 không
   - khung tiết có nằm trong 12 tiết chuẩn không
   - giảng viên có bị đụng lịch dạy khác không
   - giảng viên có được phân công module đó không
8. Nếu hợp lệ:
   - lưu lịch dạy
9. Nếu không hợp lệ:
   - báo lỗi rõ ràng
   - không cho lưu

Rule bắt buộc:
- không cho xếp lịch vào thứ 7/chủ nhật
- không cho xếp ngoài khung 08:00 - 20:00
- không cho đụng lịch giảng viên

==================================================
6. FLOW MỚI - GIẢNG VIÊN XEM LỊCH DẠY
==================================================

Giảng viên phải có thể xem lịch dạy theo 2 dạng:

A. DẠNG THỜI KHÓA BIỂU
- trục ngang: ngày trong tuần hoặc ngày cụ thể
- trục dọc: tiết 1 đến tiết 12
- hiển thị rõ:
  - khóa học
  - module
  - buổi học
  - trạng thái

B. DẠNG DANH SÁCH
- ngày
- thứ
- tiết bắt đầu
- tiết kết thúc
- khóa học
- module
- hình thức học
- trạng thái

Giảng viên bấm vào từng buổi học để đi tiếp tới:
- cập nhật link học
- đăng tài nguyên
- tạo bài kiểm tra
- điểm danh

==================================================
7. FLOW MỚI - GIẢNG VIÊN XIN NGHỈ / PHẢN HỒI XIN OFF
==================================================

Đây là phần mới quan trọng.

Nếu giảng viên không thể dạy theo lịch admin đã xếp, giảng viên phải có chức năng gửi đơn xin nghỉ.

------------------------------------------
7.1. MỤC ĐÍCH
------------------------------------------

- giảng viên báo bận / xin nghỉ
- admin nhận phản hồi
- admin quyết định duyệt hoặc từ chối
- nếu duyệt thì admin có thể đổi lịch hoặc đổi giảng viên

------------------------------------------
7.2. THÔNG TIN ĐƠN XIN NGHỈ
------------------------------------------

Đơn xin nghỉ nên có các thông tin:

- id
- giang_vien_id
- khoa_hoc_id nullable nếu cần
- module_hoc_id nullable nếu cần
- lich_hoc_id nullable nếu gắn trực tiếp buổi học
- ngay_xin_nghi
- buoi_hoc nullable
- tiet_bat_dau nullable
- tiet_ket_thuc nullable
- ly_do
- ghi_chu_phan_hoi nullable
- trang_thai:
  - cho_duyet
  - da_duyet
  - tu_choi
- nguoi_duyet_id nullable
- ngay_duyet nullable
- created_at
- updated_at

Hãy kiểm tra schema hiện tại rồi quyết định:
- tạo bảng mới
- hay tận dụng bảng yêu cầu/phản hồi nếu đã có cấu trúc gần giống

Nếu dùng lại bảng cũ thì phải giải thích rõ vì sao phù hợp.

------------------------------------------
7.3. FLOW GIẢNG VIÊN GỬI ĐƠN
------------------------------------------

1. Giảng viên vào mục:
   - Xin nghỉ / phản hồi lịch dạy
2. Hệ thống hiển thị các lịch dạy đã được xếp
3. Giảng viên chọn:
   - một buổi học cụ thể
   hoặc
   - chọn ngày + buổi/tiết nếu muốn xin nghỉ riêng
4. Giảng viên nhập lý do
5. Bấm gửi đơn
6. Hệ thống kiểm tra:
   - ngày xin nghỉ có hợp lệ không
   - tiết xin nghỉ có hợp lệ không
   - có trùng với lịch dạy thực tế của giảng viên không nếu đang xin nghỉ theo lịch đã xếp
7. Nếu hợp lệ:
   - tạo đơn với trạng thái `cho_duyet`

------------------------------------------
7.4. FLOW ADMIN XỬ LÝ ĐƠN
------------------------------------------

1. Admin vào danh sách đơn xin nghỉ giảng viên
2. Xem chi tiết:
   - giảng viên
   - ngày
   - buổi/tiết
   - lịch dạy liên quan
   - lý do
3. Admin chọn:
   - duyệt
   - từ chối
4. Nếu duyệt:
   - cập nhật trạng thái đơn
   - có thể yêu cầu admin xử lý tiếp:
     - đổi lịch học
     - hoặc đổi giảng viên
5. Nếu từ chối:
   - cập nhật trạng thái đơn
   - có thể nhập ghi chú phản hồi

------------------------------------------
7.5. LUỒNG NGHIỆP VỤ SAU KHI DUYỆT
------------------------------------------

Sau khi admin duyệt đơn xin nghỉ:
- lịch dạy không tự mất nếu chưa có rule xử lý
- nhưng hệ thống phải cảnh báo rằng buổi đó cần được:
  - dời lịch
  - hoặc thay giảng viên
  - hoặc xử lý thủ công

Nếu bạn thấy phù hợp với code hiện tại, có thể thêm trạng thái cảnh báo cho lịch học:
- can_xu_ly_thay_doi_giang_vien
- can_doi_lich
Nhưng chỉ làm nếu không phá flow cũ.

==================================================
8. RULE CHỐNG ĐỤNG LỊCH
==================================================

Rule kiểm tra xung đột phải giữ lại và làm chặt hơn.

Một lịch bị coi là đụng nếu:
- cùng giảng viên
- cùng ngày
- và khoảng tiết giao nhau

Ví dụ:
- lịch cũ: tiết 1-4
- lịch mới: tiết 3-5
=> xung đột

Ví dụ:
- lịch cũ: tiết 1-4
- lịch mới: tiết 5-8
=> không xung đột

Áp dụng khi:
- admin tạo lịch mới
- admin sửa lịch
- admin đổi giảng viên cho lịch
- admin duyệt điều chỉnh lịch phát sinh từ đơn xin nghỉ

==================================================
9. QUY ƯỚC HIỂN THỊ THỜI KHÓA BIỂU
==================================================

Hệ thống phải có 2 kiểu hiển thị:

A. THỜI KHÓA BIỂU
- tiết 1 đến tiết 12
- ngày trong tuần hoặc ngày cụ thể
- hiển thị rõ lịch dạy thực tế
- nếu có đơn xin nghỉ chờ duyệt / đã duyệt thì nên có badge hoặc màu cảnh báo

B. DANH SÁCH
- giảng viên
- ngày
- thứ
- tiết
- khóa học
- module
- trạng thái
- trạng thái đơn xin nghỉ nếu có

==================================================
10. CÁC PHẦN CẦN ĐỌC TRONG REPO TRƯỚC KHI SỬA
==================================================

Bắt buộc đọc code hiện tại liên quan đến:
- LichHoc
- KhoaHoc
- ModuleHoc
- PhanCongModuleGiangVien
- controller admin lịch học
- controller giảng viên xem lịch
- controller giảng viên nhận phân công
- view liên quan tới lịch học và phân công
- nếu đã có bảng hoặc flow gần giống “yêu cầu”, “phản hồi”, “đơn”, hãy kiểm tra có thể tận dụng không

==================================================
11. KIẾN TRÚC CODE MONG MUỐN
==================================================

Không được nhồi toàn bộ logic vào controller.

Ưu tiên tách thành:

- controller cho admin quản lý lịch học
- controller cho giảng viên xem lịch
- controller cho đơn xin nghỉ giảng viên
- request validate riêng
- service kiểm tra khung giờ chuẩn
- service kiểm tra xung đột lịch
- service xử lý đơn xin nghỉ
- service build dữ liệu thời khóa biểu / danh sách

Ví dụ:
- TeacherScheduleRuleService
- TeacherScheduleConflictService
- TeacherLeaveRequestService
- TeacherScheduleViewService

==================================================
12. CHIA THEO PHASE
==================================================

Hãy làm theo từng phase.

PHASE 1:
- đọc code hiện tại
- chỉ ra phần nào đang theo flow cũ “đăng ký lịch rảnh”
- đề xuất cách chuyển sang flow mới
- phân tích file nào cần sửa

PHASE 2:
- chuẩn hóa khung giờ dạy:
  - 08:00 đến 20:00
  - 12 tiết
  - thứ 2 đến thứ 6
- tích hợp rule này vào quản lý lịch học

PHASE 3:
- sửa flow admin tạo/chỉnh lịch học
- kiểm tra ngoài khung giờ
- kiểm tra cuối tuần
- kiểm tra đụng lịch

PHASE 4:
- xây 2 kiểu hiển thị lịch:
  - thời khóa biểu
  - danh sách

PHASE 5:
- xây chức năng giảng viên gửi đơn xin nghỉ / xin off
- màn tạo đơn
- màn danh sách đơn của giảng viên

PHASE 6:
- xây màn admin duyệt / từ chối đơn
- cập nhật trạng thái
- cảnh báo lịch cần xử lý tiếp

PHASE 7:
- test toàn bộ flow
- đảm bảo không phá flow hiện tại:
  - xem lịch dạy
  - cập nhật link học
  - đăng tài nguyên
  - bài kiểm tra
  - điểm danh

==================================================
13. ĐẦU RA TÔI MUỐN
==================================================

Tôi muốn bạn trả ra theo format:

PHẦN A - PHÂN TÍCH
- flow cũ
- flow mới
- file nào cần sửa
- bảng/model nào cần sửa hoặc thêm

PHẦN B - THIẾT KẾ NGHIỆP VỤ
- admin xếp lịch
- giảng viên xem lịch
- giảng viên xin nghỉ
- admin duyệt đơn

PHẦN C - THIẾT KẾ KỸ THUẬT
- migration
- model
- request
- controller
- service
- route
- view

PHẦN D - TRIỂN KHAI CODE
- code theo phase
- giải thích rõ thay đổi

PHẦN E - TEST
- test xếp lịch trong giờ chuẩn
- test xếp lịch ngoài giờ
- test xếp lịch cuối tuần
- test đụng lịch
- test gửi đơn xin nghỉ
- test admin duyệt đơn
- test hiển thị thời khóa biểu và danh sách

==================================================
14. YÊU CẦU CUỐI
==================================================

Hãy bắt đầu bằng việc đọc code thật của repo hiện tại rồi mới sửa.

Mục tiêu cuối cùng:
- không còn flow giảng viên đăng ký lịch rảnh
- admin xếp lịch theo khung mặc định 08:00 - 20:00, 12 tiết, thứ 2 đến thứ 6
- giảng viên xem lịch dạy
- giảng viên có thể gửi đơn xin nghỉ / xin off theo ngày, buổi, tiết
- admin xử lý đơn đó

Không được code mù.
Không được bỏ qua rule chống đụng lịch.
Không được phá flow giảng viên hiện tại.