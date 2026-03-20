# PROMPT CHI TIẾT CHO GEMINI/CODEX
## Phần học viên theo đúng hệ thống Laravel hiện tại
## Chia theo từng phase, làm xong phase nào dừng phase đó

---

## 1. PROMPT TỔNG QUÁT

```text
Bạn là một Senior Laravel Developer đang hỗ trợ phát triển đồ án Laravel của tôi về hệ thống quản lý trung tâm đào tạo.

Bối cảnh project hiện tại:
- Framework: Laravel 12
- PHP: 8.2
- Hệ thống đã có 3 vai trò chính:
  - admin
  - giang_vien
  - hoc_vien
- Hệ thống đang dùng các model quan trọng như:
  - NguoiDung
  - GiangVien
  - HocVien
  - HocVienKhoaHoc
  - KhoaHoc
  - ModuleHoc
  - LichHoc
  - TaiNguyenBuoiHoc
  - BaiKiemTra
  - DiemDanh
  - YeuCauHocVien
  - ThongBao
- Hệ thống dùng khái niệm “khóa học”, “module học”, “lịch học”, không đi theo kiểu LMS chung chung.
- Hệ thống hiện tại đã có sẵn nền phần học viên ở mức:
  - đăng ký học viên
  - đăng nhập
  - xem khóa học của tôi
  - xem chi tiết khóa học
  - xem buổi học
  - xem tài liệu giảng viên công khai
  - vào lớp online nếu có link và đúng trạng thái

Flow học viên theo hệ thống hiện tại:
1. Người dùng đăng ký tài khoản học viên
2. Hệ thống tạo tài khoản học viên ngay, không chờ admin duyệt
3. Học viên đăng nhập vào khu vực học viên
4. Học viên chỉ thấy khóa học khi đã được ghi danh vào bảng hoc_vien_khoa_hoc
5. Việc ghi danh hiện tại xảy ra theo 2 cách:
   - admin thêm trực tiếp học viên vào khóa học
   - giảng viên gửi yêu cầu, admin duyệt rồi hệ thống thêm học viên vào khóa học
6. Học viên vào “Khóa học của tôi”
7. Học viên vào chi tiết khóa học
8. Học viên xem danh sách buổi học theo module
9. Học viên xem tài liệu giảng viên đã công khai
10. Học viên vào lớp online nếu buổi học đang diễn ra
11. Các phần còn thiếu cần hoàn thiện dần:
   - học viên tự xin vào lớp
   - dashboard dùng dữ liệu thật
   - theo dõi hoạt động học tập
   - tiến độ học tập
   - bài kiểm tra phía học viên
   - điểm và kết quả học tập

Mục tiêu lần này:
Hãy code phần học viên theo đúng hệ thống hiện tại của tôi, chia theo từng phase riêng biệt. Chỉ làm đúng phase tôi yêu cầu.

Nguyên tắc làm việc:
- Chỉ làm đúng phase tôi yêu cầu
- Không tự động làm sang phase tiếp theo
- Trước khi code phải đọc codebase hiện tại để tận dụng những gì đã có
- Nếu đã có model/controller/migration/view/route liên quan thì phải ưu tiên tận dụng và mở rộng
- Không làm lại từ đầu nếu project đã có nền
- Code phải bám sát naming hiện tại của project
- Dùng Blade cho giao diện
- Tôn trọng phân quyền theo role hiện tại
- Khi trả lời mỗi phase phải ghi rõ:
  1. phân tích ngắn theo code hiện tại
  2. file cần tạo
  3. file cần sửa
  4. code đầy đủ cho từng file
  5. lệnh artisan cần chạy nếu có
  6. checklist test thủ công
- Nếu project hiện tại thiếu nền để làm đầy đủ phase, phải nêu rõ phần nào đang tận dụng được và phần nào cần bổ sung
- Nếu cần giả định thì phải nêu rõ trước khi code

Yêu cầu bắt buộc:
- Chỉ code đúng phase được yêu cầu
- Không code trước phase sau
- Không đổi flow nghiệp vụ hiện tại của hệ thống nếu tôi chưa yêu cầu
- Luôn bám sát cấu trúc thật của project Laravel hiện có
```

---

## 2. PHASE 1 — RÀ SOÁT VÀ CHUẨN HÓA ĐẦU VÀO HỌC VIÊN

```text
Hãy thực hiện PHASE 1 cho project Laravel hiện tại của tôi.

Mục tiêu phase 1:
Rà soát và chuẩn hóa phần đầu vào của học viên theo đúng hệ thống hiện tại.

Phạm vi nghiệp vụ:
- đăng ký tài khoản học viên
- đăng nhập học viên
- chuyển hướng đúng vào khu vực học viên
- hồ sơ học viên cơ bản

Yêu cầu nghiệp vụ:
- Học viên đăng ký bằng form hiện có
- Khi chọn vai trò hoc_vien thì hệ thống tạo tài khoản ngay
- Sau khi đăng ký thành công, học viên đăng nhập được và vào đúng dashboard học viên
- Middleware/phân quyền học viên phải rõ ràng
- Profile học viên phải hoạt động ổn định với dữ liệu thật

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - AuthController
  - HocVienController
  - middleware CheckHocVien
  - route học viên
  - form đăng ký / đăng nhập / profile
- Nếu logic hiện có đã đúng thì chỉ chuẩn hóa, fix lỗi, hoàn thiện
- Không làm phần tham gia khóa học ở phase này
- Không làm buổi học, tài liệu, bài kiểm tra ở phase này

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

## 3. PHASE 2 — HỌC VIÊN XEM “KHÓA HỌC CỦA TÔI”

```text
Hãy thực hiện PHASE 2 cho project Laravel hiện tại của tôi.

Điều kiện:
- Học viên đã có tài khoản hợp lệ
- Học viên đã được ghi danh vào hoc_vien_khoa_hoc

Mục tiêu phase 2:
Cho phép học viên xem danh sách khóa học mình đang tham gia.

Yêu cầu nghiệp vụ:
- Học viên đăng nhập
- Vào mục “Khóa học của tôi”
- Hệ thống hiển thị đúng các khóa học học viên đã được ghi danh
- Mỗi khóa học hiển thị tối thiểu:
  - tên khóa học
  - nhóm ngành
  - ngày khai giảng nếu có
  - trạng thái ghi danh
  - nút vào chi tiết khóa học
- Nếu chưa có khóa học nào thì phải có empty state rõ ràng

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - model HocVienKhoaHoc
  - relation với KhoaHoc
  - route hiện có của hoc-vien.khoa-hoc-cua-toi
  - view index hiện có
- Nếu project đã có phần này thì ưu tiên hoàn thiện bằng dữ liệu thật, eager loading hợp lý, chỉnh UI/logic nếu cần
- Không làm chi tiết buổi học ở phase này
- Không làm tài liệu ở phase này

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

## 4. PHASE 3 — HỌC VIÊN VÀO CHI TIẾT KHÓA HỌC VÀ XEM BUỔI HỌC

```text
Hãy thực hiện PHASE 3 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 2 đã xong
- Hệ thống đã có dữ liệu KhoaHoc, ModuleHoc, LichHoc

Mục tiêu phase 3:
Cho phép học viên vào chi tiết khóa học và xem danh sách buổi học theo module.

Yêu cầu nghiệp vụ:
- Học viên chỉ được vào chi tiết khóa học nếu thực sự thuộc khóa học đó
- Nếu không thuộc khóa học thì chặn truy cập và điều hướng hợp lý
- Trong trang chi tiết khóa học, hiển thị:
  - thông tin khóa học
  - danh sách module
  - danh sách buổi học
  - ngày học
  - giờ học
  - hình thức học
  - phòng học hoặc link online
  - trạng thái buổi học

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - HocVienController@chiTietKhoaHoc
  - model LichHoc
  - relation ModuleHoc và KhoaHoc
  - view show của học viên
- Nếu project đã có nền thì tận dụng, chỉ hoàn thiện logic và giao diện
- Không làm tài liệu chi tiết ở phase này
- Không làm bài kiểm tra ở phase này

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

## 5. PHASE 4 — HỌC VIÊN XEM TÀI LIỆU GIẢNG VIÊN ĐÃ CÔNG KHAI

```text
Hãy thực hiện PHASE 4 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 3 đã xong
- Hệ thống đã có TaiNguyenBuoiHoc

Mục tiêu phase 4:
Cho phép học viên xem tài liệu giảng viên đăng theo từng buổi học, nhưng chỉ thấy các tài liệu đã công khai.

Yêu cầu nghiệp vụ:
- Trong từng buổi học của khóa học, học viên xem được:
  - loại tài nguyên
  - tiêu đề
  - mô tả
  - nút xem chi tiết
  - nút tải về nếu là file nội bộ tải được
  - link ngoài nếu tài nguyên là URL
- Chỉ tài nguyên có trạng thái hiển thị phù hợp mới được học viên nhìn thấy
- Nếu file bị lỗi hoặc không tồn tại thì hiển thị trạng thái rõ ràng

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - model TaiNguyenBuoiHoc
  - relation với LichHoc
  - cột trạng thái hiển thị
  - view chi tiết khóa học của học viên
- Phải tận dụng code hiện có nếu project đã có nền hiển thị tài liệu
- Chỉ query dữ liệu công khai cho học viên
- Không làm upload tài liệu phía giảng viên ở phase này

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

## 6. PHASE 5 — HỌC VIÊN VÀO LỚP ONLINE

```text
Hãy thực hiện PHASE 5 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 3 và 4 đã xong
- Hệ thống đã có dữ liệu link_online trong LichHoc

Mục tiêu phase 5:
Cho phép học viên vào lớp online đúng theo điều kiện buổi học.

Yêu cầu nghiệp vụ:
- Nếu buổi học là online
- Có link_online
- Và trạng thái buổi học cho phép
- Thì học viên thấy nút “Vào phòng học”
- Nếu không đủ điều kiện thì không hiển thị nút hoặc hiển thị trạng thái chờ rõ ràng

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - cột hinh_thuc
  - cột link_online
  - cột trang_thai của LichHoc
  - giao diện show học viên hiện tại
- Tận dụng code đang có nếu đã hiển thị một phần
- Chỉ hoàn thiện đúng điều kiện hiển thị và trải nghiệm
- Không làm logic họp video ngoài hệ thống

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Checklist test phase 5

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 7. PHASE 6 — HỌC VIÊN TỰ XIN VÀO LỚP / KHÓA HỌC

```text
Hãy thực hiện PHASE 6 cho project Laravel hiện tại của tôi.

Lưu ý quan trọng:
- Theo hệ thống hiện tại, tính năng này chưa có đầy đủ ở phía học viên
- Hãy đọc code hiện có và thiết kế phần này sao cho tận dụng được tối đa nền sẵn có

Mục tiêu phase 6:
Bổ sung luồng để học viên tự xem các khóa học có thể tham gia và gửi yêu cầu xin vào lớp.

Yêu cầu nghiệp vụ:
- Học viên xem được danh sách khóa học có thể đăng ký hoặc xin tham gia
- Học viên bấm “Xin vào lớp” hoặc “Gửi yêu cầu tham gia”
- Không cho gửi trùng yêu cầu
- Không cho gửi nếu học viên đã ở trong khóa học rồi
- Yêu cầu phải đi vào luồng admin duyệt
- Nếu project hiện có thể tái sử dụng YeuCauHocVien thì phải ưu tiên tái sử dụng

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - model YeuCauHocVien
  - màn admin duyệt yêu cầu học viên
  - các route đang có
  - structure khóa học có thể public/đang mở/cho phép xin vào
- Nếu cần tạo thêm cột hoặc bảng thì nêu rõ lý do
- Nếu có thể dùng lại YeuCauHocVien thì phải map nghiệp vụ chặt chẽ
- Giao diện Blade phải phù hợp với hệ thống hiện tại

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

## 8. PHASE 7 — DASHBOARD HỌC VIÊN DÙNG DỮ LIỆU THẬT

```text
Hãy thực hiện PHASE 7 cho project Laravel hiện tại của tôi.

Mục tiêu phase 7:
Thay dashboard học viên đang mang tính minh họa bằng dữ liệu thật từ hệ thống.

Yêu cầu nghiệp vụ:
- Dashboard học viên phải hiển thị dữ liệu thật thay vì số ngẫu nhiên
- Tối thiểu nên có:
  - số khóa học đang tham gia
  - số buổi học sắp tới
  - số tài liệu mới hoặc số buổi có tài liệu
  - tiến độ tổng quan ở mức tối thiểu nếu tính được
- Nếu có phần chưa đủ dữ liệu để tính hoàn chỉnh thì hiển thị ở mức khả dụng và ghi chú rõ

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - dashboard học viên hiện tại
  - HocVienController hoặc route dashboard
  - dữ liệu thật nào đã có thể truy xuất
- Chỉ thay phần số liệu minh họa bằng dữ liệu thật
- Không làm bài kiểm tra ở phase này
- Không làm điểm ở phase này

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Checklist test phase 7

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 9. PHASE 8 — HỌC VIÊN XEM HOẠT ĐỘNG VÀ TIẾN ĐỘ HỌC TẬP

```text
Hãy thực hiện PHASE 8 cho project Laravel hiện tại của tôi.

Mục tiêu phase 8:
Cho phép học viên theo dõi hoạt động học tập và tiến độ học tập.

Yêu cầu nghiệp vụ:
- Học viên xem được:
  - buổi học sắp tới
  - tài liệu mới được công khai
  - trạng thái học tập theo khóa
  - số buổi đã học / tổng số buổi
  - tiến độ theo phần trăm nếu tính được
- Nếu chưa đủ dữ liệu để tính tiến độ phức tạp, hãy làm phiên bản tối thiểu nhưng đúng dữ liệu

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - LichHoc
  - DiemDanh
  - TaiNguyenBuoiHoc
  - HocVienKhoaHoc
  - dashboard hoặc page học viên nào phù hợp
- Có thể tách:
  - 1 trang hoạt động học tập
  - 1 trang tiến độ học tập
  hoặc gom thành 1 module nếu phù hợp với project
- Không làm bài kiểm tra ở phase này

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Lệnh artisan cần chạy
6. Checklist test phase 8

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 10. PHASE 9 — HỌC VIÊN LÀM BÀI KIỂM TRA

```text
Hãy thực hiện PHASE 9 cho project Laravel hiện tại của tôi.

Lưu ý:
- Đây là phase có thể phụ thuộc vào nền dữ liệu bài kiểm tra hiện có
- Trước khi code phải đọc kỹ phần BaiKiemTra và các bảng liên quan

Mục tiêu phase 9:
Xây nền hoặc hoàn thiện luồng để học viên xem danh sách bài kiểm tra và làm bài kiểm tra.

Yêu cầu nghiệp vụ:
- Học viên xem được danh sách bài kiểm tra thuộc các khóa học mình tham gia
- Chỉ thấy bài kiểm tra hợp lệ theo phạm vi được phép
- Có thể vào màn làm bài nếu bài kiểm tra đã mở
- Nếu project hiện chưa có đủ bảng kết quả/bài làm thì phải:
  - nêu rõ phần nền nào đang có
  - thiết kế theo hướng mở rộng được
  - chỉ code trong phạm vi khả thi

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - model BaiKiemTra
  - quan hệ với LichHoc, ModuleHoc, KhoaHoc
  - hiện đã có bảng bài làm/kết quả hay chưa
- Nếu thiếu bảng lưu bài làm thì phải đề xuất migration rõ ràng
- Route và controller học viên phải tách biệt với giảng viên

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Lệnh artisan cần chạy
6. Checklist test phase 9

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 11. PHASE 10 — HỌC VIÊN XEM ĐIỂM VÀ KẾT QUẢ HỌC TẬP

```text
Hãy thực hiện PHASE 10 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 9 đã có nền tối thiểu

Mục tiêu phase 10:
Cho phép học viên xem điểm, kết quả học tập và lịch sử bài kiểm tra của mình.

Yêu cầu nghiệp vụ:
- Học viên xem được:
  - danh sách bài kiểm tra đã làm
  - điểm từng bài
  - trạng thái hoàn thành
  - ngày làm bài
  - nhận xét hoặc thông tin bổ sung nếu có
- Học viên chỉ được xem dữ liệu của chính mình
- Nếu hệ thống mới có dữ liệu một phần thì phải hiển thị ở mức read-only phù hợp

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - bảng kết quả/bài làm nếu đã có
  - model nào đang dùng để lưu điểm
  - route sidebar học viên nào đang thiếu
- Hoàn thiện route, controller, view cho:
  - bài kiểm tra của học viên
  - kết quả học tập của học viên

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo
3. File cần sửa
4. Code đầy đủ
5. Lệnh artisan cần chạy
6. Checklist test phase 10

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## 12. PHASE 11 — RÀ SOÁT TỔNG THỂ PHẦN HỌC VIÊN

```text
Hãy thực hiện PHASE 11 cho project Laravel hiện tại của tôi.

Mục tiêu phase 11:
Rà soát và hoàn thiện tổng thể phần học viên sau khi các phase chính đã có.

Yêu cầu:
- Rà soát route học viên
- Rà soát middleware và phân quyền
- Rà soát sidebar học viên
- Kiểm tra các route sidebar đang gọi có tồn tại thật không
- Kiểm tra null safety ở các view
- Kiểm tra eager loading để tránh N+1
- Kiểm tra thông báo success/error
- Kiểm tra consistency giao diện
- Hoàn thiện những lỗi nhỏ còn sót nhưng không mở rộng thêm nghiệp vụ mới

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

## 13. THỨ TỰ NÊN LÀM

```text
Làm theo đúng thứ tự:
1. Phase 1
2. Phase 2
3. Phase 3
4. Phase 4
5. Phase 5
6. Phase 6
7. Phase 7
8. Phase 8
9. Phase 9
10. Phase 10
11. Phase 11
```

---

## 14. CÂU CHỐT BẮT BUỘC Ở CUỐI MỖI PHASE

```text
Chỉ làm đúng phase này. Không tự động làm sang phase khác. Phải ưu tiên tận dụng model, controller, migration, route, view đã có trong project trước khi tạo mới. Nếu phase hiện tại bị phụ thuộc vào phần nền chưa tồn tại trong hệ thống, phải nêu rõ phần nào đang có, phần nào chưa có, và chỉ code trong phạm vi an toàn của phase đó.
```

