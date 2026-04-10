# Flow đánh giá thực tế của đồ án `thuctap_khaitri`

## 1. Mục tiêu tài liệu

Tài liệu này mô tả **đúng flow đánh giá đang có trong code hiện tại** của dự án, không mô tả theo phiên bản lý tưởng.

Mục tiêu:

- giúp AI hoặc người mới vào project hiểu nhanh phân hệ `đánh giá / kiểm tra online / chấm điểm / tổng hợp kết quả`
- chỉ ra actor, route, trạng thái, rule nghiệp vụ và dữ liệu thay đổi ở từng bước
- bám vào implementation hiện tại trong `routes/web.php`, controller, service, model, test và docs

Nếu có mâu thuẫn giữa tài liệu này và cảm giác “nên như vậy”, hãy ưu tiên:

1. `routes/web.php`
2. controller / service đang chạy
3. feature test

---

## 2. Phạm vi phân hệ đánh giá

Phân hệ này bao gồm toàn bộ chuỗi:

- ngân hàng câu hỏi
- import câu hỏi
- cấu hình đề
- gửi duyệt
- admin duyệt hoặc từ chối
- admin phát hành hoặc đóng đề
- học viên truy cập đề
- pre-check giám sát
- tạo bài làm
- log giám sát và snapshot
- nộp bài
- chấm tự động
- chấm tự luận
- hậu kiểm giám sát
- cập nhật `ket_qua_hoc_tap`

Chuỗi tổng quát:

```text
Ngân hàng câu hỏi
-> giảng viên tạo/cấu hình đề
-> gửi duyệt
-> admin duyệt
-> admin phát hành
-> học viên làm bài
-> hệ thống chấm
-> giảng viên chấm tay nếu có tự luận
-> hệ thống refresh kết quả học tập
```

---

## 3. Actor và quyền đúng theo code

### 3.1. `admin`

Quyền chính:

- quản lý trực tiếp ngân hàng câu hỏi qua `admin/kiem-tra-online/cau-hoi/*`
- tạo, sửa, import, preview, confirm import câu hỏi
- duyệt bài kiểm tra
- từ chối bài kiểm tra
- phát hành bài kiểm tra cho học viên
- đóng bài kiểm tra
- xem từng bài làm
- cập nhật hậu kiểm giám sát cho bài làm

Lưu ý:

- admin là actor duy nhất có route trực tiếp để CRUD question bank trong `routes/web.php`
- admin không chấm tự luận ở flow chuẩn hiện tại, nhưng có thể hậu kiểm giám sát attempt

### 3.2. `giang_vien`

Quyền chính:

- tạo bài kiểm tra trong phạm vi khóa/module/buổi học mà mình đã được phân công và đã `da_nhan`
- cấu hình câu hỏi cho đề
- import câu hỏi từ file ngay trong màn hình cấu hình đề để đẩy vào ngân hàng câu hỏi
- cấu hình chấm điểm theo `thu_cong` hoặc `goi_diem`
- cấu hình giám sát
- gửi đề duyệt
- xem danh sách bài làm chờ chấm
- chấm câu tự luận
- cập nhật hậu kiểm giám sát cho bài làm thuộc phạm vi được phân công

Lưu ý:

- giảng viên không có route CRUD question bank “độc lập” như admin
- giảng viên chỉ đi vào question bank gián tiếp qua màn hình đề thi
- mọi quyền đều bị chặn nếu không có `phan_cong_module_giang_vien.trang_thai = da_nhan`

### 3.3. `hoc_vien`

Quyền chính:

- xem danh sách bài kiểm tra đủ điều kiện
- xem chi tiết một bài kiểm tra
- làm pre-check nếu bài thi bật giám sát
- bắt đầu làm bài
- nộp bài
- phát sinh log giám sát
- gửi snapshot giám sát
- xem attempt của chính mình

Điều kiện cốt lõi:

- học viên phải thuộc khóa có ghi danh hợp lệ trong `hoc_vien_khoa_hoc`
- trạng thái ghi danh được chấp nhận cho flow bài thi hiện tại là `dang_hoc` hoặc `hoan_thanh`

---

## 4. Thực thể lõi và quan hệ dữ liệu

### 4.1. `ngan_hang_cau_hoi`

Vai trò:

- lưu câu hỏi nguồn để tái sử dụng cho nhiều đề

Gắn với:

- `khoa_hoc_id`
- `module_hoc_id` có thể có hoặc null
- `nguoi_tao_id`

Thuộc tính nghiệp vụ quan trọng:

- `loai_cau_hoi`: `trac_nghiem`, `tu_luan`
- `kieu_dap_an`: `mot_dap_an`, `nhieu_dap_an`, `dung_sai`
- `trang_thai`: `nhap`, `san_sang`, `tam_an`
- `co_the_tai_su_dung`
- `diem_mac_dinh`

Quan hệ:

- 1 câu hỏi có nhiều `dap_an_cau_hoi`

Lưu ý quan trọng:

- question bank hiện hỗ trợ nhiều mode đáp án
- nhưng flow chọn câu hỏi vào đề hiện tại chỉ dùng được các câu thuộc scope `dungChoFlowRaDeHienTai()`, tức là:
  - tự luận
  - trắc nghiệm một đáp án đúng
  - đúng/sai
- trắc nghiệm nhiều đáp án đúng có thể tồn tại trong ngân hàng, nhưng **không phải đường chính của exam builder hiện tại**

### 4.2. `dap_an_cau_hoi`

Vai trò:

- lưu từng đáp án của câu hỏi trắc nghiệm

Thuộc tính chính:

- `ky_hieu`
- `noi_dung`
- `is_dap_an_dung`
- `thu_tu`

### 4.3. `bai_kiem_tra`

Vai trò:

- là “đề thi” mà học viên nhìn thấy và làm bài

Gắn với:

- `khoa_hoc_id`
- `module_hoc_id` có thể null với đề cuối khóa
- `lich_hoc_id` có thể có nếu scope theo buổi học
- `nguoi_tao_id`
- `nguoi_duyet_id`

Thuộc tính nghiệp vụ quan trọng:

- `pham_vi`: `module`, `buoi_hoc`, `cuoi_khoa`
- `loai_bai_kiem_tra`: `module`, `buoi_hoc`, `cuoi_khoa`
- `loai_noi_dung`: `trac_nghiem`, `tu_luan`, `hon_hop`
- `trang_thai_duyet`
- `trang_thai_phat_hanh`
- `tong_diem`
- `che_do_tinh_diem`
- `so_cau_goi_diem`
- `so_lan_duoc_lam`
- `randomize_questions`
- `randomize_answers`
- toàn bộ cấu hình giám sát như `co_giam_sat`, `bat_buoc_camera`, `bat_buoc_fullscreen`...

Quan hệ:

- 1 đề có nhiều `chi_tiet_bai_kiem_tra`
- 1 đề có nhiều `bai_lam_bai_kiem_tra`

### 4.4. `chi_tiet_bai_kiem_tra`

Vai trò:

- là bảng pivot giữa đề thi và câu hỏi

Mỗi dòng lưu:

- đề nào dùng câu hỏi nào
- thứ tự hiển thị
- điểm số của riêng câu hỏi
- cờ `bat_buoc`

### 4.5. `bai_lam_bai_kiem_tra`

Vai trò:

- là một lần làm bài của một học viên cho một đề

Thuộc tính quan trọng:

- `lan_lam_thu`
- `trang_thai`
- `trang_thai_cham`
- `bat_dau_luc`
- `nop_luc`
- `diem_so`
- `tong_diem_trac_nghiem`
- `tong_diem_tu_luan`
- `precheck_data`
- `tong_so_vi_pham`
- `trang_thai_giam_sat`
- `da_tu_dong_nop`
- `nguoi_cham_id`
- `nguoi_hau_kiem_id`

### 4.6. `chi_tiet_bai_lam_bai_kiem_tra`

Vai trò:

- lưu câu trả lời của học viên cho từng câu thuộc đề

Mỗi dòng có thể lưu:

- `dap_an_cau_hoi_id` cho câu trắc nghiệm
- `cau_tra_loi_text` cho câu tự luận
- `is_dung`
- `diem_tu_dong`
- `diem_tu_luan`
- `nhan_xet`

### 4.7. `bai_lam_vi_pham_giam_sat`

Vai trò:

- log lại sự kiện giám sát phát sinh trong lúc thi

### 4.8. `bai_lam_snapshot_giam_sat`

Vai trò:

- lưu ảnh snapshot hoặc lỗi snapshot trong bài thi có giám sát

### 4.9. `ket_qua_hoc_tap`

Vai trò:

- là bảng tổng hợp kết quả cuối cùng theo nhiều cấp:
  - cấp bài thi
  - cấp module
  - cấp khóa học

---

## 5. Route và entrypoint chính của phân hệ

### 5.1. Admin

- `GET /admin/kiem-tra-online/cau-hoi`
- `POST /admin/kiem-tra-online/cau-hoi`
- `POST /admin/kiem-tra-online/cau-hoi/import`
- `GET /admin/kiem-tra-online/cau-hoi/preview`
- `POST /admin/kiem-tra-online/cau-hoi/confirm-import`
- `GET /admin/kiem-tra-online/phe-duyet`
- `GET /admin/kiem-tra-online/phe-duyet/{id}`
- `POST /admin/kiem-tra-online/phe-duyet/{id}/approve`
- `POST /admin/kiem-tra-online/phe-duyet/{id}/reject`
- `POST /admin/kiem-tra-online/phe-duyet/{id}/publish`
- `POST /admin/kiem-tra-online/phe-duyet/{id}/close`
- `GET /admin/kiem-tra-online/phe-duyet/bai-lam/{baiLamId}`
- `POST /admin/kiem-tra-online/phe-duyet/bai-lam/{baiLamId}/giam-sat`

### 5.2. Giảng viên

- `GET /giang-vien/bai-kiem-tra`
- `POST /giang-vien/bai-kiem-tra`
- `GET /giang-vien/bai-kiem-tra/{id}/edit`
- `PUT /giang-vien/bai-kiem-tra/{id}`
- `GET /giang-vien/bai-kiem-tra/{id}/giam-sat`
- `PUT /giang-vien/bai-kiem-tra/{id}/giam-sat`
- `POST /giang-vien/bai-kiem-tra/{id}/import-preview`
- `POST /giang-vien/bai-kiem-tra/{id}/import-confirm`
- `POST /giang-vien/bai-kiem-tra/{id}/gui-duyet`
- `GET /giang-vien/cham-diem/danh-sach`
- `GET /giang-vien/cham-diem/{id}`
- `POST /giang-vien/cham-diem/{id}`
- `POST /giang-vien/cham-diem/{id}/giam-sat`

### 5.3. Học viên

- `GET /hoc-vien/bai-kiem-tra`
- `GET /hoc-vien/bai-kiem-tra/{id}`
- `GET /hoc-vien/bai-kiem-tra/{id}/pre-check`
- `POST /hoc-vien/bai-kiem-tra/{id}/pre-check`
- `POST /hoc-vien/bai-kiem-tra/{id}/bat-dau`
- `POST /hoc-vien/bai-kiem-tra/{id}/nop`
- `POST /hoc-vien/bai-lam/{baiLamId}/giam-sat/log`
- `POST /hoc-vien/bai-lam/{baiLamId}/giam-sat/snapshot`

---

## 6. State machine của phân hệ

### 6.1. Trạng thái của `bai_kiem_tra`

```text
trang_thai_duyet:
nhap -> cho_duyet -> da_duyet
nhap -> cho_duyet -> tu_choi

trang_thai_phat_hanh:
nhap -> phat_hanh -> dong
```

### 6.2. Trạng thái truy cập suy diễn cho học viên

`BaiKiemTra::access_status_key`:

```text
an
sap_mo
dang_mo
da_dong
```

Ý nghĩa:

- `an`: chưa đủ điều kiện hiển thị
- `sap_mo`: đã duyệt + phát hành nhưng `ngay_mo` còn ở tương lai
- `dang_mo`: học viên được bắt đầu làm bài
- `da_dong`: đã quá `ngay_dong`

### 6.3. Trạng thái của `bai_lam_bai_kiem_tra`

```text
trang_thai:
dang_lam -> da_nop -> cho_cham -> da_cham

trang_thai_cham:
chua_cham -> cho_cham -> da_cham
```

Ghi chú:

- `da_nop` là bước trung gian sau khi học viên nộp
- hệ thống auto grade xong sẽ đẩy sang `cho_cham` nếu còn câu tự luận chưa chấm, hoặc `da_cham` nếu đề chỉ có phần auto grade

### 6.4. Trạng thái giám sát của `bai_lam_bai_kiem_tra`

```text
khong_ap_dung
binh_thuong
can_xem_xet
da_xac_nhan
nghi_ngo
```

### 6.5. Trạng thái của `ngan_hang_cau_hoi`

```text
nhap
san_sang
tam_an
```

---

## 7. Flow chi tiết đầu-cuối

## 7.1. Tạo hoặc sửa câu hỏi thủ công

Actor:

- trực tiếp: `admin`

Điểm vào:

- `admin.kiem-tra-online.cau-hoi.create`
- `admin.kiem-tra-online.cau-hoi.store`
- `admin.kiem-tra-online.cau-hoi.edit`
- `admin.kiem-tra-online.cau-hoi.update`

Điều kiện:

- câu hỏi phải thuộc một `khoa_hoc`
- `module_hoc_id` nếu có thì phải đúng module của khóa đó
- nội dung câu hỏi không được trùng trong cùng khóa học sau khi normalize chuỗi

Xử lý:

- admin nhập metadata câu hỏi
- nếu là trắc nghiệm thì hệ thống build danh sách đáp án
- nếu là tự luận thì không cần đáp án
- validate theo loại câu hỏi và kiểu đáp án

Rule chính:

- ít nhất 2 đáp án cho câu trắc nghiệm
- nội dung đáp án không trống
- đáp án không được trùng nhau
- `mot_dap_an` phải có đúng 1 đáp án đúng
- `nhieu_dap_an` phải có ít nhất 2 đáp án đúng
- `dung_sai` phải có đúng 2 đáp án và đúng 1 đáp án đúng

Output:

- tạo hoặc cập nhật `ngan_hang_cau_hoi`
- tạo hoặc đồng bộ lại `dap_an_cau_hoi`

## 7.2. Import câu hỏi và preview

Actor:

- `admin` từ màn question bank
- `giang_vien` từ màn hình cấu hình đề

Điểm vào:

- admin:
  - `admin.kiem-tra-online.cau-hoi.import`
  - `admin.kiem-tra-online.cau-hoi.preview`
  - `admin.kiem-tra-online.cau-hoi.confirm-import`
- giảng viên:
  - `giang-vien.bai-kiem-tra.import-preview`
  - `giang-vien.bai-kiem-tra.import-confirm`

Định dạng file theo request hiện tại:

- `.docx`
- `.pdf`
- `.xlsx`
- `.csv`
- `.txt`

Giới hạn:

- admin import request đang giới hạn `10MB`

Flow:

1. actor tải file lên
2. service phân tích file và build preview
3. preview được lưu vào session
4. người dùng xem preview
5. confirm import để ghi thật vào ngân hàng câu hỏi

Điểm quan trọng:

- preview là bước bắt buộc để giảm rủi ro import thẳng dữ liệu bẩn
- admin lưu preview vào session key `import_preview`
- giảng viên lưu preview vào session key riêng theo `preview_id`

Output khi confirm:

- câu hỏi mới được tạo vào `ngan_hang_cau_hoi`
- câu import mới tương thích với flow ra đề hiện tại
- thường được đưa về trạng thái `san_sang`
- `co_the_tai_su_dung = true`

## 7.3. Chọn câu hỏi vào đề theo scope khóa / module / buổi / cuối khóa

Actor:

- `giang_vien`

Điểm vào:

- `giang-vien.bai-kiem-tra.store`
- `giang-vien.bai-kiem-tra.edit`
- `giang-vien.bai-kiem-tra.update`

Step A. Tạo khung đề:

- giảng viên chọn:
  - `khoa_hoc_id`
  - `pham_vi = module | buoi_hoc | cuoi_khoa`
  - `module_hoc_id` nếu là đề theo module
  - `lich_hoc_id` nếu là đề theo buổi học
- hệ thống resolve `loai_bai_kiem_tra`

Step B. Kiểm tra quyền:

- nếu là đề module hoặc buổi học:
  - giảng viên phải có `phan_cong_module_giang_vien` với `trang_thai = da_nhan` trên đúng module
- nếu là đề cuối khóa:
  - chỉ cần giảng viên có phân công đã nhận trong khóa đó

Step C. Chọn câu hỏi:

- scope câu hỏi lấy theo `khoa_hoc_id`
- nếu đề không phải cuối khóa và có `module_hoc_id`:
  - câu hỏi hợp lệ là câu hỏi của module hiện tại hoặc câu hỏi cấp khóa `module_hoc_id = null`
- nếu đề cuối khóa:
  - có thể nhìn câu hỏi cấp khóa và có thể lọc theo module

Lưu ý rất quan trọng:

- service chọn câu hỏi chỉ nhận câu ở trạng thái `san_sang`
- phải có `co_the_tai_su_dung = true`
- phải thuộc `dungChoFlowRaDeHienTai()`

Output:

- mỗi câu được chọn sẽ sinh một dòng trong `chi_tiet_bai_kiem_tra`
- hệ thống tính lại:
  - `tong_diem`
  - `loai_noi_dung = trac_nghiem | tu_luan | hon_hop`

## 7.4. Tính điểm theo `thu_cong` hoặc `goi_diem`

Actor:

- `giang_vien`

Chế độ 1. `thu_cong`

- giảng viên nhập điểm cho từng câu
- mỗi câu phải có điểm tối thiểu `0.25`

Chế độ 2. `goi_diem`

- giảng viên cấu hình:
  - `so_cau_goi_diem`
  - `tong_diem_goi_diem`
- service tự chia điểm cho từng câu

Rule quan trọng:

- tổng số câu đã chọn phải bằng `so_cau_goi_diem`
- tổng điểm gói phải đủ lớn để mỗi câu sau khi chia vẫn >= `0.25`

Output:

- `chi_tiet_bai_kiem_tra.diem_so` được chuẩn hóa
- `bai_kiem_tra.tong_diem` được cập nhật theo tổng điểm chi tiết

## 7.5. Trường hợp đề tự luận thuần không dùng ngân hàng câu hỏi

Đây là điểm AI rất dễ bỏ sót.

Code hiện tại cho phép:

- bài kiểm tra không có dòng nào trong `chi_tiet_bai_kiem_tra`
- nhưng vẫn được gửi duyệt nếu có `mo_ta`

Ý nghĩa nghiệp vụ:

- đây là đề tự luận tự do
- học viên nộp vào `noi_dung_bai_lam`
- không cần chọn câu hỏi từ ngân hàng

Khi update đề:

- nếu không có câu hỏi nào được chọn, hệ thống để:
  - `loai_noi_dung = tu_luan`
  - `tong_diem = 10`

## 7.6. Gửi duyệt

Actor:

- `giang_vien`

Điểm vào:

- `giang-vien.bai-kiem-tra.submit`

Service kiểm tra readiness:

- có `tieu_de`
- `thoi_gian_lam_bai > 0`
- `so_lan_duoc_lam >= 1`
- nếu có `ngay_mo` và `ngay_dong` thì `ngay_dong > ngay_mo`
- nếu không có câu hỏi thì phải có `mo_ta`
- nếu có câu hỏi:
  - câu hỏi phải còn tồn tại
  - điểm mỗi câu phải hợp lệ
  - tổng điểm đề phải khớp tổng điểm từng câu
- nếu dùng `goi_diem`:
  - số câu đã chọn phải khớp `so_cau_goi_diem`
- nếu bật giám sát:
  - `so_lan_vi_pham_toi_da >= 1`
  - nếu bật camera thì `chu_ky_snapshot_giay >= 10`

Output:

- `trang_thai_duyet = cho_duyet`
- `trang_thai_phat_hanh = nhap`
- set `de_xuat_duyet_luc`

## 7.7. Admin duyệt hoặc từ chối

Actor:

- `admin`

Điểm vào:

- `admin.kiem-tra-online.phe-duyet.approve`
- `admin.kiem-tra-online.phe-duyet.reject`

Approve:

- chạy lại `ensureReadyForApproval()`
- update:
  - `trang_thai_duyet = da_duyet`
  - `nguoi_duyet_id`
  - `duyet_luc`
  - `ghi_chu_duyet`

Reject:

- yêu cầu `ghi_chu_duyet`
- update:
  - `trang_thai_duyet = tu_choi`
  - `trang_thai_phat_hanh = nhap`
  - `nguoi_duyet_id`
  - `duyet_luc`

## 7.8. Admin phát hành hoặc đóng đề

Actor:

- `admin`

Điểm vào:

- `admin.kiem-tra-online.phe-duyet.publish`
- `admin.kiem-tra-online.phe-duyet.close`

Publish:

- chỉ đề `da_duyet` mới được phát hành
- chạy lại `ensureReadyForApproval()`
- update:
  - `trang_thai_phat_hanh = phat_hanh`
  - `phat_hanh_luc`
  - `trang_thai = true`

Close:

- update `trang_thai_phat_hanh = dong`

## 7.9. Học viên nhìn thấy đề khi đủ điều kiện

Actor:

- `hoc_vien`

Điểm vào:

- `hoc-vien.bai-kiem-tra.index`

Điều kiện query thực tế:

- `bai_kiem_tra.trang_thai = true`
- `trang_thai_duyet = da_duyet`
- `trang_thai_phat_hanh = phat_hanh`
- `khoa_hoc_id` thuộc các khóa mà học viên có ghi danh với trạng thái:
  - `dang_hoc`
  - `hoan_thanh`

Sau đó hệ thống suy ra `access_status_key`:

- `an`
- `sap_mo`
- `dang_mo`
- `da_dong`

Chỉ khi `dang_mo` thì `can_student_start = true`.

## 7.10. Pre-check nếu bài thi bật giám sát

Actor:

- `hoc_vien`

Điểm vào:

- `hoc-vien.bai-kiem-tra.precheck`
- `hoc-vien.bai-kiem-tra.precheck.submit`

Khi nào cần:

- `bai_kiem_tra.co_giam_sat = true`

Payload pre-check được validate theo:

- `browser_supported`
- `visibility_supported`
- `camera_supported`
- `camera_ok`
- `fullscreen_supported`
- `fullscreen_ok`

Rule:

- nếu đề bắt buộc camera thì camera phải support và hoạt động
- nếu đề bắt buộc fullscreen thì fullscreen phải support và hoạt động

TTL:

- pre-check pass chỉ sống trong session **15 phút**

Sau khi submit thành công:

- trạng thái pre-check pass được ghi vào session
- khi bấm bắt đầu, service sẽ consume trạng thái này

## 7.11. Tạo attempt

Actor:

- `hoc_vien`

Điểm vào:

- `hoc-vien.bai-kiem-tra.bat-dau`

Guard condition:

- đề phải `dang_mo`
- chưa có attempt `dang_lam`
- chưa vượt `so_lan_duoc_lam`
- nếu đề giám sát thì phải có pre-check hợp lệ chưa hết hạn

Khi start:

- tạo `bai_lam_bai_kiem_tra`
- lưu:
  - IP
  - user agent
  - `precheck_data`
  - `precheck_completed_at`
  - `tong_so_vi_pham = 0`
  - `trang_thai_giam_sat = binh_thuong` nếu có giám sát
- đồng thời tạo sẵn `chi_tiet_bai_lam_bai_kiem_tra` cho tất cả câu của đề

Nếu đề đang `randomize_questions` hoặc `randomize_answers`:

- màn hình show sẽ shuffle trên context attempt đang làm

## 7.12. Ghi log vi phạm và snapshot

Actor:

- `hoc_vien`

Điểm vào:

- `hoc-vien.bai-lam.giam-sat.log`
- `hoc-vien.bai-lam.giam-sat.snapshot`

Điều kiện:

- attempt còn `can_resume`
- đề có `co_giam_sat = true`

Event log hợp lệ:

- `tab_switch`
- `window_blur`
- `window_focus`
- `fullscreen_exit`
- `camera_off`
- `copy_paste_blocked`
- `right_click_blocked`

Snapshot:

- client gửi `captured` hoặc `failed`
- hệ thống lưu ảnh hoặc record lỗi

Rule tổng hợp giám sát:

- sau khi finalize attempt, nếu số vi phạm >= ngưỡng cho phép thì `trang_thai_giam_sat = can_xem_xet`
- nếu trong 10 phút gần nhất có >= 3 lỗi snapshot thì cũng bị `can_xem_xet`

## 7.13. Nộp bài

Actor:

- `hoc_vien`

Điểm vào:

- `hoc-vien.bai-kiem-tra.nop`

Có 2 nhánh:

### Nhánh A. Đề tự luận thuần không có câu hỏi cấu trúc

- hệ thống yêu cầu `noi_dung_bai_lam` nếu không phải auto submit
- update bài làm:
  - `noi_dung_bai_lam`
  - `trang_thai = cho_cham`
  - `trang_thai_cham = cho_cham`
  - `nop_luc`

### Nhánh B. Đề có `chi_tiet_bai_kiem_tra`

- hệ thống validate từng câu bắt buộc
- lưu từng câu trả lời vào `chi_tiet_bai_lam_bai_kiem_tra`
- set `noi_dung_bai_lam` bằng phần text tự luận tổng hợp nếu có
- tạm update:
  - `trang_thai = da_nop`
  - `nop_luc`
- sau đó gọi auto grade

Auto submit:

- nếu client tự động nộp do vi phạm và đề bật giám sát, hệ thống log thêm event auto submit

## 7.14. Auto grade trắc nghiệm

Service chính:

- `App\Services\BaiKiemTraScoringService`

Flow:

- load chi tiết bài làm và đáp án đúng
- với câu trắc nghiệm:
  - nếu đáp án chọn đúng thì lấy đủ điểm câu
  - nếu sai thì 0 điểm
- ghi lại vào từng `chi_tiet_bai_lam_bai_kiem_tra`:
  - `is_dung`
  - `diem_tu_dong`

Sau khi duyệt hết câu:

- cộng `tong_diem_trac_nghiem`
- cộng `tong_diem_tu_luan` nếu đã có điểm tay
- set `diem_so`
- set `trang_thai_cham`

Rule:

- nếu còn ít nhất một câu tự luận chưa có `diem_tu_luan`, `trang_thai_cham = cho_cham`
- nếu không còn câu tự luận chưa chấm, `trang_thai_cham = da_cham`

## 7.15. Chấm tay nếu có tự luận

Actor:

- `giang_vien`

Điểm vào:

- `giang-vien.cham-diem.index`
- `giang-vien.cham-diem.show`
- `giang-vien.cham-diem.store`

Điều kiện:

- giảng viên phải có quyền trên đề tương ứng
- chỉ chấm các bài có `trang_thai_cham = cho_cham`

Khi chấm:

- nhập điểm cho từng câu tự luận
- điểm từng câu phải nằm trong khoảng `0 -> diem_toi_da_cua_cau`
- có thể nhập nhận xét

Sau khi lưu:

- service ghi `diem_tu_luan`
- chạy lại auto grade để cộng dồn cuối
- set:
  - `nguoi_cham_id`
  - `manual_graded_at`

Output:

- bài làm sang `da_cham`
- `diem_so` hoàn chỉnh

## 7.16. Hậu kiểm giám sát

Actor:

- `giang_vien`
- `admin`

Điểm vào:

- giảng viên: `giang-vien.cham-diem.surveillance`
- admin: `admin.kiem-tra-online.phe-duyet.attempt.surveillance`

Điều kiện:

- đề phải bật giám sát

Các trạng thái review hợp lệ:

- `binh_thuong`
- `can_xem_xet`
- `da_xac_nhan`
- `nghi_ngo`

Khi review:

- update:
  - `trang_thai_giam_sat`
  - `ghi_chu_giam_sat`
  - `nguoi_hau_kiem_id`
  - `hau_kiem_luc`

## 7.17. Refresh `ket_qua_hoc_tap`

Service chính:

- `App\Services\KetQuaHocTapService`

Refresh được gọi sau:

- học viên nộp bài
- giảng viên chấm tay
- điểm danh thay đổi

Flow của service có 3 lớp:

### Lớp 1. Cấp bài thi

Method:

- `refreshForExamStudent()`

Rule:

- lấy **attempt đã chấm** có `diem_so` cao nhất của học viên trên đề đó
- ghi 1 dòng `ket_qua_hoc_tap` theo cấp bài thi
- `trang_thai = dat` nếu `diem_so >= 50% tong_diem`

### Lớp 2. Cấp module

Method:

- `refreshForModuleStudent()`

Flow:

- refresh lại các đề thuộc module trước
- lấy trung bình `diem_kiem_tra` của các kết quả bài thi trong module
- lấy tỷ lệ điểm danh của các buổi thuộc module
- tính `diem_tong_ket` module theo trọng số khóa học

Rule:

- nếu có cả điểm danh và điểm kiểm tra thì tính theo trọng số
- nếu chỉ có điểm kiểm tra thì dùng điểm kiểm tra
- module đạt `hoan_thanh` khi `diem_tong_ket >= 5`

### Lớp 3. Cấp khóa học

Method:

- `refreshForCourseStudent()`
- `refreshAllForCourseStudent()`

Flow:

- tính điểm danh toàn khóa
- nếu `phuong_thuc_danh_gia = theo_module`:
  - lấy trung bình điểm tổng kết của các module
- nếu không:
  - lấy kết quả của đề `cuoi_khoa`
- tính `diem_tong_ket` theo:
  - `ty_trong_diem_danh`
  - `ty_trong_kiem_tra`

Rule:

- nếu `diem_tong_ket >= 5` thì `trang_thai = dat`
- nếu học viên đang có ghi danh `dang_hoc` và khóa đạt, service tự động update `hoc_vien_khoa_hoc.trang_thai = hoan_thanh`

## 7.18. Dữ liệu mẫu minh họa end-to-end

Phần này không phải dữ liệu seed thật của database, mà là **bộ dữ liệu mẫu bám theo schema và flow hiện tại** để AI khác nhìn phát hiểu ngay một ca chạy đầy đủ.

### Bối cảnh mẫu

```yaml
admin:
  ma_nguoi_dung: 1
  ho_ten: "Admin Khải Trí"
  vai_tro: "admin"

teacher_user:
  ma_nguoi_dung: 12
  ho_ten: "Nguyễn Văn A"
  vai_tro: "giang_vien"

teacher_profile:
  id: 5
  nguoi_dung_id: 12

student_user:
  ma_nguoi_dung: 30
  ho_ten: "Trần Thị B"
  vai_tro: "hoc_vien"

course:
  id: 101
  ma_khoa_hoc: "KH-ENG-01"
  ten_khoa_hoc: "Tiếng Anh Giao Tiếp 01"
  loai: "hoat_dong"
  trang_thai_van_hanh: "dang_day"
  phuong_thuc_danh_gia: "theo_module"
  ty_trong_diem_danh: 20
  ty_trong_kiem_tra: 80

module:
  id: 201
  khoa_hoc_id: 101
  ma_module: "KH-ENG-01-M1"
  ten_module: "Module 1 - Greeting"
  so_buoi: 3

assignment:
  id: 301
  khoa_hoc_id: 101
  module_hoc_id: 201
  giang_vien_id: 5
  trang_thai: "da_nhan"

student_enrollment:
  id: 401
  khoa_hoc_id: 101
  hoc_vien_id: 30
  trang_thai: "dang_hoc"

schedule:
  id: 501
  khoa_hoc_id: 101
  module_hoc_id: 201
  buoi_so: 1
  hinh_thuc: "online"
  trang_thai: "cho"
```

### Mẫu câu hỏi trong ngân hàng

```yaml
question_1:
  id: 601
  khoa_hoc_id: 101
  module_hoc_id: 201
  ma_cau_hoi: "CH-MCQ-001"
  noi_dung: "Laravel được viết bằng ngôn ngữ nào?"
  loai_cau_hoi: "trac_nghiem"
  kieu_dap_an: "mot_dap_an"
  muc_do: "de"
  diem_mac_dinh: 4
  trang_thai: "san_sang"
  co_the_tai_su_dung: true

answers_question_1:
  - id: 701
    ngan_hang_cau_hoi_id: 601
    ky_hieu: "A"
    noi_dung: "PHP"
    is_dap_an_dung: true
  - id: 702
    ngan_hang_cau_hoi_id: 601
    ky_hieu: "B"
    noi_dung: "Java"
    is_dap_an_dung: false

question_2:
  id: 602
  khoa_hoc_id: 101
  module_hoc_id: 201
  ma_cau_hoi: "CH-ESSAY-001"
  noi_dung: "Trình bày vai trò của migration trong Laravel."
  loai_cau_hoi: "tu_luan"
  muc_do: "trung_binh"
  diem_mac_dinh: 6
  trang_thai: "san_sang"
  co_the_tai_su_dung: true
```

### Mẫu bài kiểm tra sau khi giảng viên cấu hình và admin phát hành

```yaml
exam:
  id: 801
  khoa_hoc_id: 101
  module_hoc_id: 201
  lich_hoc_id: 501
  tieu_de: "Kiểm tra Module 1"
  pham_vi: "module"
  loai_bai_kiem_tra: "module"
  loai_noi_dung: "hon_hop"
  thoi_gian_lam_bai: 30
  ngay_mo: "2026-04-08 08:00:00"
  ngay_dong: "2026-04-08 10:00:00"
  tong_diem: 10
  so_lan_duoc_lam: 1
  che_do_tinh_diem: "thu_cong"
  randomize_questions: false
  randomize_answers: false
  co_giam_sat: true
  bat_buoc_fullscreen: true
  bat_buoc_camera: true
  so_lan_vi_pham_toi_da: 2
  chu_ky_snapshot_giay: 30
  tu_dong_nop_khi_vi_pham: false
  trang_thai_duyet: "da_duyet"
  trang_thai_phat_hanh: "phat_hanh"
  trang_thai: true

exam_details:
  - id: 901
    bai_kiem_tra_id: 801
    ngan_hang_cau_hoi_id: 601
    thu_tu: 1
    diem_so: 4
    bat_buoc: true
  - id: 902
    bai_kiem_tra_id: 801
    ngan_hang_cau_hoi_id: 602
    thu_tu: 2
    diem_so: 6
    bat_buoc: true
```

### Mẫu pre-check pass trước khi bắt đầu bài thi

```json
{
  "browser_supported": true,
  "camera_supported": true,
  "camera_ok": true,
  "fullscreen_supported": true,
  "fullscreen_ok": true,
  "visibility_supported": true,
  "user_agent": "Mozilla/5.0",
  "platform": "Windows",
  "captured_at": "2026-04-08T08:04:12+07:00"
}
```

### Mẫu bài làm khi học viên vừa bắt đầu

```yaml
attempt_started:
  id: 1001
  bai_kiem_tra_id: 801
  hoc_vien_id: 30
  lan_lam_thu: 1
  trang_thai: "dang_lam"
  trang_thai_cham: "chua_cham"
  bat_dau_luc: "2026-04-08 08:05:00"
  precheck_completed_at: "2026-04-08 08:04:30"
  tong_so_vi_pham: 0
  trang_thai_giam_sat: "binh_thuong"
  da_tu_dong_nop: false
```

### Mẫu log giám sát và snapshot

```yaml
surveillance_log:
  id: 1101
  bai_lam_bai_kiem_tra_id: 1001
  loai_su_kien: "tab_switch"
  la_vi_pham: true
  mo_ta: "Student switched tab during exam."

snapshot:
  id: 1201
  bai_lam_bai_kiem_tra_id: 1001
  status: "captured"
  captured_at: "2026-04-08 08:10:00"
```

### Mẫu dữ liệu khi học viên nộp bài

```yaml
attempt_after_submit:
  id: 1001
  bai_kiem_tra_id: 801
  hoc_vien_id: 30
  trang_thai: "cho_cham"
  trang_thai_cham: "cho_cham"
  tong_diem_trac_nghiem: 4.00
  tong_diem_tu_luan: 0.00
  diem_so: 4.00
  nop_luc: "2026-04-08 08:25:00"
  tong_so_vi_pham: 1
  trang_thai_giam_sat: "binh_thuong"

attempt_answers:
  - id: 1301
    bai_lam_bai_kiem_tra_id: 1001
    chi_tiet_bai_kiem_tra_id: 901
    ngan_hang_cau_hoi_id: 601
    dap_an_cau_hoi_id: 701
    is_dung: true
    diem_tu_dong: 4.00
  - id: 1302
    bai_lam_bai_kiem_tra_id: 1001
    chi_tiet_bai_kiem_tra_id: 902
    ngan_hang_cau_hoi_id: 602
    cau_tra_loi_text: "Migration giúp quản lý version schema database."
    diem_tu_luan: null
```

### Mẫu dữ liệu sau khi giảng viên chấm tự luận

```yaml
attempt_after_manual_grading:
  id: 1001
  trang_thai: "da_cham"
  trang_thai_cham: "da_cham"
  tong_diem_trac_nghiem: 4.00
  tong_diem_tu_luan: 5.50
  diem_so: 9.50
  nguoi_cham_id: 12
  manual_graded_at: "2026-04-08 09:00:00"

essay_answer_after_grading:
  id: 1302
  diem_tu_luan: 5.50
  nhan_xet: "Trả lời đúng ý chính, diễn đạt khá rõ."
```

### Mẫu kết quả học tập sau khi service refresh

```yaml
ket_qua_cap_bai_thi:
  khoa_hoc_id: 101
  module_hoc_id: 201
  bai_kiem_tra_id: 801
  hoc_vien_id: 30
  diem_kiem_tra: 9.50
  diem_tong_ket: 9.50
  trang_thai: "dat"
  chi_tiet:
    bai_lam_id: 1001
    lan_lam_thu: 1

ket_qua_cap_module:
  khoa_hoc_id: 101
  module_hoc_id: 201
  bai_kiem_tra_id: null
  hoc_vien_id: 30
  diem_diem_danh: 10.00
  diem_kiem_tra: 9.50
  diem_tong_ket: 9.60
  ty_le_tham_du: 100.00
  trang_thai: "hoan_thanh"

ket_qua_cap_khoa:
  khoa_hoc_id: 101
  module_hoc_id: null
  bai_kiem_tra_id: null
  hoc_vien_id: 30
  phuong_thuc_danh_gia: "theo_module"
  diem_diem_danh: 10.00
  diem_kiem_tra: 9.60
  diem_tong_ket: 9.68
  trang_thai: "dat"
```

### Cách AI nên dùng bộ dữ liệu mẫu này

- nếu cần debug flow bài thi, hãy map từng record mẫu với model tương ứng
- nếu cần viết test, có thể dùng luôn quan hệ giữa `course -> module -> assignment -> enrollment -> exam -> attempt`
- nếu cần giải thích cho AI khác, chỉ cần đưa block dữ liệu mẫu này trước, sau đó chỉ ra bước flow đang nói tới
- nếu cần tạo seeder thật, có thể dùng bộ dữ liệu này làm khung thiết kế fixture

---

## 8. Rule nghiệp vụ ngầm rất quan trọng

- chỉ đề `da_duyet + phat_hanh + trang_thai = true` mới hiện cho học viên
- học viên phải có ghi danh hợp lệ trong khóa thì mới thấy đề
- `dang_hoc` và `hoan_thanh` đều được nhìn thấy đề; `ngung_hoc` thì không
- học viên không được start nếu đề chưa mở hoặc đã đóng
- học viên không được start nếu đã vượt `so_lan_duoc_lam`
- nếu đang có một attempt `dang_lam`, hệ thống không tạo attempt mới
- bài thi có giám sát bắt buộc qua pre-check trước khi start
- pre-check pass chỉ có hiệu lực 15 phút trong session
- trắc nghiệm được chấm tự động
- tự luận sẽ đi vào `cho_cham`
- kết quả cấp bài thi lấy **bài làm đã chấm tốt nhất**
- ngưỡng đạt bài thi là `>= 50% tổng điểm đề`
- tổng kết khóa dùng trọng số điểm danh và kiểm tra từ `khoa_hoc`
- có thể tồn tại đề tự luận thuần không dùng ngân hàng câu hỏi
- không được sửa cấu hình giám sát khi đang có bài làm `dang_lam`
- giảng viên chỉ thấy và thao tác trên đề thuộc phạm vi phân công đã nhận

---

## 9. Những điểm AI rất dễ hiểu sai

- question bank hỗ trợ `nhieu_dap_an`, nhưng exam builder hiện tại không coi đây là luồng chính để ra đề
- route question bank trực tiếp nằm ở khu admin, không phải teacher area
- giảng viên import câu hỏi qua màn hình đề thi nhưng dữ liệu cuối cùng vẫn đi vào ngân hàng câu hỏi dùng chung
- `da_nop` không phải trạng thái cuối; sau auto grade bài còn có thể sang `cho_cham` hoặc `da_cham`
- `trang_thai_giam_sat = can_xem_xet` không đồng nghĩa học viên chắc chắn gian lận, mà chỉ là cần hậu kiểm
- `ket_qua_hoc_tap` không nên sửa tay một dòng riêng lẻ nếu chưa hiểu service refresh toàn cục

---

## 10. Map file nguồn sự thật

### 10.1. Route

- `routes/web.php`

### 10.2. Controller

- `app/Http/Controllers/Admin/NganHangCauHoiController.php`
- `app/Http/Controllers/Admin/BaiKiemTraPheDuyetController.php`
- `app/Http/Controllers/GiangVien/BaiKiemTraController.php`
- `app/Http/Controllers/HocVien/BaiKiemTraController.php`

### 10.3. Service

- `app/Services/BaiKiemTraScoringService.php`
- `app/Services/KetQuaHocTapService.php`
- `app/Services/ExamConfigurationService.php`
- `app/Services/ExamQuestionSelectionService.php`
- `app/Services/ExamQuestionImportService.php`
- `app/Services/QuestionBankImportService.php`
- `app/Services/ExamSurveillanceService.php`
- `app/Services/ExamPrecheckService.php`
- `app/Services/ExamSurveillanceLogService.php`
- `app/Services/ExamSnapshotService.php`

### 10.4. Model

- `app/Models/NganHangCauHoi.php`
- `app/Models/DapAnCauHoi.php`
- `app/Models/BaiKiemTra.php`
- `app/Models/ChiTietBaiKiemTra.php`
- `app/Models/BaiLamBaiKiemTra.php`
- `app/Models/ChiTietBaiLamBaiKiemTra.php`
- `app/Models/BaiLamViPhamGiamSat.php`
- `app/Models/BaiLamSnapshotGiamSat.php`
- `app/Models/KetQuaHocTap.php`
- `app/Models/KhoaHoc.php`

### 10.5. Test

- `tests/Feature/OnlineExamFlowTest.php`
- `tests/Feature/LearningLogicTest.php`
- `tests/Feature/StudentLearningFlowTest.php`
- `tests/Feature/QuestionBankImportFlowTest.php`
- `tests/Feature/QuestionDocumentImportFlowTest.php`
- `tests/Feature/QuestionBankPhaseOneTest.php`

---

## 11. Tóm tắt một câu

Flow đánh giá hiện tại của dự án là:

```text
Admin hoặc giảng viên chuẩn bị nguồn câu hỏi
-> giảng viên cấu hình đề trong phạm vi phân công
-> admin duyệt và phát hành
-> học viên làm bài và bị giám sát nếu cần
-> hệ thống auto grade phần trắc nghiệm
-> giảng viên chấm phần tự luận
-> service tổng hợp lại kết quả học tập ở cấp đề, module và khóa học
```
