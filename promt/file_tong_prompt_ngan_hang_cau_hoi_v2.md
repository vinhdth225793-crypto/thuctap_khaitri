# FILE TỔNG PROMPT NGÂN HÀNG CÂU HỎI

## Mục tiêu module
Triển khai module **Ngân hàng câu hỏi trắc nghiệm** cho hệ thống Laravel `thuctap_khaitri` theo từng phase nhỏ, làm xong phần nào dứt phần đó.

### Nghiệp vụ chốt
- Ngân hàng câu hỏi được lưu theo **KHÓA HỌC**
- Chỉ làm **câu hỏi trắc nghiệm** ở giai đoạn này
- Mỗi câu hỏi gồm:
  - 1 cột câu hỏi
  - 3 cột đáp án sai
  - 1 cột đáp án đúng
- Hệ thống phải kiểm tra **trùng lặp câu hỏi** trong cùng khóa học
- Có **file Excel mẫu** để admin hoặc giảng viên tải về nhập câu hỏi
- Có chức năng **import Excel**
- Khi import phải có bước:
  - preview dữ liệu
  - kiểm tra lỗi dữ liệu
  - kiểm tra trùng lặp trong file
  - kiểm tra trùng lặp với hệ thống
  - chỉ lưu các dòng hợp lệ sau khi xác nhận
- **Admin** toàn quyền
- **Giảng viên** chỉ thao tác với khóa học mà mình phụ trách
- **Học viên** không có quyền

---

# PROMPT TỔNG ĐIỀU PHỐI

```text
Bạn đang làm việc trên repo Laravel hiện có của tôi: thuctap_khaitri.

Tôi muốn bạn triển khai module “Ngân hàng câu hỏi trắc nghiệm” theo từng phase nhỏ. Chỉ làm đúng phase tôi gửi, không làm lan sang phase khác.

Bối cảnh nghiệp vụ:
- Ngân hàng câu hỏi lưu theo KHÓA HỌC
- Chỉ làm câu hỏi trắc nghiệm ở giai đoạn này
- Mỗi câu hỏi gồm:
  - 1 cột câu hỏi
  - 3 cột đáp án sai
  - 1 cột đáp án đúng
- Hệ thống phải kiểm tra trùng lặp câu hỏi trong cùng khóa học
- Có file Excel mẫu để admin hoặc giảng viên tải về nhập câu hỏi
- Có chức năng import Excel
- Khi import phải có bước preview, kiểm tra lỗi dữ liệu, kiểm tra trùng lặp trong file và trùng với hệ thống rồi mới cho lưu
- Admin toàn quyền
- Giảng viên chỉ thao tác được với khóa học mà mình phụ trách
- Học viên không có quyền

Nguyên tắc làm việc:
1. Luôn phân tích code hiện có trước khi sửa
2. Không phá các chức năng cũ đang chạy
3. Giữ code Laravel rõ ràng, dễ bảo trì
4. Ưu tiên blade đơn giản, dễ dùng
5. Nếu cần, tách logic ra service thay vì nhét hết vào controller
6. Sau mỗi phase phải liệt kê:
   - file tạo
   - file sửa
   - route mới
   - cách test thủ công

Bây giờ hãy chờ tôi gửi Phase 1.
```

---

# PHASE 1 — TẠO NỀN DỮ LIỆU VÀ CRUD THỦ CÔNG

```text
Bạn đang làm việc trên repo Laravel hiện có của tôi: thuctap_khaitri.

Tôi muốn bạn triển khai Phase 1 của module “Ngân hàng câu hỏi trắc nghiệm”.

Mục tiêu phase này:
- Tạo cấu trúc dữ liệu cho ngân hàng câu hỏi
- Lưu câu hỏi theo KHÓA HỌC
- Làm CRUD thủ công cơ bản
- Có kiểm tra trùng lặp khi thêm/sửa thủ công
- Chưa làm import/export Excel ở phase này

Bối cảnh dự án:
- Hệ thống đã có phân quyền admin, giảng viên, học viên
- Đã có khóa học, module, lịch học, bài kiểm tra cơ bản
- Giai đoạn này chỉ làm câu hỏi TRẮC NGHIỆM
- Mỗi câu hỏi gồm:
  - nội dung câu hỏi
  - 3 đáp án sai
  - 1 đáp án đúng

Yêu cầu thực hiện:

1. Tạo migration và model cho bảng ngân_hang_cau_hoi với các cột:
- id
- khoa_hoc_id
- noi_dung_cau_hoi
- dap_an_sai_1
- dap_an_sai_2
- dap_an_sai_3
- dap_an_dung
- nguoi_tao_id
- created_at
- updated_at
- deleted_at nếu dùng soft delete thì thêm

2. Thiết lập quan hệ model:
- CauHoi thuộc KhoaHoc
- CauHoi thuộc NguoiDung
- KhoaHoc có nhiều CauHoi

3. Kiểm tra trùng lặp câu hỏi trong cùng khóa học:
- Chuẩn hóa nội dung trước khi so sánh:
  - trim khoảng trắng đầu cuối
  - gộp nhiều khoảng trắng thành 1
  - lowercase
- Nếu cùng khóa học mà nội dung câu hỏi bị trùng thì không cho lưu
- Áp dụng cho cả thêm mới và cập nhật

4. Tạo module CRUD cơ bản:
- Danh sách câu hỏi
- Thêm mới câu hỏi
- Sửa câu hỏi
- Xóa câu hỏi
- Tìm kiếm theo nội dung câu hỏi
- Lọc theo khóa học
- Phân trang

5. Validate dữ liệu:
- không được để trống nội dung câu hỏi
- không được để trống 3 đáp án sai và 1 đáp án đúng
- đáp án đúng không được trùng với bất kỳ đáp án sai nào
- 3 đáp án sai không được trùng nhau
- nếu thêm/sửa thủ công mà câu hỏi trùng thì báo lỗi rõ ràng

6. Phân quyền:
- Admin được xem và thao tác toàn bộ ngân hàng câu hỏi
- Giảng viên chỉ được xem/thêm/sửa/xóa câu hỏi của khóa học mà mình được phân công
- Học viên không có quyền truy cập module này

7. Tạo route, controller, request validation, blade view theo phong cách repo hiện có

8. Giao diện:
- đơn giản, sạch, dễ dùng
- dùng blade
- không cần giao diện quá cầu kỳ

Không làm trong phase này:
- Không export Excel
- Không import Excel
- Không preview import
- Không gắn câu hỏi vào bài kiểm tra
- Không làm câu hỏi tự luận

Đầu ra bắt buộc:
1. Liệt kê các file đã tạo
2. Liệt kê các file đã sửa
3. Liệt kê các route mới
4. Hướng dẫn migrate
5. Hướng dẫn test thủ công
6. Giải thích cách kiểm tra trùng lặp đang xử lý
```

---

# PHASE 2 — EXPORT FILE EXCEL MẪU

```text
Tiếp tục trên repo Laravel hiện có của tôi: thuctap_khaitri.

Hãy triển khai Phase 2 của module “Ngân hàng câu hỏi trắc nghiệm”.

Bối cảnh:
- Phase 1 đã có bảng ngân hàng câu hỏi và CRUD thủ công
- Câu hỏi đang lưu theo khóa học
- Mỗi câu hỏi gồm:
  - nội dung câu hỏi
  - 3 đáp án sai
  - 1 đáp án đúng

Mục tiêu phase này:
- Tạo chức năng tải xuống file Excel mẫu
- File mẫu dùng để admin hoặc giảng viên nhập nhiều câu hỏi trắc nghiệm
- Chưa làm import ở phase này

Yêu cầu thực hiện:

1. Tạo chức năng export file Excel mẫu
2. File Excel mẫu phải có đúng 5 cột:
- cau_hoi
- dap_an_sai_1
- dap_an_sai_2
- dap_an_sai_3
- dap_an_dung

3. Dòng đầu tiên là header rõ ràng
4. Có thể thêm dòng ví dụ mẫu nếu thấy hợp lý, nhưng ưu tiên file sạch, dễ nhập
5. Nếu tiện thì có thể thêm 1 sheet hướng dẫn ngắn, nhưng không bắt buộc
6. Chỉ export file mẫu nhập liệu, chưa export dữ liệu thật từ database

7. Phân quyền:
- Admin tải được file mẫu
- Giảng viên tải được file mẫu
- Học viên không có quyền

8. Tạo route và nút tải file mẫu trong giao diện quản lý ngân hàng câu hỏi

9. Nếu cần package Laravel Excel thì cài đặt gọn gàng và ghi rõ hướng dẫn cài

Không làm trong phase này:
- Không import Excel
- Không đọc file Excel
- Không preview dữ liệu
- Không lưu dữ liệu từ file

Đầu ra bắt buộc:
1. Liệt kê file tạo/sửa
2. Liệt kê route mới
3. Nếu có package mới thì ghi lệnh cài
4. Hướng dẫn test chức năng tải file mẫu
5. Mô tả cấu trúc file Excel mẫu
```

---

# PHASE 3 — UPLOAD FILE VÀ PREVIEW DỮ LIỆU

```text
Tiếp tục trên repo Laravel hiện có của tôi: thuctap_khaitri.

Hãy triển khai Phase 3 của module “Ngân hàng câu hỏi trắc nghiệm”.

Bối cảnh:
- Đã có CRUD thủ công
- Đã có file Excel mẫu
- Giờ tôi muốn upload file Excel và xem trước dữ liệu trước khi lưu

Mục tiêu phase này:
- Cho admin hoặc giảng viên upload file Excel
- Đọc dữ liệu từ file
- Hiển thị preview danh sách câu hỏi chuẩn bị import
- Chưa lưu thật vào database ngay ở bước đầu

Yêu cầu thực hiện:

1. Tạo giao diện import gồm:
- chọn khóa học
- chọn file Excel
- nút đọc file / xem trước

2. Khi upload file:
- đọc toàn bộ dòng dữ liệu
- bỏ qua dòng header
- parse dữ liệu theo đúng 5 cột:
  - cau_hoi
  - dap_an_sai_1
  - dap_an_sai_2
  - dap_an_sai_3
  - dap_an_dung

3. Hiển thị preview dữ liệu trên giao diện dưới dạng bảng, gồm:
- STT
- Câu hỏi
- Đáp án sai 1
- Đáp án sai 2
- Đáp án sai 3
- Đáp án đúng
- Trạng thái
- Ghi chú

4. Chưa lưu vào database ở bước preview
5. Dữ liệu preview có thể lưu tạm ở session, cache hoặc bảng tạm tùy giải pháp đơn giản và ổn định nhất

6. Validate từng dòng:
- thiếu câu hỏi => lỗi
- thiếu 1 trong 4 đáp án => lỗi
- đáp án đúng trùng với 1 đáp án sai => lỗi
- các đáp án bị trùng nhau => lỗi

7. Phân quyền:
- Admin được import
- Giảng viên chỉ import cho khóa học mình phụ trách
- Học viên không có quyền

8. Giao diện preview phải dễ hiểu, đơn giản, hiển thị được trạng thái từng dòng

Không làm trong phase này:
- Chưa kiểm tra trùng lặp trong file và database ở mức hoàn chỉnh nếu chưa cần
- Chưa lưu dữ liệu thật vào database
- Chưa xác nhận import cuối cùng

Đầu ra bắt buộc:
1. Liệt kê file tạo/sửa
2. Liệt kê route mới
3. Hướng dẫn test upload và preview
4. Giải thích cách dữ liệu preview đang được lưu tạm
```

---

# PHASE 4 — KIỂM TRA TRÙNG LẶP

```text
Tiếp tục trên repo Laravel hiện có của tôi: thuctap_khaitri.

Hãy triển khai Phase 4 của module “Ngân hàng câu hỏi trắc nghiệm”.

Bối cảnh:
- Đã có upload và preview dữ liệu Excel
- Giờ tôi muốn kiểm tra trùng lặp thật rõ trước khi lưu

Mục tiêu phase này:
- Phát hiện câu hỏi trùng trong chính file import
- Phát hiện câu hỏi trùng với database của cùng khóa học
- Hiển thị trạng thái rõ ràng cho từng dòng

Yêu cầu thực hiện:

1. Chuẩn hóa nội dung câu hỏi trước khi so sánh:
- trim khoảng trắng đầu cuối
- thay nhiều khoảng trắng liên tiếp thành 1 khoảng trắng
- chuyển về lowercase

2. Kiểm tra 2 loại trùng:
- trung_lap_trong_file
- trung_lap_trong_he_thong

3. Trùng lặp chỉ tính trong phạm vi cùng khóa học

4. Với mỗi dòng preview, xác định một trong các trạng thái:
- hop_le
- trung_lap_trong_file
- trung_lap_trong_he_thong
- loi_du_lieu

5. Hiển thị tổng kết trên màn hình preview:
- tổng số dòng
- số dòng hợp lệ
- số dòng trùng trong file
- số dòng trùng trong hệ thống
- số dòng lỗi

6. Hiển thị ghi chú rõ ràng cho từng dòng
Ví dụ:
- Trùng với dòng 3 trong file
- Trùng với câu hỏi đã có trong hệ thống
- Thiếu đáp án đúng
- Đáp án đúng bị trùng với đáp án sai

7. Không cho import tự động các dòng trùng hoặc lỗi

8. Tách logic kiểm tra trùng lặp ra service hoặc helper rõ ràng, không nhét quá nhiều vào controller

Không làm trong phase này:
- Chưa lưu dữ liệu vào database
- Chưa xác nhận import cuối cùng

Đầu ra bắt buộc:
1. Liệt kê file tạo/sửa
2. Giải thích rõ thuật toán kiểm tra trùng lặp
3. Hướng dẫn test với 3 trường hợp:
   - file hợp lệ
   - file có dòng trùng nhau
   - file trùng với dữ liệu đã có trong hệ thống
```

---

# PHASE 5 — XÁC NHẬN IMPORT VÀ CHỈ LƯU DÒNG HỢP LỆ

```text
Tiếp tục trên repo Laravel hiện có của tôi: thuctap_khaitri.

Hãy triển khai Phase 5 của module “Ngân hàng câu hỏi trắc nghiệm”.

Bối cảnh:
- Đã có upload file
- Đã có preview
- Đã có kiểm tra lỗi dữ liệu và trùng lặp
- Giờ tôi muốn xác nhận import và lưu vào database

Mục tiêu phase này:
- Cho người dùng bấm xác nhận import
- Chỉ lưu các dòng hợp lệ
- Bỏ qua các dòng trùng hoặc lỗi
- Hiển thị kết quả import rõ ràng

Yêu cầu thực hiện:

1. Thêm nút “Xác nhận import”
2. Khi người dùng bấm xác nhận:
- chỉ lưu các dòng có trạng thái hop_le
- không lưu các dòng trung_lap_trong_file
- không lưu các dòng trung_lap_trong_he_thong
- không lưu các dòng loi_du_lieu

3. Sau khi import xong, hiển thị kết quả:
- đã thêm bao nhiêu câu hỏi
- bao nhiêu dòng trùng trong file
- bao nhiêu dòng trùng trong hệ thống
- bao nhiêu dòng lỗi
- bao nhiêu dòng bị bỏ qua

4. Sau khi import thành công:
- xóa dữ liệu preview tạm
- quay về danh sách ngân hàng câu hỏi hoặc trang kết quả import

5. Ghi nhận nguoi_tao_id đúng theo tài khoản đang đăng nhập

6. Kiểm tra phân quyền kỹ:
- admin import toàn quyền
- giảng viên chỉ import cho khóa học mình được phép

7. Đảm bảo import không bị lưu trùng nếu bấm submit nhiều lần
- cần có cách chống double submit đơn giản

Không làm trong phase này:
- Không làm import tự luận
- Không làm random đề
- Không làm gắn vào bài kiểm tra

Đầu ra bắt buộc:
1. Liệt kê file tạo/sửa
2. Mô tả flow import hoàn chỉnh
3. Hướng dẫn test end-to-end
4. Giải thích cách tránh lưu trùng khi submit lại
```

---

# PHASE 6 — HOÀN THIỆN, DỌN CODE, KIỂM THỬ

```text
Tiếp tục trên repo Laravel hiện có của tôi: thuctap_khaitri.

Hãy triển khai Phase 6 để hoàn thiện module “Ngân hàng câu hỏi trắc nghiệm”.

Mục tiêu:
- Rà soát toàn bộ module ngân hàng câu hỏi
- Dọn code
- Hoàn thiện giao diện
- Seed dữ liệu mẫu
- Viết hướng dẫn kỹ thuật ngắn

Yêu cầu thực hiện:

1. Rà soát toàn bộ:
- migration
- model
- controller
- service
- request validation
- blade view
- route

2. Chuẩn hóa:
- message thông báo
- tên biến
- validate
- phân quyền
- format giao diện

3. Bổ sung seed dữ liệu mẫu:
- một vài khóa học
- một vài câu hỏi mẫu
- dữ liệu để test import nếu cần

4. Kiểm tra các flow:
- thêm thủ công
- sửa
- xóa
- tải file mẫu
- upload file
- preview
- phát hiện trùng lặp
- xác nhận import

5. Viết file tài liệu kỹ thuật ngắn trong repo:
- mô tả module ngân hàng câu hỏi
- cấu trúc bảng
- cách migrate
- cách seed
- cách test import Excel

6. Không làm thêm tính năng mới ngoài phạm vi ngân hàng câu hỏi

Đầu ra bắt buộc:
1. Checklist các việc đã hoàn thành
2. Danh sách file tạo/sửa cuối cùng
3. Hướng dẫn chạy module từ đầu
4. Gợi ý các phần tiếp theo có thể làm sau ngân hàng câu hỏi
```

---

# THỨ TỰ NÊN LÀM

1. **Phase 1**: CRUD thủ công + dữ liệu nền  
2. **Phase 2**: export file Excel mẫu  
3. **Phase 3**: upload và preview  
4. **Phase 4**: kiểm tra trùng lặp  
5. **Phase 5**: xác nhận import và lưu  
6. **Phase 6**: dọn code và hoàn thiện  

---

# KẾT QUẢ CUỐI CÙNG MONG MUỐN

Sau khi hoàn thành đủ 6 phase, module ngân hàng câu hỏi phải làm được:

- Admin thêm thủ công câu hỏi trắc nghiệm theo khóa học
- Giảng viên thêm hoặc import câu hỏi cho khóa học mình phụ trách
- Admin/giảng viên tải file Excel mẫu
- Nhập nhiều câu hỏi bằng file Excel
- Hệ thống đọc file và hiển thị preview
- Hệ thống kiểm tra lỗi dữ liệu
- Hệ thống kiểm tra câu hỏi trùng trong file
- Hệ thống kiểm tra câu hỏi trùng với database
- Chỉ lưu các câu hợp lệ
- Có thông báo kết quả import rõ ràng
- Có seed/test/tài liệu kỹ thuật cơ bản
