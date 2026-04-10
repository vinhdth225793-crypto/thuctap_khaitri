# Flow chi tiết bài kiểm tra tự luận trong project `thuctap_khaitri`

## 1. Kết luận sau khi đọc code thật

Sau khi rà các `route`, `controller`, `service`, `model`, `view` và `feature test`, kết luận là:

- code hiện tại **không chỉ có trắc nghiệm**
- hệ thống đã có sẵn 3 loại nội dung đề:
  - `trac_nghiem`
  - `tu_luan`
  - `hon_hop`
- phần `tự luận` đang tồn tại theo **2 kiểu nghiệp vụ khác nhau**

### Kiểu A. Tự luận tự do

- đề **không có** dòng nào trong `chi_tiet_bai_kiem_tra`
- giảng viên dùng `mo_ta` để nhập đề bài
- học viên nộp bài vào `bai_lam_bai_kiem_tra.noi_dung_bai_lam`
- bài làm đi thẳng sang `cho_cham`

### Kiểu B. Tự luận theo từng câu hỏi

- đề **có** các câu hỏi trong `chi_tiet_bai_kiem_tra`
- câu hỏi lấy từ `ngan_hang_cau_hoi.loai_cau_hoi = tu_luan`
- học viên trả lời từng câu ở `chi_tiet_bai_lam_bai_kiem_tra.cau_tra_loi_text`
- giảng viên chấm từng câu bằng `diem_tu_luan`

### Kiểu C. Đề hỗn hợp

- đề có cả `trac_nghiem` và `tu_luan`
- trắc nghiệm được auto grade
- tự luận chờ giảng viên chấm tay

## 2. Các file mình đã đối chiếu

### Route

- `routes/web.php`

### Controller

- `app/Http/Controllers/GiangVien/BaiKiemTraController.php`
- `app/Http/Controllers/HocVien/BaiKiemTraController.php`
- `app/Http/Controllers/Admin/BaiKiemTraPheDuyetController.php`

### Service

- `app/Services/ExamQuestionSelectionService.php`
- `app/Services/ExamConfigurationService.php`
- `app/Services/BaiKiemTraScoringService.php`

### Model

- `app/Models/BaiKiemTra.php`
- `app/Models/BaiLamBaiKiemTra.php`
- `app/Models/NganHangCauHoi.php`

### View

- `resources/views/pages/hoc-vien/bai-kiem-tra/show.blade.php`
- `resources/views/pages/giang-vien/bai-kiem-tra/cham-diem-show.blade.php`

### Test xác nhận flow

- `tests/Feature/OnlineExamFlowTest.php`
- `tests/Feature/StudentLearningFlowTest.php`

## 3. Vì sao dễ tưởng hệ thống mới có trắc nghiệm

Phần runtime làm bài và chấm bài đã có tự luận, nhưng pipeline import hiện còn thiên về trắc nghiệm:

- `QuestionImportPersistenceService` đang tạo câu import với `loai_cau_hoi = trac_nghiem`
- `ParsedQuestionValidator` đang từ chối các loại import ngoài câu hỏi objective
- tài liệu ngân hàng câu hỏi hiện mô tả nhiều cho mẫu trắc nghiệm hơn

Nói ngắn gọn:

- **làm bài tự luận đã có**
- **import tự luận từ file ngoài hiện chưa phải luồng chính**

## 4. Flow hiện tại của bài kiểm tra tự luận trong code

## 4.1. Giảng viên tạo khung bài kiểm tra

Điểm vào:

- `POST /giang-vien/bai-kiem-tra`

Xử lý hiện tại:

- tạo bản ghi `bai_kiem_tra`
- mặc định `loai_noi_dung = tu_luan`
- mặc định:
  - `trang_thai_duyet = nhap`
  - `trang_thai_phat_hanh = nhap`
  - `tong_diem = 10`
  - `so_lan_duoc_lam = 1`

Ý nghĩa nghiệp vụ:

- khi vừa tạo khung, hệ thống đang coi đề là một đề tự luận cho đến khi giảng viên chọn câu hỏi cụ thể

## 4.2. Giảng viên chọn nhánh nội dung đề

Điểm vào:

- `PUT /giang-vien/bai-kiem-tra/{id}`

Ở bước này thực tế có 3 nhánh:

### Nhánh 1. Tự luận tự do

Điều kiện:

- `question_ids = []`
- giảng viên nhập đề bài trong `mo_ta`

Hệ thống xử lý:

- xóa toàn bộ `chi_tiet_bai_kiem_tra` nếu có
- set:
  - `loai_noi_dung = tu_luan`
  - `tong_diem = 10`

Kết quả:

- đây là đề tự luận dạng một bài viết tổng
- không cần chọn câu hỏi từ ngân hàng

### Nhánh 2. Tự luận theo từng câu

Điều kiện:

- `question_ids` chỉ chứa các câu có `loai_cau_hoi = tu_luan`

Hệ thống xử lý:

- tạo lại `chi_tiet_bai_kiem_tra`
- mỗi câu có:
  - `thu_tu`
  - `diem_so`
  - `bat_buoc = true`
- `ExamQuestionSelectionService` trả về:
  - `tong_diem`
  - `loai_noi_dung = tu_luan`

Kết quả:

- đề vẫn là tự luận
- nhưng tự luận đã có cấu trúc theo từng câu

### Nhánh 3. Hỗn hợp

Điều kiện:

- `question_ids` chứa cả câu trắc nghiệm và tự luận

Hệ thống xử lý:

- đồng bộ chi tiết câu hỏi như nhánh 2
- `ExamQuestionSelectionService` tự suy ra `loai_noi_dung = hon_hop`

Kết quả:

- đề có phần auto grade và phần chấm tay

## 4.3. Rule gửi duyệt cho đề tự luận

Điểm vào:

- `POST /giang-vien/bai-kiem-tra/{id}/gui-duyet`

Service:

- `ExamConfigurationService::ensureReadyForApproval()`

Rule quan trọng:

- phải có `tieu_de`
- `thoi_gian_lam_bai > 0`
- `so_lan_duoc_lam >= 1`
- nếu không có câu hỏi thì **bắt buộc phải có `mo_ta`**
- nếu có câu hỏi:
  - câu hỏi phải còn tồn tại
  - điểm mỗi câu hợp lệ
  - tổng điểm đề khớp tổng điểm các câu

Ý nghĩa:

- flow hiện tại đã chính thức cho phép đề tự luận không cần question bank

## 4.4. Admin duyệt và phát hành

Điểm vào:

- `POST /admin/kiem-tra-online/phe-duyet/{id}/approve`
- `POST /admin/kiem-tra-online/phe-duyet/{id}/publish`

Flow:

1. admin xem chi tiết đề
2. hệ thống kiểm tra lại readiness
3. admin duyệt
4. admin phát hành
5. học viên mới nhìn thấy đề

Điều này áp dụng giống nhau cho:

- đề tự luận tự do
- đề tự luận theo câu
- đề hỗn hợp

## 4.5. Học viên truy cập và bắt đầu làm bài

Điểm vào:

- `GET /hoc-vien/bai-kiem-tra`
- `GET /hoc-vien/bai-kiem-tra/{id}`
- `POST /hoc-vien/bai-kiem-tra/{id}/bat-dau`

Guard chính:

- đề phải `da_duyet`
- đề phải `phat_hanh`
- đề phải đang mở
- học viên chưa vượt số lần làm
- không có bài làm `dang_lam`

Nếu đề bật giám sát:

- học viên phải qua pre-check trước khi bắt đầu

Khi bấm bắt đầu:

- hệ thống tạo `bai_lam_bai_kiem_tra`
- nếu đề có `chi_tiet_bai_kiem_tra` thì tạo sẵn `chi_tiet_bai_lam_bai_kiem_tra` cho từng câu

## 4.6. Học viên nộp bài: flow tự luận tự do

Điều kiện nhận diện:

- `bai_kiem_tra->chiTietCauHois->isEmpty()`

Điểm vào:

- `POST /hoc-vien/bai-kiem-tra/{id}/nop`

Payload chính:

- `noi_dung_bai_lam`

Hệ thống update:

- `bai_lam_bai_kiem_tra.noi_dung_bai_lam`
- `trang_thai = cho_cham`
- `trang_thai_cham = cho_cham`
- `nop_luc`

Điểm cần nhớ:

- nhánh này **không đi qua auto grade**
- bài được chuyển thẳng sang hàng chờ chấm

## 4.7. Học viên nộp bài: flow tự luận theo từng câu

Điều kiện nhận diện:

- đề có `chi_tiet_bai_kiem_tra`
- các câu trong đề là `tu_luan`

Payload chính:

- `answers[chi_tiet_bai_kiem_tra_id][cau_tra_loi_text]`

Validate:

- nếu câu bắt buộc thì phải nhập nội dung

Hệ thống lưu:

- vào `chi_tiet_bai_lam_bai_kiem_tra.cau_tra_loi_text`
- đồng thời ghép một bản tóm tắt text vào `bai_lam_bai_kiem_tra.noi_dung_bai_lam`

Sau đó:

- gọi `BaiKiemTraScoringService::autoGrade()`

Vì đề chỉ có tự luận nên kết quả sẽ là:

- `tong_diem_trac_nghiem = 0`
- `tong_diem_tu_luan = 0` hoặc `null` trước khi chấm xong
- `trang_thai_cham = cho_cham`
- `trang_thai = cho_cham`

## 4.8. Học viên nộp bài: flow hỗn hợp

Payload gồm:

- `dap_an_cau_hoi_id` cho trắc nghiệm
- `cau_tra_loi_text` cho tự luận

Hệ thống xử lý:

- lưu từng dòng vào `chi_tiet_bai_lam_bai_kiem_tra`
- auto grade phần trắc nghiệm
- phần tự luận giữ `diem_tu_luan = null`

Kết quả thường là:

- `tong_diem_trac_nghiem` có điểm
- `tong_diem_tu_luan = 0`
- `trang_thai_cham = cho_cham`
- `trang_thai = cho_cham`

## 4.9. Giảng viên chấm tự luận

Điểm vào:

- `GET /giang-vien/cham-diem/{id}`
- `POST /giang-vien/cham-diem/{id}`

Màn chấm hiện tại:

- nếu là trắc nghiệm: chỉ hiển thị đáp án và điểm auto
- nếu là tự luận: hiển thị câu trả lời, ô nhập điểm và ô nhận xét

Rule chấm:

- chỉ chấm các câu `loai_cau_hoi = tu_luan`
- điểm từng câu phải nằm trong khoảng `0 -> diem_so cua cau`

Sau khi lưu:

- service ghi `diem_tu_luan`
- service ghi `nhan_xet`
- auto grade chạy lại để cộng tổng cuối
- set:
  - `nguoi_cham_id`
  - `manual_graded_at`

Kết quả cuối:

- `trang_thai_cham = da_cham`
- `trang_thai = da_cham`
- `diem_so = tong_diem_trac_nghiem + tong_diem_tu_luan`

## 4.10. Cập nhật kết quả học tập

Sau 2 thời điểm dưới đây, hệ thống đều refresh kết quả học tập:

- sau khi học viên nộp bài
- sau khi giảng viên chấm tay

Service:

- `KetQuaHocTapService`

Ý nghĩa:

- đề tự luận cũng đi vào pipeline kết quả học tập như trắc nghiệm
- không có nhánh riêng bị bỏ quên

## 5. Flow nghiệp vụ nên chốt cho phần bài kiểm tra tự luận

Phần dưới đây là flow mình đề xuất để team dùng làm chuẩn nghiệp vụ, tránh việc UI khiến người dùng tưởng hệ thống chỉ hỗ trợ trắc nghiệm.

## 5.1. Ở màn hình cấu hình đề phải tách rõ 4 lựa chọn

Giảng viên nên chọn rõ:

1. `Trắc nghiệm`
2. `Tự luận tự do`
3. `Tự luận theo câu hỏi`
4. `Hỗn hợp`

Map vào hệ thống hiện tại:

- `Trắc nghiệm` -> `loai_noi_dung = trac_nghiem`
- `Tự luận tự do` -> `loai_noi_dung = tu_luan`, `question_ids = []`, dùng `mo_ta`
- `Tự luận theo câu hỏi` -> `loai_noi_dung = tu_luan`, chọn question bank loại `tu_luan`
- `Hỗn hợp` -> `loai_noi_dung = hon_hop`

## 5.2. Flow chi tiết đề tự luận tự do

1. Giảng viên tạo khung đề.
2. Chọn loại nội dung là `Tự luận tự do`.
3. Form ẩn phần chọn câu hỏi.
4. Form bắt buộc nhập:
   - `tieu_de`
   - `mo_ta`
   - `thoi_gian_lam_bai`
   - `ngay_mo`, `ngay_dong` nếu có
   - `so_lan_duoc_lam`
5. Gửi duyệt.
6. Admin duyệt và phát hành.
7. Học viên bấm bắt đầu làm bài.
8. Hệ thống tạo 1 `bai_lam_bai_kiem_tra`.
9. Học viên nhập bài vào `noi_dung_bai_lam`.
10. Khi nộp bài, trạng thái chuyển sang `cho_cham`.
11. Giảng viên mở màn chấm bài tự luận tổng.
12. Giảng viên nhập điểm cuối và nhận xét tổng.
13. Hệ thống cập nhật `diem_so`, `manual_graded_at`, `ket_qua_hoc_tap`.

Ghi chú:

- flow chấm tổng cho kiểu này hiện chưa tách màn chấm riêng thật rõ như kiểu tự luận theo từng câu
- nếu muốn UX đẹp hơn, nên bổ sung một màn chấm tổng cho `noi_dung_bai_lam`

## 5.3. Flow chi tiết đề tự luận theo từng câu hỏi

1. Giảng viên tạo khung đề.
2. Chọn loại nội dung là `Tự luận theo câu hỏi`.
3. Form bật tab chọn câu hỏi.
4. Bộ lọc câu hỏi ưu tiên `loai_cau_hoi = tu_luan`.
5. Giảng viên chọn từng câu tự luận từ ngân hàng.
6. Gán điểm cho từng câu.
7. Hệ thống tạo `chi_tiet_bai_kiem_tra`.
8. Gửi duyệt.
9. Admin duyệt và phát hành.
10. Học viên bắt đầu làm bài.
11. Hệ thống tạo:
    - `bai_lam_bai_kiem_tra`
    - các dòng `chi_tiet_bai_lam_bai_kiem_tra`
12. Học viên trả lời từng câu trong `cau_tra_loi_text`.
13. Học viên nộp bài.
14. Hệ thống chuyển bài sang `cho_cham`.
15. Giảng viên vào màn `cham-diem`.
16. Giảng viên chấm từng câu:
    - nhập `diem_tu_luan`
    - nhập `nhan_xet`
17. Hệ thống cộng tổng điểm và đổi bài sang `da_cham`.
18. `KetQuaHocTapService` refresh kết quả học tập.

## 5.4. Flow chi tiết đề hỗn hợp

1. Giảng viên tạo khung đề.
2. Chọn loại nội dung là `Hỗn hợp`.
3. Chọn cả câu trắc nghiệm và tự luận.
4. Gán điểm cho từng câu.
5. Gửi duyệt, admin duyệt, admin phát hành.
6. Học viên làm bài:
   - trắc nghiệm chọn đáp án
   - tự luận nhập text
7. Học viên nộp bài.
8. Hệ thống auto grade phần trắc nghiệm.
9. Hệ thống giữ phần tự luận ở `cho_cham`.
10. Giảng viên chấm phần tự luận.
11. Hệ thống cộng điểm cuối và refresh kết quả học tập.

## 6. Trạng thái dữ liệu nên hiểu thật rõ

### Đối với đề

- `trang_thai_duyet`: `nhap -> cho_duyet -> da_duyet`
- `trang_thai_phat_hanh`: `nhap -> phat_hanh -> dong`
- `loai_noi_dung`:
  - `trac_nghiem`
  - `tu_luan`
  - `hon_hop`

### Đối với bài làm

- `trang_thai`:
  - `dang_lam`
  - `da_nop`
  - `cho_cham`
  - `da_cham`
- `trang_thai_cham`:
  - `chua_cham`
  - `cho_cham`
  - `da_cham`

Rule thực tế:

- đề chỉ có trắc nghiệm có thể đi thẳng tới `da_cham`
- đề có tự luận sẽ dừng ở `cho_cham` cho tới khi giảng viên chấm xong

## 7. Những chỗ cần làm thêm nếu muốn phần tự luận thật sự hoàn chỉnh

Đây là các gap mình thấy sau khi đọc code:

### Gap 1. Import câu hỏi tự luận chưa phải luồng chính

Hiện trạng:

- import từ file đang ưu tiên câu hỏi objective
- chưa có pipeline rõ ràng để import câu tự luận từ Word/PDF/Excel rồi đẩy thẳng vào question bank

Nếu muốn hoàn chỉnh:

- mở rộng parser để nhận diện `loai_cau_hoi = tu_luan`
- cho phép câu import không cần đáp án lựa chọn
- cho phép import `goi_y_tra_loi` hoặc `rubric`

### Gap 2. UI tạo đề chưa nói rõ 2 kiểu tự luận

Hiện trạng:

- runtime support có rồi
- nhưng UI chưa nói rõ:
  - tự luận tự do
  - tự luận theo từng câu

Nếu muốn dễ dùng:

- thêm lựa chọn nội dung đề rõ ràng ngay ở form
- đổi label và helper text để giảng viên không hiểu lầm

### Gap 3. Flow chấm đề tự luận tự do nên có màn chấm tổng riêng

Hiện trạng:

- đề tự luận theo từng câu có màn chấm tốt hơn
- đề tự luận tự do đang thiên về lưu `noi_dung_bai_lam` và chuyển `cho_cham`

Nếu muốn chặt:

- thêm màn chấm tổng cho `noi_dung_bai_lam`
- cho nhập:
  - điểm tổng
  - nhận xét tổng
  - rubric hoặc tiêu chí nếu cần

## 8. Kết luận nghiệp vụ nên chốt với team

Nếu chốt theo code hiện tại thì nên hiểu như sau:

- hệ thống đã có nền tự luận
- phần thiếu không phải là runtime nộp bài hay chấm bài
- phần cần làm rõ thêm là:
  - trải nghiệm cấu hình đề
  - import câu hỏi tự luận
  - màn chấm tổng cho tự luận tự do

Nói ngắn gọn:

- `trắc nghiệm` đã xong
- `tự luận theo từng câu` đã có nền thật
- `tự luận tự do` cũng đã có nền thật
- thứ cần bổ sung tiếp là làm cho flow này **hiển thị rõ, dùng rõ, import rõ và chấm rõ**
