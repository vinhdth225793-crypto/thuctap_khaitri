# FLOW QUAN HỆ GIỮA MÔN HỌC – KHÓA HỌC – MODULE – ACTOR

## 1. Mục đích
Tài liệu này mô tả flow nghiệp vụ và mối quan hệ giữa **môn học**, **khóa học**, **module** với các actor **Admin**, **Giảng viên**, **Học viên** trong hệ thống **học và kiểm tra online** theo hướng nghiệp vụ phù hợp với trung tâm thực tập.

---

## 2. Các thành phần chính trong hệ thống

### 2.1. Môn học
Môn học là đơn vị kiến thức lớn do trung tâm quản lý.

Ví dụ:
- Tin học văn phòng
- Lập trình PHP
- Tiếng Anh giao tiếp

**Thông tin chính của môn học:**
- Mã môn học
- Tên môn học
- Mô tả
- Trạng thái hoạt động

**Ý nghĩa nghiệp vụ:**
- Môn học là cấp cao nhất trong phần đào tạo
- Mỗi môn học có thể mở ra nhiều khóa học khác nhau theo từng đợt

---

### 2.2. Khóa học
Khóa học là một đợt đào tạo cụ thể của một môn học.

Ví dụ:
- Môn học: Lập trình PHP
- Khóa học: PHP cơ bản khóa 04-2026

**Thông tin chính của khóa học:**
- Mã khóa học
- Tên khóa học
- Thuộc môn học nào
- Ngày khai giảng
- Ngày kết thúc
- Học phí
- Số lượng học viên tối đa
- Trạng thái: mở đăng ký / đang học / đã kết thúc

**Ý nghĩa nghiệp vụ:**
- Một môn học có thể có nhiều khóa học
- Mỗi khóa học chỉ thuộc về một môn học

---

### 2.3. Module
Module là phần nội dung nhỏ trong một khóa học, dùng để chia nội dung giảng dạy thành từng chủ đề, từng buổi học hoặc từng giai đoạn.

Ví dụ khóa học PHP cơ bản có các module:
- Module 1: Giới thiệu PHP
- Module 2: Biến và kiểu dữ liệu
- Module 3: Cấu trúc điều khiển
- Module 4: Form và xử lý dữ liệu
- Module 5: Kiểm tra cuối khóa

**Thông tin chính của module:**
- Mã module
- Tên module
- Thuộc khóa học nào
- Thứ tự học
- Mô tả
- Thời lượng
- Loại module: lý thuyết / thực hành / kiểm tra
- Trạng thái

**Ý nghĩa nghiệp vụ:**
- Một khóa học có nhiều module
- Một module chỉ thuộc một khóa học
- Module là nơi gắn nội dung học, bài tập, tài liệu, bài kiểm tra

---

## 3. Các actor trong hệ thống

### 3.1. Admin
Admin là người quản trị toàn bộ hoạt động đào tạo trên hệ thống.

**Nhiệm vụ chính:**
- Quản lý môn học
- Quản lý khóa học
- Quản lý module
- Quản lý tài khoản giảng viên, học viên
- Phân công giảng viên phụ trách khóa học hoặc module
- Duyệt học viên vào khóa học
- Theo dõi tiến độ học tập
- Theo dõi kết quả kiểm tra
- Quản lý trạng thái hoạt động của hệ thống

---

### 3.2. Giảng viên
Giảng viên là người được admin phân công giảng dạy trong khóa học hoặc module.

**Nhiệm vụ chính:**
- Xem khóa học được phân công
- Xem module được giao phụ trách
- Đăng nội dung bài học
- Đăng tài liệu, video, bài tập
- Tạo hoặc quản lý bài kiểm tra nếu được phân quyền
- Theo dõi học viên trong khóa học
- Chấm điểm hoặc xem kết quả học tập

---

### 3.3. Học viên
Học viên là người tham gia học và làm kiểm tra online.

**Nhiệm vụ chính:**
- Đăng ký tài khoản
- Tham gia khóa học sau khi được admin duyệt hoặc xếp lớp
- Xem danh sách khóa học đã tham gia
- Học theo từng module
- Xem tài liệu, video, nội dung bài học
- Làm bài tập, bài kiểm tra
- Xem kết quả và tiến độ học tập

---

## 4. Quan hệ giữa môn học – khóa học – module

### 4.1. Quan hệ môn học và khóa học
- Một **môn học** có thể có **nhiều khóa học**
- Một **khóa học** chỉ thuộc **một môn học**

**Biểu diễn:**
- Môn học 1 --- n Khóa học

---

### 4.2. Quan hệ khóa học và module
- Một **khóa học** có thể có **nhiều module**
- Một **module** chỉ thuộc **một khóa học**

**Biểu diễn:**
- Khóa học 1 --- n Module

---

### 4.3. Quan hệ khóa học và giảng viên
- Một **khóa học** có thể có **một hoặc nhiều giảng viên**
- Một **giảng viên** có thể dạy **nhiều khóa học**

**Biểu diễn:**
- Khóa học n --- n Giảng viên

Có thể quản lý qua bảng phân công giảng dạy.

---

### 4.4. Quan hệ module và giảng viên
- Một **module** được admin phân công cho **một giảng viên phụ trách chính**
- Một **giảng viên** có thể phụ trách **nhiều module**

**Biểu diễn:**
- Giảng viên 1 --- n Module
hoặc
- Module thuộc phân công cụ thể của giảng viên

---

### 4.5. Quan hệ khóa học và học viên
- Một **khóa học** có **nhiều học viên**
- Một **học viên** có thể tham gia **nhiều khóa học**

**Biểu diễn:**
- Khóa học n --- n Học viên

Cần bảng trung gian để lưu:
- ngày tham gia
- trạng thái học
- kết quả cuối khóa
- tiến độ học tập

---

## 5. Flow nghiệp vụ tổng thể

## Bước 1: Admin tạo môn học
Admin đăng nhập hệ thống và vào chức năng quản lý môn học.

Admin thực hiện:
- thêm mới môn học
- nhập tên môn học
- nhập mô tả
- chọn trạng thái hoạt động

Hệ thống lưu môn học vào cơ sở dữ liệu.

**Kết quả:**
- Môn học được tạo thành công
- Môn học là nền tảng để mở các khóa học sau này

---

## Bước 2: Admin tạo sẵn khóa học thuộc môn học
Sau khi có môn học, admin có thể tạo sẵn các khóa học để trung tâm chủ động quản lý kế hoạch đào tạo.

### Hướng nghiệp vụ
- Admin tạo sẵn thông tin khóa học trước
- Khóa học ban đầu ở trạng thái: **chờ mở**
- Học viên có thể được tư vấn, ghi danh hoặc đăng ký trước vào khóa học
- Khi số lượng học viên đã đủ hoặc đạt mức phù hợp, admin chỉ cần **mở khóa học** và **chọn ngày khai giảng**
- Nếu phát sinh đợt học mới, admin có thể **thêm khóa học mới** cùng môn học đó

### Admin nhập khi tạo sẵn khóa học:
- tên khóa học
- mã khóa học
- học phí
- số lượng tối đa
- số lượng tối thiểu để mở lớp (nếu có)
- mô tả khóa học
- trạng thái ban đầu: chờ mở

### Khi đủ học viên và tiến hành mở khóa học:
Admin thực hiện:
- chọn khóa học đã tạo sẵn
- cập nhật trạng thái từ **chờ mở** sang **mở / đang tuyển sinh / sắp khai giảng**
- chọn ngày khai giảng
- hệ thống có thể tự tính hoặc admin nhập ngày kết thúc

Hệ thống lưu khóa học và liên kết với môn học.

**Kết quả:**
- Một môn học có thể có nhiều khóa học được tạo sẵn
- Khóa học chỉ được mở chính thức khi đủ học viên hoặc đủ điều kiện khai giảng
- Khi có nhu cầu mới, admin chỉ cần thêm khóa học mới mà không ảnh hưởng các khóa học cũ

---

## Bước 3: Admin tạo module cho khóa học
Admin chọn một khóa học rồi tạo danh sách module cho khóa học đó.

Admin nhập cho từng module:
- tên module
- mô tả
- thứ tự học
- thời lượng
- loại module
- trạng thái

Hệ thống lưu danh sách module thuộc khóa học.

**Kết quả:**
- Khóa học được chia thành các phần học rõ ràng
- Học viên có thể học theo thứ tự module
- Giảng viên dễ quản lý nội dung giảng dạy

---

## Bước 4: Admin phân công giảng viên
Admin là người quản lý toàn bộ việc phân công giảng dạy.

Có thể phân công theo 2 hướng:

### Hướng 1: Phân công theo khóa học
- Admin gán một hoặc nhiều giảng viên cho toàn bộ khóa học

### Hướng 2: Phân công theo module
- Admin gán từng module cho giảng viên cụ thể

**Theo nghiệp vụ phù hợp với trung tâm:**
- Admin sẽ là người chủ động phân công
- Giảng viên không tự đăng ký dạy
- Giảng viên chỉ nhận thông báo phần mình phụ trách và chuẩn bị nội dung

**Kết quả:**
- Giảng viên đăng nhập sẽ thấy module hoặc khóa học được giao

---

## Bước 5: Học viên tham gia khóa học
Học viên có thể tham gia khóa học theo mô hình trung tâm quản lý.

Có 2 cách triển khai:

### Cách 1: Học viên đăng ký, admin duyệt
- Học viên chọn khóa học muốn tham gia
- Gửi yêu cầu đăng ký
- Admin xét duyệt
- Nếu hợp lệ, học viên được thêm vào khóa học

### Cách 2: Admin thêm trực tiếp học viên vào khóa học
- Admin chọn khóa học
- Chọn học viên
- Thêm vào danh sách lớp

**Theo hướng phù hợp nghiệp vụ trung tâm:**
- Học viên có thể đăng ký
- Admin là người quyết định cuối cùng để xếp lớp

**Kết quả:**
- Học viên chính thức thuộc khóa học
- Hệ thống ghi nhận quan hệ giữa học viên và khóa học

---

## Bước 6: Giảng viên đăng nội dung cho module
Sau khi được phân công, giảng viên đăng nhập vào hệ thống và xem danh sách module phụ trách.

Giảng viên thực hiện:
- đăng bài học
- đăng tài liệu
- thêm video
- thêm bài tập
- cập nhật hướng dẫn học
- tạo bài kiểm tra nếu được cấp quyền

**Kết quả:**
- Module có nội dung học đầy đủ để học viên truy cập

---

## Bước 7: Học viên học theo module
Học viên đăng nhập vào tài khoản, vào khóa học mình đã tham gia.

Học viên thực hiện:
- xem danh sách module
- học module theo thứ tự
- xem tài liệu, video, nội dung bài học
- làm bài tập hoặc bài kiểm tra nếu có

Hệ thống ghi nhận:
- thời gian học
- module đã hoàn thành
- tiến độ học tập
- điểm số nếu có

**Kết quả:**
- Học viên học tuần tự theo lộ trình khóa học

---

## Bước 8: Kiểm tra online
Kiểm tra online có thể gắn ở:
- cuối mỗi module
- giữa khóa
- cuối khóa

Người tạo bài kiểm tra:
- Admin
- hoặc giảng viên nếu có phân quyền

Thông tin bài kiểm tra:
- tên bài kiểm tra
- thời gian làm bài
- số câu hỏi
- số lần được làm
- điểm đạt
- thời gian mở và đóng bài kiểm tra

Học viên làm bài kiểm tra trên hệ thống.

Hệ thống xử lý:
- nhận bài làm
- chấm điểm tự động với câu trắc nghiệm
- lưu kết quả
- hiển thị điểm cho học viên và giảng viên/admin

**Kết quả:**
- Kết quả kiểm tra được lưu gắn với học viên, module hoặc khóa học

---

## Bước 9: Theo dõi kết quả và hoàn thành khóa học
Admin và giảng viên có thể theo dõi:
- danh sách học viên trong khóa học
- tiến độ học từng module
- số lần làm bài kiểm tra
- điểm số
- kết quả cuối khóa

Học viên có thể xem:
- module đã hoàn thành
- điểm từng bài kiểm tra
- kết quả tổng kết khóa học

**Kết quả:**
- Hệ thống hỗ trợ quản lý học và kiểm tra online đầy đủ

---

## 6. Flow tóm tắt ngắn gọn dạng tuyến

Admin tạo môn học  
→ Admin tạo khóa học thuộc môn học  
→ Admin tạo module cho khóa học  
→ Admin phân công giảng viên cho khóa học hoặc module  
→ Học viên đăng ký hoặc được admin xếp vào khóa học  
→ Giảng viên đăng bài giảng, tài liệu, bài tập cho module  
→ Học viên học theo từng module  
→ Học viên làm bài kiểm tra online  
→ Hệ thống lưu kết quả, tiến độ  
→ Admin và giảng viên theo dõi, đánh giá kết quả học tập

---

## 7. Sơ đồ quan hệ dạng text

Admin
- tạo môn học
- tạo khóa học
- tạo module
- phân công giảng viên
- xếp học viên vào khóa học
- quản lý kiểm tra và kết quả

Môn học
- có nhiều khóa học

Khóa học
- thuộc một môn học
- có nhiều module
- có nhiều học viên
- có một hoặc nhiều giảng viên

Module
- thuộc một khóa học
- do giảng viên phụ trách
- chứa bài học, tài liệu, bài tập, bài kiểm tra

Giảng viên
- được admin phân công
- phụ trách khóa học hoặc module
- đăng nội dung giảng dạy
- theo dõi kết quả học viên

Học viên
- tham gia khóa học
- học các module
- làm bài kiểm tra
- xem kết quả và tiến độ

---

## 8. Kết luận nghiệp vụ
Trong mô hình học và kiểm tra online theo nghiệp vụ trung tâm thực tập:

- **Môn học** là danh mục đào tạo lớn
- **Khóa học** là một lớp/đợt học cụ thể của môn học
- **Module** là các phần nội dung nhỏ trong khóa học
- **Admin** là người quản lý và điều phối toàn bộ
- **Giảng viên** là người được phân công giảng dạy nội dung
- **Học viên** là người tham gia học và làm kiểm tra

Flow này phù hợp với hướng triển khai đồ án:
- Admin làm trung tâm quản lý
- Giảng viên nhận phân công và đăng nội dung dạy
- Học viên tham gia khóa học, học theo module, làm kiểm tra online, xem kết quả trên hệ thống
