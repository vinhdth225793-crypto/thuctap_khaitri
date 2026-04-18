# PROMPT CHO AI AGENT CODE

Bạn đang làm việc trên repo Laravel hiện có của tôi.  
Mục tiêu là **hoàn thiện triệt để phần kiểm tra tự luận**, theo hướng **giống bài kiểm tra trắc nghiệm về mặt cấu trúc quản lý đề**, nhưng khác ở chỗ **câu hỏi là tự luận**.

## Bối cảnh repo hiện tại
Repo hiện đã có nền quan trọng sau:
- `BaiKiemTra` đã phân biệt `tu_luan_tu_do` và `tu_luan_theo_cau`
- `BaiKiemTraController` đã có:
  - `question_ids`
  - `question_scores`
  - `importPreview()`
  - `importConfirm()`
  - `submitForApproval()`
  - `publish()`
  - `chamDiemStore()`
- `BaiLamBaiKiemTra` đã có quan hệ `chiTietTraLois()`
- routes đã có flow cho:
  - tạo/sửa bài kiểm tra
  - import preview/confirm
  - gửi duyệt / phát hành
  - học viên bắt đầu / nộp bài
  - giảng viên chấm điểm

## Mục tiêu nghiệp vụ cần đạt
1. **Tự luận theo câu** trở thành flow chính cho kiểm tra tự luận.
2. Giảng viên có thể tạo câu hỏi tự luận bằng 2 cách:
   - nhập trực tiếp qua textbox
   - import file câu hỏi
3. Một bài kiểm tra tự luận có thể có:
   - 1 câu
   - hoặc nhiều câu
4. Mỗi câu hỏi tự luận có thể có:
   - nội dung câu hỏi
   - đáp án mẫu / rubric chấm
   - gợi ý
   - điểm
   - thứ tự
   - mức độ
5. Học viên làm bài theo từng câu.
6. Hệ thống lưu bài làm theo từng câu.
7. Giảng viên chấm theo từng câu.
8. Vẫn giữ được mode **tự luận tự do** hiện có để không phá backward compatibility.

---

# NGUYÊN TẮC BẮT BUỘC

## 1. Làm theo phase, không nhảy cóc
Chỉ làm **1 phase tại 1 thời điểm**.  
Không được chuyển sang phase tiếp theo nếu phase hiện tại chưa pass tự kiểm.

## 2. Sau mỗi phase phải báo cáo
Phải trả lời đúng format:
- Những gì đã làm
- File đã sửa/thêm
- Migration/Model/Service/Controller/View/Route đã thay đổi
- Logic chính đã implement
- Tự kiểm
- Rủi ro còn lại
- Phase đã PASS hay chưa

## 3. Không refactor lan rộng
Chỉ chỉnh những phần liên quan trực tiếp đến:
- kiểm tra tự luận
- câu hỏi tự luận
- import câu hỏi tự luận
- bài làm tự luận
- chấm điểm tự luận

## 4. Tận dụng cấu trúc đang có
Không viết lại toàn bộ module bài kiểm tra.  
Phải tái sử dụng tối đa:
- `BaiKiemTra`
- `BaiKiemTraController`
- `question_ids`
- `question_scores`
- `importPreview/importConfirm`
- `chiTietTraLois`
- `chamDiemStore()`

## 5. Không phá mode cũ
Mode `tu_luan_tu_do` phải vẫn chạy được.  
Mode `tu_luan_theo_cau` phải được nâng cấp thành flow hoàn chỉnh hơn.

---

# PHASE 1 — KHẢO SÁT VÀ MAPPING TOÀN BỘ FLOW HIỆN TẠI

## Mục tiêu
Hiểu chính xác repo hiện tại đang hỗ trợ gì cho bài kiểm tra tự luận, câu hỏi, import, bài làm và chấm điểm.

## Việc phải làm
1. Đọc và phân tích kỹ các file liên quan tối thiểu:
   - `app/Models/BaiKiemTra.php`
   - `app/Http/Controllers/GiangVien/BaiKiemTraController.php`
   - `app/Models/BaiLamBaiKiemTra.php`
   - các model liên quan tới câu hỏi / chi tiết bài kiểm tra / chi tiết trả lời
   - `routes/web.php`
   - các view của màn edit/create/import/chấm điểm/học viên làm bài
2. Xác định rõ:
   - mode nào đang có
   - câu hỏi đang được lưu ở bảng/model nào
   - chi tiết câu hỏi trong đề đang được gắn ra sao
   - bài làm theo từng câu đang được lưu ra sao
   - import hiện tại hỗ trợ loại câu hỏi nào
3. Lập **gap analysis**:
   - phần nào của flow tự luận đã có
   - phần nào còn thiếu
   - phần nào đang chỉ hỗ trợ trắc nghiệm
   - phần nào cần mở rộng để hỗ trợ tự luận theo câu

## Output bắt buộc
- sơ đồ flow hiện tại của kiểm tra tự luận
- danh sách file liên quan
- danh sách bảng/model liên quan
- khoảng trống cần triển khai
- đề xuất plan phase 2 → phase cuối

## Không được làm trong phase này
- Không thêm migration
- Không sửa business logic lớn
- Không đổi UI lớn
- Không tạo feature runtime mới

## Điều kiện PASS
- Đã mapping rõ full flow hiện tại
- Đã xác định chính xác nơi cần sửa
- Đã có plan đủ chi tiết cho các phase sau

---

# PHASE 2 — CHUẨN HÓA THIẾT KẾ DỮ LIỆU CHO TỰ LUẬN THEO CÂU

## Mục tiêu
Đảm bảo dữ liệu đủ để vận hành kiểm tra tự luận theo câu giống flow trắc nghiệm.

## Việc phải làm
1. Kiểm tra model/bảng câu hỏi hiện tại:
   - xem đã hỗ trợ `loai_cau_hoi = tu_luan` chưa
   - xem có các field cho:
     - nội dung câu hỏi
     - gợi ý
     - đáp án mẫu
     - rubric chấm
     - mức độ
     - trạng thái
2. Kiểm tra chi tiết bài kiểm tra:
   - xem bảng/model gắn câu hỏi vào đề đã đủ cho tự luận chưa
   - có cần bổ sung field như:
     - `diem_so`
     - `thu_tu`
     - `rubric_rieng`
     - `huong_dan_rieng`
3. Kiểm tra chi tiết trả lời:
   - xem bảng/model bài làm theo từng câu đã đủ field cho:
     - nội dung trả lời tự luận
     - file đính kèm nếu có
     - điểm từng câu
     - nhận xét từng câu
4. Nếu thiếu, tạo migration bổ sung theo hướng an toàn, không phá dữ liệu cũ.

## Yêu cầu implementation
- Không xóa field cũ
- Không đổi tên field cũ đang dùng nếu chưa thật cần
- Migration phải tương thích dữ liệu hiện tại
- Nếu có enum/string status mới thì phải đồng nhất naming

## Output bắt buộc
- bảng nào đã kiểm tra
- field nào đã bổ sung
- migration nào đã tạo
- giải thích vì sao từng field là cần thiết

## Điều kiện PASS
- Dữ liệu đã đủ cho:
  - tạo câu hỏi tự luận
  - gắn nhiều câu vào đề
  - học viên trả lời từng câu
  - giảng viên chấm từng câu

---

# PHASE 3 — CHUẨN HÓA MODE NỘI DUNG VÀ RULE NGHIỆP VỤ

## Mục tiêu
Làm rõ và khóa chặt hành vi của từng mode:
- `tu_luan_tu_do`
- `tu_luan_theo_cau`
- `trac_nghiem`
- `hon_hop`

## Việc phải làm
1. Rà lại các helper trong `BaiKiemTra`
2. Chuẩn hóa rule:
   - `tu_luan_tu_do`:
     - không cần `question_ids`
     - dùng 1 đề bài tổng
     - chấm toàn bài
   - `tu_luan_theo_cau`:
     - bắt buộc có >= 1 câu hỏi
     - phải có điểm từng câu
     - học viên trả lời từng câu
     - giảng viên chấm từng câu
3. Refactor nhẹ các hàm validate content mode trong controller/service để rule rõ ràng hơn.
4. Đảm bảo `question_ids` và `question_scores` hoạt động đúng với câu hỏi tự luận.

## Tự kiểm bắt buộc
- `tu_luan_tu_do` không cho lưu nhầm câu hỏi
- `tu_luan_theo_cau` không cho submit khi chưa có câu hỏi
- `tu_luan_theo_cau` tính tổng điểm từ từng câu
- `tu_luan_tu_do` vẫn chấm toàn bài bình thường

## Điều kiện PASS
- Rule giữa các mode rõ ràng
- Không còn logic mơ hồ giữa tự luận tự do và tự luận theo câu

---

# PHASE 4 — TẠO NHANH CÂU HỎI TỰ LUẬN BẰNG TEXTBOX

## Mục tiêu
Cho giảng viên tạo trực tiếp câu hỏi tự luận trong màn hình chỉnh sửa bài kiểm tra.

## Việc phải làm
1. Bổ sung UI trong tab `questions` hoặc khu vực quản lý câu hỏi:
   - nút “Thêm câu hỏi tự luận”
   - form inline/modal
2. Form tạo nhanh phải có các field:
   - nội dung câu hỏi
   - gợi ý trả lời
   - đáp án mẫu
   - rubric chấm
   - điểm mặc định
   - mức độ
   - thứ tự
   - trạng thái
3. Tạo route/controller/action hoặc endpoint xử lý:
   - lưu câu hỏi vào ngân hàng câu hỏi
   - set `loai_cau_hoi = tu_luan`
   - trả về `question_id`
4. Sau khi tạo xong:
   - tự động add vào `question_ids` của bài kiểm tra đang edit
   - cập nhật UI ngay
5. Nếu repo có service quản lý chọn câu hỏi, phải tái sử dụng service đó thay vì sync thủ công rời rạc.

## Yêu cầu implementation
- Không tạo câu hỏi duplicate vô lý nếu user bấm submit nhiều lần
- Validation phải rõ
- Không tạo route/controller tách biệt quá xa style hiện tại của repo
- Nên ưu tiên thêm vào flow `BaiKiemTraController` hoặc service đang có nếu hợp lý

## Tự kiểm bắt buộc
- Tạo 1 câu hỏi tự luận thành công
- Tạo nhiều câu liên tiếp thành công
- Câu hỏi tạo xong xuất hiện trong danh sách đề hiện tại
- `question_ids` được sync đúng
- tổng điểm bài kiểm tra cập nhật đúng

## Điều kiện PASS
- Giảng viên tạo câu hỏi tự luận bằng textbox và dùng ngay trong đề được

---

# PHASE 5 — IMPORT FILE CÂU HỎI TỰ LUẬN

## Mục tiêu
Hoàn thiện flow import câu hỏi tự luận giống import trắc nghiệm, nhưng phù hợp bản chất tự luận.

## Việc phải làm
1. Kiểm tra `importPreview()` và `importConfirm()` hiện tại đang parse dữ liệu thế nào
2. Mở rộng parser để hỗ trợ câu hỏi tự luận với các cột ví dụ:
   - `noi_dung`
   - `goi_y_tra_loi`
   - `dap_an_mau`
   - `rubric`
   - `diem`
   - `muc_do`
   - `status`
   - `note`
3. Tạo hoặc cập nhật template import riêng cho câu hỏi tự luận
4. Ở bước preview:
   - hiển thị rõ các cột
   - báo lỗi nếu thiếu nội dung câu hỏi
   - báo lỗi nếu điểm không hợp lệ
5. Ở bước confirm:
   - tạo các câu hỏi vào ngân hàng
   - set `loai_cau_hoi = tu_luan`
   - trả về danh sách ID mới tạo
   - auto add vào bài kiểm tra hiện tại

## Yêu cầu implementation
- Không làm hỏng import trắc nghiệm đang có
- Nếu file import có cả dữ liệu không hợp lệ, phải báo lỗi rõ
- Nếu cần, tách logic parse tự luận vào service riêng
- Không hardcode cứng vào controller nếu logic dài

## Tự kiểm bắt buộc
- Import 1 câu hỏi tự luận thành công
- Import nhiều câu thành công
- Preview hiển thị đúng
- Confirm lưu đúng
- Các câu import xong được gắn vào đề

## Điều kiện PASS
- Flow import tự luận chạy được end-to-end

---

# PHASE 6 — HOÀN THIỆN MÀN HÌNH CHỌN VÀ QUẢN LÝ CÂU HỎI TỰ LUẬN

## Mục tiêu
Làm cho phần tự luận theo câu có UX giống phần trắc nghiệm trong màn hình edit bài kiểm tra.

## Việc phải làm
1. Trong màn hình edit bài kiểm tra, phần `questions` phải có:
   - danh sách câu đã chọn
   - nút tạo nhanh câu tự luận
   - nút import câu tự luận
   - khu vực chọn từ ngân hàng câu hỏi
2. Phải cho phép:
   - chọn 1 hoặc nhiều câu
   - bỏ chọn câu
   - đổi thứ tự
   - sửa điểm từng câu
3. Tổng điểm đề phải cập nhật theo điểm từng câu
4. Chỉ hiện câu hỏi tự luận khi mode là `tu_luan_theo_cau`
5. Nếu mode là `hon_hop`, phải xử lý đúng cả câu trắc nghiệm lẫn tự luận

## Yêu cầu implementation
- Không phá flow trắc nghiệm hiện tại
- Không để câu hỏi tự luận lẫn sai vào mode không phù hợp
- Nếu repo đang có filter/select service, phải tái sử dụng

## Tự kiểm bắt buộc
- Chọn câu hỏi tự luận từ ngân hàng
- Bỏ chọn câu
- Đổi điểm từng câu
- Tổng điểm cập nhật đúng
- Chuyển mode thì UI hiển thị hợp lý

## Điều kiện PASS
- Màn hình cấu hình đề tự luận theo câu hoạt động đầy đủ

---

# PHASE 7 — HỌC VIÊN LÀM BÀI TỰ LUẬN THEO TỪNG CÂU

## Mục tiêu
Cho học viên làm bài tự luận theo từng câu giống cách hệ thống đang xử lý trắc nghiệm theo câu.

## Việc phải làm
1. Kiểm tra flow:
   - `show`
   - `precheck`
   - `batDau`
   - `nopBai`
2. Nếu mode là `tu_luan_theo_cau`:
   - hiển thị danh sách câu hỏi theo thứ tự
   - mỗi câu có ô trả lời riêng
3. Khi học viên nộp bài:
   - tạo bản ghi `BaiLamBaiKiemTra`
   - lưu từng câu trả lời vào `chiTietTraLois`
4. Nếu mode là `tu_luan_tu_do`:
   - giữ flow hiện tại
   - lưu `noi_dung_bai_lam` tổng
5. Nếu hệ thống có autosave hoặc save draft, mở rộng cho câu tự luận nếu phù hợp kiến trúc hiện tại

## Yêu cầu implementation
- Không làm hỏng flow trắc nghiệm
- Không ghi đè sai giữa `noi_dung_bai_lam` tổng và `chiTietTraLois`
- Nếu không có câu nào trong đề thì không cho bắt đầu/nộp bài ở mode `tu_luan_theo_cau`

## Tự kiểm bắt buộc
- Học viên làm bài 1 câu tự luận
- Học viên làm bài nhiều câu tự luận
- Câu trả lời từng câu được lưu đúng
- `tu_luan_tu_do` vẫn hoạt động như cũ

## Điều kiện PASS
- Bài làm tự luận theo câu được lưu đúng cấu trúc

---

# PHASE 8 — CHẤM ĐIỂM TỰ LUẬN THEO TỪNG CÂU

## Mục tiêu
Hoàn thiện phần chấm bài để giảng viên chấm từng câu tự luận rõ ràng và ổn định.

## Việc phải làm
1. Rà `chamDiemShow()` và `chamDiemStore()`
2. Nếu bài làm có `chiTietTraLois` thuộc câu tự luận:
   - hiển thị từng câu hỏi
   - hiển thị câu trả lời tương ứng
   - giảng viên nhập điểm từng câu
   - giảng viên nhập nhận xét từng câu nếu cần
3. Cộng tổng điểm tự luận từ từng câu
4. Cập nhật:
   - `tong_diem_tu_luan`
   - `diem_so`
   - `trang_thai_cham`
   - `manual_graded_at`
   - `nguoi_cham_id`
5. Nếu là `tu_luan_tu_do`:
   - giữ flow chấm tổng bài hiện có

## Yêu cầu implementation
- Không duplicate logic chấm
- Không để điểm từng câu vượt quá điểm tối đa của câu
- Có validation rõ ràng
- Không phá nhánh chấm toàn bài hiện có

## Tự kiểm bắt buộc
- Chấm bài 1 câu tự luận
- Chấm bài nhiều câu tự luận
- Cộng tổng điểm đúng
- Nhận xét từng câu lưu đúng
- `tu_luan_tu_do` vẫn chấm tổng bài bình thường

## Điều kiện PASS
- Giảng viên chấm bài tự luận theo từng câu ổn định

---

# PHASE 9 — GỬI DUYỆT, PHÁT HÀNH VÀ KIỂM TRA ĐIỀU KIỆN HOÀN CHỈNH

## Mục tiêu
Đảm bảo bài kiểm tra tự luận chỉ được gửi duyệt/phát hành khi đã đủ cấu hình.

## Việc phải làm
1. Mở rộng `ensureReadyForApproval()` hoặc logic tương đương để kiểm riêng:
   - `tu_luan_theo_cau`:
     - có ít nhất 1 câu
     - mỗi câu có điểm hợp lệ
     - tổng điểm > 0
   - `tu_luan_tu_do`:
     - có đề bài tổng
     - có tổng điểm
2. Kiểm tra publish flow để chắc chắn:
   - không publish bài lỗi cấu hình
3. Bổ sung thông báo lỗi rõ ràng cho giảng viên

## Yêu cầu implementation
- Không phá flow approval/publish đang chạy
- Thông báo lỗi phải chỉ rõ thiếu gì
- Giữ đúng trạng thái nháp/chờ duyệt/đã duyệt/phát hành

## Tự kiểm bắt buộc
- Đề tự luận theo câu thiếu câu hỏi → không gửi duyệt được
- Đề có câu hỏi nhưng thiếu điểm → không gửi duyệt được
- Đề đầy đủ → gửi duyệt được
- Publish chỉ thành công khi đã duyệt

## Điều kiện PASS
- Approval/publish cho tự luận hoạt động chuẩn

---

# PHASE 10 — BÁO CÁO, LƯU TRỮ VÀ TRA CỨU BÀI LÀM TỰ LUẬN

## Mục tiêu
Bảo đảm hệ thống lưu trữ được đầy đủ dữ liệu của một bài kiểm tra tự luận.

## Việc phải làm
1. Rà lại dữ liệu cuối cùng cần lưu ở cấp bài làm:
   - học viên
   - bài kiểm tra
   - thời gian bắt đầu/nộp
   - tổng điểm
   - trạng thái chấm
   - người chấm
   - nhận xét tổng
2. Rà dữ liệu ở cấp từng câu:
   - câu hỏi
   - nội dung trả lời
   - điểm từng câu
   - nhận xét từng câu
3. Bổ sung màn hình hoặc API tra cứu nếu cần
4. Nếu hệ thống có export báo cáo bài làm:
   - đảm bảo xuất được dữ liệu tổng
   - và có thể mở rộng ra dữ liệu chi tiết từng câu

## Yêu cầu implementation
- Không gây N+1 query rõ rệt ở màn review/chấm
- Ưu tiên eager loading
- Không chỉ lưu được mà còn phải đọc ra được dễ dàng

## Tự kiểm bắt buộc
- Một bài làm tự luận theo câu có thể xem lại đầy đủ
- Giảng viên thấy được từng câu và từng câu trả lời
- Admin có thể tra cứu log chấm bài nếu flow hiện tại hỗ trợ

## Điều kiện PASS
- Dữ liệu lưu trữ và đọc lại đầy đủ, nhất quán

---

# PHASE 11 — TEST TÍCH HỢP TOÀN BỘ VÀ LÀM SẠCH

## Mục tiêu
Rà soát toàn bộ flow từ tạo đề → tạo câu hỏi → import → học viên làm → giảng viên chấm → duyệt/phát hành → lưu trữ.

## Kịch bản bắt buộc phải test
1. Tạo bài kiểm tra `tu_luan_theo_cau`
2. Tạo nhanh 1 câu hỏi tự luận bằng textbox
3. Tạo nhanh nhiều câu hỏi tự luận
4. Import file câu hỏi tự luận
5. Chọn câu và gán điểm
6. Gửi duyệt thành công khi đủ điều kiện
7. Publish thành công khi đã duyệt
8. Học viên làm bài với 1 câu
9. Học viên làm bài với nhiều câu
10. Lưu `chiTietTraLois` đúng
11. Giảng viên chấm từng câu
12. Cộng tổng điểm đúng
13. `tu_luan_tu_do` vẫn chạy bình thường
14. Flow trắc nghiệm không bị phá
15. Không tạo duplicate câu hỏi / duplicate sync / duplicate chấm điểm

## Việc phải làm
1. Rà code vừa sửa
2. Loại bỏ duplicate logic
3. Thêm test nếu repo có framework test đang dùng
4. Nếu chưa đủ test tự động thì viết checklist test tay đầy đủ

## Output bắt buộc
- danh sách test case
- pass/fail từng case
- lỗi đã sửa
- TODO còn lại
- kết luận sẵn sàng merge hay chưa

## Điều kiện PASS
- Tất cả flow chính đều chạy xuyên suốt
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
Khi xong phase 1, dừng lại và báo cáo đúng format.  
Chỉ tiếp tục phase 2 khi phase 1 đã PASS.
