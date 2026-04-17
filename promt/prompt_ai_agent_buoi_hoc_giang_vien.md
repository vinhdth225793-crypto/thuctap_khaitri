# Prompt cho AI Agent Code — Triển khai nghiệp vụ buổi học cho giảng viên

Bạn đang làm việc trên repo Laravel hiện có của tôi.  
Mục tiêu là triển khai hoàn chỉnh nghiệp vụ **buổi học cho giảng viên** theo flow đã chốt.

---

## Bối cảnh nghiệp vụ cần triển khai

Hệ thống hiện đã có các thành phần liên quan như:

- `LichHoc`
- phân công giảng viên
- start/finish teaching session
- teacher check-in/check-out
- điểm danh học viên
- link online / live room

Tôi muốn nâng cấp nghiệp vụ như sau:

1. Khi admin tạo lịch dạy cho giảng viên, admin có thể nhập sẵn **link Google Meet** cho từng buổi.
2. Mỗi buổi học có **khung giờ dạy cố định** dựa trên lịch đã sinh.
3. Giảng viên được phép:
   - **bắt đầu/check-in trước 30 phút**
   - **check-out/kết thúc trễ tối đa 60 phút**
4. Nếu giảng viên:
   - vào trễ
   - không check-in/check-out trong khung cho phép
   - đóng buổi sớm hơn 30 phút trước giờ kết thúc
   thì hệ thống phải **ghi nhận vi phạm** và **báo admin**.
5. Khi giảng viên mở lớp:
   - nếu admin đã nhập `link_online` thì dùng link đó
   - nếu chưa có thì tạo link mới và lưu lại để học viên thấy
6. Khi giảng viên kết thúc buổi:
   - hệ thống nhắc giảng viên có **15 phút để điểm danh học viên**
   - sau buổi phải có:
     - nhật ký check-in/check-out giảng viên
     - điểm danh học viên theo buổi
7. Toàn bộ logic phải dựa trên **repo hiện có**, ưu tiên **tận dụng cấu trúc sẵn có**, không phá hệ thống cũ nếu chưa cần.

---

## Nguyên tắc làm việc bắt buộc

### 1. Làm theo phase, không nhảy cóc
Chỉ được làm **1 phase tại 1 thời điểm**.  
**Không chuyển phase tiếp theo nếu phase hiện tại chưa hoàn tất và chưa tự kiểm pass.**

### 2. Mỗi phase phải báo cáo đủ
Khi xong mỗi phase, phải trả lời theo format:

- Các file đã sửa
- Các migration đã thêm
- Các class/service/job/request/policy mới đã tạo
- Logic chính đã implement
- Những điểm cần tôi review
- Kết quả tự kiểm
- Những rủi ro còn lại
- Xác nhận phase đã sẵn sàng chuyển tiếp hay chưa

### 3. Không tự ý refactor lan rộng
Chỉ chỉnh những gì phục vụ trực tiếp nghiệp vụ buổi học.  
Không đổi tên class/file lớn nếu không thật cần thiết.  
Không làm “cleanup toàn hệ thống”.

### 4. Ưu tiên code an toàn và rõ ràng
Ưu tiên:
- service riêng cho business logic
- validation rõ ràng
- dùng transaction khi cần
- log đầy đủ
- tránh nhét toàn bộ logic vào controller

### 5. Mỗi phase phải tự test lại
Trước khi kết thúc phase, phải:
- rà route
- rà model relation
- rà migration
- rà validation
- rà các case edge chính
- rà chỗ có thể gây lỗi backward compatibility

### 6. Nếu repo hiện có đã có logic gần đúng
Thì phải **tái sử dụng và mở rộng**, không code lại từ đầu.

---

# PHASE 1 — KHẢO SÁT VÀ CHUẨN HÓA THIẾT KẾ TRIỂN KHAI

## Mục tiêu
Hiểu chính xác repo hiện tại đang có gì liên quan đến:

- `LichHoc`
- admin tạo/sửa lịch học
- start/finish teaching session
- teacher check-in/check-out
- online link / live room
- điểm danh học viên
- notifications / jobs / scheduler nếu có

Sau đó lập kế hoạch implementation chi tiết bám code thật.

## Việc phải làm
1. Đọc và phân tích các file liên quan, tối thiểu gồm:
   - `app/Models/LichHoc.php`
   - controller admin quản lý lịch học
   - controller giảng viên start/finish
   - controller teacher attendance
   - controller điểm danh học viên
   - routes liên quan
   - migration liên quan `lich_hoc` và bảng log/điểm danh
2. Xác định:
   - dữ liệu nào đã có
   - relation nào đã có
   - logic nào đã có thể tái sử dụng
   - logic nào đang xung đột với nghiệp vụ mới
3. Viết ra kế hoạch triển khai cực cụ thể cho các phase sau:
   - cần thêm cột nào
   - cần thêm service nào
   - cần thêm job nào
   - cần sửa method nào trong model/controller
4. Không code business logic lớn ở phase này, chỉ:
   - khảo sát
   - xác nhận thiết kế
   - chỉ bổ sung code nếu thật sự cần để support phase sau

## Output bắt buộc
Phải trả về:
- danh sách file liên quan
- sơ đồ data flow hiện tại
- gap analysis giữa code hiện tại và nghiệp vụ mới
- kế hoạch phase 2 → phase N

## Tiêu chí pass phase
- Đã xác định chính xác nơi cần sửa
- Đã tránh được việc sửa trùng hoặc phá logic cũ
- Đã có plan đủ chi tiết để triển khai phase tiếp

## Không được làm trong phase này
- Không viết migration thật
- Không sửa business logic lớn
- Không thêm job chạy thật
- Không thay đổi flow runtime chính

## Điều kiện chuyển phase
Chỉ chuyển phase 2 khi đã có:
- mapping file rõ ràng
- thiết kế cột/bảng rõ ràng
- danh sách service/job rõ ràng

---

# PHASE 2 — CHUẨN HÓA DỮ LIỆU VÀ CẤU TRÚC NỀN

## Mục tiêu
Bổ sung nền tảng dữ liệu để hỗ trợ nghiệp vụ mới mà chưa đụng mạnh vào flow runtime.

## Việc phải làm
1. Kiểm tra bảng `lich_hoc` hiện tại và bổ sung các cột cần thiết nếu chưa có. Ưu tiên các cột sau:
   - `actual_started_at`
   - `actual_finished_at`
   - `online_link_source`
   - `teacher_monitoring_status`
   - `teacher_monitoring_note`
   - có thể cân nhắc:
     - `allow_open_before_minutes`
     - `allow_close_after_minutes`
     - `attendance_remind_after_finish_minutes`
   nếu muốn cấu hình động theo buổi
2. Kiểm tra bảng log check-in/check-out giảng viên hiện có.
   - Nếu đã có bảng phù hợp thì mở rộng thêm field cần thiết
   - Nếu chưa đủ thì bổ sung field như:
     - `expected_start_at`
     - `expected_end_at`
     - `check_in_status`
     - `check_out_status`
     - `late_minutes`
     - `early_leave_minutes`
     - `flag_reason`
     - `flagged_at`
3. Nếu cần cơ chế cảnh báo admin riêng:
   - tạo bảng alert/notification tracking phù hợp
   - hoặc tái sử dụng notification nếu hệ thống đã có nền
4. Cập nhật model:
   - fillable/casts/constants
   - relation cần thiết
   - helper status constants/enums nội bộ nếu repo đang dùng style đó

## Yêu cầu implementation
- Migration phải an toàn với dữ liệu cũ
- Không xóa cột cũ
- Không đổi tên field cũ đang được dùng nếu chưa có lý do rất mạnh
- Dùng nullable hợp lý để không phá dữ liệu hiện tại
- Nếu có enum/string status mới thì phải thống nhất naming

## Tự kiểm bắt buộc
- Migration up/down hợp lệ
- Không làm hỏng dữ liệu hiện tại
- Model casts đúng kiểu datetime/string
- Relation không bị sai khóa ngoại
- Các cột mới đủ để support các phase sau

## Output bắt buộc khi xong phase
- danh sách migration đã tạo
- model nào đã cập nhật
- bảng nào đã thay đổi
- giải thích tại sao từng field mới là cần thiết

## Điều kiện chuyển phase
Chỉ chuyển phase 3 khi:
- cấu trúc DB đã đủ
- model đã nhận diện đúng các field mới
- không còn thiếu dữ liệu nền cho business rule

---

# PHASE 3 — ĐƯA BUSINESS RULE THỜI GIAN VÀO MODEL / SERVICE

## Mục tiêu
Chuẩn hóa toàn bộ rule thời gian của buổi học theo lịch học.

## Rule bắt buộc
Cho 1 buổi học có:
- `starts_at`
- `ends_at`

Hệ thống phải tính được:
- `teacher_open_window_starts_at = starts_at - 30 phút`
- `teacher_checkout_deadline = ends_at + 60 phút`
- `teacher_early_finish_threshold = ends_at - 30 phút`

Và các rule:
1. Giảng viên chỉ được check-in / bắt đầu buổi trong khung cho phép
2. Giảng viên được check-out / kết thúc trong khung cho phép
3. Nếu kết thúc trước `ends_at - 30 phút` thì bị đánh dấu đóng sớm
4. Nếu quá hạn mà chưa check-in/check-out thì có trạng thái vi phạm

## Việc phải làm
1. Tạo service chuyên xử lý business logic thời gian, ví dụ:
   - `TeachingSessionWindowService`
   - hoặc tên tương đương phù hợp style repo
2. Bổ sung vào `LichHoc` các helper/accessor/method cần thiết, ví dụ:
   - lấy cửa sổ mở lớp
   - lấy deadline checkout
   - kiểm tra có được start/checkin không
   - kiểm tra có được finish/checkout không
   - kiểm tra có bị late/no-show/early-close không
3. Refactor nhẹ các method cũ trong model nếu đang có:
   - `canStartTeachingSession`
   - `canFinishTeachingSession`
   - `canOpenOnlineRoom`
   - `canJoinOnline`
   để dùng chung rule mới, tránh trùng logic

## Yêu cầu implementation
- Không hardcode logic lung tung ở controller
- Rule thời gian phải gom về model/helper/service
- Dùng `Carbon`
- Xử lý timezone nhất quán theo app config
- Tránh lặp công thức thời gian nhiều nơi

## Tự kiểm bắt buộc
Test bằng ít nhất các case:
1. Đúng 30 phút trước giờ học → được phép start
2. Sớm hơn 31 phút → chưa được phép start
3. Đúng giờ học → được start
4. Sau khi kết thúc 10 phút → vẫn được finish/checkout
5. Sau khi kết thúc 61 phút → không còn hợp lệ
6. Kết thúc sớm hơn 30 phút → bị flag
7. Chưa start nhưng đã quá giờ → có thể xác định late/no-show

## Output bắt buộc
- service/business rule nào đã tạo
- method nào đã sửa/thêm
- bảng mapping rule thời gian
- kết quả tự kiểm cho từng case

## Điều kiện chuyển phase
Chỉ chuyển phase 4 khi rule thời gian đã dùng được độc lập và rõ ràng.

---

# PHASE 4 — FLOW ADMIN TẠO LỊCH VÀ QUẢN LÝ LINK ONLINE

## Mục tiêu
Bảo đảm admin khi tạo lịch có thể setup link online cho từng buổi và hệ thống lưu đúng để giảng viên/học viên dùng.

## Việc phải làm
1. Kiểm tra flow admin create/update `LichHoc`
2. Đảm bảo các màn create/update/store/update:
   - nhận `link_online`
   - validate đúng
   - lưu đúng
3. Nếu hiện tại đã có `link_online` thì chỉ cần chuẩn hóa thêm:
   - `online_link_source = admin_manual` khi admin nhập thủ công
4. Nếu admin sửa lịch:
   - giữ link cũ nếu không thay đổi
   - không làm mất nguồn link
5. Nếu có thao tác “apply to all online” hiện hữu:
   - giữ tương thích
   - không phá logic cũ

## Yêu cầu implementation
- Validation link hợp lệ
- Không ép buộc có link nếu lớp không phải online
- Nếu lớp online mà chưa có link thì vẫn chấp nhận, vì phase giảng viên sẽ tạo bổ sung khi mở lớp
- Cập nhật UI/backend theo style hiện tại của repo

## Tự kiểm bắt buộc
- Tạo buổi online có link admin nhập sẵn
- Tạo buổi online chưa có link
- Tạo buổi offline không cần link
- Sửa lịch không làm mất link ngoài ý muốn

## Output bắt buộc
- Các form/request/controller đã sửa
- Logic xác định nguồn link
- Các case đã test tay

## Điều kiện chuyển phase
Chỉ chuyển phase 5 khi flow admin lưu lịch + link online đã ổn.

---

# PHASE 5 — FLOW GIẢNG VIÊN CHECK-IN / BẮT ĐẦU BUỔI HỌC

## Mục tiêu
Khi giảng viên vào dạy, hệ thống xử lý đúng check-in và start session theo khung giờ.

## Việc phải làm
1. Kiểm tra route/controller hiện có cho:
   - check-in
   - start teaching session
2. Thiết kế rule rõ:
   - nếu giảng viên bấm bắt đầu buổi học mà chưa check-in:
     - hệ thống tự check-in luôn
     - hoặc gọi dùng chung service để tạo log check-in
3. Khi start/check-in:
   - validate giảng viên đúng người được phân công
   - validate buổi học chưa bị hủy
   - validate đang trong cửa sổ hợp lệ
4. Ghi log:
   - `check_in_at`
   - `expected_start_at`
   - `check_in_status`
   - `late_minutes` nếu có
5. Cập nhật `lich_hoc`:
   - `actual_started_at`
   - `trang_thai = dang_hoc` nếu phù hợp
6. Tạo service riêng, ví dụ:
   - `TeacherAttendanceService`
   - `TeachingSessionService`

## Yêu cầu implementation
- Không duplicate logic giữa check-in và start
- Nếu check-in rồi mới start thì không tạo log trùng
- Nếu start là hành động chính thì có thể auto tạo check-in
- Xử lý idempotent hợp lý: bấm lại không tạo log loạn

## Tự kiểm bắt buộc
- Start đúng 30 phút trước giờ học → pass
- Start sớm hơn 30 phút → fail đúng cách
- Start đúng giảng viên được phân công → pass
- Start sai giảng viên → fail
- Start nhiều lần không tạo log check-in duplicate
- Start buổi đã hủy → fail

## Output bắt buộc
- Flow runtime check-in/start
- File/controller/service đã sửa
- Cấu trúc log đã ghi như thế nào
- Case duplicate được xử lý ra sao

## Điều kiện chuyển phase
Chỉ chuyển phase 6 khi start/check-in đã chạy đúng và log sạch.

---

# PHASE 6 — FLOW MỞ LỚP ONLINE VÀ TẠO LINK NẾU THIẾU

## Mục tiêu
Khi giảng viên mở lớp online:
- ưu tiên dùng link admin đã setup
- nếu chưa có thì tạo link mới
- lưu lại để học viên truy cập thấy

## Việc phải làm
1. Kiểm tra cơ chế mở lớp online hiện tại:
   - `link_online`
   - live room
   - online entry url
2. Thống nhất rule:
   - nếu `link_online` có sẵn → dùng luôn
   - nếu chưa có → gọi service tạo link mới
   - sau khi tạo xong:
     - lưu lại vào `lich_hoc.link_online`
     - set `online_link_source = teacher_generated` hoặc `system_generated`
3. Đảm bảo học viên nhìn thấy đúng link mới sau khi giảng viên mở lớp
4. Nếu hệ thống hiện chưa tích hợp thật Google Meet API:
   - tạo abstraction service/interface
   - có thể dùng placeholder service hoặc fake provider rõ ràng
   - không hardcode lung tung trong controller

## Yêu cầu implementation
- Tách service tạo link online riêng
- Không phụ thuộc cứng vào provider cụ thể
- Nếu chưa tích hợp thật, ghi chú TODO rõ
- Không overwrite link admin nhập sẵn

## Tự kiểm bắt buộc
- Buổi online có link admin → mở đúng link cũ
- Buổi online chưa có link → tạo link mới và lưu lại
- Buổi offline → không chạy logic tạo link
- Học viên đọc lại buổi học thấy đúng link vừa mở

## Output bắt buộc
- service/link provider đã thêm
- logic chọn link
- cách lưu source của link
- các edge case đã check

## Điều kiện chuyển phase
Chỉ chuyển phase 7 khi luồng mở lớp online đã đúng.

---

# PHASE 7 — FLOW GIẢNG VIÊN CHECK-OUT / KẾT THÚC BUỔI HỌC

## Mục tiêu
Khi giảng viên kết thúc buổi, hệ thống ghi log check-out và xác định có đóng sớm / quá hạn hay không.

## Việc phải làm
1. Kiểm tra route/controller hiện có cho:
   - check-out
   - finish teaching session
2. Dùng chung service với phase 5 nếu phù hợp
3. Khi finish/check-out:
   - ghi `check_out_at`
   - tính `check_out_status`
   - tính `early_leave_minutes` nếu có
4. Cập nhật `lich_hoc`:
   - `actual_finished_at`
   - `trang_thai = hoan_thanh` nếu hợp lệ
5. Nếu finish sớm hơn `ends_at - 30 phút`:
   - gắn `teacher_monitoring_status = dong_som`
   - tạo alert cho admin
6. Nếu finish/check-out trễ nhưng vẫn trong `+60 phút`:
   - cho phép nhưng có thể log trạng thái phù hợp
7. Nếu quá `+60 phút`:
   - xử lý theo rule hệ thống
   - ít nhất phải flag bất thường

## Yêu cầu implementation
- Không tạo duplicate check-out
- Nếu finish rồi thì không cho finish tiếp gây sai dữ liệu
- Phân biệt:
   - “kết thúc buổi”
   - “log chấm công check-out”
  nhưng phải đồng bộ với nhau

## Tự kiểm bắt buộc
- Finish đúng giờ
- Finish trễ 10 phút
- Finish sớm 20 phút → chưa bị flag đóng sớm
- Finish sớm 31 phút → bị flag đóng sớm
- Finish sau deadline 60 phút → xử lý bất thường đúng cách

## Output bắt buộc
- Logic finish/check-out
- Alert đóng sớm hoạt động ra sao
- Đồng bộ giữa trạng thái buổi và log giảng viên

## Điều kiện chuyển phase
Chỉ chuyển phase 8 khi check-out/finish đã đúng.

---

# PHASE 8 — TỰ ĐỘNG GIÁM SÁT VÀ CẢNH BÁO ADMIN

## Mục tiêu
Tự động phát hiện:
- vào trễ
- không dạy
- chưa check-out
- đóng sớm

và báo cho admin.

## Việc phải làm
1. Tạo job/service giám sát định kỳ, ví dụ:
   - `MonitorTeachingSessionsJob`
2. Nếu cần, đăng ký scheduler trong kernel/console routes tùy version Laravel của repo
3. Rule tối thiểu:
   - Sau giờ bắt đầu mà chưa check-in → cảnh báo `vao_tre`
   - Quá `ends_at + 60 phút` mà chưa check-in/start → cảnh báo `khong_day`
   - Quá `ends_at + 60 phút` mà chưa check-out/finish → cảnh báo `chua_checkout`
   - Finish sớm hơn `ends_at - 30 phút` → cảnh báo `dong_som`
4. Tạo notification/alert record cho admin
5. Chống bắn cảnh báo trùng lặp nhiều lần cho cùng 1 buổi và cùng 1 loại vi phạm

## Yêu cầu implementation
- Job phải idempotent
- Không tạo spam notification
- Có cờ đánh dấu đã alert chưa
- Có thể chạy lại an toàn
- Dễ mở rộng thêm loại vi phạm sau này

## Tự kiểm bắt buộc
- Một buổi chưa check-in sau giờ bắt đầu → tạo đúng 1 alert
- Một buổi không dạy → tạo đúng alert tương ứng
- Buổi đã alert rồi, job chạy lại không spam trùng
- Buổi bình thường không bị alert sai

## Output bắt buộc
- Job nào đã tạo
- Scheduler đã đăng ký thế nào
- Cơ chế chống duplicate alert
- Mapping vi phạm → cảnh báo admin

## Điều kiện chuyển phase
Chỉ chuyển phase 9 khi monitoring job chạy đúng logic.

---

# PHASE 9 — NHẮC GIẢNG VIÊN ĐIỂM DANH TRONG 15 PHÚT SAU KHI KẾT THÚC

## Mục tiêu
Sau khi giảng viên kết thúc buổi học, hệ thống nhắc giảng viên có 15 phút để điểm danh và chốt buổi.

## Việc phải làm
1. Khi finish buổi học:
   - tạo notification/in-app reminder cho giảng viên
2. Xác định deadline:
   - `attendance_deadline = actual_finished_at + 15 phút`
   - hoặc fallback hợp lý nếu cần
3. Điều chỉnh flow điểm danh học viên hiện tại:
   - chỉ cho phép điểm danh/chốt báo cáo trong khung hợp lệ
   - nếu quá hạn:
     - hoặc cho phép lưu nhưng gắn cờ `nop_muon`
     - hoặc khóa hẳn tùy cách repo đang phù hợp
4. Nếu có màn hình điểm danh:
   - hiển thị deadline rõ ràng
   - hiển thị trạng thái còn bao lâu

## Yêu cầu implementation
- Không phá flow điểm danh hiện có
- Chỉ bổ sung ràng buộc thời gian và reminder
- Nếu report đã có sẵn thì tận dụng để chốt buổi

## Tự kiểm bắt buộc
- Finish buổi → có nhắc điểm danh
- Trong 15 phút → điểm danh bình thường
- Quá 15 phút → xử lý theo rule mới
- Có log rõ trạng thái nộp đúng hạn / muộn

## Output bắt buộc
- Notification/reminder nào đã thêm
- Logic deadline được đặt ở đâu
- `DiemDanhController` hay service nào đã chỉnh

## Điều kiện chuyển phase
Chỉ chuyển phase 10 khi flow nhắc + deadline điểm danh đã hoạt động.

---

# PHASE 10 — CHỐT DỮ LIỆU CUỐI BUỔI HỌC VÀ TRANG QUẢN TRỊ TRA CỨU

## Mục tiêu
Bảo đảm sau mỗi buổi học, hệ thống luôn truy xuất được đầy đủ hồ sơ buổi dạy.

## Hồ sơ cuối buổi bắt buộc phải có
1. Nhật ký giảng viên:
   - check-in
   - check-out
   - actual start
   - actual finish
   - vào trễ / không dạy / đóng sớm / chưa checkout
2. Điểm danh học viên:
   - danh sách học viên của buổi
   - trạng thái điểm danh từng người
   - thời gian chốt
   - báo cáo giảng viên
3. Cảnh báo admin nếu có vi phạm

## Việc phải làm
1. Rà lại dữ liệu cuối buổi có đủ chưa
2. Nếu admin chưa có nơi xem tổng hợp:
   - bổ sung vào trang chi tiết `lich_hoc`
   - hoặc trang quản trị buổi học
3. Hiển thị rõ:
   - trạng thái vận hành buổi
   - nguồn link online
   - log giảng viên
   - tình trạng điểm danh học viên
   - cảnh báo phát sinh
4. Làm sạch các điểm còn lệch giữa controller/model/service

## Yêu cầu implementation
- Tối ưu để admin debug được từng buổi
- Không cần làm UI đẹp phức tạp, nhưng phải đủ dữ liệu và rõ ràng
- Tránh truy vấn N+1 nếu có thể

## Tự kiểm bắt buộc
- Một buổi học hoàn chỉnh có thể tra ra đủ toàn bộ log
- Admin nhìn vào biết ngay:
   - dạy đúng hay không
   - link nào đã dùng
   - điểm danh đã chốt chưa
   - có cảnh báo gì không

## Output bắt buộc
- màn admin nào đã cập nhật
- dữ liệu tổng hợp cuối buổi gồm những gì
- truy vấn nào đã eager load / tối ưu

## Điều kiện chuyển phase
Chỉ chuyển phase 11 khi dữ liệu cuối buổi đã tra cứu được đầy đủ.

---

# PHASE 11 — KIỂM THỬ TÍCH HỢP, EDGE CASE, VÀ LÀM SẠCH

## Mục tiêu
Chạy rà soát toàn bộ flow từ admin → giảng viên → học viên → admin monitoring.

## Các kịch bản bắt buộc phải test
1. Admin tạo buổi online có link sẵn  
2. Admin tạo buổi online chưa có link  
3. Giảng viên vào sớm 20 phút → được phép  
4. Giảng viên vào sớm 31 phút → không được phép  
5. Giảng viên vào trễ → bị flag  
6. Giảng viên không check-in cả buổi → admin nhận cảnh báo  
7. Giảng viên đóng sớm hơn 30 phút → bị flag  
8. Giảng viên finish xong được nhắc điểm danh  
9. Giảng viên điểm danh trong 15 phút → hợp lệ  
10. Giảng viên điểm danh quá hạn → bị xử lý đúng rule  
11. Học viên nhìn thấy link lớp đúng nguồn  
12. Không tạo duplicate log hoặc duplicate alert  
13. Các flow cũ không liên quan không bị phá

## Việc phải làm
1. Rà toàn bộ code vừa sửa
2. Loại bỏ duplicate logic
3. Bổ sung guard/validation còn thiếu
4. Nếu repo có test framework đang dùng:
   - viết test cho core services/jobs/controllers quan trọng
5. Nếu chưa có test đầy đủ:
   - ít nhất viết checklist test tay chi tiết

## Output bắt buộc
- danh sách test case đã chạy
- test nào pass/fail
- lỗi nào đã sửa
- danh sách TODO còn lại nếu có
- xác nhận hệ thống sẵn sàng merge hay chưa

## Điều kiện hoàn tất toàn bộ task
Chỉ được kết luận hoàn tất khi:
- tất cả phase trước đều đã pass
- không còn lỗi logic lớn đã biết
- flow chính chạy xuyên suốt

---

# Quy tắc phản hồi sau mỗi phase

Sau khi làm xong mỗi phase, hãy trả lời theo đúng mẫu này:

## Phase X — [Tên phase]

### 1. Những gì đã làm
- ...

### 2. File đã sửa / thêm
- ...

### 3. Migration / Model / Service / Job / Controller / View đã thay đổi
- ...

### 4. Logic chính đã implement
- ...

### 5. Tự kiểm
- case 1: pass/fail
- case 2: pass/fail
- ...

### 6. Rủi ro / điểm cần lưu ý
- ...

### 7. Kết luận
- Phase này đã **PASS / CHƯA PASS**
- Lý do
- Chỉ khi PASS mới được chuyển phase tiếp theo

---

# Hướng dẫn thực thi

Bắt đầu từ **PHASE 1**.  
Không làm phase khác trước.  
Khi hoàn tất phase 1, dừng lại và báo cáo đúng mẫu.  
Chỉ tiếp tục phase 2 nếu phase 1 đã PASS.
