Bạn là senior Laravel developer + business flow analyst.

Tôi muốn bạn đọc code thật của project Laravel hiện tại và CHỈNH SỬA lại phần **ngân hàng câu hỏi / thêm câu hỏi** theo hướng tách rõ:

1. Câu hỏi thêm cho **khóa học mẫu**
2. Câu hỏi thêm cho **khóa học hoạt động / khóa học cụ thể**

==================================================
1. BỐI CẢNH NGHIỆP VỤ MỚI
==================================================

Hiện tại trong hệ thống của tôi, phần khóa học đã có phân biệt:
- khóa học mẫu
- khóa học hoạt động / khóa học bình thường

Tôi muốn phần ngân hàng câu hỏi cũng đi theo đúng nghiệp vụ đó.

Tức là khi thêm câu hỏi, giảng viên hoặc admin phải chọn rõ:
- thêm câu hỏi vào **khóa học mẫu**
hoặc
- thêm câu hỏi vào **khóa học hoạt động**

Mục tiêu:
- tách khóa học mẫu ra riêng cho dễ sử dụng
- dễ quản lý ngân hàng câu hỏi theo đúng nghiệp vụ
- tránh giao diện trộn lẫn giữa khóa mẫu và khóa hoạt động
- về sau có thể tái sử dụng câu hỏi tốt hơn

==================================================
2. YÊU CẦU CỰC KỲ QUAN TRỌNG
==================================================

- Không được code mù.
- Phải đọc code thật của repo hiện tại trước khi sửa.
- Phải đọc kỹ tối thiểu các phần:
  - `app/Models/KhoaHoc.php`
  - `app/Models/NganHangCauHoi.php`
  - `app/Http/Controllers/Admin/NganHangCauHoiController.php`
  - form thêm/sửa câu hỏi ở `pages.admin.question-bank.form`
  - index question bank
  - import / preview / confirm import nếu có liên quan
- Phải tận dụng đúng các field/logic đã có trong repo:
  - `khoa_hoc.loai`
  - `khoa_hoc_mau_id`
  - `scopeMau()`
  - `scopeHoatDong()`
  - relation `khoaHocMau()`
  - relation `lopDaMo()`
- Không phá flow hiện tại của câu hỏi:
  - tạo tay
  - sửa
  - import
  - preview
  - confirm import
- Nếu có thể, chỉ mở rộng và chuẩn hóa UI + logic, không thay đổi schema bừa bãi.

==================================================
3. NHỮNG GÌ TÔI MUỐN SAU KHI SỬA
==================================================

Sau khi sửa xong, phần thêm câu hỏi phải hoạt động như sau:

A. Khi vào tạo câu hỏi mới:
- hệ thống hiển thị rõ 2 lựa chọn:
  1. Thêm cho khóa học mẫu
  2. Thêm cho khóa học hoạt động

B. Nếu chọn “khóa học mẫu”:
- dropdown khóa học chỉ hiện các khóa có `loai = mau`

C. Nếu chọn “khóa học hoạt động”:
- dropdown khóa học chỉ hiện các khóa có `loai = hoat_dong`

D. Nếu cần, có thể gắn module theo khóa học đã chọn như hiện tại, nhưng module cũng phải đúng với khóa học tương ứng

E. Ở màn danh sách ngân hàng câu hỏi:
- nên có filter tách:
  - tất cả
  - khóa mẫu
  - khóa hoạt động
- hoặc ít nhất hiển thị rõ câu hỏi đang thuộc loại khóa học nào

==================================================
4. HƯỚNG NGHIỆP VỤ MONG MUỐN
==================================================

Tôi muốn hiểu ngân hàng câu hỏi như sau:

------------------------------------------
4.1. CÂU HỎI THUỘC KHÓA HỌC MẪU
------------------------------------------

- dùng để xây dựng bộ câu hỏi gốc
- có thể tái sử dụng về sau
- thường là bộ câu hỏi nền theo chương trình / khóa mẫu
- khi mở lớp từ khóa mẫu, về sau có thể tham chiếu hoặc chọn lại từ đây

------------------------------------------
4.2. CÂU HỎI THUỘC KHÓA HỌC HOẠT ĐỘNG
------------------------------------------

- là câu hỏi gắn với một lớp / khóa học đang chạy thật
- có thể là câu hỏi riêng cho lớp đó
- phục vụ vận hành thực tế, kiểm tra thực tế

------------------------------------------
4.3. MỤC TIÊU TÁCH RIÊNG
------------------------------------------

Tôi muốn giao diện và logic phải giúp người dùng phân biệt rõ:
- đây là câu hỏi của khóa mẫu
- đây là câu hỏi của khóa đang hoạt động

Không muốn dồn tất cả vào một danh sách chọn khóa học chung chung nữa.

==================================================
5. NHỮNG GÌ CẦN PHÂN TÍCH TRƯỚC KHI SỬA
==================================================

Hãy đọc và phân tích:

1. `KhoaHoc.php`
- xác định cách phân biệt `loai = mau` và `loai = hoat_dong`
- kiểm tra relation giữa khóa mẫu và khóa hoạt động

2. `NganHangCauHoiController`
- create()
- store()
- edit()
- update()
- index()
- import()
- preview()
- confirmImport()

3. `NganHangCauHoi`
- đang gắn với `khoa_hoc_id` như thế nào
- có cần thêm field gì không hay chỉ cần dùng `khoa_hoc_id` + `khoa_hoc.loai`

4. View form thêm/sửa câu hỏi
- hiện đang chọn khóa học ra sao
- module đang load theo khóa học ra sao

5. Màn index ngân hàng câu hỏi
- hiện đang filter / group ra sao
- có thể thêm hiển thị loại khóa học mà không phá UI không

==================================================
6. YÊU CẦU GIAO DIỆN MỚI
==================================================

------------------------------------------
6.1. FORM THÊM / SỬA CÂU HỎI
------------------------------------------

Tôi muốn form thêm câu hỏi được chỉnh thành rõ ràng hơn.

Cần có trường chọn:
- `doi_tuong_khoa_hoc` hoặc tên phù hợp
  - `mau`
  - `hoat_dong`

Sau khi chọn:
- dropdown `khoa_hoc_id` chỉ load danh sách đúng loại đã chọn

Ví dụ:
- nếu chọn “Khóa học mẫu” → chỉ hiện các khóa `loai = mau`
- nếu chọn “Khóa học hoạt động” → chỉ hiện các khóa `loai = hoat_dong`

Có thể dùng:
- dropdown thường
- radio
- tab
- segmented button
Miễn sao dễ dùng và rõ ràng.

------------------------------------------
6.2. DANH SÁCH NGÂN HÀNG CÂU HỎI
------------------------------------------

Tôi muốn phần danh sách hiển thị rõ hơn:
- cột hoặc badge:
  - Khóa mẫu
  - Khóa hoạt động
- filter theo:
  - tất cả
  - khóa mẫu
  - khóa hoạt động

Nếu có group summary hiện tại thì hãy xem có thể tách theo loại khóa học luôn không.

------------------------------------------
6.3. IMPORT CÂU HỎI
------------------------------------------

Nếu phần import hiện tại đang cho chọn khóa học, thì cũng phải sửa giống vậy:
- trước tiên chọn:
  - khóa mẫu
  - hoặc khóa hoạt động
- rồi chỉ cho chọn các khóa học đúng loại

Điều này phải áp dụng nhất quán cho:
- tạo tay
- import
- preview
- confirm import

==================================================
7. LOGIC LƯU DỮ LIỆU MONG MUỐN
==================================================

Tôi không nhất thiết muốn tạo thêm cột mới trong `ngan_hang_cau_hoi` nếu không cần.

Ưu tiên:
- vẫn dùng `khoa_hoc_id`
- phân biệt loại qua `khoa_hoc.loai`

Tức là:
- câu hỏi nào gắn với khóa học có `loai = mau` thì được xem là câu hỏi của khóa mẫu
- câu hỏi nào gắn với khóa học có `loai = hoat_dong` thì là câu hỏi của khóa hoạt động

Chỉ khi bạn thấy thật sự cần thêm field riêng thì mới đề xuất, nhưng phải giải thích rõ vì sao.

==================================================
8. FLOW NGHIỆP VỤ SAU KHI SỬA
==================================================

------------------------------------------
8.1. FLOW THÊM CÂU HỎI MỚI
------------------------------------------

1. Admin/giảng viên vào tạo câu hỏi
2. Chọn loại đích:
   - khóa học mẫu
   - khóa học hoạt động
3. Hệ thống load danh sách khóa học phù hợp
4. Chọn khóa học
5. Chọn module nếu có
6. Nhập nội dung câu hỏi / đáp án
7. Lưu câu hỏi
8. Hệ thống hiển thị câu hỏi thuộc đúng nhóm loại khóa học

------------------------------------------
8.2. FLOW IMPORT CÂU HỎI
------------------------------------------

1. Người dùng vào import câu hỏi
2. Chọn loại đích:
   - khóa học mẫu
   - khóa học hoạt động
3. Chọn khóa học phù hợp
4. Upload file
5. Preview
6. Confirm import
7. Hệ thống lưu câu hỏi vào đúng khóa học theo loại đã chọn

------------------------------------------
8.3. FLOW DANH SÁCH
------------------------------------------

1. Người dùng vào danh sách ngân hàng câu hỏi
2. Có thể filter:
   - tất cả
   - khóa mẫu
   - khóa hoạt động
3. Hệ thống hiển thị rõ câu hỏi thuộc loại nào

==================================================
9. YÊU CẦU KỸ THUẬT
==================================================

Không được nhồi hết logic vào blade hay controller.

Ưu tiên:
- tận dụng query theo `KhoaHoc::mau()` và `KhoaHoc::hoatDong()`
- controller build riêng danh sách khóa học mẫu / khóa hoạt động
- nếu cần, tách helper/service nhỏ để trả về option dữ liệu cho form

Ví dụ:
- trong create/edit:
  - `sampleCourses`
  - `activeCourses`
- hoặc giữ 1 mảng chung nhưng có group label rõ ràng

Miễn là giao diện dễ dùng và logic dễ bảo trì.

==================================================
10. NHỮNG CHỖ CẦN CHUẨN HÓA
==================================================

Hãy kiểm tra và sửa đồng bộ ở các chỗ sau nếu có liên quan:

- create question
- edit question
- store question
- update question
- import question
- preview import
- confirm import
- index filter
- hiển thị label/badge loại khóa học
- load module theo khóa học đã chọn

==================================================
11. CHIA THEO PHASE
==================================================

PHASE 1:
- đọc code hiện tại
- xác định phần khóa học mẫu / khóa hoạt động đang dùng thế nào
- xác định phần question bank hiện tại đang trộn ở đâu
- đề xuất hướng sửa an toàn nhất

PHASE 2:
- sửa controller để load riêng:
  - khóa học mẫu
  - khóa học hoạt động
- chuẩn hóa query/filter

PHASE 3:
- sửa form thêm/sửa câu hỏi
- thêm chọn loại khóa học đích
- load dropdown đúng loại

PHASE 4:
- sửa phần import/preview/confirm import cho nhất quán
- áp dụng chọn loại khóa học cho import

PHASE 5:
- sửa index / summary / badge hiển thị
- hiển thị rõ câu hỏi thuộc khóa mẫu hay khóa hoạt động

PHASE 6:
- test toàn bộ flow
- đảm bảo không phá chức năng cũ

==================================================
12. ĐẦU RA TÔI MUỐN
==================================================

Tôi muốn bạn trả kết quả theo format:

PHẦN A - PHÂN TÍCH HIỆN TRẠNG
- `KhoaHoc` đang phân biệt mẫu/hoạt động thế nào
- `NganHangCauHoi` đang gắn với khóa học ra sao
- form hiện tại đang trộn ở đâu
- import hiện tại đang trộn ở đâu

PHẦN B - THIẾT KẾ NGHIỆP VỤ
- flow thêm câu hỏi cho khóa mẫu
- flow thêm câu hỏi cho khóa hoạt động
- flow import
- flow danh sách/filter

PHẦN C - THIẾT KẾ KỸ THUẬT
- controller nào cần sửa
- model/query nào cần tận dụng
- view nào cần sửa
- có cần migration hay không
- giải thích vì sao chọn dùng `khoa_hoc.loai` thay vì thêm cột mới nếu phù hợp

PHẦN D - TRIỂN KHAI CODE
- code theo từng phase
- nêu rõ file nào được sửa / thêm
- bảo đảm create / edit / import / preview / confirm import / index đều đồng bộ

PHẦN E - TEST
- test thêm câu hỏi cho khóa mẫu
- test thêm câu hỏi cho khóa hoạt động
- test sửa câu hỏi giữa 2 loại
- test import cho khóa mẫu
- test import cho khóa hoạt động
- test filter danh sách
- test hiển thị badge loại khóa học

==================================================
13. RULE CỤ THỂ KHI TRIỂN KHAI
==================================================

Bạn phải tuân thủ các rule sau:

1. Không làm thay đổi logic ngân hàng câu hỏi ngoài phạm vi cần thiết.
2. Không phá phần đáp án, loại câu hỏi, import preview, confirm import.
3. Không làm hỏng logic module của câu hỏi.
4. Nếu một khóa học được chọn thì module chỉ được load từ khóa học đó.
5. Nếu chuyển từ khóa học mẫu sang khóa học hoạt động trong form:
   - dropdown khóa học phải reset
   - dropdown module cũng phải reset
6. Nếu edit một câu hỏi đã có:
   - phải tự xác định sẵn loại khóa học hiện tại của câu hỏi
   - form phải chọn đúng tab/radio tương ứng
7. Nếu import:
   - preview phải giữ được thông tin loại khóa học đã chọn
   - confirm import phải lưu đúng khóa học đó
8. Nếu index:
   - filter phải chạy đúng theo `khoa_hoc.loai`
   - tránh lọc sai hoặc group sai

==================================================
14. GỢI Ý CÁCH HIỂN THỊ TỐT NHẤT
==================================================

Tôi muốn giao diện phần thêm câu hỏi rõ ràng, dễ dùng.

Hãy ưu tiên một trong các cách sau:

CÁCH 1 - RADIO
- Chọn loại khóa học:
  - Khóa học mẫu
  - Khóa học hoạt động

CÁCH 2 - TAB
- Tab 1: Khóa học mẫu
- Tab 2: Khóa học hoạt động

CÁCH 3 - SEGMENTED BUTTON
- nút chọn nhanh loại khóa học

Sau đó mới hiện dropdown khóa học phù hợp.

Ngoài ra ở màn danh sách:
- thêm badge:
  - `Khóa mẫu`
  - `Khóa hoạt động`
- màu dễ phân biệt
- nên hiển thị gần tên khóa học hoặc ở cột riêng

==================================================
15. QUERY / FILTER MONG MUỐN
==================================================

Tôi muốn query/filter được chuẩn hóa như sau:

A. LẤY KHÓA HỌC MẪU
- chỉ lấy `khoa_hoc.loai = mau`

B. LẤY KHÓA HỌC HOẠT ĐỘNG
- chỉ lấy `khoa_hoc.loai = hoat_dong`

C. LỌC CÂU HỎI
- nếu filter `course_type = mau`
  -> chỉ hiện câu hỏi có `khoaHoc.loai = mau`
- nếu filter `course_type = hoat_dong`
  -> chỉ hiện câu hỏi có `khoaHoc.loai = hoat_dong`

D. SUMMARY / GROUP
Nếu màn hình đang có summary nhóm câu hỏi theo khóa học/module, hãy xem có thể:
- thêm trường hiển thị loại khóa học
- hoặc group theo:
  - khóa mẫu
  - khóa hoạt động
trước rồi mới group tiếp xuống khóa học/module

==================================================
16. IMPORT PHẢI NHẤT QUÁN VỚI FLOW MỚI
==================================================

Nếu phần import hiện tại đang có:
- chọn khóa học
- upload file
- preview
- confirm import

thì hãy sửa để hỗ trợ thêm:
- chọn loại khóa học đích trước
- rồi mới chọn khóa học

Điều này phải được giữ xuyên suốt:

A. Form import
- chọn `mau` / `hoat_dong`
- dropdown khóa học đúng loại

B. Preview
- phải biết preview này đang import vào khóa học loại nào
- hiển thị lại rõ cho người dùng kiểm tra

C. Confirm import
- lưu vào đúng khóa học đã chọn
- không bị lẫn loại

==================================================
17. CHỈNH SỬA INDEX CHO DỄ DÙNG HƠN
==================================================

Tôi muốn màn danh sách ngân hàng câu hỏi sau khi sửa sẽ dễ dùng hơn.

Cần có tối thiểu:
- filter theo loại khóa học
- badge hiển thị loại khóa học
- tên khóa học
- module
- trạng thái câu hỏi
- loại câu hỏi

Nếu đang có compact view / detail view thì phải giữ tương thích.

==================================================
18. YÊU CẦU VỀ TƯƠNG THÍCH DỮ LIỆU CŨ
==================================================

Phải đảm bảo:
- câu hỏi cũ vẫn hiển thị bình thường
- nếu câu hỏi cũ đã gắn với khóa học nào thì tự suy ra loại khóa học từ `khoa_hoc.loai`
- không cần migration chỉ để gán lại dữ liệu nếu không thật sự cần

Nếu phát hiện dữ liệu cũ có vấn đề như:
- `khoa_hoc_id` null
- khóa học không tồn tại
- khóa học không có `loai`
thì phải báo rõ trong phân tích

==================================================
19. YÊU CẦU CUỐI
==================================================

Hãy bắt đầu bằng việc đọc code thật của repo hiện tại và tập trung vào:
- `KhoaHoc`
- `NganHangCauHoi`
- `NganHangCauHoiController`
- form create/edit
- flow import

Mục tiêu cuối cùng:
- thêm câu hỏi được theo 2 hướng:
  - khóa học mẫu
  - khóa học hoạt động
- giao diện tách rõ khóa học mẫu ra riêng
- import cũng tách rõ tương tự
- danh sách câu hỏi hiển thị rõ loại khóa học
- không phá các chức năng hiện có

Không được code mù.
Không được thêm logic rối hơn khi repo đã có sẵn nền `loai = mau / hoat_dong`.
Ưu tiên tận dụng cấu trúc hiện có của `KhoaHoc`.
Ưu tiên không thêm cột mới cho ngân hàng câu hỏi nếu chỉ cần dùng `khoa_hoc_id` + `khoa_hoc.loai` là đủ.

đặt biệt nhứo code kí thự theo chuẩn utf8