# Prompt chi tiết nâng cấp quản lý kết quả học tập cho phần học viên

Bạn đang làm việc trên project Laravel của tôi: `thuctap_khaitri`.

Mục tiêu của bạn là **phân tích, thiết kế và cải tiến hoàn chỉnh phần quản lý kết quả học tập của học viên** trong hệ thống học tập và kiểm tra online, bám theo code hiện có trong repo, **không viết lại từ đầu nếu không cần thiết**.

---

## 1. Bối cảnh hệ thống

Hệ thống của tôi là web học tập và kiểm tra online, có 3 actor chính:

- **Admin**
- **Giảng viên**
- **Học viên**

Hiện tại hệ thống đã có nhiều chức năng nền như:

- quản lý tài khoản
- phân quyền
- nhóm ngành / khóa học / module / lịch học
- phân công giảng viên
- học viên tham gia khóa học
- điểm danh
- tài nguyên / bài giảng
- bài kiểm tra online
- bài làm bài kiểm tra
- giám sát thi
- live room
- kết quả học tập (đã có nền hoặc đã có bảng / route / model liên quan)

Tuy nhiên, phần **quản lý kết quả học tập** cho học viên hiện chưa thật sự hoàn chỉnh ở góc độ nghiệp vụ, trải nghiệm, tổng hợp dữ liệu và hiển thị báo cáo.

Tôi muốn nâng cấp phần này để hệ thống có thể quản lý và hiển thị **kết quả học tập của học viên một cách đầy đủ, dễ hiểu, có tổng hợp, có chi tiết, có tiến trình và phù hợp để demo đồ án**.

---

## 2. Yêu cầu bắt buộc trước khi code

Trước khi sửa code, hãy đọc và phân tích kỹ **toàn bộ phần liên quan đến học viên và kết quả học tập** trong project.

Tối thiểu phải đọc:

### 2.1 Route
- `routes/web.php`

### 2.2 Model
- `app/Models/KhoaHoc.php`
- `app/Models/ModuleHoc.php`
- `app/Models/LichHoc.php`
- `app/Models/BaiKiemTra.php`
- `app/Models/BaiLamBaiKiemTra.php`
- `app/Models/ChiTietBaiLamBaiKiemTra.php`
- `app/Models/KetQuaHocTap.php`
- các model liên quan học viên, ghi danh, điểm danh, live room, bài giảng nếu có

### 2.3 Controller
- controller phần học viên
- controller bài kiểm tra online
- controller kết quả học tập
- controller dashboard / tiến độ học tập học viên
- controller admin/giảng viên có liên quan đến chấm điểm hoặc tổng hợp kết quả

### 2.4 View
- các view phần học viên:
  - dashboard
  - hoạt động / tiến độ
  - khóa học của tôi
  - buổi học
  - bài giảng
  - bài kiểm tra
  - kết quả / điểm / tiến độ nếu đã có
- các view admin / giảng viên liên quan đến xem kết quả học tập nếu đã có

### 2.5 Migration
Kiểm tra kỹ các migration có liên quan đến:
- bài kiểm tra
- bài làm
- chi tiết bài làm
- kết quả học tập
- điểm danh
- tiến độ học tập
- học viên trong khóa học
- module
- lịch học

### 2.6 Yêu cầu phân tích
Bạn phải xác định rõ:
1. Hệ thống hiện đã có những bảng nào cho kết quả học tập
2. Dữ liệu điểm hiện được sinh ra từ đâu
3. Kết quả học tập hiện đang tổng hợp tới mức nào
4. Thiếu những gì để tạo ra một hệ quản lý kết quả học tập đầy đủ

---

## 3. Mục tiêu nâng cấp

Tôi muốn phần **quản lý kết quả học tập** đạt được những mục tiêu sau:

### 3.1 Đối với học viên
Học viên phải xem được:

- danh sách khóa học đang học
- danh sách module trong khóa học
- tiến độ học tập theo module / theo khóa
- kết quả bài kiểm tra
- điểm từng bài kiểm tra
- số lần làm bài nếu có
- trạng thái đạt / chưa đạt
- tổng kết điểm toàn khóa
- chuyên cần / điểm danh nếu có áp dụng
- kết quả cuối cùng của khóa học
- nhận xét hoặc ghi chú từ giảng viên nếu có

### 3.2 Đối với giảng viên
Giảng viên phải có thể:

- xem kết quả học tập của học viên trong khóa mình dạy
- xem theo từng module / từng bài kiểm tra
- cập nhật điểm thủ công nếu có phần tự luận / đánh giá thủ công
- cập nhật nhận xét học viên
- xem tiến độ học tập tổng quan
- xác nhận / chốt kết quả học tập nếu nghiệp vụ yêu cầu

### 3.3 Đối với admin
Admin phải có thể:

- xem tổng quan kết quả học tập toàn hệ thống
- xem kết quả học tập theo khóa học / theo lớp / theo module
- xem học viên nào đạt / chưa đạt
- xem thống kê tỷ lệ hoàn thành
- cấu hình quy tắc tính kết quả học tập nếu cần
- can thiệp / điều chỉnh / rà soát dữ liệu khi cần

---

## 4. Kết quả học tập cần được hiểu theo nghiệp vụ nào

Bạn phải chuẩn hóa nghiệp vụ kết quả học tập theo hướng sau:

### 4.1 Kết quả học tập không chỉ là điểm bài kiểm tra
Kết quả học tập phải là **bức tranh tổng hợp** của học viên trong một khóa học hoặc module, gồm ít nhất:

- điểm bài kiểm tra
- tình trạng hoàn thành bài kiểm tra
- tiến độ học bài
- điểm danh / chuyên cần (nếu áp dụng)
- đánh giá cuối module / cuối khóa
- nhận xét của giảng viên (nếu có)

### 4.2 Kết quả học tập phải có 2 cấp
#### Cấp 1: Kết quả chi tiết
- theo bài kiểm tra
- theo buổi học
- theo module

#### Cấp 2: Kết quả tổng hợp
- tổng kết module
- tổng kết khóa học

### 4.3 Cần hỗ trợ nhiều tình huống
Ví dụ:
- khóa học chỉ có 1 bài kiểm tra cuối khóa
- khóa học có bài kiểm tra theo từng module
- khóa học có cả trắc nghiệm và tự luận
- có điểm chuyên cần
- có nhận xét thủ công từ giảng viên

---

## 5. Yêu cầu đầu ra sau khi hoàn thiện

Sau khi nâng cấp, hệ thống phải có một flow đầy đủ như sau:

### 5.1 Học viên
- vào khóa học của tôi
- xem từng module
- xem điểm từng bài kiểm tra
- xem tiến độ hoàn thành
- xem tổng kết kết quả học tập
- biết mình đạt hay chưa đạt
- biết mình thiếu gì để hoàn thành khóa

### 5.2 Giảng viên
- vào khóa học mình dạy
- xem danh sách học viên
- xem kết quả học tập của từng học viên
- cập nhật điểm thủ công / nhận xét nếu có
- chốt kết quả

### 5.3 Admin
- xem báo cáo tổng hợp
- kiểm tra dữ liệu kết quả
- lọc theo khóa học / module / học viên
- đảm bảo hệ thống minh bạch và dễ đối soát

---

## 6. Nguyên tắc triển khai bắt buộc

Bạn phải làm theo **phase**, phase nào xong thì dừng lại để test phase đó.

### Bắt buộc
- không làm dồn tất cả một lần
- không nhảy phase
- xong phase nào phải test phase đó
- chỉ khi phase hiện tại ổn mới chuyển phase mới
- ưu tiên tận dụng code hiện có
- không tạo thêm bảng/model vô nghĩa nếu hệ thống đã có cấu trúc gần đúng
- không refactor phá hỏng flow cũ đang chạy

---

# 7. TRIỂN KHAI THEO PHASE

---

## PHASE 1 — Phân tích hiện trạng và lập bản đồ dữ liệu kết quả học tập

### Mục tiêu
Hiểu rõ hệ thống hiện tại đang quản lý kết quả học tập tới đâu.

### Việc cần làm
1. Đọc route, model, controller, migration, view liên quan
2. Xác định:
   - dữ liệu bài kiểm tra
   - dữ liệu bài làm
   - dữ liệu điểm
   - dữ liệu kết quả học tập
   - dữ liệu tiến độ
   - dữ liệu điểm danh nếu tham gia vào kết quả học tập
3. Vẽ lại luồng dữ liệu:
   - điểm phát sinh từ đâu
   - lưu vào bảng nào
   - hiển thị ở đâu
4. Xác định các lỗ hổng hiện tại

### Output bắt buộc của phase 1
- danh sách file đã đọc
- mô tả hiện trạng
- bảng nào đang dùng cho kết quả học tập
- thiếu gì
- đề xuất cấu trúc dữ liệu chuẩn hóa

### Cách test phase 1
- không test UI mới
- chỉ cần liệt kê rõ được hệ thống hiện tại đang có gì và thiếu gì

---

## PHASE 2 — Chuẩn hóa mô hình dữ liệu quản lý kết quả học tập

### Mục tiêu
Đưa dữ liệu kết quả học tập về một cấu trúc chặt chẽ, dễ mở rộng.

### Yêu cầu nghiệp vụ
Kết quả học tập nên hỗ trợ ít nhất các cấp:

#### Kết quả theo bài kiểm tra
- học viên
- bài kiểm tra
- điểm
- trạng thái đạt / chưa đạt
- số lần làm
- ngày hoàn thành

#### Kết quả theo module
- học viên
- module
- điểm trung bình module
- trạng thái hoàn thành
- nhận xét nếu có

#### Kết quả theo khóa học
- học viên
- khóa học
- điểm tổng kết
- tỷ lệ hoàn thành
- trạng thái đạt / chưa đạt / đang học
- nhận xét tổng kết nếu có

### Việc cần làm
1. Kiểm tra bảng `ket_qua_hoc_tap` hiện có
2. Đánh giá xem có đủ để gánh 3 cấp kết quả trên không
3. Nếu chưa đủ:
   - thêm field
   - hoặc thêm bảng phụ hợp lý
4. Tạo quan hệ model rõ ràng giữa:
   - học viên
   - khóa học
   - module
   - bài kiểm tra
   - bài làm
   - kết quả học tập

### Yêu cầu kỹ thuật
- không phá dữ liệu cũ
- migration phải rõ nghĩa
- relation phải đầy đủ
- ưu tiên đặt tên dễ hiểu

### Output bắt buộc của phase 2
- migration mới
- field mới
- model/relation mới
- giải thích cấu trúc dữ liệu sau khi chuẩn hóa

### Cách test phase 2
- migrate thử
- kiểm tra relation
- kiểm tra dữ liệu cũ có còn dùng được không

---

## PHASE 3 — Tự động tổng hợp kết quả học tập từ dữ liệu bài kiểm tra

### Mục tiêu
Khi học viên làm bài và có điểm, hệ thống phải tự sinh hoặc cập nhật kết quả học tập.

### Việc cần làm
1. Xác định nơi hiện tại đang chấm điểm bài kiểm tra
2. Gắn logic đồng bộ kết quả học tập vào flow đó
3. Khi có bài làm mới / chấm xong:
   - cập nhật kết quả theo bài kiểm tra
   - cập nhật kết quả theo module
   - cập nhật kết quả theo khóa học
4. Nếu có trắc nghiệm tự động chấm:
   - cập nhật ngay
5. Nếu có tự luận chấm tay:
   - cập nhật sau khi chấm xong
6. Nếu có nhiều lần làm bài:
   - xác định quy tắc dùng điểm cao nhất / lần cuối / trung bình
   - quy tắc này phải rõ ràng

### Yêu cầu nghiệp vụ
Cần xử lý được:
- bài kiểm tra chưa làm
- bài kiểm tra đã làm nhưng chưa chấm xong
- bài kiểm tra đã có điểm
- học viên đạt / chưa đạt
- module có đủ bài kiểm tra để tính tổng hợp chưa

### Output bắt buộc của phase 3
- logic tổng hợp nằm ở đâu
- file đã sửa
- service/helper mới nếu có
- quy tắc tính điểm đang dùng

### Cách test phase 3
- tạo dữ liệu bài làm mẫu
- chấm điểm
- kiểm tra bảng kết quả học tập
- kiểm tra số liệu tổng hợp theo module / khóa học

---

## PHASE 4 — Bổ sung chuyên cần / điểm danh vào kết quả học tập (nếu phù hợp nghiệp vụ)

### Mục tiêu
Nếu hệ thống có điểm danh học viên, có thể dùng dữ liệu đó để phản ánh chuyên cần.

### Việc cần làm
1. Kiểm tra dữ liệu attendance học viên hiện có
2. Đánh giá có nên đưa vào kết quả học tập không
3. Nếu có:
   - tính tỷ lệ tham gia
   - số buổi có mặt / vắng / có phép / đi trễ
   - quy đổi thành chỉ số chuyên cần hoặc thông tin phụ
4. Hiển thị chuyên cần trong kết quả học tập

### Lưu ý
Nếu nghiệp vụ trung tâm chưa cần điểm chuyên cần thì vẫn nên lưu và hiển thị như một chỉ số tham khảo, không nhất thiết tính vào điểm tổng kết.

### Output bắt buộc của phase 4
- attendance có được đưa vào kết quả hay không
- nếu có thì quy tắc nào đang dùng
- file đã sửa
- cách hiển thị mới

### Cách test phase 4
- tạo dữ liệu attendance mẫu
- kiểm tra kết quả học tập có phản ánh chuyên cần đúng không

---

## PHASE 5 — Xây dựng màn hình “Kết quả học tập” cho học viên

### Mục tiêu
Học viên có một màn hình rõ ràng để xem kết quả học tập của mình.

### Màn hình cần có
#### A. Tổng quan
- khóa học
- trạng thái học tập
- tỷ lệ hoàn thành
- kết quả tổng kết
- đạt / chưa đạt

#### B. Theo module
- danh sách module
- điểm từng module
- tiến độ từng module
- trạng thái hoàn thành

#### C. Theo bài kiểm tra
- tên bài kiểm tra
- điểm
- số lần làm
- trạng thái
- ngày làm bài

#### D. Chuyên cần / ghi chú
- số buổi đã học
- tỷ lệ tham gia
- nhận xét nếu có

### Việc cần làm
1. Kiểm tra hiện có trang nào gần với chức năng này chưa
2. Nếu chưa có, tạo route/controller/view mới
3. Nếu có rồi nhưng sơ sài, refactor lại
4. UI phải dễ hiểu và phù hợp demo đồ án

### Output bắt buộc của phase 5
- route mới
- controller mới hoặc method mới
- view mới/sửa
- mô tả UI kết quả học tập của học viên

### Cách test phase 5
- đăng nhập học viên
- vào màn hình kết quả học tập
- kiểm tra xem đủ dữ liệu tổng quan, module, bài kiểm tra chưa

---

## PHASE 6 — Xây dựng màn hình xem kết quả học tập cho giảng viên

### Mục tiêu
Giảng viên có thể xem và quản lý kết quả học tập của học viên trong khóa mình dạy.

### Việc cần làm
1. Tạo hoặc refactor màn hình danh sách học viên của khóa/module
2. Với mỗi học viên, giảng viên xem được:
   - điểm các bài kiểm tra
   - kết quả module
   - kết quả khóa học
   - chuyên cần nếu có
3. Nếu có phần tự luận / đánh giá thủ công:
   - giảng viên được cập nhật điểm
   - giảng viên được thêm nhận xét
4. Nếu nghiệp vụ cần:
   - có thao tác chốt kết quả

### Output bắt buộc của phase 6
- route / controller / view cho giảng viên
- quyền truy cập
- luồng cập nhật / xem kết quả

### Cách test phase 6
- đăng nhập giảng viên
- vào khóa học đang dạy
- xem kết quả học viên
- chỉnh sửa điểm / nhận xét nếu có
- kiểm tra dữ liệu lưu lại đúng

---

## PHASE 7 — Xây dựng màn hình báo cáo kết quả học tập cho admin

### Mục tiêu
Admin có cái nhìn tổng hợp toàn hệ thống hoặc theo từng khóa.

### Màn hình nên có
- lọc theo khóa học
- lọc theo module
- lọc theo học viên
- danh sách đạt / chưa đạt
- tỷ lệ hoàn thành
- điểm trung bình
- số học viên đang học / hoàn thành / chưa đạt

### Việc cần làm
1. Tạo route/controller/view báo cáo admin
2. Tạo truy vấn tổng hợp
3. Nếu cần, tạo service tổng hợp kết quả
4. UI đơn giản nhưng rõ ràng

### Output bắt buộc của phase 7
- route mới
- controller mới
- truy vấn / service tổng hợp
- giao diện báo cáo

### Cách test phase 7
- đăng nhập admin
- mở báo cáo kết quả học tập
- lọc theo khóa học / module
- kiểm tra số liệu đúng

---

## PHASE 8 — Hoàn thiện quy tắc đạt / chưa đạt và trạng thái học tập

### Mục tiêu
Chuẩn hóa quy tắc học viên đạt hay chưa đạt.

### Trạng thái đề xuất
- `dang_hoc`
- `cho_cham`
- `hoan_thanh`
- `dat`
- `khong_dat`

### Việc cần làm
1. Xác định quy tắc đạt:
   - theo điểm trung bình
   - theo bài kiểm tra bắt buộc
   - theo tỷ lệ chuyên cần
2. Chuẩn hóa cách lưu trạng thái
3. Hiển thị rõ trên UI học viên / giảng viên / admin

### Output bắt buộc của phase 8
- quy tắc đạt / chưa đạt
- field / logic dùng ở đâu
- UI hiển thị ra sao

### Cách test phase 8
- tạo các case đạt / chưa đạt
- kiểm tra trạng thái hiển thị đúng

---

## PHASE 9 — Refactor cuối cùng để dùng tốt cho đồ án

### Mục tiêu
Làm phần quản lý kết quả học tập đủ đẹp, đủ chặt, đủ rõ để demo và bảo vệ đồ án.

### Việc cần làm
1. Rà lại route/controller/model/view
2. Tách service xử lý tổng hợp nếu cần
3. Giảm logic nặng trong blade
4. Chuẩn hóa badge / trạng thái / card / bảng
5. Đảm bảo:
   - học viên xem dễ hiểu
   - giảng viên thao tác dễ
   - admin lọc và kiểm tra dễ

### Output bắt buộc của phase 9
- danh sách file refactor cuối
- flow hoàn chỉnh
- cách demo toàn bộ chức năng

### Cách test phase 9
- demo bằng 3 vai trò:
  - admin
  - giảng viên
  - học viên
- đi qua 1 khóa học mẫu có dữ liệu đầy đủ
- kiểm tra kết quả học tập từ lúc thi đến lúc tổng hợp cuối

---

# 8. Ràng buộc kỹ thuật

Bạn phải tuân thủ:

1. Không hardcode ID
2. Không phá flow cũ đang chạy
3. Ưu tiên tận dụng bảng/model hiện có
4. Chỉ thêm migration mới khi thật sự cần
5. Validate đầy đủ
6. Nếu logic tổng hợp phức tạp, tách service riêng
7. Code phải dễ hiểu, dễ bảo trì
8. Cần giải thích rõ mỗi phase thay đổi gì

---

# 9. Format output bắt buộc sau mỗi phase

Sau mỗi phase, bạn phải trả về đúng format này:

## A. Phân tích hiện trạng
- file đã đọc
- logic đang nằm ở đâu
- vấn đề hiện tại là gì

## B. File đã sửa
- route
- controller
- model
- migration
- view
- service/helper nếu có

## C. Những gì thêm mới
- field mới
- bảng mới
- relation mới
- method mới
- route mới

## D. Flow sau khi sửa
- người dùng thao tác gì
- hệ thống xử lý gì
- kết quả hiển thị gì

## E. Cách test thủ công
- URL test
- vai trò nào test
- bước test
- kết quả mong đợi
- bảng DB cần kiểm tra nếu cần

---

# 10. Kết quả cuối cùng tôi mong muốn

Sau khi hoàn thiện, hệ thống phải đạt được:

1. Học viên có màn hình kết quả học tập rõ ràng, đầy đủ
2. Giảng viên quản lý được kết quả học tập học viên
3. Admin có báo cáo tổng hợp kết quả học tập
4. Điểm thi, tiến độ, chuyên cần, trạng thái đạt/chưa đạt được tổng hợp hợp lý
5. Hệ thống phù hợp để demo đồ án và dễ mở rộng sau này

---

# 11. Yêu cầu làm việc cuối cùng

Bắt đầu từ **PHASE 1**.

- Không được nhảy phase
- Không được gom tất cả vào 1 lần sửa
- Phase nào xong phải dừng lại
- Phải output đúng format yêu cầu
- Phải đưa cách test rất cụ thể
- Chỉ khi phase hiện tại ổn mới chuyển phase tiếp theo
