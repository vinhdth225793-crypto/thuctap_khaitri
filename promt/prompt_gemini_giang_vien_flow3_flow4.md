# PROMPT CHI TIẾT CHO GEMINI AGENT CODE
## Phần giảng viên: Flow 3 (quản lý học viên trong lớp học) và Flow 4 (điểm danh học viên)
## Chia theo từng phase, làm xong phase nào dừng phase đó

---

## 1. PROMPT TỔNG QUÁT

```text
Bạn là một senior Laravel developer đang hỗ trợ phát triển đồ án thực tập Laravel về hệ thống học tập và kiểm tra online.

Bối cảnh project hiện tại:
- Hệ thống đã có phần admin khá đầy đủ
- Phần giảng viên hiện đã có nền:
  - xem lộ trình giảng dạy
  - biết module đã nhận dạy
  - biết các buổi học của module
  - có phần đăng tài liệu sau buổi dạy
- Repo hiện tại đã có các controller ở nhánh giảng viên như:
  - PhanCongController
  - TaiNguyenController
  - DiemDanhController
  - BaiGiangController
  - BaiKiemTraController
=> Vì vậy nhiệm vụ lần này là hoàn thiện tiếp đúng trên nền code đang có, không làm lại từ đầu.

Phạm vi chức năng cần làm trong lần này:
- FLOW 3: Quản lý học viên trong lớp học
- FLOW 4: Điểm danh học viên

Mục tiêu nghiệp vụ:
1. Giảng viên xem được danh sách học viên của lớp học/module mình phụ trách
2. Giảng viên theo dõi được tình hình học viên:
   - đi học
   - kết quả học
   - trạng thái tham gia
3. Giảng viên có thể tạo yêu cầu thay đổi học viên trong lớp:
   - đề nghị thêm học viên
   - đề nghị xóa học viên
   - đề nghị cập nhật trạng thái/ghi chú học viên
   - nhưng admin là người duyệt cuối cùng
4. Giảng viên có thể điểm danh học viên theo từng buổi học
5. Hệ thống lưu lịch sử điểm danh và cho phép xem lại thống kê

Lưu ý nghiệp vụ:
- Không dùng khái niệm “môn học”, dùng “nhóm ngành”
- Giảng viên chỉ thao tác trong phạm vi module/lớp mình phụ trách
- Danh sách học viên chính thức vẫn do admin kiểm soát cuối cùng
- Điểm danh phải gắn với đúng buổi học / lịch học
- Một học viên chỉ có một trạng thái điểm danh cho một buổi học

Yêu cầu làm việc:
- Chỉ làm đúng phase tôi yêu cầu
- Không tự động làm sang phase khác
- Trước khi code phải đọc cấu trúc project hiện tại để tận dụng:
  - model HocVienKhoaHoc nếu có
  - model LichHoc nếu có
  - controller DiemDanhController hiện tại
  - controller giảng viên hiện tại
  - route hiện tại
  - blade hiện tại của giảng viên
- Nếu project đã có bảng/model/controller liên quan thì ưu tiên tận dụng, chỉ bổ sung thiếu sót
- Khi trả lời mỗi phase phải ghi rõ:
  1. phân tích ngắn dựa trên project hiện tại
  2. file cần tạo
  3. file cần sửa
  4. code đầy đủ cho từng file
  5. lệnh artisan cần chạy
  6. checklist test thủ công
- Nếu cần giả định theo project hiện tại thì phải nêu rõ trước khi code

Mục tiêu cuối:
Hoàn thiện phần giảng viên cho:
- quản lý học viên trong lớp học
- điểm danh học viên theo từng buổi học
- luồng đề xuất thay đổi học viên để admin duyệt
```

---

## 2. FLOW 3 — QUẢN LÝ HỌC VIÊN TRONG LỚP HỌC

### PHASE 1 — GIẢNG VIÊN XEM DANH SÁCH HỌC VIÊN CỦA LỚP/MODULE MÌNH DẠY

```text
Hãy thực hiện FLOW 3 - PHASE 1 cho project Laravel hiện tại của tôi.

Mục tiêu phase 1:
Cho phép giảng viên xem danh sách học viên thuộc khóa học/lớp/module mà mình đang phụ trách.

Yêu cầu nghiệp vụ:
- Giảng viên chỉ xem được học viên của lớp hoặc khóa học có module mình đang dạy
- Hiển thị các thông tin:
  - mã học viên nếu có
  - họ tên
  - email
  - số điện thoại
  - ngày tham gia khóa học
  - trạng thái học viên trong khóa học
  - ghi chú nếu có
- Có thống kê tổng số học viên
- Có thể truy cập từ màn hình chi tiết lớp học/module giảng viên đang dạy

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - bảng/model HocVienKhoaHoc hoặc tương đương
  - relation giữa giảng viên -> module -> khóa học -> học viên
  - controller nào đang phù hợp để thêm action này
- Nếu project đã có relation thì tận dụng
- Nếu thiếu relation model thì bổ sung
- Tạo route giảng viên phù hợp
- Tạo action controller phù hợp
- Tạo view blade hiển thị danh sách học viên
- Giao diện bám theo style giảng viên hiện tại
- Có eager loading hợp lý để tránh query dư
- Không làm thêm/sửa/xóa học viên chính thức ở phase này
- Không làm điểm danh ở phase này

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Lệnh artisan cần chạy
6. Checklist test phase 1

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

### PHASE 2 — GIẢNG VIÊN THEO DÕI TÌNH HÌNH HỌC VIÊN

```text
Hãy thực hiện FLOW 3 - PHASE 2 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 1 đã xong
- Giảng viên đã xem được danh sách học viên

Mục tiêu phase 2:
Cho phép giảng viên xem thêm thông tin theo dõi học viên để phục vụ quản lý lớp học.

Yêu cầu nghiệp vụ:
- Trên danh sách học viên hoặc trang chi tiết học viên trong lớp, giảng viên có thể xem:
  - số buổi đã tham gia
  - số buổi vắng
  - số buổi đi trễ nếu có
  - số bài kiểm tra đã làm nếu có dữ liệu
  - trạng thái học tập tổng quan nếu có
- Nếu một phần dữ liệu chưa có trong project, hãy hiển thị ở mức tối thiểu có thể từ dữ liệu hiện tại
- Không yêu cầu làm dashboard quá phức tạp, chỉ cần đủ để giảng viên theo dõi lớp

Yêu cầu kỹ thuật:
- Đọc dữ liệu hiện tại của project:
  - đã có bảng điểm danh chưa
  - đã có bảng bài kiểm tra/kết quả chưa
- Nếu đã có thì tận dụng để tổng hợp
- Nếu chưa có dữ liệu đầy đủ thì hiển thị tạm những gì hiện có và chú thích rõ trong code
- Có thể thêm cột thống kê hoặc khu vực card tổng hợp
- Không làm chỉnh sửa dữ liệu ở phase này

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Checklist test phase 2

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

### PHASE 3 — GIẢNG VIÊN TẠO YÊU CẦU THÊM / XÓA / CẬP NHẬT HỌC VIÊN, ADMIN DUYỆT

```text
Hãy thực hiện FLOW 3 - PHASE 3 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 1 đã xong
- Danh sách học viên chính thức vẫn do admin kiểm soát

Mục tiêu phase 3:
Cho phép giảng viên tạo yêu cầu thay đổi danh sách học viên, nhưng admin là người xác nhận cuối cùng.

Yêu cầu nghiệp vụ:
- Giảng viên có thể tạo 3 loại yêu cầu:
  1. yêu cầu thêm học viên vào khóa học/lớp
  2. yêu cầu xóa học viên khỏi khóa học/lớp
  3. yêu cầu cập nhật trạng thái hoặc ghi chú học viên
- Khi giảng viên gửi yêu cầu:
  - yêu cầu được lưu ở trạng thái chờ duyệt
  - chưa làm thay đổi dữ liệu chính thức ngay
- Admin có thể:
  - chấp nhận
  - từ chối
- Chỉ khi admin chấp nhận thì dữ liệu chính thức mới thay đổi
- Giảng viên chỉ được tạo yêu cầu cho lớp/module mình đang phụ trách

Yêu cầu kỹ thuật:
- Đọc project hiện tại xem đã có bảng yêu cầu nào tương tự chưa
- Nếu chưa có thì thiết kế bảng yêu cầu hợp lý, ví dụ:
  - id
  - loai_yeu_cau
  - khoa_hoc_id
  - hoc_vien_id
  - giang_vien_id
  - noi_dung / ghi_chu
  - trang_thai_duyet
  - admin_duyet_id
  - thoi_gian_duyet
- Tạo model nếu cần
- Tạo route/controller/action cho giảng viên tạo yêu cầu
- Tạo route/controller/action cho admin duyệt yêu cầu nếu project chưa có
- Tạo form giao diện đơn giản nhưng dùng được
- Phân quyền rõ ràng
- Không làm realtime notification ở phase này

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Lệnh artisan cần chạy
6. Checklist test phase 3

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 3. FLOW 4 — ĐIỂM DANH HỌC VIÊN

### PHASE 1 — CHUẨN HÓA DỮ LIỆU ĐIỂM DANH THEO BUỔI HỌC

```text
Hãy thực hiện FLOW 4 - PHASE 1 cho project Laravel hiện tại của tôi.

Mục tiêu phase 1:
Chuẩn hóa cấu trúc dữ liệu điểm danh học viên theo từng buổi học/lịch học.

Yêu cầu nghiệp vụ:
- Mỗi buổi học có danh sách điểm danh riêng
- Mỗi học viên chỉ có 1 trạng thái điểm danh trong 1 buổi
- Các trạng thái điểm danh gồm:
  - co_mat
  - vang_mat
  - vao_tre
  - có thể mở rộng sau nếu cần
- Có thể có ghi chú

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - đã có bảng/model điểm danh chưa
  - controller DiemDanhController hiện xử lý tới đâu
  - bảng lich_hoc hiện đang làm khóa chính cho buổi học như thế nào
- Nếu đã có bảng điểm danh thì tận dụng và chuẩn hóa
- Nếu chưa có thì tạo migration/model mới
- Cần ràng buộc unique hợp lý, ví dụ:
  - unique(lich_hoc_id, hoc_vien_id)
- Bổ sung relation:
  - LichHoc hasMany DiemDanh
  - HocVien / NguoiDung hasMany DiemDanh
  - DiemDanh belongsTo LichHoc
  - DiemDanh belongsTo HocVien/NguoiDung
- Không làm giao diện lớn ở phase này ngoài mức tối thiểu

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Lệnh artisan cần chạy
6. Checklist test phase 1

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

### PHASE 2 — GIẢNG VIÊN ĐIỂM DANH HỌC VIÊN THEO TỪNG BUỔI

```text
Hãy thực hiện FLOW 4 - PHASE 2 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 1 đã xong
- Hệ thống đã có dữ liệu điểm danh
- Giảng viên đã xem được danh sách học viên lớp mình dạy

Mục tiêu phase 2:
Cho phép giảng viên điểm danh học viên theo từng buổi học.

Yêu cầu nghiệp vụ:
- Giảng viên vào một buổi học/lịch học cụ thể
- Hệ thống hiển thị danh sách học viên của lớp/khóa học tương ứng
- Giảng viên có thể chọn trạng thái cho từng học viên:
  - có mặt
  - vắng mặt
  - vào trễ
- Có thể nhập ghi chú nếu cần
- Lưu một lần cho toàn bộ danh sách học viên
- Chỉ giảng viên được phân công module/lịch học đó mới được thao tác

Yêu cầu kỹ thuật:
- Đọc project hiện tại để tận dụng DiemDanhController hiện có nếu phù hợp
- Tạo/hoàn thiện route giảng viên cho điểm danh
- Tạo/hoàn thiện action store/update điểm danh
- Tạo view blade điểm danh dễ thao tác
- Có validate:
  - lịch học hợp lệ
  - học viên thuộc lớp hợp lệ
  - quyền giảng viên hợp lệ
- Có thể dùng form submit một lần cho cả lớp
- Không làm tự động điểm danh qua Zoom/Meet

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Lệnh artisan cần chạy
6. Checklist test phase 2

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

### PHASE 3 — GIẢNG VIÊN XEM LẠI VÀ CHỈNH SỬA ĐIỂM DANH

```text
Hãy thực hiện FLOW 4 - PHASE 3 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 2 đã xong

Mục tiêu phase 3:
Cho phép giảng viên xem lại lịch sử điểm danh của từng buổi và chỉnh sửa nếu cần.

Yêu cầu nghiệp vụ:
- Giảng viên có thể mở lại một buổi học đã điểm danh
- Xem lại toàn bộ trạng thái điểm danh của học viên
- Chỉnh sửa điểm danh nếu nhập sai
- Hiển thị rõ:
  - trạng thái từng học viên
  - thời gian cập nhật gần nhất
  - ghi chú nếu có
- Chỉ giảng viên phụ trách buổi đó mới được sửa

Yêu cầu kỹ thuật:
- Tận dụng dữ liệu điểm danh hiện có
- Tạo action edit/update nếu chưa có
- Giao diện có thể dùng lại form điểm danh với dữ liệu đã fill sẵn
- Có thông báo lưu thành công/thất bại rõ ràng

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Checklist test phase 3

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

### PHASE 4 — THỐNG KÊ ĐIỂM DANH CHO GIẢNG VIÊN

```text
Hãy thực hiện FLOW 4 - PHASE 4 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 2 hoặc 3 đã xong
- Hệ thống đã có dữ liệu điểm danh thực tế

Mục tiêu phase 4:
Cho phép giảng viên xem thống kê điểm danh của lớp học.

Yêu cầu nghiệp vụ:
- Giảng viên xem được:
  - tổng số buổi học
  - tổng số lần có mặt
  - tổng số lần vắng
  - tổng số lần vào trễ
  - danh sách học viên nghỉ nhiều
- Có thể xem theo:
  - toàn lớp
  - từng học viên
  - từng module nếu phù hợp
- Giao diện đủ rõ để giảng viên theo dõi chuyên cần

Yêu cầu kỹ thuật:
- Tận dụng dữ liệu điểm danh hiện có
- Tạo query tổng hợp hợp lý, tránh N+1
- Có thể làm card thống kê + bảng chi tiết
- Không cần biểu đồ phức tạp nếu chưa cần
- Không làm báo cáo PDF/export ở phase này

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Checklist test phase 4

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 4. PHASE HOÀN THIỆN — RÀ SOÁT ROUTE, SIDEBAR, PHÂN QUYỀN, GIAO DIỆN

```text
Hãy thực hiện PHASE HOÀN THIỆN cho project Laravel hiện tại của tôi.

Mục tiêu:
Hoàn thiện trải nghiệm sử dụng cho phần:
- quản lý học viên trong lớp học
- điểm danh học viên

Yêu cầu:
- Rà soát route giảng viên
- Rà soát middleware và phân quyền
- Bổ sung liên kết vào sidebar giảng viên nếu cần:
  - Học viên lớp học
  - Điểm danh
- Đồng bộ giao diện Blade với phần giảng viên hiện có
- Kiểm tra route name, lỗi null, lỗi query dư, eager loading
- Hiển thị thông báo thành công/thất bại rõ ràng
- Không thêm chức năng mới ngoài việc hoàn thiện

Tôi muốn bạn trả ra:
1. Danh sách phần cần rà soát
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Lệnh artisan cần chạy nếu có
6. Checklist test tổng thể

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 5. THỨ TỰ NÊN LÀM

```text
Làm theo thứ tự:
FLOW 3:
1. Phase 1
2. Phase 2
3. Phase 3

FLOW 4:
1. Phase 1
2. Phase 2
3. Phase 3
4. Phase 4

Cuối cùng:
- Phase hoàn thiện
```

---

## 6. CÂU CHỐT NÊN THÊM CUỐI MỖI PHASE

```text
Chỉ làm đúng phase này. Không tự động làm sang phase khác. Nếu project hiện tại đã có bảng/model/controller liên quan thì phải ưu tiên tận dụng trước khi tạo mới. Nếu cần giả định thì phải nêu rõ giả định trước khi code.
```
