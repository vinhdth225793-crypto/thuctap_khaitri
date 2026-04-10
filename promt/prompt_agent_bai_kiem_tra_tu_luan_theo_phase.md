# Prompt chi tiết cho agent code: nâng cấp và hoàn thiện phần bài kiểm tra tự luận

Bạn là senior Laravel developer + exam workflow architect + business analyst cho hệ thống đào tạo nội bộ.

Tôi muốn bạn đọc code thật của project Laravel hiện tại và **triển khai hoàn thiện phần BÀI KIỂM TRA TỰ LUẬN** theo đúng flow đang có sẵn trong repo, không được code mù, không được viết lại cả module nếu code hiện tại đã có nền.

Tài liệu này là prompt giao việc cho agent code.  
Mục tiêu là giúp agent làm việc theo từng phase, có thứ tự rõ ràng, không sửa lan man, không phá flow cũ.

==================================================
1. MỤC TIÊU TỔNG THỂ
==================================================

Hiện tại hệ thống bài kiểm tra đã có nền cho:

- giảng viên tạo bài kiểm tra
- chọn phạm vi:
  - theo buổi học
  - theo module
  - cuối khóa
- chọn câu hỏi từ ngân hàng câu hỏi
- import câu hỏi từ file ngoài
- cấu hình thời gian làm bài
- cấu hình số lần làm
- gửi duyệt
- admin duyệt và phát hành
- học viên bắt đầu làm bài
- học viên nộp bài
- hệ thống auto grade phần trắc nghiệm
- giảng viên chấm tay phần tự luận
- hệ thống cập nhật kết quả học tập

Sau khi đọc code thật, tôi thấy module hiện tại **đã có nền tự luận**, nhưng phần này chưa được thể hiện rõ ràng về nghiệp vụ và UI.

Tôi muốn agent code làm cho phần bài kiểm tra tự luận trở nên:

- rõ mode
- rõ flow
- rõ UI
- rõ dữ liệu
- rõ màn chấm
- rõ logic import
- rõ test

==================================================
2. KẾT LUẬN NGHIỆP VỤ PHẢI CHỐT TRƯỚC KHI CODE
==================================================

Sau khi đọc code thật, agent phải chốt đúng rằng hệ thống hiện có 3 loại nội dung đề:

- `trac_nghiem`
- `tu_luan`
- `hon_hop`

Và riêng `tu_luan` hiện đang tồn tại theo 2 kiểu:

### A. Tự luận tự do

- đề không có dòng nào trong `chi_tiet_bai_kiem_tra`
- giảng viên nhập đề bài vào `mo_ta`
- học viên nộp toàn bộ bài vào `bai_lam_bai_kiem_tra.noi_dung_bai_lam`
- bài đi thẳng sang `cho_cham`

### B. Tự luận theo từng câu hỏi

- đề có nhiều dòng trong `chi_tiet_bai_kiem_tra`
- câu hỏi lấy từ `ngan_hang_cau_hoi.loai_cau_hoi = tu_luan`
- học viên trả lời từng câu ở `chi_tiet_bai_lam_bai_kiem_tra.cau_tra_loi_text`
- giảng viên chấm từng câu bằng `diem_tu_luan`

### C. Đề hỗn hợp

- đề có cả trắc nghiệm và tự luận
- phần trắc nghiệm auto grade
- phần tự luận chờ chấm tay

Agent phải bám đúng 3 mode này, không được đánh đồng tự luận chỉ là “một textarea”.

==================================================
3. NHỮNG FILE BẮT BUỘC PHẢI ĐỌC TRƯỚC
==================================================

Agent bắt buộc phải đọc code thật trước khi sửa:

- `routes/web.php`
- `app/Http/Controllers/GiangVien/BaiKiemTraController.php`
- `app/Http/Controllers/HocVien/BaiKiemTraController.php`
- `app/Http/Controllers/Admin/BaiKiemTraPheDuyetController.php`
- `app/Services/ExamQuestionSelectionService.php`
- `app/Services/ExamConfigurationService.php`
- `app/Services/BaiKiemTraScoringService.php`
- `app/Services/KetQuaHocTapService.php`
- `app/Services/ExamQuestionImportService.php`
- `app/Services/QuestionImport/ParsedQuestionValidator.php`
- `app/Services/QuestionImport/QuestionImportPersistenceService.php`
- `app/Models/BaiKiemTra.php`
- `app/Models/BaiLamBaiKiemTra.php`
- `app/Models/ChiTietBaiKiemTra.php`
- `app/Models/ChiTietBaiLamBaiKiemTra.php`
- `app/Models/NganHangCauHoi.php`
- `resources/views/pages/giang-vien/bai-kiem-tra/edit.blade.php`
- `resources/views/pages/hoc-vien/bai-kiem-tra/show.blade.php`
- `resources/views/pages/giang-vien/bai-kiem-tra/cham-diem-show.blade.php`
- `resources/views/pages/admin/kiem-tra-online/phe-duyet/show.blade.php`
- `tests/Feature/OnlineExamFlowTest.php`
- `tests/Feature/StudentLearningFlowTest.php`

Ngoài ra phải đọc tài liệu nội bộ:

- `promt/flow/baikiemtra_tuluan.md`

==================================================
4. YÊU CẦU RẤT QUAN TRỌNG
==================================================

- Không được code mù.
- Không được làm lại toàn bộ module bài kiểm tra.
- Phải tận dụng tối đa flow hiện tại.
- Nếu code hiện tại đã có sẵn logic tự luận thì phải giữ, chỉ mở rộng và làm rõ.
- Không được phá flow trắc nghiệm đang ổn.
- Không được phá flow hỗn hợp đang có.
- Không được làm sai `KetQuaHocTapService`.
- Không được biến tự luận thành một nhánh xử lý rời rạc, không liên thông với kết quả học tập.
- Phải ưu tiên tương thích ngược với dữ liệu hiện có.

==================================================
5. MỤC TIÊU NGHIỆP VỤ MUỐN ĐẠT SAU KHI LÀM
==================================================

Sau khi hoàn thiện, hệ thống phải hỗ trợ rõ ràng:

### Mode 1. Trắc nghiệm

- flow cũ giữ nguyên

### Mode 2. Tự luận tự do

- giảng viên tạo đề bằng mô tả đề bài
- học viên làm bài bằng một bài viết tổng
- giảng viên chấm điểm tổng bài

### Mode 3. Tự luận theo câu hỏi

- giảng viên chọn các câu tự luận từ ngân hàng
- học viên trả lời từng câu
- giảng viên chấm từng câu

### Mode 4. Hỗn hợp

- giữ flow cũ
- trắc nghiệm auto grade
- tự luận chấm tay

==================================================
6. NHỮNG GÌ PHẢI LÀM RÕ TRONG UI / UX
==================================================

Tôi muốn phần tự luận được nhìn thấy rõ ở giao diện, không còn cảm giác “hệ thống chỉ có trắc nghiệm”.

Agent phải làm rõ:

1. Ở màn tạo/sửa đề
- cho giảng viên thấy rõ loại nội dung đề:
  - trắc nghiệm
  - tự luận tự do
  - tự luận theo câu hỏi
  - hỗn hợp

2. Nếu chọn `tự luận tự do`
- ẩn phần chọn question bank
- nhấn mạnh phần nhập `mô tả đề`
- nhấn mạnh là học viên sẽ nộp một bài viết tổng

3. Nếu chọn `tự luận theo câu hỏi`
- hiện phần question bank
- ưu tiên filter câu hỏi `tu_luan`
- cho nhập điểm từng câu

4. Nếu chọn `hỗn hợp`
- cho chọn cả trắc nghiệm và tự luận
- UI phải nói rõ đề sẽ có auto grade và chấm tay

5. Ở phía học viên
- hiển thị rõ đề đang là loại gì
- nếu là tự luận tự do thì hiện ô nhập bài tổng
- nếu là tự luận theo câu thì hiện danh sách câu hỏi
- nếu là hỗn hợp thì hiển thị cả 2 phần rõ ràng

6. Ở phía giảng viên chấm bài
- phải phân biệt rõ:
  - chấm tổng cho bài tự luận tự do
  - chấm từng câu cho bài tự luận theo câu
  - chấm phần tự luận trong đề hỗn hợp

==================================================
7. NHỮNG RULE NGHIỆP VỤ BẮT BUỘC PHẢI GIỮ
==================================================

### Rule 1. Đề tự luận tự do vẫn là đề hợp lệ

- nếu không có `chi_tiet_bai_kiem_tra`
- nhưng có `mo_ta`
- thì đề vẫn phải được gửi duyệt, duyệt và phát hành bình thường

### Rule 2. Đề tự luận theo câu phải dùng câu hỏi loại `tu_luan`

- không được trộn câu hỏi sai loại khi chọn riêng mode này

### Rule 3. Đề hỗn hợp vẫn phải giữ nguyên flow hiện có

- trắc nghiệm auto grade
- tự luận chờ chấm

### Rule 4. Tự luận tự do không đi qua auto grade

- khi nộp bài:
  - `trang_thai = cho_cham`
  - `trang_thai_cham = cho_cham`

### Rule 5. Tự luận theo câu vẫn đi qua pipeline scoring chung

- hệ thống vẫn có thể gọi `BaiKiemTraScoringService`
- nhưng chỉ dùng để tổng hợp trạng thái và cộng điểm đã chấm

### Rule 6. Kết quả học tập phải được refresh đúng

- sau khi nộp bài
- sau khi giảng viên chấm bài

### Rule 7. Không phá dữ liệu cũ

- các đề cũ có `loai_noi_dung = tu_luan` phải vẫn chạy được

==================================================
8. CÁC PHẦN MUỐN AGENT TRIỂN KHAI
==================================================

Tôi muốn agent code chia việc thành các phase rõ ràng như dưới đây.

==================================================
9. CHIA THEO PHASE
==================================================

------------------------------------------
PHASE 1. ĐỌC CODE VÀ CHỐT HIỆN TRẠNG
------------------------------------------

Mục tiêu:

- hiểu đúng flow hiện tại của bài kiểm tra tự luận
- xác định phần nào đã có
- xác định phần nào còn thiếu

Việc phải làm:

1. Đọc các file đã liệt kê ở mục 3.
2. Chỉ ra rõ:
   - flow tạo đề
   - flow gửi duyệt
   - flow admin duyệt và phát hành
   - flow học viên bắt đầu làm bài
   - flow học viên nộp bài
   - flow giảng viên chấm bài
   - flow refresh kết quả học tập
3. Chốt rõ 3 mode:
   - tự luận tự do
   - tự luận theo câu hỏi
   - hỗn hợp
4. Chỉ ra:
   - file nào đang xử lý đúng rồi
   - file nào cần sửa
   - file nào cần thêm
5. Nếu có mâu thuẫn giữa tài liệu cũ và code thật, phải ưu tiên code thật.

Đầu ra mong muốn:

- báo cáo hiện trạng
- danh sách file liên quan
- danh sách thay đổi đề xuất theo phase

------------------------------------------
PHASE 2. CHUẨN HÓA NGHIỆP VỤ VÀ TYPE ĐỀ TỰ LUẬN
------------------------------------------

Mục tiêu:

- làm rõ mode nội dung đề
- không để UI/logic mơ hồ nữa

Việc phải làm:

1. Rà lại `BaiKiemTra` và form tạo/sửa đề.
2. Nếu cần, thêm trường hoặc mapping UI để phân biệt rõ:
   - `trac_nghiem`
   - `tu_luan_tu_do`
   - `tu_luan_theo_cau`
   - `hon_hop`

Lưu ý:

- không nhất thiết phải thêm cột DB mới nếu có thể suy ra từ:
  - `loai_noi_dung`
  - có hay không có `chi_tiet_bai_kiem_tra`
- nếu thấy nên thêm cột để UX rõ hơn thì phải nêu rõ trade-off

3. Chuẩn hóa helper text ở form giảng viên:
   - mode nào dùng `mo_ta`
   - mode nào dùng question bank
   - mode nào auto grade
   - mode nào chấm tay

4. Chuẩn hóa validate ở controller/service:
   - tự luận tự do: bắt buộc `mo_ta`
   - tự luận theo câu: bắt buộc có câu hỏi loại `tu_luan`
   - hỗn hợp: chấp nhận cả 2 loại

Đầu ra mong muốn:

- UI tạo/sửa đề rõ ràng
- validation rõ ràng
- không gây hiểu lầm với giảng viên

------------------------------------------
PHASE 3. HOÀN THIỆN FLOW TỰ LUẬN TỰ DO
------------------------------------------

Mục tiêu:

- làm cho mode tự luận tự do trở thành một flow hoàn chỉnh, rõ ràng từ đầu đến cuối

Việc phải làm:

1. Kiểm tra lại flow:
   - tạo đề
   - gửi duyệt
   - duyệt
   - phát hành
   - học viên bắt đầu
   - học viên nộp bài
   - giảng viên chấm

2. Hoàn thiện phía học viên:
   - hiển thị đề bài từ `mo_ta`
   - hiển thị một editor/textarea rõ ràng cho `noi_dung_bai_lam`
   - thông báo rõ đây là bài tự luận tổng

3. Hoàn thiện phía giảng viên:
   - tạo hoặc nâng cấp màn chấm cho `tự luận tự do`
   - cho nhập:
     - điểm tổng
     - nhận xét tổng

4. Hoàn thiện lưu dữ liệu:
   - `noi_dung_bai_lam`
   - `diem_so`
   - `nhan_xet`
   - `manual_graded_at`
   - `nguoi_cham_id`

5. Đảm bảo sau khi chấm xong:
   - `trang_thai = da_cham`
   - `trang_thai_cham = da_cham`

Rủi ro cần chú ý:

- hiện code đã có nhánh nộp bài tự luận tự do, nhưng màn chấm tổng có thể chưa tách đủ rõ
- không được làm đứt flow cũ của `cham-diem`

------------------------------------------
PHASE 4. HOÀN THIỆN FLOW TỰ LUẬN THEO TỪNG CÂU HỎI
------------------------------------------

Mục tiêu:

- làm mode tự luận theo câu trở thành flow chính thức, dễ dùng

Việc phải làm:

1. Ở màn chọn câu hỏi cho đề:
   - cho filter rõ theo `loai_cau_hoi = tu_luan`
   - hiển thị thông tin phù hợp với câu tự luận:
     - nội dung
     - mức độ
     - điểm mặc định
     - gợi ý trả lời nếu có

2. Khi giảng viên chọn câu:
   - bắt buộc nhập hoặc xác nhận điểm từng câu
   - giữ `bat_buoc = true` nếu đúng rule hiện tại

3. Ở phía học viên:
   - hiển thị từng câu tự luận riêng
   - hỗ trợ nhập `cau_tra_loi_text`
   - validate câu bắt buộc

4. Ở phía giảng viên chấm:
   - hiển thị từng câu
   - hiển thị câu trả lời
   - nhập `diem_tu_luan`
   - nhập `nhan_xet`

5. Sau khi chấm:
   - tổng hợp điểm lại qua `BaiKiemTraScoringService`
   - refresh `KetQuaHocTapService`

Đầu ra mong muốn:

- mode tự luận theo câu dùng rõ ràng như một mode riêng

------------------------------------------
PHASE 5. CỦNG CỐ FLOW ĐỀ HỖN HỢP
------------------------------------------

Mục tiêu:

- không phá flow hỗn hợp đang có
- làm rõ phần tự luận bên trong đề hỗn hợp

Việc phải làm:

1. Rà lại màn hình học viên:
   - câu trắc nghiệm hiển thị đúng
   - câu tự luận hiển thị đúng
   - thứ tự hiển thị ổn

2. Rà lại nộp bài:
   - trắc nghiệm lưu `dap_an_cau_hoi_id`
   - tự luận lưu `cau_tra_loi_text`

3. Rà lại scoring:
   - trắc nghiệm auto grade
   - tự luận vào `cho_cham`

4. Rà lại màn chấm:
   - câu trắc nghiệm hiển thị readonly
   - câu tự luận hiển thị ô nhập điểm

Đầu ra mong muốn:

- đề hỗn hợp hoạt động ổn định, dễ hiểu

------------------------------------------
PHASE 6. HỖ TRỢ IMPORT CÂU HỎI TỰ LUẬN
------------------------------------------

Mục tiêu:

- mở rộng pipeline import để không còn mặc định “import là trắc nghiệm”

Việc phải làm:

1. Đọc các file:
   - `ExamQuestionImportService`
   - `QuestionDocumentImportService`
   - `ParsedQuestionValidator`
   - `QuestionImportPersistenceService`
   - parser support liên quan

2. Cho phép import câu tự luận từ file ngoài với các rule hợp lý.

3. Thiết kế format dữ liệu import cho tự luận, ví dụ:
   - nội dung câu hỏi
   - loại câu hỏi = tự luận
   - mức độ
   - điểm mặc định
   - gợi ý trả lời hoặc rubric

4. Update preview import:
   - hiển thị đúng loại câu hỏi
   - không bắt buộc có đáp án lựa chọn cho câu tự luận

5. Update persistence:
   - không còn hard-code `loai_cau_hoi = trac_nghiem`
   - không còn ép `kieu_dap_an = mot_dap_an` cho mọi loại

Đầu ra mong muốn:

- import được câu tự luận
- preview đúng
- lưu vào question bank đúng loại

------------------------------------------
PHASE 7. NÂNG CẤP QUESTION BANK CHO TỰ LUẬN
------------------------------------------

Mục tiêu:

- question bank phải hỗ trợ tự luận rõ ràng, không chỉ tồn tại ở mức schema

Việc phải làm:

1. Kiểm tra form tạo/sửa câu hỏi.
2. Khi `loai_cau_hoi = tu_luan`:
   - ẩn phần đáp án lựa chọn
   - hiện các field phù hợp:
     - gợi ý trả lời
     - giải thích hướng chấm
     - mức độ
     - điểm mặc định

3. Kiểm tra list question bank:
   - badge loại câu hỏi
   - filter tự luận
   - trạng thái sẵn sàng

4. Kiểm tra builder chọn câu hỏi trong đề:
   - tự luận phải dễ lọc, dễ chọn

Đầu ra mong muốn:

- question bank thực sự dùng được cho tự luận

------------------------------------------
PHASE 8. CHẤM ĐIỂM, TỔNG HỢP ĐIỂM VÀ KẾT QUẢ HỌC TẬP
------------------------------------------

Mục tiêu:

- đảm bảo tự luận đi hết pipeline chấm điểm và kết quả học tập

Việc phải làm:

1. Rà `BaiKiemTraScoringService`:
   - không làm sai auto grade trắc nghiệm
   - cộng đúng `tong_diem_tu_luan`
   - set đúng `trang_thai_cham`

2. Nếu triển khai chấm tổng cho tự luận tự do:
   - quyết định lưu điểm tổng ở đâu
   - đảm bảo `diem_so` cuối cùng hợp lệ

3. Rà `KetQuaHocTapService`:
   - bài tự luận chấm xong phải refresh kết quả bình thường

4. Đảm bảo:
   - không bị mất điểm cũ
   - không bị refresh sai cấp bài thi/module/khóa học

Đầu ra mong muốn:

- tự luận sau khi chấm xong đi đúng vào kết quả học tập

------------------------------------------
PHASE 9. ADMIN REVIEW VÀ HIỂN THỊ THÔNG TIN ĐỀ TỰ LUẬN
------------------------------------------

Mục tiêu:

- admin nhìn vào là hiểu đề đang là loại gì

Việc phải làm:

1. Ở màn duyệt đề:
   - hiển thị rõ đề là:
     - trắc nghiệm
     - tự luận tự do
     - tự luận theo câu
     - hỗn hợp

2. Nếu là tự luận tự do:
   - hiển thị `mo_ta` như đề bài chính

3. Nếu là tự luận theo câu:
   - hiển thị danh sách câu tự luận
   - điểm từng câu

4. Nếu là hỗn hợp:
   - hiển thị rõ phần nào trắc nghiệm, phần nào tự luận

Đầu ra mong muốn:

- admin review đề rõ ràng hơn, ít nhầm lẫn

------------------------------------------
PHASE 10. TEST TOÀN BỘ FLOW
------------------------------------------

Mục tiêu:

- đảm bảo phần tự luận chạy ổn và không làm gãy flow cũ

Agent phải viết hoặc cập nhật test cho ít nhất các ca sau:

1. Tạo đề tự luận tự do.
2. Gửi duyệt đề tự luận tự do.
3. Admin duyệt và phát hành đề tự luận tự do.
4. Học viên bắt đầu và nộp bài tự luận tự do.
5. Giảng viên chấm bài tự luận tự do.
6. Tạo đề tự luận theo câu.
7. Học viên nộp bài tự luận theo câu.
8. Giảng viên chấm từng câu tự luận.
9. Đề hỗn hợp vẫn auto grade phần trắc nghiệm và chờ chấm phần tự luận.
10. Kết quả học tập refresh đúng sau khi chấm.
11. Import câu hỏi tự luận hoạt động đúng nếu phase import được triển khai.
12. Các đề cũ `trac_nghiem` vẫn chạy như cũ.

==================================================
10. YÊU CẦU KỸ THUẬT
==================================================

- Không nhồi toàn bộ logic vào controller.
- Ưu tiên service nếu logic đủ lớn.
- Không tạo abstraction quá mức nếu repo hiện tại chưa theo hướng đó.
- Phải giữ naming bám style code đang có.
- Phải giữ migration, model, controller, blade, service ở đúng chỗ.
- Khi sửa view, phải giữ style hiện tại của repo.
- Không thêm thư viện ngoài nếu chưa thật sự cần.

==================================================
11. NHỮNG GÌ KHÔNG LÀM TRONG PHASE NÀY
==================================================

Để tránh đi quá xa, phase này KHÔNG làm:

- AI chấm tự luận
- NLP chấm điểm tự động
- plagiarism detection
- so khớp văn bản nâng cao
- rubric scoring bằng AI
- realtime collaborative grading
- upload file bài làm dạng Word/PDF của học viên nếu repo chưa có nền

Chỉ tập trung:

- hoàn thiện flow tự luận hiện có
- làm rõ mode
- làm rõ UI
- hoàn thiện import nếu cần
- hoàn thiện chấm bài
- giữ đúng kết quả học tập

==================================================
12. ĐỊNH DẠNG ĐẦU RA TÔI MUỐN TỪ AGENT
==================================================

Tôi muốn agent trả kết quả theo format:

PHẦN A. PHÂN TÍCH HIỆN TRẠNG
- flow tự luận hiện có
- file liên quan
- phần đã có nền
- phần còn thiếu

PHẦN B. THIẾT KẾ NGHIỆP VỤ
- tự luận tự do
- tự luận theo câu hỏi
- hỗn hợp
- import câu tự luận
- chấm điểm

PHẦN C. THIẾT KẾ KỸ THUẬT
- migration
- model
- service
- controller
- blade
- test

PHẦN D. TRIỂN KHAI THEO PHASE
- phase nào làm gì
- file nào sửa
- vì sao sửa

PHẦN E. TEST
- ca test đã chạy
- ca test chưa chạy được
- rủi ro còn lại

==================================================
13. YÊU CẦU CUỐI CÙNG
==================================================

Hãy bắt đầu bằng việc đọc code thật của repo hiện tại rồi mới làm.

Mục tiêu cuối cùng là:

- hệ thống thể hiện rõ ràng phần bài kiểm tra tự luận
- hỗ trợ tốt `tự luận tự do`
- hỗ trợ tốt `tự luận theo câu hỏi`
- giữ ổn định `đề hỗn hợp`
- nếu làm phase import thì import được câu tự luận
- giảng viên chấm bài dễ hơn
- học viên làm bài dễ hiểu hơn
- admin duyệt đề dễ hiểu hơn
- kết quả học tập không bị sai

Không được code mù.  
Không được làm lại toàn bộ module nếu repo đã có nền tốt.  
Phải đi theo từng phase, hoàn thành phase nào chắc phase đó.  
Phải dùng văn bản UTF-8 có dấu tiếng Việt đầy đủ khi viết tài liệu, label và helper text.
