Bạn là senior Laravel architect + feature engineer.

Tôi đang làm đồ án web học tập và kiểm tra online bằng Laravel.
Repo của tôi:
https://github.com/vinhdth225793-crypto/thuctap_khaitri.git

Tôi muốn bạn phát triển và chuẩn hóa riêng chức năng **Bài kiểm tra** cho hệ thống hiện tại của tôi.

==================================================
MỤC TIÊU CHÍNH
==================================================

Tôi muốn chức năng bài kiểm tra bám đúng flow sau:

1. Admin quản lý ngân hàng câu hỏi
2. Admin import câu hỏi từ Excel
3. Giảng viên tạo bài kiểm tra cho khóa học / module / buổi học mình phụ trách
4. Bài kiểm tra lấy câu hỏi từ ngân hàng câu hỏi
5. Hỗ trợ 3 hướng tạo đề:
   - đề danh sách
   - đề tự động
   - đề section (phase sau)
6. Giảng viên lưu nháp hoặc gửi admin duyệt
7. Admin duyệt / từ chối / yêu cầu chỉnh sửa
8. Chỉ bài kiểm tra đã duyệt và đã công bố mới hiện cho học viên
9. Học viên làm bài ngay trên hệ thống
10. Hệ thống chấm điểm và lưu kết quả
11. Nếu có câu tự luận thì giảng viên chấm tay

==================================================
YÊU CẦU BẮT BUỘC
==================================================

- Không được viết lại toàn bộ hệ thống từ đầu
- Phải đọc repo hiện tại trước
- Phải tận dụng tối đa code cũ đang có
- Phải bám theo cấu trúc hệ thống hiện tại:
  - khóa học
  - module học
  - buổi học / lịch học
  - phân công giảng viên
  - học viên khóa học
  - ngân hàng câu hỏi
  - bài kiểm tra
  - bài làm bài kiểm tra
- Mọi thay đổi phải bám repo thật
- Nếu đã có migration / model / controller / route / view phù hợp thì tận dụng
- Chỉ thêm bảng mới khi thật sự cần
- Làm theo từng phase
- Xong phase nào phải test được phase đó
- Trước khi code mỗi phase, phải nêu:
  1. mục tiêu phase
  2. repo hiện tại đang có gì liên quan
  3. file sẽ sửa
  4. migration sẽ thêm hoặc sửa
  5. cách test

==================================================
PHASE 0 - AUDIT REPO VÀ CHỐT KIẾN TRÚC
==================================================

Mục tiêu:
- Đọc toàn bộ repo
- Xác định toàn bộ phần hiện có liên quan đến bài kiểm tra
- Chốt hướng phát triển ít phá hệ thống nhất

Bạn phải đọc kỹ các phần sau:
- database/migrations
- app/Models
- app/Http/Controllers
- routes/web.php
- resources/views

Bạn phải kiểm tra đặc biệt các thành phần sau:
- NganHangCauHoi
- DapAnCauHoi
- BaiKiemTra
- ChiTietBaiKiemTra
- BaiLamBaiKiemTra
- ChiTietBaiLamBaiKiemTra
- KetQuaHocTap
- các controller admin / giảng viên / học viên liên quan
- route admin / giảng viên / học viên liên quan đến bài kiểm tra
- view hiện có của bài kiểm tra và ngân hàng câu hỏi

Bạn phải trả lời rõ:
1. Repo hiện đã có những bảng nào cho bài kiểm tra
2. Repo hiện đã có những model nào
3. Repo hiện đã có những controller nào
4. Repo hiện đã có những route nào
5. Repo hiện đã có những view nào
6. Cái gì đang dùng được
7. Cái gì còn thiếu
8. Cái gì bị trùng hoặc chồng chéo
9. Nên giữ cấu trúc hiện tại ra sao để phát triển tiếp

Output phase 0 bắt buộc:
1. Tóm tắt phát hiện
2. Kiến trúc dữ liệu đề xuất
3. Danh sách file sẽ sửa theo phase
4. Lộ trình phase cụ thể

CHƯA CODE NGAY nếu chưa xong phase 0.

==================================================
PHASE 1 - CHUẨN HÓA NGÂN HÀNG CÂU HỎI
==================================================

Mục tiêu:
- Hoàn thiện ngân hàng câu hỏi làm nền cho bài kiểm tra

Flow mục tiêu:
- Admin vào ngân hàng câu hỏi
- Tạo câu hỏi thủ công
- Quản lý đáp án
- Lọc theo khóa học / module / loại câu hỏi / mức độ
- Ẩn / hiện / tái sử dụng câu hỏi

Yêu cầu nghiệp vụ:
- Mỗi câu hỏi gắn với:
  - khóa học
  - module
  - loại câu hỏi
  - mức độ
  - điểm mặc định
  - giải thích đáp án
  - trạng thái
- Mỗi câu hỏi có nhiều đáp án
- Ưu tiên hỗ trợ:
  - một đáp án đúng
  - nhiều đáp án đúng
  - đúng/sai
  - tự luận

Bạn cần:
1. Kiểm tra migration hiện tại của ngân hàng câu hỏi và đáp án
2. Kiểm tra model hiện tại
3. Kiểm tra controller hiện tại
4. Bổ sung field còn thiếu nếu cần
5. Chuẩn hóa relationship Eloquent
6. Chuẩn hóa validate tạo / sửa câu hỏi
7. Chuẩn hóa giao diện quản lý câu hỏi nếu cần

Output phase 1:
1. Migration thêm/sửa nếu cần
2. Model cập nhật
3. Controller cập nhật
4. Request validate nếu cần
5. Route cập nhật
6. View blade cập nhật
7. Cách test tạo/sửa câu hỏi

==================================================
PHASE 2 - IMPORT EXCEL CHO NGÂN HÀNG CÂU HỎI
==================================================

Mục tiêu:
- Cho phép admin import câu hỏi từ Excel an toàn

Flow mục tiêu:
1. Admin tải file mẫu
2. Admin upload file Excel
3. Hệ thống đọc file
4. Hệ thống preview dữ liệu trước khi lưu
5. Hệ thống kiểm tra lỗi và nghi trùng
6. Admin xác nhận import
7. Câu hỏi được lưu vào ngân hàng câu hỏi

Yêu cầu nghiệp vụ:
- File mẫu giai đoạn đầu nên đơn giản:
  - nội dung câu hỏi
  - đáp án đúng
  - đáp án sai 1
  - đáp án sai 2
  - đáp án sai 3
  - mức độ
  - module
  - giải thích đáp án
- Kiểm tra:
  - thiếu dữ liệu
  - module không hợp lệ
  - thiếu đáp án đúng
  - dòng trống
  - câu hỏi trùng hoặc gần giống

Bạn cần:
1. Kiểm tra code import hiện tại nếu repo đã có
2. Tận dụng tối đa code cũ
3. Thêm bước preview trước khi import chính thức nếu chưa có
4. Thêm kiểm tra trùng câu hỏi
5. Thêm thông báo số dòng hợp lệ / lỗi / nghi trùng
6. Chuẩn hóa file mẫu export nếu cần

Output phase 2:
1. Controller import cập nhật
2. Logic preview import
3. Logic check duplicate
4. Route import / preview / confirm
5. View import
6. Template export nếu cần
7. Cách test import

==================================================
PHASE 3 - TẠO BÀI KIỂM TRA CƠ BẢN
==================================================

Mục tiêu:
- Giảng viên tạo bài kiểm tra gắn đúng khóa học / module / buổi học

Flow mục tiêu:
1. Giảng viên vào khóa học mình phụ trách
2. Chọn module hoặc buổi học
3. Chọn tạo bài kiểm tra
4. Nhập thông tin cơ bản
5. Chọn hình thức tạo đề
6. Lưu nháp hoặc chuyển bước tiếp theo

Thông tin cơ bản của bài kiểm tra:
- tiêu đề
- mô tả
- khóa học
- module
- buổi học
- thời gian làm bài
- thời gian mở
- thời gian đóng
- số lần được làm
- điểm đạt
- trộn câu hỏi hay không
- trạng thái hiển thị ban đầu

Bạn cần:
1. Kiểm tra model BaiKiemTra hiện tại
2. Kiểm tra migration BaiKiemTra hiện tại
3. Kiểm tra form tạo bài kiểm tra hiện có
4. Bổ sung field nếu còn thiếu
5. Chỉ cho giảng viên tạo bài kiểm tra trong khóa học/module/buổi học mình được phân công

Output phase 3:
1. Migration thêm/sửa nếu cần
2. Model cập nhật
3. Controller tạo/sửa bài kiểm tra
4. Request validate
5. Route tạo/sửa
6. View form tạo/sửa
7. Logic kiểm tra quyền giảng viên
8. Cách test tạo bài kiểm tra

==================================================
PHASE 4 - ĐỀ DANH SÁCH
==================================================

Mục tiêu:
- Giảng viên chọn thủ công các câu hỏi từ ngân hàng câu hỏi để tạo đề

Flow mục tiêu:
1. Giảng viên chọn kiểu đề = danh sách
2. Hệ thống hiển thị danh sách câu hỏi từ ngân hàng
3. Giảng viên lọc câu hỏi theo:
   - khóa học
   - module
   - loại câu hỏi
   - mức độ
   - từ khóa
4. Giảng viên chọn các câu muốn đưa vào đề
5. Giảng viên sắp xếp thứ tự câu hỏi
6. Giảng viên điều chỉnh điểm từng câu nếu cần
7. Lưu chi tiết bài kiểm tra

Yêu cầu nghiệp vụ:
- Một bài kiểm tra có nhiều câu hỏi
- Mỗi câu có thể có:
  - thứ tự
  - điểm riêng
  - bắt buộc trả lời hay không
- Giảng viên không được chọn câu hỏi ngoài phạm vi khóa học phù hợp

Bạn cần:
1. Kiểm tra bảng chi tiết bài kiểm tra hiện tại
2. Kiểm tra relationship giữa BaiKiemTra và NganHangCauHoi
3. Tận dụng bảng hiện có nếu phù hợp
4. Tạo giao diện chọn câu hỏi
5. Tạo chức năng sắp xếp câu hỏi trong đề
6. Tạo chức năng xóa câu khỏi đề

Output phase 4:
1. Model relationship đầy đủ
2. Controller thêm/xóa/sắp xếp câu hỏi
3. Route chi tiết đề danh sách
4. View chọn câu hỏi
5. View danh sách câu trong đề
6. Cách test đề danh sách

==================================================
PHASE 5 - ĐỀ TỰ ĐỘNG
==================================================

Mục tiêu:
- Giảng viên tạo bài kiểm tra theo điều kiện, hệ thống tự chọn câu hỏi

Flow mục tiêu:
1. Giảng viên chọn kiểu đề = tự động
2. Nhập các điều kiện:
   - số lượng câu hỏi
   - module
   - loại câu hỏi
   - mức độ
   - có trộn câu hỏi hay không
3. Hệ thống lưu cấu hình sinh đề
4. Khi học viên bắt đầu làm bài:
   - hệ thống tự chọn ngẫu nhiên câu hỏi phù hợp
   - tạo bộ câu hỏi cho bài làm

Yêu cầu nghiệp vụ:
- Chỉ lấy câu hỏi đang hoạt động
- Chỉ lấy câu hỏi thuộc khóa học/module phù hợp
- Nếu số lượng câu không đủ thì báo lỗi rõ ràng
- Nếu đề tự động thì mỗi lần làm có thể khác nhau, tùy thiết kế repo hiện tại

Bạn cần:
1. Kiểm tra BaiKiemTra hiện có đã có field loại đề chưa
2. Nếu chưa có, thêm field phù hợp
3. Thêm nơi lưu cấu hình tự động
4. Tạo logic random câu hỏi
5. Gắn logic này vào bước bắt đầu làm bài

Output phase 5:
1. Migration thêm cấu hình đề tự động nếu cần
2. Controller xử lý lưu cấu hình
3. Service/helper chọn câu ngẫu nhiên
4. Tích hợp vào flow batDau làm bài
5. View cấu hình đề tự động
6. Cách test đề tự động

==================================================
PHASE 6 - DUYỆT BÀI KIỂM TRA
==================================================

Mục tiêu:
- Giảng viên tạo bài kiểm tra nhưng phải qua admin duyệt

Flow mục tiêu:
1. Giảng viên tạo xong bài kiểm tra
2. Giảng viên chọn:
   - lưu nháp
   - gửi admin duyệt
3. Admin xem danh sách bài kiểm tra chờ duyệt
4. Admin xem chi tiết:
   - thông tin bài kiểm tra
   - danh sách câu hỏi hoặc cấu hình đề tự động
   - khóa học / module / buổi học
   - thời gian mở / đóng
5. Admin chọn:
   - duyệt
   - từ chối
   - yêu cầu chỉnh sửa
6. Nếu duyệt thì bài kiểm tra được phép công bố

Trạng thái nên có:
- nhap
- cho_duyet
- can_chinh_sua
- bi_tu_choi
- da_duyet
- da_cong_bo
- da_dong

Bạn cần:
1. Kiểm tra repo hiện đã có flow duyệt nào cho bài kiểm tra chưa
2. Tận dụng tối đa code duyệt hiện có
3. Chuẩn hóa trạng thái
4. Thêm ghi chú duyệt / lý do từ chối
5. Tạo giao diện admin duyệt bài kiểm tra

Output phase 6:
1. Migration thêm field nếu cần
2. Controller giảng viên gửi duyệt
3. Controller admin duyệt / từ chối / yêu cầu sửa
4. Route tương ứng
5. View danh sách chờ duyệt
6. View chi tiết duyệt
7. Cách test workflow duyệt

==================================================
PHASE 7 - HỌC VIÊN LÀM BÀI
==================================================

Mục tiêu:
- Cho phép học viên bắt đầu làm và nộp bài trên hệ thống

Flow mục tiêu:
1. Học viên vào khóa học của tôi
2. Chọn module / buổi học
3. Xem danh sách bài kiểm tra
4. Chỉ thấy bài kiểm tra khi:
   - thuộc khóa học đó
   - đã duyệt
   - đã công bố
   - đã đến thời gian mở
   - chưa quá hạn
   - chưa vượt số lần làm
5. Học viên bấm bắt đầu
6. Hệ thống tạo bài làm
7. Học viên chọn đáp án
8. Học viên nộp bài hoặc hệ thống tự nộp khi hết giờ

Bạn cần:
1. Kiểm tra controller học viên hiện có
2. Kiểm tra model BaiLamBaiKiemTra và ChiTietBaiLam hiện có
3. Tận dụng tối đa flow batDau / nopBai hiện có
4. Chuẩn hóa validate quyền học viên
5. Chuẩn hóa giao diện làm bài
6. Nếu đề tự động thì gắn logic sinh câu hỏi ở bước bắt đầu

Output phase 7:
1. Controller batDau / nopBai cập nhật
2. Model bài làm cập nhật nếu cần
3. Route học viên
4. View danh sách bài kiểm tra cho học viên
5. View làm bài
6. Cách test học viên làm bài

==================================================
PHASE 8 - CHẤM ĐIỂM VÀ KẾT QUẢ
==================================================

Mục tiêu:
- Chấm điểm tự động cho trắc nghiệm
- Hỗ trợ chấm tay cho tự luận
- Lưu kết quả học tập

Flow mục tiêu:
1. Sau khi nộp bài:
   - hệ thống chấm các câu trắc nghiệm tự động
2. Nếu có câu tự luận:
   - bài làm chuyển trạng thái chờ chấm
   - giảng viên vào khu chấm điểm
3. Sau khi chấm xong:
   - cập nhật điểm tổng
   - cập nhật đạt/chưa đạt
   - lưu vào kết quả học tập
4. Học viên xem kết quả nếu được phép

Bạn cần:
1. Kiểm tra logic chấm hiện tại nếu đã có
2. Kiểm tra bảng ket_qua_hoc_tap
3. Chuẩn hóa cách tính điểm
4. Chuẩn hóa trạng thái bài làm
5. Tạo hoặc sửa giao diện chấm tự luận nếu cần
6. Tạo hoặc sửa giao diện xem kết quả

Output phase 8:
1. Logic chấm tự động
2. Logic chấm tay
3. Controller giảng viên chấm bài
4. Controller học viên xem kết quả
5. View chấm bài
6. View kết quả
7. Cách test toàn bộ

==================================================
PHASE 9 - SECTION (NÂNG CAO, LÀM SAU)
==================================================

Mục tiêu:
- Mở rộng bài kiểm tra theo dạng section

Flow mục tiêu:
1. Một bài kiểm tra có nhiều section
2. Mỗi section có:
   - tiêu đề
   - mô tả / ngữ cảnh
   - danh sách câu hỏi
   - thứ tự
3. Học viên làm bài theo từng section

Yêu cầu:
- Chỉ làm phase này sau khi phase 1 đến 8 đã ổn
- Không được làm phase này nếu core chưa chạy tốt

Bạn cần:
1. Đề xuất bảng section nếu cần
2. Tạo liên kết section - câu hỏi
3. Cập nhật giao diện giảng viên
4. Cập nhật giao diện học viên

Output phase 9:
1. Kiến trúc section
2. Migration nếu cần
3. Model
4. Controller
5. View
6. Cách test

==================================================
FORMAT KẾT QUẢ MỖI PHASE
==================================================

Mỗi phase phải trả lời theo format:

1. Mục tiêu phase
2. Repo hiện tại đang có gì liên quan
3. Vấn đề đang có
4. Hướng xử lý được chọn
5. Vì sao chọn cách này
6. File sẽ sửa
7. Migration sẽ thêm / sửa
8. Code đầy đủ
9. Cách chạy
10. Cách test
11. Rủi ro còn lại

==================================================
QUY TẮC CODE
==================================================

- Laravel style rõ ràng
- Dùng Eloquent relationship chuẩn
- Dùng FormRequest nếu cần
- Không hardcode bừa
- Tận dụng code hiện có trước
- Không phá dữ liệu cũ nếu chưa cần
- Không tự ý đổi tên bảng/cột mạnh nếu chưa thật sự cần
- Không trả lời chung chung
- Mọi đề xuất phải bám repo thật
- Nếu repo đã có một phần nào rồi thì phải refactor trên nền đó
- Nếu có chỗ chưa chắc chắn thì phải nói rõ

==================================================
BẮT ĐẦU NGAY
==================================================

Bây giờ hãy bắt đầu với:
PHASE 0 - AUDIT REPO VÀ CHỐT KIẾN TRÚC

Chưa code ngay.
Trước tiên hãy đọc kỹ repo và báo cáo:
- phần nào đã có cho bài kiểm tra
- phần nào còn thiếu
- phần nào nên giữ nguyên
- phần nào nên refactor
- rồi chia roadmap code chi tiết theo phase

YÊU CẦU RẤT QUAN TRỌNG VỀ TIẾNG VIỆT / UNICODE

- Toàn bộ code, view blade, migration, seed, controller, validation message, label giao diện phải dùng tiếng Việt Unicode chuẩn.
- Tất cả file text/code phải lưu dưới dạng UTF-8.
- Không được dùng ký tự lỗi mã hóa, không dùng chuỗi bị bể dấu kiểu: "bài giảng", "gia?ng vie?n", "ho?c vie?n".
- Khi sửa hoặc tạo file Blade, phải kiểm tra lại toàn bộ chữ tiếng Việt hiển thị đúng dấu.
- Nếu thấy text bị lỗi dấu, phải sửa lại ngay theo Unicode chuẩn.
- Ưu tiên dùng UTF-8 without BOM nếu phù hợp môi trường Laravel.
- Không copy lại text từ nguồn gây lỗi encoding.
- Kiểm tra kỹ:
  - tiêu đề
  - label form
  - placeholder
  - thông báo validate
  - nút bấm
  - toast / alert
  - nội dung blade
- Với dữ liệu DB và view, phải đảm bảo hiển thị tiếng Việt đúng Unicode.
- Nếu phát hiện file cũ có dấu hiệu lỗi encoding, hãy note rõ file nào bị lỗi và sửa lại.
- Sau mỗi phase, hãy tự rà lại tất cả text tiếng Việt trong các file đã sửa để đảm bảo không lỗi dấu.