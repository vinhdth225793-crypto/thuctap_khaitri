# Prompt cho AI Agent Code — Quản lý điểm và kết quả thi theo phase

Bạn đang làm việc trên repo Laravel hiện có của tôi.
Mục tiêu là **hoàn thiện triệt để phần tính điểm và quản lý kết quả thi** cho toàn hệ thống, dựa trên repo hiện tại.

---

## Bối cảnh nghiệp vụ

Hệ thống hiện đã có nền cho các phần sau:

- `BaiKiemTra`
- `BaiLamBaiKiemTra`
- `KetQuaHocTap`
- `KetQuaHocTapService`
- `BaiKiemTraScoringService`
- flow giảng viên chấm tự luận
- flow auto-grade trắc nghiệm
- flow refresh kết quả học tập sau khi chấm
- màn giảng viên xem điểm theo bài kiểm tra
- màn giảng viên xem kết quả học tập theo khóa
- học viên có route xem bài kiểm tra và kết quả học tập
- admin có phần quản lý/phê duyệt bài kiểm tra

Tôi muốn hoàn thiện nghiệp vụ **tính điểm và quản lý kết quả thi** cho 3 actor chính:

1. **Giảng viên**
   - Tự luận: giảng viên chấm xong thì lưu điểm lại theo **khóa học → module → bài kiểm tra → học viên → lần làm**.
   - Trắc nghiệm: cũng đi theo cùng pipeline lưu điểm và kết quả.
   - Giảng viên xem được:
     - có bao nhiêu học viên làm bài
     - từng điểm của mỗi học viên
     - từng lần làm bài
     - kết quả tổng hợp theo khóa/module/bài kiểm tra

2. **Admin**
   - Quản lý điểm toàn khóa
   - Xem điểm theo từng học viên
   - Drill down từ khóa → module → bài kiểm tra → từng lần làm

3. **Học viên**
   - Xem được điểm mình đã làm
   - Bao nhiêu lần làm bài thì hiện bấy nhiêu điểm
   - Xem kết quả tổng hợp hiện hành

4. **Cơ chế tính điểm**
   Hệ thống phải hỗ trợ hoặc ít nhất thiết kế sẵn để hỗ trợ các cách tính:
   - Điểm theo từng bài riêng biệt
   - Trung bình các bài kiểm tra trong module
   - Trung bình theo khóa học
   - Có thể cấu hình chỉ lấy một số bài kiểm tra nhất định để tính trung bình
   - Có thể chọn logic theo **điểm cao nhất**, **trung bình**, hoặc logic cấu hình khác nếu nghiệp vụ mở rộng

---

## Yêu cầu bắt buộc

### 1. Làm theo phase, không nhảy cóc
Chỉ làm **1 phase tại 1 thời điểm**.
Không được chuyển sang phase tiếp theo nếu phase hiện tại chưa PASS.

### 2. Sau mỗi phase phải báo cáo đầy đủ
Phải trả lời đúng format:
- Những gì đã làm
- File đã sửa/thêm
- Migration/Model/Service/Controller/View/Route đã thay đổi
- Logic chính đã implement
- Tự kiểm
- Rủi ro còn lại
- Kết luận PASS / CHƯA PASS

### 3. Không refactor lan rộng
Chỉ tập trung vào phần:
- tính điểm
- quản lý kết quả thi
- hiển thị kết quả theo actor
- mở rộng cấu hình tính điểm

### 4. Tái sử dụng tối đa cấu trúc hiện có
Phải ưu tiên tái sử dụng:
- `BaiLamBaiKiemTra`
- `KetQuaHocTap`
- `KetQuaHocTapService`
- `BaiKiemTraScoringService`
- flow chấm bài hiện tại
- flow scoreboard hiện tại của giảng viên

### 5. Không phá backward compatibility
Logic hiện có như:
- lấy **điểm cao nhất** cho bài kiểm tra
- trung bình bài kiểm tra của module
- tổng hợp cấp khóa theo module hoặc theo bài cuối khóa

phải tiếp tục chạy được nếu không bật cấu hình mới.

---

# PHASE 1 — KHẢO SÁT TOÀN BỘ LUỒNG ĐIỂM VÀ KẾT QUẢ THI HIỆN TẠI

## Mục tiêu
Hiểu chính xác repo hiện đang tính điểm và lưu kết quả như thế nào cho từng actor.

## Việc phải làm
1. Đọc và phân tích kỹ tối thiểu các file sau:
   - `app/Models/BaiKiemTra.php`
   - `app/Models/BaiLamBaiKiemTra.php`
   - `app/Models/KetQuaHocTap.php`
   - `app/Services/BaiKiemTraScoringService.php`
   - `app/Services/KetQuaHocTapService.php`
   - `app/Http/Controllers/GiangVien/BaiKiemTraController.php`
   - `app/Http/Controllers/GiangVien/PhanCongController.php`
   - controller học viên liên quan tới bài kiểm tra / kết quả học tập
   - controller admin liên quan tới kết quả học tập nếu có
   - `routes/web.php`
2. Xác định rõ:
   - dữ liệu điểm gốc đang nằm ở đâu
   - dữ liệu kết quả tổng hợp đang nằm ở đâu
   - lúc nào hệ thống refresh kết quả học tập
   - cách tính điểm ở cấp bài kiểm tra / module / khóa
   - actor nào hiện đã có màn hình xem điểm
   - actor nào còn thiếu
3. Lập gap analysis:
   - phần đã có
   - phần còn thiếu
   - phần nào đang cứng logic
   - phần nào cần tách cấu hình

## Output bắt buộc
- sơ đồ flow hiện tại cho giảng viên / học viên / admin
- sơ đồ dữ liệu hiện tại
- mapping model/bảng/route/controller liên quan
- khoảng trống cần triển khai ở phase sau

## Không được làm trong phase này
- Không thêm migration
- Không sửa business logic lớn
- Không thay UI lớn

## Điều kiện PASS
- Đã hiểu toàn bộ pipeline điểm từ bài làm đến kết quả tổng hợp
- Đã xác định rõ các chỗ cần sửa

---

# PHASE 2 — CHUẨN HÓA THIẾT KẾ DỮ LIỆU CHO QUẢN LÝ ĐIỂM VÀ KẾT QUẢ

## Mục tiêu
Bảo đảm cấu trúc dữ liệu đủ để lưu:
- từng lần làm bài
- điểm chính thức của bài kiểm tra
- kết quả module
- kết quả khóa học
- cấu hình tính điểm mở rộng trong tương lai

## Việc phải làm
1. Kiểm tra `bai_lam_bai_kiem_tra`:
   - đã đủ field cho nhiều lần làm chưa
   - đã đủ field cho điểm tổng, điểm tự luận, điểm trắc nghiệm, người chấm, thời gian chấm chưa
2. Kiểm tra `ket_qua_hoc_tap`:
   - có phân biệt được cấp bài kiểm tra / module / khóa chưa
   - có field nào cần bổ sung cho cấu hình và truy vết không, ví dụ:
     - `attempt_strategy_used`
     - `aggregation_strategy_used`
     - `source_attempt_id`
     - `source_attempt_ids`
     - `calculation_metadata`
3. Nếu cần mở rộng khả năng tính điểm theo cấu hình:
   - cân nhắc thêm bảng cấu hình đánh giá theo khóa / module / nhóm bài kiểm tra
   - hoặc thêm JSON config vào thực thể phù hợp
4. Cập nhật model:
   - fillable
   - casts
   - relation
   - constants/enums nội bộ nếu repo đang dùng style đó

## Yêu cầu implementation
- Migration an toàn với dữ liệu cũ
- Không xóa field cũ
- Không đổi tên field cũ đang dùng nếu không thật cần
- Các field mới phải nullable hoặc có default hợp lý

## Tự kiểm bắt buộc
- Migration up/down hợp lệ
- Không phá dữ liệu hiện có
- Có đủ dữ liệu nền để support các phase sau

## Điều kiện PASS
- Dữ liệu đã đủ để lưu và truy vết kết quả ở mọi cấp

---

# PHASE 3 — CHUẨN HÓA RULE TÍNH ĐIỂM Ở CẤP BÀI KIỂM TRA

## Mục tiêu
Làm rõ cách xác định **điểm chính thức của một bài kiểm tra** từ nhiều lần làm bài.

## Rule hiện tại cần giữ tương thích
Theo flow hiện tại, điểm chính thức cấp bài kiểm tra đang lấy theo **điểm cao nhất của các lần làm đã chấm**.

## Mục tiêu mở rộng
Thiết kế để có thể hỗ trợ thêm các chiến lược:
- `highest_score`
- `latest_attempt`
- `average_attempts`
- `first_attempt`
- hoặc chiến lược khác về sau

## Việc phải làm
1. Tách logic xác định điểm chính thức của bài kiểm tra ra service rõ ràng, ví dụ:
   - `ExamResultAggregationService`
   - hoặc tên tương đương phù hợp style repo
2. Chuẩn hóa đầu vào:
   - danh sách attempt đã chấm
   - chiến lược áp dụng
3. Chuẩn hóa đầu ra:
   - điểm chính thức
   - attempt nào được dùng
   - metadata phục vụ hiển thị và audit
4. Sửa `KetQuaHocTapService::refreshForExamStudent()` để dùng service mới thay vì hardcode trực tiếp.

## Yêu cầu implementation
- Giữ default là `highest_score` để không phá logic cũ
- Dễ mở rộng chiến lược mới
- Không nhét logic dày vào controller

## Tự kiểm bắt buộc
- 1 bài có 1 attempt → kết quả đúng
- nhiều attempt → `highest_score` đúng
- nhiều attempt → `latest_attempt` đúng nếu bật cấu hình
- attempt chưa chấm không được dùng sai

## Điều kiện PASS
- Logic xác định điểm chính thức của bài kiểm tra đã tách rõ, dễ test, dễ mở rộng

---

# PHASE 4 — CHUẨN HÓA TÍNH ĐIỂM CẤP MODULE

## Mục tiêu
Chuẩn hóa kết quả cấp module từ các bài kiểm tra trong module và dữ liệu điểm danh.

## Flow hiện tại cần giữ tương thích
Hiện repo đang tính:
- `diem_kiem_tra` cấp module = trung bình các bài kiểm tra của module
- kết hợp với điểm danh để tính `diem_tong_ket`

## Mục tiêu mở rộng
Thiết kế để có thể hỗ trợ:
- trung bình toàn bộ bài kiểm tra trong module
- trung bình chỉ một nhóm bài kiểm tra được chọn
- trung bình top N bài
- chỉ lấy bài bắt buộc
- trọng số khác nhau theo bài kiểm tra nếu cần

## Việc phải làm
1. Tách logic tính điểm module ra service riêng hoặc module hóa rõ hơn trong `KetQuaHocTapService`
2. Thiết kế cấu hình cho module-level aggregation, ví dụ:
   - all_exams_average
   - selected_exams_average
   - weighted_average
   - top_n_average
3. Nếu cần, thêm quan hệ hoặc bảng cấu hình để đánh dấu:
   - bài kiểm tra nào được tính vào module
   - trọng số từng bài
4. Chuẩn hóa metadata lưu vào `ket_qua_hoc_tap.chi_tiet` hoặc field mới để giải thích cách tính.

## Yêu cầu implementation
- Giữ default là logic cũ: average all exams
- Không phá tính điểm danh hiện có
- Có thể audit lại cách tính từ dữ liệu lưu trữ

## Tự kiểm bắt buộc
- module có 1 bài → đúng
- module có nhiều bài → average đúng
- cấu hình chỉ chọn một số bài → tính đúng
- cấu hình weighted → tính đúng nếu có triển khai

## Điều kiện PASS
- Tính điểm module đã rõ ràng, có thể cấu hình, vẫn tương thích logic cũ

---

# PHASE 5 — CHUẨN HÓA TÍNH ĐIỂM CẤP KHÓA HỌC

## Mục tiêu
Làm rõ cách tính điểm toàn khóa cho học viên.

## Flow hiện tại cần giữ tương thích
Repo hiện có 2 kiểu:
- `theo_module`: lấy trung bình điểm tổng kết các module
- kiểu còn lại: lấy điểm bài cuối khóa, thường là điểm cao nhất trong nhóm bài cuối khóa

## Mục tiêu mở rộng
Thiết kế để hỗ trợ:
- average all modules
- average selected modules
- final-exam-based
- weighted modules
- selected exams across course

## Việc phải làm
1. Tách logic tính course result ra service rõ ràng nếu hiện đang nhồi chung quá nhiều trong `KetQuaHocTapService`
2. Chuẩn hóa chiến lược tính điểm cấp khóa
3. Bảo đảm trọng số điểm danh / kiểm tra vẫn hoạt động đúng
4. Lưu metadata mô tả cách tính kết quả khóa

## Yêu cầu implementation
- Không phá logic cũ
- Dễ thêm chiến lược mới
- Có thể debug được từ dữ liệu lưu trong DB

## Tự kiểm bắt buộc
- khóa theo module → tính đúng
- khóa theo final exam → tính đúng
- khóa có cấu hình chọn một số module/bài kiểm tra → tính đúng nếu có triển khai

## Điều kiện PASS
- Kết quả cấp khóa rõ ràng, có cấu hình, dễ truy vết

---

# PHASE 6 — FLOW GIẢNG VIÊN XEM ĐIỂM THEO BÀI KIỂM TRA VÀ THEO HỌC VIÊN

## Mục tiêu
Hoàn thiện màn giảng viên để xem đầy đủ:
- bao nhiêu học viên làm bài
- từng điểm của mỗi học viên
- từng lần làm bài
- trạng thái chấm
- điểm chính thức đang được dùng

## Việc phải làm
1. Rà lại `diemKiemTraIndex()` và các helper scoreboard hiện có
2. Chuẩn hóa dữ liệu hiển thị theo cấu trúc:
   - khóa học
   - module
   - bài kiểm tra
   - học viên
   - danh sách attempts
3. Mỗi học viên ở mỗi bài kiểm tra phải xem được:
   - số lần làm
   - điểm từng lần
   - attempt nào được dùng làm điểm chính thức
   - trạng thái chấm từng lần
4. Nếu cần, thêm màn chi tiết hoặc expandable rows để xem attempt detail
5. Đảm bảo tự luận và trắc nghiệm đi cùng một pipeline hiển thị

## Yêu cầu implementation
- Không phá màn hiện có
- Tận dụng scoreboard hiện tại
- Query phải tránh N+1 nếu có thể

## Tự kiểm bắt buộc
- 1 học viên nhiều attempt hiển thị đúng
- nhiều học viên trong cùng bài hiển thị đúng
- bài tự luận đã chấm và trắc nghiệm auto-grade đều hiển thị đúng

## Điều kiện PASS
- Giảng viên quản lý điểm theo bài kiểm tra và từng học viên đầy đủ

---

# PHASE 7 — FLOW GIẢNG VIÊN XEM KẾT QUẢ TỔNG HỢP THEO KHÓA / MODULE

## Mục tiêu
Hoàn thiện màn kết quả học tập cho giảng viên để thấy được:
- điểm toàn khóa của từng học viên
- điểm từng module
- điểm từng bài kiểm tra
- drill down đến từng lần làm bài

## Việc phải làm
1. Rà lại `PhanCongController::ketQuaHocTap()` hoặc flow tương đương
2. Chuẩn hóa dữ liệu trả về cho view:
   - `course_result`
   - `module_results`
   - `exam_results`
   - raw attempts nếu cần drill down
3. Hiển thị rõ:
   - công thức/tóm tắt cách tính điểm
   - số bài kiểm tra đã hoàn thành
   - trạng thái đạt/chưa đạt
4. Nếu cần, thêm link từ exam result sang attempt list của bài kiểm tra đó

## Yêu cầu implementation
- Tận dụng `ket_qua_hoc_tap` làm nguồn tổng hợp chính
- Không tính toán nặng lặp lại quá nhiều ở view
- Có eager loading hợp lý

## Tự kiểm bắt buộc
- giảng viên xem được kết quả toàn khóa
- xem được từng module
- xem được từng bài kiểm tra
- drill down được đến attempts nếu có triển khai

## Điều kiện PASS
- Màn kết quả tổng hợp cho giảng viên rõ ràng và đủ thông tin

---

# PHASE 8 — FLOW HỌC VIÊN XEM ĐIỂM VÀ LỊCH SỬ LÀM BÀI

## Mục tiêu
Cho học viên xem được:
- mọi lần làm bài của mình
- điểm từng lần
- điểm chính thức hiện đang được dùng
- kết quả tổng hợp theo module/khóa

## Việc phải làm
1. Rà controller/view của học viên liên quan tới:
   - danh sách bài kiểm tra
   - chi tiết bài kiểm tra
   - kết quả học tập
2. Với mỗi bài kiểm tra của học viên, hiển thị:
   - danh sách attempts
   - lần làm thứ mấy
   - thời gian bắt đầu/nộp
   - điểm từng lần
   - trạng thái chấm
   - attempt nào đang được dùng làm điểm chính thức
3. Trong trang kết quả học tập của học viên, hiển thị:
   - course result
   - module results
   - exam results
4. Nếu có cấu hình tính điểm đặc biệt, phải hiện mô tả ngắn để học viên hiểu điểm của mình được tính như thế nào

## Yêu cầu implementation
- Chỉ hiển thị dữ liệu của chính học viên đang đăng nhập
- Không lộ dữ liệu học viên khác
- Giao diện rõ ràng giữa “lịch sử attempts” và “điểm chính thức”

## Tự kiểm bắt buộc
- học viên có nhiều attempts → hiển thị đủ
- học viên xem được điểm chính thức
- học viên xem được kết quả module/khóa

## Điều kiện PASS
- Học viên xem điểm và lịch sử làm bài đầy đủ, rõ ràng

---

# PHASE 9 — FLOW ADMIN QUẢN LÝ ĐIỂM TOÀN KHÓA

## Mục tiêu
Tạo hoặc hoàn thiện màn admin để quản lý điểm toàn khóa theo từng học viên.

## Việc phải làm
1. Kiểm tra repo hiện có đã có route/controller admin cho kết quả học tập chưa
2. Nếu chưa có hoặc chưa đủ, tạo flow admin mới để:
   - chọn khóa học
   - xem danh sách học viên của khóa
   - xem `course_result`, `module_results`, `exam_results`
   - drill down đến attempts
3. Tái sử dụng tối đa logic đang có ở màn giảng viên nếu phù hợp
4. Nếu admin cần export, chuẩn bị cấu trúc dữ liệu sẵn để phase sau có thể cắm export dễ dàng

## Yêu cầu implementation
- Không copy/paste logic lớn từ giảng viên nếu có thể tách service chung
- Phân quyền rõ: admin xem toàn khóa, không bị giới hạn như giảng viên
- Hiển thị rõ cấu trúc khóa → module → bài kiểm tra → attempt

## Tự kiểm bắt buộc
- admin xem được điểm toàn khóa
- xem được theo từng học viên
- xem được kết quả cấp module và bài kiểm tra
- drill down attempt đúng

## Điều kiện PASS
- Admin có màn quản lý điểm toàn khóa hoàn chỉnh

---

# PHASE 10 — CHUẨN HÓA EXPORT / LƯU TRỮ / TRA CỨU KẾT QUẢ

## Mục tiêu
Bảo đảm dữ liệu điểm và kết quả thi có thể lưu trữ, tra cứu, và export dễ dàng.

## Việc phải làm
1. Chuẩn hóa output data structure cho các loại báo cáo:
   - báo cáo tổng theo khóa
   - báo cáo theo module
   - báo cáo theo bài kiểm tra
   - báo cáo theo attempt
2. Nếu repo đã có export/report của giảng viên, rà và tái sử dụng
3. Đảm bảo mỗi record báo cáo có đủ:
   - thông tin học viên
   - thông tin khóa/module/bài kiểm tra
   - attempt number
   - điểm từng lần
   - điểm chính thức
   - trạng thái chấm
   - chiến lược tính điểm đang áp dụng nếu có
4. Chuẩn hóa metadata và relation để export không phải tính toán lại quá nhiều

## Yêu cầu implementation
- Không hardcode output rời rạc ở nhiều chỗ
- Ưu tiên tạo query/service dùng chung cho báo cáo
- Dễ nối với Excel export sau này

## Tự kiểm bắt buộc
- xuất dữ liệu tổng hợp đúng
- drill down theo bài / attempt đúng
- metadata cách tính điểm không bị mất

## Điều kiện PASS
- Dữ liệu điểm và kết quả thi có thể tra cứu và export ổn định

---

# PHASE 11 — TEST TÍCH HỢP TOÀN BỘ VÀ LÀM SẠCH

## Mục tiêu
Rà toàn bộ flow từ học viên làm bài → chấm bài → tính điểm → tổng hợp kết quả → hiển thị cho từng actor.

## Kịch bản bắt buộc phải test
1. Học viên làm bài trắc nghiệm 1 lần
2. Học viên làm bài trắc nghiệm nhiều lần
3. Học viên làm bài tự luận 1 lần
4. Học viên làm bài tự luận nhiều lần
5. Giảng viên chấm tự luận xong, hệ thống refresh kết quả đúng
6. Trắc nghiệm auto-grade xong, hệ thống refresh kết quả đúng
7. Điểm chính thức cấp bài kiểm tra đúng theo strategy mặc định
8. Kết quả module đúng
9. Kết quả khóa đúng
10. Giảng viên xem scoreboard đúng
11. Học viên xem lịch sử attempts đúng
12. Admin xem điểm toàn khóa đúng
13. Không phá flow cũ nếu không bật cấu hình mới
14. Không có duplicate refresh gây lệch dữ liệu
15. Không lộ dữ liệu sai actor

## Việc phải làm
1. Rà toàn bộ code vừa sửa
2. Loại bỏ duplicate logic
3. Viết test nếu repo có framework test phù hợp
4. Nếu chưa đủ test tự động, viết checklist test tay chi tiết

## Output bắt buộc
- danh sách test case
- pass/fail từng case
- lỗi đã sửa
- TODO còn lại
- kết luận sẵn sàng merge hay chưa

## Điều kiện PASS
- Tất cả flow chính chạy xuyên suốt
- Không còn lỗi logic lớn đã biết
- Không phá backward compatibility quan trọng

---

# FORMAT BÁO CÁO SAU MỖI PHASE

Sau mỗi phase, hãy trả lời đúng mẫu:

## Phase X — [Tên phase]

### 1. Những gì đã làm
- ...

### 2. File đã sửa / thêm
- ...

### 3. Migration / Model / Service / Controller / View / Route đã thay đổi
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

# HƯỚNG DẪN THỰC THI

Bắt đầu từ **PHASE 1**.
Không làm phase khác trước.
Khi hoàn tất phase 1, dừng lại và báo cáo đúng format.
Chỉ tiếp tục phase 2 khi phase 1 đã PASS.
