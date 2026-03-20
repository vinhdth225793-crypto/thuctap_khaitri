# PROMPT CHI TIẾT CHO GEMINI AGENT CODE
## Dự án: Hệ thống học tập và kiểm tra online - Laravel
## Mục tiêu: Triển khai chức năng Thư viện + Bài giảng + Duyệt Admin theo phase, tận dụng tối đa code hiện có

Bạn là AI coding agent đang làm việc trực tiếp trên repository Laravel của dự án thực tập. Nhiệm vụ của bạn là triển khai chức năng **Thư viện tài nguyên**, **Bài giảng liên kết khóa học**, và **quy trình duyệt nội dung bởi Admin** theo đúng flow đã chốt.

## 0. YÊU CẦU LÀM VIỆC BẮT BUỘC

### 0.1. Không được phá cấu trúc cũ nếu chưa kiểm tra
Trước khi code, bắt buộc đọc và đánh giá các phần đang có để **tận dụng**, không đập đi làm lại vô lý:
- `routes/web.php`
- các model liên quan khóa học, module, buổi học, tài nguyên, bài kiểm tra
- controller giảng viên hiện có, đặc biệt phần tài nguyên / bài giảng / phân công
- controller admin hiện có, đặc biệt phần khóa học / module / lịch học
- view quản lý tài nguyên hiện tại của giảng viên
- migration hiện có liên quan tới `tai_nguyen_buoi_hoc`, `lich_hoc`, `khoa_hoc`, `module_hoc`, `phan_cong`
- middleware phân quyền admin / giảng viên / học viên

### 0.2. Ưu tiên tận dụng tài nguyên hiện có
Nếu đã có thành phần phù hợp thì ưu tiên:
- **giữ lại và mở rộng**
- **đổi tên hợp lý** nếu cần
- **refactor nhỏ, an toàn**
- **không tạo trùng model/controller/view nếu có thể tái sử dụng**

### 0.3. Cách làm theo phase
Mỗi phase phải:
1. phân tích code cũ đang có
2. nêu rõ tận dụng gì
3. nêu rõ thêm mới gì
4. code xong phải tự kiểm tra route, migration, model relation, view link, validate
5. ghi ngắn phần đã làm xong

### 0.4. Quy tắc chất lượng
- Dùng Laravel style rõ ràng
- Đặt tên dễ hiểu, ưu tiên tiếng Việt không dấu theo convention đang có của repo nếu repo đang dùng kiểu đó
- Không hard-code bừa bãi
- Tách FormRequest nếu form phức tạp
- Tạo migration theo hướng **mở rộng CSDL hiện tại**, không phá dữ liệu cũ
- Không sửa phần không liên quan
- Nếu có chỗ chưa chắc, comment TODO rõ ràng

---

# 1. BỐI CẢNH NGHIỆP VỤ CẦN TRIỂN KHAI

## 1.1. Flow nghiệp vụ đã chốt
Hệ thống có 2 lớp:

### A. Kho nội dung
- Thư viện tài nguyên
- lưu video, PDF, Word, PowerPoint, ảnh, audio, file nén, link ngoài, tài liệu khác
- video phải được tải lên thư viện trước để xử lý trước
- tài nguyên do giảng viên đăng phải qua admin duyệt

### B. Triển khai đào tạo
- khóa học
- module
- buổi học
- bài giảng
- bài kiểm tra

Luồng chuẩn:
1. Admin tạo khóa học / module / buổi học
2. Admin phân công giảng viên
3. Admin hoặc giảng viên tải tài nguyên lên thư viện
4. Nếu giảng viên tải thì admin duyệt tài nguyên
5. Giảng viên/Admin tạo bài giảng gắn với buổi học
6. Bài giảng chọn tài nguyên từ thư viện
7. Nếu giảng viên tạo thì admin duyệt bài giảng
8. Chỉ bài giảng đã duyệt mới công bố cho học viên

## 1.2. Quy tắc quan trọng
- **Thư viện** là kho tài nguyên dùng chung
- **Bài giảng** là nội dung học nằm trong khóa học/module/buổi học
- Một bài giảng có:
  - 1 tài nguyên chính
  - nhiều tài nguyên phụ
- Học viên chỉ thấy:
  - bài giảng đã duyệt
  - tài nguyên đã duyệt
  - video ở trạng thái sẵn sàng
  - bài giảng đến đúng thời gian mở

---

# 2. ĐỊNH HƯỚNG KIẾN TRÚC CẦN ƯU TIÊN

## 2.1. Tận dụng phần đã có
Trước khi thêm mới, hãy tìm cách tận dụng các phần hiện đang có của repo, đặc biệt nếu hệ thống đã có:
- model kiểu `TaiNguyenBuoiHoc`
- CRUD tài nguyên của giảng viên
- trang quản lý tài nguyên trong chi tiết phân công/buổi học
- controller `BaiGiangController`
- controller `PhanCongController`
- route nhóm giảng viên/admin

## 2.2. Hướng refactor tối ưu
Nếu hiện tại tài nguyên đang gắn trực tiếp vào `lich_hoc_id`, hãy refactor theo hướng:

### Hướng mong muốn
- tạo thực thể **ThuVienTaiNguyen** hoặc refactor an toàn từ bảng tài nguyên cũ thành kho dùng chung
- tạo thực thể **BaiGiang** độc lập
- tạo bảng liên kết **bai_giang_tai_nguyen** để gắn nhiều tài nguyên vào một bài giảng

### Nhưng phải cân nhắc tận dụng code cũ
Nếu model `TaiNguyenBuoiHoc` đang dùng nhiều nơi, có thể chọn 1 trong 2 hướng:

#### Hướng A - Khuyên dùng nếu code cũ còn ít phụ thuộc
- tạo model/bảng mới `ThuVienTaiNguyen`
- giữ `TaiNguyenBuoiHoc` cũ để tương thích tạm thời
- dần chuyển logic sang thư viện mới

#### Hướng B - Khuyên dùng nếu muốn tận dụng mạnh
- refactor `TaiNguyenBuoiHoc` thành kho thư viện dùng chung bằng cách:
  - cho phép `lich_hoc_id` nullable
  - thêm cột phân loại, người tạo, trạng thái duyệt, trạng thái xử lý video, phạm vi sử dụng
  - dùng bảng pivot để bài giảng liên kết tới tài nguyên
- giữ tên class cũ nếu đổi quá nhiều sẽ ảnh hưởng rộng, nhưng nên chuẩn hóa tên hiển thị là “Thư viện tài nguyên”

Agent phải **đọc code hiện tại rồi chọn hướng ít phá vỡ nhất**, sau đó ghi rõ lý do chọn.

---

# 3. KẾT QUẢ ĐẦU RA MONG MUỐN

Sau tất cả các phase, hệ thống phải có:

## 3.1. Cho Admin
- menu Thư viện tài nguyên
- danh sách tài nguyên toàn hệ thống
- lọc theo loại, người tải, trạng thái duyệt, trạng thái xử lý
- duyệt / từ chối / yêu cầu chỉnh sửa tài nguyên giảng viên tải lên
- danh sách bài giảng chờ duyệt
- duyệt / từ chối / yêu cầu chỉnh sửa bài giảng
- công bố bài giảng

## 3.2. Cho Giảng viên
- tải tài nguyên lên thư viện
- chọn loại tài nguyên khi upload
- thấy trạng thái tài nguyên
- tạo bài giảng gắn buổi học
- chọn tài nguyên chính + tài nguyên phụ từ thư viện
- gửi admin duyệt
- xem phản hồi nếu bị từ chối / cần sửa

## 3.3. Cho Học viên
- vào khóa học
- xem danh sách bài giảng theo module / buổi học
- xem video / PDF / tài liệu đã được duyệt
- không thấy nội dung chưa duyệt hoặc chưa mở

---

# 4. CHIA PHASE TRIỂN KHAI

## PHASE 1 - KHẢO SÁT VÀ CHỐT HƯỚNG TẬN DỤNG CODE CŨ

### Mục tiêu
Hiểu rõ hệ thống hiện có để không code chồng chéo.

### Việc phải làm
1. Đọc toàn bộ phần liên quan:
   - route admin/giảng viên/học viên
   - model tài nguyên hiện có
   - controller tài nguyên / bài giảng / phân công
   - migration liên quan
   - view giảng viên quản lý tài nguyên
2. Liệt kê:
   - cái gì có thể giữ nguyên
   - cái gì phải refactor
   - cái gì phải thêm mới
3. Chốt chiến lược kỹ thuật:
   - dùng bảng mới hay refactor bảng cũ
   - có cần tạo `BaiGiang` mới hay tận dụng cấu trúc cũ

### Yêu cầu đầu ra phase 1
- báo cáo ngắn trong comment hoặc file note nội bộ:
  - “Tận dụng được gì”
  - “Phải sửa gì”
  - “Hướng chọn cuối cùng”

### Không được làm ở phase 1
- chưa sửa quá nhiều code nghiệp vụ nếu chưa chốt hướng

---

## PHASE 2 - THIẾT KẾ CSDL MỞ RỘNG AN TOÀN

### Mục tiêu
Tạo nền dữ liệu đúng flow mới nhưng vẫn tương thích code cũ tối đa.

### Bắt buộc có các nhóm dữ liệu sau

#### A. Tài nguyên thư viện
Các trường tối thiểu:
- id
- tieu_de
- mo_ta
- loai_tai_nguyen
  - video
  - pdf
  - word
  - powerpoint
  - excel
  - image
  - audio
  - archive
  - link_ngoai
  - tai_lieu_khac
- file_path hoặc link_ngoai
- file_name
- file_extension
- file_size
- mime_type
- nguoi_tao_id
- vai_tro_nguoi_tao
- trang_thai_duyet
  - nhap
  - cho_duyet
  - da_duyet
  - can_chinh_sua
  - tu_choi
- trang_thai_xu_ly
  - khong_ap_dung
  - cho_xu_ly
  - dang_xu_ly
  - san_sang
  - loi_xu_ly
- ghi_chu_admin
- ngay_gui_duyet
- ngay_duyet
- nguoi_duyet_id
- is_public_library hoặc pham_vi_su_dung
- timestamps
- softDeletes nếu repo đang dùng kiểu này

#### B. Bài giảng
Các trường tối thiểu:
- id
- khoa_hoc_id
- module_hoc_id
- lich_hoc_id
- nguoi_tao_id
- tieu_de
- mo_ta
- loai_bai_giang
  - video
  - tai_lieu
  - bai_doc
  - bai_tap
  - hon_hop
- tai_nguyen_chinh_id (nullable)
- thu_tu_hien_thi
- thoi_diem_mo
- trang_thai_duyet
  - nhap
  - cho_duyet
  - da_duyet
  - can_chinh_sua
  - tu_choi
- trang_thai_cong_bo
  - an
  - da_cong_bo
- ghi_chu_admin
- ngay_gui_duyet
- ngay_duyet
- nguoi_duyet_id
- timestamps
- softDeletes nếu phù hợp

#### C. Liên kết bài giảng - tài nguyên phụ
Bảng pivot ví dụ `bai_giang_tai_nguyen`
- id
- bai_giang_id
- tai_nguyen_id
- vai_tro_tai_nguyen
  - chinh
  - phu
- thu_tu_hien_thi
- timestamps

### Việc phải làm
1. Tạo migration mới, **không sửa migration cũ đã chạy**, trừ khi repo đang ở giai đoạn dev và strategy cho phép.
2. Tạo/điều chỉnh model relation đầy đủ.
3. Nếu refactor từ `TaiNguyenBuoiHoc`, tạo migration thêm cột cần thiết thay vì đập bảng.

### Tự kiểm tra phase 2
- migrate chạy được
- foreign key hợp lý
- relation Eloquent chạy được
- không làm hỏng dữ liệu cũ

---

## PHASE 3 - MODEL, ENUM/CONSTANT, ACCESSOR, SCOPE

### Mục tiêu
Chuẩn hóa tầng model để controller/view dùng sạch hơn.

### Việc phải làm
1. Hoàn thiện model tài nguyên thư viện:
   - fillable
   - casts
   - relation tới người tạo, người duyệt
   - accessor URL file
   - helper kiểm tra loại video/pdf/link
   - scope theo trạng thái duyệt
   - scope theo trạng thái xử lý
   - scope tài nguyên dùng được cho bài giảng
2. Hoàn thiện model bài giảng:
   - relation khóa học/module/buổi học
   - relation người tạo/người duyệt
   - relation tài nguyên chính
   - relation tài nguyên phụ qua pivot
   - helper kiểm tra có thể công bố hay không
3. Nếu repo chưa có enum thật, dùng hằng số trong model hoặc class constant để tránh magic string rải khắp nơi.

### Tự kiểm tra phase 3
- relation eager-load ổn
- không lặp logic trạng thái trong controller quá nhiều

---

## PHASE 4 - ROUTE VÀ MENU HỆ THỐNG

### Mục tiêu
Thêm route rõ ràng, không lẫn với phần cũ.

### Việc phải làm
1. Đọc route hiện có và giữ style nhóm route của repo.
2. Thêm route cho admin:
   - thư viện tài nguyên
   - duyệt tài nguyên
   - duyệt bài giảng
   - công bố bài giảng
3. Thêm route cho giảng viên:
   - danh sách thư viện của tôi
   - tạo/sửa/gửi duyệt tài nguyên
   - tạo/sửa/gửi duyệt bài giảng
4. Thêm route cho học viên:
   - xem bài giảng trong chi tiết khóa học
   - xem bài giảng chi tiết
5. Nếu cần, tách route thành nhóm theo module nhưng **không phá file route hiện tại** nếu repo đang dùng 1 file.

### Gợi ý tên route
- `admin.thu-vien.index`
- `admin.thu-vien.duyet`
- `admin.bai-giang.cho-duyet`
- `giang-vien.thu-vien.index`
- `giang-vien.thu-vien.store`
- `giang-vien.bai-giang.index`
- `giang-vien.bai-giang.gui-duyet`
- `hoc-vien.bai-giang.show`

### Tự kiểm tra phase 4
- `php artisan route:list` không lỗi
- middleware đúng vai trò
- tên route không trùng route cũ

---

## PHASE 5 - CHỨC NĂNG THƯ VIỆN CHO GIẢNG VIÊN VÀ ADMIN

### Mục tiêu
Có phân hệ thư viện riêng, dùng được thật.

### Việc phải làm

#### A. Trang danh sách thư viện
Hiển thị:
- tiêu đề
- loại tài nguyên
- người tạo
- trạng thái duyệt
- trạng thái xử lý
- ngày tạo
- nút xem/sửa/gửi duyệt

#### B. Form thêm/sửa tài nguyên
Có các trường:
- tiêu đề
- mô tả
- loại tài nguyên
- upload file hoặc link
- tag nếu phù hợp

#### C. Logic upload
- validate theo loại file
- lưu file thống nhất một kiểu
- ưu tiên 1 chuẩn lưu file duy nhất, không nửa storage nửa public lộn xộn
- viết helper URL file thống nhất

#### D. Logic duyệt tài nguyên
- admin tạo -> có thể auto duyệt
- giảng viên tạo -> `cho_duyet`
- admin duyệt -> `da_duyet`
- admin từ chối -> `tu_choi`
- admin yêu cầu sửa -> `can_chinh_sua`

#### E. Video processing
Nếu chưa làm queue/transcoding thật, vẫn phải thiết kế trước trạng thái:
- video upload xong có thể gán `san_sang` tạm
- nhưng code phải chừa chỗ cho xử lý sau
- comment TODO rõ ràng nếu chưa có hạ tầng xử lý video thật

### Tận dụng code cũ bắt buộc xem xét
- nếu đã có form upload tài nguyên giảng viên, tái sử dụng layout, component, style, validation
- nếu đã có preview/download/hide-show thì tận dụng theo logic mới

### Tự kiểm tra phase 5
- upload được video/pdf/link
- lưu được trạng thái
- admin duyệt được
- giảng viên thấy phản hồi duyệt

---

## PHASE 6 - CHỨC NĂNG BÀI GIẢNG LIÊN KẾT KHÓA HỌC

### Mục tiêu
Tạo bài giảng đúng flow khóa học -> module -> buổi học.

### Việc phải làm

#### A. Trang danh sách bài giảng
Theo ngữ cảnh:
- trong chi tiết khóa học/module/buổi học
- hoặc trang tổng hợp bài giảng của giảng viên

#### B. Form tạo/sửa bài giảng
Có các trường:
- tiêu đề
- mô tả
- loại bài giảng
- khóa học/module/buổi học
- tài nguyên chính từ thư viện
- nhiều tài nguyên phụ từ thư viện
- thứ tự hiển thị
- thời điểm mở

#### C. Chọn tài nguyên từ thư viện
Chỉ hiển thị:
- tài nguyên đã duyệt
- của chính người tạo hoặc tài nguyên được phép dùng
- video phải ở trạng thái `san_sang`

#### D. Logic duyệt bài giảng
- admin tạo -> có thể tự duyệt hoặc lưu nháp
- giảng viên tạo -> `cho_duyet`
- admin duyệt -> `da_duyet`
- công bố tách riêng khỏi duyệt

#### E. Logic công bố
Điều kiện để công bố:
- bài giảng đã duyệt
- tài nguyên chính nếu có phải đã duyệt
- video chính nếu có phải `san_sang`

### Tận dụng code cũ bắt buộc xem xét
- nếu `BaiGiangController` hiện có đang là danh sách module được phân công, hãy mở rộng thay vì bỏ
- nếu view chi tiết phân công đang là nơi giảng viên thao tác bài giảng/tài nguyên, có thể giữ nó làm màn quản trị chính rồi refactor sạch lại

### Tự kiểm tra phase 6
- tạo được bài giảng
- gắn được tài nguyên chính và phụ
- route chi tiết xem ổn
- không bị N+1 nghiêm trọng ở danh sách

---

## PHASE 7 - MÀN HÌNH DUYỆT CHO ADMIN

### Mục tiêu
Admin có dashboard duyệt nội dung rõ ràng.

### Việc phải làm
1. Trang duyệt tài nguyên:
   - danh sách chờ duyệt
   - lọc theo loại / giảng viên / ngày tạo
   - modal hoặc form duyệt / từ chối / yêu cầu sửa
2. Trang duyệt bài giảng:
   - xem thông tin bài giảng
   - xem tài nguyên liên kết
   - xem vị trí khóa học/module/buổi học
   - duyệt / từ chối / yêu cầu sửa
3. Ghi lại ghi chú admin để giảng viên nhìn thấy.

### Tự kiểm tra phase 7
- admin thao tác duyệt được
- giảng viên thấy trạng thái cập nhật ngay trên danh sách của họ

---

## PHASE 8 - HIỂN THỊ CHO HỌC VIÊN

### Mục tiêu
Học viên xem bài giảng đã công bố trong khóa học.

### Việc phải làm
1. Trong chi tiết khóa học của học viên, load:
   - module
   - buổi học
   - danh sách bài giảng đã công bố
2. Chỉ hiển thị nội dung thỏa điều kiện:
   - bài giảng `da_duyet`
   - `da_cong_bo`
   - đến thời điểm mở
   - tài nguyên liên quan hợp lệ
3. View chi tiết bài giảng:
   - nếu video: phát video
   - nếu pdf: preview/tải
   - nếu link ngoài: mở link
   - nếu hỗn hợp: hiển thị tài nguyên chính + danh sách tài nguyên phụ

### Tận dụng code cũ bắt buộc xem xét
- nếu học viên hiện đã có trang chi tiết khóa học load `lichHoc -> taiNguyen`, refactor sang `lichHoc -> baiGiang` nhưng giữ giao diện đang quen dùng nếu hợp lý

### Tự kiểm tra phase 8
- học viên không thấy nội dung chưa duyệt
- không lộ route admin/giảng viên
- không lỗi quyền truy cập trực tiếp bằng URL

---

## PHASE 9 - REFACTOR, DỌN RÁC, TƯƠNG THÍCH NGƯỢC

### Mục tiêu
Kết thúc sạch, tránh để 2 hệ logic chồng nhau.

### Việc phải làm
1. Tìm và sửa chỗ còn dùng logic tài nguyên cũ trực tiếp theo buổi học nếu đã chuyển sang bài giảng mới.
2. Chuẩn hóa:
   - accessor file URL
   - tên biến
   - trạng thái
   - message flash
3. Xóa code dead nếu chắc chắn không còn dùng.
4. Nếu chưa thể xóa vì sợ ảnh hưởng, comment rõ phần legacy.
5. Kiểm tra các view cũ còn nút route chết hay không.

### Tự kiểm tra phase 9
- route không chết
- view không gọi field đã bị đổi tên
- model relation không chồng chéo khó hiểu

---

## PHASE 10 - TEST THỦ CÔNG TỐI THIỂU

### Mục tiêu
Đảm bảo tính năng chạy được trước khi dừng.

### Checklist phải tự test

#### Giảng viên
- upload video
- upload pdf
- gửi duyệt tài nguyên
- tạo bài giảng
- gắn tài nguyên chính và phụ
- gửi admin duyệt

#### Admin
- duyệt tài nguyên
- từ chối tài nguyên
- yêu cầu sửa tài nguyên
- duyệt bài giảng
- công bố bài giảng

#### Học viên
- vào khóa học
- thấy bài giảng đã công bố
- xem được video/pdf
- không thấy bài giảng chưa duyệt

#### Kỹ thuật
- migration chạy được
- route:list không lỗi
- không có lỗi 500 ở các trang chính
- file upload có URL đúng

---

# 5. YÊU CẦU VỀ CÁCH CODE

## 5.1. Controller
- Controller ngắn gọn, không nhồi quá nhiều logic
- Dùng service nhỏ nếu cần cho duyệt / upload / công bố
- Nếu repo hiện chưa dùng service layer, có thể giữ logic trong controller vừa phải nhưng tách private method hợp lý

## 5.2. Validation
- Tạo FormRequest cho:
  - tạo/sửa tài nguyên
  - tạo/sửa bài giảng
  - duyệt tài nguyên
  - duyệt bài giảng nếu cần

## 5.3. View
- Ưu tiên tái sử dụng Blade layout và component cũ
- Giao diện admin/giảng viên giữ cùng style hệ thống
- Form cần hiển thị lỗi validate rõ ràng
- Danh sách cần có badge trạng thái dễ nhìn

## 5.4. Bảo mật
- kiểm tra quyền ở controller/policy nếu repo có
- giảng viên không được sửa nội dung của giảng viên khác nếu không có quyền
- học viên không được truy cập tài nguyên chưa công bố bằng URL trực tiếp

---

# 6. CÁC VẤN ĐỀ CẦN GIẢI QUYẾT TRIỆT ĐỂ

Agent phải chú ý giải quyết dứt điểm các vấn đề sau nếu phát hiện trong code cũ:

1. **Tài nguyên đang bị gắn cứng vào buổi học**
   - phải chuyển thành tư duy thư viện dùng chung

2. **Bài giảng chưa là thực thể độc lập**
   - phải tạo hoặc hoàn thiện để bài giảng là nội dung học thật

3. **UI có nhưng backend chưa xử lý thật**
   - ví dụ option upload/chuyển định dạng nếu có mà backend chưa làm
   - phải bỏ hoặc làm thật, không để giao diện giả

4. **URL file và cách lưu file không thống nhất**
   - phải chuẩn hóa một kiểu truy xuất

5. **Thiếu cơ chế duyệt admin**
   - phải có trạng thái, ghi chú, thời gian duyệt, người duyệt

6. **Học viên đang xem tài nguyên trực tiếp theo buổi học**
   - phải chuyển sang xem bài giảng đã công bố

7. **Trùng vai trò giữa “loại tài nguyên” và “loại bài giảng”**
   - phải tách khái niệm rõ ràng

---

# 7. CÁCH BÁO CÁO SAU MỖI PHASE

Sau mỗi phase, Gemini agent phải trả lời ngắn gọn theo mẫu:

- Đã tận dụng những gì từ code cũ
- Đã thêm mới những file nào
- Đã sửa những file nào
- Kết quả đạt được
- Còn rủi ro gì hoặc TODO gì

Không được trả lời mơ hồ kiểu “đã hoàn thành”. Phải nêu rõ file và logic.

---

# 8. ƯU TIÊN TRIỂN KHAI

Thứ tự ưu tiên bắt buộc:
1. Khảo sát code cũ
2. CSDL + model
3. Thư viện tài nguyên
4. Bài giảng
5. Duyệt admin
6. Học viên xem bài giảng
7. Refactor sạch
8. Test tối thiểu

Không được làm view quá nhiều trước khi chốt model và route.

---

# 9. CHỐT YÊU CẦU CUỐI

Hãy triển khai theo đúng phase ở trên, bám sát flow nghiệp vụ đã chốt, và **ưu tiên tận dụng tài nguyên đang có trong repo** thay vì tạo một hệ hoàn toàn mới. Mọi thay đổi phải hướng đến:
- ít phá code cũ nhất
- rõ nghiệp vụ hơn
- dễ demo đồ án hơn
- dễ mở rộng về sau

Nếu trong quá trình đọc repo phát hiện một phần hiện tại có thể tái sử dụng tốt hơn so với kế hoạch trên, được phép điều chỉnh kỹ thuật, nhưng phải giữ nguyên mục tiêu nghiệp vụ cuối cùng.
