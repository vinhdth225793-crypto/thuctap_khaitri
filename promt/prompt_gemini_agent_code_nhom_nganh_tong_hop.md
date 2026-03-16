# PROMPT TỔNG HỢP CHO GEMINI AGENT CODE
## Phiên bản nghiệp vụ: đổi toàn bộ “môn học” thành “nhóm ngành”

---

## PROMPT TỔNG QUÁT CHO GEMINI AGENT CODE

```text
Bạn là một senior Laravel developer đang hỗ trợ phát triển đồ án thực tập về hệ thống học tập và kiểm tra online cho trung tâm giáo dục Khai Trí.

Công nghệ:
- Laravel
- Blade
- MySQL
- MVC rõ ràng
- Giao diện admin, giảng viên, học viên
- Code sạch, dễ đọc, dễ mở rộng
- Đặt tên biến, hàm, migration, model, controller rõ ràng, nhất quán
- Ưu tiên tái sử dụng cấu trúc project hiện tại
- Ưu tiên code chạy thực tế, không viết demo giả

Bối cảnh nghiệp vụ:
- Hệ thống học online 100%, không có phòng học trực tiếp
- Admin quản lý nhóm ngành, khóa học, module, lớp học online, giảng viên, học viên
- Giảng viên được phân công dạy module
- Mỗi module có số buổi học cụ thể
- Mỗi buổi học có link học online như Zoom, Google Meet
- Học viên vào web để thấy link và tham gia học
- Sau mỗi buổi học, giảng viên đăng bài giảng, tài liệu, bài tập để học viên xem lại
- Có điểm danh theo từng buổi học
- Kiểm tra online và chấm điểm sẽ phát triển sau

Lưu ý nghiệp vụ rất quan trọng:
- Trong toàn bộ đồ án này, KHÔNG dùng khái niệm “môn học” nữa
- Thay toàn bộ bằng “nhóm ngành”
- Từ nay logic hệ thống phải hiểu:
  - Nhóm ngành là cấp cha
  - Khóa học thuộc nhóm ngành
  - Module thuộc khóa học
  - Buổi học thuộc module
- Nếu project hiện tại vẫn đang có bảng/model/controller/view dùng tên MonHoc hoặc mon_hoc:
  - phải phân tích trước
  - ưu tiên refactor an toàn
  - nếu chưa đổi DB ngay thì vẫn phải đổi toàn bộ label hiển thị sang “nhóm ngành”
  - nếu đổi luôn DB thì phải có phương án migration rename hoặc tương thích dữ liệu cũ

Yêu cầu làm việc:
- Chỉ làm đúng phase tôi yêu cầu
- Không làm lan sang phase khác nếu tôi chưa yêu cầu
- Trước khi code, hãy phân tích file hiện tại trong project để tránh trùng logic
- Khi trả lời, phải ghi rõ:
  1. phân tích ngắn
  2. file nào cần tạo
  3. file nào cần sửa
  4. code đầy đủ cho từng file
  5. lệnh artisan cần chạy
  6. checklist test thủ công
- Nếu một chức năng phụ thuộc cấu trúc dữ liệu cũ thì phải nói rõ cách mapping với project hiện tại
- Nếu route, tên bảng, model trong project hiện tại khác với đề xuất, hãy ưu tiên bám project hiện tại
- Giao diện dùng Blade, đồng bộ với style sẵn có trong project
- Không tự ý đổi kiến trúc lớn nếu chưa cần
- Khi cần giả định, phải nêu rõ giả định trước khi code

Mục tiêu cuối:
Xây dựng chức năng quản lý buổi học online theo module, link học trực tuyến, tài liệu/bài tập sau buổi học, điểm danh, và đồng thời đổi toàn bộ nghiệp vụ từ môn học sang nhóm ngành.
```

---

## PHASE 0 — ĐỔI TOÀN BỘ “MÔN HỌC” THÀNH “NHÓM NGÀNH”

```text
Hãy thực hiện PHASE 0 cho project Laravel hiện tại của tôi.

Mục tiêu phase 0:
Đổi toàn bộ nghiệp vụ, giao diện, tên hiển thị, và nếu phù hợp thì cả code từ “môn học” thành “nhóm ngành”.

Yêu cầu nghiệp vụ:
- Trong toàn bộ đồ án, không dùng khái niệm “môn học” nữa
- Thay bằng “nhóm ngành”
- Ý nghĩa mới:
  - một nhóm ngành có nhiều khóa học
  - một khóa học có nhiều module
  - một module có nhiều buổi học
- Tất cả label giao diện admin, giảng viên, học viên phải đổi thành “nhóm ngành”
- Tất cả tiêu đề trang, breadcrumb, sidebar, nút bấm, form, table liên quan đến môn học phải đổi thành nhóm ngành

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - model MonHoc
  - bảng mon_hoc
  - controller liên quan
  - route liên quan
  - view liên quan
- Đề xuất 2 phương án:
  1. Refactor giao diện trước, giữ tên bảng mon_hoc tạm thời để an toàn
  2. Refactor đầy đủ từ DB/model/controller/view sang nhom_nganh
- Chọn phương án an toàn nhất cho project hiện tại và triển khai
- Nếu chưa đổi DB ngay, phải đổi toàn bộ text hiển thị sang “nhóm ngành”
- Nếu đổi luôn DB thì phải có migration rename bảng/cột hoặc phương án tương thích dữ liệu cũ

Tôi muốn bạn trả ra:
1. Phân tích ngắn 2 phương án
2. Chọn 1 phương án phù hợp nhất
3. Danh sách file cần tạo/sửa
4. Code đầy đủ
5. Lệnh artisan hoặc SQL cần chạy
6. Checklist test phase 0

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## PHASE 1 — THIẾT KẾ DỮ LIỆU BUỔI HỌC CHO MODULE

```text
Hãy thực hiện PHASE 1 cho project Laravel hiện tại của tôi.

Điều kiện:
- Toàn bộ nghiệp vụ cấp cha đã dùng khái niệm “nhóm ngành” thay cho “môn học”

Mục tiêu phase 1:
Xây dựng cấu trúc dữ liệu cho "buổi học" thuộc "module học".

Yêu cầu nghiệp vụ:
- Mỗi module có nhiều buổi học
- Mỗi buổi học có:
  - mã buổi học
  - tên buổi học
  - module_id
  - khóa học hoặc lớp học liên quan nếu cần bám theo cấu trúc project hiện tại
  - số thứ tự buổi
  - ngày học
  - giờ bắt đầu
  - giờ kết thúc
  - nền tảng học online: zoom / google_meet / khac
  - link phòng học
  - meeting_id
  - mật khẩu
  - ghi chú
  - trạng thái buổi học: chua_dien_ra / dang_dien_ra / da_ket_thuc / hoan
  - created_at, updated_at

Yêu cầu kỹ thuật:
- Đọc cấu trúc bảng module, khóa học, nhóm ngành hiện tại trong project rồi đề xuất quan hệ hợp lý nhất
- Tạo migration
- Tạo model BuoiHoc hoặc tên phù hợp với project
- Khai báo relation giữa ModuleHoc và BuoiHoc
- Tạo seeder mẫu nếu cần
- Không làm giao diện ở phase này ngoài mức tối thiểu nếu bắt buộc
- Không làm tài liệu sau buổi học
- Không làm điểm danh
- Không làm bài kiểm tra

Tôi muốn bạn trả ra:
1. Phân tích ngắn trước khi code
2. Danh sách file cần tạo/sửa
3. Code đầy đủ cho từng file
4. Lệnh artisan cần chạy
5. Checklist test phase 1

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## PHASE 2 — ADMIN TẠO VÀ QUẢN LÝ BUỔI HỌC CHO TỪNG MODULE

```text
Hãy thực hiện PHASE 2 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 1 đã xong
- Giờ cần giao diện admin quản lý buổi học của từng module

Mục tiêu phase 2:
Admin có thể tạo, sửa, xóa, xem danh sách buổi học theo từng module.

Yêu cầu nghiệp vụ:
- Từ trang module, admin bấm vào xem danh sách buổi học
- Hiển thị danh sách buổi học của module:
  - số thứ tự buổi
  - tên buổi học
  - ngày học
  - giờ bắt đầu - giờ kết thúc
  - nền tảng học online
  - trạng thái
- Admin có thể:
  - thêm buổi học
  - sửa buổi học
  - xóa buổi học
- Có validate:
  - số thứ tự buổi không trùng trong cùng 1 module
  - giờ kết thúc phải lớn hơn giờ bắt đầu
- Giao diện dùng Blade, bám style admin hiện có trong project

Yêu cầu kỹ thuật:
- Tạo controller admin riêng nếu cần
- Tạo route admin
- Tạo view index, create, edit
- Thêm nút “Buổi học” ở trang module hoặc khu vực phù hợp
- Không làm quyền giảng viên ở phase này
- Không làm upload tài liệu ở phase này
- Không làm điểm danh ở phase này

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. Các file cần tạo/sửa
3. Code đầy đủ
4. Checklist test phase 2

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## PHASE 3 — GIẢNG VIÊN XEM MODULE ĐƯỢC PHÂN CÔNG VÀ DANH SÁCH BUỔI HỌC

```text
Hãy thực hiện PHASE 3 cho project Laravel hiện tại của tôi.

Điều kiện:
- Đã có dữ liệu buổi học ở phase trước
- Project đã có phân công giảng viên cho module

Mục tiêu phase 3:
Giảng viên đăng nhập và xem được:
- module mình được phân công
- số buổi cần dạy
- danh sách buổi học của từng module

Yêu cầu nghiệp vụ:
- Giảng viên chỉ thấy module của mình
- Mỗi module hiển thị:
  - tên module
  - khóa học hoặc lớp học liên quan
  - nhóm ngành liên quan
  - tổng số buổi
  - trạng thái phân công
- Khi vào chi tiết module, giảng viên thấy danh sách buổi học:
  - buổi số mấy
  - ngày giờ học
  - trạng thái
  - link lớp học nếu đã có
- Không được thấy module của giảng viên khác

Yêu cầu kỹ thuật:
- Tận dụng bảng phân công giảng viên hiện có trong project
- Tạo controller, route, view cho giảng viên
- Thêm menu sidebar giảng viên nếu cần
- Phân quyền rõ ràng

Không làm:
- sửa điểm danh
- upload tài liệu
- tạo bài kiểm tra

Tôi muốn bạn trả ra:
1. Phân tích relation với bảng phân công hiện tại
2. Các file cần tạo/sửa
3. Code đầy đủ
4. Checklist test phase 3

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## PHASE 4 — GIẢNG VIÊN NHẬP LINK ZOOM/GOOGLE MEET CHO TỪNG BUỔI HỌC

```text
Hãy thực hiện PHASE 4 cho project Laravel hiện tại của tôi.

Mục tiêu phase 4:
Cho phép giảng viên nhập và cập nhật link học online cho từng buổi học.

Yêu cầu nghiệp vụ:
- Giảng viên vào danh sách buổi học của module mình dạy
- Mỗi buổi học có thể nhập:
  - nền tảng: Zoom / Google Meet / Khác
  - link phòng học
  - meeting_id
  - mật khẩu
  - ghi chú
- Chỉ giảng viên được phân công module đó mới được sửa
- Học viên chỉ được xem, không được sửa
- Trong giao diện giảng viên cần có:
  - nút cập nhật link học
  - nút copy link
- Nếu chưa có link thì hiển thị trạng thái “chưa cập nhật”

Yêu cầu kỹ thuật:
- Có thể dùng form modal hoặc trang edit riêng
- Validate link hợp lệ ở mức cơ bản
- Blade có nút copy bằng JavaScript đơn giản
- Không cần tích hợp API Zoom hay Google Meet, chỉ lưu link thủ công

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần sửa/tạo
3. Code đầy đủ
4. Checklist test phase 4

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## PHASE 5 — HỌC VIÊN XEM LINK BUỔI HỌC VÀ VÀO HỌC ONLINE

```text
Hãy thực hiện PHASE 5 cho project Laravel hiện tại của tôi.

Mục tiêu phase 5:
Học viên đăng nhập và xem được các buổi học của lớp hoặc module mình tham gia, đồng thời thấy link để vào học online.

Yêu cầu nghiệp vụ:
- Học viên chỉ thấy lớp học hoặc module mà mình đã được ghi danh
- Hiển thị danh sách buổi học:
  - nhóm ngành
  - khóa học
  - module
  - buổi số mấy
  - ngày giờ học
  - nền tảng học
  - link tham gia nếu có
  - ghi chú
- Có nút:
  - vào học
  - copy link
- Nếu chưa đến ngày học vẫn có thể hiển thị hoặc tùy điều kiện project hiện tại
- Nếu chưa có link thì báo “giảng viên chưa cập nhật link”

Yêu cầu kỹ thuật:
- Phân tích cách xác định học viên thuộc lớp hoặc module nào theo cấu trúc project hiện tại
- Tạo controller, route, view cho học viên
- Thêm menu phù hợp trong sidebar học viên

Không làm:
- điểm danh tự động
- kiểm tra online

Tôi muốn bạn trả ra:
1. Phân tích relation học viên với lớp/module
2. Các file cần tạo/sửa
3. Code đầy đủ
4. Checklist test phase 5

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## PHASE 6 — GIẢNG VIÊN ĐĂNG BÀI GIẢNG, TÀI LIỆU, BÀI TẬP SAU TỪNG BUỔI HỌC

```text
Hãy thực hiện PHASE 6 cho project Laravel hiện tại của tôi.

Mục tiêu phase 6:
Sau mỗi buổi học, giảng viên có thể đăng nội dung bài giảng và bài tập cho buổi học đó để học viên xem lại.

Yêu cầu nghiệp vụ:
- Mỗi buổi học có khu vực “Nội dung sau buổi học”
- Giảng viên có thể thêm:
  - tiêu đề nội dung
  - mô tả hoặc tóm tắt buổi học
  - bài tập về nhà
  - file tài liệu đính kèm nếu project đang hỗ trợ upload
  - link video hoặc link tài liệu ngoài nếu có
- Một buổi học có thể có 1 hoặc nhiều tài nguyên sau buổi học
- Học viên chỉ được xem
- Giảng viên được thêm, sửa, xóa nội dung của buổi học mình phụ trách

Yêu cầu kỹ thuật:
- Nếu project hiện tại đã có upload file thì tái sử dụng
- Nếu chưa có, có thể tạm làm bản text + link trước, rồi ghi chú hướng mở rộng upload file
- Tạo bảng dữ liệu riêng cho tài liệu, bài giảng sau buổi học nếu cần
- Giao diện Blade rõ ràng, dễ nhìn

Tôi muốn bạn trả ra:
1. Phân tích thiết kế bảng phù hợp
2. File cần tạo/sửa
3. Code đầy đủ
4. Checklist test phase 6

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## PHASE 7 — HỌC VIÊN XEM LẠI BÀI GIẢNG VÀ BÀI TẬP THEO TỪNG BUỔI HỌC

```text
Hãy thực hiện PHASE 7 cho project Laravel hiện tại của tôi.

Mục tiêu phase 7:
Học viên vào hệ thống và xem lại nội dung bài giảng, bài tập sau từng buổi học.

Yêu cầu nghiệp vụ:
- Học viên vào module hoặc danh sách buổi học
- Chọn từng buổi học để xem:
  - link học online
  - tóm tắt bài giảng
  - tài liệu
  - bài tập về nhà
  - link video nếu có
- Chỉ học viên thuộc lớp hoặc module mới được xem
- Giao diện rõ ràng, dễ theo dõi theo từng buổi học

Yêu cầu kỹ thuật:
- Tạo trang chi tiết buổi học cho học viên
- Tận dụng dữ liệu từ phase 6
- Có thể gom chung vào trang chi tiết buổi học nếu hợp lý

Tôi muốn bạn trả ra:
1. Phân tích ngắn
2. File cần tạo/sửa
3. Code đầy đủ
4. Checklist test phase 7

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## PHASE 8 — ĐIỂM DANH THEO TỪNG BUỔI HỌC

```text
Hãy thực hiện PHASE 8 cho project Laravel hiện tại của tôi.

Mục tiêu phase 8:
Cho phép giảng viên điểm danh học viên theo từng buổi học.

Yêu cầu nghiệp vụ:
- Giảng viên vào một buổi học
- Hệ thống hiển thị danh sách học viên thuộc lớp hoặc module đó
- Giảng viên đánh dấu:
  - có mặt
  - vắng
  - vào trễ
- Có ghi chú nếu cần
- Mỗi học viên chỉ có 1 trạng thái điểm danh cho 1 buổi học
- Học viên có thể xem lại lịch sử điểm danh của mình
- Admin có thể xem thống kê điểm danh nếu thuận tiện

Yêu cầu kỹ thuật:
- Tạo bảng điểm danh riêng
- Ràng buộc unique theo buoi_hoc_id + hoc_vien_id
- Form điểm danh nên đơn giản, dễ thao tác
- Có thể dùng submit 1 lần cho toàn danh sách học viên

Không làm:
- tự động điểm danh qua Zoom API
- nhận diện thời gian tham gia phòng học

Tôi muốn bạn trả ra:
1. Phân tích thiết kế dữ liệu
2. File cần tạo/sửa
3. Code đầy đủ
4. Checklist test phase 8

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## PHASE 9 — HOÀN THIỆN GIAO DIỆN, PHÂN QUYỀN VÀ LIÊN KẾT MENU

```text
Hãy thực hiện PHASE 9 cho project Laravel hiện tại của tôi.

Mục tiêu phase 9:
Hoàn thiện các liên kết giao diện, sidebar, phân quyền và trải nghiệm sử dụng cho toàn bộ chức năng buổi học online.

Yêu cầu:
- Rà soát route admin, giảng viên, học viên
- Rà soát middleware và phân quyền
- Bổ sung menu sidebar:
  - admin: quản lý nhóm ngành, khóa học, module, buổi học
  - giảng viên: module giảng dạy, buổi học, link học, nội dung sau buổi học, điểm danh
  - học viên: lịch học, vào học online, xem bài giảng, lịch sử điểm danh
- Tối ưu giao diện Blade cho đồng bộ style project
- Thêm nút copy link ở nơi cần thiết
- Kiểm tra lỗi route name, lỗi biến null, eager loading cần thiết
- Không thêm chức năng mới ngoài việc hoàn thiện

Tôi muốn bạn trả ra:
1. Danh sách phần cần rà soát
2. File cần sửa
3. Code đầy đủ
4. Checklist test tổng thể

Chỉ làm đúng phase này. Không tự động làm sang phase khác.
```

---

## THỨ TỰ NÊN LÀM

```text
Làm theo thứ tự:
1. Phase 0
2. Phase 1
3. Phase 2
4. Phase 3
5. Phase 4
6. Phase 6
7. Phase 7
8. Phase 8
9. Phase 5
10. Phase 9
```

---

## CÂU CHỐT THÊM CUỐI MỖI PHASE

```text
Chỉ làm đúng phase này. Không tự động làm sang phase khác. Nếu cần giả định theo project hiện tại thì nêu rõ giả định trước khi code.
```
