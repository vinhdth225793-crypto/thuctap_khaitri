# PROMPT CHO GEMINI AGENT CODE
## Chức năng: Admin thêm / xóa / sửa học sinh vào khóa học
## Phạm vi: chỉ làm phía admin trước, giao diện học viên làm sau

---

## 1. PROMPT TỔNG QUÁT

```text
Bạn là một senior Laravel developer đang hỗ trợ phát triển đồ án thực tập về hệ thống học tập và kiểm tra online cho trung tâm giáo dục Khai Trí.

Công nghệ:
- Laravel
- Blade
- MySQL
- MVC rõ ràng
- Code sạch, dễ đọc, dễ mở rộng
- Ưu tiên tái sử dụng cấu trúc project hiện tại
- Ưu tiên code chạy thực tế, không viết demo giả

Bối cảnh nghiệp vụ:
- Hệ thống có admin, giảng viên, học viên
- Hiện tại cần làm chức năng ADMIN quản lý học viên trong khóa học
- Hệ thống đã có danh sách học viên tổng
- Admin cần thêm học viên vào khóa học
- Admin cần xóa học viên khỏi khóa học
- Admin cần sửa thông tin ghi danh nếu cần
- Giao diện học viên tự xem khóa học của mình làm sau, chưa làm trong phase này
- Nghiệp vụ hiện tại của đồ án dùng “nhóm ngành”, không dùng “môn học”

Yêu cầu làm việc:
- Chỉ làm đúng phase tôi yêu cầu
- Không làm sang phase khác nếu tôi chưa yêu cầu
- Trước khi code, phải đọc cấu trúc project hiện tại để tránh trùng logic
- Nếu project đã có bảng như hoc_vien_khoa_hoc hoặc tương tự thì ưu tiên tận dụng
- Nếu thiếu bảng thì mới tạo migration mới
- Khi trả lời, phải ghi rõ:
  1. phân tích ngắn
  2. file nào cần tạo
  3. file nào cần sửa
  4. code đầy đủ cho từng file
  5. lệnh artisan cần chạy
  6. checklist test thủ công
- Nếu có nhiều phương án, hãy chọn phương án an toàn nhất với project hiện tại
- Giao diện dùng Blade, đồng bộ với admin hiện có trong project

Mục tiêu cuối:
Xây dựng chức năng cho admin quản lý học viên trong khóa học, gồm:
- xem danh sách học viên đã thuộc khóa học
- thêm học viên vào khóa học từ danh sách học viên toàn hệ thống
- xóa học viên khỏi khóa học
- cập nhật thông tin ghi danh nếu cần
```

---

## 2. PHASE 1 — KIỂM TRA VÀ CHUẨN HÓA CẤU TRÚC DỮ LIỆU HỌC VIÊN KHÓA HỌC

```text
Hãy thực hiện PHASE 1 cho project Laravel hiện tại của tôi.

Mục tiêu phase 1:
Kiểm tra project hiện tại đã có cấu trúc dữ liệu quản lý học viên trong khóa học chưa, và chuẩn hóa lại nếu cần.

Yêu cầu nghiệp vụ:
- Một khóa học có nhiều học viên
- Một học viên có thể tham gia nhiều khóa học nếu nghiệp vụ cho phép
- Cần có bảng trung gian để lưu việc ghi danh học viên vào khóa học
- Bảng này cần đủ để dùng cho admin quản lý về sau

Yêu cầu kỹ thuật:
- Đọc project hiện tại để kiểm tra:
  - đã có bảng hoc_vien_khoa_hoc hay chưa
  - đã có model HocVienKhoaHoc hay chưa
  - relation giữa HocVien và KhoaHoc đã có chưa
- Nếu đã có rồi, hãy tận dụng và chỉnh lại nếu còn thiếu
- Nếu chưa có, hãy tạo mới
- Bảng ghi danh nên có tối thiểu:
  - id
  - hoc_vien_id
  - khoa_hoc_id
  - ngay_tham_gia hoặc ngay_ghi_danh
  - trang_thai
  - ghi_chu nếu cần
  - created_at
  - updated_at
- Cần ràng buộc tránh trùng một học viên trong cùng một khóa học
- Khai báo đầy đủ relation ở model

Không làm trong phase này:
- giao diện admin
- thêm học viên bằng form
- xóa học viên
- giao diện học viên

Tôi muốn bạn trả ra:
1. Phân tích ngắn cấu trúc hiện tại
2. Danh sách file cần tạo/sửa
3. Code đầy đủ
4. Lệnh artisan cần chạy
5. Checklist test phase 1

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 3. PHASE 2 — ADMIN XEM DANH SÁCH HỌC VIÊN TRONG TỪNG KHÓA HỌC

```text
Hãy thực hiện PHASE 2 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 1 đã xong
- Đã có relation dữ liệu giữa học viên và khóa học

Mục tiêu phase 2:
Admin xem được danh sách học viên thuộc từng khóa học.

Yêu cầu nghiệp vụ:
- Từ trang danh sách khóa học, admin có thể bấm vào nút “Học viên”
- Khi vào, admin xem được danh sách học viên thuộc khóa học đó
- Hiển thị các cột:
  - mã học viên nếu có
  - họ tên
  - email
  - số điện thoại
  - ngày ghi danh
  - trạng thái
- Có thống kê số lượng học viên hiện tại trong khóa học
- Giao diện bám theo style admin hiện có

Yêu cầu kỹ thuật:
- Tạo route admin phù hợp
- Tạo controller admin riêng hoặc tận dụng controller hiện có nếu hợp lý
- Tạo view danh sách học viên của khóa học
- Có eager loading hợp lý để tránh N+1
- Nếu project đã có menu hoặc nút trong trang khóa học thì gắn thêm nút “Học viên”

Không làm trong phase này:
- form thêm học viên
- xóa học viên
- sửa ghi danh
- giao diện học viên

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo/sửa
3. Code đầy đủ
4. Checklist test phase 2

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 4. PHASE 3 — ADMIN THÊM HỌC VIÊN VÀO KHÓA HỌC

```text
Hãy thực hiện PHASE 3 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 2 đã xong
- Admin đã xem được danh sách học viên trong khóa học

Mục tiêu phase 3:
Admin thêm học viên từ danh sách học viên toàn hệ thống vào một khóa học cụ thể.

Yêu cầu nghiệp vụ:
- Trong trang quản lý học viên của khóa học, admin có nút “Thêm học viên”
- Admin có thể chọn từ danh sách học viên toàn hệ thống
- Chỉ hiển thị các tài khoản học viên hợp lệ
- Không cho thêm trùng học viên đã có trong khóa học
- Có thể chọn một hoặc nhiều học viên nếu phù hợp với project hiện tại
- Khi thêm xong, hệ thống lưu vào bảng ghi danh học viên khóa học
- Có thông báo thành công hoặc lỗi rõ ràng

Yêu cầu kỹ thuật:
- Tạo form thêm học viên
- Có validate:
  - học_vien tồn tại
  - khóa_học tồn tại
  - không trùng dữ liệu
- Nếu phù hợp, hỗ trợ tìm kiếm học viên theo tên, email hoặc mã học viên
- Giao diện dùng Blade
- Ưu tiên làm cách đơn giản, dễ chạy trước

Không làm trong phase này:
- giao diện học viên
- lịch sử học viên
- điểm danh
- học phí
- phân lớp sâu hơn nếu chưa có

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo/sửa
3. Code đầy đủ
4. Checklist test phase 3

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 5. PHASE 4 — ADMIN XÓA HỌC VIÊN KHỎI KHÓA HỌC

```text
Hãy thực hiện PHASE 4 cho project Laravel hiện tại của tôi.

Mục tiêu phase 4:
Admin có thể xóa một học viên khỏi khóa học.

Yêu cầu nghiệp vụ:
- Trong danh sách học viên của khóa học, mỗi dòng có nút “Xóa khỏi khóa học”
- Khi admin xác nhận xóa:
  - hệ thống xóa bản ghi ghi danh
  - hoặc cập nhật trạng thái ngừng học nếu project hiện tại phù hợp hơn
- Sau khi xóa, học viên không còn xuất hiện trong danh sách học viên của khóa học
- Có thông báo rõ ràng

Yêu cầu kỹ thuật:
- Chọn cách an toàn nhất:
  - xóa mềm nếu cấu trúc hiện tại phù hợp
  - hoặc xóa bản ghi bảng trung gian nếu nghiệp vụ hiện tại đơn giản
- Validate để tránh xóa nhầm dữ liệu không tồn tại
- Route và action phải rõ ràng
- Giao diện có confirm trước khi xóa

Không làm trong phase này:
- giao diện học viên
- log hoạt động
- hoàn tiền, học phí, chuyển lớp

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo/sửa
3. Code đầy đủ
4. Checklist test phase 4

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 6. PHASE 5 — ADMIN SỬA THÔNG TIN GHI DANH HỌC VIÊN TRONG KHÓA HỌC

```text
Hãy thực hiện PHASE 5 cho project Laravel hiện tại của tôi.

Mục tiêu phase 5:
Admin có thể sửa thông tin ghi danh của học viên trong khóa học.

Yêu cầu nghiệp vụ:
- Admin có thể sửa các thông tin như:
  - ngày ghi danh
  - trạng thái
  - ghi chú
- Không sửa thông tin cá nhân gốc của học viên ở đây
- Chỉ sửa thông tin liên quan đến việc học viên đang thuộc khóa học đó

Yêu cầu kỹ thuật:
- Tạo form edit cho bản ghi ghi danh
- Validate dữ liệu đầu vào
- Giao diện rõ ràng, đơn giản
- Nếu project đã có modal/form pattern thì tái sử dụng

Không làm trong phase này:
- giao diện học viên
- lịch sử thay đổi
- báo cáo nâng cao

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo/sửa
3. Code đầy đủ
4. Checklist test phase 5

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 7. PHASE 6 — HOÀN THIỆN GIAO DIỆN ADMIN, NÚT LIÊN KẾT, VÀ KIỂM TRA LỖI

```text
Hãy thực hiện PHASE 6 cho project Laravel hiện tại của tôi.

Mục tiêu phase 6:
Hoàn thiện trải nghiệm sử dụng chức năng admin quản lý học viên trong khóa học.

Yêu cầu nghiệp vụ:
- Rà soát toàn bộ route liên quan
- Kiểm tra middleware admin
- Bổ sung nút liên kết hợp lý trong:
  - danh sách khóa học
  - chi tiết khóa học nếu có
  - sidebar admin nếu cần
- Đồng bộ giao diện Blade với phần admin hiện có
- Hiển thị thông báo thành công/thất bại rõ ràng
- Kiểm tra lỗi null, lỗi route name, lỗi query dư thừa
- Có thể thêm bộ lọc cơ bản nếu thuận tiện:
  - lọc theo trạng thái ghi danh
  - tìm học viên trong khóa học

Yêu cầu kỹ thuật:
- Không thêm chức năng mới ngoài việc hoàn thiện
- Tối ưu code ở mức hợp lý
- Đảm bảo code dễ bảo trì

Tôi muốn bạn trả ra:
1. Danh sách phần cần rà soát
2. File cần sửa
3. Code đầy đủ
4. Checklist test tổng thể phase 1 đến phase 6

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 8. THỨ TỰ NÊN LÀM

```text
Làm theo thứ tự:
1. Phase 1
2. Phase 2
3. Phase 3
4. Phase 4
5. Phase 5
6. Phase 6
```

---

## 9. CÂU CHỐT NÊN THÊM CUỐI MỖI PHASE

```text
Chỉ làm đúng phase này. Không tự động làm sang phase khác. Nếu project hiện tại đã có bảng/model/controller liên quan thì phải ưu tiên tận dụng trước khi tạo mới.
```
