# PROMPT CHI TIẾT CHO GEMINI AGENT CODE
## Phần giảng viên cho hệ thống học tập và kiểm tra online
## Chia theo từng phase, làm xong phase nào dừng phase đó

---

# 1. PROMPT TỔNG QUÁT

```text
Bạn là một senior Laravel developer đang hỗ trợ phát triển đồ án thực tập về hệ thống học tập và kiểm tra online cho trung tâm giáo dục Khai Trí.

Công nghệ và nguyên tắc:
- Laravel
- Blade
- MySQL
- MVC rõ ràng
- Code sạch, dễ đọc, dễ mở rộng
- Ưu tiên tái sử dụng cấu trúc project hiện tại
- Ưu tiên bám theo database, model, controller, route và giao diện đang có trong project
- Không viết code demo giả
- Code phải có khả năng chạy thực tế
- Phân quyền rõ ràng giữa admin, giảng viên, học viên

Bối cảnh nghiệp vụ hiện tại:
- Hệ thống đã có phần admin gần hoàn chỉnh
- Admin đã quản lý các phần nền như nhóm ngành, khóa học, module, học viên khóa học, lịch học, phân công giảng viên
- Bây giờ cần phát triển PHẦN GIẢNG VIÊN
- Giảng viên được admin phân công dạy module
- Giảng viên phải chấp nhận module dạy
- Khi giảng viên đã chấp nhận và đủ điều kiện, admin sẽ mở khóa học
- Giảng viên biết được module mình dạy và số buổi phải dạy
- Giảng viên dạy theo từng buổi học
- Mỗi buổi học giảng viên có thể cập nhật link Zoom / Google Meet / link học online khác
- Sau mỗi buổi học, giảng viên đăng lại bài giảng, tài liệu, bài tập cho học viên xem lại đúng tại buổi học đó
- Giảng viên xem danh sách học viên của khóa học hoặc module mình dạy
- Nếu giảng viên muốn thêm / sửa / xóa học viên khỏi khóa học thì phải tạo yêu cầu, và admin xác nhận thì mới được áp dụng
- Giảng viên điểm danh học viên theo từng buổi
- Giảng viên tạo bài kiểm tra sau mỗi module hoặc buổi học
- Giao diện học viên sẽ làm sau, nhưng dữ liệu phía giảng viên phải thiết kế đúng để sau này học viên dùng được

Lưu ý nghiệp vụ:
- Không dùng khái niệm “môn học”, dùng “nhóm ngành”
- Nhóm ngành là cấp cha
- Khóa học thuộc nhóm ngành
- Module thuộc khóa học
- Buổi học hoặc lịch học gắn với module / khóa học tùy cấu trúc project hiện tại
- Bài giảng sau buổi học phải gắn với đúng buổi học
- Danh sách học viên chính thức vẫn do admin kiểm soát cuối cùng

Yêu cầu làm việc:
- Chỉ làm đúng phase tôi yêu cầu
- Không làm lan sang phase khác nếu tôi chưa yêu cầu
- Trước khi code, phải đọc project hiện tại để tận dụng những gì đã có
- Nếu project đã có bảng/model/controller liên quan thì ưu tiên tận dụng và mở rộng
- Nếu chưa có mới tạo migration/model/controller mới
- Khi trả lời mỗi phase, phải ghi rõ:
  1. phân tích ngắn dựa trên project hiện tại
  2. file nào cần tạo
  3. file nào cần sửa
  4. code đầy đủ cho từng file
  5. lệnh artisan cần chạy
  6. checklist test thủ công
- Nếu cần giả định theo project hiện tại thì phải nêu rõ giả định trước khi code
- Giao diện dùng Blade và đồng bộ với style giảng viên / admin đang có trong project
- Route name, middleware, sidebar, menu phải rõ ràng

Mục tiêu cuối:
Xây dựng đầy đủ phần giảng viên theo flow:
1. xem phân công module
2. chấp nhận hoặc từ chối dạy
3. xem số buổi và lịch dạy
4. nhập link học online
5. đăng bài giảng / tài liệu / bài tập sau buổi học
6. xem danh sách học viên
7. gửi yêu cầu thay đổi học viên để admin xác nhận
8. điểm danh theo buổi
9. tạo bài kiểm tra theo module hoặc buổi học
```

---

# 2. PHASE 1 — GIẢNG VIÊN XEM PHÂN CÔNG VÀ CHẤP NHẬN MODULE DẠY

```text
Hãy thực hiện PHASE 1 cho project Laravel hiện tại của tôi.

Mục tiêu phase 1:
Cho phép giảng viên đăng nhập và xem danh sách module được admin phân công, sau đó chấp nhận hoặc từ chối dạy.

Yêu cầu nghiệp vụ:
- Giảng viên chỉ thấy các module được phân công cho chính mình
- Hiển thị:
  - tên khóa học
  - tên module
  - nhóm ngành liên quan nếu truy xuất được
  - số buổi phải dạy
  - trạng thái phân công
  - ghi chú phân công nếu có
- Giảng viên có nút:
  - Chấp nhận dạy
  - Từ chối dạy
- Sau khi chấp nhận:
  - trạng thái phân công đổi sang đã xác nhận
- Sau khi từ chối:
  - trạng thái phân công đổi sang từ chối hoặc trạng thái phù hợp theo project hiện tại
- Giảng viên không được chỉnh phân công của giảng viên khác
- Admin về sau có thể dựa vào trạng thái này để mở khóa học

Yêu cầu kỹ thuật:
- Đọc project hiện tại để kiểm tra:
  - bảng/model phân công giảng viên hiện có
  - trạng thái hiện đang dùng là gì
  - controller giảng viên đã có chưa
- Tạo route giảng viên phù hợp
- Tạo controller hoặc action cho giảng viên
- Tạo view danh sách phân công
- Thêm menu sidebar giảng viên nếu cần
- Phân quyền rõ ràng theo tài khoản giảng viên hiện tại
- Không làm lịch học ở phase này
- Không làm link học online ở phase này
- Không làm bài giảng sau buổi ở phase này

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

# 3. PHASE 2 — GIẢNG VIÊN XEM SỐ BUỔI PHẢI DẠY VÀ LỊCH DẠY

```text
Hãy thực hiện PHASE 2 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 1 đã xong
- Giảng viên đã xem được module và chấp nhận dạy
- Project hiện tại đã có module và có dữ liệu số buổi hoặc lịch học

Mục tiêu phase 2:
Cho phép giảng viên xem số buổi phải dạy trong từng module và danh sách các buổi học/lịch dạy.

Yêu cầu nghiệp vụ:
- Giảng viên vào module mình phụ trách
- Thấy được:
  - tên module
  - khóa học liên quan
  - nhóm ngành liên quan
  - tổng số buổi phải dạy
  - danh sách buổi học hoặc lịch học
- Mỗi buổi học hiển thị:
  - số thứ tự buổi
  - ngày học
  - giờ bắt đầu
  - giờ kết thúc
  - trạng thái buổi học
- Chỉ giảng viên của module đó được xem
- Không được xem module của người khác

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - đang dùng bảng lich_hoc hay bảng buoi_hoc hay chưa
  - số buổi đang nằm ở module_hoc hay bảng khác
- Nếu project đã có lich_hoc thì ưu tiên tận dụng
- Nếu thiếu relation thì bổ sung relation cần thiết
- Tạo route và action cho giảng viên
- Tạo view danh sách module và chi tiết lịch dạy
- Không làm sửa lịch ở phase này
- Không làm link học online ở phase này

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

# 4. PHASE 3 — GIẢNG VIÊN CẬP NHẬT LINK HỌC ONLINE CHO TỪNG BUỔI

```text
Hãy thực hiện PHASE 3 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 2 đã xong
- Giảng viên đã xem được danh sách buổi học hoặc lịch học

Mục tiêu phase 3:
Cho phép giảng viên cập nhật link học online cho từng buổi dạy.

Yêu cầu nghiệp vụ:
- Giảng viên chỉ được cập nhật link cho buổi học của module mình phụ trách
- Mỗi buổi học có thể nhập:
  - nền tảng học: Zoom / Google Meet / Khác
  - link học online
  - meeting_id
  - mật khẩu
  - ghi chú cho buổi học
- Hiển thị trạng thái:
  - chưa cập nhật link
  - đã cập nhật link
- Có nút copy link trong giao diện giảng viên
- Không cần tích hợp API Zoom/Google Meet
- Chỉ lưu thủ công

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định:
  - bảng lich_hoc hiện có đã có các cột link chưa
  - nếu chưa có thì tạo migration bổ sung cột
- Tạo form cập nhật link
- Có validate URL ở mức cơ bản
- Có JavaScript copy link đơn giản
- Không làm giao diện học viên ở phase này
- Không làm đăng bài giảng ở phase này

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

# 5. PHASE 4 — GIẢNG VIÊN ĐĂNG BÀI GIẢNG, TÀI LIỆU, BÀI TẬP SAU BUỔI HỌC

```text
Hãy thực hiện PHASE 4 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 3 đã xong
- Hệ thống đã có buổi học/lịch học cụ thể

Mục tiêu phase 4:
Cho phép giảng viên đăng lại nội dung bài giảng sau mỗi buổi học để học viên xem lại sau này.

Yêu cầu nghiệp vụ:
- Nội dung sau buổi học phải gắn với đúng buổi học
- Giảng viên có thể thêm:
  - tiêu đề bài giảng
  - tóm tắt nội dung đã dạy
  - bài tập về nhà
  - link video
  - link tài liệu ngoài
  - file tài liệu đính kèm nếu project hiện tại hỗ trợ upload
  - ghi chú
- Một buổi học có thể có 1 hoặc nhiều tài nguyên sau buổi học
- Giảng viên chỉ được quản lý nội dung của buổi học thuộc module mình dạy
- Có thể thêm / sửa / xóa nội dung sau buổi học

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xem đã có bảng upload hay tài liệu nào tái sử dụng được chưa
- Nếu chưa có, tạo bảng mới cho nội dung sau buổi học
- Tạo model, relation, controller/action và view phù hợp
- Giao diện Blade phải rõ ràng
- Nếu upload file chưa có sẵn trong project, có thể làm bản text + link trước nhưng phải nêu rõ

Không làm ở phase này:
- giao diện học viên
- điểm danh
- bài kiểm tra

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

# 6. PHASE 5 — GIẢNG VIÊN XEM DANH SÁCH HỌC VIÊN TRONG KHÓA HỌC/MODULE

```text
Hãy thực hiện PHASE 5 cho project Laravel hiện tại của tôi.

Điều kiện:
- Project hiện tại đã có bảng học viên khóa học hoặc dữ liệu tương tự
- Giảng viên đã có module được phân công

Mục tiêu phase 5:
Cho phép giảng viên xem danh sách học viên của khóa học hoặc module mình đang dạy.

Yêu cầu nghiệp vụ:
- Giảng viên chỉ xem được học viên thuộc khóa học/module mà mình phụ trách
- Hiển thị:
  - mã học viên nếu có
  - họ tên
  - email
  - số điện thoại
  - trạng thái học trong khóa học nếu có
  - ngày tham gia nếu có
- Có thống kê tổng số học viên
- Không cho giảng viên sửa trực tiếp danh sách chính thức ở phase này

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xác định relation giữa:
  - giảng viên
  - module
  - khóa học
  - học viên_khóa_học
- Tạo action và view cho giảng viên
- Có eager loading để tránh query dư thừa
- Giao diện Blade gọn, rõ ràng

Không làm ở phase này:
- thêm học viên
- xóa học viên
- sửa học viên
- điểm danh

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

# 7. PHASE 6 — GIẢNG VIÊN GỬI YÊU CẦU THÊM / SỬA / XÓA HỌC VIÊN, ADMIN XÁC NHẬN

```text
Hãy thực hiện PHASE 6 cho project Laravel hiện tại của tôi.

Điều kiện:
- Phase 5 đã xong
- Danh sách học viên chính thức vẫn do admin kiểm soát

Mục tiêu phase 6:
Cho phép giảng viên tạo yêu cầu thay đổi danh sách học viên, nhưng admin là người xác nhận cuối cùng.

Yêu cầu nghiệp vụ:
- Giảng viên có thể tạo 3 loại yêu cầu:
  - yêu cầu thêm học viên vào khóa học
  - yêu cầu xóa học viên khỏi khóa học
  - yêu cầu cập nhật trạng thái/ghi chú học viên trong khóa học
- Khi giảng viên gửi yêu cầu:
  - yêu cầu được lưu ở trạng thái chờ duyệt
  - chưa làm thay đổi dữ liệu chính thức ngay
- Admin có màn hình xem yêu cầu:
  - chấp nhận
  - từ chối
- Chỉ khi admin chấp nhận thì dữ liệu chính thức mới thay đổi
- Giảng viên chỉ được tạo yêu cầu cho khóa học/module mình phụ trách

Yêu cầu kỹ thuật:
- Thiết kế bảng yêu cầu phù hợp nếu project chưa có
- Tạo model, relation, controller/action cho giảng viên và admin nếu cần
- Tạo form tạo yêu cầu phía giảng viên
- Tạo màn hình duyệt yêu cầu phía admin nếu chưa có
- Phân quyền rõ ràng
- Giao diện đơn giản nhưng đủ dùng

Không làm ở phase này:
- giao diện học viên
- lịch sử chi tiết audit log sâu
- thông báo realtime nếu chưa có

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

# 8. PHASE 7 — GIẢNG VIÊN ĐIỂM DANH HỌC VIÊN THEO TỪNG BUỔI

```text
Hãy thực hiện PHASE 7 cho project Laravel hiện tại của tôi.

Điều kiện:
- Project đã có buổi học/lịch học
- Giảng viên đã xem được danh sách học viên của khóa học/module

Mục tiêu phase 7:
Cho phép giảng viên điểm danh học viên theo từng buổi học.

Yêu cầu nghiệp vụ:
- Giảng viên vào một buổi học
- Hệ thống hiển thị danh sách học viên của khóa học/module đó
- Giảng viên đánh dấu trạng thái từng học viên:
  - có mặt
  - vắng mặt
  - vào trễ
- Có thể nhập ghi chú nếu cần
- Mỗi học viên chỉ có 1 bản ghi điểm danh cho 1 buổi học
- Giảng viên chỉ được điểm danh buổi học của module mình phụ trách

Yêu cầu kỹ thuật:
- Đọc project hiện tại để kiểm tra đã có bảng điểm danh chưa
- Nếu chưa có, tạo bảng điểm danh mới
- Ràng buộc unique theo buổi_học/lịch_học và học_vien
- Có form submit một lần cho cả danh sách học viên
- Giao diện phải dễ thao tác
- Chưa cần tự động lấy dữ liệu từ Zoom/Meet

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

# 9. PHASE 8 — GIẢNG VIÊN TẠO BÀI KIỂM TRA THEO MODULE HOẶC BUỔI HỌC

```text
Hãy thực hiện PHASE 8 cho project Laravel hiện tại của tôi.

Điều kiện:
- Giảng viên đã có module được phân công
- Hệ thống đã có luồng dạy học theo buổi

Mục tiêu phase 8:
Cho phép giảng viên tạo bài kiểm tra sau mỗi module hoặc sau mỗi buổi học.

Yêu cầu nghiệp vụ:
- Giảng viên có thể tạo bài kiểm tra theo:
  - module
  - buổi học
- Mỗi bài kiểm tra có:
  - tên bài kiểm tra
  - mô tả
  - thời gian làm bài
  - ngày mở
  - ngày đóng
  - trạng thái
  - phạm vi áp dụng: theo module hoặc theo buổi
- Giảng viên chỉ được tạo bài kiểm tra cho module mình phụ trách
- Chưa cần làm giao diện học viên làm bài ở phase này
- Chưa cần làm chấm điểm chi tiết nếu chưa có nền

Yêu cầu kỹ thuật:
- Đọc project hiện tại để xem đã có bảng kiểm tra/câu hỏi nào chưa
- Nếu chưa có, thiết kế cấu trúc tối thiểu để tạo bài kiểm tra
- Tạo controller/action/view cho giảng viên
- Tạo relation cần thiết với module/buổi học
- Giao diện Blade đơn giản, dễ mở rộng về sau

Không làm ở phase này:
- giao diện học viên làm bài
- chấm điểm tự động nâng cao
- ngân hàng câu hỏi phức tạp nếu project chưa có nền

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

# 10. PHASE 9 — HOÀN THIỆN PHẦN GIẢNG VIÊN: SIDEBAR, ROUTE, MIDDLEWARE, GIAO DIỆN

```text
Hãy thực hiện PHASE 9 cho project Laravel hiện tại của tôi.

Mục tiêu phase 9:
Hoàn thiện toàn bộ trải nghiệm của phần giảng viên sau khi đã làm các chức năng chính.

Yêu cầu:
- Rà soát toàn bộ route giảng viên
- Rà soát middleware và phân quyền
- Hoàn thiện sidebar/menu giảng viên
- Bổ sung liên kết hợp lý giữa:
  - phân công dạy
  - module đang dạy
  - lịch dạy
  - link học online
  - nội dung sau buổi học
  - danh sách học viên
  - yêu cầu thay đổi học viên
  - điểm danh
  - bài kiểm tra
- Đồng bộ giao diện Blade với project hiện có
- Kiểm tra lỗi route name, lỗi null, lỗi query dư, eager loading
- Thêm thông báo thành công/thất bại rõ ràng
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

# 11. THỨ TỰ NÊN LÀM

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

# 12. CÂU CHỐT NÊN THÊM CUỐI MỖI PHASE

```text
Chỉ làm đúng phase này. Không tự động làm sang phase khác. Nếu project hiện tại đã có bảng/model/controller liên quan thì phải ưu tiên tận dụng trước khi tạo mới. Nếu cần giả định thì phải nêu rõ giả định trước khi code.
```
