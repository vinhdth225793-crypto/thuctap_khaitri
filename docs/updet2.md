Bạn đang làm việc trên project Laravel của tôi có tên: `thuctap_khaitri`.

Repo hiện tại của tôi đã có sẵn nhiều chức năng liên quan đến:
- giảng viên
- khóa học
- module
- lịch học
- bài giảng
- tài nguyên
- bài kiểm tra

Hiện tại tôi muốn bạn tập trung đúng vào màn hình:

`/giang-vien/khoa-hoc/{id}`

Ví dụ local:
`http://localhost/thuctap_khaitri/public/giang-vien/khoa-hoc/9`

## 1. YÊU CẦU BẮT BUỘC TRƯỚC KHI CODE

Trước khi sửa code, hãy đọc kỹ các file liên quan hiện có trong project, tối thiểu gồm:

### Route
- `routes/web.php`

### Controller
- `app/Http/Controllers/GiangVien/PhanCongController.php`
- `app/Http/Controllers/GiangVien/TaiNguyenController.php`
- `app/Http/Controllers/GiangVien/BaiGiangController.php`

### View
- `resources/views/pages/giang-vien/phan-cong/show.blade.php`
- `resources/views/pages/giang-vien/phan-cong/index.blade.php`
- `resources/views/pages/giang-vien/phan-cong/_course_card.blade.php`

### Model
- `app/Models/KhoaHoc.php`
- `app/Models/LichHoc.php`
- `app/Models/PhanCongModuleGiangVien.php`
- `app/Models/BaiGiang.php`
- `app/Models/TaiNguyenBuoiHoc.php`

Nếu project hiện tại đã có model hoặc bảng liên quan đến:
- điểm danh giảng viên
- teacher attendance log
- live room
- meeting room
thì phải đọc kỹ và tận dụng, không tạo trùng lặp vô nghĩa.

---

# 2. BỐI CẢNH NGHIỆP VỤ CẦN TRIỂN KHAI

Trong màn hình giảng viên khóa học `/giang-vien/khoa-hoc/{id}`, có khu vực **Lộ trình giảng dạy** hiển thị các buổi học trong module mà giảng viên được phân công.

Tôi muốn chỉnh nghiệp vụ của phần này như sau:

## 2.1. Điểm danh giảng viên cho mọi buổi học
Với **mỗi buổi học trong module**, dù là:
- buổi học online
- hay buổi học trực tiếp

thì **đều phải có phần điểm danh giảng viên**.

Điểm danh giảng viên là theo từng `lich_hoc`, không phải theo cả khóa học.

Mỗi buổi học cần thể hiện được:
- trạng thái điểm danh giảng viên
- giờ check-in
- giờ check-out
- nút thao tác phù hợp

## 2.2. Nếu là buổi online
Nếu `lich_hoc` là buổi học `online`, hiện tại hệ thống có nghiệp vụ tạo phòng học online và có link Google Meet.

Tôi muốn bổ sung thêm:
- mỗi buổi online sẽ có **1 phòng live nội bộ trên hệ thống**
- giảng viên có thể bấm vào để **mở thẳng trên hệ thống**
- phòng live này là theo từng buổi học, không dùng chung toàn khóa
- vẫn giữ link Google Meet cũ để tương thích nghiệp vụ hiện tại
- nhưng bổ sung thêm phòng live riêng của hệ thống để demo đồ án chuyên nghiệp hơn

## 2.3. Nếu là buổi trực tiếp
Nếu là buổi `truc_tiep`:
- không cần phòng live
- nhưng vẫn phải có điểm danh giảng viên
- có thể hiển thị thông tin phòng học trực tiếp hoặc ghi chú nếu đã có dữ liệu

---

# 3. MỤC TIÊU CHUNG CỦA CHỨC NĂNG

Hãy nâng cấp phần **Lộ trình giảng dạy** trong `/giang-vien/khoa-hoc/{id}` để:

1. Giao diện rõ nghiệp vụ hơn
2. Mỗi buổi học đều có khu vực điểm danh giảng viên
3. Buổi online có thể tạo và vào phòng live nội bộ
4. Phòng live gắn đúng theo từng `lich_hoc`
5. Không phá vỡ các chức năng cũ đang chạy
6. Dễ demo trên localhost cho đồ án tốt nghiệp

---

# 4. CÁCH LÀM BẮT BUỘC

Bạn phải làm theo **từng phase riêng**, phase nào xong phase đó.

## Nguyên tắc bắt buộc
- Không nhảy phase
- Không gom tất cả vào một lần sửa lớn
- Ở đầu mỗi phase phải phân tích hiện trạng code ngắn gọn
- Liệt kê file sẽ sửa trước khi sửa
- Sau khi code xong phase đó, phải liệt kê:
  - file đã sửa
  - migration mới
  - route mới
  - controller method mới
  - model/quan hệ mới nếu có
  - cách test thủ công trên localhost
- Nếu phase nào chưa đủ dữ liệu hoặc phát hiện code cũ đã có logic tương tự thì phải tận dụng, không tạo dư thừa

---

# 5. TRIỂN KHAI THEO PHASE

---

## PHASE 1 — Chuẩn hóa UI và nghiệp vụ hiển thị của bảng “Lộ trình giảng dạy”

### Mục tiêu
Chỉnh lại phần bảng hoặc danh sách `Lộ trình giảng dạy` trong trang `/giang-vien/khoa-hoc/{id}` để mỗi buổi học hiển thị rõ ràng hơn, dễ thao tác hơn, đúng định hướng nghiệp vụ mới.

### Mỗi buổi học cần hiển thị tối thiểu
#### Nhóm 1: Thông tin buổi học
- số buổi
- tên buổi hoặc tiêu đề buổi
- module
- ngày học
- giờ bắt đầu
- giờ kết thúc
- hình thức học: `online` hoặc `truc_tiep`

#### Nhóm 2: Nếu là buổi online
- link Google Meet hiện tại nếu đã có
- trạng thái phòng live nội bộ:
  - chưa tạo
  - đã tạo
  - đang diễn ra
  - đã kết thúc
- nút thao tác phù hợp:
  - tạo phòng live
  - vào phòng live
  - đóng phòng

#### Nhóm 3: Nếu là buổi trực tiếp
- hiển thị ghi chú trực tiếp hoặc phòng học nếu có
- không hiển thị nút tạo phòng live
- vẫn phải có điểm danh giảng viên

#### Nhóm 4: Điểm danh giảng viên
Áp dụng cho cả online và trực tiếp:
- trạng thái điểm danh:
  - chưa điểm danh
  - đã check-in
  - đã check-out
  - hoàn thành
- giờ check-in
- giờ check-out
- nút check-in / check-out

#### Nhóm 5: Các nghiệp vụ phụ hiện có nếu đang dùng
- tài nguyên buổi học
- bài giảng
- bài kiểm tra
- điểm danh liên quan khác nếu đã có

### Yêu cầu kỹ thuật phase 1
- Chỉ refactor giao diện và cách hiển thị dữ liệu
- Chưa cần triển khai logic phòng live thật
- Chưa cần migration mới ở phase này nếu chưa bắt buộc
- Có thể dùng placeholder cho trạng thái phòng live nếu phase sau mới code thật
- Không nhồi quá nhiều logic vào blade
- Nếu cần, tách partial/component blade cho mỗi dòng buổi học

### Tiêu chí hoàn thành phase 1
- Trang `/giang-vien/khoa-hoc/{id}` nhìn rõ hơn
- Bảng hoặc danh sách lộ trình giảng dạy thể hiện đủ chỗ cho nghiệp vụ mới
- Có vị trí rõ ràng cho điểm danh giảng viên và phòng live
- Không làm hỏng chức năng hiện tại

---

## PHASE 2 — Hoàn thiện nghiệp vụ điểm danh giảng viên theo từng buổi học

### Mục tiêu
Thêm hoặc hoàn thiện chức năng **điểm danh giảng viên** cho từng `lich_hoc`, áp dụng cho cả online và trực tiếp.

### Yêu cầu nghiệp vụ
Mỗi `lich_hoc` cần có dữ liệu điểm danh giảng viên, tối thiểu gồm:
- `id`
- `lich_hoc_id`
- `giang_vien_id`
- `check_in_at`
- `check_out_at`
- `ghi_chu` nullable
- `trang_thai`
- timestamps

### Trạng thái gợi ý
- `chua_diem_danh`
- `da_checkin`
- `da_checkout`
- `hoan_thanh`

### Hành động cần có
- giảng viên check-in
- giảng viên check-out

### Điều kiện nghiệp vụ
- chỉ giảng viên được phân công đúng module mới được thao tác
- không được check-out khi chưa check-in
- không được check-in trùng nhiều lần cho cùng một buổi nếu đã có dữ liệu hợp lệ
- có thể cho phép cập nhật ghi chú nếu cần

### Việc cần làm trong phase 2
1. Kiểm tra project hiện tại đã có bảng/model attendance log của giảng viên chưa
2. Nếu đã có thì tận dụng và chuẩn hóa lại
3. Nếu chưa đủ thì tạo migration mới, model mới, quan hệ mới
4. Thêm route:
   - check-in giảng viên
   - check-out giảng viên
5. Thêm controller method phù hợp
6. Đổ dữ liệu trạng thái vào trang `/giang-vien/khoa-hoc/{id}`
7. Hiển thị badge trạng thái, thời gian và nút thao tác ở từng buổi học

### Yêu cầu kỹ thuật phase 2
- Validate đầy đủ
- Dùng transaction nếu cần
- Flash message rõ ràng
- Không hardcode id
- Code theo style của repo hiện tại

### Tiêu chí hoàn thành phase 2
- Giảng viên có thể check-in / check-out cho từng buổi học
- Buổi online và trực tiếp đều dùng chung attendance logic
- Bảng lộ trình giảng dạy hiển thị đúng trạng thái attendance

---

## PHASE 3 — Tạo phòng live nội bộ theo từng buổi học online

### Mục tiêu
Mỗi `lich_hoc` có hình thức `online` sẽ có một **phòng live nội bộ của hệ thống**.

### Yêu cầu nghiệp vụ
- Buổi online có thể có room riêng
- Mỗi room gắn theo từng `lich_hoc`
- Không dùng room chung cho toàn khóa
- Chỉ áp dụng cho buổi online
- Giữ lại Google Meet link cũ
- Phòng live nội bộ là phần bổ sung mới

### Dữ liệu tối thiểu cần có cho room
Nếu chưa có, thiết kế bảng riêng hoặc giải pháp phù hợp để lưu:
- `id`
- `lich_hoc_id`
- `room_code`
- `room_name`
- `is_active`
- `status`
- `started_at`
- `ended_at`
- `created_by`
- timestamps

### Trạng thái gợi ý cho room
- `chua_tao`
- `da_tao`
- `dang_dien_ra`
- `da_ket_thuc`

### Hành động cần có
- tạo phòng live
- vào phòng live
- đóng phòng / kết thúc phòng

### Route cần có
- route tạo phòng theo `lich_hoc`
- route vào phòng
- route kết thúc phòng

### View cần có
- trang phòng live riêng cho giảng viên
- hiển thị:
  - thông tin buổi học
  - module
  - khóa học
  - giảng viên
  - trạng thái phòng
  - khu vực live placeholder

### Yêu cầu thiết kế
Đây là đồ án nên ưu tiên giải pháp dễ làm, dễ demo:
- có thể làm “phòng live nội bộ” theo hướng session room
- có trang riêng của room
- có layout mô phỏng lớp học online
- chưa cần làm video conference phức tạp ngay ở phase này

### Tiêu chí hoàn thành phase 3
- Buổi online có thể tạo room riêng
- Có nút vào phòng trên trang lộ trình giảng dạy
- Có trang room hiển thị riêng theo từng buổi học

---

## PHASE 4 — Nâng cấp phòng live để mở thẳng trong hệ thống

### Mục tiêu
Khi giảng viên bấm `Vào phòng live`, hệ thống mở một trang nội bộ có trải nghiệm giống lớp học online.

### Giao diện mong muốn
Trang phòng live nên có:
- tiêu đề buổi học
- thông tin khóa học / module / giảng viên
- trạng thái phòng
- khu vực video/live chính
- khu vực chat hoặc ghi chú placeholder
- khu vực danh sách học viên placeholder nếu cần
- nút:
  - bắt đầu buổi học
  - kết thúc buổi học

### Lưu ý kỹ thuật
Vì đây là đồ án, hãy chọn giải pháp khả thi nhất cho Laravel hiện tại:
- có thể làm theo hướng embed
- hoặc mô phỏng internal live room
- hoặc chuẩn bị kiến trúc để sau này thay bằng Jitsi/WebRTC
- ưu tiên dễ demo trên localhost

### Nghiệp vụ
- Giảng viên vào phòng và có thể bấm bắt đầu buổi học
- Khi bắt đầu, cập nhật trạng thái room
- Khi kết thúc, cập nhật `ended_at`, `status`
- Nếu đã kết thúc thì không cho bắt đầu lại nếu không phù hợp nghiệp vụ

### Tiêu chí hoàn thành phase 4
- Trang room usable để demo
- Có trạng thái room rõ ràng
- Có hành động bắt đầu / kết thúc

---

## PHASE 5 — Đồng bộ điểm danh giảng viên với phòng live online

### Mục tiêu
Liên kết attendance của giảng viên với nghiệp vụ phòng live online.

### Logic mong muốn
#### Với buổi online
- khi giảng viên vào phòng hoặc bắt đầu buổi học, nếu chưa check-in thì có thể:
  - tự động check-in
  - hoặc gợi ý check-in
- khi giảng viên kết thúc phòng, nếu chưa check-out thì:
  - tự động check-out
  - hoặc gợi ý check-out

#### Với buổi trực tiếp
- check-in / check-out thủ công ở bảng lộ trình giảng dạy
- không có room live

### Yêu cầu
- Không tạo dữ liệu attendance trùng
- Không làm sai logic giữa online và trực tiếp
- UI phải làm rõ:
  - online có room + attendance
  - trực tiếp chỉ attendance

### Tiêu chí hoàn thành phase 5
- Attendance online có thể đồng bộ theo room
- Attendance trực tiếp vẫn hoạt động bình thường
- Dữ liệu không bị trùng hoặc lệch trạng thái

---

## PHASE 6 — Refactor toàn bộ UI/UX để demo đồ án tốt hơn

### Mục tiêu
Hoàn thiện giao diện phần `Lộ trình giảng dạy` để nhìn chuyên nghiệp, rõ nghiệp vụ, dễ bảo vệ đồ án.

### Yêu cầu UI/UX
Mỗi buổi học nên có 4 cụm rõ ràng:

#### Cụm 1: Thông tin buổi học
- số buổi
- tiêu đề buổi
- module
- ngày giờ
- hình thức

#### Cụm 2: Dạy học
- online:
  - Google Meet link
  - phòng live nội bộ
  - trạng thái room
  - nút tạo / vào / kết thúc
- trực tiếp:
  - phòng học / ghi chú trực tiếp

#### Cụm 3: Điểm danh giảng viên
- badge trạng thái
- giờ vào
- giờ ra
- nút check-in / check-out

#### Cụm 4: Tài nguyên / bài giảng / kiểm tra
- hiển thị gọn
- giữ lại các liên kết chức năng cũ nếu đã có

### Yêu cầu kỹ thuật
- Tách partial blade nếu cần
- Dùng badge, button, card hợp lý
- Controller nên chuẩn hóa data trước khi đổ ra blade
- Hạn chế logic if quá rối trong view

### Tiêu chí hoàn thành phase 6
- Trang rõ ràng
- dễ nhìn
- nghiệp vụ mạch lạc
- phù hợp demo đồ án tốt nghiệp

---

# 6. RÀNG BUỘC QUAN TRỌNG

Bạn phải tuân thủ các ràng buộc sau:

1. Không phá vỡ route và nghiệp vụ cũ đang chạy nếu không thật sự cần
2. Không xóa link Google Meet hiện tại
3. Chỉ giảng viên được phân công đúng module mới được thao tác attendance hoặc room
4. Chỉ buổi `online` mới được tạo phòng live
5. Buổi `truc_tiep` không có room live nhưng vẫn có attendance
6. Không hardcode dữ liệu
7. Validate đầy đủ
8. Nếu phát hiện code cũ đã có `teacherAttendanceLogs` hoặc logic tương tự thì tận dụng thay vì tạo mới vô tội vạ
9. Nếu cần refactor thì làm an toàn, rõ ràng, dễ bảo trì

---

# 7. OUTPUT BẮT BUỘC SAU MỖI PHASE

Sau mỗi phase, bạn phải trả về đầy đủ:

## A. Phân tích hiện trạng
- đã đọc những file nào
- hiện trạng logic đang nằm ở đâu

## B. Danh sách file đã sửa
Ví dụ:
- `routes/web.php`
- `app/Http/Controllers/...`
- `resources/views/...`

## C. Migration / Model / Route / Controller method mới
Liệt kê rõ:
- migration mới
- model mới
- relation mới
- route mới
- controller method mới

## D. Mô tả luồng hoạt động
Mô tả ngắn, dễ hiểu:
- giảng viên bấm gì
- hệ thống xử lý gì
- dữ liệu lưu ở đâu
- view nào hiển thị gì

## E. Cách test thủ công trên localhost
Ví dụ:
1. vào URL nào
2. bấm nút nào
3. mong đợi kết quả gì
4. kiểm tra DB/table nào

---

# 8. CÁCH CODE MONG MUỐN

- Ưu tiên code sạch, dễ đọc
- Không viết logic quá dày trong blade
- Có thể tách partial/component blade
- Dùng eager loading hợp lý
- Nếu thêm relation thì khai báo đủ trong model
- Nếu thêm migration thì đặt tên rõ nghĩa
- Nếu thêm enum trạng thái thì dùng thống nhất
- Nếu phát hiện dữ liệu đang bị lệch kiến trúc thì refactor nhẹ và an toàn

---

# 9. KẾT QUẢ CUỐI CÙNG TÔI MONG MUỐN

Sau khi hoàn tất tất cả phase, tôi muốn màn `/giang-vien/khoa-hoc/{id}` đạt được kết quả sau:

1. Khu vực **Lộ trình giảng dạy** rõ ràng, đẹp, dễ demo
2. Mỗi buổi học đều có **điểm danh giảng viên**
3. Buổi học **online** có thể:
   - giữ Google Meet link cũ
   - tạo phòng live nội bộ
   - vào phòng live trên hệ thống
4. Buổi học **trực tiếp** vẫn có attendance đầy đủ
5. Luồng code rõ ràng, dễ mở rộng sau này cho:
   - điểm danh học viên
   - chat lớp học
   - room thật bằng WebRTC/Jitsi
   - ghi hình buổi học
6. Phù hợp với đồ án hệ thống học tập và kiểm tra online

---

# 10. YÊU CẦU LÀM VIỆC CUỐI CÙNG

Bắt đầu từ **Phase 1**.

Trước khi sửa code:
- hãy phân tích hiện trạng code hiện có
- nêu file sẽ sửa
- sau đó mới tiến hành code cho phase 1

Không được nhảy sang phase 2 nếu phase 1 chưa hoàn tất.