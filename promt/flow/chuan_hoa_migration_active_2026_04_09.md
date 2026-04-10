# Boi canh
Ban la mot coding agent dang lam viec truc tiep trong codebase Laravel cua toi tai:

- `d:\xampp\htdocs\thuctap_khaitri`

Day la repo Laravel 12 / PHP 8.2. He thong dang dung naming tieng Viet cho phan lon bang, cot va migration nghiep vu.

Toi muon ban ra soat toan bo project, kiem tra tat ca migration, doi chieu voi code dang dung that, va dua bo migration ve mot trang thai thong nhat, gon, de doc, de bao tri, nhung KHONG duoc lam thay doi logic nghiep vu dang hoat dong.

Ban duoc phep:
- sua migration active neu that su can thiet va co the build schema an toan
- tao migration cleanup moi neu do la cach an toan hon
- bien migration lich su thanh compatibility stub neu can giu tuong thich `migrations` ledger
- xoa file migration/file code du thua neu da co bang chung ro rang la khong con duoc dung

Ban KHONG duoc:
- lam thay doi logic nghiep vu dang dung
- doi ten bang/cot chi de "dep" neu khong co loi ich thuc te
- xoa bang/cot/index/file chi vi "co ve cu"
- dung DB local that de thu nghiem kieu pha huy du lieu


# Boi canh thuc te cua repo nay
Truoc khi lam, hay coi nhung diem sau la rang buoc bat buoc:

1. Nguon migration active:
- `database/migrations`

2. Co archive migration cu:
- `database/migrations/legacy_archive_2026_03_28`

3. Co tai lieu audit schema san trong repo:
- `docs/database-schema-audit-2026-03-28.md`
- `docs/database-schema-audit-2026-04-09.md`

4. Khong duoc coi `database/database.sqlite` la source of truth cua schema hien tai.

5. Hien tai active migration da co 42 file. Archive cu chi de doi chieu lich su, khong mac dinh la build path active.

6. Co 3 migration lich su lien quan den nhanh `giang_vien_lich_ranh` da duoc xu ly theo huong compatibility:
- `database/migrations/2026_03_29_000100_tao_bang_giang_vien_lich_ranh.php`
- `database/migrations/2026_03_30_000100_bo_sung_tiet_hoc_cho_lich_hoc_va_lich_ranh.php`
- `database/migrations/2026_03_31_000100_loai_bo_bang_giang_vien_lich_ranh.php`

Luu y rat quan trong:
- Bang `giang_vien_lich_ranh` KHONG nam trong schema cuoi cung.
- Hai migration ngay `2026_03_29` va `2026_03_31` co the dang la compatibility stub.
- Migration `2026_03_30_000100...` van giu lai phan bo sung 3 cot cho `lich_hoc` la:
  - `tiet_bat_dau`
  - `tiet_ket_thuc`
  - `buoi_hoc`
- Ba cot nay dang duoc code hien tai su dung that, tuyet doi khong duoc xoa nham.

7. Mot so nhom bang dang duoc code hien tai su dung ro rang:
- Ha tang Laravel: `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`, `sessions`
- Nguoi dung: `nguoi_dung`, `giang_vien`, `hoc_vien`, `tai_khoan_cho_phe_duyet`
- Ho tro he thong: `system_settings`, `banners`, `thong_bao`
- Dao tao: `nhom_nganh`, `khoa_hoc`, `module_hoc`, `phan_cong_module_giang_vien`, `hoc_vien_khoa_hoc`, `lich_hoc`, `diem_danh`, `diem_danh_giang_vien`, `yeu_cau_hoc_vien`, `giang_vien_don_xin_nghi`, `ket_qua_hoc_tap`
- Tai nguyen va bai giang: `tai_nguyen_buoi_hoc`, `bai_giangs`, `bai_giang_tai_nguyen`
- Live room: `phong_hoc_live`, `phong_hoc_live_nguoi_tham_gia`, `phong_hoc_live_ban_ghi`
- Kiem tra danh gia: `bai_kiem_tra`, `ngan_hang_cau_hoi`, `dap_an_cau_hoi`, `chi_tiet_bai_kiem_tra`, `bai_lam_bai_kiem_tra`, `chi_tiet_bai_lam_bai_kiem_tra`
- Giam sat thi: `bai_lam_snapshot_giam_sat`, `bai_lam_vi_pham_giam_sat`


# Muc tieu cu the
Ban phai lam duoc dong thoi 7 viec sau:

1. Audit toan bo migration va map ra schema cuoi cung dang duoc he thong ky vong.
2. Doi chieu schema voi toan bo code dang dung that trong model, controller, service, view, request, command, test.
3. Tim migration/file schema du thua, trung chuc nang, mang tinh lich su, hoac chi con y nghia compatibility.
4. Chuan hoa migration thanh mot he thong co cau truc ro rang, naming de theo doi, thu tu phu thuoc FK an toan.
5. Khong lam thay doi logic nghiep vu, khong lam mat schema dang duoc code hien tai su dung.
6. Neu co file du thua that su, ban co the xoa; neu can giu vi ledger/compatibility thi chuyen thanh stub thay vi xoa bua.
7. Verify lai bang migrate tren moi truong an toan va chay test.


# Pham vi bat buoc phai doc
Truoc khi sua gi, ban bat buoc phai doc va scan toi thieu:

## A. Database
- `database/migrations`
- `database/migrations/legacy_archive_2026_03_28`
- `database/seeders`
- `database/factories` neu co lien quan

## B. Eloquent va domain
- `app/Models`
- `app/Services`
- `app/Support`
- `app/Console`

## C. Noi co the rang buoc schema
- `app/Http/Controllers`
- `app/Http/Requests`
- `resources/views`
- `routes`
- `tests/Feature`
- `tests/Unit`

## D. Tai lieu co san
- `docs/database-schema-audit-2026-03-28.md`
- `docs/database-schema-audit-2026-04-09.md`


# Tu duy bat buoc
Phai phan loai moi bang/cot/index/migration vao mot trong cac nhom sau:

- `Dang dung ro rang`
- `Dang dung gian tiep qua code/test`
- `Legacy nhung con gia tri`
- `Compatibility stub can giu`
- `Ten xau nhung chua nen dung`
- `Co the gop/chuan hoa`
- `Nghi rac / co the xoa`
- `Rui ro cao, tam thoi giu nguyen`

Nguyen tac:
- Uu tien an toan he thong hon tham my.
- Uu tien bang chung tu code hon cam giac "co ve thua".
- Uu tien cleanup toi thieu, chinh xac, co kiem chung.
- Neu 1 migration da tung chay tren moi truong that, phai can nhac giu filename de tuong thich ledger.
- Neu can chuan hoa, uu tien tao migration cleanup moi hoac gop schema active mot cach co kiem soat.


# Cach danh gia mot file migration co duoc giu, sua hay xoa
Voi moi migration active va migration archive, hay tra loi:

1. No tao bang gi, sua bang gi, drop bang gi?
2. Bang/cot/index do co dang duoc model nao dung khong?
3. Co duoc controller/service/request/view/test goi toi khong?
4. Co phai schema cuoi cung dang ton tai trong he thong khong?
5. No la migration active thuc su, migration lich su, hay compatibility stub?
6. Neu bo di thi:
- co anh huong `migrate:fresh` khong?
- co anh huong toi moi truong da co ledger migration khong?
- co anh huong toi code hien tai khong?


# Muc tieu chuan hoa
Ban phai co gang dua bo migration ve mot the thong nhat theo cac nguyen tac sau:

1. Migration active phai phan anh schema cuoi cung dang dung that.
2. Migration lich su/legacy khong nen nam trong build path active neu no khong con gia tri xay schema.
3. Khong de nhanh migration kieu "tao bang -> them cot -> xoa bang" neu bang do khong ton tai trong schema cuoi, tru khi can giu vi ledger compatibility.
4. Naming convention cho migration moi phai thong nhat:
- Tao bang: `YYYY_MM_DD_HHMMSS_tao_bang_<ten_bang>.php`
- Bo sung cot: `YYYY_MM_DD_HHMMSS_bo_sung_<nhom_cot>_cho_<ten_bang>.php`
- Cap nhat index: `YYYY_MM_DD_HHMMSS_cap_nhat_index_<ten_bang>.php`
- Cleanup rac: `YYYY_MM_DD_HHMMSS_don_dep_<muc_tieu>.php`
5. Khong doi ten hang loat migration lich su chi de dep ten. Chi chuan hoa cho phan canonical active ve sau.


# Chien luoc thuc hien bat buoc
Lam viec theo dung thu tu sau:

## Phase 1: Inventory
- Liet ke toan bo migration active theo thu tu.
- Liet ke toan bo migration archive theo thu tu.
- Danh dau migration nao la create, alter, cleanup, compatibility stub.
- Ve so do dependency FK giua cac bang.

## Phase 2: Map voi code thuc te
- Quet toan repo de map bang/cot/index voi:
  - model
  - relation
  - validation
  - query builder/Eloquent
  - service
  - blade/view
  - test
- Viet ro bang nao duoc dung truc tiep, bang nao dung gian tiep, bang nao khong co bang chung su dung.

## Phase 3: Xac dinh canonical schema
- Chot schema canonical ma code hien tai dang su dung.
- Chot bang/cot/index nao bat buoc giu.
- Chot migration nao la rac thuc su.
- Chot migration nao chi nen chuyen thanh stub thay vi xoa.

## Phase 4: Cleanup co kiem soat
Neu da co bang chung du manh, ban duoc phep:
- gop migration active theo huong de build schema ro rang hon
- xoa migration/file du thua that su
- chuyen migration lich su thanh stub neu can giu ledger compatibility
- cap nhat model/test/code lien quan neu thay doi la bat buoc de schema moi build dung

Nhung van phai tuan thu:
- khong doi logic nghiep vu
- khong drop bang/cot dang duoc dung
- khong xoa cac bang ha tang Laravel chi vi tuong la rac

## Phase 5: Verify an toan
Bat buoc verify bang:
- `php artisan migrate:status`
- build schema tren moi truong test/an toan
- `php artisan test`

Neu can build schema tu dau, hay dung SQLite tam rieng hoac test env.
Khong duoc pha `database/database.sqlite` neu do la file local cua toi.


# Nhung diem can soi ky trong repo nay
Ban phai uu tien kiem tra rat ky cac diem sau:

1. Nhanh `giang_vien_lich_ranh`
- Xac nhan bang nay khong con trong schema cuoi.
- Xac nhan khong con model/controller/service/test/view nao dung no.
- Neu da la nhanh rac lich su thi giu theo huong compatibility stub, khong de no lam ban build schema moi.

2. Cot lich hoc dang dung that
- `lich_hoc.tiet_bat_dau`
- `lich_hoc.tiet_ket_thuc`
- `lich_hoc.buoi_hoc`

3. Cac bang moi duoc them sau dot tach 33 migration
- `giang_vien_don_xin_nghi`
- `diem_danh_giang_vien`
- `bai_lam_snapshot_giam_sat`
- `bai_lam_vi_pham_giam_sat`
- `ket_qua_hoc_tap` va cac migration bo sung/index cua no

Khong duoc coi nhung bang nay la rac neu con co model/service/test tham chieu.

4. Cac migration ten xau nhung khong nhat thiet phai sua
- `tao_bang_*`
- `bo_sung_*`
- `cap_nhat_*`

Ten khong dep KHONG dong nghia voi nen sua lich su.


# Tieu chi de xoa file hoac migration
Chi duoc xoa neu dong thoi thoa man:

1. Khong con tham gia build schema canonical.
2. Khong con gia tri compatibility ledger hoac da duoc thay bang stub an toan.
3. Khong con duoc model/controller/service/request/view/test/seeder/factory nao tham chieu.
4. Khong lam mat logic nghiep vu.
5. Da verify lai `migrate` va `test` sau khi cleanup.

Neu chua du 5 dieu kien nay, khong duoc xoa.


# Tieu chi thanh cong
Ket qua cuoi cung phai dat:

1. Bo migration active gon hon, ro hon, thong nhat hon.
2. Schema build ra dung voi code hien tai.
3. Khong co bang/cot rac ro rang trong build path active.
4. Migration lich su duoc xu ly dung:
- giu archive neu can doi chieu
- giu stub neu can ledger compatibility
- xoa chi khi that su du bang chung
5. Khong co regression logic nghiep vu.
6. Test suite van pass.


# Yeu cau bao cao truoc khi sua manh
Truoc khi thuc hien cleanup dien rong, ban phai bao cao ngan gon nhung du cac muc sau:

1. Tong so migration active va archive
2. Danh sach domain schema
3. Nhung migration dang la candidate cleanup
4. Nhung migration/file nghi la compatibility stub
5. Nhung bang/cot/index nghi rac
6. Nhung muc chi xau ve naming nhung chua nen dung
7. Ke hoach cleanup an toan nhat


# Yeu cau bao cao sau khi hoan thanh
Sau khi xong, ban phai bao cao:

1. File migration nao da sua
2. File migration/file code nao da xoa
3. File nao duoc giu lai du la ten xau, va ly do
4. Bang/cot/index nao da xoa hoac giu
5. Co migration stub nao duoc tao/giu lai de compatibility hay khong
6. Ket qua verify:
- `php artisan migrate:status`
- neu co build tren SQLite tam: ket qua
- `php artisan test`


# Lenh goi y
Ban co the dung cac lenh sau de audit:

```powershell
Get-ChildItem database/migrations | Sort-Object Name
```

```powershell
Get-ChildItem database/migrations/legacy_archive_2026_03_28 | Sort-Object Name
```

```powershell
rg -n "Schema::create|Schema::table|Schema::rename|Schema::drop|dropIfExists|renameColumn|dropColumn" database/migrations database/migrations/legacy_archive_2026_03_28
```

```powershell
rg -n "protected \\$table|belongsTo|hasMany|belongsToMany|hasOne|morph|foreignIdFor|DB::table|->join\\(|->leftJoin\\(|assertDatabaseHas|assertDatabaseMissing" app tests
```

```powershell
rg -n "giang_vien_lich_ranh|tiet_bat_dau|tiet_ket_thuc|buoi_hoc|ket_qua_hoc_tap|diem_danh_giang_vien|giam_sat" app tests resources database
```

```powershell
php artisan migrate:status
```

```powershell
php artisan test
```


# Bat dau
Bay gio hay:

1. Doc toan bo migration active
2. Doc migration archive de doi chieu
3. Scan toan bo repo de map schema voi code that
4. Phan loai migration/bang/cot theo muc do can giu hay co the cleanup
5. Chot schema canonical
6. Cleanup mot cach an toan
7. Verify lai bang migrate va test

Neu co cho nao rui ro cao, uu tien dung lai va ghi ro ly do thay vi xoa bua.
