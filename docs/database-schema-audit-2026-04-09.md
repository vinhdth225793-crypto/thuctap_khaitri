Bạn là một senior Laravel architect + database designer + refactoring engineer.

Tôi có một project Laravel hiện tại mà phần migrations đang bị bừa bãi, trùng lặp, thiếu chuẩn hóa naming convention, khóa ngoại chưa đồng nhất, và có khả năng mô hình dữ liệu chưa tối ưu theo domain nghiệp vụ.

MỤC TIÊU CỦA BẠN:
Không sửa lẻ từng migration cũ.
Thay vào đó, hãy phân tích toàn bộ project để:
1. đọc và hiểu các chức năng hiện có của hệ thống,
2. đối chiếu các migrations cũ với chức năng thực tế,
3. phát hiện bảng trùng, bảng dư, bảng đặt tên không chuẩn, quan hệ sai hoặc khó hiểu,
4. đề xuất một schema mới đã chuẩn hóa,
5. viết lại toàn bộ bộ migrations mới từ đầu theo schema chuẩn,
6. chuẩn bị kế hoạch migrate dữ liệu từ schema cũ sang schema mới,
7. đảm bảo bộ schema mới phản ánh đúng nghiệp vụ hiện có của hệ thống.

YÊU CẦU RẤT QUAN TRỌNG:
- Không được bắt đầu bằng việc viết migration mới ngay.
- Phải đi theo flow phân tích -> chuẩn hóa -> thiết kế -> mapping -> triển khai.
- Không được giữ tư duy “vá chỗ nào lỗi thì sửa chỗ đó”.
- Phải xem toàn bộ schema như một hệ thống thống nhất.
- Phải ưu tiên tính dễ bảo trì, dễ mở rộng, rõ nghĩa cho developer Laravel.
- Ưu tiên chuẩn Laravel:
  - primary key mặc định là `id`
  - foreign key theo dạng `*_id`
  - quan hệ Eloquent dễ khai báo
  - tên bảng, tên cột rõ nghĩa, nhất quán
- Chỉ giữ lại cấu trúc cũ nếu nó thực sự hợp lý.
- Nếu phát hiện chỗ sai hoặc không nhất quán, mạnh dạn đề xuất thiết kế lại.

BỐI CẢNH DỰ ÁN:
- Đây là project Laravel.
- Tôi muốn chuẩn hóa toàn bộ migration.
- Tôi sẵn sàng xóa toàn bộ migration cũ để viết lại bộ migration mới sạch hơn.
- Tuy nhiên, trước khi viết lại, bạn phải phân tích các migration cũ, đối chiếu với model/controller/service/view/routes nếu có, để hiểu chức năng hệ thống.
- Mục tiêu là “chuẩn hóa migration theo chức năng”, có thể gom các dữ liệu cùng domain vào cấu trúc hợp lý hơn, nhưng không được gom bừa nếu khác nghiệp vụ.

PHẠM VI PHÂN TÍCH:
Hãy đọc và phân tích ít nhất các phần sau:
1. thư mục `database/migrations`
2. Models
3. Controllers
4. Requests / Services / Repositories nếu có
5. Routes
6. Views / Blade / API resources nếu cần để hiểu dữ liệu được dùng thế nào
7. Seeders / Factories nếu có
8. Bất kỳ enum / constant / config nào phản ánh business rules

NHIỆM VỤ CỤ THỂ CỦA BẠN:

==================================================
PHẦN 1 - KIỂM KÊ HỆ THỐNG
==================================================
Hãy lập một báo cáo kiểm kê toàn hệ thống.

1. Liệt kê tất cả chức năng nghiệp vụ hiện có của project.
   Ví dụ:
   - quản lý người dùng
   - học viên
   - giảng viên
   - khóa học
   - module học
   - lịch học
   - điểm danh
   - bài giảng / tài nguyên
   - bài kiểm tra
   - câu hỏi / đáp án
   - bài làm
   - kết quả học tập
   - thông báo
   - chờ phê duyệt tài khoản
   - v.v.

2. Với mỗi chức năng, hãy chỉ ra:
   - bảng nào đang liên quan
   - model nào đang liên quan
   - controller/action nào đang dùng
   - quan hệ dữ liệu hiện tại ra sao
   - chỗ nào logic nghiệp vụ đang phụ thuộc vào schema

3. Xuất kết quả dưới dạng bảng hoặc markdown rõ ràng.

==================================================
PHẦN 2 - PHÂN TÍCH MIGRATION CŨ
==================================================
Hãy đọc toàn bộ migration cũ và phân tích chi tiết.

Với mỗi migration hoặc mỗi bảng, hãy ghi rõ:
- tên bảng
- mục đích nghiệp vụ
- khóa chính
- khóa ngoại
- unique/index
- enum/trạng thái
- cột nào đáng chú ý
- bảng này đang được dùng thật hay có vẻ thừa
- có trùng ý nghĩa với bảng khác không
- có chỗ nào naming không chuẩn hoặc gây hiểu nhầm không
- có chỗ nào sai design relation không

Đặc biệt hãy kiểm tra:
- cột nào tên là `hoc_vien_id`, `giang_vien_id`, `nguoi_dung_id`, nhưng thực chất FK trỏ sang bảng khác với tên gọi của nó
- bảng profile và bảng user có bị chồng chéo không
- có bảng pivot nào đặt tên chưa chuẩn không
- có bảng nào chỉ khác tên nhưng cùng bản chất không
- có bảng nào nên merge / nên tách lại không
- có enum/string nào chưa đồng nhất không
- có bảng nào đang denormalized quá sớm không
- có migration nào chỉ là bản vá tạm thời / trùng / dư thừa không

==================================================
PHẦN 3 - PHÁT HIỆN VẤN ĐỀ THIẾT KẾ
==================================================
Sau khi phân tích, hãy tạo danh sách các vấn đề hiện tại.

Phân nhóm vấn đề thành:
1. Naming convention problems
2. Key / foreign key inconsistency
3. Duplicate tables / overlapping responsibilities
4. Bad normalization / over-normalization
5. Enum / status inconsistency
6. Laravel convention violations
7. Potential maintenance risks
8. Potential migration/reset risks
9. Query / indexing issues
10. Business modeling issues

Với mỗi vấn đề, hãy mô tả:
- hiện trạng
- tại sao nó là vấn đề
- hậu quả kỹ thuật
- cách sửa tốt nhất

==================================================
PHẦN 4 - THIẾT KẾ SCHEMA MỚI ĐÃ CHUẨN HÓA
==================================================
Dựa trên chức năng thực tế của hệ thống, hãy thiết kế lại schema mới từ đầu.

YÊU CẦU THIẾT KẾ:
- Tên bảng rõ nghĩa, nhất quán
- Tên cột rõ nghĩa
- PK chuẩn là `id` nếu không có lý do cực mạnh để làm khác
- FK chuẩn Laravel `foreignId`
- Quan hệ Eloquent phải dễ hiểu
- Trạng thái / enum phải có chiến lược thống nhất
- Các bảng được gom theo domain nghiệp vụ
- Chỉ merge bảng khi thật sự hợp lý về business
- Không giữ nguyên cấu trúc cũ nếu nó sai

Hãy đề xuất schema mới theo nhóm domain, ví dụ:
- users / auth / profiles
- courses / modules / categories
- enrollments / assignments / schedules / attendance
- learning materials
- assessments / question bank / answers / submissions
- reports / results / notifications / approval requests
- system tables

Với mỗi bảng mới, hãy mô tả:
- tên bảng
- mục đích
- các cột chính
- khóa ngoại
- unique constraints
- indexes
- quan hệ với bảng khác
- soft delete có cần không
- timestamps có cần không

==================================================
PHẦN 5 - BẢNG MAPPING CŨ -> MỚI
==================================================
Hãy tạo một bảng mapping đầy đủ giữa schema cũ và schema mới.

Với mỗi bảng cũ, hãy chỉ ra:
- bảng mới nào thay thế nó
- bảng cũ bị xóa hẳn hay gộp sang bảng nào
- cột nào map sang cột nào
- dữ liệu nào cần transform
- dữ liệu nào cần bỏ
- logic nghiệp vụ nào cần cập nhật theo schema mới

Ví dụ mong muốn:
| Old Table | Old Column | New Table | New Column | Action | Notes |
|-----------|------------|-----------|------------|--------|-------|

Nếu có trường hợp:
- 2 bảng cũ gộp thành 1 bảng mới
- 1 bảng cũ tách thành 2 bảng mới
- cột đổi tên
- FK đổi đích
- enum đổi giá trị
thì phải ghi rất rõ.

==================================================
PHẦN 6 - THỨ TỰ TẠO MIGRATIONS MỚI
==================================================
Hãy đề xuất thứ tự tạo migration mới hợp lý.

Ví dụ:
1. users
2. student_profiles / teacher_profiles
3. categories / departments
4. courses
5. course_modules
6. enrollments / assignments
7. class_sessions
8. attendances
9. learning_materials
10. assessments
11. question_bank_items
12. question_options
13. assessment_questions
14. assessment_attempts
15. assessment_attempt_answers
16. learning_results
17. notifications
18. pending_accounts
19. settings / banners nếu còn cần

Giải thích vì sao cần thứ tự đó.

==================================================
PHẦN 7 - VIẾT CODE MIGRATION MỚI
==================================================
Sau khi hoàn tất các bước phân tích và thiết kế ở trên, hãy bắt đầu viết code migration mới.

YÊU CẦU KHI VIẾT MIGRATION:
- dùng Laravel migration chuẩn
- code sạch, dễ đọc
- dùng `Schema::create`
- dùng `foreignId()->constrained()` khi hợp lý
- thêm `cascadeOnDelete`, `nullOnDelete`, `restrictOnDelete` một cách có chủ đích
- thêm unique/index rõ ràng
- enum hoặc string có giải thích rõ
- timestamps / softDeletes hợp lý
- không viết migration theo kiểu chắp vá
- không dùng tên cột mơ hồ

Nếu có bảng nên dùng JSON thì phải giải thích vì sao.
Nếu có bảng tổng hợp/denormalized thì phải giải thích vì sao nó cần tồn tại.

==================================================
PHẦN 8 - KẾ HOẠCH MIGRATE DỮ LIỆU CŨ
==================================================
Nếu cần giữ dữ liệu cũ, hãy viết kế hoạch migrate dữ liệu.

Yêu cầu:
- chỉ rõ dữ liệu nào có thể chuyển thẳng
- dữ liệu nào cần transform
- dữ liệu nào có nguy cơ lỗi
- thứ tự migrate dữ liệu
- cách validate dữ liệu sau migrate
- cách rollback nếu migrate lỗi

Nếu hợp lý, hãy đề xuất:
- artisan command
- seeder
- custom migration data script
- temporary mapping tables
- logging strategy

==================================================
PHẦN 9 - KẾ HOẠCH REFACTOR CODE THEO SCHEMA MỚI
==================================================
Sau khi thay schema, code ứng dụng chắc chắn phải sửa theo.

Hãy liệt kê những phần cần refactor:
- Models
- Relationships
- Controllers
- Form Requests
- Services
- Repositories
- Validation rules
- Queries
- Blade views / API resources
- Seeders
- Factories
- Tests

Với mỗi phần, hãy mô tả:
- đang phụ thuộc schema cũ như thế nào
- cần sửa gì để chạy với schema mới

==================================================
PHẦN 10 - OUTPUT CUỐI CÙNG PHẢI CÓ
==================================================
Tôi muốn output cuối cùng của bạn gồm đủ các mục sau:

1. Tổng quan chức năng hệ thống
2. Phân tích schema/migration cũ
3. Danh sách vấn đề hiện tại
4. Đề xuất schema mới chuẩn hóa
5. ERD logic mô tả bằng markdown
6. Bảng mapping cũ -> mới
7. Thứ tự tạo migration mới
8. Toàn bộ code migration mới
9. Kế hoạch migrate dữ liệu
10. Kế hoạch refactor code ứng dụng
11. Danh sách rủi ro và checklist kiểm thử

==================================================
QUY TẮC LÀM VIỆC
==================================================
- Luôn giải thích reasoning ở mức kỹ thuật rõ ràng, nhưng ưu tiên output có cấu trúc.
- Không được nhảy cóc sang code trước khi xong phần phân tích.
- Nếu phát hiện một bảng đang thiết kế sai về mặt domain, hãy đề xuất lại dứt khoát.
- Nếu phát hiện 2 bảng cùng chức năng, hãy phân tích có nên merge hay không.
- Nếu thấy schema cũ lệch chuẩn Laravel, hãy chuẩn hóa về convention Laravel.
- Hãy ưu tiên tính rõ ràng cho developer bảo trì về sau.
- Trước khi viết migration mới, phải có một “schema proposal” hoàn chỉnh.
- Nếu có nhiều phương án thiết kế, hãy đưa ra phương án tốt nhất và giải thích ngắn vì sao chọn nó.

==================================================
ĐỊNH DẠNG TRẢ LỜI MONG MUỐN
==================================================
Hãy làm theo đúng trình tự sau:

Bước 1. Audit chức năng hệ thống
Bước 2. Audit migration/schema cũ
Bước 3. Liệt kê vấn đề
Bước 4. Đề xuất schema mới
Bước 5. Mapping cũ -> mới
Bước 6. Thứ tự migration mới
Bước 7. Viết migration code mới
Bước 8. Kế hoạch migrate dữ liệu
Bước 9. Kế hoạch refactor code
Bước 10. Checklist test và rollout

Mỗi bước phải có heading rõ ràng.
Nếu output quá dài, chia thành nhiều phần nhưng vẫn giữ đúng thứ tự.
Không bỏ qua bước nào.
Lưu ý:
- Không được trả lời ở mức lý thuyết chung chung.
- Phải bám sát đúng codebase hiện tại.
- Phải trích dẫn trực tiếp từ các file migration/model/controller đang có trong project.
- Nếu đề xuất merge bảng, phải chỉ rõ merge bảng nào với bảng nào và lý do.
- Nếu đề xuất đổi tên bảng/cột, phải chỉ rõ tên cũ và tên mới.
- Nếu thấy một bảng đang FK sai hướng hoặc tên cột gây hiểu nhầm, phải nêu cụ thể.
- Chỉ bắt đầu viết migration mới sau khi đã có đầy đủ audit + schema proposal + mapping.

Trước tiên chỉ làm từ Bước 1 đến Bước 5.
Chưa cần viết code migration mới.

Tiếp tục từ Bước 6 đến Bước 10.
Bắt đầu viết migration code mới.

Bây giờ hãy viết luôn các model tương ứng với schema mới và refactor relationships.

Đặc biệt chú ý các bảng người dùng, học viên, giảng viên, khóa học, module học, lịch học, điểm danh, bài kiểm tra, bài làm và kết quả học tập; kiểm tra kỹ xem các cột như `hoc_vien_id`, `giang_vien_id`, `nguoi_dung_id` có đang trỏ đúng semantic table hay không.