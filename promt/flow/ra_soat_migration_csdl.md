# Boi canh
Ban la mot coding agent dang lam viec truc tiep tren codebase Laravel cua toi.

Van de hien tai cua du an:
- migration duoc tao theo tung dot phat sinh tinh nang, nen co cho thieu dong bo
- co kha nang ton tai bang, cot, index, foreign key hoac migration thua
- co the co schema rac, schema legacy, hoac cau truc bi trung chuc nang
- ten migration va naming convention giua cac giai doan chua that su thong nhat
- toi muon ra toan bo CSDL de chuan hoa, gom theo luong nghiep vu, don phan rac, nhung khong lam hong he thong

Thong tin thuc te hien tai cua repo:
- migration active dang nam trong `database/migrations`
- co thu muc archive cu: `database/migrations/legacy_archive_2026_03_28`
- co file DB local: `database/database.sqlite`
- co seeders trong `database/seeders`
- co models trong `app/Models`

Muc tieu cua ban la:
- audit toan bo migration, schema va quan he du lieu
- doi chieu schema voi code dang dung that
- xac dinh phan nao dang dung, phan nao legacy nhung con gia tri, phan nao la rac hoac du thua
- chuan hoa logic to chuc schema theo tung nghiep vu
- xoa hoac gop phan rac mot cach an toan
- khong lam gay ung dung, khong lam mat du lieu dang can cho he thong


# Tu duy bat buoc
KHONG duoc xoa bang/cot/index chi vi thay "co ve cu".
KHONG duoc sua migration lich su theo cam tinh.
KHONG duoc rename bang/cot chi de dep ten neu rui ro cao hon loi ich.
KHONG duoc dung truc tiep database dang dung that theo kieu pha huy du lieu.

Ban phai phan biet ro:
- `schema dang dung that`
- `schema legacy nhung con phuc vu backward compatibility`
- `schema du thua hoac trung chuc nang`
- `migration dat ten xau nhung van an toan neu giu nguyen`

Uu tien an toan he thong hon tham my.


# Pham vi bat buoc phai doc
Truoc khi sua bat cu thu gi, ban phai doc va doi chieu toi thieu cac khu vuc sau:

## 1. Migration va seed
- `database/migrations`
- `database/seeders`
- neu can: `database/factories`

## 2. Model va quan he du lieu
- `app/Models`

## 3. Nhung noi co the dung bang/cot truc tiep hoac gian tiep
- `app/Http/Controllers`
- `app/Services`
- `app/Support`
- `app/Console`
- `resources/views`
- `tests/Feature`
- `tests/Unit`

## 4. Nhung noi co the sinh truy van hoac ky vong schema
- `Schema::create`
- `Schema::table`
- `Schema::rename`
- `Schema::drop`
- `$table->foreignId`
- `$table->foreign`
- `$table->index`
- `$table->unique`
- `$table->dropColumn`
- `$table`, `$fillable`, `$casts`, relationship methods trong model
- `assertDatabaseHas`, `assertDatabaseMissing`
- query builder / Eloquent filter theo ten cot


# Muc tieu dau ra
Ban phai lam duoc 6 viec:

1. Lap ban do schema hien tai
- migration nao tao/chinh bang nao
- bang nao co model tuong ung
- bang nao duoc dung trong controller/service/test
- bang nao co seed/factory

2. Nhom toan bo schema theo nghiep vu
Vi du toi thieu phai nhom duoc:
- tai khoan, xac thuc, phien, cache, jobs
- nguoi dung, giang vien, hoc vien, phe duyet tai khoan
- cai dat he thong, banner, thong bao
- nhom nganh, khoa hoc, module, phan cong, lich hoc, diem danh, yeu cau hoc vien, ket qua hoc tap
- tai nguyen, bai giang, thu vien, live room
- bai kiem tra, ngan hang cau hoi, dap an, bai lam, giam sat thi

3. Phan loai tung bang/cot/index/migration vao nhom
- `Dang dung ro rang`
- `Legacy nhung con gia tri`
- `Ten chua dep nhung chua nen dung`
- `Trung chuc nang / co the gop`
- `Nghi rac / co the xoa`
- `Rui ro cao, can giu`

4. Chuan hoa naming convention
Ban phai de xuat quy chuan ro rang cho:
- ten migration moi
- ten bang
- ten pivot table
- ten foreign key
- ten index
- cach dat ten cot trang thai / co boolean / timestamp

5. Chi cleanup nhung phan co bang chung an toan
- bang khong con duoc model, service, test, seeder, factory nao dung
- cot khong con reference trong code
- index hoac foreign key du thua
- migration archive khong con tham gia luong active
- cac cau truc trung nghiep vu va da co cau truc canonical thay the ro rang

6. Verify toan he thong sau cleanup
- migration phai chay duoc tren DB sach
- test phai van pass
- schema cuoi cung phai phan anh dung nghiep vu hien tai


# Cac diem nghi van can uu tien ra ky
Day la nhung vung ban phai soi ky, nhung chua duoc phep xoa neu chua du bang chung:

1. Lich su migration cu
- thu muc `database/migrations/legacy_archive_2026_03_28`
- can xac dinh day chi la archive tham khao hay con cho nao phu thuoc

2. Ten migration khong dong nhat giua cac giai doan
- co giai doan dung kieu `tao_bang_*`, `bo_sung_*`, `cap_nhat_*`
- co giai doan cu dung kieu `create_*`, `refactor_*`, `cleanup_*`, `finalize_*`
- can phan biet: cai nao chi xau ve ten, cai nao that su gay roi luong schema

3. Bang/cot Laravel mac dinh
- `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`, `sessions`
- khong duoc xoa chi vi tuong la rac; phai kiem tra chung co dang phuc vu he thong hay khong

4. Nhung bang/ban ghi theo luong nghiep vu co the bi tach nho hoac chong cheo
- luong nguoi dung
- luong dao tao
- luong bai giang va tai nguyen
- luong thi online va giam sat thi

5. Nhung noi co dau hieu "cleanup chong cleanup"
- migration kieu them roi sua roi refactor roi finalize
- phai kiem tra xem ket qua cuoi cung co dang mang cot thua, index thua, hoac quan he thua khong


# Cach lam bat buoc
Lam dung theo cac phase sau.

## Phase 1: Audit migration inventory
Ban phai:
- doc toan bo file trong `database/migrations`
- lap danh sach migration active theo thu tu thoi gian
- ghi ro migration nao tao bang moi, migration nao chi chinh bang cu
- tach rieng phan archive cu de doi chieu

Ket qua phase nay phai co:
- danh sach bang hien tai
- migration tao ra bang do
- migration nao tiep tuc sua bang do

Chua cleanup o phase nay.

## Phase 2: Map schema voi code that
Ban phai scan toan bo repo de biet:
- bang nao co model
- model nao co relationship
- cot nao duoc dung trong query/validation/test
- bang nao co seed/factory
- bang nao chi ton tai trong migration nhung khong co bat ky dau hieu dung that nao

Voi moi candidate nghi rac, phai ghi duoc:
- ten bang/cot/index
- dang duoc tham chieu o dau
- khong duoc tham chieu o dau
- neu xoa thi anh huong file nao
- muc rui ro

## Phase 3: De xuat schema chuan theo nghiep vu
Ban phai tao mot ban de xuat ro rang:
- nhom nghiep vu nao nen giu nguyen
- nhom nao can gop
- bang/cot nao nen doi ten nhung chua nen lam ngay
- bang/cot nao co the don ngay
- migration nao nen de nguyen lich su
- migration nao co the thay bang migration cleanup moi

Quan trong:
- uu tien tao migration cleanup moi hon la sua file migration lich su da tung chay
- chi can nhac sua migration lich su neu repo nay duoc xac nhan la con trong giai doan co the reset toan bo schema va ban verify duoc `migrate:fresh`

## Phase 4: Thuc hien cleanup an toan
Chi khi da co du bang chung, ban moi duoc:
- tao migration moi de drop bang/cot/index rac
- tao migration moi de gop/chuan hoa quan he neu that su can
- cap nhat model, relation, service, controller, test neu phai doi schema
- xoa phan migration/archive rac chi khi chac chan khong con dung de build schema active

Khong duoc:
- xoa du lieu thuc cua nguoi dung ma khong co chien luoc migrate du lieu
- rename bang/cot quy mo lon chi de dong bo ten neu khong co loi ich thuc te

## Phase 5: Verify tren moi truong an toan
Ban bat buoc phai verify bang cach an toan.

Uu tien:
- `php artisan migrate:status`
- chay test
- neu can kiem tra luong build schema tu dau, dung mot SQLite tam rieng de `migrate:fresh`

KHONG duoc pha file DB dang dung that theo kieu:
- ghi de bua `database/database.sqlite`
- chay `migrate:fresh` len DB dang co du lieu ma khong kiem soat

Neu can DB tam, hay tao file SQLite tam rieng chi de verify.


# Quy tac an toan
1. Khong sua migration lich su dang active chi de doi ten dep.

2. Neu mot migration ten xau nhung schema cuoi cung dang on, co the giu nguyen va chi chuan hoa tu cac migration moi ve sau.

3. Neu mot bang/cot khong thay dung trong controller/view nhung van co:
- model
- relationship
- test
- seed
- factory
thi van phai coi la dang dung.

4. Neu mot bang dang co du lieu nghiep vu quan trong, khong duoc drop truc tiep.
Neu muon gop, phai co chien luoc migrate du lieu.

5. Khong xoa cac bang ha tang Laravel neu chua chung minh la khong dung:
- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`
- `password_reset_tokens`
- `sessions`

6. Thu muc `legacy_archive_2026_03_28` phai duoc coi la vung tham chieu lich su.
Chi xoa hoac chinh neu ban da chung minh:
- schema active khong phu thuoc
- khong con tai lieu/quy trinh nao dung no
- repo da co migration active thay the day du

7. Khong cleanup dua tren suy doan.
Moi hanh dong drop/rename/gop deu phai co bang chung tu code.


# Lenh goi y
Ban co the dung cac lenh kieu:

```powershell
Get-ChildItem database/migrations | Sort-Object Name
```

```powershell
php artisan migrate:status
```

```powershell
rg -n "Schema::create|Schema::table|Schema::rename|Schema::drop|dropIfExists|renameColumn|dropColumn" database/migrations
```

```powershell
rg -n "protected \\$table|belongsTo|hasMany|belongsToMany|hasOne|morph" app/Models
```

```powershell
rg -n "assertDatabaseHas|assertDatabaseMissing|DB::table|->join\\(|->leftJoin\\(|->where\\(" tests app
```

```powershell
rg -n "bai_lam_|khoa_hoc|module_hoc|lich_hoc|ngan_hang_cau_hoi|tai_nguyen|phong_hoc_live" app tests database
```


# Yeu cau bao cao truoc khi sua manh
Truoc khi cleanup dien rong, ban phai bao cao:

1. Tong so migration active
2. Co archive cu hay khong
3. Danh sach domain nghiep vu
4. Nhung bang/cot/index nghi rac
5. Nhung migration ten khong dong nhat
6. Nhung cho chi xau ve naming nhung chua nen dung
7. Nhung cho that su co the cleanup an toan
8. Rui ro neu cleanup


# Yeu cau bao cao sau khi hoan thanh
Sau khi lam xong, ban phai bao cao:

1. Nhung bang/cot/index da xoa hoac giu lai
2. Nhung migration cleanup moi da tao
3. Nhung file code/test da cap nhat theo schema moi
4. Nhung phan nghi ngo nhung chua dam dung vi rui ro
5. Ket qua verify:
- `php artisan migrate:status`
- neu co dung DB tam: ket qua `migrate:fresh`
- `php artisan test`


# Ky vong cuoi cung
Ket qua tot la:
- schema gon hon va ro theo nghiep vu
- khong con bang/cot/index rac co bang chung ro rang
- naming convention duoc thong nhat cho tuong lai
- phan legacy nguy hiem duoc ghi chu ro, khong don bua
- he thong van chay on va test van pass


# Bat dau cong viec
Bay gio hay:

1. doc toan bo `database/migrations`
2. doc `database/seeders`
3. doc `app/Models`
4. scan toan repo de map bang/cot voi code va test
5. lap danh sach candidate cleanup theo muc do an toan
6. chi cleanup sau khi co bang chung du manh
7. verify toan he thong tren moi truong an toan
