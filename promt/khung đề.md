Bạn là senior Laravel developer + system analyst + exam workflow architect.

Tôi muốn bạn đọc code thật của project Laravel hiện tại và xây dựng / chuẩn hóa hoàn chỉnh chức năng **TẠO BÀI KIỂM TRA ONLINE** cho hệ thống học tập và kiểm tra online.

==================================================
1. MỤC TIÊU CHỨC NĂNG
==================================================

Tôi muốn hệ thống có chức năng tạo bài kiểm tra cho giảng viên, trong đó bài kiểm tra có thể thuộc một trong các loại / phạm vi sau:

1. Kiểm tra theo buổi học
2. Kiểm tra cuối module
3. Kiểm tra toàn khóa

Giảng viên phải có thể:
- tạo khung bài kiểm tra
- chọn câu hỏi từ ngân hàng câu hỏi thuộc khóa học đó
- hoặc import câu hỏi từ file ngoài giống chức năng import của ngân hàng câu hỏi
- cấu hình thời gian mở/đóng
- cấu hình thời gian làm bài
- cấu hình số lần được làm
- cấu hình cách chia điểm:
  - chọn gói điểm tự động
  - hoặc nhập điểm thủ công từng câu
- gửi duyệt bài kiểm tra
- sau khi được duyệt thì admin có thể phát hành

==================================================
2. YÊU CẦU CỰC KỲ QUAN TRỌNG
==================================================

- Không được code mù.
- Phải đọc code thật của repo hiện tại trước khi sửa/làm.
- Phải đọc kỹ tối thiểu các phần:
  - `App\Http\Controllers\GiangVien\BaiKiemTraController`
  - `App\Http\Controllers\Admin\BaiKiemTraPheDuyetController`
  - `App\Http\Controllers\Admin\NganHangCauHoiController`
  - model `BaiKiemTra`
  - model `NganHangCauHoi`
  - model `DapAnCauHoi`
  - model `LichHoc`
  - model `ModuleHoc`
  - model `KhoaHoc`
  - model chi tiết bài kiểm tra nếu có
  - model bài làm / chi tiết bài làm nếu có
  - view tạo/sửa bài kiểm tra
  - route giảng viên và admin liên quan bài kiểm tra
- Không được phá flow hiện tại nếu repo đã có nền.
- Nếu repo đã có các field/trạng thái liên quan, phải tận dụng trước.
- Không thêm schema thừa nếu logic hiện tại đã đủ.

==================================================
3. MỤC TIÊU NGHIỆP VỤ
==================================================

Chức năng tạo bài kiểm tra phải hỗ trợ đúng các trường hợp sau:

A. KIỂM TRA THEO BUỔI HỌC
- bài kiểm tra gắn với một `lich_hoc_id`
- dùng để kiểm tra sau một buổi học cụ thể

B. KIỂM TRA CUỐI MODULE
- bài kiểm tra gắn với một `module_hoc_id`
- dùng để đánh giá sau khi học xong module

C. KIỂM TRA TOÀN KHÓA
- bài kiểm tra gắn với `khoa_hoc_id`
- dùng để đánh giá cuối khóa

==================================================
4. NGUỒN CÂU HỎI CỦA BÀI KIỂM TRA
==================================================

Bài kiểm tra phải có thể lấy câu hỏi theo 2 hướng:

------------------------------------------
4.1. CHỌN TỪ NGÂN HÀNG CÂU HỎI
------------------------------------------

Giảng viên chọn câu hỏi từ ngân hàng câu hỏi.

Rule:
- chỉ lấy câu hỏi thuộc đúng khóa học
- nếu là kiểm tra theo buổi/module thì ưu tiên hoặc giới hạn theo module nếu phù hợp với code hiện tại
- câu hỏi phải ở trạng thái hợp lệ / sẵn sàng
- chỉ cho dùng các câu hỏi đúng phạm vi hiện tại

------------------------------------------
4.2. THÊM CÂU HỎI TỪ FILE NGOÀI
------------------------------------------

Ngoài việc chọn từ ngân hàng có sẵn, tôi muốn giảng viên có thể thêm câu hỏi từ file ngoài, theo hướng giống chức năng import của ngân hàng câu hỏi.

Yêu cầu:
- có thể upload file mẫu import
- parse file
- preview câu hỏi
- sau đó:
  - hoặc lưu vào ngân hàng câu hỏi trước
  - hoặc dùng để đưa thẳng vào bài kiểm tra nếu thiết kế hiện tại cho phép
- phải bám đúng flow hiện tại của hệ thống, nhưng ưu tiên an toàn dữ liệu

Nếu cần chọn một hướng an toàn nhất, ưu tiên:
- import file ngoài
- preview
- lưu vào ngân hàng câu hỏi của khóa học đó
- sau đó giảng viên chọn câu hỏi vừa import để ghép vào bài kiểm tra

Lý do:
- tránh tạo ra 2 nguồn câu hỏi rời rạc
- dữ liệu dễ quản lý hơn

==================================================
5. CÁC CHỨC NĂNG CẦN CÓ CỦA BÀI KIỂM TRA
==================================================

Tôi muốn chức năng tạo bài kiểm tra có đầy đủ các phần sau:

1. Tạo khung bài kiểm tra
2. Chọn phạm vi bài kiểm tra
3. Chọn câu hỏi từ ngân hàng
4. Import câu hỏi từ file ngoài
5. Preview danh sách câu hỏi đã chọn
6. Thiết lập cách chia điểm
7. Chỉnh điểm từng câu nếu dùng thủ công
8. Thiết lập tổng điểm
9. Thiết lập thời gian làm bài
10. Thiết lập thời gian mở / đóng
11. Thiết lập số lần được làm
12. Thiết lập xáo trộn câu hỏi
13. Nếu phù hợp, hỗ trợ xáo trộn đáp án
14. Lưu nháp bài kiểm tra
15. Gửi duyệt bài kiểm tra
16. Admin duyệt / từ chối
17. Admin phát hành / đóng bài kiểm tra
18. Giảng viên xem lịch sử bài kiểm tra đã tạo
19. Giảng viên xem bài làm và chấm điểm nếu có câu tự luận

==================================================
6. THÔNG TIN CẦN NHẬP KHI TẠO BÀI KIỂM TRA
==================================================

Khi tạo bài kiểm tra, giảng viên cần nhập tối thiểu:

- khóa học
- loại / phạm vi bài kiểm tra:
  - buoi_hoc
  - module
  - cuoi_khoa
- module học (nếu là theo module hoặc buổi học)
- buổi học (nếu là theo buổi học)
- tiêu đề bài kiểm tra
- mô tả
- thời gian làm bài (phút)
- ngày mở
- ngày đóng
- số lần được làm
- randomize câu hỏi
- nếu phù hợp:
  - randomize đáp án
- danh sách câu hỏi được chọn
- cách chia điểm:
  - gói điểm tự động
  - thủ công từng câu

==================================================
7. CHỨC NĂNG CHIA ĐIỂM MỚI CẦN THÊM
==================================================

Tôi muốn phần tạo bài kiểm tra có 2 cách setup điểm:

------------------------------------------
7.1. CÁCH 1 - GÓI ĐIỂM TỰ ĐỘNG
------------------------------------------

Giảng viên có thể chọn một “gói điểm” để hệ thống tự chia điểm.

Ý nghĩa:
- giảng viên chọn tổng điểm của đề
- chọn số câu muốn dùng
- hệ thống tự chia điểm cho từng câu

Ví dụ:
- tổng điểm = 10
- số câu = 20
=> mỗi câu 0.5 điểm

Ví dụ:
- tổng điểm = 30
- số câu = 30
=> mỗi câu 1 điểm

Ví dụ:
- tổng điểm = 10
- số câu = 3
=> hệ thống phải xử lý chia điểm hợp lý

Yêu cầu:
- hệ thống phải có cơ chế chia điểm tự động
- nếu tổng điểm chia không đều, phải có rule rõ ràng

Gợi ý rule chia điểm:
1. Ưu tiên chia đều cho tất cả câu
2. Nếu ra số lẻ:
   - làm tròn theo 2 chữ số thập phân
   - và điều chỉnh câu cuối để tổng điểm khớp chính xác
3. Tổng điểm cuối cùng của toàn đề phải luôn đúng bằng tổng điểm giảng viên chọn

Ví dụ:
- tổng điểm = 10
- số câu = 3
- chia đều 3.33, 3.33, 3.34

Tôi muốn agent xử lý đúng logic này, không để tổng điểm lệch.

Ngoài ra:
- giảng viên có thể chọn số câu từ danh sách đã tick
- hoặc hệ thống tự giới hạn số câu được chọn đúng bằng số câu của gói điểm
- nếu chọn quá số câu cho gói, hệ thống phải báo lỗi hoặc yêu cầu điều chỉnh

------------------------------------------
7.2. CÁCH 2 - SETUP ĐIỂM THỦ CÔNG
------------------------------------------

Nếu không dùng gói điểm, giảng viên có thể nhập điểm từng câu thủ công.

Yêu cầu:
- mỗi câu có ô nhập điểm
- hệ thống cộng tổng điểm tự động
- tổng điểm hiển thị realtime
- validate điểm từng câu hợp lệ
- không cho tổng điểm rỗng hoặc âm

------------------------------------------
7.3. GIAO DIỆN CHỌN CÁCH CHIA ĐIỂM
------------------------------------------

Tôi muốn ở màn hình tạo/sửa đề có lựa chọn:

- `che_do_tinh_diem = goi_diem`
- `che_do_tinh_diem = thu_cong`

Nếu chọn `goi_diem`:
- hiện các trường:
  - tổng điểm
  - số câu
  - nút áp dụng chia điểm tự động

Nếu chọn `thu_cong`:
- hiện cột nhập điểm từng câu

------------------------------------------
7.4. GỢI Ý GÓI ĐIỂM
------------------------------------------

Nếu phù hợp, có thể hiển thị nhanh một số gợi ý gói điểm phổ biến:
- 10 điểm / 10 câu
- 10 điểm / 20 câu
- 10 điểm / 40 câu
- 30 điểm / 30 câu
- 100 điểm / 50 câu

Nhưng không bắt buộc hard-code nếu làm rối.
Quan trọng là phải có cơ chế:
- nhập tổng điểm
- nhập số câu
- tự chia điểm

==================================================
8. RULE NGHIỆP VỤ CHỌN PHẠM VI
==================================================

------------------------------------------
8.1. Nếu là kiểm tra theo buổi học
------------------------------------------
- bắt buộc chọn khóa học
- bắt buộc chọn module tương ứng
- bắt buộc chọn `lich_hoc_id`
- câu hỏi nên ưu tiên từ module của buổi học đó

------------------------------------------
8.2. Nếu là kiểm tra cuối module
------------------------------------------
- bắt buộc chọn khóa học
- bắt buộc chọn module
- không cần `lich_hoc_id`
- câu hỏi nên lấy trong phạm vi module đó

------------------------------------------
8.3. Nếu là kiểm tra toàn khóa
------------------------------------------
- chỉ cần khóa học
- không bắt buộc gắn module
- câu hỏi có thể lấy từ toàn bộ ngân hàng câu hỏi thuộc khóa đó

==================================================
9. RULE NGHIỆP VỤ CHỌN CÂU HỎI
==================================================

Khi giảng viên chọn câu hỏi từ ngân hàng:
- hệ thống chỉ cho thấy câu hỏi phù hợp phạm vi hiện tại
- phải có filter tìm kiếm:
  - theo nội dung
  - theo module
  - theo loại câu hỏi
  - theo mức độ
- phải có checkbox hoặc cơ chế chọn nhiều câu
- phải hiển thị trước:
  - nội dung câu hỏi
  - loại câu hỏi
  - module
  - điểm mặc định
  - trạng thái

Khi thêm câu hỏi vào đề:
- hệ thống lưu vào chi tiết bài kiểm tra
- cho phép chỉnh điểm từng câu
- cho phép bỏ câu hỏi khỏi đề

Nếu dùng `goi_diem`:
- số câu được chọn phải khớp hoặc không vượt số câu trong gói theo rule bạn chọn
- phải báo lỗi rõ ràng nếu không khớp

==================================================
10. RULE NGHIỆP VỤ IMPORT CÂU HỎI TỪ FILE NGOÀI
==================================================

Tôi muốn flow import trong bài kiểm tra bám gần giống ngân hàng câu hỏi.

Flow mong muốn:
1. Giảng viên ở màn tạo/sửa đề
2. Có nút “Import câu hỏi từ file”
3. Upload file
4. Hệ thống parse và preview
5. Hệ thống kiểm tra:
   - câu hỏi hợp lệ
   - đáp án hợp lệ
   - không trùng quá mức nếu có rule
6. Sau preview:
   - lưu vào ngân hàng câu hỏi của khóa học
   - hoặc cho chọn ngay các câu vừa import vào đề

Nếu code hiện tại phù hợp hơn, hãy chọn phương án:
- import → lưu ngân hàng câu hỏi → reload danh sách câu hỏi → giảng viên tick chọn

Ưu tiên:
- nhất quán với hệ thống hiện tại
- tránh sinh ra luồng phụ quá phức tạp

==================================================
11. FLOW NGHIỆP VỤ CHI TIẾT
==================================================

------------------------------------------
11.1. FLOW TẠO KHUNG BÀI KIỂM TRA
------------------------------------------

1. Giảng viên vào khu vực bài kiểm tra
2. Chọn “Tạo bài kiểm tra”
3. Nhập:
   - khóa học
   - phạm vi
   - module/buổi nếu cần
   - tiêu đề
   - mô tả
   - thời gian làm bài
4. Hệ thống kiểm tra:
   - giảng viên có được phân công đúng phạm vi đó không
5. Nếu hợp lệ:
   - tạo bài kiểm tra ở trạng thái nháp
6. Chuyển sang màn chỉnh sửa đề

------------------------------------------
11.2. FLOW CHỌN CÂU HỎI TỪ NGÂN HÀNG
------------------------------------------

1. Giảng viên vào màn sửa đề
2. Hệ thống load câu hỏi thuộc đúng khóa học
3. Nếu là theo module/buổi:
   - ưu tiên hoặc giới hạn câu hỏi trong module đó
4. Giảng viên tick chọn câu hỏi
5. Chọn cách chia điểm:
   - gói điểm
   - thủ công
6. Nếu chọn gói điểm:
   - nhập tổng điểm
   - nhập số câu
   - hệ thống chia điểm tự động
7. Nếu chọn thủ công:
   - nhập điểm từng câu
8. Lưu cấu hình đề

------------------------------------------
11.3. FLOW IMPORT FILE NGOÀI
------------------------------------------

1. Giảng viên bấm “Import câu hỏi từ file”
2. Chọn file
3. Hệ thống parse
4. Preview kết quả
5. Nếu hợp lệ:
   - lưu vào ngân hàng câu hỏi của khóa học
   - cho phép sử dụng ngay trong bài kiểm tra
6. Giảng viên chọn các câu muốn đưa vào đề

------------------------------------------
11.4. FLOW CẤU HÌNH THỜI GIAN
------------------------------------------

Giảng viên cấu hình:
- thời gian làm bài
- ngày mở
- ngày đóng
- số lần được làm
- xáo trộn câu hỏi
- xáo trộn đáp án nếu hỗ trợ

Hệ thống kiểm tra:
- ngày đóng phải sau ngày mở
- thời gian làm bài hợp lệ
- số lần làm hợp lệ

------------------------------------------
11.5. FLOW GỬI DUYỆT
------------------------------------------

1. Giảng viên bấm “Gửi duyệt”
2. Hệ thống kiểm tra:
   - có ít nhất câu hỏi hoặc có mô tả hợp lệ
   - cấu hình bài kiểm tra đầy đủ
   - nếu dùng gói điểm thì tổng điểm khớp
   - nếu dùng thủ công thì tổng điểm hợp lệ
3. Nếu hợp lệ:
   - chuyển `trang_thai_duyet = cho_duyet`
4. Admin sẽ duyệt tiếp

------------------------------------------
11.6. FLOW ADMIN DUYỆT / PHÁT HÀNH
------------------------------------------

1. Admin vào danh sách bài kiểm tra chờ duyệt
2. Xem chi tiết đề
3. Duyệt hoặc từ chối
4. Nếu duyệt:
   - có thể phát hành
5. Nếu phát hành:
   - học viên thấy đề trong danh sách

------------------------------------------
11.7. FLOW HỌC VIÊN LÀM BÀI
------------------------------------------

1. Học viên xem bài kiểm tra đã phát hành
2. Bắt đầu làm bài
3. Hệ thống ghi nhận bài làm
4. Học viên nộp bài
5. Hệ thống chấm phần tự động nếu có
6. Chuyển phần tự luận cho giảng viên chấm

------------------------------------------
11.8. FLOW GIẢNG VIÊN CHẤM ĐIỂM
------------------------------------------

1. Giảng viên vào danh sách bài làm chờ chấm
2. Mở bài làm
3. Chấm các câu tự luận
4. Nhập nhận xét
5. Lưu điểm
6. Hệ thống cập nhật kết quả học tập

==================================================
12. YÊU CẦU GIAO DIỆN
==================================================

Tôi muốn giao diện tạo bài kiểm tra rõ ràng, dễ dùng.

Màn hình nên có các khu:

A. THÔNG TIN CHUNG
- khóa học
- phạm vi
- module
- buổi học
- tiêu đề
- mô tả

B. THỜI GIAN & CẤU HÌNH
- thời gian làm bài
- ngày mở
- ngày đóng
- số lần làm
- randomize câu hỏi
- randomize đáp án nếu có

C. NGUỒN CÂU HỎI
- tab 1: chọn từ ngân hàng câu hỏi
- tab 2: import từ file ngoài

D. THIẾT LẬP ĐIỂM
- chế độ tính điểm:
  - gói điểm
  - thủ công
- nếu gói điểm:
  - tổng điểm
  - số câu
  - nút áp dụng chia điểm
- nếu thủ công:
  - cột nhập điểm từng câu

E. DANH SÁCH CÂU HỎI ĐÃ CHỌN
- thứ tự
- nội dung
- điểm
- loại câu hỏi
- nút xóa khỏi đề

F. HÀNH ĐỘNG
- lưu nháp
- gửi duyệt
- quay lại

==================================================
13. NHỮNG GÌ CẦN KIỂM TRA VÀ TẬN DỤNG
==================================================

Bắt buộc phải kiểm tra repo hiện tại xem đã có gì để tận dụng:
- `BaiKiemTraController`
- query lấy câu hỏi khả dụng
- `syncExamQuestions()`
- logic randomize_questions
- logic submitForApproval()
- admin duyệt / publish
- import câu hỏi ở ngân hàng câu hỏi
- các model chi tiết đề / bài làm / chấm điểm

Không được viết lại trùng nếu repo đã có nền phù hợp.

==================================================
14. KIẾN TRÚC CODE MONG MUỐN
==================================================

Không được nhồi hết logic vào controller.

Ưu tiên tách:
- service build danh sách câu hỏi khả dụng
- service import câu hỏi từ file ngoài vào ngân hàng câu hỏi
- service sync câu hỏi vào đề
- service validate cấu hình đề
- service chia điểm theo gói
- service preview import nếu cần

Ví dụ:
- `ExamQuestionSelectionService`
- `ExamQuestionImportService`
- `ExamConfigurationService`
- `ExamScoringPackageService`

Tên có thể khác, nhưng phải sạch và dễ bảo trì.

==================================================
15. CHIA THEO PHASE
==================================================

PHASE 1:
- đọc code hiện tại
- phân tích flow bài kiểm tra hiện có
- xác định phần nào đã có, phần nào còn thiếu
- đề xuất hướng tích hợp an toàn nhất

PHASE 2:
- chuẩn hóa form tạo bài kiểm tra:
  - buổi học
  - cuối module
  - toàn khóa
- chuẩn hóa rule validate

PHASE 3:
- chuẩn hóa chọn câu hỏi từ ngân hàng câu hỏi
- filter đúng theo khóa học / module / phạm vi

PHASE 4:
- thêm flow import câu hỏi từ file ngoài vào bài kiểm tra
- bám theo logic import của ngân hàng câu hỏi

PHASE 5:
- thêm chức năng chia điểm:
  - gói điểm tự động
  - thủ công từng câu
- xử lý rule chia điểm và làm tròn

PHASE 6:
- hoàn thiện setup thời gian, số lần làm, randomize
- hoàn thiện lưu nháp / gửi duyệt

PHASE 7:
- kiểm tra admin duyệt / phát hành
- kiểm tra học viên làm bài / giảng viên chấm điểm không bị gãy

==================================================
16. ĐẦU RA TÔI MUỐN
==================================================

Tôi muốn bạn trả ra theo format sau:

PHẦN A - PHÂN TÍCH HIỆN TRẠNG
- flow tạo bài kiểm tra hiện tại
- file nào liên quan
- phần nào đã có
- phần nào cần mở rộng

PHẦN B - THIẾT KẾ NGHIỆP VỤ
- flow tạo bài kiểm tra theo buổi học
- flow tạo bài kiểm tra cuối module
- flow tạo bài kiểm tra toàn khóa
- flow chọn câu hỏi từ ngân hàng
- flow import file ngoài
- flow chia điểm theo gói
- flow setup điểm thủ công
- flow setup thời gian / gửi duyệt / phát hành

PHẦN C - THIẾT KẾ KỸ THUẬT
- controller/model/service/view cần sửa hoặc thêm
- logic nào tận dụng
- logic nào cần viết mới

PHẦN D - TRIỂN KHAI CODE
- code theo từng phase
- giải thích file nào được sửa / thêm

PHẦN E - TEST
- test tạo đề theo buổi học
- test tạo đề cuối module
- test tạo đề toàn khóa
- test chọn câu hỏi từ ngân hàng
- test import file ngoài
- test chia điểm theo gói
- test chia điểm thủ công
- test setup thời gian
- test gửi duyệt
- test admin duyệt / phát hành
- test học viên làm bài
- test giảng viên chấm điểm

==================================================
17. YÊU CẦU CUỐI
==================================================

Hãy bắt đầu bằng việc đọc code thật của repo hiện tại rồi mới sửa.

Mục tiêu cuối cùng:
- tạo bài kiểm tra theo buổi học / cuối module / toàn khóa
- câu hỏi lấy từ ngân hàng câu hỏi thuộc khóa đó
- hỗ trợ import câu hỏi từ file ngoài giống hướng ngân hàng câu hỏi
- có setup thời gian và các cấu hình cần thiết
- có thêm chức năng chọn gói điểm hoặc setup điểm thủ công
- nếu chọn gói điểm thì hệ thống tự chia điểm chính xác
- flow bài kiểm tra hoàn chỉnh, không phá các chức năng hiện có

Không được code mù.
Không được bỏ qua flow hiện tại của giảng viên và admin.
Ưu tiên tích hợp chắc chắn với repo đang có.