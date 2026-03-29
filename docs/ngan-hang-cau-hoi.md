# Tài liệu kỹ thuật: Module ngân hàng câu hỏi trắc nghiệm

## 1. Phạm vi
Module này quản lý ngân hàng câu hỏi theo khóa học tại trang `/admin/kiem-tra-online/cau-hoi`.

Hệ thống hiện hỗ trợ:
- CRUD câu hỏi thủ công.
- Import hàng loạt bằng file Excel mẫu mới.
- Giữ tương thích an toàn với file CSV legacy cũ.
- Preview dữ liệu trước khi xác nhận lưu.
- Kiểm tra trùng trong file và trùng với dữ liệu đang có.

## 2. Các route đang dùng
Các route active hiện nằm trong `routes/web.php`:
- `GET /admin/kiem-tra-online/cau-hoi/template`
  Route name: `admin.kiem-tra-online.cau-hoi.template`
  Chức năng: tải file mẫu import từ `storage/app/templates/imports/...`
- `POST /admin/kiem-tra-online/cau-hoi/import`
  Route name: `admin.kiem-tra-online.cau-hoi.import`
  Chức năng: upload file, parse dữ liệu và tạo preview session
- `GET /admin/kiem-tra-online/cau-hoi/preview`
  Route name: `admin.kiem-tra-online.cau-hoi.preview`
  Chức năng: hiển thị màn hình xem trước dữ liệu import
- `POST /admin/kiem-tra-online/cau-hoi/confirm-import`
  Route name: `admin.kiem-tra-online.cau-hoi.confirm-import`
  Chức năng: xác nhận import các dòng hợp lệ

## 3. Thành phần xử lý chính
### Controller
`app/Http/Controllers/Admin/NganHangCauHoiController.php`
- `downloadTemplate()`: trả file mẫu từ storage
- `import()`: validate request, build preview, lưu session preview
- `preview()`: hiển thị preview và kiểm tra preview thuộc đúng người dùng
- `confirmImport()`: xác nhận import và thông báo số dòng bị bỏ qua nếu phát sinh trùng ở thời điểm confirm

### Service
`app/Services/QuestionBankImportService.php`
- `buildPreview()`: đọc file, nhận diện profile header, validate từng dòng, gắn trạng thái preview
- `confirmImport()`: tạo câu hỏi và đáp án cho các dòng hợp lệ, đồng thời re-check duplicate trước khi tạo

### Template registry / config
- `config/import_templates.php`: khai báo template import và legacy profile
- `app/Support/Imports/ImportTemplateRegistry.php`: nơi lấy cấu hình template/profile tập trung để dễ mở rộng
- `app/Support/Imports/SimpleXlsxReader.php`: đọc sheet Excel theo tên sheet

## 4. Cấu trúc lưu file mẫu
File mẫu import đang được tổ chức theo hướng mở rộng:

```text
storage/app/templates/imports/
└── cau-hoi/
    └── mau-import-cau-hoi-trac-nghiem.xlsx
```

Cấu hình hiện tại nằm trong `config/import_templates.php`:
- template key: `question_bank_mcq`
- path: `templates/imports/cau-hoi/mau-import-cau-hoi-trac-nghiem.xlsx`
- download name: `mau-import-cau-hoi-trac-nghiem.xlsx`
- sheet import chính: `Mau_Import`
- dòng bắt đầu nhập dữ liệu: `7`

## 5. Mẫu import Excel mới
### Sheet chính
- `Mau_Import`

### Header bắt buộc
- `cau_hoi`
- `dap_an_1`
- `dap_an_2`
- `dap_an_3`
- `dap_an_4`
- `dap_an_dung`

### Quy tắc quan trọng
Cột `dap_an_dung` lưu theo nội dung đáp án đúng, không lưu `A/B/C/D`.

Ví dụ:
- `dap_an_1 = Hà Nội`
- `dap_an_2 = Huế`
- `dap_an_3 = Đà Nẵng`
- `dap_an_4 = Cần Thơ`
- `dap_an_dung = Hà Nội`

Service sẽ chuẩn hóa chuỗi bằng `trim + gộp khoảng trắng + lowercase UTF-8` để so khớp text và đánh dấu đúng đáp án tương ứng.

## 6. Tương thích legacy CSV
Hệ thống vẫn giữ profile CSV cũ để tránh làm hỏng quy trình đang dùng:
- profile key: `question_bank_mcq_csv`
- header cũ:
  - `cau_hoi`
  - `dap_an_sai_1`
  - `dap_an_sai_2`
  - `dap_an_sai_3`
  - `dap_an_dung`

Trong profile legacy:
- `dap_an_dung` vẫn là nội dung đáp án đúng
- ba cột `dap_an_sai_*` là đáp án sai
- service vẫn chuyển về đúng 4 đáp án chuẩn để lưu vào `dap_an_cau_hoi`

## 7. Luồng import hiện tại
1. Người dùng tải file mẫu ở màn index.
2. Người dùng chọn khóa học và upload file Excel hoặc CSV.
3. Service đọc file:
   - Excel: chỉ đọc sheet `Mau_Import`
   - CSV/TXT: đọc theo hàng và tự nhận diện header legacy
4. Hệ thống tìm dòng header hợp lệ theo cấu hình template/profile.
5. Với file Excel mẫu mới, hệ thống chỉ bắt đầu đọc dữ liệu từ dòng 7 trở đi.
6. Mỗi dòng hợp lệ được convert thành:
   - 1 câu hỏi trắc nghiệm một đáp án đúng
   - 4 đáp án với đúng thứ tự A/B/C/D
7. Preview hiển thị trạng thái từng dòng:
   - `hop_le`
   - `trung_lap_trong_file`
   - `trung_lap_trong_he_thong`
   - `loi_du_lieu`
8. Khi confirm import, service kiểm tra duplicate lại một lần nữa trước khi tạo để tránh trường hợp dữ liệu thay đổi sau lúc preview.

## 8. Validate đang áp dụng
### Validate file upload
- chỉ nhận `.xlsx`, `.csv`, `.txt`
- dung lượng tối đa `5MB`

### Validate cấu trúc
- phải tìm được một dòng header hợp lệ
- template Excel mới phải có đúng 6 cột chuẩn
- template legacy phải có đúng header CSV cũ

### Validate từng dòng dữ liệu
- không được thiếu `cau_hoi`
- không được thiếu một trong 4 đáp án
- không được thiếu `dap_an_dung`
- `dap_an_dung` phải khớp đúng 1 đáp án theo text
- 4 đáp án không được trùng nhau
- phát hiện trùng trong file
- phát hiện trùng với ngân hàng câu hỏi hiện có theo cùng khóa học

## 9. Session preview
Preview import hiện được lưu trong session key `import_preview`, gồm các thông tin:
- khóa học
- tên khóa học
- định dạng file nguồn
- profile đã nhận diện
- dữ liệu preview
- summary
- `user_id`

Màn preview và bước confirm đều kiểm tra `user_id` để tránh dùng nhầm preview của người khác hoặc phiên cũ.

## 10. Tương thích với các chức năng khác
Câu hỏi import mới hiện được tạo với:
- `loai_cau_hoi = trac_nghiem`
- `kieu_dap_an = mot_dap_an`
- `trang_thai = san_sang`
- `co_the_tai_su_dung = true`

Vì vậy dữ liệu import mới tương thích với flow ra đề hiện tại thông qua scope `dungChoFlowRaDeHienTai()` trong model `NganHangCauHoi`.

## 11. Cách kiểm tra nhanh
### Chạy test riêng
```bash
php artisan test --filter=QuestionBankImportFlowTest
```

### Chạy toàn bộ test
```bash
php artisan test
```

### Test tay
1. Mở `/admin/kiem-tra-online/cau-hoi`.
2. Bấm `Tải file dữ liệu mẫu`.
3. Nhập dữ liệu vào sheet `Mau_Import` từ dòng 7.
4. Upload file tại modal import.
5. Kiểm tra preview.
6. Xác nhận import.
7. Kiểm tra câu hỏi và đáp án đúng trong danh sách ngân hàng câu hỏi.
8. Kiểm tra lại khả năng chọn câu hỏi vào bài kiểm tra.

## 12. Cách thêm template mới sau này
1. Thêm file mẫu mới vào `storage/app/templates/imports/<nhom>/...`.
2. Khai báo template mới trong `config/import_templates.php`.
3. Thêm method lấy template/profile tương ứng trong `ImportTemplateRegistry` nếu cần.
4. Dùng registry trong controller/service thay vì hardcode đường dẫn.
5. Viết test download + parse + preview cho template mới.

Hướng này giúp hệ thống mở rộng thêm template học viên, khóa học, giảng viên, module hoặc lịch học mà không phải rải đường dẫn và rule parsing ở nhiều nơi.
