# PROMPT CHO AI AGENT CODE

Bạn đang làm việc trên repo Laravel hiện có của tôi.  
Mục tiêu là triển khai **triệt để nghiệp vụ chốt điểm học viên theo module**, đồng thời hoàn thiện phần **kết quả học tập cho học viên** và **quản lý/lưu hồ sơ kết quả cho admin**.

## Bối cảnh repo hiện tại
Repo hiện đã có các thành phần nền như sau:

- `BaiLamBaiKiemTra`: lưu từng lần học viên làm bài, gồm `lan_lam_thu`, `diem_so`, `trang_thai_cham`, `nguoi_cham_id`, `nhan_xet`, thời gian bắt đầu/nộp, v.v.
- `KetQuaHocTap`: lưu kết quả học tập tổng hợp ở cấp bài kiểm tra, module và khóa học, với các cột như `diem_diem_danh`, `diem_kiem_tra`, `diem_tong_ket`, `trang_thai`, `nhan_xet_giang_vien`, `chi_tiet`.
- `KetQuaHocTapService`: đang refresh kết quả theo bài kiểm tra, module và khóa học. Hiện logic đang lấy điểm bài kiểm tra theo attempt tốt nhất, tính điểm module bằng trung bình kết quả bài kiểm tra + điểm danh theo trọng số, và tính điểm khóa học theo cấu hình hiện có.
- `GiangVien\BaiKiemTraController`: đang xử lý chấm điểm, xem điểm kiểm tra, báo cáo điểm; sau khi chấm có nối với pipeline cập nhật kết quả.
- `GiangVien\PhanCongController`: đã có màn `khoa-hoc/{id}/ket-qua` để giảng viên xem kết quả khóa học/học viên, nhưng repo hiện chưa có thao tác nghiệp vụ “chốt điểm module” đúng nghĩa.

## Nghiệp vụ mới cần đạt
1. Một **module** có thể có nhiều bài kiểm tra:
   - bài kiểm tra nhỏ / bài kiểm tra buổi
   - bài kiểm tra lớn / cuối module
2. Điểm danh học viên theo các buổi học của module được quy đổi thành **điểm quá trình**.
3. Nhiều bài kiểm tra nhỏ được tính trung bình lại thành **một đầu điểm nhỏ**.
4. Đầu điểm nhỏ đó được đứng ngang hàng với **bài kiểm tra lớn cuối module** để tính ra **điểm kiểm tra module**.
5. Sau khi mọi dữ liệu đã đủ, giảng viên có một chức năng **Chốt điểm module** cho từng học viên hoặc theo lô.
6. Sau khi chốt, hệ thống lưu:
   - điểm các bài kiểm tra
   - điểm trung bình bài kiểm tra
   - điểm quá trình
   - điểm giảng viên chốt / kết quả học tập chính thức
   - người chốt
   - thời gian chốt
   - trạng thái chốt
7. Học viên ở màn **Kết quả học tập** phải thấy được:
   - điểm các bài kiểm tra
   - điểm trung bình của tất cả bài kiểm tra đã làm
   - điểm quá trình
   - điểm giảng viên chốt là kết quả học tập chính thức
8. Admin phải có màn quản lý các kết quả đã chốt để **lưu hồ sơ/xét duyệt**.

---

# NGUYÊN TẮC BẮT BUỘC

## 1. Làm theo phase, không nhảy cóc
Chỉ làm **1 phase tại 1 thời điểm**.  
Không được chuyển phase tiếp theo nếu phase hiện tại chưa PASS.

## 2. Tận dụng tối đa cấu trúc hiện có
Phải tái sử dụng tối đa:
- `KetQuaHocTapService`
- `KetQuaHocTap`
- `BaiLamBaiKiemTra`
- màn `khoa-hoc/{id}/ket-qua`
- màn `diem-kiem-tra`
- pipeline chấm điểm hiện có

## 3. Không refactor lan rộng
Chỉ chỉnh những phần liên quan trực tiếp đến:
- tổng hợp điểm module
- chốt điểm module
- hiển thị kết quả học tập cho học viên
- quản lý kết quả chốt cho admin

## 4. Mỗi phase phải tự kiểm
Sau mỗi phase phải báo cáo:
- Những gì đã làm
- File đã sửa/thêm
- Migration/Model/Service/Controller/View/Route đã thay đổi
- Logic chính đã implement
- Tự kiểm
- Rủi ro còn lại
- PASS / CHƯA PASS

## 5. Không phá flow cũ
Các flow đang có như:
- chấm bài tự luận
- chấm bài trắc nghiệm
- xem điểm theo bài kiểm tra
- refresh kết quả học tập hiện tại  
phải vẫn chạy được sau khi thêm nghiệp vụ mới.

---

# PHASE 1 — KHẢO SÁT CHI TIẾT FLOW HIỆN TẠI

## Mục tiêu
Xác định chính xác repo hiện tại đang làm gì cho:
- điểm bài kiểm tra
- điểm danh
- kết quả học tập theo module/khóa
- màn giảng viên xem kết quả
- màn học viên xem kết quả
- phần admin đang có/đang thiếu

## Việc phải làm
1. Đọc kỹ tối thiểu các file:
   - `app/Services/KetQuaHocTapService.php`
   - `app/Models/KetQuaHocTap.php`
   - `app/Models/BaiLamBaiKiemTra.php`
   - `app/Http/Controllers/GiangVien/PhanCongController.php`
   - `app/Http/Controllers/GiangVien/BaiKiemTraController.php`
   - `routes/web.php`
   - các view liên quan tới:
     - giảng viên xem kết quả khóa học
     - giảng viên xem điểm kiểm tra
     - học viên xem kết quả học tập
2. Xác định rõ:
   - dữ liệu nào đang được lưu ở cấp bài kiểm tra
   - dữ liệu nào đang được lưu ở cấp module
   - dữ liệu nào đang được lưu ở cấp khóa
   - logic tính điểm đang nằm ở đâu
   - route/màn nào phù hợp nhất để gắn chức năng chốt điểm
3. Kết luận rõ:
   - hiện tại có hay chưa có “chốt điểm”
   - nếu chưa có thì vị trí cắm chuẩn là ở đâu

## Output bắt buộc
- sơ đồ flow hiện tại
- danh sách file liên quan
- gap analysis
- đề xuất chính xác phase 2 → phase cuối

## Điều kiện PASS
- Đã xác định rõ nơi cắm chức năng chốt điểm
- Đã xác định rõ dữ liệu cần thêm
- Đã xác định rõ chỗ nào tái sử dụng được

---

# PHASE 2 — THIẾT KẾ DỮ LIỆU CHO “CHỐT ĐIỂM MODULE”

## Mục tiêu
Bổ sung dữ liệu cần thiết để lưu kết quả chốt.

## Việc phải làm
1. Kiểm tra bảng/model `ket_qua_hoc_tap` hiện tại.
2. Bổ sung các field cần thiết nếu chưa có, ưu tiên:
   - `da_chot`
   - `nguoi_chot_id`
   - `chot_luc`
   - `diem_trung_binh_bai_kiem_tra`
   - `diem_qua_trinh`
   - `diem_giang_vien_chot`
   - `trang_thai_luu_ho_so`
   - `nguoi_duyet_id`
   - `duyet_luc`
   - `chi_tiet_chot` hoặc tận dụng `chi_tiet`
3. Nếu thấy `ket_qua_hoc_tap` không đủ sạch để chứa lịch sử chốt, cân nhắc tạo thêm bảng history/snapshot như:
   - `ket_qua_hoc_tap_chot_logs`
   nhưng chỉ làm nếu thật sự cần.
4. Cập nhật model `KetQuaHocTap`:
   - `fillable`
   - `casts`
   - relations với `nguoi_chot`, `nguoi_duyet` nếu phù hợp style repo

## Yêu cầu implementation
- Không xóa field cũ
- Không phá dữ liệu cũ
- Migration phải nullable hợp lý
- Không đổi tên cột cũ nếu không bắt buộc

## Tự kiểm bắt buộc
- Migration up/down chạy ổn
- Model đọc/ghi field mới đúng
- Không ảnh hưởng record cũ

## Điều kiện PASS
- Đã có đủ dữ liệu nền cho nghiệp vụ chốt điểm và lưu hồ sơ

---

# PHASE 3 — CHUẨN HÓA LOGIC PHÂN LOẠI BÀI KIỂM TRA NHỎ / LỚN

## Mục tiêu
Xây được rule rõ ràng để tính điểm module theo đúng nghiệp vụ mới.

## Việc phải làm
1. Kiểm tra trong repo hiện tại bài kiểm tra có những field nào để phân biệt:
   - bài kiểm tra buổi
   - bài kiểm tra cuối module
   - bài kiểm tra cuối khóa
2. Xác định cách phân loại bài kiểm tra trong module:
   - nhóm **bài nhỏ**
   - nhóm **bài lớn**
3. Nếu field hiện tại chưa đủ rõ, bổ sung config/rule phù hợp, ví dụ:
   - tái sử dụng `loai_bai_kiem_tra`
   - hoặc thêm mapping nội bộ tại service nếu đã có enum đủ dùng
4. Chuẩn hóa nghiệp vụ:
   - `diem_tb_bai_nho = avg(các bài nhỏ)`
   - `diem_bai_lon = điểm bài cuối module`
   - `diem_trung_binh_bai_kiem_tra = avg(diem_tb_bai_nho, diem_bai_lon)`
   - nếu chưa có bài lớn thì fallback hợp lý

## Yêu cầu implementation
- Không phá logic bài cuối khóa đang có trong `KetQuaHocTapService`
- Nếu repo đã có enum/type, phải tái sử dụng
- Không hardcode lung tung ở controller

## Tự kiểm bắt buộc
- Module chỉ có bài nhỏ
- Module có bài nhỏ + bài lớn
- Module có 1 bài lớn duy nhất
- Không nhầm bài cuối khóa thành bài cuối module

## Điều kiện PASS
- Đã có rule tính điểm kiểm tra module đúng theo nghiệp vụ mới

---

# PHASE 4 — TÁCH RIÊNG SERVICE TÍNH ĐIỂM CHỐT MODULE

## Mục tiêu
Không nhồi toàn bộ logic vào `KetQuaHocTapService`.

## Việc phải làm
1. Tạo service mới, ví dụ:
   - `ModuleFinalScoreService`
   - hoặc `KetQuaHocTapFinalizeService`
2. Service này phải tính được cho **1 học viên trong 1 module**:
   - điểm các bài kiểm tra
   - điểm trung bình bài kiểm tra
   - điểm quá trình
   - điểm giảng viên chốt
3. Dữ liệu đầu vào phải lấy từ:
   - `KetQuaHocTap` cấp bài kiểm tra
   - `DiemDanh` / `LichHoc` của module
   - cấu hình trọng số của khóa học
4. Kết quả đầu ra nên trả về object/array chuẩn hóa gồm:
   - `exam_scores`
   - `average_exam_score`
   - `process_score`
   - `final_score`
   - `attendance_summary`
   - `calculation_breakdown`

## Yêu cầu implementation
- Tách logic đủ sạch để controller chỉ gọi service
- Có thể tái sử dụng cho cả UI preview trước khi chốt và thao tác chốt thật
- Không phá `KetQuaHocTapService` hiện tại; chỉ refactor nhẹ nếu cần

## Tự kiểm bắt buộc
- Tính đúng khi đủ dữ liệu
- Tính đúng khi thiếu bài lớn
- Tính đúng khi chưa có điểm danh
- Tính đúng khi học viên chưa làm một số bài

## Điều kiện PASS
- Service tính điểm module hoạt động độc lập và ổn định

---

# PHASE 5 — THÊM CHỨC NĂNG “GIẢNG VIÊN CHỐT ĐIỂM MODULE”

## Mục tiêu
Cho giảng viên chốt điểm module cho từng học viên hoặc theo lô.

## Việc phải làm
1. Thêm route mới vào nhánh giảng viên, ví dụ:
   - `POST /giang-vien/khoa-hoc/{khoaHoc}/module/{module}/ket-qua/{hocVien}/chot`
   - có thể thêm route chốt hàng loạt nếu phù hợp
2. Thêm method controller phù hợp, ưu tiên trong `PhanCongController` hoặc controller riêng cho kết quả học tập nếu repo đang cần tách bớt.
3. Trước khi chốt, hệ thống phải kiểm tra:
   - mọi bài kiểm tra cần thiết đã chấm xong chưa
   - có bài nào còn `cho_cham` không
   - có dữ liệu điểm danh của module chưa
4. Khi chốt:
   - gọi service tính điểm module
   - lưu:
     - `diem_trung_binh_bai_kiem_tra`
     - `diem_qua_trinh`
     - `diem_giang_vien_chot`
     - `da_chot = true`
     - `nguoi_chot_id`
     - `chot_luc`
     - `chi_tiet_chot`
5. Nếu đã chốt rồi:
   - không được chốt lại bừa
   - nếu cần sửa phải có flow “mở chốt / chốt lại” riêng

## Yêu cầu implementation
- Không để route chốt nằm ở màn chấm từng bài
- Chốt phải đặt ở màn quản lý kết quả theo khóa/module
- Có transaction khi ghi dữ liệu chốt

## Tự kiểm bắt buộc
- Chốt 1 học viên thành công
- Không cho chốt khi còn bài chưa chấm
- Không cho chốt lặp vô tội vạ
- Chốt xong dữ liệu lưu đúng

## Điều kiện PASS
- Giảng viên chốt điểm module được end-to-end

---

# PHASE 6 — MỞ RỘNG MÀN GIẢNG VIÊN “KẾT QUẢ KHÓA HỌC”

## Mục tiêu
Biến màn `khoa-hoc/{id}/ket-qua` thành nơi preview và chốt điểm.

## Việc phải làm
1. Tận dụng flow hiện tại trong `PhanCongController::ketQuaHocTap()` và view tương ứng.
2. Với mỗi học viên và mỗi module, hiển thị đầy đủ:
   - **Điểm các bài kiểm tra**
   - **Điểm trung bình bài kiểm tra**
   - **Điểm quá trình**
   - **Điểm giảng viên chốt**
3. Trước khi chốt, hiển thị kết quả preview tính toán từ service mới.
4. Sau khi chốt, hiển thị:
   - trạng thái đã chốt
   - người chốt
   - thời gian chốt
5. Nếu cần, bổ sung nút:
   - `Xem chi tiết cách tính`
   - `Chốt điểm`
   - `Mở chốt` (nếu user có quyền)

## Yêu cầu implementation
- Không làm màn quá nặng
- Tránh N+1 query
- Ưu tiên eager loading và preload theo khóa/module/học viên

## Tự kiểm bắt buộc
- Giảng viên xem được đầy đủ dữ liệu trước khi chốt
- Sau khi chốt giao diện phản ánh đúng trạng thái
- Chốt xong vẫn xem lại được breakdown

## Điều kiện PASS
- Màn giảng viên đủ để vận hành nghiệp vụ chốt điểm thực tế

---

# PHASE 7 — CẬP NHẬT MÀN HỌC VIÊN “KẾT QUẢ HỌC TẬP”

## Mục tiêu
Học viên thấy rõ 4 lớp thông tin theo đúng nghiệp vụ mới.

## Việc phải làm
1. Tìm route/controller/view của học viên cho `ket-qua-hoc-tap`.
2. Cập nhật UI để hiển thị theo module:
   - **Điểm các bài kiểm tra**
   - **Điểm trung bình của tất cả bài kiểm tra**
   - **Điểm quá trình**
   - **Điểm giảng viên chốt**
3. Với phần “điểm các bài kiểm tra”, nếu có thể thì hiển thị:
   - tên bài kiểm tra
   - loại bài kiểm tra
   - số lần làm
   - điểm chính thức
4. Nếu module chưa được chốt:
   - hiển thị “Chưa chốt”
   - không gọi đó là kết quả chính thức
5. Nếu đã chốt:
   - hiển thị rõ đây là **kết quả học tập chính thức**

## Yêu cầu implementation
- Không dùng dữ liệu tự tính ở view
- Tính toán phải từ service hoặc dữ liệu đã lưu
- Không phá màn học viên hiện có

## Tự kiểm bắt buộc
- Học viên thấy đủ 4 block
- Module chưa chốt hiển thị đúng
- Module đã chốt hiển thị đúng
- Nếu học viên làm nhiều lần thì vẫn hiển thị điểm bài kiểm tra hợp lý

## Điều kiện PASS
- Màn học viên phản ánh đúng nghiệp vụ mới

---

# PHASE 8 — THÊM MÀN ADMIN QUẢN LÝ VÀ LƯU HỒ SƠ

## Mục tiêu
Admin có nơi xem, duyệt và lưu hồ sơ các kết quả đã chốt.

## Việc phải làm
1. Thêm route admin mới, ví dụ:
   - danh sách khóa học có kết quả chốt
   - danh sách học viên theo khóa/module
   - chi tiết hồ sơ kết quả
2. Tái sử dụng tối đa logic load dữ liệu từ màn giảng viên nếu có thể.
3. Admin phải thấy:
   - điểm các bài kiểm tra
   - điểm trung bình bài kiểm tra
   - điểm quá trình
   - điểm giảng viên chốt
   - trạng thái chốt
   - trạng thái lưu hồ sơ
4. Thêm thao tác:
   - `Duyệt lưu hồ sơ`
   - hoặc `Đánh dấu đã lưu hồ sơ`
5. Khi admin duyệt:
   - cập nhật `trang_thai_luu_ho_so`
   - `nguoi_duyet_id`
   - `duyet_luc`

## Yêu cầu implementation
- Không tạo một pipeline dữ liệu khác biệt với giảng viên
- Admin chỉ quản lý hồ sơ và trạng thái xét duyệt/lưu trữ
- Không thay đổi điểm chốt ở màn admin nếu không có yêu cầu đặc biệt

## Tự kiểm bắt buộc
- Admin xem được danh sách kết quả đã chốt
- Admin xem được chi tiết từng học viên
- Admin duyệt/lưu hồ sơ thành công
- Trạng thái duyệt được lưu đúng

## Điều kiện PASS
- Admin có đủ nghiệp vụ quản lý hồ sơ kết quả

---

# PHASE 9 — KHÓA CHỈNH SỬA VÀ FLOW MỞ CHỐT

## Mục tiêu
Đảm bảo sau khi chốt, dữ liệu không bị chỉnh sửa âm thầm.

## Việc phải làm
1. Xác định sau khi `da_chot = true`, các thao tác nào phải bị khóa:
   - chỉnh điểm chốt
   - ghi đè kết quả module
   - cập nhật tay ngoài quy trình
2. Nếu cần thay đổi sau chốt:
   - thêm flow `Mở chốt`
   - chỉ cho người có quyền
   - lưu audit reason
3. Xem xét các chỗ có thể tự refresh đè dữ liệu đã chốt từ `KetQuaHocTapService`
4. Chặn hoặc điều chỉnh để:
   - dữ liệu chốt không bị service thường ghi đè nhầm
   - hoặc chỉ update phần “tạm tính”, còn phần “đã chốt” giữ nguyên

## Yêu cầu implementation
- Đây là phase rất quan trọng
- Không để `refreshAllForCourseStudent()` vô tình ghi đè điểm đã chốt
- Nếu cần, tách rõ:
   - dữ liệu tạm
   - dữ liệu đã chốt

## Tự kiểm bắt buộc
- Chốt xong service refresh thường không phá dữ liệu chốt
- Mở chốt hoạt động đúng
- Có audit tối thiểu khi mở chốt/chốt lại

## Điều kiện PASS
- Dữ liệu đã chốt an toàn theo đúng nghiệp vụ

---

# PHASE 10 — CHUẨN HÓA CÔNG THỨC VÀ CHI TIẾT TÍNH ĐIỂM

## Mục tiêu
Đảm bảo công thức được lưu rõ, giải thích được, truy xuất được.

## Việc phải làm
1. Chuẩn hóa công thức:
   - `diem_tb_bai_nho`
   - `diem_bai_lon`
   - `diem_trung_binh_bai_kiem_tra`
   - `diem_qua_trinh`
   - `diem_giang_vien_chot`
2. Lưu breakdown vào `chi_tiet_chot` hoặc `chi_tiet` dưới dạng JSON:
   - danh sách bài nhỏ
   - điểm từng bài
   - bài lớn nào được dùng
   - tổng buổi/số buổi tham gia
   - tỷ lệ điểm danh
   - công thức cuối cùng
3. Đảm bảo có thể hiển thị lại breakdown cho:
   - giảng viên
   - admin
   - học viên nếu cần ở mức rút gọn

## Yêu cầu implementation
- Công thức phải tái lập được
- Người khác đọc lại phải hiểu điểm ra từ đâu
- Không chỉ lưu số cuối cùng

## Tự kiểm bắt buộc
- Một record đã chốt có đầy đủ breakdown
- Đọc lại từ DB hiểu được cách tính
- UI xem chi tiết khớp với dữ liệu đã lưu

## Điều kiện PASS
- Kết quả chốt minh bạch và kiểm tra được

---

# PHASE 11 — TEST TÍCH HỢP TOÀN BỘ

## Mục tiêu
Kiểm tra toàn bộ flow từ chấm bài → tính điểm → chốt → học viên xem → admin lưu hồ sơ.

## Kịch bản bắt buộc phải test
1. Module chỉ có bài kiểm tra nhỏ
2. Module có bài nhỏ + bài cuối module
3. Học viên có nhiều lần làm bài
4. Có học viên chưa tham gia đủ buổi
5. Có bài tự luận chưa chấm xong
6. Giảng viên thử chốt khi còn bài chưa chấm
7. Giảng viên chốt thành công khi đủ điều kiện
8. Học viên xem kết quả trước khi chốt
9. Học viên xem kết quả sau khi chốt
10. Admin xem và lưu hồ sơ kết quả đã chốt
11. Refresh kết quả học tập sau chốt không làm hỏng dữ liệu chốt
12. Flow cũ của chấm điểm và xem điểm bài kiểm tra không bị phá

## Việc phải làm
1. Viết test tự động nếu repo có nền test phù hợp
2. Nếu chưa, viết checklist test tay rất chi tiết
3. Rà lại query, eager loading, duplicate logic

## Output bắt buộc
- danh sách test case
- pass/fail từng case
- lỗi đã sửa
- TODO còn lại
- xác nhận sẵn sàng merge hay chưa

## Điều kiện PASS
- Flow vận hành xuyên suốt
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
