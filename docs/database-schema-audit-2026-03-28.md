# Refactor lai bo migration theo file nho hon

Ngay cap nhat: 2026-03-28

## PHAN A - CAC MIGRATION HIEN TAI DANG QUA LON

Sau dot refactor truoc, bo migration moi da duoc rut tu 57 file cu xuong 8 file domain-level. Tuy nhien 8 file nay van qua to va chua dat muc tieu moi cua prompt.

### 1. Danh sach file dang qua lon
- `0001_01_01_000000_create_framework_tables.php`: tao 7 bang framework trong cung 1 file.
- `2026_01_24_000000_create_identity_and_access_tables.php`: tao 4 bang identity trong cung 1 file.
- `2026_03_08_000000_create_system_support_tables.php`: tao 3 bang support trong cung 1 file.
- `2026_03_10_000000_create_training_catalog_tables.php`: tao 3 bang danh muc dao tao trong cung 1 file.
- `2026_03_10_100000_create_training_delivery_tables.php`: tao 5 bang trien khai giang day trong cung 1 file.
- `2026_03_16_000000_create_learning_resource_tables.php`: tao 3 bang tai nguyen hoc tap trong cung 1 file.
- `2026_03_16_100000_create_assessment_tables.php`: tao 7 bang kiem tra danh gia trong cung 1 file.
- `2026_03_26_090000_create_live_learning_tables.php`: tao 3 bang live room trong cung 1 file.

### 2. Vi sao can tach nho hon
- File qua lon nen doc mot lan rat met va kho review.
- Khi loi o 1 bang thi viec debug bi lan voi 4-7 bang khac trong cung file.
- Git diff kho nho va kho track lich su thay doi theo tung bang.
- Ve mat migration order, file domain-level che mat thu tu phu thuoc FK that su.
- Nhin ten file chua the biet ngay no tao bang nao, dac biet voi cac file ten tong quat nhu `create_assessment_tables`.

## PHAN B - DANH SACH MIGRATION MOI SAU KHI CHIA NHO

### 1. Nhom nen tang he thong
- `2026_03_28_000001_tao_bang_cache_va_cache_locks.php`: tao `cache`, `cache_locks`. Giu chung vi day la cap framework rat sat nhau.
- `2026_03_28_000002_tao_bang_jobs_va_job_batches.php`: tao `jobs`, `job_batches`. Giu chung vi cung mot co che queue batch.
- `2026_03_28_000003_tao_bang_failed_jobs.php`: tao `failed_jobs`. Tach rieng de debug queue failure ro rang.
- `2026_03_28_000004_tao_bang_password_reset_tokens.php`: tao `password_reset_tokens`. Tach rieng vi phuc vu auth.
- `2026_03_28_000005_tao_bang_sessions.php`: tao `sessions`. Tach rieng vi lien quan session driver.

### 2. Nhom nguoi dung va phan quyen
- `2026_03_28_000006_tao_bang_nguoi_dung.php`: tao `nguoi_dung`. Bang cot loi phai tach rieng.
- `2026_03_28_000007_tao_bang_giang_vien.php`: tao `giang_vien`. Tach rieng vi phu thuoc `nguoi_dung`.
- `2026_03_28_000008_tao_bang_hoc_vien.php`: tao `hoc_vien`. Tach rieng vi phu thuoc `nguoi_dung`.
- `2026_03_28_000009_tao_bang_tai_khoan_cho_phe_duyet.php`: tao `tai_khoan_cho_phe_duyet`. Tach rieng vi la hang doi phe duyet.

### 3. Nhom ho tro he thong
- `2026_03_28_000010_tao_bang_system_settings.php`: tao `system_settings`.
- `2026_03_28_000011_tao_bang_banners.php`: tao `banners`.
- `2026_03_28_000012_tao_bang_thong_bao.php`: tao `thong_bao`. Tach rieng vi co FK den `nguoi_dung`.

### 4. Nhom danh muc dao tao
- `2026_03_28_000013_tao_bang_nhom_nganh.php`: tao `nhom_nganh`.
- `2026_03_28_000014_tao_bang_khoa_hoc.php`: tao `khoa_hoc`. Tach rieng vi bang lon, co self-reference va FK sang `nguoi_dung`.
- `2026_03_28_000015_tao_bang_module_hoc.php`: tao `module_hoc`.

### 5. Nhom trien khai giang day
- `2026_03_28_000016_tao_bang_phan_cong_module_giang_vien.php`: tao `phan_cong_module_giang_vien`.
- `2026_03_28_000017_tao_bang_hoc_vien_khoa_hoc.php`: tao `hoc_vien_khoa_hoc`.
- `2026_03_28_000018_tao_bang_lich_hoc.php`: tao `lich_hoc`.
- `2026_03_28_000019_tao_bang_diem_danh.php`: tao `diem_danh`.
- `2026_03_28_000020_tao_bang_yeu_cau_hoc_vien.php`: tao `yeu_cau_hoc_vien`.

### 6. Nhom tai nguyen hoc tap
- `2026_03_28_000021_tao_bang_tai_nguyen_buoi_hoc.php`: tao `tai_nguyen_buoi_hoc`.
- `2026_03_28_000022_tao_bang_bai_giangs.php`: tao `bai_giangs`.
- `2026_03_28_000023_tao_bang_bai_giang_tai_nguyen.php`: tao `bai_giang_tai_nguyen`.

### 7. Nhom kiem tra danh gia
- `2026_03_28_000024_tao_bang_bai_kiem_tra.php`: tao `bai_kiem_tra`.
- `2026_03_28_000025_tao_bang_ngan_hang_cau_hoi.php`: tao `ngan_hang_cau_hoi`.
- `2026_03_28_000026_tao_bang_dap_an_cau_hoi.php`: tao `dap_an_cau_hoi`.
- `2026_03_28_000027_tao_bang_chi_tiet_bai_kiem_tra.php`: tao `chi_tiet_bai_kiem_tra`.
- `2026_03_28_000028_tao_bang_bai_lam_bai_kiem_tra.php`: tao `bai_lam_bai_kiem_tra`.
- `2026_03_28_000029_tao_bang_chi_tiet_bai_lam_bai_kiem_tra.php`: tao `chi_tiet_bai_lam_bai_kiem_tra`.
- `2026_03_28_000030_tao_bang_ket_qua_hoc_tap.php`: tao `ket_qua_hoc_tap`.

### 8. Nhom hoc truc tuyen / live room
- `2026_03_28_000031_tao_bang_phong_hoc_live.php`: tao `phong_hoc_live`.
- `2026_03_28_000032_tao_bang_phong_hoc_live_nguoi_tham_gia.php`: tao `phong_hoc_live_nguoi_tham_gia`.
- `2026_03_28_000033_tao_bang_phong_hoc_live_ban_ghi.php`: tao `phong_hoc_live_ban_ghi`.

## PHAN C - CODE MIGRATION DA REFACTOR

### 1. Nhung thay doi da thuc hien
- Xoa bo 8 migration domain-level trung gian kho doc.
- Tao lai bo migration active thanh 33 file nho hon trong `database/migrations/`.
- Giu archive 57 migration cu tai `database/migrations/legacy_archive_2026_03_28/` de doi chieu lich su.
- Giu nguyen schema cuoi cung da chot, chi doi cach chia file migration.

### 2. Cap nhat code lien quan can thiet
- `app/Models/NguoiDung.php`: cast dung `email_xac_thuc` va bo sung relation `khoaHocs()`.
- `app/Models/DiemDanh.php`: relation `hocVien()` tro ve dung `NguoiDung` theo FK `hoc_vien_id`.

## PHAN D - XAC NHAN SAU REFACTOR

### 1. Schema cuoi cung co giu nguyen khong
- Co. Cac bang va cot cuoi cung dang duoc code hien tai su dung da duoc giu nguyen.
- Khong doi ten bang.
- Khong doi ten cot dang duoc model, controller, test su dung.

### 2. Bang / cot nao thay doi
- Khong co thay doi schema nghiep vu moi.
- Chi thay doi muc do chia nho migration file.
- Hai dieu chinh code duy nhat la canh chinh relation/cast de khop schema da chot.

### 3. Thu tu migration da an toan chua
- Co. Thu tu da duoc sap de theo dependency FK thuc te:
  - framework truoc
  - `nguoi_dung` truoc cac bang phu thuoc user
  - `nhom_nganh` truoc `khoa_hoc`
  - `khoa_hoc` truoc `module_hoc`
  - `lich_hoc` truoc `diem_danh`
  - `tai_nguyen_buoi_hoc` truoc `bai_giangs`
  - `bai_kiem_tra`, `ngan_hang_cau_hoi` truoc cac bang chi tiet
  - `bai_giangs` truoc `phong_hoc_live`

## PHAN E - CHECKLIST TEST

### 1. Da kiem tra
- `php -l` cho toan bo migration moi va model da sua.
- `php artisan test` da pass toan bo test suite, xac nhan bo migration moi van duoc tao tu dau trong test environment.
- Cac foreign key chinh da duoc kiem tra lai theo thu tu migration moi.
- Relation model lien quan da duoc doi chieu va sua o `NguoiDung`, `DiemDanh`.

### 2. Luu y ve `migrate:fresh`
- Khong chay `php artisan migrate:fresh` tren local DB that cua ban de tranh dong vao du lieu hien co.
- `php artisan test` su dung co che refresh database trong moi truong test, nen van xac nhan duoc tinh dung cua thu tu migration ma khong anh huong local data.

### 3. Danh sach active migration sau khi chia nho
- Tong so file active: 33 file.
- Tong so file archive migration cu: 57 file.