Bạn là senior Laravel developer + scheduling system analyst.

Tôi muốn bạn đọc code thật của project Laravel hiện tại và CHỈNH SỬA lại toàn bộ phần **lịch học / buổi học** theo nghiệp vụ mới của trung tâm thực tập.

==================================================
1. BỐI CẢNH NGHIỆP VỤ MỚI
==================================================

Hiện tại hệ thống của tôi đã có:
- khóa học
- module học
- lịch học / buổi học
- phân công giảng viên
- giảng viên xem lịch dạy
- giảng viên cập nhật link học online

Nhưng phần lịch học hiện tại cần được CHUẨN HÓA LẠI theo **mô hình lịch học theo ca** giống cách trung tâm đang vận hành thực tế.

Theo thực tế của trung tâm:

A. KHUNG GIỜ HỌC CHUẨN
1. Ban ngày:
- Sáng: 07:30 - 11:30
- Chiều: 13:30 - 16:30

2. Buổi tối:
- Tối: 18:30 - 20:45

B. MẪU LỊCH HỌC PHỔ BIẾN
- Lớp tối thường học theo:
  - thứ 2 - 4 - 6
  - hoặc thứ 3 - 5 - 7
- Một số lớp nghề học ban ngày:
  - sáng hoặc chiều trong tuần
  - sáng/chiều thứ 7
  - sáng/chiều chủ nhật

C. ĐẶC THÙ HỆ THỐNG TÔI LÀ ONLINE
- không cần quản lý phòng học vật lý kiểu P101, P102...
- thay bằng:
  - link học online
  - nền tảng học
  - meeting id / passcode nếu có
- mục tiêu là lịch học online theo ca, không phải lịch phòng học

==================================================
2. MỤC TIÊU CẦN SỬA
==================================================

Tôi muốn hệ thống chuyển sang quản lý lịch học theo **ca học chuẩn**, thay vì nhập giờ quá tự do.

Cần sửa để:
1. mỗi buổi học phải gắn với một ca học chuẩn
2. admin có thể tạo lịch theo mẫu ca:
   - sáng
   - chiều
   - tối
3. admin có thể áp dụng lịch lặp theo mẫu:
   - 2-4-6
   - 3-5-7
   - hoặc chọn thủ công các thứ học
4. hệ thống tự sinh các buổi học theo ca và ngày học
5. giảng viên và học viên xem lịch học dễ hơn
6. vì là hệ thống online nên thay trọng tâm:
   - từ phòng học vật lý
   - sang link học online của từng buổi

==================================================
3. YÊU CẦU CỰC KỲ QUAN TRỌNG
==================================================

- Không được code mù.
- Phải đọc code thật của repo hiện tại trước khi sửa.
- Phải đọc tối thiểu:
  - `App\Models\LichHoc`
  - `App\Http\Controllers\Admin\LichHocController`
  - service planning / scheduling hiện có nếu có
  - `App\Models\KhoaHoc`
  - `App\Models\ModuleHoc`
  - `App\Models\PhanCongModuleGiangVien`
  - các view admin tạo/sửa lịch học
  - các view giảng viên xem lịch dạy
  - các view học viên xem lịch học
- Không phá các chức năng hiện có:
  - phân công giảng viên
  - bài giảng / tài nguyên theo buổi học
  - bài kiểm tra theo buổi học
  - điểm danh theo buổi học
- Không được bỏ mất liên kết hiện tại của `LichHoc` với:
  - bài giảng
  - tài nguyên
  - bài kiểm tra
  - điểm danh
  - đơn xin nghỉ

==================================================
4. TƯ DUY THIẾT KẾ MỚI CHO LỊCH HỌC
==================================================

Tôi muốn chuẩn hóa `LichHoc` theo hướng:

Mỗi buổi học phải có:
- khóa học
- module học
- giảng viên
- ngày học
- thứ trong tuần
- ca học
- giờ bắt đầu
- giờ kết thúc
- buổi số
- hình thức học
- link online
- nền tảng online
- meeting id / passcode nếu cần
- trạng thái
- ghi chú

Trong đó:
- `ca_hoc` phải là dữ liệu chuẩn hóa
- không nhập giờ học lung tung nếu đã chọn ca học chuẩn

==================================================
5. KHUNG CA HỌC CẦN CHUẨN HÓA
==================================================

Tôi muốn hệ thống chốt 3 ca học chuẩn:

- `sang` = 07:30 - 11:30
- `chieu` = 13:30 - 16:30
- `toi` = 18:30 - 20:45

Nếu cần, có thể tạo:
- enum
- hằng số
- service catalog
- bảng cấu hình ca học

Nhưng phải bám kiến trúc repo hiện tại và chọn giải pháp đơn giản, dễ bảo trì.

Ưu tiên:
- dễ dùng cho admin
- dễ render cho giảng viên và học viên
- dễ sinh lịch tự động

==================================================
6. MẪU LỊCH LẶP CẦN HỖ TRỢ
==================================================

Tôi muốn admin có thể tạo lịch theo các mẫu phổ biến:

A. MẪU TỐI 2-4-6
- thứ 2, thứ 4, thứ 6
- ca tối

B. MẪU TỐI 3-5-7
- thứ 3, thứ 5, thứ 7
- ca tối

C. MẪU BAN NGÀY
- sáng hoặc chiều theo các thứ được chọn thủ công

D. MẪU CUỐI TUẦN
- sáng thứ 7
- chiều thứ 7
- sáng chủ nhật
- chiều chủ nhật

Tôi muốn admin có thể:
- chọn mẫu nhanh
hoặc
- chọn thủ công danh sách thứ học

==================================================
7. FLOW NGHIỆP VỤ MỚI
==================================================

------------------------------------------
7.1. FLOW ADMIN TẠO LỊCH HỌC THEO CA
------------------------------------------

1. Admin vào quản lý lịch học của khóa học
2. Chọn module
3. Chọn giảng viên
4. Chọn cách tạo lịch:
   - tạo 1 buổi thủ công
   - tạo lịch lặp theo mẫu
5. Nếu tạo lịch lặp:
   - chọn ngày bắt đầu
   - chọn ngày kết thúc
   - chọn ca học:
     - sáng
     - chiều
     - tối
   - chọn mẫu ngày học:
     - 2-4-6
     - 3-5-7
     - hoặc chọn thủ công các thứ
   - nhập số buổi nếu cần
6. Hệ thống tự sinh các bản ghi `LichHoc`
7. Mỗi bản ghi tự có:
   - ngày học
   - thứ
   - ca học
   - giờ bắt đầu / kết thúc theo ca chuẩn
   - buổi số
8. Admin lưu lịch
9. Hệ thống kiểm tra xung đột lịch giảng viên trước khi lưu

------------------------------------------
7.2. FLOW ADMIN SỬA 1 BUỔI HỌC
------------------------------------------

1. Admin mở một `LichHoc`
2. Có thể sửa:
   - ngày học
   - ca học
   - giảng viên
   - link online
   - hình thức học
   - ghi chú
3. Nếu đổi ca học:
   - hệ thống tự cập nhật giờ bắt đầu / kết thúc theo ca chuẩn
4. Hệ thống kiểm tra:
   - xung đột giảng viên
   - dữ liệu hợp lệ
5. Lưu cập nhật

------------------------------------------
7.3. FLOW GIẢNG VIÊN XEM LỊCH DẠY
------------------------------------------

Giảng viên phải xem lịch dạy theo cách dễ hiểu hơn.

Hiển thị:
- ngày học
- thứ
- ca học
- giờ học
- khóa học
- module
- buổi số
- link học
- trạng thái buổi học

Nên có:
- dạng danh sách
- nếu phù hợp thì dạng thời khóa biểu

------------------------------------------
7.4. FLOW HỌC VIÊN XEM LỊCH HỌC
------------------------------------------

Học viên phải xem được:
- lịch học theo khóa học đang tham gia
- ngày học
- thứ
- ca học
- giờ học
- module
- giảng viên
- link học online nếu có
- trạng thái buổi học

------------------------------------------
7.5. FLOW GẮN LINK ONLINE CHO BUỔI HỌC
------------------------------------------

Vì hệ thống là online, mỗi buổi học nên hỗ trợ:
- link học online
- nền tảng học
- meeting id
- passcode

Giảng viên hoặc admin có thể cập nhật thông tin này cho từng buổi học.

==================================================
8. QUY TẮC KIỂM TRA XUNG ĐỘT LỊCH
==================================================

Hệ thống phải kiểm tra xung đột khi xếp lịch theo ca.

Một lịch bị xem là xung đột nếu:
- cùng giảng viên
- cùng ngày
- và cùng ca học
hoặc
- giờ học giao nhau

Vì ca học đã chuẩn hóa, rule ưu tiên là:
- giảng viên không được có 2 buổi trùng cùng ngày cùng ca

Nếu vẫn có giờ bắt đầu / kết thúc thủ công ở một số trường hợp, phải kiểm tra overlap giờ như hiện tại.

==================================================
9. THIẾT KẾ KỸ THUẬT MONG MUỐN
==================================================

Tôi muốn bạn phân tích repo hiện tại rồi chọn hướng phù hợp:

A. Nếu `LichHoc` đã có:
- `gio_bat_dau`
- `gio_ket_thuc`
- `buoi_hoc`
- `thu_trong_tuan`

thì ưu tiên:
- thêm `ca_hoc`
- khi chọn `ca_hoc`, tự map ra giờ chuẩn

B. Nếu đã có service chuẩn hóa tiết/buổi học:
- tận dụng
- không viết trùng logic

C. Nếu cần, tạo một service riêng kiểu:
- `CaHocCatalogService`
- hoặc tương đương

Service này chịu trách nhiệm:
- map `ca_hoc` -> giờ bắt đầu / kết thúc
- label hiển thị ca học
- các mẫu lặp phổ biến

==================================================
10. GIAO DIỆN CẦN CÓ
==================================================

A. PHÍA ADMIN
- màn tạo lịch học theo ca
- có chọn:
  - module
  - giảng viên
  - ngày bắt đầu / kết thúc
  - ca học
  - mẫu thứ học
- có thể xem preview lịch trước khi lưu

B. PHÍA GIẢNG VIÊN
- lịch dạy hiển thị theo ca
- rõ:
  - sáng / chiều / tối
  - giờ học
  - link online

C. PHÍA HỌC VIÊN
- lịch học hiển thị theo ca
- rõ:
  - buổi nào
  - giờ nào
  - module nào
  - link học

==================================================
11. NHỮNG GÌ KHÔNG CẦN TRỌNG TÂM
==================================================

Giai đoạn này không cần tập trung vào:
- phòng học vật lý kiểu P101, P102...
- sơ đồ phòng học
- lịch phòng học
- xếp phòng
- quản lý thiết bị phòng học

Vì hệ thống của tôi là học online, trọng tâm phải là:
- ca học
- lịch học
- link học online

==================================================
12. CHIA THEO PHASE
==================================================

PHASE 1:
- đọc code hiện tại của lịch học / buổi học
- phân tích:
  - `LichHoc` hiện đang lưu gì
  - `LichHocController` đang tạo/sửa lịch ra sao
  - service planning hiện có kiểm tra xung đột kiểu gì
- chỉ ra phần nào tận dụng được
- đề xuất hướng sửa an toàn nhất

PHASE 2:
- chuẩn hóa dữ liệu ca học
- thêm `ca_hoc` nếu cần
- tạo mapping:
  - sáng = 07:30 - 11:30
  - chiều = 13:30 - 16:30
  - tối = 18:30 - 20:45
- đảm bảo khi có `ca_hoc` thì giờ học được sinh đúng

PHASE 3:
- sửa form admin tạo/sửa lịch học
- hỗ trợ:
  - chọn ca học
  - chọn mẫu 2-4-6
  - chọn mẫu 3-5-7
  - chọn thủ công các thứ
- thêm preview lịch sẽ sinh

PHASE 4:
- sửa logic tạo lịch lặp
- hệ thống tự sinh `LichHoc` theo:
  - ngày bắt đầu
  - ngày kết thúc
  - ca học
  - mẫu thứ học
- sinh đúng `buoi_so`

PHASE 5:
- tích hợp kiểm tra xung đột giảng viên theo ca học
- không cho lưu lịch bị trùng
- hiển thị lỗi rõ ràng

PHASE 6:
- chuẩn hóa giao diện lịch dạy của giảng viên
- chuẩn hóa giao diện lịch học của học viên
- hiển thị rõ:
  - ca học
  - giờ học
  - link online

PHASE 7:
- test toàn bộ flow
- đảm bảo không phá:
  - bài giảng theo buổi
  - tài nguyên theo buổi
  - bài kiểm tra theo buổi
  - điểm danh theo buổi
  - đơn xin nghỉ theo buổi

==================================================
13. ĐẦU RA TÔI MUỐN
==================================================

Tôi muốn bạn trả ra theo format:

PHẦN A - PHÂN TÍCH HIỆN TRẠNG
- `LichHoc` hiện có gì
- controller lịch học hiện xử lý ra sao
- chỗ nào đang chưa phù hợp với lịch theo ca
- phần nào tận dụng được

PHẦN B - THIẾT KẾ NGHIỆP VỤ
- flow admin tạo lịch theo ca
- flow admin sửa lịch
- flow giảng viên xem lịch dạy
- flow học viên xem lịch học
- flow gắn link online cho buổi học

PHẦN C - THIẾT KẾ KỸ THUẬT
- migration/model/service/controller/view cần sửa hoặc thêm
- logic mapping ca học
- logic tạo lịch lặp
- logic chống xung đột

PHẦN D - TRIỂN KHAI CODE
- code theo từng phase
- giải thích file nào được sửa / thêm

PHẦN E - TEST
- test tạo lịch sáng
- test tạo lịch chiều
- test tạo lịch tối
- test mẫu 2-4-6
- test mẫu 3-5-7
- test mẫu chọn thủ công
- test trùng lịch giảng viên
- test hiển thị lịch cho giảng viên
- test hiển thị lịch cho học viên

==================================================
14. YÊU CẦU CUỐI
==================================================

Hãy bắt đầu bằng việc đọc code thật của repo hiện tại rồi mới sửa.

Mục tiêu cuối cùng:
- hệ thống quản lý lịch học theo ca chuẩn của trung tâm
- ca học gồm sáng / chiều / tối với giờ cố định
- hỗ trợ mẫu lịch 2-4-6, 3-5-7 và chọn thủ công
- hệ thống online nên tập trung vào link học, không phải phòng học vật lý
- không phá các liên kết hiện có của `LichHoc` trong toàn hệ thống

Không được code mù.
Không được làm lại từ đầu nếu repo đã có nền tốt.
Ưu tiên làm chắc từng phase, xong phần nào ổn phần đó rồi mới sang phần tiếp theo.