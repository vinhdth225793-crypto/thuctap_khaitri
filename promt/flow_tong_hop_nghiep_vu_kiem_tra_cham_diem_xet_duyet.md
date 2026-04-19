# Flow tổng hợp nghiệp vụ kiểm tra, chấm điểm, xét duyệt và công bố kết quả

## 1. Mục tiêu

Tài liệu này tổng hợp flow nghiệp vụ cho hệ thống quản lý kiểm tra và xét duyệt kết quả của trung tâm giáo dục, dựa trên nghiệp vụ đã chốt:

- Giảng viên tạo **3 loại bài kiểm tra**:
  - Kiểm tra **cuối khóa**
  - Kiểm tra **theo module**
  - Kiểm tra **theo buổi học**
- Học viên tham gia làm bài và hệ thống lưu **toàn bộ kết quả bài làm**.
- Hệ thống tính **điểm danh theo khóa học** dựa trên số buổi tham gia / tổng số buổi.
- Cuối khóa, giảng viên chọn **phương án xét duyệt** để tính điểm cuối cùng.
- Sau đó giảng viên gửi **admin duyệt**.
- Admin là người **duyệt, chốt và lưu kết quả chính thức**.
- Học viên xem được dữ liệu theo từng nhóm: điểm danh, bài kiểm tra từng loại, điểm trung bình và điểm xét duyệt chính thức.

---

## 2. Vai trò người dùng

### 2.1. Giảng viên

Giảng viên có thể:

- Tạo bài kiểm tra
- Chọn loại bài kiểm tra:
  - cuối khóa
  - theo module
  - theo buổi học
- Xem danh sách học viên đã làm bài
- Xem lịch sử kết quả bài kiểm tra của học viên
- Xem dữ liệu điểm danh theo khóa học
- Lập phiếu xét duyệt cuối khóa
- Chọn phương án tính điểm xét duyệt
- Gửi danh sách xét duyệt cho admin

### 2.2. Học viên

Học viên có thể:

- Làm các bài kiểm tra được giảng viên tạo
- Xem điểm danh theo khóa học
- Xem danh sách bài kiểm tra cuối khóa
- Xem danh sách bài kiểm tra module
- Xem danh sách bài kiểm tra buổi học
- Xem điểm trung bình tham khảo
- Xem điểm xét duyệt chính thức sau khi admin chốt

### 2.3. Admin

Admin có thể:

- Xem toàn bộ thông tin của khóa học, lớp học, học viên
- Xem lịch sử bài kiểm tra của học viên
- Xem dữ liệu điểm danh
- Xem phiếu xét duyệt do giảng viên gửi
- Duyệt, từ chối hoặc chốt kết quả cuối cùng
- Lưu trữ kết quả xét duyệt chính thức

---

## 3. Ba loại bài kiểm tra

### 3.1. Kiểm tra cuối khóa

- Là bài kiểm tra dùng để đánh giá vào cuối khóa học
- Có thể được dùng trực tiếp để xét đậu/rớt
- Khi giảng viên chọn mode xét theo cuối khóa, hệ thống lấy điểm bài này để tính với điểm danh

### 3.2. Kiểm tra theo module

- Gắn với từng module trong khóa học
- Mỗi module có thể có 1 hoặc nhiều bài kiểm tra
- Khi xét cuối khóa, giảng viên có thể chọn một hoặc nhiều bài module để đưa vào tính trung bình

### 3.3. Kiểm tra theo buổi học

- Gắn với từng buổi học cụ thể
- Dùng để kiểm tra mức độ tiếp thu theo từng buổi
- Có thể được giảng viên chọn đưa vào danh sách bài dùng để xét cuối khóa

---

## 4. Dữ liệu cần được lưu

### 4.1. Dữ liệu bài kiểm tra

Mỗi bài kiểm tra cần có:

- Mã bài kiểm tra
- Tên bài kiểm tra
- Loại bài kiểm tra:
  - cuối khóa
  - module
  - buổi học
- Khóa học
- Module hoặc buổi học nếu có
- Thời gian mở / đóng
- Thời gian làm bài
- Thang điểm
- Trạng thái phát hành

### 4.2. Dữ liệu bài làm của học viên

Mỗi lần học viên làm bài cần lưu:

- Học viên
- Bài kiểm tra
- Thời gian bắt đầu
- Thời gian nộp bài
- Điểm bài làm
- Trạng thái chấm
- Chi tiết bài làm nếu cần
- Lịch sử chấm / chỉnh sửa điểm nếu có

### 4.3. Dữ liệu điểm danh theo khóa học

Mỗi học viên trong khóa học cần có:

- Tổng số buổi của khóa học
- Số buổi đã tham gia
- Tỷ lệ tham dự (%)
- Điểm chuyên cần quy đổi thang 10

### 4.4. Dữ liệu phiếu xét duyệt cuối khóa

Mỗi phiếu xét duyệt cần lưu:

- Khóa học / lớp học
- Giảng viên lập phiếu
- Phương án xét duyệt
- Tỷ lệ tính điểm
- Danh sách bài kiểm tra được chọn
- Điểm danh từng học viên
- Điểm kiểm tra dùng để xét của từng học viên
- Điểm xét duyệt tạm
- Trạng thái phiếu
- Người duyệt / người chốt
- Thời gian gửi duyệt / duyệt / chốt
- Ghi chú nếu bị từ chối

---

## 5. Điểm danh và công thức tính điểm danh

### 5.1. Nguyên tắc

Điểm danh được tính theo tỷ lệ số buổi học viên tham gia trên tổng số buổi của khóa học.

### 5.2. Công thức

```text
điểm_chuyên_cần = (số_buổi_tham_gia / tổng_số_buổi_khóa_học) * 10
```

### 5.3. Ví dụ

- Tổng số buổi: 15
- Số buổi tham gia: 12
- Tỷ lệ tham dự: 12 / 15 = 80%
- Điểm chuyên cần: 8.0

---

## 6. Hai phương án xét duyệt cuối khóa

## 6.1. Phương án 1: Xét theo bài kiểm tra cuối khóa + điểm danh

### Mô tả

Giảng viên chọn mode xét theo **cuối khóa**.

Hệ thống sẽ:

1. Lấy điểm bài kiểm tra cuối khóa của học viên
2. Lấy điểm chuyên cần của học viên
3. Tính điểm xét duyệt theo tỷ lệ:
   - 80% điểm kiểm tra
   - 20% điểm danh

### Công thức

```text
điểm_xét_duyệt = (điểm_cuối_khóa * 0.8) + (điểm_chuyên_cần * 0.2)
```

### Khi dùng

Áp dụng cho lớp/khóa học có cách đánh giá đơn giản, chủ yếu dựa trên:

- bài kiểm tra cuối khóa
- chuyên cần

---

## 6.2. Phương án 2: Xét theo các bài kiểm tra module / buổi + điểm danh

### Mô tả

Giảng viên chọn mode xét theo **module / buổi học**.

Hệ thống sẽ:

1. Hiển thị danh sách các bài kiểm tra module
2. Hiển thị danh sách các bài kiểm tra buổi học
3. Giảng viên chọn những bài muốn dùng để xét
4. Hệ thống lấy điểm các bài được chọn của từng học viên
5. Tính trung bình các bài đã chọn
6. Lấy điểm chuyên cần
7. Tính điểm xét duyệt theo tỷ lệ 80/20

### Công thức

```text
điểm_trung_bình_kiểm_tra = tổng_điểm_các_bài_được_chọn / số_bài_được_chọn

điểm_xét_duyệt = (điểm_trung_bình_kiểm_tra * 0.8) + (điểm_chuyên_cần * 0.2)
```

### Khi dùng

Áp dụng cho lớp/khóa học có:

- nhiều module
- nhiều bài kiểm tra theo buổi
- nhu cầu chọn linh hoạt các bài dùng để xét cuối khóa

---

## 7. Quy trình nghiệp vụ tổng thể

## 7.1. Giai đoạn 1: Tạo bài kiểm tra

### Bước 1. Giảng viên tạo bài kiểm tra

Giảng viên tạo bài kiểm tra và chọn 1 trong 3 loại:

- cuối khóa
- module
- buổi học

### Bước 2. Thiết lập thông tin bài kiểm tra

Bao gồm:

- tên bài
- loại bài
- khóa học
- module hoặc buổi nếu có
- thời gian làm bài
- hình thức bài kiểm tra
- trạng thái mở / đóng

---

## 7.2. Giai đoạn 2: Học viên làm bài

### Bước 3. Học viên tham gia làm bài

Học viên nhìn thấy bài kiểm tra phù hợp và bắt đầu làm.

### Bước 4. Hệ thống lưu kết quả bài làm

Hệ thống lưu:

- học viên đã làm bài nào
- loại bài kiểm tra nào
- điểm số
- thời gian làm
- trạng thái nộp / chấm bài

### Bước 5. Giảng viên theo dõi lịch sử kết quả

Giảng viên có thể xem toàn bộ bài học viên đã làm, không ghi đè, không mất lịch sử.

---

## 7.3. Giai đoạn 3: Tính điểm danh

### Bước 6. Hệ thống tổng hợp dữ liệu điểm danh theo khóa học

Với mỗi học viên trong khóa học:

- xác định tổng số buổi học
- xác định số buổi học viên đã tham gia
- tính tỷ lệ tham dự
- quy đổi thành điểm chuyên cần thang 10

---

## 7.4. Giai đoạn 4: Giảng viên lập phiếu xét duyệt cuối khóa

### Bước 7. Giảng viên mở màn hình xét duyệt

Giảng viên chọn khóa học / lớp học cần xét cuối khóa.

### Bước 8. Giảng viên chọn phương án xét duyệt

Có 2 phương án:

- `final_exam_attendance`
- `selected_exams_attendance`

### Bước 9A. Nếu chọn xét theo cuối khóa

Giảng viên chọn 1 bài kiểm tra cuối khóa.

Hệ thống tính cho từng học viên:

- điểm cuối khóa
- điểm chuyên cần
- điểm xét duyệt tạm theo 80/20
- trạng thái đậu/rớt tạm

### Bước 9B. Nếu chọn xét theo module / buổi

Giảng viên tick chọn các bài kiểm tra module / buổi học muốn dùng.

Hệ thống tính cho từng học viên:

- điểm từng bài được chọn
- điểm trung bình các bài được chọn
- điểm chuyên cần
- điểm xét duyệt tạm theo 80/20
- trạng thái đậu/rớt tạm

### Bước 10. Giảng viên lưu nháp phiếu xét duyệt

Hệ thống lưu:

- mode xét duyệt
- danh sách bài được chọn
- công thức tính
- điểm tạm cho từng học viên

### Bước 11. Giảng viên gửi admin duyệt

Trạng thái phiếu chuyển sang **submitted**.

---

## 7.5. Giai đoạn 5: Admin duyệt và chốt

### Bước 12. Admin xem phiếu xét duyệt

Admin nhìn thấy:

- thông tin khóa học
- mode xét duyệt
- danh sách bài được chọn
- điểm danh từng học viên
- điểm kiểm tra dùng để xét
- điểm xét duyệt tạm
- trạng thái tạm thời đậu/rớt

### Bước 13. Admin xử lý phiếu

Admin có thể:

- chuyển sang reviewing
- approved
- rejected
- finalized

### Bước 14. Nếu từ chối

Admin ghi lý do từ chối.

Phiếu chuyển về trạng thái **rejected** để giảng viên chỉnh sửa và gửi lại.

### Bước 15. Nếu chốt chính thức

Khi admin finalized:

- hệ thống lưu điểm xét duyệt chính thức
- hệ thống lưu kết quả đậu / rớt chính thức
- ghi log người duyệt / người chốt / thời gian / ghi chú

---

## 7.6. Giai đoạn 6: Học viên xem kết quả

### Bước 16. Học viên xem bảng điểm danh theo khóa học

Hiển thị:

- tổng số buổi
- số buổi tham gia
- tỷ lệ chuyên cần
- điểm chuyên cần

### Bước 17. Học viên xem bảng bài kiểm tra cuối khóa

Hiển thị danh sách bài cuối khóa đã làm và điểm số.

### Bước 18. Học viên xem bảng bài kiểm tra module

Hiển thị danh sách bài theo module và điểm số.

### Bước 19. Học viên xem bảng bài kiểm tra buổi học

Hiển thị danh sách bài theo buổi học và điểm số.

### Bước 20. Học viên xem bảng điểm trung bình

Đây là **điểm tham khảo**, phản ánh trung bình các bài kiểm tra.

Không phải điểm chính thức nếu admin chưa chốt.

### Bước 21. Học viên xem bảng điểm xét duyệt chính thức

Chỉ hiển thị khi phiếu xét duyệt đã **finalized**.

Bao gồm:

- mode xét duyệt
- điểm kiểm tra dùng để xét
- điểm chuyên cần
- điểm xét duyệt cuối cùng
- kết quả đậu / rớt
- trạng thái đã chốt

---

## 8. Trạng thái nghiệp vụ

## 8.1. Trạng thái phiếu xét duyệt cuối khóa

- `draft` — giảng viên đang lập phiếu
- `submitted` — giảng viên đã gửi admin
- `reviewing` — admin đang xem xét
- `rejected` — admin từ chối, chờ chỉnh sửa
- `approved` — admin đã duyệt
- `finalized` — admin đã chốt chính thức

## 8.2. Trạng thái bài làm

Tùy theo hệ thống hiện có, có thể dùng các trạng thái như:

- đang làm
- đã nộp
- chờ chấm
- đã chấm
- bị hủy / không hợp lệ

---

## 9. Cách xác định đậu / rớt

## 9.1. Rule mặc định đề xuất

- **Đậu** nếu điểm xét duyệt >= 5.0
- **Rớt** nếu điểm xét duyệt < 5.0

## 9.2. Có thể mở rộng sau

Trong các giai đoạn nâng cấp tiếp theo, có thể mở rộng thêm rule như:

- phải đạt tỷ lệ chuyên cần tối thiểu
- bài cuối khóa phải không dưới điểm sàn
- thiếu bài trong danh sách chọn thì chưa đủ điều kiện xét

---

## 10. Dữ liệu hiển thị cho từng vai trò

## 10.1. Màn hình giảng viên

Nên có:

- Danh sách bài kiểm tra đã tạo
  - tab cuối khóa
  - tab module
  - tab buổi học
- Danh sách bài học viên đã làm
- Màn hình lập phiếu xét duyệt cuối khóa
- Màn hình xem preview kết quả tạm
- Màn hình gửi admin duyệt

## 10.2. Màn hình admin

Nên có:

- Danh sách phiếu xét duyệt theo khóa học / lớp học
- Chi tiết phiếu xét duyệt
- Dữ liệu bài kiểm tra được chọn
- Dữ liệu điểm danh
- Kết quả tính điểm từng học viên
- Nút duyệt / từ chối / chốt
- Lịch sử audit

## 10.3. Màn hình học viên

Nên có:

- Bảng điểm danh theo khóa học
- Bảng bài kiểm tra cuối khóa
- Bảng bài kiểm tra module
- Bảng bài kiểm tra buổi học
- Bảng điểm trung bình tham khảo
- Bảng điểm xét duyệt chính thức

---

## 11. Kiến trúc nghiệp vụ nên tách thành các nhóm dữ liệu

## 11.1. Nhóm bài kiểm tra

Lưu thông tin định nghĩa bài kiểm tra:

- loại bài
- khóa học
- module / buổi
- cấu hình bài thi

## 11.2. Nhóm kết quả bài làm

Lưu từng lần học viên làm bài:

- điểm
- trạng thái
- thời gian
- lịch sử chấm

## 11.3. Nhóm điểm danh theo khóa học

Lưu hoặc tính toán:

- tổng số buổi
- số buổi tham gia
- tỷ lệ tham dự
- điểm chuyên cần

## 11.4. Nhóm phiếu xét duyệt cuối khóa

Lưu:

- phương án xét duyệt
- danh sách bài được chọn
- điểm tạm
- trạng thái gửi duyệt / chốt
- điểm chính thức

---

## 12. Hướng triển khai theo phase

## Phase 1 — Chuẩn hóa domain phiếu xét duyệt cuối khóa

Mục tiêu:

- Tách rõ dữ liệu kết quả bài kiểm tra với dữ liệu phiếu xét duyệt cuối khóa
- Tạo cấu trúc phiếu xét duyệt có trạng thái riêng

## Phase 2 — Chuẩn hóa 3 loại bài kiểm tra

Mục tiêu:

- Làm rõ loại bài kiểm tra:
  - cuối khóa
  - module
  - buổi học
- Chuẩn hóa query lấy dữ liệu cho giảng viên xét duyệt

## Phase 3 — Chuẩn hóa service tính điểm danh

Mục tiêu:

- Đóng gói logic attendance thành service dùng lại được
- Dùng thống nhất cho giảng viên, admin và học viên

## Phase 4 — Tính điểm xét theo cuối khóa + điểm danh 8/2

Mục tiêu:

- Hỗ trợ mode xét duyệt `final_exam_attendance`
- Tính điểm xét duyệt tạm cho toàn bộ học viên

## Phase 5 — Tính điểm xét theo nhiều bài module/buổi + điểm danh 8/2

Mục tiêu:

- Hỗ trợ mode xét duyệt `selected_exams_attendance`
- Tính trung bình bài được chọn + điểm danh

## Phase 6 — Màn hình giảng viên lập và gửi phiếu xét duyệt

Mục tiêu:

- Cho giảng viên chọn mode
- Chọn bài
- Xem preview
- Lưu nháp / gửi duyệt

## Phase 7 — Workflow admin duyệt, từ chối, chốt

Mục tiêu:

- Admin xem xét phiếu
- Duyệt / từ chối / chốt
- Lưu kết quả chính thức

## Phase 8 — Màn hình học viên

Mục tiêu:

- Hiển thị dữ liệu rõ ràng
- Phân biệt điểm tham khảo và điểm chính thức

## Phase 9 — Rollout an toàn và tương thích với dữ liệu cũ

Mục tiêu:

- Không phá luồng cũ
- Tận dụng logic hiện có
- Chuyển đổi dần, có backward compatibility

---

## 13. Kết luận

Flow nghiệp vụ này phù hợp với bài toán thực tế của trung tâm giáo dục vì:

- Cho phép giảng viên tạo và quản lý 3 loại bài kiểm tra
- Tận dụng được dữ liệu điểm danh theo khóa học
- Hỗ trợ 2 phương án xét duyệt linh hoạt
- Tách rõ vai trò giảng viên và admin
- Cho học viên nhìn thấy dữ liệu minh bạch theo từng bảng
- Dễ triển khai dần trên codebase hiện có

Tài liệu này có thể dùng làm nền cho:

- đặc tả BA
- prompt cho AI coding assistant
- checklist refactor theo phase
- tài liệu trao đổi giữa dev, giảng viên vận hành và admin trung tâm
