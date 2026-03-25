Bạn là senior Laravel architect + refactor engineer.

Tôi đang làm đồ án web học tập và kiểm tra online bằng Laravel.
Repo của tôi:
https://github.com/vinhdth225793-crypto/thuctap_khaitri.git

Nhiệm vụ của bạn là đọc TOÀN BỘ repo, đặc biệt:
- database/migrations
- app/Models
- app/Http/Controllers
- routes/web.php
- resources/views
- các logic liên quan tới:
  - người dùng / phân quyền
  - khóa học / module / buổi học
  - thư viện tài nguyên
  - bài giảng
  - bài kiểm tra
  - học viên khóa học
  - phân công giảng viên

MỤC TIÊU CHÍNH:
1. Kiểm tra các bảng hoặc chức năng có dấu hiệu trùng nhau, chồng chéo nhau, hoặc làm cùng một việc nhưng khác tên.
2. Kiểm tra các migrations có bị sửa đi sửa lại nhiều lần, logic thiếu nhất quán, hoặc gây khó bảo trì.
3. Đề xuất cách chuẩn hóa schema tốt nhất nhưng phải ưu tiên:
   - tận dụng tối đa code cũ
   - không phá vỡ flow hiện tại nếu chưa cần
   - tránh xóa dữ liệu đang có
4. Sau khi phân tích xong, thực hiện refactor theo PHASE, mỗi phase làm dứt điểm một nhóm vấn đề.

YÊU CẦU RẤT QUAN TRỌNG:
- Không code ngay lập tức.
- Bước đầu tiên phải AUDIT toàn bộ repo.
- Phải đọc kỹ các migration hiện có, model hiện có, controller hiện có, route hiện có.
- Phải chỉ ra rõ:
  - bảng nào trùng ý nghĩa
  - bảng nào gần giống nhau
  - bảng nào đang gánh nhiều trách nhiệm
  - model nào có nhưng migration chưa đầy đủ
  - migration nào bổ sung hợp lý
  - migration nào bị chồng chéo
  - cột nào nên giữ
  - cột nào nên bỏ
  - khóa ngoại nào chưa thống nhất
- Chỉ được refactor theo hướng ít phá hệ thống nhất.
- Nếu có 2 cách, hãy chọn cách ít rủi ro hơn cho đồ án.

==================================================
PHẦN 1 - CÁCH LÀM VIỆC BẮT BUỘC
==================================================

Hãy làm theo đúng thứ tự sau:

BƯỚC A - AUDIT TOÀN BỘ REPO
1. Đọc toàn bộ cấu trúc project.
2. Liệt kê:
   - models chính
   - migrations chính
   - các bảng nghiệp vụ chính
   - các route chính
   - các controller chính
3. Tạo bảng phân tích theo format:

| Nhóm chức năng | Thành phần hiện có | Bị trùng/chồng chéo với | Mức độ vấn đề | Đề xuất xử lý |

4. Tạo thêm bảng audit CSDL theo format:

| Tên bảng | Mục đích hiện tại | Có đang dùng thật không | Trùng/gần giống bảng nào | Nên giữ/gộp/bỏ | Ghi chú |

5. Tạo thêm bảng audit migration theo format:

| File migration | Mục đích | Hợp lý / Chồng chéo / Nghi ngờ | Ảnh hưởng | Đề xuất |

BƯỚC B - CHỐT KIẾN TRÚC SAU KHI CHUẨN HÓA
Sau khi audit xong, hãy đề xuất:
1. Schema mục tiêu cuối cùng
2. Bảng nào là bảng chính
3. Bảng nào là bảng phụ
4. Quan hệ giữa các bảng
5. Những gì giữ nguyên
6. Những gì phải sửa

BƯỚC C - CHIA PHASE THỰC HIỆN
Chia công việc thành các phase nhỏ.
Mỗi phase phải có:
- mục tiêu
- file cần sửa
- migration cần thêm hoặc chỉnh
- model cần sửa
- controller cần sửa
- route cần sửa
- view cần sửa
- rủi ro có thể xảy ra
- cách test sau khi làm xong

==================================================
PHẦN 2 - NHỮNG ĐIỂM NGHI NGỜ CẦN KIỂM TRA KỸ
==================================================

Bạn phải đặc biệt kiểm tra kỹ các điểm sau trong repo:

1. USER / AUTH
- Có đang tồn tại song song:
  - users
  - nguoi_dungs
  - User model
  - NguoiDung model
- Kiểm tra xem hệ thống hiện tại đang auth theo bảng nào.
- Kiểm tra cái nào là dư thừa.
- Đề xuất hướng tốt nhất:
  - giữ nguoi_dungs làm bảng auth chính
  - hay chuyển về users
- Nhưng phải ưu tiên phương án ít phá hệ thống nhất.

2. NHÓM NGÀNH / MÔN HỌC
- Kiểm tra quá trình đổi từ mon_hoc sang nhom_nganh.
- Kiểm tra code còn chỗ nào gọi mon_hoc, mon_hoc_id hay không.
- Nếu còn sót phải note rõ.

3. KHÓA HỌC
- Kiểm tra các migration của khoa_hoc có bị chồng chéo không.
- Đặc biệt kiểm tra các migration kiểu:
  - thêm cột rồi sau đó drop và tạo lại cột tương tự
- Đề xuất cách làm cho schema sạch hơn.

4. PHÂN CÔNG GIẢNG VIÊN
- Kiểm tra bảng phan_cong_module_giang_vien
- Kiểm tra các migration fix default trạng thái có trùng lặp không.
- Đề xuất schema cuối cùng rõ ràng.

5. HỌC VIÊN / GIẢNG VIÊN / NGƯỜI DÙNG
- Kiểm tra khóa ngoại hiện đang dùng:
  - nguoi_dung.ma_nguoi_dung
  - giang_vien.id
  - hoc_vien.id
- Kiểm tra sự thiếu nhất quán giữa các bảng nghiệp vụ.
- Đề xuất chuẩn FK thống nhất nhất có thể.

6. TÀI NGUYÊN / THƯ VIỆN / BÀI GIẢNG
- Kiểm tra:
  - TaiNguyenBuoiHoc
  - BaiGiang
  - các controller giảng viên
  - các views bài giảng / tài liệu
- Xác định rõ:
  - hiện hệ thống đang gắn tài nguyên trực tiếp vào buổi học
  - hay đã có bài giảng độc lập
- Kiểm tra có model BaiGiang nhưng migration có đủ chưa.
- Kiểm tra pivot hoặc bảng liên kết bài giảng - tài nguyên có chưa.
- Nếu thiếu migration nhưng model đã có thì phải note rõ.
- Đề xuất chuẩn hóa theo flow mục tiêu:
  - Thư viện tài nguyên là kho dùng chung
  - Bài giảng là nội dung nằm trong khóa học/module/buổi học
  - Một bài giảng có 1 tài nguyên chính + nhiều tài nguyên phụ
  - Giảng viên tạo nhưng admin phải duyệt

7. BÀI KIỂM TRA
- Kiểm tra các migrations bài_kiem_tra, bai_lam_bai_kiem_tra
- Xem có bị trùng hay chỉ là mở rộng hợp lý
- Đánh giá mức ổn định

8. YÊU CẦU HỌC VIÊN
- Kiểm tra bảng yeu_cau_hoc_vien
- Xem có đang gánh quá nhiều vai trò không
- Đề xuất giữ nguyên hay tách nhỏ

==================================================
PHẦN 3 - KIẾN TRÚC MỤC TIÊU MONG MUỐN
==================================================

Flow nghiệp vụ mục tiêu của tôi như sau:

A. Cấu trúc đào tạo
- Nhóm ngành
- Khóa học
- Module học
- Buổi học / lịch học

B. Thư viện tài nguyên
- dùng cho admin và giảng viên
- lưu:
  - video
  - pdf
  - word
  - powerpoint
  - image
  - audio
  - link ngoài
- video phải có trạng thái xử lý
- tài nguyên do giảng viên tạo phải qua admin duyệt

C. Bài giảng
- nằm trong khóa học / module / buổi học
- có:
  - tiêu đề
  - mô tả
  - loại bài giảng
  - tài nguyên chính
  - nhiều tài nguyên phụ
  - trạng thái duyệt
  - trạng thái công bố
- bài giảng do giảng viên tạo phải qua admin duyệt

D. Học viên
- chỉ thấy bài giảng đã duyệt, đã công bố, đúng lịch mở

E. Bài kiểm tra
- gắn theo buổi học/module nếu cần

Bạn phải refactor để hệ thống tiến gần flow này nhất, nhưng không làm hỏng code cũ đang có.

==================================================
PHẦN 4 - NGUYÊN TẮC REFACTOR BẮT BUỘC
==================================================

1. KHÔNG được tự ý xóa bảng quan trọng đang dùng thật nếu chưa có phương án migrate dữ liệu.
2. KHÔNG được đổi tên lung tung gây vỡ code hàng loạt nếu có cách trung gian an toàn hơn.
3. Nếu cần, hãy:
   - thêm migration mới để chuẩn hóa dần
   - sửa model để tương thích ngược
   - giữ API cũ hoạt động tạm thời
4. Ưu tiên:
   - thêm migration chuẩn hóa
   - gộp logic ở model/controller
   - bỏ dần phần cũ
5. Mỗi phase phải code xong là chạy được.
6. Mỗi phase phải có hướng dẫn test.
7. Chỉ sau khi phân tích xong mới được code.

==================================================
PHẦN 5 - CHIA PHASE CỤ THỂ PHẢI LÀM
==================================================

PHASE 0 - AUDIT & CHỐT HƯỚNG
Mục tiêu:
- đọc toàn bộ repo
- chỉ ra các phần trùng/chồng chéo
- đưa ra kiến trúc chốt cuối cùng
Output bắt buộc:
- báo cáo audit
- bảng các bảng trùng/chức năng tương tự
- sơ đồ quan hệ đề xuất
- danh sách file cần sửa theo phase

PHASE 1 - CHUẨN HÓA AUTH VÀ ĐỊNH DANH
Mục tiêu:
- xác định bảng auth chính
- thống nhất hướng dùng User hay NguoiDung
- thống nhất quan hệ FK cơ bản
Cần làm:
- kiểm tra toàn bộ auth/middleware/model
- note rõ bảng nào để lại, bảng nào bỏ vai trò
- nếu chưa thể bỏ thì phải ghi rõ “deprecated”
Output:
- đề xuất chuẩn auth
- code chỉnh nhẹ để hệ thống ổn định hơn

PHASE 2 - CHUẨN HÓA CẤU TRÚC CSDL NGHIỆP VỤ CHÍNH
Mục tiêu:
- làm sạch các bảng:
  - nhom_nganh
  - khoa_hoc
  - module_hoc
  - lich_hoc
  - phan_cong_module_giang_vien
  - hoc_vien_khoa_hoc
Cần làm:
- rà migration chồng chéo
- đề xuất migration chuẩn hóa mới nếu cần
- giữ tương thích dữ liệu cũ

PHASE 3 - CHUẨN HÓA THƯ VIỆN TÀI NGUYÊN
Mục tiêu:
- biến TaiNguyenBuoiHoc thành thư viện tài nguyên dùng tốt hơn hoặc refactor tên/logic nếu cần
- tách rõ:
  - tài nguyên thư viện
  - tài nguyên gắn cho bài giảng
Cần làm:
- kiểm tra model hiện tại
- kiểm tra migration hiện tại
- bổ sung field còn thiếu:
  - trang_thai_duyet
  - trang_thai_xu_ly_video
  - loai_tai_nguyen chuẩn
  - pham_vi_su_dung
  - created_by
  - approved_by
- tận dụng tối đa bảng cũ nếu có thể
Output:
- migration mới
- model chuẩn hóa
- controller/service nếu cần

PHASE 4 - CHUẨN HÓA BÀI GIẢNG
Mục tiêu:
- xác định BaiGiang là thực thể chính
- nếu model đã có mà migration chưa có thì phải tạo migration đầy đủ
- hỗ trợ:
  - tài nguyên chính
  - nhiều tài nguyên phụ
  - duyệt admin
  - công bố bài giảng
Cần làm:
- tạo bảng bai_giang nếu thiếu
- tạo bảng pivot nếu thiếu
- sửa model/quan hệ
- sửa controller
- sửa route
- sửa view
Output:
- bài giảng chạy độc lập, gắn đúng khóa học/module/buổi học

PHASE 5 - DUYỆT ADMIN CHO TÀI NGUYÊN VÀ BÀI GIẢNG
Mục tiêu:
- giảng viên tạo nội dung nhưng admin phải duyệt
Cần làm:
- trạng thái:
  - nhap
  - cho_duyet
  - can_chinh_sua
  - tu_choi
  - da_duyet
  - da_cong_bo
- giao diện admin duyệt
- ghi chú lý do duyệt/từ chối
Output:
- flow duyệt hoàn chỉnh

PHASE 6 - CHUẨN HÓA PHÍA HỌC VIÊN
Mục tiêu:
- học viên chỉ thấy nội dung hợp lệ
Cần làm:
- filter bài giảng đã duyệt, đã công bố
- kiểm tra lịch mở
- kiểm tra video đã sẵn sàng
Output:
- trang học viên hiển thị đúng nghiệp vụ

PHASE 7 - DỌN DẸP & TỐI ƯU
Mục tiêu:
- bỏ dần logic cũ dư thừa
- note các phần deprecated
- cập nhật README kỹ thuật
- tạo checklist test
Output:
- code sạch hơn
- migration rõ hơn
- tài liệu kỹ thuật rõ hơn

==================================================
PHẦN 6 - CÁCH OUTPUT MONG MUỐN
==================================================

Mỗi phase, bạn phải trả lời theo format:

1. Tóm tắt phát hiện
2. Vấn đề đang có
3. Giải pháp chọn
4. Vì sao chọn cách này
5. File sẽ sửa
6. Migration sẽ thêm/sửa
7. Code đầy đủ
8. Cách chạy migrate
9. Cách test
10. Rủi ro còn lại

==================================================
PHẦN 7 - QUY TẮC CODE
==================================================

- Laravel style rõ ràng
- không hardcode bừa
- dùng relationship Eloquent chuẩn
- migration phải có up/down rõ
- không viết code phá dữ liệu cũ nếu chưa cần
- tận dụng code hiện có trước
- nếu controller cũ dùng được thì refactor, không viết lại toàn bộ vô lý
- nếu view cũ tận dụng được thì sửa trên nền cũ
- nếu cần tạo service/helper thì giải thích rõ lý do
- nếu có tên bảng/cột tiếng Việt đang dùng rồi thì cố gắng thống nhất theo cái đang có, tránh đổi quá mạnh
- mọi đề xuất phải bám vào repo thật, không được trả lời chung chung

==================================================
PHẦN 8 - VIỆC CẦN LÀM NGAY
==================================================

Bây giờ hãy bắt đầu với PHASE 0:
- đọc repo
- audit toàn bộ
- liệt kê tất cả phần trùng/chồng chéo/chức năng tương tự
- đề xuất schema mục tiêu
- chia phase thật chi tiết cho repo này

CHƯA CODE NGAY nếu chưa xong PHASE 0.
Sau khi xong PHASE 0, mới bắt đầu PHASE 1.