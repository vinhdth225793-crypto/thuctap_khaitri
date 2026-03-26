Bạn là senior Laravel architect + feature engineer.

Tôi đang làm đồ án web học tập và kiểm tra online bằng Laravel.
Repo của tôi:
https://github.com/vinhdth225793-crypto/thuctap_khaitri.git

Tôi muốn bạn phát triển chức năng **Phòng học Live** cho hệ thống hiện tại của tôi, bám đúng kiến trúc repo đang có.

==================================================
MỤC TIÊU TÍNH NĂNG
==================

Tôi muốn hệ thống có chức năng **phòng học trực tuyến (Live)** gắn với:

* khóa học
* module
* buổi học / lịch học
* bài giảng

Flow nghiệp vụ đã chốt như sau:

1. Admin tạo cấu trúc đào tạo:

   * nhóm ngành
   * khóa học
   * module
   * buổi học

2. Admin phân công giảng viên

3. Admin hoặc giảng viên vào đúng khóa học / module / buổi học để tạo **bài giảng dạng Live**

4. Bài giảng Live có thể chọn nền tảng:

   * Zoom
   * Google Meet

5. Bài giảng live phải có:

   * tiêu đề
   * mô tả
   * thời gian bắt đầu
   * thời lượng dự kiến
   * moderator / người điều hành
   * tài liệu đính kèm
   * các cấu hình nâng cao

6. Nếu **giảng viên tạo hoặc sửa**, thì phải qua **admin duyệt**

7. Khi bài giảng live đã được duyệt và công bố:

   * học viên trong khóa học sẽ thấy buổi học live
   * học viên có thể vào phòng học từ trong hệ thống

8. Về cách vào phòng:

   * Zoom: ưu tiên tích hợp để học ngay từ website
   * Google Meet: tạo link và vào từ website của tôi

9. Sau buổi học:

   * cập nhật trạng thái kết thúc
   * có thể gắn bản ghi vào bài giảng nếu có
   * có thể dùng dữ liệu tham gia để hỗ trợ điểm danh

==================================================
YÊU CẦU BẮT BUỘC
================

* Không được viết lại toàn bộ hệ thống từ đầu
* Phải đọc repo hiện tại trước
* Phải tận dụng tối đa code cũ
* Không được phá cấu trúc khóa học / module / buổi học đang có
* Mọi thay đổi phải bám theo repo thật
* Nếu có phần cũ dùng được thì refactor trên nền cũ
* Nếu có bảng / model / controller đang phù hợp thì tận dụng
* Chỉ thêm bảng mới khi thật sự cần
* Làm theo phase, xong phase nào phải test được phase đó
* Trước khi code mỗi phase, phải nêu:

  * mục tiêu phase
  * file dự định sửa
  * migration cần thêm
  * model cần thêm / sửa
  * controller cần thêm / sửa
  * route cần thêm / sửa
  * view cần thêm / sửa
  * cách test

==================================================
PHASE 0 - KHẢO SÁT REPO VÀ CHỐT HƯỚNG
=====================================

Mục tiêu:

* Đọc repo hiện tại
* Xác định live room nên gắn vào đâu trong hệ thống
* Không code ngay
* Chỉ phân tích và chốt hướng triển khai phù hợp nhất

Bạn phải kiểm tra kỹ:

1. Các bảng / model / controller liên quan đến:

* khóa học
* module học
* lịch học / buổi học
* bài giảng
* tài nguyên
* phân công giảng viên
* học viên khóa học
* admin duyệt

2. Trả lời rõ:

* nên gắn Live vào `bai_giang`, `lich_hoc`, hay cả hai
* nên tạo bảng `phong_hoc_live` riêng hay gộp vào bảng hiện có
* tận dụng được gì từ `TaiNguyenBuoiHoc`
* tận dụng được gì từ `BaiGiang` nếu model đã có
* tận dụng được gì từ `PhanCong`, `LichHoc`, `HocVienKhoaHoc`

3. Sau khi phân tích, chốt kiến trúc:

* bảng chính
* bảng phụ
* quan hệ giữa:

  * bài giảng live
  * buổi học
  * moderator
  * người tham gia
  * bản ghi
  * tài liệu đính kèm

Output phase 0 phải có:

1. Tóm tắt phát hiện
2. Kiến trúc đề xuất
3. Danh sách file sẽ sửa theo phase
4. Lộ trình phase cụ thể

CHƯA CODE NGAY nếu chưa xong phase 0.

==================================================
PHASE 1 - THÊM CẤU TRÚC DỮ LIỆU PHÒNG HỌC LIVE
==============================================

Mục tiêu:

* Tạo nền CSDL cho chức năng live room
* Gắn được với khóa học / module / buổi học / bài giảng

Yêu cầu nghiệp vụ:

* Một bài giảng live thuộc:

  * khóa học
  * module
  * buổi học / lịch học
* Một bài giảng live có cấu hình phòng học
* Một bài giảng live có thể có:

  * 1 moderator chính
  * trợ giảng nếu cần
  * tài liệu đính kèm
  * bản ghi sau buổi học
* Một bài giảng live có trạng thái duyệt và trạng thái công bố

Bạn cần:

1. Kiểm tra bảng hiện tại có thể tận dụng không
2. Nếu cần thì tạo migration mới cho các bảng sau:

* bảng cấu hình live room
* bảng log người tham gia
* bảng bản ghi live room

Gợi ý bảng:

* `phong_hoc_live`
* `phong_hoc_live_nguoi_tham_gia`
* `phong_hoc_live_ban_ghi`

Các cột nên có tối thiểu:

Bảng `phong_hoc_live`

* id
* bai_giang_id hoặc lich_hoc_id (chọn kiến trúc phù hợp nhất sau khi audit)
* nen_tang_live: zoom / google_meet
* loai_live: meeting / class / webinar
* tieu_de
* mo_ta
* moderator_id
* tro_giang_id nullable
* thoi_gian_bat_dau
* thoi_luong_phut
* mo_phong_truoc_phut
* nhac_truoc_phut
* suc_chua_toi_da
* cho_phep_chat
* cho_phep_thao_luan
* cho_phep_chia_se_man_hinh
* tat_mic_khi_vao
* tat_camera_khi_vao
* cho_phep_ghi_hinh
* chi_admin_duoc_ghi_hinh
* tu_dong_gan_ban_ghi
* trang_thai_duyet
* trang_thai_cong_bo
* trang_thai_phong
* du_lieu_nen_tang_json
* created_by
* approved_by
* approved_at
* created_at
* updated_at

Bảng `phong_hoc_live_nguoi_tham_gia`

* id
* phong_hoc_live_id
* nguoi_dung_id
* vai_tro: host / moderator / assistant / student
* joined_at
* left_at
* trang_thai

Bảng `phong_hoc_live_ban_ghi`

* id
* phong_hoc_live_id
* nguon_ban_ghi: zoom / google_meet / upload
* tieu_de
* duong_dan_file
* link_ngoai
* thoi_luong
* trang_thai
* created_at
* updated_at

Output phase 1:

1. Migration đầy đủ
2. Model đầy đủ
3. Quan hệ Eloquent đầy đủ
4. Hướng dẫn migrate
5. Cách test

==================================================
PHASE 2 - TẠO CHỨC NĂNG CẤU HÌNH BÀI GIẢNG LIVE
===============================================

Mục tiêu:

* Tạo form cấu hình phòng học live trong đúng flow hệ thống hiện tại

Flow phải bám:

* vào khóa học
* chọn module
* chọn buổi học
* tạo / sửa bài giảng
* chọn loại bài giảng là Live
* hiện form cấu hình live

Yêu cầu:

1. Tận dụng giao diện bài giảng / tài nguyên hiện có nếu hợp lý
2. Tạo hoặc sửa form để có các trường:

* tiêu đề
* mô tả
* nền tảng live
* thời gian bắt đầu
* thời lượng
* moderator
* trợ giảng
* thời gian mở phòng trước
* thời gian nhắc
* sức chứa
* cấu hình nâng cao:

  * cho phép chat
  * cho phép thảo luận
  * cho phép chia sẻ màn hình
  * tắt mic khi vào
  * tắt camera khi vào
  * cho phép ghi hình
  * chỉ admin ghi hình
  * tự động gắn bản ghi
  * khóa copy nội dung mô tả

3. Cho phép đính kèm tài liệu:

* từ thư viện
* từ tài nguyên hiện có
* hoặc từ dữ liệu bài giảng hiện tại nếu repo đã hỗ trợ

4. Nếu admin tạo:

* có thể duyệt luôn hoặc lưu nháp

5. Nếu giảng viên tạo:

* lưu nháp hoặc gửi admin duyệt

Bạn cần:

* sửa hoặc tạo controller phù hợp
* sửa hoặc thêm request validate
* sửa route
* sửa view blade
* tận dụng layout hiện có của admin / giảng viên

Output phase 2:

1. Danh sách file sửa
2. Code controller
3. Code request validate
4. Code route
5. Code view blade
6. Cách test toàn bộ form cấu hình

==================================================
PHASE 3 - XÂY QUY TRÌNH DUYỆT ADMIN CHO BÀI GIẢNG LIVE
======================================================

Mục tiêu:

* Giảng viên tạo / sửa bài giảng live nhưng phải qua admin duyệt

Yêu cầu nghiệp vụ:

* Admin thấy danh sách bài giảng live chờ duyệt
* Admin xem được:

  * tiêu đề
  * khóa học
  * module
  * buổi học
  * moderator
  * nền tảng live
  * thời gian học
  * tài liệu đính kèm
  * cấu hình nâng cao
* Admin có thể:

  * duyệt
  * từ chối
  * yêu cầu chỉnh sửa

Trạng thái nên hỗ trợ:

* nhap
* cho_duyet
* can_chinh_sua
* bi_tu_choi
* da_duyet
* da_cong_bo
* da_ket_thuc

Bạn cần:

* thêm giao diện admin duyệt
* thêm action duyệt / từ chối / yêu cầu sửa
* lưu lý do phản hồi
* cập nhật trạng thái bài giảng live và phòng học live

Output phase 3:

1. Route admin duyệt
2. Controller admin duyệt
3. View danh sách chờ duyệt
4. View chi tiết duyệt
5. Code cập nhật trạng thái
6. Cách test workflow duyệt

==================================================
PHASE 4 - TẠO TRANG CHI TIẾT PHÒNG HỌC LIVE CHO HỌC VIÊN VÀ GIẢNG VIÊN
======================================================================

Mục tiêu:

* Tạo trang bài giảng live / phòng học live trong hệ thống

Trang này phải hiển thị:

* tiêu đề buổi học
* mô tả
* thời gian bắt đầu
* thời lượng
* moderator
* trạng thái:

  * chưa đến giờ
  * sắp bắt đầu
  * đang diễn ra
  * đã kết thúc
  * đã hủy
* tài liệu đính kèm
* nút tham gia / bắt đầu buổi học

Yêu cầu:

1. Nếu là giảng viên hoặc moderator:

* thấy nút:

  * bắt đầu buổi học
  * vào phòng học
  * kết thúc buổi học

2. Nếu là học viên:

* chỉ thấy nút tham gia khi:

  * thuộc khóa học
  * bài giảng đã duyệt
  * đã công bố
  * đúng giờ mở phòng

3. Nếu chưa tới giờ:

* hiện countdown hoặc thông báo chờ

4. Nếu đã kết thúc:

* hiện trạng thái kết thúc
* hiện bản ghi nếu có

Bạn cần:

* tạo route chi tiết live room
* tạo controller show
* tạo blade giao diện chi tiết
* filter quyền truy cập theo vai trò

Output phase 4:

1. Route
2. Controller
3. View blade
4. Logic phân quyền
5. Logic trạng thái hiển thị
6. Cách test với admin / giảng viên / học viên

==================================================
PHASE 5 - TÍCH HỢP ZOOM
=======================

Mục tiêu:

* Cho phép bài giảng live dùng Zoom

Yêu cầu:

1. Kiểm tra cách tích hợp phù hợp nhất với repo hiện tại
2. Backend chuẩn bị dữ liệu Zoom:

* meeting info
* join data
* host data
* du_lieu_nen_tang_json

3. Trang live room:

* nếu platform = zoom
* hiển thị nút:

  * bắt đầu bằng Zoom
  * tham gia Zoom
* nếu khả thi, chuẩn bị theo hướng nhúng Zoom trên website
* nếu chưa làm full SDK ngay thì làm bước trung gian:

  * lưu cấu hình
  * tạo nút join/start
  * chuẩn hóa dữ liệu để phase sau nâng cấp SDK dễ dàng

4. Tách rõ:

* phần cấu hình Zoom
* phần vào Zoom

Bạn cần:

* tạo service / helper nếu cần
* không hardcode bừa
* có xử lý env/config rõ ràng

Output phase 5:

1. Cấu trúc config Zoom
2. Controller/service xử lý
3. Route
4. View cập nhật
5. Cách test Zoom flow

==================================================
PHASE 6 - TÍCH HỢP GOOGLE MEET
==============================

Mục tiêu:

* Cho phép bài giảng live dùng Google Meet

Yêu cầu:

1. Tích hợp theo hướng phù hợp:

* tạo link Meet / dữ liệu Meet
* hiển thị trong bài giảng live
* học viên vào học từ trong website

2. Không làm lệch kiến trúc hiện có
3. Tách rõ:

* cấu hình Google Meet
* link tham gia
* moderator bắt đầu
* học viên vào học

4. Nếu repo hiện chưa đủ nền để làm sâu API, thì phải làm trước bản:

* cấu hình dữ liệu Meet
* lưu link
* hiển thị đúng flow
* chuẩn hóa để dễ nâng cấp về sau

Output phase 6:

1. Cấu trúc config Google Meet
2. Migration nếu cần thêm field
3. Controller/service
4. Route
5. View
6. Cách test

==================================================
PHASE 7 - LOG THAM GIA, KẾT THÚC BUỔI HỌC, BẢN GHI
==================================================

Mục tiêu:

* Hoàn thiện vòng đời buổi live

Yêu cầu:

1. Khi moderator bắt đầu buổi học:

* cập nhật trạng thái `dang_dien_ra`

2. Khi học viên vào:

* ghi log tham gia
* lưu joined_at
* khi rời thì lưu left_at

3. Khi kết thúc:

* cập nhật `da_ket_thuc`

4. Nếu có bản ghi:

* gắn vào bảng bản ghi
* hiển thị trong bài giảng live sau khi kết thúc

5. Nếu `tu_dong_gan_ban_ghi = true`

* hiển thị bản ghi như tài nguyên xem lại

Output phase 7:

1. Code log tham gia
2. Code cập nhật trạng thái phòng
3. Code xử lý bản ghi
4. View hiển thị bản ghi
5. Cách test toàn bộ vòng đời buổi học

==================================================
PHASE 8 - CHUẨN HÓA PHÍA HỌC VIÊN
=================================

Mục tiêu:

* Đảm bảo học viên chỉ thấy và vào được nội dung live hợp lệ

Yêu cầu:

1. Học viên chỉ thấy bài giảng live khi:

* đã duyệt
* đã công bố
* thuộc khóa học
* đúng lịch hoặc được mở trước

2. Nếu chưa đến giờ:

* hiện thông báo chờ

3. Nếu moderator chưa bắt đầu:

* hiện thông báo chờ người điều hành

4. Nếu đã kết thúc:

* hiện bản ghi / tài liệu nếu được phép

5. Tận dụng trang khóa học / chi tiết khóa học hiện có nếu phù hợp

Output phase 8:

1. Code filter học viên
2. Code controller / view cập nhật
3. Cách test với nhiều trạng thái khác nhau

==================================================
PHASE 9 - DỌN DẸP, TỐI ƯU, GHI CHÚ KỸ THUẬT
===========================================

Mục tiêu:

* Làm sạch code
* Giữ khả năng mở rộng

Bạn cần:

1. Kiểm tra phần nào trong repo có thể tái sử dụng hoặc bị trùng
2. Nếu có code live room viết tạm, hãy refactor lại
3. Ghi chú rõ:

* file nào mới
* file nào sửa
* phần nào dùng tạm
* phần nào cần nâng cấp tiếp

4. Viết checklist test kỹ thuật

Output phase 9:

1. Danh sách file thay đổi
2. Ghi chú kỹ thuật
3. Checklist test
4. TODO nâng cấp sau này

==================================================
FORMAT KẾT QUẢ MỖI PHASE
========================

Mỗi phase phải trả kết quả theo đúng format:

1. Mục tiêu phase
2. Phân tích repo liên quan đến phase này
3. Hướng xử lý chọn
4. Vì sao chọn hướng này
5. File sẽ sửa
6. Migration sẽ thêm / sửa
7. Code đầy đủ
8. Cách chạy
9. Cách test
10. Rủi ro còn lại

==================================================
QUY TẮC CODE
============

* Laravel code rõ ràng, dễ bảo trì
* Dùng Eloquent relationship chuẩn
* Dùng FormRequest nếu cần validate
* Không hardcode bừa
* Ưu tiên refactor trên code cũ
* Không phá dữ liệu cũ nếu chưa cần
* Nếu một phase chưa chắc chắn thì phải nói rõ
* Mọi đề xuất phải bám repo thật
* Không trả lời chung chung
* Không viết lại toàn bộ project vô lý

==================================================
BẮT ĐẦU NGAY
============

Bây giờ hãy bắt đầu với:
PHASE 0 - KHẢO SÁT REPO VÀ CHỐT HƯỚNG

Chưa code ngay.
Trước tiên hãy audit phần liên quan:

* khóa học
* module
* buổi học / lịch học
* bài giảng
* tài nguyên
* phân công giảng viên
* học viên khóa học
* admin duyệt

Sau khi audit xong:

* chốt kiến trúc phù hợp nhất
* rồi mới chuyển sang phase 1
