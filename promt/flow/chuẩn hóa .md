Bạn là một coding agent đang làm việc trực tiếp trên codebase của dự án Laravel quản lý đào tạo trực tuyến của tôi.

Mục tiêu của bạn là rà soát và cải thiện toàn bộ phần văn bản hiển thị trên giao diện người dùng theo từng phase, nhưng KHÔNG làm thay đổi logic nghiệp vụ, route, controller behavior, database schema hay quyền truy cập của hệ thống.

## Bối cảnh dự án
- Đây là hệ thống quản lý đào tạo trực tuyến có nhiều vai trò như admin, giảng viên, học viên.
- Hệ thống có nhiều màn hình quản trị, học tập, khóa học, lịch học, bài giảng, bài kiểm tra, tài nguyên, live room, thông báo...
- Hiện tại trong hệ thống có các vấn đề:
  1. Nhiều chỗ bị thiếu dấu tiếng Việt.
  2. Một số chỗ có lỗi font / lỗi encoding / ký tự hiển thị không đúng.
  3. Nhiều đoạn văn bản hiển thị chưa tự nhiên, chưa hay, chưa thống nhất cách diễn đạt.
  4. Có chỗ viết hoa, viết thường, dấu câu, khoảng trắng, xuống dòng chưa chuẩn.
  5. Một số label, button, heading, thông báo, trạng thái, mô tả còn khó hiểu hoặc chưa phù hợp ngữ cảnh người dùng.

## Mục tiêu công việc
Bạn cần cải thiện phần nội dung hiển thị để giao diện:
- đúng tiếng Việt có dấu
- không lỗi font, không lỗi ký tự
- rõ nghĩa, tự nhiên, dễ hiểu
- lịch sự, thống nhất văn phong
- phù hợp ngữ cảnh của từng vai trò người dùng
- không quá học thuật, không quá cứng, không quá văn nói
- ngắn gọn nhưng đủ ý
- nhất quán trên toàn hệ thống

## Nguyên tắc bắt buộc
1. KHÔNG thay đổi logic nghiệp vụ.
2. KHÔNG tự ý đổi tên biến, tên hàm, route, model, migration nếu không cần thiết cho việc hiển thị văn bản.
3. KHÔNG sửa các nội dung động lấy từ database trừ khi đang xử lý encoding an toàn ở tầng hiển thị.
4. Chỉ chỉnh các chuỗi hiển thị trong:
   - Blade views
   - component views
   - file config / constants / enums / helpers chứa text hiển thị
   - validation message
   - flash message / toast / alert / notification text
   - placeholder / label / title / heading / button text
   - trạng thái hiển thị ra giao diện
5. Nếu phát hiện text bị lặp lại ở nhiều nơi, ưu tiên chuẩn hóa để thống nhất cách dùng.
6. Nếu có khả năng lỗi font do encoding dữ liệu, hãy ghi nhận rõ chỗ đó và đề xuất cách xử lý an toàn, nhưng không tự ý phá dữ liệu hiện có.
7. Không thêm nội dung dài dòng. Ưu tiên văn phong ngắn, rõ, tự nhiên, dễ dùng trong UI.
8. Nếu không chắc ngữ cảnh của một chuỗi, hãy giữ nghĩa cũ và chỉ chỉnh cho dễ đọc hơn.

## Văn phong mong muốn
- Tiếng Việt chuẩn, có dấu đầy đủ.
- Tự nhiên, dễ hiểu, thân thiện vừa phải.
- Tránh từ quá chuyên môn nếu là giao diện cho người dùng phổ thông.
- Tránh câu quá cộc lốc hoặc máy móc.
- Tránh kiểu dịch thô từ tiếng Anh.
- Ưu tiên các cách diễn đạt như:
  - “Lưu thay đổi”
  - “Cập nhật thông tin”
  - “Không tìm thấy dữ liệu”
  - “Bạn chưa được phân công vào module này”
  - “Vui lòng chọn khóa học”
  - “Thao tác thành công”
  - “Có lỗi xảy ra, vui lòng thử lại”

## Cách làm việc theo phase
Bạn phải làm việc theo từng phase, không sửa tràn lan toàn repo trong một lần.

### Phase 1: Audit và lập danh sách
- Quét toàn bộ codebase để tìm tất cả chuỗi hiển thị ra giao diện.
- Phân nhóm theo khu vực:
  - Public/Auth
  - Admin
  - Giảng viên
  - Học viên
  - Khóa học / module / lịch học
  - Bài giảng / tài nguyên / live room
  - Bài kiểm tra / câu hỏi / chấm điểm
  - Thông báo / trạng thái / validation / empty states
- Lập danh sách các lỗi theo nhóm:
  1. Thiếu dấu
  2. Lỗi font / encoding nghi ngờ
  3. Câu chữ khó hiểu
  4. Không thống nhất cách gọi
  5. Viết hoa / dấu câu / khoảng trắng chưa chuẩn
- Chưa sửa ngay toàn bộ. Trước tiên hãy báo cáo audit.

### Phase 2: Chuẩn hóa glossary
- Tạo một bảng quy chuẩn thuật ngữ hiển thị dùng thống nhất trong toàn hệ thống.
- Ví dụ các từ cần thống nhất:
  - học viên / sinh viên
  - giảng viên / giáo viên
  - khóa học / lớp học / lớp đang mở
  - module học
  - bài giảng
  - tài nguyên
  - bài kiểm tra
  - điểm danh
  - kết quả học tập
  - gửi duyệt / chờ duyệt / đã duyệt / từ chối
- Đề xuất cách gọi chuẩn cho toàn hệ thống trước khi sửa hàng loạt.

### Phase 3: Sửa theo từng khu vực UI
Sửa lần lượt theo từng khu vực, mỗi lần chỉ làm 1 nhóm:
1. Auth + Public pages
2. Admin pages
3. Teacher pages
4. Student pages
5. Shared components / modals / tables / buttons / alerts
6. Validation + notifications + status labels

Với mỗi khu vực:
- sửa text hiển thị
- giữ nguyên logic
- ưu tiên thay ít nhưng đúng
- đảm bảo thống nhất với glossary đã chốt

### Phase 4: Rà soát lần cuối
- Kiểm tra toàn hệ thống lần cuối để tìm:
  - text còn không dấu
  - text lỗi encoding
  - text trùng nghĩa nhưng khác cách viết
  - trạng thái hiển thị chưa thống nhất
  - nút bấm / tiêu đề / placeholder chưa tự nhiên
- Chuẩn hóa nốt những điểm còn sót.

## Cách báo cáo sau mỗi phase
Sau mỗi phase, bắt buộc cung cấp:
1. Tóm tắt đã làm gì
2. Các file đã sửa
3. Những nhóm text đã được chuẩn hóa
4. Những chỗ còn nghi ngờ cần tôi xác nhận
5. Những chỗ nghi lỗi dữ liệu/encoding từ database mà chưa nên sửa tay

## Cách thực hiện thay đổi
Khi sửa, ưu tiên:
- sửa trực tiếp chuỗi text trong view nếu đơn giản
- nếu text lặp nhiều nơi, gom về constant/helper/lang file hợp lý nếu codebase đang phù hợp
- không tái cấu trúc quá lớn chỉ vì text
- không tạo abstraction thừa

## Tiêu chí đánh giá kết quả
Kết quả được xem là tốt khi:
- giao diện không còn chữ không dấu bất thường
- không còn text lỗi font rõ ràng
- các button, tiêu đề, thông báo, trạng thái đọc tự nhiên
- cách gọi thuật ngữ thống nhất
- giao diện nhìn chuyên nghiệp và dễ dùng hơn
- không làm hỏng chức năng cũ

## Bắt đầu ngay
Trước tiên, hãy thực hiện Phase 1:
- quét repo
- lập audit các chuỗi hiển thị có vấn đề
- nhóm theo khu vực
- đề xuất glossary chuẩn
- chưa sửa hàng loạt trước khi đưa ra báo cáo phase đầu tiên
Ưu tiên rà soát các thư mục sau trước:
- resources/views
- app/View/Components
- app/Http/Controllers (các flash message / validation / status text)
- app/Services nếu có chuỗi hiển thị
- lang hoặc các file constant/enum chứa text trạng thái