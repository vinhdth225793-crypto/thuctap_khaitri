Bạn là senior Laravel developer + exam workflow architect + anti-cheat feature engineer.

Tôi muốn bạn đọc code thật của project Laravel hiện tại và xây dựng / nâng cấp chức năng **BÀI KIỂM TRA CÓ ÁP DỤNG GIÁM SÁT NÂNG CAO** cho hệ thống học tập và kiểm tra online.

==================================================
1. MỤC TIÊU TỔNG THỂ
==================================================

Hiện tại hệ thống đã có nền bài kiểm tra gồm:
- giảng viên tạo bài kiểm tra
- chọn phạm vi:
  - theo buổi học
  - cuối module
  - toàn khóa
- chọn câu hỏi từ ngân hàng câu hỏi
- import câu hỏi từ file ngoài
- setup thời gian
- setup số lần làm
- setup gói điểm hoặc điểm thủ công
- gửi duyệt
- admin duyệt / phát hành
- học viên làm bài
- giảng viên chấm bài
- hệ thống cập nhật kết quả học tập

Bây giờ tôi muốn nâng cấp thêm **chế độ giám sát cho bài kiểm tra**, theo hướng:

- bài kiểm tra thường
- bài kiểm tra có giám sát nâng cao

Tôi KHÔNG yêu cầu AI nhận diện hành vi bất thường ở giai đoạn này.
Tôi chỉ muốn làm chắc đến mức:

1. pre-check trước khi thi
2. bắt buộc fullscreen nếu cấu hình bật
3. phát hiện đổi tab / blur / thoát fullscreen
4. bật camera nếu cấu hình yêu cầu
5. chụp snapshot định kỳ
6. log camera on/off
7. log các hành vi vi phạm
8. hậu kiểm bài thi để giảng viên/admin xem log và snapshot
9. gắn cờ bài thi “cần xem xét” nếu có nhiều vi phạm

==================================================
2. YÊU CẦU CỰC KỲ QUAN TRỌNG
==================================================

- Không được code mù.
- Phải đọc code thật của repo hiện tại trước khi sửa.
- Không làm lại toàn bộ module bài kiểm tra nếu repo đã có nền.
- Phải tận dụng tối đa flow hiện tại của:
  - `App\Http\Controllers\GiangVien\BaiKiemTraController`
  - `App\Http\Controllers\Admin\BaiKiemTraPheDuyetController`
  - `App\Http\Controllers\HocVien\BaiKiemTraController`
  - model `BaiKiemTra`
  - model `BaiLamBaiKiemTra`
  - model chi tiết bài kiểm tra / chi tiết trả lời nếu có
  - `BaiKiemTraScoringService`
  - `ExamConfigurationService`
  - `ExamQuestionSelectionService`
  - `ExamQuestionImportService`
  - `KetQuaHocTapService`
- Không phá flow kiểm tra hiện tại nếu người dùng chọn bài kiểm tra thường.
- Chế độ giám sát phải là một lớp mở rộng thêm, không phải viết lại toàn bộ hệ thống kiểm tra.
- Phải giữ nguyên flow nền hiện có:
  - tạo đề
  - gửi duyệt
  - admin phát hành
  - học viên bắt đầu làm bài
  - học viên nộp bài
  - giảng viên chấm
- Chỉ được mở rộng thêm lớp giám sát vào flow đó.

==================================================
3. MỤC TIÊU NGHIỆP VỤ CỦA CHẾ ĐỘ GIÁM SÁT
==================================================

Tôi muốn bài kiểm tra có 2 chế độ:

A. Bài kiểm tra thường
- dùng flow hiện tại
- không bắt buộc pre-check
- không ghi log giám sát
- không snapshot camera

B. Bài kiểm tra giám sát nâng cao
- có bước kiểm tra trước khi thi
- có rule chống gian lận
- có camera giám sát mức vừa
- có lưu log hành vi
- có hậu kiểm sau khi nộp bài

Mục tiêu là làm cho bài kiểm tra giống với môi trường thi online thực tế hơn.

==================================================
4. NHỮNG GÌ PHẢI ĐỌC TRƯỚC
==================================================

Bắt buộc đọc kỹ các phần liên quan trong repo hiện tại:

- `routes/web.php`
- `App\Http\Controllers\GiangVien\BaiKiemTraController`
- `App\Http\Controllers\HocVien\BaiKiemTraController`
- controller admin duyệt bài kiểm tra
- model `BaiKiemTra`
- model `BaiLamBaiKiemTra`
- model chi tiết trả lời nếu có
- các blade:
  - giảng viên tạo/sửa đề
  - học viên xem chi tiết bài kiểm tra
  - học viên làm bài / nộp bài
  - giảng viên chấm bài
  - admin duyệt bài kiểm tra
- các service đang dùng cho bài kiểm tra

Phải chỉ ra rõ:
- phần nào đã có nền
- phần nào cần mở rộng
- field nào đã có thể tận dụng
- field nào phải thêm mới

==================================================
5. CÁC CHỨC NĂNG CẦN CÓ CHO BÀI KIỂM TRA GIÁM SÁT
==================================================

Tôi muốn bài kiểm tra giám sát có các chức năng sau:

1. giảng viên chọn loại bài kiểm tra:
   - thường
   - giám sát nâng cao

2. giảng viên cấu hình chống gian lận:
   - bật fullscreen bắt buộc
   - bật camera bắt buộc
   - số lần vi phạm tối đa
   - chu kỳ snapshot
   - có tự động nộp bài khi vượt số lần vi phạm không
   - có chặn copy/paste không
   - có chặn chuột phải không

3. học viên phải qua bước pre-check trước khi thi:
   - kiểm tra camera
   - kiểm tra fullscreen
   - kiểm tra trình duyệt / quyền truy cập cần thiết

4. trong lúc thi:
   - phát hiện đổi tab
   - phát hiện blur/focus
   - phát hiện thoát fullscreen
   - log camera bị tắt
   - chụp snapshot định kỳ
   - cảnh báo học viên khi vi phạm

5. khi nộp bài:
   - tổng hợp log vi phạm
   - gắn cờ bài làm:
     - bình thường
     - cần xem xét

6. giảng viên/admin có thể hậu kiểm:
   - xem log hành vi
   - xem số lần vi phạm
   - xem snapshot
   - xem trạng thái giám sát

==================================================
6. CẤU HÌNH CẦN THÊM CHO BÀI KIỂM TRA
==================================================

Tôi muốn ở màn tạo/sửa bài kiểm tra có thêm nhóm cấu hình:

A. CHẾ ĐỘ GIÁM SÁT
- `co_giam_sat` hoặc tên phù hợp
- giá trị:
  - 0 = bài kiểm tra thường
  - 1 = bài kiểm tra giám sát nâng cao

B. CẤU HÌNH ANTI-CHEAT
- `bat_buoc_fullscreen`
- `bat_buoc_camera`
- `so_lan_vi_pham_toi_da`
- `chu_ky_snapshot_giay`
- `tu_dong_nop_khi_vi_pham`
- `chan_copy_paste`
- `chan_chuot_phai`

Nếu repo chưa có các field này, hãy đề xuất migration thêm cột phù hợp vào bảng `bai_kiem_tra`.
Tên cột có thể khác, nhưng phải rõ ràng và bám style code hiện có.

==================================================
7. THIẾT KẾ DỮ LIỆU GIÁM SÁT
==================================================

Tôi muốn có dữ liệu lưu lại hành vi giám sát theo từng bài làm.

Ưu tiên một thiết kế rõ ràng, ví dụ có thể thêm bảng log riêng cho bài làm.

Gợi ý bảng:
- `bai_lam_vi_pham_giam_sat`
hoặc tên phù hợp

Thông tin nên có:
- id
- bai_lam_bai_kiem_tra_id
- loai_su_kien:
  - tab_switch
  - window_blur
  - fullscreen_exit
  - camera_off
  - snapshot_captured
  - snapshot_failed
  - warning_issued
  - auto_submit
- mo_ta
- so_lan_vi_pham_hien_tai nullable
- meta json nullable
- created_at

Ngoài ra cần lưu snapshot nếu bật camera.

Có thể làm theo 1 trong 2 cách:
1. lưu file snapshot vào storage/public rồi lưu path trong DB
2. có bảng riêng cho snapshot

Ví dụ:
- `bai_lam_snapshot_giam_sat`
  - id
  - bai_lam_bai_kiem_tra_id
  - duong_dan_file
  - captured_at
  - status
  - meta json nullable

Bạn phải phân tích và chọn cách phù hợp với repo hiện tại.

==================================================
8. RULE NGHIỆP VỤ BẮT BUỘC PHẢI CHỐT RÕ
==================================================

Bạn phải chốt rõ và code đúng các rule sau:

A. PRE-CHECK FAIL
- nếu `bat_buoc_camera = true` mà không mở được camera:
  - không cho bắt đầu bài thi
- nếu `bat_buoc_fullscreen = true` mà không bật được fullscreen:
  - không cho bắt đầu bài thi
- nếu trình duyệt không hỗ trợ API cần thiết:
  - không cho bắt đầu bài thi giám sát
- bài thi thường thì bỏ qua pre-check

B. SNAPSHOT
- snapshot chỉ chụp trong lúc bài thi đang diễn ra
- không cần chụp ở giai đoạn pre-check trừ khi thật sự cần
- mặc định chu kỳ snapshot nên có giá trị hợp lý, ví dụ 30 giây
- ảnh phải được tối ưu dung lượng hợp lý
- nếu snapshot fail thì log lại
- snapshot fail không tự động tính là vi phạm ngay, trừ khi kèm camera off hoặc mất stream kéo dài

C. PHÂN BIỆT VI PHẠM THẬT VÀ CHỈ LOG
- các sự kiện nên tính vào số lần vi phạm:
  - tab_switch
  - fullscreen_exit
  - camera_off
- các sự kiện chỉ log, không nhất thiết tăng vi phạm:
  - snapshot_captured
  - snapshot_failed
  - window_focus
- `warning_issued` là log cảnh báo, không phải bản thân là vi phạm mới

D. GẮN CỜ BÀI LÀM
- nếu tổng số vi phạm >= ngưỡng cấu hình:
  - hệ thống tự gắn bài làm là `can_xem_xet`
- nếu camera bị tắt kéo dài hoặc snapshot fail liên tục, có thể gắn `can_xem_xet`
- giảng viên/admin được phép cập nhật trạng thái hậu kiểm thủ công nếu muốn

E. AUTO SUBMIT
- chỉ auto submit nếu cấu hình `tu_dong_nop_khi_vi_pham = true`
- khi auto submit:
  - log sự kiện `auto_submit`
  - lưu trạng thái phù hợp
  - không làm mất dữ liệu câu trả lời đã có

F. FLOW THƯỜNG PHẢI GIỮ NGUYÊN
- nếu bài thi không bật giám sát:
  - không load JS chống gian lận phức tạp
  - không bắt pre-check
  - không tạo snapshot/log thừa

==================================================
9. FLOW NGHIỆP VỤ CHI TIẾT
==================================================

------------------------------------------
9.1. FLOW GIẢNG VIÊN TẠO ĐỀ THI GIÁM SÁT
------------------------------------------

1. Giảng viên vào tạo bài kiểm tra
2. Chọn:
   - khóa học
   - phạm vi:
     - buổi học
     - module
     - toàn khóa
   - tiêu đề
   - mô tả
   - thời gian làm bài
3. Chọn loại bài kiểm tra:
   - thường
   - giám sát nâng cao
4. Nếu chọn giám sát nâng cao:
   - hiện cấu hình anti-cheat
5. Giảng viên chọn câu hỏi từ ngân hàng hoặc import file ngoài
6. Chọn chế độ tính điểm:
   - gói điểm
   - thủ công
7. Cấu hình thời gian:
   - ngày mở
   - ngày đóng
   - số lần làm
   - random câu hỏi
   - random đáp án nếu có
8. Lưu nháp
9. Gửi duyệt

------------------------------------------
9.2. FLOW ADMIN DUYỆT VÀ PHÁT HÀNH
------------------------------------------

1. Admin vào bài kiểm tra chờ duyệt
2. Xem:
   - thông tin đề
   - câu hỏi
   - cấu hình điểm
   - cấu hình giám sát
3. Chọn:
   - duyệt
   - từ chối
4. Nếu duyệt:
   - phát hành
5. Học viên được thấy bài kiểm tra

------------------------------------------
9.3. FLOW HỌC VIÊN PRE-CHECK TRƯỚC KHI THI
------------------------------------------

Nếu bài kiểm tra là loại giám sát nâng cao:

1. Học viên mở chi tiết bài kiểm tra
2. Hệ thống hiển thị nút “Kiểm tra trước khi thi”
3. Hệ thống kiểm tra:
   - camera có quyền truy cập không
   - camera có đang hoạt động không
   - trình duyệt có hỗ trợ API cần thiết không
   - fullscreen có bật được không nếu bài thi yêu cầu
4. Hệ thống hiển thị quy chế thi:
   - không đổi tab
   - không thoát fullscreen
   - không tắt camera
5. Học viên xác nhận đã hiểu quy định
6. Chỉ khi pre-check đạt mới cho bắt đầu làm bài

------------------------------------------
9.4. FLOW HỌC VIÊN BẮT ĐẦU THI
------------------------------------------

1. Học viên bấm “Bắt đầu làm bài”
2. Hệ thống tạo `BaiLamBaiKiemTra`
3. Ghi nhận:
   - thời gian bắt đầu
   - IP nếu phù hợp
   - user agent nếu phù hợp
4. Nếu bài thi giám sát:
   - kích hoạt tracking hành vi
   - kích hoạt snapshot camera định kỳ
5. Hiển thị giao diện làm bài thi

------------------------------------------
9.5. FLOW GIÁM SÁT TRONG LÚC THI
------------------------------------------

Trong lúc học viên làm bài, hệ thống phải theo dõi:

A. ĐỔI TAB / RỜI CỬA SỔ
- dùng event `visibilitychange`, `blur`, `focus`
- log lại khi học viên rời khỏi cửa sổ
- tăng số lần vi phạm nếu cần

B. THOÁT FULLSCREEN
- nếu bật fullscreen bắt buộc:
  - log sự kiện
  - cảnh báo học viên
  - tăng số lần vi phạm

C. CAMERA
- nếu bật camera bắt buộc:
  - theo dõi camera còn hoạt động không
  - nếu camera bị tắt hoặc stream bị mất:
    - log sự kiện
    - cảnh báo học viên

D. SNAPSHOT ĐỊNH KỲ
- nếu bật camera:
  - chụp snapshot theo chu kỳ cấu hình
  - upload/lưu ảnh
  - log thành công / thất bại

E. COPY/PASTE / CHUỘT PHẢI
- nếu cấu hình bật chặn:
  - chặn thao tác
  - có thể log hoặc chỉ cảnh báo

------------------------------------------
9.6. FLOW CẢNH BÁO VÀ VI PHẠM
------------------------------------------

Tôi muốn hệ thống có cơ chế cảnh báo rõ ràng:

- lần 1: cảnh báo nhẹ
- lần 2: cảnh báo mạnh hơn
- từ lần N trở đi:
  - đánh dấu bài thi cần xem xét
  - hoặc tự nộp bài nếu cấu hình bật `tu_dong_nop_khi_vi_pham`

Rule:
- số lần vi phạm tối đa phải cấu hình được ở bài kiểm tra
- nếu vượt ngưỡng:
  - log `auto_submit`
  - tự nộp bài nếu chọn chế độ đó

------------------------------------------
9.7. FLOW NỘP BÀI
------------------------------------------

1. Học viên bấm nộp bài hoặc hết giờ
2. Hệ thống lưu đáp án như flow hiện tại
3. Đồng thời tổng hợp dữ liệu giám sát:
   - tổng số vi phạm
   - log vi phạm
   - trạng thái camera
   - snapshot
4. Gắn trạng thái giám sát cho bài làm, ví dụ:
   - `binh_thuong`
   - `can_xem_xet`

------------------------------------------
9.8. FLOW HẬU KIỂM
------------------------------------------

Giảng viên/admin phải có thể xem hậu kiểm bài thi:

1. Mở chi tiết bài làm
2. Xem:
   - số lần đổi tab
   - số lần blur
   - số lần thoát fullscreen
   - camera có bị tắt không
   - timeline vi phạm
   - snapshot
3. Hệ thống hiển thị nhãn:
   - bình thường
   - cần xem xét
4. Người quản lý quyết định:
   - chấp nhận bài làm
   - giữ nguyên
   - đánh dấu nghi ngờ
   - xử lý theo quy chế nội bộ nếu muốn

==================================================
10. YÊU CẦU GIAO DIỆN
==================================================

A. PHÍA GIẢNG VIÊN
- form tạo/sửa bài kiểm tra có phần “Giám sát”
- cấu hình anti-cheat rõ ràng
- xem trạng thái giám sát của bài làm
- chấm bài và xem log nếu cần

B. PHÍA HỌC VIÊN
- màn chi tiết bài kiểm tra có trạng thái:
  - bài thi thường
  - bài thi giám sát
- màn pre-check trước khi thi
- màn làm bài có:
  - đồng hồ
  - cảnh báo vi phạm
  - trạng thái camera nếu có
- thông báo rõ khi bị vi phạm

C. PHÍA ADMIN
- màn duyệt đề có xem được cấu hình giám sát
- màn hậu kiểm hoặc chi tiết bài làm có xem được:
  - log giám sát
  - snapshot
  - trạng thái cần xem xét

D. HẬU KIỂM PHẢI RÕ VỊ TRÍ
- ưu tiên hiển thị hậu kiểm ngay trong màn chi tiết bài làm
- thêm tab riêng “Giám sát”
- tab này hiển thị:
  - tổng số vi phạm
  - timeline vi phạm
  - gallery snapshot
  - trạng thái giám sát

==================================================
11. YÊU CẦU KỸ THUẬT
==================================================

Không được nhồi toàn bộ logic vào controller.

Ưu tiên tách service hợp lý, ví dụ:
- `ExamSurveillanceService`
- `ExamPrecheckService`
- `ExamSurveillanceLogService`
- `ExamSnapshotService`

Tên có thể khác, nhưng mục tiêu là:
- code sạch
- dễ bảo trì
- bám đúng cấu trúc repo hiện tại

Phần frontend chống gian lận có thể dùng JS ở blade hoặc module JS riêng, nhưng phải:
- gọn
- ổn định
- không phá luồng làm bài hiện tại

Bạn phải ưu tiên:
- giữ nguyên route / flow hiện có của học viên:
  - `batDau`
  - `show`
  - `nopBai`
- chỉ thêm lớp JS giám sát vào giao diện làm bài
- không viết lại toàn bộ cơ chế làm bài nếu không cần

==================================================
12. NHỮNG GÌ KHÔNG LÀM TRONG PHASE NÀY
==================================================

Để tránh làm quá tay, giai đoạn này KHÔNG làm các nội dung sau:

- không triển khai AI nhận diện khuôn mặt
- không phân tích hành vi bằng computer vision
- không stream webcam realtime lên server
- không ghi video liên tục
- không kết luận gian lận tự động tuyệt đối
- không hủy kết quả tự động chỉ dựa trên log
- không triển khai hệ thống giám thị trực tiếp realtime

Hệ thống giai đoạn này chỉ:
- ghi log
- cảnh báo
- snapshot
- gắn cờ bài thi cần xem xét
- hỗ trợ hậu kiểm

==================================================
13. CHIA THEO PHASE
==================================================

PHASE 1:
- đọc code hiện tại của phần kiểm tra
- chỉ ra luồng hiện có:
  - giảng viên tạo đề
  - admin duyệt
  - học viên làm bài
  - giảng viên chấm
- xác định chỗ nào cần cắm thêm lớp giám sát
- đề xuất thiết kế dữ liệu và kiến trúc an toàn nhất

PHASE 2:
- thêm cấu hình giám sát vào `BaiKiemTra`
- migration nếu cần
- update form tạo/sửa đề của giảng viên
- update validate và save logic

PHASE 3:
- tạo dữ liệu log giám sát / snapshot
- migration + model nếu cần
- service ghi log giám sát

PHASE 4:
- xây màn pre-check trước khi thi cho học viên
- kiểm tra camera / fullscreen / quyền truy cập
- chỉ cho bắt đầu bài thi khi đạt yêu cầu

PHASE 5:
- tích hợp anti-cheat trong lúc thi:
  - tab switch
  - blur/focus
  - fullscreen exit
  - copy/paste / chuột phải nếu bật
- lưu log và cảnh báo học viên

PHASE 6:
- tích hợp camera snapshot định kỳ
- lưu snapshot
- log camera on/off hoặc snapshot failed
- gắn snapshot với bài làm

PHASE 7:
- tổng hợp trạng thái giám sát khi nộp bài
- gắn cờ bài làm bình thường / cần xem xét
- hiển thị hậu kiểm ở phía giảng viên/admin

PHASE 8:
- test toàn bộ flow
- đảm bảo:
  - bài thi thường vẫn hoạt động như cũ
  - bài thi giám sát hoạt động ổn định
  - không phá chức năng chấm bài / kết quả học tập

==================================================
14. ĐẦU RA TÔI MUỐN
==================================================

Tôi muốn bạn trả ra theo format:

PHẦN A - PHÂN TÍCH HIỆN TRẠNG
- flow kiểm tra hiện có trong repo
- file nào liên quan
- phần nào tận dụng được
- phần nào cần mở rộng

PHẦN B - THIẾT KẾ NGHIỆP VỤ
- flow giảng viên tạo đề có giám sát
- flow admin duyệt
- flow học viên pre-check
- flow học viên làm bài có giám sát
- flow hậu kiểm

PHẦN C - THIẾT KẾ KỸ THUẬT
- migration nào cần thêm
- model nào cần thêm/sửa
- service nào cần thêm/sửa
- controller/view nào cần sửa

PHẦN D - TRIỂN KHAI CODE
- code theo từng phase
- giải thích file nào được sửa / thêm

PHẦN E - TEST
- test bài kiểm tra thường
- test bài kiểm tra giám sát
- test pre-check
- test tab switch / blur / fullscreen exit
- test camera snapshot
- test log vi phạm
- test hậu kiểm
- test chấm bài không bị gãy

==================================================
15. YÊU CẦU CUỐI
==================================================

Hãy bắt đầu bằng việc đọc code thật của repo hiện tại rồi mới làm.

Mục tiêu cuối cùng:
- hệ thống có bài kiểm tra thường và bài kiểm tra giám sát nâng cao
- không cần AI ở giai đoạn này
- có pre-check, anti-cheat cơ bản, camera snapshot, log vi phạm, hậu kiểm
- không phá flow hiện tại của module bài kiểm tra

Không được code mù.
Không được làm lại toàn bộ module nếu repo đã có nền tốt.
Ưu tiên làm chắc từng phase, xong phần nào ổn phần đó rồi mới sang phần tiếp theo.