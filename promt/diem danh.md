Bạn là senior Laravel developer + attendance workflow architect.

Tôi muốn bạn đọc code thật của project Laravel hiện tại và CHUẨN HÓA / MỞ RỘNG chức năng **điểm danh buổi học online** theo nghiệp vụ mới dưới đây.

==================================================
1. BỐI CẢNH HIỆN TẠI CỦA HỆ THỐNG
==================================================

Project Laravel hiện tại đã có các thành phần liên quan:
- `LichHoc`
- chi tiết khóa học giảng viên tại route kiểu:
  - `/giang-vien/khoa-hoc/{id}`
- điểm danh buổi học cho học viên ở phía giảng viên:
  - `/giang-vien/buoi-hoc/{lichHocId}/diem-danh`
- live room ở phía giảng viên:
  - `/giang-vien/live-room/{id}`
- admin quản lý khóa học, lịch học, bài giảng, bài kiểm tra

Tôi muốn mở rộng thêm nghiệp vụ **điểm danh giảng viên cho buổi học online**, gắn trực tiếp với từng buổi học trong chi tiết khóa học giảng viên.

==================================================
2. MỤC TIÊU NGHIỆP VỤ MỚI
==================================================

Tôi muốn hệ thống hỗ trợ rõ 2 loại điểm danh:

A. ĐIỂM DANH HỌC VIÊN
- đã có nền hiện tại
- giảng viên điểm danh học viên theo buổi học

B. ĐIỂM DANH GIẢNG VIÊN
- áp dụng cho buổi học online
- khi giảng viên bắt đầu dạy online thì có nút điểm danh để xác nhận đã vào lớp
- hệ thống ghi nhận:
  - giờ mở live / bắt đầu dạy
  - giờ kết thúc live / kết thúc dạy
- gửi thông tin này cho admin theo dõi
- admin có màn riêng để xem log điểm danh giảng viên

Tôi muốn điểm danh giảng viên được xem như:
- xác nhận giảng viên đã vào lớp đúng buổi
- xác nhận giảng viên đã bắt đầu dạy
- xác nhận giảng viên đã kết thúc buổi học

==================================================
3. KỊCH BẢN NGHIỆP VỤ MONG MUỐN
==================================================

Ở màn:
- `http://localhost/thuctap_khaitri/public/giang-vien/khoa-hoc/{id}`

Trong danh sách buổi học, với mỗi `LichHoc`:

- nếu buổi học là online
- thì hiển thị thêm nút / khu thao tác điểm danh giảng viên

Flow mong muốn:

1. Giảng viên vào chi tiết khóa học
2. Ở từng buổi học online, có nút:
   - “Bắt đầu buổi học” hoặc “Điểm danh vào lớp”
3. Khi giảng viên bấm nút đó:
   - hệ thống ghi nhận giờ bắt đầu dạy / giờ mở live
   - đánh dấu giảng viên đã điểm danh vào buổi
4. Khi buổi học kết thúc, giảng viên bấm nút:
   - “Kết thúc buổi học”
5. Hệ thống ghi nhận:
   - giờ kết thúc live / giờ kết thúc dạy
6. Hệ thống lưu log điểm danh giảng viên
7. Admin có thể vào khu vực điểm danh để xem:
   - theo khóa học
   - theo buổi học
   - theo giảng viên
   - xem giờ bắt đầu / giờ kết thúc / trạng thái

==================================================
4. YÊU CẦU CỰC KỲ QUAN TRỌNG
==================================================

- Không được code mù.
- Phải đọc code thật của repo hiện tại trước khi sửa.
- Phải đọc kỹ tối thiểu:
  - `routes/web.php`
  - `App\Http\Controllers\GiangVien\PhanCongController`
  - `App\Http\Controllers\GiangVien\DiemDanhController`
  - `App\Http\Controllers\GiangVien\LiveRoomController`
  - `App\Models\LichHoc`
  - model điểm danh hiện có
  - các view:
    - chi tiết khóa học giảng viên
    - màn điểm danh buổi học
    - live room nếu có liên quan
- Không được phá flow điểm danh học viên hiện có.
- Không được phá liên kết của `LichHoc` với:
  - bài giảng
  - tài nguyên
  - bài kiểm tra
  - live room
  - điểm danh học viên

==================================================
5. MỤC TIÊU THIẾT KẾ
==================================================

Tôi muốn tách rõ:

A. ĐIỂM DANH HỌC VIÊN
- điểm danh danh sách học viên trong buổi học

B. ĐIỂM DANH GIẢNG VIÊN
- ghi nhận việc giảng viên thực sự vào dạy buổi đó
- ghi nhận giờ bắt đầu
- ghi nhận giờ kết thúc
- ghi log vận hành buổi học online

==================================================
6. FLOW GIẢNG VIÊN ĐIỂM DANH BUỔI HỌC ONLINE
==================================================

------------------------------------------
6.1. HIỂN THỊ NÚT ĐIỂM DANH GIẢNG VIÊN
------------------------------------------

Ở màn chi tiết khóa học giảng viên:
- với từng `LichHoc`
- nếu buổi học có `hinh_thuc = online` hoặc logic tương đương
- hiển thị thêm khu thao tác:

Trạng thái ban đầu:
- chưa bắt đầu
- nút: `Bắt đầu buổi học`

Sau khi bắt đầu:
- hiển thị:
  - giờ bắt đầu
  - trạng thái đang dạy
- nút: `Kết thúc buổi học`

Sau khi kết thúc:
- hiển thị:
  - giờ bắt đầu
  - giờ kết thúc
  - trạng thái đã hoàn thành điểm danh giảng viên

------------------------------------------
6.2. FLOW BẤM “BẮT ĐẦU BUỔI HỌC”
------------------------------------------

1. Giảng viên vào đúng buổi học online
2. Bấm nút `Bắt đầu buổi học`
3. Hệ thống kiểm tra:
   - giảng viên có được phân công buổi đó không
   - buổi học có phải online không
   - chưa có log bắt đầu trước đó
4. Nếu hợp lệ:
   - lưu log điểm danh giảng viên
   - lưu `thoi_gian_bat_dau_day`
   - nếu phù hợp thì lưu `thoi_gian_mo_live`
5. Cập nhật trạng thái buổi:
   - giảng viên đã vào lớp
   - đang dạy

------------------------------------------
6.3. FLOW BẤM “KẾT THÚC BUỔI HỌC”
------------------------------------------

1. Giảng viên bấm nút `Kết thúc buổi học`
2. Hệ thống kiểm tra:
   - phải có log bắt đầu trước đó
   - chưa có log kết thúc
3. Nếu hợp lệ:
   - lưu `thoi_gian_ket_thuc_day`
   - nếu phù hợp thì lưu `thoi_gian_tat_live`
   - cập nhật log điểm danh giảng viên là hoàn thành
4. Buổi học chuyển sang trạng thái:
   - đã ghi nhận giảng viên dạy xong buổi

==================================================
7. DỮ LIỆU CẦN LƯU CHO ĐIỂM DANH GIẢNG VIÊN
==================================================

Tôi muốn có dữ liệu lưu riêng cho điểm danh giảng viên, không trộn lẫn với điểm danh học viên.

Bạn hãy kiểm tra repo hiện tại rồi chọn hướng phù hợp nhất.
Nếu chưa có bảng phù hợp, đề xuất tạo bảng mới, ví dụ:

- `diem_danh_giang_vien`
hoặc
- `lich_hoc_giang_vien_logs`
hoặc tên phù hợp

Thông tin nên có:
- id
- lich_hoc_id
- khoa_hoc_id
- module_hoc_id
- giang_vien_id
- hinh_thuc_hoc
- thoi_gian_bat_dau_day nullable
- thoi_gian_ket_thuc_day nullable
- thoi_gian_mo_live nullable
- thoi_gian_tat_live nullable
- trang_thai:
  - chua_bat_dau
  - dang_day
  - da_ket_thuc
- ghi_chu nullable
- nguoi_tao_id nullable nếu cần
- created_at
- updated_at

Nếu hệ thống live room đã có dữ liệu start/end riêng, hãy phân tích xem:
- có thể tận dụng hay đồng bộ thêm không
- nhưng vẫn phải đảm bảo có một log điểm danh giảng viên rõ ràng cho admin xem

==================================================
8. QUAN HỆ VỚI LIVE ROOM
==================================================

Nếu buổi học online có dùng `LiveRoomController`, tôi muốn bạn phân tích và chọn cách hợp lý:

A. CÁCH 1
- nút “Bắt đầu buổi học” chỉ ghi log điểm danh giảng viên
- live room vẫn hoạt động riêng

B. CÁCH 2
- khi giảng viên bấm “Bắt đầu buổi học”
- hệ thống đồng thời:
  - ghi log điểm danh giảng viên
  - và đồng bộ trạng thái live room đang bắt đầu nếu phù hợp

C. CÁCH 3
- nếu hệ thống live room đã có `start/end`
- thì dùng dữ liệu đó để hỗ trợ điểm danh giảng viên
- nhưng vẫn phải có màn quản trị riêng cho log điểm danh giảng viên

Bạn phải đọc code hiện tại và chọn cách phù hợp, ít phá code nhất.

==================================================
9. ADMIN CẦN CÓ MỤC ĐIỂM DANH RIÊNG
==================================================

Tôi muốn admin có thêm một mục quản lý điểm danh.

Mục này phải phân loại rõ:

A. ĐIỂM DANH GIẢNG VIÊN
B. ĐIỂM DANH HỌC VIÊN

------------------------------------------
9.1. ĐIỂM DANH GIẢNG VIÊN
------------------------------------------

Admin phải xem được:
- theo khóa học
- theo module
- theo buổi học
- theo giảng viên

Hiển thị:
- tên khóa học
- module
- buổi học
- giảng viên
- hình thức học
- giờ bắt đầu dạy
- giờ kết thúc dạy
- giờ mở live
- giờ tắt live
- trạng thái
- ghi chú/log

Có bộ lọc:
- theo khóa học
- theo giảng viên
- theo ngày
- theo trạng thái

------------------------------------------
9.2. ĐIỂM DANH HỌC VIÊN
------------------------------------------

Admin cũng cần có khu xem điểm danh học viên theo khóa học.

Tôi không yêu cầu viết lại flow điểm danh học viên, nhưng muốn admin có màn tổng hợp xem được:
- khóa học
- buổi học
- học viên
- trạng thái điểm danh
- giảng viên phụ trách

Nếu repo đã có bảng điểm danh học viên thì tận dụng để build màn hình admin.

==================================================
10. GHI LOG ĐIỂM DANH GIẢNG VIÊN
==================================================

Tôi muốn có log rõ ràng cho giảng viên, ví dụ:

- đã bắt đầu buổi học lúc mấy giờ
- đã kết thúc buổi học lúc mấy giờ
- có mở live không
- có tắt live không
- thời lượng dạy thực tế nếu tính được
- trạng thái điểm danh giảng viên

Nếu phù hợp, có thể tính thêm:
- `tong_thoi_luong_day_phut`
từ giờ bắt đầu và giờ kết thúc

==================================================
11. FLOW ADMIN XEM ĐIỂM DANH
==================================================

------------------------------------------
11.1. MÀN TỔNG QUAN
------------------------------------------

Admin có menu mới ví dụ:
- `Điểm danh`
hoặc
- `Quản lý điểm danh`

Trong đó có 2 tab:
- Điểm danh giảng viên
- Điểm danh học viên

------------------------------------------
11.2. MÀN ĐIỂM DANH GIẢNG VIÊN
------------------------------------------

1. Admin vào tab điểm danh giảng viên
2. Chọn bộ lọc:
   - khóa học
   - giảng viên
   - ngày
   - trạng thái
3. Xem danh sách log điểm danh giảng viên
4. Có thể bấm xem chi tiết 1 buổi

------------------------------------------
11.3. MÀN ĐIỂM DANH HỌC VIÊN
------------------------------------------

1. Admin vào tab điểm danh học viên
2. Chọn bộ lọc theo khóa học / buổi học
3. Xem danh sách học viên và trạng thái điểm danh

==================================================
12. GIAO DIỆN CẦN CÓ
==================================================

A. PHÍA GIẢNG VIÊN
- trong chi tiết khóa học:
  - mỗi buổi học online có nút `Bắt đầu buổi học`
  - sau khi bắt đầu có nút `Kết thúc buổi học`
  - hiển thị trạng thái điểm danh giảng viên
  - hiển thị giờ bắt đầu / giờ kết thúc nếu có

B. PHÍA ADMIN
- menu `Điểm danh`
- tab:
  - Điểm danh giảng viên
  - Điểm danh học viên
- danh sách, bộ lọc, chi tiết

==================================================
13. YÊU CẦU KỸ THUẬT
==================================================

Không được nhồi toàn bộ logic vào controller.

Ưu tiên tách rõ:
- service xử lý điểm danh giảng viên
- service tổng hợp báo cáo điểm danh admin nếu cần

Ví dụ:
- `TeacherAttendanceService`
- `AttendanceReportService`

Tên có thể khác, nhưng phải sạch và dễ bảo trì.

==================================================
14. CHIA THEO PHASE
==================================================

PHASE 1:
- đọc code hiện tại:
  - `PhanCongController`
  - `DiemDanhController`
  - `LiveRoomController`
  - `LichHoc`
  - view chi tiết khóa học giảng viên
- phân tích phần nào tận dụng được
- đề xuất thiết kế dữ liệu và kiến trúc an toàn nhất

PHASE 2:
- thiết kế và tạo dữ liệu log điểm danh giảng viên
- migration/model nếu cần
- relation với `LichHoc`, `KhoaHoc`, `ModuleHoc`, `GiangVien`

PHASE 3:
- sửa giao diện chi tiết khóa học giảng viên
- thêm nút:
  - `Bắt đầu buổi học`
  - `Kết thúc buổi học`
- chỉ hiển thị cho buổi online
- hiển thị trạng thái điểm danh giảng viên

PHASE 4:
- viết logic backend cho:
  - bắt đầu buổi học
  - kết thúc buổi học
- lưu log đầy đủ
- đồng bộ với live room nếu phù hợp

PHASE 5:
- tạo khu admin `Điểm danh`
- tab điểm danh giảng viên
- filter theo khóa học / giảng viên / ngày / trạng thái
- hiển thị log điểm danh giảng viên

PHASE 6:
- tạo màn admin xem điểm danh học viên theo khóa học
- tận dụng dữ liệu điểm danh học viên hiện có
- không phá flow cũ

PHASE 7:
- test toàn bộ flow
- đảm bảo:
  - không phá điểm danh học viên hiện tại
  - không phá live room
  - không phá bài giảng / kiểm tra / tài nguyên theo `LichHoc`

==================================================
15. ĐẦU RA TÔI MUỐN
==================================================

Tôi muốn bạn trả ra theo format:

PHẦN A - PHÂN TÍCH HIỆN TRẠNG
- phần điểm danh hiện có của repo
- chỗ nào đã có nền
- chỗ nào thiếu
- phần nào nên tận dụng

PHẦN B - THIẾT KẾ NGHIỆP VỤ
- flow giảng viên bắt đầu buổi học online
- flow giảng viên kết thúc buổi học
- flow admin xem điểm danh giảng viên
- flow admin xem điểm danh học viên

PHẦN C - THIẾT KẾ KỸ THUẬT
- migration/model/service/controller/view cần sửa hoặc thêm
- relation cần có
- logic đồng bộ với live room nếu cần

PHẦN D - TRIỂN KHAI CODE
- code theo từng phase
- giải thích file nào được sửa / thêm

PHẦN E - TEST
- test nút bắt đầu buổi học
- test nút kết thúc buổi học
- test log điểm danh giảng viên
- test admin xem danh sách điểm danh giảng viên
- test admin xem điểm danh học viên
- test không phá flow cũ

==================================================
16. YÊU CẦU CUỐI
==================================================

Hãy bắt đầu bằng việc đọc code thật của repo hiện tại rồi mới làm.

Mục tiêu cuối cùng:
- mỗi buổi học online có điểm danh giảng viên riêng
- ghi nhận giờ bắt đầu / giờ kết thúc / giờ mở live / giờ tắt live
- admin có mục điểm danh riêng để xem:
  - điểm danh giảng viên
  - điểm danh học viên
- không phá các chức năng hiện có của hệ thống

Không được code mù.
Không được làm lại từ đầu nếu repo đã có nền tốt.
Ưu tiên giữ `LichHoc` là trung tâm của buổi học trong toàn hệ thống.
Bạn là senior Laravel developer + attendance workflow architect.

Tôi muốn bạn đọc code thật của project Laravel hiện tại và CHUẨN HÓA / MỞ RỘNG chức năng **điểm danh buổi học online** theo nghiệp vụ mới dưới đây.

==================================================
1. BỐI CẢNH HIỆN TẠI CỦA HỆ THỐNG
==================================================

Project Laravel hiện tại đã có các thành phần liên quan:
- `LichHoc`
- chi tiết khóa học giảng viên tại route kiểu:
  - `/giang-vien/khoa-hoc/{id}`
- điểm danh buổi học cho học viên ở phía giảng viên:
  - `/giang-vien/buoi-hoc/{lichHocId}/diem-danh`
- live room ở phía giảng viên:
  - `/giang-vien/live-room/{id}`
- admin quản lý khóa học, lịch học, bài giảng, bài kiểm tra

Tôi muốn mở rộng thêm nghiệp vụ **điểm danh giảng viên cho buổi học online**, gắn trực tiếp với từng buổi học trong chi tiết khóa học giảng viên.

==================================================
2. MỤC TIÊU NGHIỆP VỤ MỚI
==================================================

Tôi muốn hệ thống hỗ trợ rõ 2 loại điểm danh:

A. ĐIỂM DANH HỌC VIÊN
- đã có nền hiện tại
- giảng viên điểm danh học viên theo buổi học

B. ĐIỂM DANH GIẢNG VIÊN
- áp dụng cho buổi học online
- khi giảng viên bắt đầu dạy online thì có nút điểm danh để xác nhận đã vào lớp
- hệ thống ghi nhận:
  - giờ mở live / bắt đầu dạy
  - giờ kết thúc live / kết thúc dạy
- gửi thông tin này cho admin theo dõi
- admin có màn riêng để xem log điểm danh giảng viên

Tôi muốn điểm danh giảng viên được xem như:
- xác nhận giảng viên đã vào lớp đúng buổi
- xác nhận giảng viên đã bắt đầu dạy
- xác nhận giảng viên đã kết thúc buổi học

==================================================
3. KỊCH BẢN NGHIỆP VỤ MONG MUỐN
==================================================

Ở màn:
- `http://localhost/thuctap_khaitri/public/giang-vien/khoa-hoc/{id}`

Trong danh sách buổi học, với mỗi `LichHoc`:

- nếu buổi học là online
- thì hiển thị thêm nút / khu thao tác điểm danh giảng viên

Flow mong muốn:

1. Giảng viên vào chi tiết khóa học
2. Ở từng buổi học online, có nút:
   - “Bắt đầu buổi học” hoặc “Điểm danh vào lớp”
3. Khi giảng viên bấm nút đó:
   - hệ thống ghi nhận giờ bắt đầu dạy / giờ mở live
   - đánh dấu giảng viên đã điểm danh vào buổi
4. Khi buổi học kết thúc, giảng viên bấm nút:
   - “Kết thúc buổi học”
5. Hệ thống ghi nhận:
   - giờ kết thúc live / giờ kết thúc dạy
6. Hệ thống lưu log điểm danh giảng viên
7. Admin có thể vào khu vực điểm danh để xem:
   - theo khóa học
   - theo buổi học
   - theo giảng viên
   - xem giờ bắt đầu / giờ kết thúc / trạng thái

==================================================
4. YÊU CẦU CỰC KỲ QUAN TRỌNG
==================================================

- Không được code mù.
- Phải đọc code thật của repo hiện tại trước khi sửa.
- Phải đọc kỹ tối thiểu:
  - `routes/web.php`
  - `App\Http\Controllers\GiangVien\PhanCongController`
  - `App\Http\Controllers\GiangVien\DiemDanhController`
  - `App\Http\Controllers\GiangVien\LiveRoomController`
  - `App\Models\LichHoc`
  - model điểm danh hiện có
  - các view:
    - chi tiết khóa học giảng viên
    - màn điểm danh buổi học
    - live room nếu có liên quan
- Không được phá flow điểm danh học viên hiện có.
- Không được phá liên kết của `LichHoc` với:
  - bài giảng
  - tài nguyên
  - bài kiểm tra
  - live room
  - điểm danh học viên

==================================================
5. MỤC TIÊU THIẾT KẾ
==================================================

Tôi muốn tách rõ:

A. ĐIỂM DANH HỌC VIÊN
- điểm danh danh sách học viên trong buổi học

B. ĐIỂM DANH GIẢNG VIÊN
- ghi nhận việc giảng viên thực sự vào dạy buổi đó
- ghi nhận giờ bắt đầu
- ghi nhận giờ kết thúc
- ghi log vận hành buổi học online

==================================================
6. FLOW GIẢNG VIÊN ĐIỂM DANH BUỔI HỌC ONLINE
==================================================

------------------------------------------
6.1. HIỂN THỊ NÚT ĐIỂM DANH GIẢNG VIÊN
------------------------------------------

Ở màn chi tiết khóa học giảng viên:
- với từng `LichHoc`
- nếu buổi học có `hinh_thuc = online` hoặc logic tương đương
- hiển thị thêm khu thao tác:

Trạng thái ban đầu:
- chưa bắt đầu
- nút: `Bắt đầu buổi học`

Sau khi bắt đầu:
- hiển thị:
  - giờ bắt đầu
  - trạng thái đang dạy
- nút: `Kết thúc buổi học`

Sau khi kết thúc:
- hiển thị:
  - giờ bắt đầu
  - giờ kết thúc
  - trạng thái đã hoàn thành điểm danh giảng viên

------------------------------------------
6.2. FLOW BẤM “BẮT ĐẦU BUỔI HỌC”
------------------------------------------

1. Giảng viên vào đúng buổi học online
2. Bấm nút `Bắt đầu buổi học`
3. Hệ thống kiểm tra:
   - giảng viên có được phân công buổi đó không
   - buổi học có phải online không
   - chưa có log bắt đầu trước đó
4. Nếu hợp lệ:
   - lưu log điểm danh giảng viên
   - lưu `thoi_gian_bat_dau_day`
   - nếu phù hợp thì lưu `thoi_gian_mo_live`
5. Cập nhật trạng thái buổi:
   - giảng viên đã vào lớp
   - đang dạy

------------------------------------------
6.3. FLOW BẤM “KẾT THÚC BUỔI HỌC”
------------------------------------------

1. Giảng viên bấm nút `Kết thúc buổi học`
2. Hệ thống kiểm tra:
   - phải có log bắt đầu trước đó
   - chưa có log kết thúc
3. Nếu hợp lệ:
   - lưu `thoi_gian_ket_thuc_day`
   - nếu phù hợp thì lưu `thoi_gian_tat_live`
   - cập nhật log điểm danh giảng viên là hoàn thành
4. Buổi học chuyển sang trạng thái:
   - đã ghi nhận giảng viên dạy xong buổi

==================================================
7. DỮ LIỆU CẦN LƯU CHO ĐIỂM DANH GIẢNG VIÊN
==================================================

Tôi muốn có dữ liệu lưu riêng cho điểm danh giảng viên, không trộn lẫn với điểm danh học viên.

Bạn hãy kiểm tra repo hiện tại rồi chọn hướng phù hợp nhất.
Nếu chưa có bảng phù hợp, đề xuất tạo bảng mới, ví dụ:

- `diem_danh_giang_vien`
hoặc
- `lich_hoc_giang_vien_logs`
hoặc tên phù hợp

Thông tin nên có:
- id
- lich_hoc_id
- khoa_hoc_id
- module_hoc_id
- giang_vien_id
- hinh_thuc_hoc
- thoi_gian_bat_dau_day nullable
- thoi_gian_ket_thuc_day nullable
- thoi_gian_mo_live nullable
- thoi_gian_tat_live nullable
- trang_thai:
  - chua_bat_dau
  - dang_day
  - da_ket_thuc
- ghi_chu nullable
- nguoi_tao_id nullable nếu cần
- created_at
- updated_at

Nếu hệ thống live room đã có dữ liệu start/end riêng, hãy phân tích xem:
- có thể tận dụng hay đồng bộ thêm không
- nhưng vẫn phải đảm bảo có một log điểm danh giảng viên rõ ràng cho admin xem

==================================================
8. QUAN HỆ VỚI LIVE ROOM
==================================================

Nếu buổi học online có dùng `LiveRoomController`, tôi muốn bạn phân tích và chọn cách hợp lý:

A. CÁCH 1
- nút “Bắt đầu buổi học” chỉ ghi log điểm danh giảng viên
- live room vẫn hoạt động riêng

B. CÁCH 2
- khi giảng viên bấm “Bắt đầu buổi học”
- hệ thống đồng thời:
  - ghi log điểm danh giảng viên
  - và đồng bộ trạng thái live room đang bắt đầu nếu phù hợp

C. CÁCH 3
- nếu hệ thống live room đã có `start/end`
- thì dùng dữ liệu đó để hỗ trợ điểm danh giảng viên
- nhưng vẫn phải có màn quản trị riêng cho log điểm danh giảng viên

Bạn phải đọc code hiện tại và chọn cách phù hợp, ít phá code nhất.

==================================================
9. ADMIN CẦN CÓ MỤC ĐIỂM DANH RIÊNG
==================================================

Tôi muốn admin có thêm một mục quản lý điểm danh.

Mục này phải phân loại rõ:

A. ĐIỂM DANH GIẢNG VIÊN
B. ĐIỂM DANH HỌC VIÊN

------------------------------------------
9.1. ĐIỂM DANH GIẢNG VIÊN
------------------------------------------

Admin phải xem được:
- theo khóa học
- theo module
- theo buổi học
- theo giảng viên

Hiển thị:
- tên khóa học
- module
- buổi học
- giảng viên
- hình thức học
- giờ bắt đầu dạy
- giờ kết thúc dạy
- giờ mở live
- giờ tắt live
- trạng thái
- ghi chú/log

Có bộ lọc:
- theo khóa học
- theo giảng viên
- theo ngày
- theo trạng thái

------------------------------------------
9.2. ĐIỂM DANH HỌC VIÊN
------------------------------------------

Admin cũng cần có khu xem điểm danh học viên theo khóa học.

Tôi không yêu cầu viết lại flow điểm danh học viên, nhưng muốn admin có màn tổng hợp xem được:
- khóa học
- buổi học
- học viên
- trạng thái điểm danh
- giảng viên phụ trách

Nếu repo đã có bảng điểm danh học viên thì tận dụng để build màn hình admin.

==================================================
10. GHI LOG ĐIỂM DANH GIẢNG VIÊN
==================================================

Tôi muốn có log rõ ràng cho giảng viên, ví dụ:

- đã bắt đầu buổi học lúc mấy giờ
- đã kết thúc buổi học lúc mấy giờ
- có mở live không
- có tắt live không
- thời lượng dạy thực tế nếu tính được
- trạng thái điểm danh giảng viên

Nếu phù hợp, có thể tính thêm:
- `tong_thoi_luong_day_phut`
từ giờ bắt đầu và giờ kết thúc

==================================================
11. FLOW ADMIN XEM ĐIỂM DANH
==================================================

------------------------------------------
11.1. MÀN TỔNG QUAN
------------------------------------------

Admin có menu mới ví dụ:
- `Điểm danh`
hoặc
- `Quản lý điểm danh`

Trong đó có 2 tab:
- Điểm danh giảng viên
- Điểm danh học viên

------------------------------------------
11.2. MÀN ĐIỂM DANH GIẢNG VIÊN
------------------------------------------

1. Admin vào tab điểm danh giảng viên
2. Chọn bộ lọc:
   - khóa học
   - giảng viên
   - ngày
   - trạng thái
3. Xem danh sách log điểm danh giảng viên
4. Có thể bấm xem chi tiết 1 buổi

------------------------------------------
11.3. MÀN ĐIỂM DANH HỌC VIÊN
------------------------------------------

1. Admin vào tab điểm danh học viên
2. Chọn bộ lọc theo khóa học / buổi học
3. Xem danh sách học viên và trạng thái điểm danh

==================================================
12. GIAO DIỆN CẦN CÓ
==================================================

A. PHÍA GIẢNG VIÊN
- trong chi tiết khóa học:
  - mỗi buổi học online có nút `Bắt đầu buổi học`
  - sau khi bắt đầu có nút `Kết thúc buổi học`
  - hiển thị trạng thái điểm danh giảng viên
  - hiển thị giờ bắt đầu / giờ kết thúc nếu có

B. PHÍA ADMIN
- menu `Điểm danh`
- tab:
  - Điểm danh giảng viên
  - Điểm danh học viên
- danh sách, bộ lọc, chi tiết

==================================================
13. YÊU CẦU KỸ THUẬT
==================================================

Không được nhồi toàn bộ logic vào controller.

Ưu tiên tách rõ:
- service xử lý điểm danh giảng viên
- service tổng hợp báo cáo điểm danh admin nếu cần

Ví dụ:
- `TeacherAttendanceService`
- `AttendanceReportService`

Tên có thể khác, nhưng phải sạch và dễ bảo trì.

==================================================
14. CHIA THEO PHASE
==================================================

PHASE 1:
- đọc code hiện tại:
  - `PhanCongController`
  - `DiemDanhController`
  - `LiveRoomController`
  - `LichHoc`
  - view chi tiết khóa học giảng viên
- phân tích phần nào tận dụng được
- đề xuất thiết kế dữ liệu và kiến trúc an toàn nhất

PHASE 2:
- thiết kế và tạo dữ liệu log điểm danh giảng viên
- migration/model nếu cần
- relation với `LichHoc`, `KhoaHoc`, `ModuleHoc`, `GiangVien`

PHASE 3:
- sửa giao diện chi tiết khóa học giảng viên
- thêm nút:
  - `Bắt đầu buổi học`
  - `Kết thúc buổi học`
- chỉ hiển thị cho buổi online
- hiển thị trạng thái điểm danh giảng viên

PHASE 4:
- viết logic backend cho:
  - bắt đầu buổi học
  - kết thúc buổi học
- lưu log đầy đủ
- đồng bộ với live room nếu phù hợp

PHASE 5:
- tạo khu admin `Điểm danh`
- tab điểm danh giảng viên
- filter theo khóa học / giảng viên / ngày / trạng thái
- hiển thị log điểm danh giảng viên

PHASE 6:
- tạo màn admin xem điểm danh học viên theo khóa học
- tận dụng dữ liệu điểm danh học viên hiện có
- không phá flow cũ

PHASE 7:
- test toàn bộ flow
- đảm bảo:
  - không phá điểm danh học viên hiện tại
  - không phá live room
  - không phá bài giảng / kiểm tra / tài nguyên theo `LichHoc`

==================================================
15. ĐẦU RA TÔI MUỐN
==================================================

Tôi muốn bạn trả ra theo format:

PHẦN A - PHÂN TÍCH HIỆN TRẠNG
- phần điểm danh hiện có của repo
- chỗ nào đã có nền
- chỗ nào thiếu
- phần nào nên tận dụng

PHẦN B - THIẾT KẾ NGHIỆP VỤ
- flow giảng viên bắt đầu buổi học online
- flow giảng viên kết thúc buổi học
- flow admin xem điểm danh giảng viên
- flow admin xem điểm danh học viên

PHẦN C - THIẾT KẾ KỸ THUẬT
- migration/model/service/controller/view cần sửa hoặc thêm
- relation cần có
- logic đồng bộ với live room nếu cần

PHẦN D - TRIỂN KHAI CODE
- code theo từng phase
- giải thích file nào được sửa / thêm

PHẦN E - TEST
- test nút bắt đầu buổi học
- test nút kết thúc buổi học
- test log điểm danh giảng viên
- test admin xem danh sách điểm danh giảng viên
- test admin xem điểm danh học viên
- test không phá flow cũ

==================================================
16. YÊU CẦU CUỐI
==================================================

Hãy bắt đầu bằng việc đọc code thật của repo hiện tại rồi mới làm.

Mục tiêu cuối cùng:
- mỗi buổi học online có điểm danh giảng viên riêng
- ghi nhận giờ bắt đầu / giờ kết thúc / giờ mở live / giờ tắt live
- admin có mục điểm danh riêng để xem:
  - điểm danh giảng viên
  - điểm danh học viên
- không phá các chức năng hiện có của hệ thống

Không được code mù.
Không được làm lại từ đầu nếu repo đã có nền tốt.
Ưu tiên giữ `LichHoc` là trung tâm của buổi học trong toàn hệ thống.
Ưu tiên làm chắc điểm danh giảng viên cho buổi học online trước, sau đó mới mở rộng màn tổng hợp điểm danh phía admin.

nhớ định dạng utf8 