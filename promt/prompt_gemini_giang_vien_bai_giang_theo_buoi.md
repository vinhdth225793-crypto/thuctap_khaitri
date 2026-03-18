# PROMPT CHI TIẾT CHO GEMINI AGENT CODE
## Chức năng giảng viên đăng bài giảng vào buổi học
## Chia theo từng phase, làm xong phase nào dừng phase đó

---

## 1. PROMPT TỔNG QUÁT

```text
Bạn là một senior Laravel developer đang hỗ trợ phát triển đồ án thực tập về hệ thống học tập và kiểm tra online cho trung tâm giáo dục Khai Trí.

Công nghệ và nguyên tắc:
- Laravel
- Blade
- MySQL
- MVC rõ ràng
- Code sạch, dễ đọc, dễ mở rộng
- Ưu tiên tận dụng cấu trúc project hiện tại
- Không viết lại từ đầu nếu project đã có nền
- Code phải chạy thực tế
- Giao diện bám theo style hiện có của project

Bối cảnh repo hiện tại:
- Phần giảng viên đã có khu vực “Trung tâm điều hành”
- Sidebar giảng viên hiện đã có các menu như Bảng điều khiển, Lộ trình giảng dạy, Hồ sơ giảng viên
- Trong repo đã có các controller giảng viên như:
  - PhanCongController
  - TaiNguyenController
  - DiemDanhController
  - BaiKiemTraController
- View chi tiết bài dạy của giảng viên đã có phần hiển thị:
  - lịch dạy theo buổi
  - link online
  - tài nguyên đã đăng
  - bài kiểm tra
- Model LichHoc hiện đã có relation taiNguyen()
=> Vì vậy nhiệm vụ lần này là hoàn thiện chức năng đăng bài giảng của giảng viên trên nền code đang có, không làm mới từ đầu.

Mục tiêu nghiệp vụ:
- Giảng viên có thể đăng bài giảng vào từng buổi học
- Một buổi học có thể có 1 hoặc nhiều bài giảng / tài nguyên
- Loại nội dung cho phép:
  - file Word
  - file PowerPoint
  - PDF
  - file tài liệu khác nếu phù hợp
  - link ngoài
  - mô tả / ghi chú
- Giảng viên có thể đăng trước bài giảng cho các buổi chưa tới ngày học
- Sau khi dạy xong, giảng viên có thể bật/mở bài giảng cho học viên xem
- Mỗi tài nguyên phải gắn với đúng buổi học
- Cần có nơi lưu file bài giảng riêng cho giảng viên
- Ưu tiên lưu trong public/giang-vien/khoa-hoc theo cấu trúc rõ ràng, dễ quản lý
- Cần thêm menu “Bài giảng” trong khu vực giảng viên / Trung tâm điều hành giảng viên
- Chức năng làm cho phía giảng viên trước, giao diện học viên xem bài giảng làm sau

Yêu cầu kỹ thuật:
- Trước khi code phải đọc project hiện tại để xác định:
  - bảng/model tài nguyên buổi học hiện có
  - relation giữa lich_hoc và tài nguyên
  - route/controller/view hiện có của giảng viên
- Nếu đã có bảng tài nguyên rồi thì ưu tiên tận dụng và mở rộng
- Nếu cấu trúc hiện tại chưa đủ thì tạo migration bổ sung cột hoặc bảng cần thiết
- Khi trả lời mỗi phase phải ghi rõ:
  1. phân tích ngắn dựa trên repo hiện tại
  2. file cần tạo
  3. file cần sửa
  4. code đầy đủ cho từng file
  5. lệnh artisan cần chạy
  6. checklist test thủ công
- Chỉ làm đúng phase tôi yêu cầu
- Không tự động làm sang phase khác
- Nếu cần giả định thì phải nêu rõ giả định trước khi code

Mục tiêu cuối:
Hoàn thiện chức năng giảng viên đăng, quản lý và công khai bài giảng/tài nguyên theo từng buổi học trong hệ thống.
```

---

## 2. PHASE 1 — RÀ SOÁT VÀ CHUẨN HÓA CẤU TRÚC DỮ LIỆU TÀI NGUYÊN BUỔI HỌC

```text
Hãy thực hiện PHASE 1 cho project Laravel hiện tại của tôi.

Mục tiêu phase 1:
Rà soát cấu trúc dữ liệu hiện có cho tài nguyên buổi học và chuẩn hóa lại để phục vụ chức năng bài giảng của giảng viên.

Yêu cầu nghiệp vụ:
- Mỗi buổi học có thể có nhiều tài nguyên
- Mỗi tài nguyên gắn với đúng 1 buổi học
- Mỗi tài nguyên cần hỗ trợ:
  - loại tài nguyên: bai_giang / tai_lieu / bai_tap / link_ngoai
  - tiêu đề
  - mô tả
  - file đính kèm
  - link ngoài
  - trạng thái hiển thị cho học viên
  - thời điểm mở xem nếu cần
  - thứ tự hiển thị nếu cần
- Giảng viên có thể đăng trước nhưng chưa mở cho học viên
- Sau buổi học giảng viên có thể chuyển sang trạng thái mở xem

Yêu cầu kỹ thuật:
- Đọc repo hiện tại để xác định:
  - bảng/model tài nguyên buổi học hiện đang là gì
  - controller TaiNguyenController hiện có xử lý những field nào
  - relation trong model LichHoc hiện có ra sao
- Nếu bảng/model hiện tại đã dùng được thì bổ sung cột còn thiếu
- Nếu thiếu:
  - them cột trang_thai_hien_thi hoặc is_published
  - them cột ngay_mo_hien_thi nếu cần
  - them cột thu_tu_hien_thi nếu cần
  - giữ tương thích với dữ liệu cũ
- Cần ràng buộc rõ relation:
  - LichHoc hasMany TaiNguyenBuoiHoc
  - TaiNguyenBuoiHoc belongsTo LichHoc
- Không làm giao diện ở phase này ngoài mức tối thiểu
- Không làm menu ở phase này
- Không làm phần học viên ở phase này

Tôi muốn bạn trả ra:
1. Phân tích ngắn dựa trên project hiện tại
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Lệnh artisan cần chạy
6. Checklist test phase 1

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 3. PHASE 2 — THIẾT LẬP NƠI LƯU FILE BÀI GIẢNG CỦA GIẢNG VIÊN

```text
Hãy thực hiện PHASE 2 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 1 đã xong
- Hệ thống đã có cấu trúc tài nguyên buổi học

Mục tiêu phase 2:
Thiết lập nơi lưu file bài giảng của giảng viên rõ ràng, dễ quản lý và phù hợp với project.

Yêu cầu nghiệp vụ:
- File bài giảng của giảng viên phải được lưu riêng, dễ tìm
- Ưu tiên lưu trong thư mục:
  public/giang-vien/khoa-hoc
- Nên tổ chức theo cấu trúc dễ quản lý, ví dụ:
  public/giang-vien/khoa-hoc/{khoa_hoc_id}/{lich_hoc_id}/
  hoặc cấu trúc hợp lý hơn nếu project hiện tại phù hợp
- Khi upload file:
  - lưu đường dẫn vào database
  - giữ tên file an toàn
  - tránh trùng tên file
- Khi xóa tài nguyên thì xóa luôn file nếu là file nội bộ
- Chỉ cho phép các định dạng phù hợp:
  - doc
  - docx
  - ppt
  - pptx
  - pdf
  - xls/xlsx nếu cần
  - txt nếu cần
- Có giới hạn dung lượng hợp lý và nêu rõ trong code

Yêu cầu kỹ thuật:
- Đọc lại controller upload hiện tại của project
- Nếu đang dùng Storage::disk('public'), hãy cấu hình để lưu đúng vào public/giang-vien/khoa-hoc
- Nếu cần, chuẩn hóa helper/service nhỏ để tái sử dụng cho upload tài nguyên buổi học
- Không làm giao diện lớn ở phase này
- Tập trung vào backend upload, delete, path, validation

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

## 4. PHASE 3 — GIẢNG VIÊN THÊM BÀI GIẢNG/TÀI NGUYÊN VÀO TỪNG BUỔI HỌC

```text
Hãy thực hiện PHASE 3 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 1 và 2 đã xong
- Giảng viên đã có màn hình chi tiết bài dạy / lịch dạy

Mục tiêu phase 3:
Cho phép giảng viên thêm 1 hoặc nhiều bài giảng/tài nguyên vào từng buổi học.

Yêu cầu nghiệp vụ:
- Giảng viên vào buổi học cụ thể
- Có thể thêm tài nguyên gồm:
  - loại tài nguyên
  - tiêu đề
  - mô tả
  - file đính kèm
  - link ngoài
- Một buổi học có thể đăng nhiều tài nguyên
- Giảng viên được đăng trước cho buổi chưa tới ngày học
- Chỉ giảng viên được phân công module đó mới được thêm tài nguyên
- Có validate rõ ràng:
  - lich_hoc hợp lệ
  - đúng quyền giảng viên
  - file đúng định dạng
  - link ngoài đúng URL nếu có

Yêu cầu kỹ thuật:
- Tận dụng TaiNguyenController hiện có nếu hợp lý
- Nếu controller hiện có chưa chuẩn, refactor an toàn
- Tạo hoặc chỉnh form trong giao diện giảng viên
- Giao diện Blade phải hiển thị rõ:
  - thêm file
  - thêm link ngoài
  - chọn loại tài nguyên
- Không làm phần học viên xem ở phase này
- Không làm mở công khai ở phase này ngoài dữ liệu lưu trạng thái nháp / chờ mở nếu cần

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

## 5. PHASE 4 — GIẢNG VIÊN SỬA, XÓA, SẮP XẾP NHIỀU BÀI GIẢNG TRONG MỘT BUỔI HỌC

```text
Hãy thực hiện PHASE 4 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 3 đã xong
- Buổi học đã có thể có nhiều tài nguyên

Mục tiêu phase 4:
Cho phép giảng viên quản lý danh sách nhiều bài giảng/tài nguyên trong một buổi học.

Yêu cầu nghiệp vụ:
- Giảng viên xem được toàn bộ tài nguyên của một buổi học
- Có thể:
  - sửa tài nguyên
  - xóa tài nguyên
  - thay đổi thứ tự hiển thị
- Một buổi học có thể có nhiều bài giảng, file, link ngoài
- Khi xóa file nội bộ phải xóa luôn file trên server nếu phù hợp
- Chỉ giảng viên phụ trách mới được thao tác

Yêu cầu kỹ thuật:
- Nếu chưa có cột thu_tu_hien_thi thì bổ sung từ phase 1 hoặc phase này
- Tạo action edit/update/destroy rõ ràng
- Có thể làm giao diện đơn giản trước:
  - bảng danh sách
  - nút sửa
  - nút xóa
  - ô nhập thứ tự
- Không cần kéo thả nếu project hiện tại chưa phù hợp
- Không làm phần học viên ở phase này

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Lệnh artisan cần chạy
6. Checklist test phase 4

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 6. PHASE 5 — GIẢNG VIÊN ĐĂNG TRƯỚC BÀI GIẢNG NHƯNG CHƯA MỞ CHO HỌC VIÊN XEM

```text
Hãy thực hiện PHASE 5 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 3 và 4 đã xong

Mục tiêu phase 5:
Cho phép giảng viên đăng trước bài giảng vào buổi học, nhưng chưa cho học viên xem ngay.

Yêu cầu nghiệp vụ:
- Khi thêm tài nguyên, giảng viên có thể chọn trạng thái:
  - nháp
  - đã chuẩn bị
  - đã mở cho học viên xem
- Hoặc dùng mô hình đơn giản:
  - chưa mở
  - đã mở
- Giảng viên có thể đăng tài nguyên cho buổi học chưa tới ngày học
- Tài nguyên chưa mở thì học viên về sau sẽ không thấy
- Tài nguyên đã mở thì học viên mới được xem

Yêu cầu kỹ thuật:
- Thêm logic trạng thái hiển thị vào model/controller/view
- Form thêm/sửa tài nguyên phải có trường trạng thái
- Giao diện danh sách tài nguyên phải hiển thị rõ trạng thái
- Chưa cần làm giao diện học viên
- Nhưng backend phải sẵn sàng để sau này query chỉ lấy tài nguyên đã mở

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Lệnh artisan cần chạy
6. Checklist test phase 5

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 7. PHASE 6 — SAU KHI DẠY XONG, GIẢNG VIÊN MỞ BÀI GIẢNG CHO HỌC VIÊN XEM

```text
Hãy thực hiện PHASE 6 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 5 đã xong
- Hệ thống đã có trạng thái nháp/chưa mở/đã mở

Mục tiêu phase 6:
Sau khi học xong, giảng viên có thể mở bài giảng để học viên xem lại.

Yêu cầu nghiệp vụ:
- Trong từng buổi học, giảng viên có thao tác:
  - mở tài nguyên cho học viên xem
  - ẩn lại nếu cần
- Có thể mở từng tài nguyên riêng lẻ
- Hoặc nếu hợp lý có thêm thao tác mở toàn bộ tài nguyên của buổi học
- Có thể quy định chỉ khi buổi học hoàn thành mới cho mở, hoặc giảng viên được mở thủ công
- Hệ thống phải lưu rõ trạng thái hiển thị

Yêu cầu kỹ thuật:
- Tạo action publish/unpublish hoặc tương đương
- Có nút thao tác nhanh trong danh sách tài nguyên
- Giao diện hiển thị badge trạng thái
- Chuẩn bị backend để sau này học viên query được danh sách bài giảng đã mở
- Không làm giao diện học viên ở phase này

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Lệnh artisan cần chạy
6. Checklist test phase 6

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 8. PHASE 7 — THÊM MENU “BÀI GIẢNG” TRONG TRUNG TÂM ĐIỀU HÀNH GIẢNG VIÊN

```text
Hãy thực hiện PHASE 7 cho project Laravel hiện tại của tôi.

Điều kiện:
- Các phase nền về tài nguyên bài giảng đã xong hoặc gần xong

Mục tiêu phase 7:
Thêm menu “Bài giảng” trong khu vực Trung tâm điều hành giảng viên và tạo màn hình quản lý bài giảng phù hợp.

Yêu cầu nghiệp vụ:
- Trong sidebar giảng viên, thêm mục:
  - Bài giảng
- Mục này dẫn tới khu vực quản lý bài giảng của giảng viên
- Trang bài giảng có thể hiển thị:
  - danh sách khóa học đang dạy
  - danh sách module
  - danh sách buổi học
  - tổng số tài nguyên đã đăng
  - trạng thái tài nguyên
- Tên hiển thị khu vực:
  - “Trung tâm điều hành giảng viên”
  - có thêm phân hệ “Bài giảng”
- Bám theo UI hiện tại của project

Yêu cầu kỹ thuật:
- Đọc sidebar-giang-vien hiện tại và cập nhật đúng style
- Tạo route giảng viên cho trang bài giảng
- Tạo controller/action hoặc dùng controller hiện có nếu hợp lý
- Tạo view index tổng hợp cho bài giảng
- Liên kết từ trang tổng tới từng buổi học để quản lý tài nguyên

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Lệnh artisan cần chạy
6. Checklist test phase 7

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 9. PHASE 8 — HOÀN THIỆN ROUTE, MIDDLEWARE, PHÂN QUYỀN, GIAO DIỆN PHẦN BÀI GIẢNG GIẢNG VIÊN

```text
Hãy thực hiện PHASE 8 cho project Laravel hiện tại của tôi.

Mục tiêu phase 8:
Rà soát và hoàn thiện toàn bộ phần bài giảng của giảng viên.

Yêu cầu:
- Rà soát route giảng viên liên quan tới bài giảng/tài nguyên
- Rà soát middleware và bảo mật quyền giảng viên theo module được phân công
- Kiểm tra route name, form action, CSRF, method spoofing
- Kiểm tra eager loading để tránh query dư
- Đồng bộ giao diện Blade
- Thêm thông báo success/error rõ ràng
- Kiểm tra upload file, xóa file, cập nhật trạng thái, link ngoài
- Không làm giao diện học viên ở phase này
- Không làm chức năng học viên xem bài giảng ở phase này

Tôi muốn bạn trả ra:
1. Danh sách phần cần rà soát
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Lệnh artisan cần chạy nếu có
6. Checklist test tổng thể phase 1 đến phase 8

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 10. THỨ TỰ NÊN LÀM

```text
Làm theo thứ tự:
1. Phase 1
2. Phase 2
3. Phase 3
4. Phase 4
5. Phase 5
6. Phase 6
7. Phase 7
8. Phase 8
```

---

## 11. CÂU CHỐT NÊN THÊM CUỐI MỖI PHASE

```text
Chỉ làm đúng phase này. Không tự động làm sang phase khác. Nếu project hiện tại đã có bảng/model/controller liên quan thì phải ưu tiên tận dụng trước khi tạo mới. Nếu cần giả định thì phải nêu rõ giả định trước khi code.
```
