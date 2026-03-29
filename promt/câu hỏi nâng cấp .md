Bạn là senior Laravel developer + backend architect + document parsing engineer + data import specialist.

Tôi muốn bạn xây dựng chức năng **nhận diện câu hỏi từ file tài liệu (Word/PDF), chuẩn hóa dữ liệu, preview kết quả, rồi cho phép import vào hệ thống hoặc xuất ra file mẫu Excel** cho project Laravel của tôi.

==============================
1. MỤC TIÊU CHÍNH
==============================

Tôi không muốn nhập tay từng câu hỏi.

Tôi muốn hệ thống hỗ trợ:
- upload file Word `.docx`
- upload file PDF `.pdf`
- đọc nội dung file
- tự nhận diện từng câu hỏi
- tự nhận diện các đáp án
- tự cố gắng xác định đáp án đúng nếu trong file có đánh dấu
- chuẩn hóa về format nội bộ thống nhất
- hiển thị preview để kiểm tra
- cho phép:
  1. import thẳng vào hệ thống
  2. hoặc xuất ra file mẫu `.xlsx` đúng chuẩn của hệ thống để kiểm tra/chỉnh sửa rồi import sau

Yêu cầu quan trọng nhất:
- càng ít lỗi càng tốt
- ưu tiên an toàn dữ liệu hơn là cố đoán sai
- khi không chắc, phải đánh dấu lỗi / cần kiểm tra thủ công
- không được tự động import sai đáp án đúng

==============================
2. NGỮ CẢNH HỆ THỐNG
==============================

Đây là project Laravel cho hệ thống học tập và kiểm tra online.

Phần liên quan hiện có trong hệ thống:
- ngân hàng câu hỏi
- đáp án câu hỏi
- bài kiểm tra
- có thể đã có import câu hỏi từ file CSV/TXT/Excel hoặc một phần import cũ
- có giao diện admin quản lý câu hỏi trắc nghiệm

Hãy đọc code thật của project hiện tại trước khi sửa:
- routes
- controller phần câu hỏi / bài kiểm tra / import
- model câu hỏi / đáp án
- view import / preview nếu có
- migration/schema liên quan

Không được code mù.
Không được tự tưởng tượng schema.
Không được phá chức năng import hiện có nếu chưa thay thế hoàn chỉnh.

==============================
3. YÊU CẦU NGHIỆP VỤ
==============================

Chức năng mới phải xử lý được flow sau:

Bước 1:
- admin vào chức năng import câu hỏi từ tài liệu

Bước 2:
- chọn khóa học / ngân hàng câu hỏi / bài kiểm tra đích (theo code hiện tại)
- chọn file upload

Bước 3:
- hệ thống xác định loại file:
  - docx
  - pdf
- gọi parser phù hợp

Bước 4:
- parser đọc nội dung tài liệu
- tách từng câu hỏi
- tách từng đáp án
- cố gắng xác định đáp án đúng

Bước 5:
- dữ liệu parse được chuẩn hóa về format nội bộ chung

Bước 6:
- validate dữ liệu parse
- phân loại:
  - hợp lệ
  - thiếu đáp án
  - không xác định đáp án đúng
  - trùng trong file
  - trùng trong DB
  - sai định dạng
  - không hỗ trợ loại câu hỏi

Bước 7:
- hiển thị preview cho admin kiểm tra

Bước 8:
- cho admin chọn:
  1. import các câu hợp lệ vào DB
  2. hoặc xuất toàn bộ / xuất câu hợp lệ / xuất câu lỗi ra file mẫu `.xlsx`

==============================
4. FORMAT ĐÍCH CẦN CHUẨN HÓA VỀ
==============================

Dù file ngoài có format gì, sau khi parse xong phải chuẩn hóa về format sau:

- cau_hoi
- dap_an_1
- dap_an_2
- dap_an_3
- dap_an_4
- dap_an_dung

Trong đó:
- `dap_an_1..4` là nội dung 4 đáp án
- `dap_an_dung` là **nội dung đáp án đúng**
- KHÔNG lưu `dap_an_dung` là A/B/C/D
- vì hệ thống cần hỗ trợ trộn đáp án khi làm bài

Ví dụ dữ liệu chuẩn hóa:

[
  {
    "so_thu_tu": 1,
    "cau_hoi": "Thủ đô Việt Nam là gì?",
    "dap_an_1": "Hà Nội",
    "dap_an_2": "Huế",
    "dap_an_3": "Đà Nẵng",
    "dap_an_4": "Cần Thơ",
    "dap_an_dung": "Hà Nội",
    "trang_thai": "hop_le",
    "ghi_chu_loi": null,
    "nguon_file": "docx"
  }
]

==============================
5. LOẠI FILE CẦN HỖ TRỢ
==============================

Ưu tiên hỗ trợ tốt trước cho:
1. `.docx`
2. `.pdf` dạng text-based

Thiết kế phải đủ mở để sau này thêm:
- `.txt`
- `.xlsx`
- định dạng khác nếu cần

Yêu cầu:
- dùng parser riêng cho từng loại file
- nhưng tất cả parser đều trả về cùng 1 cấu trúc dữ liệu đầu ra

==============================
6. QUY TẮC NHẬN DIỆN CÂU HỎI
==============================

Parser phải hoạt động theo quy tắc rõ ràng, hạn chế đoán mò.

Một câu hỏi mới thường bắt đầu khi gặp dòng như:
- `1. ...`
- `2. ...`
- `15. ...`
- `90. ...`

Regex gợi ý:
- `^\d+\.\s+`

Yêu cầu:
- nếu đang đọc câu hỏi và gặp một dòng mới khớp regex số thứ tự, bắt đầu 1 câu hỏi mới
- nội dung câu hỏi có thể kéo dài nhiều dòng
- phải nối các dòng liên tiếp cho tới khi gặp đáp án A hoặc câu mới
- phải trim khoảng trắng và chuẩn hóa xuống dòng

Ví dụ phải đọc được:

12. Một giai đoạn trong qui trình quản lý dự án mà
“thực hiện hoàn thành các công việc được xác định trong phần lập kế hoạch...”
được gọi là giai đoạn gì

=> phải gộp thành 1 nội dung câu hỏi hoàn chỉnh

==============================
7. QUY TẮC NHẬN DIỆN ĐÁP ÁN
==============================

Các đáp án thường bắt đầu bằng:
- `A.`
- `B.`
- `C.`
- `D.`
hoặc
- `A)`
- `B)`
- `C)`
- `D)`

Regex gợi ý:
- `^[A-D][\.\)]\s+`

Yêu cầu:
- khi đã vào một câu hỏi, gặp A/B/C/D thì bắt đầu tạo đáp án tương ứng
- đáp án có thể kéo dài nhiều dòng
- nếu dòng tiếp theo không phải là B/C/D hay câu mới thì nối vào đáp án hiện tại
- phải giữ nguyên nội dung đáp án sau khi chuẩn hóa khoảng trắng

Lưu ý:
- phase đầu ưu tiên tốt cho câu hỏi trắc nghiệm 4 đáp án
- nếu câu chỉ có 3 đáp án, hơn 4 đáp án, hoặc loại câu hỏi khác thì phải đánh dấu trạng thái rõ ràng chứ không tự xử lý sai

==============================
8. QUY TẮC NHẬN DIỆN ĐÁP ÁN ĐÚNG
==============================

Đây là phần quan trọng nhất. Hệ thống phải cố gắng xác định đáp án đúng nhưng KHÔNG được đoán bừa.

Ưu tiên hỗ trợ các cách đánh dấu đáp án đúng sau:

A. Với file Word `.docx`
1. đáp án đúng được highlight màu
2. đáp án đúng được in đậm
3. đáp án đúng có ký hiệu `*`
4. có dòng riêng như:
   - `Đáp án: A`
   - `Đáp án đúng: C`
   - `Answer: B`
   - `Đáp án đúng: Hà Nội`

B. Với file PDF `.pdf`
1. nếu text giữ được ký hiệu như `*` hoặc dòng `Đáp án: A` thì nhận diện
2. nếu PDF mất style highlight/bold thì không cố đoán từ style
3. nếu không xác định được thì đánh dấu trạng thái cần kiểm tra

Quy tắc cụ thể:

TRƯỜNG HỢP 1 - Highlight:
- nếu 1 đáp án có style highlight rõ ràng và các đáp án khác không có, coi đó là đáp án đúng

TRƯỜNG HỢP 2 - Bold:
- nếu toàn bộ hoặc phần chính của 1 đáp án được in đậm rõ ràng và các đáp án khác không có, coi đó là đáp án đúng
- nếu nhiều đáp án đều bold hoặc file dùng bold lung tung thì KHÔNG dùng bold để xác định

TRƯỜNG HỢP 3 - Dấu `*`:
- nếu đáp án có `*` đầu hoặc cuối dòng, coi đó là đáp án đúng
- phải loại bỏ `*` khỏi nội dung trước khi lưu

TRƯỜNG HỢP 4 - Dòng đáp án riêng:
- nếu có dòng `Đáp án: A` hoặc tương tự, map sang nội dung của đáp án A
- nếu có dòng `Đáp án đúng: Hà Nội`, so sánh với nội dung các đáp án để tìm đáp án đúng

TRƯỜNG HỢP 5 - Không xác định được:
- KHÔNG gán bừa
- đặt:
  - `dap_an_dung = null`
  - `trang_thai = khong_xac_dinh_dap_an_dung`

==============================
9. ƯU TIÊN GIẢM LỖI
==============================

Để giảm lỗi tối đa, parser phải áp dụng nguyên tắc sau:

1. Ưu tiên “không chắc thì không kết luận”.
2. Nếu có hơn 1 tín hiệu mâu thuẫn về đáp án đúng:
   - ví dụ vừa highlight A vừa có dòng `Đáp án: C`
   - đánh dấu lỗi mâu thuẫn
   - không import thẳng
3. Nếu không đủ 4 đáp án:
   - không ép thành đủ 4
   - đánh dấu lỗi
4. Nếu nội dung đáp án đúng không khớp với 4 đáp án:
   - đánh dấu lỗi
5. Không tự sinh đáp án.
6. Không tự sửa nội dung câu hỏi theo cảm tính.
7. Với PDF scan/image-only:
   - nếu chưa có OCR ổn định, báo rõ chưa hỗ trợ
   - không giả vờ parse đúng

==============================
10. XỬ LÝ PDF
==============================

Đối với PDF:
- chỉ ưu tiên hỗ trợ tốt cho PDF dạng text-based
- dùng thư viện phù hợp để bóc text
- chấp nhận rằng PDF khó hơn docx rất nhiều

Yêu cầu:
1. nếu PDF là text-based:
   - parse câu hỏi và đáp án theo quy tắc text
2. nếu PDF là scan hoặc text quá vỡ:
   - báo giới hạn rõ
   - đưa file vào trạng thái cần xử lý khác
3. không dùng OCR bừa bãi nếu chưa có giải pháp ổn định
4. không import sai chỉ để “cho có kết quả”

==============================
11. CẤU TRÚC KỸ THUẬT MONG MUỐN
==============================

Hãy tổ chức code rõ ràng thành các phần:

1. Controller
- xử lý upload
- gọi service điều phối
- hiển thị preview
- xác nhận import
- xuất file xlsx nếu cần

2. Form Request
- validate file upload
- validate khóa học / mục tiêu import

3. Service điều phối
Ví dụ:
- QuestionDocumentImportService
Nhiệm vụ:
- nhận file
- xác định loại file
- gọi parser phù hợp
- gọi validator
- trả dữ liệu preview

4. Parser riêng theo file
- DocxQuestionParser
- PdfQuestionParser

5. Validator sau parse
- ParsedQuestionValidator

6. Service lưu DB
- QuestionImportPersistenceService

7. Service export Excel
- ParsedQuestionExportService

8. View
- màn upload
- màn preview
- nút import
- nút xuất xlsx

Không được nhồi toàn bộ logic vào controller.

==============================
12. VALIDATE SAU KHI PARSE
==============================

Sau khi parse xong, phải validate từng câu.

Một câu trắc nghiệm hợp lệ cần:
- `cau_hoi` không rỗng
- có đúng 4 đáp án cho flow chuẩn hiện tại
- các đáp án không rỗng
- các đáp án không trùng nhau
- có đúng 1 đáp án đúng
- `dap_an_dung` phải trùng với một trong 4 đáp án

Các trạng thái cần có tối thiểu:
- hop_le
- thieu_cau_hoi
- thieu_dap_an
- khong_du_4_dap_an
- trung_dap_an
- khong_xac_dinh_dap_an_dung
- nhieu_hon_mot_dap_an_dung
- dap_an_dung_khong_khop
- trung_trong_file
- trung_trong_he_thong
- sai_dinh_dang
- khong_ho_tro_pdf_scan

Mỗi câu lỗi phải có `ghi_chu_loi` rõ ràng.

==============================
13. KIỂM TRA TRÙNG
==============================

Phải kiểm tra:
1. trùng trong file hiện tại
2. trùng với ngân hàng câu hỏi đã có trong DB

Chuẩn hóa trước khi so sánh:
- trim
- gộp nhiều khoảng trắng
- bỏ xuống dòng thừa
- lowercase nếu hợp lý

Nếu trùng:
- không crash
- đánh dấu trạng thái
- hiển thị rõ trong preview
- cho phép admin quyết định import hay bỏ qua theo rule phù hợp

==============================
14. GIAO DIỆN PREVIEW
==============================

Cần có màn preview rõ ràng.

Hiển thị:
- tổng số câu đọc được
- số câu hợp lệ
- số câu lỗi
- số câu trùng
- số câu không xác định được đáp án đúng

Mỗi câu hiển thị:
- số thứ tự
- nội dung câu hỏi
- 4 đáp án
- đáp án đúng nếu xác định được
- trạng thái
- ghi chú lỗi

Cần có nút:
- Import các câu hợp lệ
- Xuất ra file mẫu Excel `.xlsx`
- Quay lại

Ưu tiên:
- admin có thể xem nhanh câu nào lỗi
- admin không bị bắt buộc nhập lại từ đầu nếu file parse chưa hoàn hảo

==============================
15. XUẤT RA FILE MẪU XLSX
==============================

Đây là phần rất quan trọng.

Nếu file Word/PDF parse xong chưa đủ sạch để import thẳng, hệ thống phải cho phép xuất ra file `.xlsx` đúng mẫu chuẩn:

- cau_hoi
- dap_an_1
- dap_an_2
- dap_an_3
- dap_an_4
- dap_an_dung

Yêu cầu:
- câu parse được thì đổ vào đúng cột
- câu chưa xác định đáp án đúng thì để trống `dap_an_dung`
- có thể thêm sheet hướng dẫn hoặc sheet lỗi nếu phù hợp
- file export phải dùng lại được cho chức năng import chuẩn của hệ thống

Mục tiêu:
- Word/PDF là nguồn dữ liệu thô
- Excel mẫu là lớp chuẩn hóa trung gian an toàn
- DB là nơi lưu cuối cùng

==============================
16. IMPORT DATABASE
==============================

Khi admin bấm import:
- chỉ import câu hợp lệ
- lưu vào schema hiện tại của project
- nếu hệ thống đã có bảng câu hỏi và bảng đáp án riêng, phải lưu đúng mô hình đó
- không phụ thuộc vào A/B/C/D làm logic đáp án đúng
- phải đánh dấu đúng bản ghi đáp án nào là đúng
- dùng transaction để tránh lưu dở dang

Nếu schema hiện tại của repo còn theo hướng cũ:
- phân tích kỹ rồi đề xuất cách tương thích an toàn
- tránh phá logic đang chạy

==============================
17. XỬ LÝ LỖI
==============================

Hệ thống phải xử lý tốt các lỗi:
- file không đúng định dạng
- file rỗng
- file hỏng
- file không đọc được
- docx có style lạ
- pdf bị vỡ text
- pdf scan không hỗ trợ
- câu thiếu đáp án
- mâu thuẫn dấu hiệu đáp án đúng
- lỗi lưu DB
- lỗi export xlsx

Yêu cầu:
- hiển thị lỗi thân thiện cho admin
- log kỹ thuật rõ ràng để debug
- không lộ stack trace ở giao diện

==============================
18. PHASE TRIỂN KHAI
==============================

Hãy làm theo thứ tự sau:

PHASE 1
- đọc code thật của project liên quan tới ngân hàng câu hỏi / đáp án / import
- phân tích schema và flow hiện tại
- đề xuất kiến trúc tích hợp parser tài liệu

PHASE 2
- tạo route
- tạo request validate upload
- tạo controller khung
- tạo giao diện upload
- tạo service điều phối parser

PHASE 3
- xây DocxQuestionParser hoàn chỉnh, ưu tiên ổn định cao

PHASE 4
- xây PdfQuestionParser cho PDF dạng text
- báo rõ giới hạn với PDF scan

PHASE 5
- xây ParsedQuestionValidator
- kiểm tra trùng trong file và trong DB

PHASE 6
- xây màn preview

PHASE 7
- xây export ra file mẫu `.xlsx`

PHASE 8
- xây import DB từ dữ liệu parse hợp lệ

PHASE 9
- hoàn thiện xử lý lỗi, log, thông báo
- test toàn bộ flow
- đảm bảo không làm hỏng chức năng import hiện có

==============================
19. YÊU CẦU CODE
==============================

Code phải:
- sạch
- dễ hiểu
- ít lỗi
- comment ở chỗ parser khó
- không nhồi logic vào controller
- bám style code hiện tại
- hạn chế tối đa ảnh hưởng chức năng cũ
- ưu tiên sự chắc chắn hơn tốc độ

==============================
20. ĐẦU RA TÔI MUỐN
==============================

Tôi muốn bạn trả ra theo từng phase:

PHẦN A - PHÂN TÍCH
- code hiện tại liên quan
- schema liên quan
- chỗ có thể tái sử dụng
- rủi ro

PHẦN B - THIẾT KẾ
- kiến trúc tổng thể
- class/service/request/view cần thêm hoặc sửa
- flow dữ liệu

PHẦN C - TRIỂN KHAI CODE
- route
- controller
- request
- parser
- validator
- export xlsx
- persistence service
- blade view

PHẦN D - TEST
- checklist test tay
- file test nên chuẩn bị
- trường hợp lỗi cần kiểm tra

PHẦN E - GIỚI HẠN
- phần nào ổn
- phần nào chưa chắc
- loại file nào chưa hỗ trợ tốt

==============================
21. YÊU CẦU CUỐI
==============================

Hãy bắt đầu bằng việc đọc code thật của project hiện tại và tập trung vào phần:
- câu hỏi trắc nghiệm
- ngân hàng câu hỏi
- đáp án
- import hiện có
- giao diện admin tương ứng

Sau đó tích hợp chức năng parser tài liệu theo hướng:
- nhận diện câu hỏi từ Word/PDF
- chuẩn hóa về format mẫu
- preview
- import DB hoặc xuất `.xlsx`

Không được code mù.
Không được đoán schema.
Không được vì muốn “có kết quả” mà import sai dữ liệu.
Ưu tiên an toàn dữ liệu và giảm lỗi parse lên hàng đầu.