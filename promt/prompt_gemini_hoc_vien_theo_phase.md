# PROMPT CHI TIẾT CHO GEMINI AGENT CODE
## Phần học viên cho hệ thống học tập và kiểm tra online
## Chia theo từng phase, làm xong phase nào dừng phase đó

---

## 1. PROMPT TỔNG QUÁT

```text
Bạn là một senior Laravel developer đang hỗ trợ phát triển đồ án thực tập Laravel về hệ thống học tập và kiểm tra online.

Bối cảnh project hiện tại:
- Hệ thống đã có phần admin và phần giảng viên tương đối rõ
- Project hiện đã có các model quan trọng như:
  - HocVien
  - HocVienKhoaHoc
  - DiemDanh
  - BaiKiemTra
  - LichHoc
  - TaiNguyenBuoiHoc
  - YeuCauHocVien
  - KhoaHoc
  - ModuleHoc
  - NhomNganh
- Hệ thống dùng khái niệm “nhóm ngành”, không dùng “môn học”
- Học viên sẽ đi theo flow:
  1. đăng ký tài khoản
  2. được admin duyệt tài khoản
  3. được admin cho vào lớp/khóa học hoặc tự xin vào lớp rồi chờ admin xác nhận
  4. sau khi tham gia khóa học, học viên xem được buổi học của khóa
  5. xem tài liệu giảng viên đăng
  6. theo dõi hoạt động học tập
  7. theo dõi tiến độ
  8. xem điểm
  9. làm bài kiểm tra sau này

Mục tiêu lần này:
Hãy code phần HỌC VIÊN theo đúng flow trên, chia theo từng phase riêng biệt, chức năng nào xong chức năng đó.

Nguyên tắc làm việc:
- Chỉ làm đúng phase tôi yêu cầu
- Không tự động làm sang phase khác
- Trước khi code phải đọc project hiện tại để tận dụng những gì đã có
- Nếu project đã có model/controller/migration liên quan thì ưu tiên tận dụng và mở rộng
- Không làm lại từ đầu nếu đã có nền
- Giao diện dùng Blade, đồng bộ với project hiện tại
- Phân quyền rõ ràng cho học viên
- Khi trả lời mỗi phase phải ghi rõ:
  1. phân tích ngắn dựa trên project hiện tại
  2. file cần tạo
  3. file cần sửa
  4. code đầy đủ cho từng file
  5. lệnh artisan cần chạy
  6. checklist test thủ công
- Nếu cần giả định thì phải nêu rõ trước khi code

Mục tiêu cuối:
Hoàn thiện phần học viên của hệ thống theo flow:
- tài khoản học viên
- tham gia khóa học
- xem buổi học
- xem tài liệu
- xem hoạt động
- xem tiến độ
- xem điểm
- chuẩn bị nền cho bài kiểm tra
```

---

## 2. PHASE 1 — HOÀN THIỆN ĐẦU VÀO HỌC VIÊN: ĐĂNG KÝ TÀI KHOẢN VÀ PHÊ DUYỆT

```text
Hãy thực hiện PHASE 1 cho project Laravel hiện tại của tôi.

Mục tiêu phase 1:
Hoàn thiện luồng đầu vào của học viên:
- đăng ký tài khoản
- chờ admin duyệt
- sau khi duyệt thì học viên đăng nhập được

Yêu cầu nghiệp vụ:
- Học viên đăng ký tài khoản bằng form hiện có hoặc form cần hoàn thiện
- Sau đăng ký, tài khoản không được học ngay nếu chưa được duyệt
- Admin là người phê duyệt cuối cùng
- Sau khi được duyệt, học viên đăng nhập được với vai trò học viên
- Trạng thái tài khoản phải rõ ràng

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - luồng đăng ký hiện có
  - bảng tài khoản chờ phê duyệt hiện có
  - model TaiKhoanChoPheDuyet nếu có
  - bảng nguoi_dung / hoc_vien hiện có
- Nếu logic hiện tại đã có thì hoàn thiện và chuẩn hóa
- Nếu cần, bổ sung validation, trạng thái, mapping dữ liệu
- Không làm phần tham gia khóa học ở phase này
- Không làm giao diện học tập ở phase này

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

## 3. PHASE 2 — HỌC VIÊN XEM DANH SÁCH KHÓA HỌC HIỆN CÓ VÀ XIN VÀO LỚP

```text
Hãy thực hiện PHASE 2 cho project Laravel hiện tại của tôi.

Điều kiện:
- Học viên đã có tài khoản hợp lệ
- Hệ thống đã có khóa học hiện có / đang mở

Mục tiêu phase 2:
Cho phép học viên xem danh sách khóa học hiện có và gửi yêu cầu xin vào lớp.

Yêu cầu nghiệp vụ:
- Học viên đăng nhập
- Học viên xem được danh sách khóa học hiện có hoặc đang mở
- Mỗi khóa học hiển thị tối thiểu:
  - tên khóa học
  - nhóm ngành
  - mô tả ngắn
  - trạng thái
  - ngày khai giảng nếu có
- Học viên có thể bấm:
  - Xin vào lớp / Đăng ký khóa học
- Sau khi gửi, hệ thống lưu yêu cầu chờ admin xác nhận
- Không cho gửi trùng yêu cầu nếu đã gửi trước đó
- Nếu học viên đã ở trong khóa học rồi thì không cho xin lại

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - đã có bảng YeuCauHocVien chưa
  - model YeuCauHocVien đang dùng cho nghiệp vụ gì
  - route học viên hiện có
- Tận dụng model/bảng hiện có nếu phù hợp
- Tạo controller/action/view học viên để xem khóa học và gửi yêu cầu
- Giao diện Blade đơn giản, rõ ràng
- Không làm duyệt yêu cầu phía admin ở phase này nếu admin đã có sẵn
- Nếu admin chưa có màn hình duyệt thì chỉ ghi chú để xử lý ở phase hoàn thiện

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

## 4. PHASE 3 — HỌC VIÊN XEM CÁC KHÓA HỌC ĐANG THAM GIA

```text
Hãy thực hiện PHASE 3 cho project Laravel hiện tại của tôi.

Điều kiện:
- Học viên đã được admin thêm vào khóa học
  hoặc
- Học viên đã gửi yêu cầu và được admin chấp nhận

Mục tiêu phase 3:
Cho phép học viên xem danh sách khóa học mình đang tham gia.

Yêu cầu nghiệp vụ:
- Học viên đăng nhập
- Học viên xem được danh sách khóa học của mình
- Mỗi khóa học hiển thị:
  - tên khóa học
  - nhóm ngành
  - trạng thái học
  - ngày tham gia
  - tiến độ tổng quan nếu có
- Học viên bấm vào được chi tiết khóa học

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - model HocVienKhoaHoc hiện có
  - relation giữa NguoiDung/HocVien và HocVienKhoaHoc
  - relation giữa HocVienKhoaHoc và KhoaHoc
- Tận dụng structure hiện có
- Tạo controller/action/view học viên phù hợp
- Có eager loading hợp lý
- Không làm buổi học chi tiết ở phase này
- Không làm tài liệu ở phase này

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

## 5. PHASE 4 — HỌC VIÊN XEM BUỔI HỌC CỦA KHÓA HỌC

```text
Hãy thực hiện PHASE 4 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 3 đã xong
- Project đã có dữ liệu LichHoc hoặc tương đương

Mục tiêu phase 4:
Cho phép học viên xem danh sách buổi học của khóa học mình đang tham gia.

Yêu cầu nghiệp vụ:
- Khi vào chi tiết khóa học, học viên xem được:
  - các module của khóa học
  - các buổi học / lịch học của khóa
- Mỗi buổi học hiển thị:
  - buổi số mấy
  - tên module
  - ngày học
  - giờ học
  - trạng thái buổi học
  - link học online nếu đã được mở cho học viên xem
- Học viên chỉ xem được buổi học của khóa mình tham gia
- Không xem được buổi học của khóa khác

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - model LichHoc
  - relation giữa KhoaHoc, ModuleHoc và LichHoc
- Tạo controller/action/view cho học viên
- Giao diện rõ ràng theo khóa học và theo buổi
- Nếu cần, chia tab theo module hoặc theo lịch học
- Không làm tài liệu chi tiết ở phase này

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Checklist test phase 4

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 6. PHASE 5 — HỌC VIÊN XEM TÀI LIỆU GIẢNG VIÊN ĐĂNG THEO TỪNG BUỔI HỌC

```text
Hãy thực hiện PHASE 5 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 4 đã xong
- Project đã có TaiNguyenBuoiHoc và dữ liệu tài nguyên theo buổi học

Mục tiêu phase 5:
Cho phép học viên xem tài liệu giảng viên đăng theo từng buổi học.

Yêu cầu nghiệp vụ:
- Học viên vào buổi học cụ thể
- Hệ thống hiển thị các tài liệu đã được giảng viên mở cho học viên xem
- Tài liệu có thể gồm:
  - file Word
  - file PDF
  - PowerPoint
  - link ngoài
  - bài tập
  - ghi chú
- Học viên có thể:
  - xem tài liệu
  - tải tài liệu nếu phù hợp
  - mở link ngoài
- Tài liệu chưa được giảng viên mở thì học viên không được thấy

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - model TaiNguyenBuoiHoc
  - relation với LichHoc
  - cột trạng thái hiển thị / publish hiện có
- Tạo controller/action/view cho học viên
- Chỉ query các tài nguyên đã được phép hiển thị
- Nếu file không tồn tại thì hiển thị trạng thái rõ
- Không làm nộp bài tập ở phase này

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Checklist test phase 5

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 7. PHASE 6 — HỌC VIÊN XEM HOẠT ĐỘNG HỌC TẬP

```text
Hãy thực hiện PHASE 6 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 3, 4, 5 đã có nền

Mục tiêu phase 6:
Cho phép học viên xem các hoạt động học tập liên quan đến khóa học của mình.

Yêu cầu nghiệp vụ:
- Học viên xem được các hoạt động như:
  - buổi học sắp tới
  - tài liệu mới được đăng
  - thông báo liên quan tới lớp
  - bài kiểm tra được mở sau này nếu có
- Có thể hiển thị theo dạng:
  - danh sách hoạt động gần đây
  - timeline
  - card hoạt động
- Chỉ hiển thị hoạt động của khóa học mà học viên đang tham gia

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xem:
  - đã có model ThongBao chưa
  - đã có dữ liệu hoạt động nào có thể tận dụng chưa
- Nếu chưa có bảng activity riêng, có thể tổng hợp từ dữ liệu hiện có:
  - lịch học
  - tài nguyên mới
  - thông báo
- Tạo controller/action/view học viên cho trang hoạt động
- Giao diện gọn, dễ hiểu

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Checklist test phase 6

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 8. PHASE 7 — HỌC VIÊN XEM TIẾN ĐỘ HỌC TẬP

```text
Hãy thực hiện PHASE 7 cho project Laravel hiện tại của tôi.

Điều kiện:
- Học viên đã xem được khóa học và buổi học
- Hệ thống có đủ dữ liệu để tính tiến độ ở mức tối thiểu

Mục tiêu phase 7:
Cho phép học viên xem tiến độ học tập của mình trong khóa học.

Yêu cầu nghiệp vụ:
- Học viên xem được:
  - đã học bao nhiêu buổi
  - còn bao nhiêu buổi
  - module nào đã hoàn thành
  - module nào chưa hoàn thành
  - tiến độ tổng quan theo phần trăm nếu tính được
- Nếu chưa đủ dữ liệu để tính phức tạp, hãy làm mức tối thiểu từ:
  - số buổi đã diễn ra
  - số buổi đã học/điểm danh
  - số module có lịch học đã hoàn tất

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - dữ liệu điểm danh có sẵn tới đâu
  - dữ liệu lịch học có sẵn tới đâu
- Tạo action/view hiển thị tiến độ
- Có thể hiển thị bằng card + progress bar
- Không làm bài kiểm tra ở phase này

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Checklist test phase 7

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 9. PHASE 8 — HỌC VIÊN XEM ĐIỂM VÀ KẾT QUẢ HỌC TẬP

```text
Hãy thực hiện PHASE 8 cho project Laravel hiện tại của tôi.

Điều kiện:
- Project đã có model BaiKiemTra hoặc dữ liệu kết quả liên quan
- Chưa cần làm giao diện làm bài nếu chưa có nền đầy đủ

Mục tiêu phase 8:
Cho phép học viên xem điểm và kết quả học tập của mình.

Yêu cầu nghiệp vụ:
- Học viên xem được:
  - danh sách bài kiểm tra đã có kết quả
  - điểm từng bài
  - trạng thái hoàn thành
  - ghi chú/nhận xét nếu có
- Nếu hiện tại project chưa có bảng kết quả đầy đủ thì:
  - hiển thị được tối đa những gì hiện có
  - viết code theo hướng dễ mở rộng sau này
- Chỉ học viên được xem điểm của chính mình

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - model BaiKiemTra đã có chưa
  - có bảng kết quả bài làm chưa
- Nếu dữ liệu chưa hoàn chỉnh, hãy thiết kế theo hướng read-only nền tảng hoặc ghi chú rõ phần cần chờ phase sau
- Tạo controller/action/view học viên để xem điểm
- Không làm chức năng làm bài kiểm tra ở phase này nếu chưa có nền

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Checklist test phase 8

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 10. PHASE 9 — HOÀN THIỆN GIAO DIỆN, SIDEBAR, ROUTE, PHÂN QUYỀN CHO HỌC VIÊN

```text
Hãy thực hiện PHASE 9 cho project Laravel hiện tại của tôi.

Mục tiêu phase 9:
Hoàn thiện toàn bộ phần học viên sau khi đã có các chức năng chính.

Yêu cầu:
- Rà soát route học viên
- Rà soát middleware và phân quyền học viên
- Hoàn thiện sidebar/menu học viên với các mục:
  - Khóa học của tôi
  - Xin vào lớp / Khóa học hiện có
  - Buổi học
  - Tài liệu
  - Hoạt động
  - Tiến độ
  - Điểm
- Đồng bộ giao diện Blade với project hiện có
- Kiểm tra route name, lỗi null, lỗi query dư, eager loading
- Hiển thị thông báo success/error rõ ràng
- Không thêm chức năng mới ngoài việc hoàn thiện

Tôi muốn bạn trả ra:
1. Danh sách phần cần rà soát
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Lệnh artisan cần chạy nếu có
6. Checklist test tổng thể phase 1 đến phase 9

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 11. THỨ TỰ NÊN LÀM

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
9. Phase 9
```

---

## 12. CÂU CHỐT NÊN THÊM CUỐI MỖI PHASE

```text
Chỉ làm đúng phase này. Không tự động làm sang phase khác. Nếu project hiện tại đã có bảng/model/controller liên quan thì phải ưu tiên tận dụng trước khi tạo mới. Nếu cần giả định thì phải nêu rõ giả định trước khi code.
```
