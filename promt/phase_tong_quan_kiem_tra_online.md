# TỔNG QUAN CÁC PHASE PHÁT TRIỂN MODULE KIỂM TRA ONLINE

## 1. Mục tiêu chung
Xây dựng hoàn chỉnh module **kiểm tra online** cho hệ thống **học tập và kiểm tra online** của đồ án Laravel `thuctap_khaitri`, bám theo nghiệp vụ thực tế tại trung tâm thực tập:

- Có **2 phương thức đánh giá khóa học**:
  - **Kiểm tra cuối khóa**: học hết toàn bộ module, làm 1 bài kiểm tra duy nhất + điểm điểm danh.
  - **Kiểm tra theo module**: mỗi module có 1 bài kiểm tra + điểm điểm danh.
- Có **ngân hàng câu hỏi**.
- Có **import Excel** để thêm nhiều câu hỏi.
- Có **2 loại câu hỏi / bài kiểm tra**:
  - Trắc nghiệm
  - Tự luận
- **Giảng viên tạo đề** nhưng **admin duyệt** rồi mới phát hành.
- **Trắc nghiệm chấm tự động**, **tự luận chấm tay**.
- Điểm tổng kết = **điểm kiểm tra + điểm điểm danh/chuyên cần**.

---

## 2. Cơ sở bám theo repo hiện tại
Repo hiện tại đã có nền tảng để phát triển tiếp:

- Quản lý **khóa học**
- Quản lý **module học**
- Quản lý **lịch học**
- Quản lý **điểm danh**
- Có **bài kiểm tra cơ bản**
- Học viên đã có trang **tiến độ học tập / chuyên cần**
- Đã có phân quyền **admin / giảng viên / học viên**

Vì vậy phần kiểm tra online sẽ được làm theo hướng **mở rộng trên code cũ**, không phá các chức năng đang có.

---

# 3. CÁC PHASE HỆ THỐNG SẼ CÓ GÌ

## PHASE 1 — Chuẩn bị dữ liệu nền cho hệ thống kiểm tra
### Mục tiêu
Tạo nền tảng database và model để các phase sau có thể phát triển đúng nghiệp vụ.

### Hệ thống sẽ có gì sau phase này
- Khóa học có thêm cấu hình:
  - `phuong_thuc_danh_gia`
  - `ty_trong_diem_danh`
  - `ty_trong_kiem_tra`
- Bài kiểm tra có thêm:
  - loại bài kiểm tra (`cuoi_khoa`, `module`)
  - loại nội dung (`trac_nghiem`, `tu_luan`, `hon_hop`)
  - trạng thái duyệt
  - trạng thái phát hành
  - tổng điểm
  - số lần được làm
- Có cấu trúc dữ liệu cho:
  - ngân hàng câu hỏi
  - đáp án câu hỏi
  - chi tiết đề kiểm tra
  - bài làm học viên
  - chi tiết bài làm
  - kết quả học tập tổng hợp

### Kết quả đầu ra
- Migration mới
- Model mới
- Quan hệ Eloquent đầy đủ
- Seeder dữ liệu mẫu

### Ý nghĩa
Đây là phase nền tảng. Chưa cần UI đẹp, chưa cần làm bài ngay, nhưng phải xây khung dữ liệu đúng từ đầu.

---

## PHASE 2 — Xây ngân hàng câu hỏi và import Excel cho admin
### Mục tiêu
Cho admin quản lý kho câu hỏi để dùng lại cho nhiều đề kiểm tra.

### Hệ thống sẽ có gì sau phase này
- Admin có màn hình:
  - danh sách câu hỏi
  - thêm mới
  - sửa
  - xóa / ẩn
  - lọc theo module, loại câu hỏi, mức độ, trạng thái
- Hỗ trợ câu hỏi:
  - trắc nghiệm
  - tự luận
- Với trắc nghiệm:
  - có nhiều đáp án
  - có đáp án đúng
- Có chức năng:
  - tải file Excel mẫu
  - import file Excel để tạo nhiều câu hỏi một lúc
- Dữ liệu lỗi import được báo rõ từng dòng

### Kết quả đầu ra
- Module ngân hàng câu hỏi cho admin
- Controller + route + blade + validate request
- Import Excel hoạt động

### Ý nghĩa
Phase này giúp hệ thống chuyên nghiệp hơn và dễ mở rộng, không phải nhập câu hỏi thủ công từng lần khi tạo đề.

---

## PHASE 3 — Giảng viên tạo đề, admin duyệt, rồi mới phát hành
### Mục tiêu
Xây đúng flow nghiệp vụ kiểm soát đề kiểm tra của trung tâm.

### Hệ thống sẽ có gì sau phase này
- Giảng viên tạo bài kiểm tra theo:
  - khóa học
  - module hoặc cuối khóa
  - loại nội dung: trắc nghiệm / tự luận / hỗn hợp
- Giảng viên chọn câu hỏi từ ngân hàng để đưa vào đề
- Giảng viên có thể cấu hình:
  - thời gian làm bài
  - ngày mở
  - ngày đóng
  - tổng điểm
  - điểm từng câu
  - số lần được làm
- Đề kiểm tra có trạng thái:
  - nháp
  - chờ duyệt
  - đã duyệt
  - từ chối
  - phát hành
  - đóng
- Admin có màn hình duyệt đề:
  - xem danh sách đề chờ duyệt
  - xem chi tiết
  - duyệt
  - từ chối
- Chỉ đề đã duyệt mới được phát hành cho học viên

### Kết quả đầu ra
- Flow tạo đề hoàn chỉnh
- Flow duyệt đề hoàn chỉnh
- Quản lý trạng thái đề kiểm tra

### Ý nghĩa
Đây là phase biến phần bài kiểm tra từ CRUD đơn giản thành đúng nghiệp vụ trung tâm.

---

## PHASE 4 — Học viên làm bài kiểm tra trắc nghiệm và tự luận
### Mục tiêu
Cho học viên vào làm bài thật trên hệ thống.

### Hệ thống sẽ có gì sau phase này
- Học viên xem được danh sách bài kiểm tra đã phát hành
- Học viên chỉ thấy bài kiểm tra của khóa mình đang học
- Học viên bấm bắt đầu làm bài
- Hệ thống tạo bài làm và lưu thời gian bắt đầu
- Giao diện làm bài hỗ trợ:
  - trắc nghiệm
  - tự luận
  - hỗn hợp
- Khi nộp bài:
  - lưu đáp án trắc nghiệm
  - lưu nội dung tự luận
  - lưu thời gian nộp
- Hệ thống tự chấm phần trắc nghiệm
- Bài có tự luận được chuyển sang trạng thái chờ chấm
- Học viên xem được lịch sử bài làm

### Kết quả đầu ra
- Học viên làm được bài kiểm tra online hoàn chỉnh
- Trắc nghiệm chấm tự động
- Tự luận được lưu để chấm sau

### Ý nghĩa
Đây là phase quan trọng nhất về mặt trải nghiệm người dùng học viên.

---

## PHASE 5 — Giảng viên chấm tự luận, tính điểm quá trình và tổng kết
### Mục tiêu
Hoàn thành phần chấm điểm và tổng hợp điểm học tập theo đúng 2 mô hình nghiệp vụ.

### Hệ thống sẽ có gì sau phase này
- Giảng viên có màn hình chấm bài tự luận:
  - danh sách bài chờ chấm
  - xem chi tiết bài làm
  - nhập điểm từng câu
  - nhập nhận xét
- Hệ thống tính điểm tổng kết cho từng học viên theo khóa học
- Hệ thống hỗ trợ 2 công thức:
  1. **Khóa học kiểm tra cuối khóa**
     - Điểm tổng kết = điểm điểm danh + điểm bài cuối khóa
  2. **Khóa học kiểm tra theo module**
     - Điểm tổng kết = điểm điểm danh + trung bình / tổng hợp điểm các module
- Hệ thống quy đổi điểm điểm danh từ dữ liệu điểm danh sẵn có
- Học viên xem được:
  - điểm điểm danh
  - điểm từng bài kiểm tra
  - điểm trung bình module hoặc cuối khóa
  - điểm tổng kết
- Admin/Giảng viên xem được bảng tổng hợp điểm khóa học

### Kết quả đầu ra
- Chức năng chấm tự luận
- Service tính điểm tổng kết
- Màn hình xem kết quả học tập

### Ý nghĩa
Phase này giúp module kiểm tra online liên kết với tiến độ và điểm danh để ra kết quả học tập hoàn chỉnh.

---

## PHASE 6 — Hoàn thiện UX, seed, test và tài liệu kỹ thuật
### Mục tiêu
Dọn hệ thống, kiểm thử, thêm dữ liệu mẫu và viết tài liệu để sẵn sàng demo/bảo vệ.

### Hệ thống sẽ có gì sau phase này
- Rà soát lại toàn bộ route/controller/model/view liên quan phần kiểm tra
- Tối ưu message, validation, phân quyền
- Có seed dữ liệu demo:
  - 1 khóa kiểu cuối khóa
  - 1 khóa kiểu theo module
  - câu hỏi mẫu
  - đề mẫu
  - bài làm mẫu
- Có test tối thiểu cho các flow chính
- Có file tài liệu kỹ thuật trong repo:
  - mô tả flow
  - mô tả bảng dữ liệu
  - hướng dẫn migrate / seed / test
  - tài khoản mẫu

### Kết quả đầu ra
- Hệ thống kiểm tra online chạy end-to-end
- Có dữ liệu demo để trình bày
- Có tài liệu kỹ thuật để bảo vệ đồ án

### Ý nghĩa
Đây là phase chốt, giúp hệ thống ổn định và dễ trình bày với giảng viên hướng dẫn / hội đồng.

---

# 4. THỨ TỰ NÊN LÀM
1. Phase 1 — Dữ liệu nền
2. Phase 2 — Ngân hàng câu hỏi
3. Phase 3 — Tạo đề và duyệt đề
4. Phase 4 — Học viên làm bài
5. Phase 5 — Chấm điểm và tính tổng kết
6. Phase 6 — Test, seed, tài liệu

Làm theo thứ tự này sẽ an toàn nhất vì repo hiện tại đã có nền bài kiểm tra cơ bản, điểm danh, tiến độ học viên.

---

# 5. TÓM TẮT NGẮN GỌN MỖI PHASE
- **Phase 1**: dựng khung dữ liệu.
- **Phase 2**: làm ngân hàng câu hỏi và import Excel.
- **Phase 3**: giảng viên tạo đề, admin duyệt, phát hành đề.
- **Phase 4**: học viên vào làm bài và nộp bài.
- **Phase 5**: giảng viên chấm tự luận, hệ thống tính điểm tổng kết.
- **Phase 6**: hoàn thiện, test, seed, viết tài liệu.

---

# 6. KẾT LUẬN
Nếu hoàn thành đủ 6 phase trên, hệ thống của đồ án sẽ có một module kiểm tra online khá đầy đủ và sát nghiệp vụ thực tế của trung tâm:

- Có quản lý đề kiểm tra
- Có ngân hàng câu hỏi
- Có import Excel
- Có duyệt đề
- Có làm bài online
- Có chấm trắc nghiệm và tự luận
- Có tính điểm tổng kết theo 2 mô hình nghiệp vụ
- Có liên kết với điểm danh và tiến độ học tập

Đây là mức rất ổn để làm đồ án tốt nghiệp vì vừa có chiều sâu nghiệp vụ, vừa có phần kỹ thuật đủ mạnh để trình bày.
