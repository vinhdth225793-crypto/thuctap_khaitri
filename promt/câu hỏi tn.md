Bạn là senior Laravel developer + backend architect + document parsing engineer + import pipeline designer.

Tôi đang phát triển một project Laravel cho hệ thống học tập và kiểm tra online. Hiện tại tôi muốn xây dựng một chức năng mới: **import câu hỏi từ nhiều loại file tài liệu vào hệ thống ngân hàng câu hỏi**, để người dùng không cần nhập thủ công từng câu.

==============================
1. MỤC TIÊU CHỨC NĂNG
==============================

Tôi muốn hệ thống hỗ trợ import câu hỏi từ các loại file tài liệu như:

- Word `.docx`
- PDF `.pdf`

Và kiến trúc phải đủ mở để sau này có thể mở rộng thêm:
- `.txt`
- `.xlsx`
- hoặc các định dạng tài liệu khác nếu cần

Mục tiêu cuối cùng:
- người dùng chọn file tài liệu
- hệ thống tự đọc nội dung
- tự phân tích câu hỏi
- tự tách đáp án
- tự xác định đáp án đúng nếu có thể
- hiển thị preview kết quả
- cho phép import vào ngân hàng câu hỏi
- giảm tối đa thao tác nhập tay

==============================
2. NGỮ CẢNH HỆ THỐNG
==============================

Đây là web học tập và kiểm tra online.

Các phần liên quan trong hệ thống có thể đã tồn tại:
- khóa học
- bài kiểm tra
- ngân hàng câu hỏi
- đáp án câu hỏi
- import câu hỏi từ Excel
- giao diện quản trị admin

Hệ thống cần lưu câu hỏi theo hướng normalized:
- 1 bảng câu hỏi
- 1 bảng đáp án
- 1 câu hỏi có nhiều đáp án
- có 1 đáp án đúng

Hệ thống cũng có yêu cầu hoặc định hướng:
- trộn đáp án khi học viên làm bài
- không phụ thuộc cứng vào A/B/C/D để xác định đáp án đúng
- đáp án đúng phải được xác định theo bản ghi đáp án thực tế trong database

==============================
3. YÊU CẦU TÍCH HỢP VỚI CODE HIỆN TẠI
==============================

Bạn KHÔNG được code mù.

Trước khi triển khai, bạn phải:
1. đọc code hiện tại liên quan đến:
   - ngân hàng câu hỏi
   - đáp án câu hỏi
   - bài kiểm tra
   - import Excel nếu đang có
   - route / controller / view admin liên quan
2. xác định:
   - bảng nào đang lưu câu hỏi
   - bảng nào đang lưu đáp án
   - cách hệ thống đang xác định đáp án đúng
   - flow hiện tại của admin khi tạo/import câu hỏi
3. tái sử dụng tối đa code sẵn có nếu hợp lý
4. không làm hỏng chức năng import Excel đang có
5. không đổi schema bừa bãi nếu không thật sự cần

==============================
4. MỤC TIÊU THIẾT KẾ KỸ THUẬT
==============================

Tôi muốn kiến trúc chức năng này:
- sạch
- tách lớp rõ ràng
- dễ bảo trì
- dễ test
- dễ mở rộng thêm loại file mới

Kiến trúc mong muốn:

1. Controller
- xử lý upload file
- gọi service điều phối
- hiển thị preview
- xác nhận import

2. Form Request
- validate file upload
- validate tham số liên quan như khóa học / ngân hàng câu hỏi đích

3. Service điều phối import tài liệu
Ví dụ:
- QuestionDocumentImportService
- nhiệm vụ: nhận file upload, xác định loại file, gọi parser phù hợp, gọi validator, trả dữ liệu preview

4. Parser theo từng loại file
Ví dụ:
- DocxQuestionParser
- PdfQuestionParser
- TxtQuestionParser (chỉ cần chuẩn bị kiến trúc, chưa cần hoàn chỉnh nếu chưa làm)
- các parser phải cùng trả về 1 định dạng dữ liệu chung

5. Service validate dữ liệu sau parse
Ví dụ:
- ParsedQuestionValidator

6. Service import vào database
Ví dụ:
- QuestionImportPersistenceService

7. View / Blade
- màn hình upload file
- màn hình preview dữ liệu parse
- thông báo thành công / lỗi

Yêu cầu quan trọng:
- không nhồi toàn bộ logic vào controller
- parser phải tách riêng theo từng loại file
- validator phải tách riêng khỏi parser
- logic lưu DB phải tách riêng khỏi parser

==============================
5. FLOW NGHIỆP VỤ MONG MUỐN
==============================

Flow tổng thể của chức năng như sau:

Bước 1:
- admin vào chức năng import câu hỏi từ tài liệu

Bước 2:
- chọn khóa học / ngân hàng câu hỏi đích / bài kiểm tra đích (tùy theo cấu trúc code hiện tại)
- chọn file upload

Bước 3:
- hệ thống kiểm tra loại file
- xác định parser phù hợp

Bước 4:
- parser đọc file
- tách danh sách câu hỏi
- tách danh sách đáp án
- cố gắng xác định đáp án đúng

Bước 5:
- dữ liệu parse được đưa qua bước validate
- xác định:
  - câu hợp lệ
  - câu lỗi
  - câu trùng
  - câu chưa xác định đáp án đúng

Bước 6:
- hiển thị preview kết quả cho admin

Bước 7:
- admin xác nhận import

Bước 8:
- hệ thống chỉ lưu các câu hợp lệ hoặc theo rule phù hợp với code hiện tại
- lưu vào DB bằng transaction
- trả thống kê import thành công / thất bại

==============================
6. CÁC LOẠI FILE CẦN HỖ TRỢ
==============================

Ưu tiên hỗ trợ tốt trước cho:

A. File Word `.docx`
B. File PDF `.pdf` dạng text-based

Kiến trúc phải mở để sau này thêm:
- `.txt`
- `.xlsx`

Yêu cầu:
- controller và service điều phối phải không phụ thuộc cứng vào 1 loại file
- parser nào cũng phải trả về cùng 1 cấu trúc dữ liệu đầu ra

==============================
7. ĐỊNH DẠNG CÂU HỎI CẦN NHẬN DIỆN
==============================

Ưu tiên xử lý tốt dạng phổ biến như sau:

1. Quản lý dự án là gì?
A. Đáp án 1
B. Đáp án 2
C. Đáp án 3
D. Đáp án 4

2. Dự án công nghệ thông tin là gì?
A. ...
B. ...
C. ...
D. ...

Các mẫu cần hỗ trợ:
- câu hỏi bắt đầu bằng số thứ tự:
  - 1.
  - 2.
  - 10.
  - 35.
- đáp án bắt đầu bằng:
  - A.
  - B.
  - C.
  - D.
hoặc
  - A)
  - B)
  - C)
  - D)

Trường hợp nội dung:
- câu hỏi có thể nhiều dòng
- đáp án có thể nhiều dòng
- có khoảng trắng thừa
- có dấu xuống dòng không đều
- có ký tự phụ

==============================
8. QUY TẮC XÁC ĐỊNH ĐÁP ÁN ĐÚNG
==============================

Hệ thống phải cố gắng xác định đáp án đúng nếu có thể.

Ưu tiên nhận diện theo các trường hợp sau:

1. File Word:
- đáp án đúng được highlight màu
- đáp án đúng được in đậm
- đáp án đúng có ký hiệu đặc biệt như `*`
- hoặc quy ước rõ ràng khác nếu phát hiện từ file thực tế

2. File PDF:
- nếu parse text còn giữ được quy ước ký hiệu thì nhận diện
- nếu PDF không giữ được format highlight/bold thì có thể không xác định được đáp án đúng

Nếu không xác định được đáp án đúng:
- vẫn parse câu hỏi và đáp án
- gắn trạng thái cần kiểm tra thủ công
- không làm hỏng toàn bộ file

Tuyệt đối:
- không ép hệ thống lưu đáp án đúng theo A/B/C/D cố định
- phải xác định đúng bản ghi đáp án nào là đúng khi lưu DB

==============================
9. CẤU TRÚC DỮ LIỆU CHUNG SAU KHI PARSE
==============================

Tất cả parser phải trả về cùng 1 format dữ liệu chuẩn, ví dụ:

[
  {
    "so_thu_tu": 1,
    "noi_dung": "Quản lý dự án là gì?",
    "loai": "trac_nghiem",
    "dap_an": [
      {
        "thu_tu_hien_thi": "A",
        "noi_dung": "....",
        "is_correct": true
      },
      {
        "thu_tu_hien_thi": "B",
        "noi_dung": "....",
        "is_correct": false
      },
      {
        "thu_tu_hien_thi": "C",
        "noi_dung": "....",
        "is_correct": false
      },
      {
        "thu_tu_hien_thi": "D",
        "noi_dung": "....",
        "is_correct": false
      }
    ],
    "trang_thai_parse": "hop_le",
    "ghi_chu_loi": null,
    "nguon_file": "docx"
  }
]

Lưu ý:
- `thu_tu_hien_thi` chỉ phục vụ nhận diện từ file gốc
- không được phụ thuộc vào nó khi lưu logic đáp án đúng trong DB
- dữ liệu parse phải đủ để sau này trộn đáp án khi làm bài

==============================
10. QUY TẮC VALIDATE SAU KHI PARSE
==============================

Sau khi parse xong, phải validate từng câu.

Một câu trắc nghiệm hợp lệ tối thiểu cần:
- nội dung câu hỏi không rỗng
- có ít nhất 2 đáp án
- ưu tiên đúng 4 đáp án
- các đáp án không rỗng
- không có đáp án trùng nhau trong cùng 1 câu
- có đúng 1 đáp án đúng nếu muốn import thẳng
- nếu chưa xác định được đáp án đúng thì phải đánh dấu trạng thái riêng

Các trạng thái cần phân loại rõ:
- hop_le
- thieu_noi_dung
- thieu_dap_an
- it_hon_so_dap_an_toi_thieu
- nhieu_hon_mot_dap_an_dung
- khong_xac_dinh_dap_an_dung
- trung_trong_file
- trung_trong_he_thong
- sai_dinh_dang
- khong_ho_tro_loai_cau_hoi

Không được làm crash toàn bộ file chỉ vì một vài câu sai.

==============================
11. KIỂM TRA TRÙNG LẶP
==============================

Khi import, hệ thống phải kiểm tra:
1. trùng câu hỏi trong chính file đang upload
2. trùng câu hỏi với dữ liệu đã có trong database

Quy tắc chuẩn hóa trước khi so sánh:
- trim khoảng trắng đầu/cuối
- gộp nhiều khoảng trắng liên tiếp thành 1
- loại bỏ xuống dòng dư
- có thể chuyển lowercase để so sánh
- không so sánh theo định dạng trình bày

Nếu phát hiện trùng:
- đánh dấu trạng thái
- hiển thị rõ trong preview
- cho phép bỏ qua các câu trùng theo rule phù hợp

==============================
12. YÊU CẦU RIÊNG CHO FILE WORD .DOCX
==============================

Đối với file Word:
- chọn package đọc `.docx` phù hợp trong Laravel/PHP
- parser phải đọc được paragraph/run
- cố gắng giữ thông tin style khi cần nhận diện đáp án đúng
- nhận diện:
  - câu hỏi theo số thứ tự
  - đáp án theo A/B/C/D
  - nội dung nhiều dòng
  - đáp án đúng theo highlight/bold/ký hiệu

Yêu cầu:
- tách riêng parser Word thành class riêng
- có comment chỗ khó
- nếu gặp câu không parse được thì gắn lỗi rõ ràng

==============================
13. YÊU CẦU RIÊNG CHO FILE PDF .PDF
==============================

Đối với file PDF:
- chọn thư viện đọc PDF phù hợp trong PHP/Laravel
- ưu tiên hỗ trợ **PDF dạng text**
- parser PDF phải nhận diện được:
  - câu hỏi theo số thứ tự
  - đáp án A/B/C/D nếu text còn rõ

Lưu ý rất quan trọng:
- PDF thường mất format highlight/bold
- PDF scan/image-only rất khó parse chính xác
- nếu PDF là scan/image-only và hệ thống chưa có OCR ổn định thì phải báo rõ là chưa hỗ trợ hoặc giới hạn hỗ trợ
- không được giả vờ parse đúng nếu dữ liệu không đáng tin

Nếu PDF không xác định được đáp án đúng:
- vẫn parse câu hỏi / đáp án nếu có thể
- gắn trạng thái cần kiểm tra

==============================
14. GIAO DIỆN NGƯỜI DÙNG
==============================

Cần có ít nhất 2 màn hình:

MÀN HÌNH 1: UPLOAD FILE
- chọn khóa học / ngân hàng câu hỏi / bài kiểm tra đích
- chọn file upload
- chấp nhận .docx và .pdf
- nút “Phân tích file”

MÀN HÌNH 2: PREVIEW KẾT QUẢ
Hiển thị:
- tổng số câu đọc được
- số câu hợp lệ
- số câu lỗi
- số câu trùng
- số câu chưa xác định đáp án đúng

Mỗi câu hiển thị:
- số thứ tự
- nội dung câu hỏi
- danh sách đáp án
- đáp án được xác định là đúng nếu có
- trạng thái
- ghi chú lỗi

Các hành động:
- quay lại
- import các câu hợp lệ
- hủy

Giao diện phải đồng bộ với admin panel hiện tại.

==============================
15. XỬ LÝ IMPORT DATABASE
==============================

Khi admin xác nhận import:
- chỉ import các câu hợp lệ theo rule phù hợp
- lưu vào bảng câu hỏi
- lưu vào bảng đáp án
- đánh dấu đúng bản ghi đáp án đúng
- gắn với khóa học / bài kiểm tra / ngân hàng câu hỏi tương ứng
- dùng transaction để tránh lưu dở dang

Yêu cầu:
- không lưu logic đáp án đúng phụ thuộc A/B/C/D
- phải tương thích với chức năng trộn đáp án khi học viên làm bài
- nếu hệ thống đã có rule import Excel tốt thì tận dụng lại phần validate / persistence nếu phù hợp

==============================
16. XỬ LÝ LỖI
==============================

Hệ thống phải xử lý tốt các lỗi sau:
- file không đúng định dạng
- file rỗng
- file hỏng
- không đọc được file
- parser lỗi
- file sai cấu trúc
- câu hỏi thiếu đáp án
- không xác định được đáp án đúng
- nhiều đáp án đúng
- câu hỏi trùng
- lỗi lưu database

Yêu cầu:
- hiển thị lỗi thân thiện cho admin
- không lộ stack trace ra giao diện
- có log kỹ thuật phù hợp để debug

==============================
17. PHÂN CHIA TRIỂN KHAI THEO PHASE
==============================

Hãy triển khai theo đúng thứ tự, không nhảy cóc:

PHASE 1:
- đọc code hiện tại
- phân tích phần câu hỏi / đáp án / import Excel / admin UI
- đề xuất kiến trúc import nhiều loại file

PHASE 2:
- tạo route
- tạo request validate upload
- tạo controller khung
- tạo giao diện upload file
- tạo service điều phối parser

PHASE 3:
- xây DocxQuestionParser hoàn chỉnh

PHASE 4:
- xây PdfQuestionParser cho PDF dạng text
- báo rõ giới hạn với PDF scan

PHASE 5:
- xây service validate dữ liệu sau parse
- kiểm tra trùng trong file và trong DB

PHASE 6:
- xây giao diện preview kết quả parse

PHASE 7:
- xây service import dữ liệu vào database bằng transaction

PHASE 8:
- hoàn thiện xử lý lỗi, thông báo, log

PHASE 9:
- test thủ công toàn bộ flow
- đảm bảo không làm hỏng import Excel đang có

==============================
18. YÊU CẦU CODE
==============================

Code phải:
- sạch
- dễ hiểu
- chia lớp rõ ràng
- không nhồi logic vào controller
- bám style code hiện tại của project
- có comment ở các đoạn parser khó
- không phá chức năng cũ
- ưu tiên tái sử dụng phần validate/import nếu project đã có

==============================
19. ĐẦU RA TÔI MUỐN
==============================

Tôi muốn bạn trả ra đầy đủ theo từng phase:

PHẦN A - PHÂN TÍCH
- code hiện tại liên quan
- schema liên quan
- flow hiện tại
- điểm có thể tái sử dụng

PHẦN B - THIẾT KẾ
- kiến trúc tổng thể
- các class/service/request/view cần thêm hoặc sửa
- luồng dữ liệu từ upload đến import

PHẦN C - TRIỂN KHAI CODE
- route
- controller
- request
- service điều phối
- parser docx
- parser pdf
- validator
- persistence service
- blade view upload/preview

PHẦN D - VALIDATE & TRÙNG LẶP
- các rule kiểm tra dữ liệu parse
- cách phát hiện trùng trong file và trong DB

PHẦN E - XỬ LÝ LỖI
- lỗi người dùng
- lỗi kỹ thuật
- giới hạn loại file

PHẦN F - TEST
- checklist test thủ công
- phần nào đã hoàn thiện
- phần nào còn giới hạn

==============================
20. CÁCH LÀM VIỆC
==============================

Hãy làm theo từng phase.

Xong mỗi phase phải báo rõ:
- đã làm gì
- file nào được tạo hoặc sửa
- luồng nào đã chạy được
- phần nào chưa làm
- rủi ro còn lại

Không nhảy sang phase sau nếu phase hiện tại chưa rõ ràng.

Ưu tiên hoàn thiện tốt theo thứ tự:
1. docx
2. pdf dạng text
3. preview + validate + import database

==============================
21. YÊU CẦU CUỐI
==============================

Hãy bắt đầu bằng việc đọc code thật của project hiện tại liên quan đến:
- ngân hàng câu hỏi
- đáp án câu hỏi
- bài kiểm tra
- import Excel
- giao diện admin tương ứng

Sau đó đề xuất kiến trúc tích hợp chức năng import từ nhiều loại file sao cho phù hợp nhất với project hiện tại.

Không được tự tưởng tượng schema. Không được code mù. Phải bám vào code thực tế của project.