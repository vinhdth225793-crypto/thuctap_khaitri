# Module Kiem Tra Online

## Muc tieu
Module nay mo rong he thong hoc tap hien tai theo 6 huong nghiep vu chinh:

- ngan hang cau hoi
- import cau hoi bang file CSV mo duoc trong Excel
- giang vien tao de va gui admin duyet
- admin phe duyet va phat hanh
- hoc vien lam bai trac nghiem, tu luan, hon hop
- giang vien cham tu luan va he thong tong hop diem hoc tap

## Cac bang du lieu moi

- `ngan_hang_cau_hoi`
- `dap_an_cau_hoi`
- `chi_tiet_bai_kiem_tra`
- `chi_tiet_bai_lam_bai_kiem_tra`
- `ket_qua_hoc_tap`

## Cac mo rong schema hien co

- `khoa_hoc`
  - `phuong_thuc_danh_gia`
  - `ty_trong_diem_danh`
  - `ty_trong_kiem_tra`
- `bai_kiem_tra`
  - `loai_bai_kiem_tra`
  - `loai_noi_dung`
  - `trang_thai_duyet`
  - `trang_thai_phat_hanh`
  - `tong_diem`
  - `so_lan_duoc_lam`
- `bai_lam_bai_kiem_tra`
  - `lan_lam_thu`
  - `tong_diem_trac_nghiem`
  - `tong_diem_tu_luan`
  - `trang_thai_cham`

## Route chinh

- Admin
  - `admin/kiem-tra-online/cau-hoi`
  - `admin/kiem-tra-online/phe-duyet`
- Giang vien
  - `giang-vien/bai-kiem-tra/{id}/edit`
  - `giang-vien/cham-diem/danh-sach`
- Hoc vien
  - `hoc-vien/bai-kiem-tra`

## Test da co

- `Tests\\Feature\\OnlineExamFlowTest`
- `Tests\\Feature\\LearningLogicTest`

Chay toan bo test:

```bash
php artisan test
```

## Seed du lieu demo

Chay seeder rieng cho demo:

```bash
php artisan db:seed --class=KiemTraOnlineDemoSeeder
```

Tai khoan demo duoc tao:

- `admin.demo-kiemtra@example.com`
- `giangvien.demo-kiemtra@example.com`
- `hocvien.demo-kiemtra@example.com`

Mat khau mac dinh:

```text
12345678
```

## Ghi chu ky thuat

- Import hien tai dung dinh dang CSV, co the mo/chinh sua bang Excel roi luu lai de import.
- Trac nghiem duoc cham tu dong.
- Tu luan duoc chuyen sang `cho_cham` va giang vien cham tay.
- `ket_qua_hoc_tap` duoc cap nhat sau khi nop bai, cham bai va sau khi diem danh thay doi.
