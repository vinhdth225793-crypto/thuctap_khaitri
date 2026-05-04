# LearnTest Online — Cổng Học Tập và Kiểm Tra Trực Tuyến

[![CI](https://github.com/vinhdth225793-crypto/thuctap_khaitri/actions/workflows/ci.yml/badge.svg)](https://github.com/vinhdth225793-crypto/thuctap_khaitri/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)](https://laravel.com/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

> Đồ án thực tập cuối khoá — Khoa Công nghệ Thông tin, Trường Đại học An Giang.
> Hệ thống quản lý học tập (LMS) cho Trung tâm Tin học Khai Trí, hỗ trợ khoá học, lịch giảng, bài kiểm tra trực tuyến có giám sát và chấm điểm tự luận.

---

## 1. Giới thiệu

LearnTest Online là cổng học tập và kiểm tra trực tuyến phục vụ vận hành đào tạo tại **Trung tâm Tin học Khai Trí — An Giang**. Hệ thống gồm 3 vai trò người dùng với quy trình nghiệp vụ khép kín:

- **Quản trị viên (admin)** — quản lý tài khoản, khoá học, module, phân công giảng viên, phê duyệt nội dung và kết quả, thiết lập hệ thống.
- **Giảng viên (giang_vien)** — nhận lớp được phân công, tạo bài giảng / bài kiểm tra / tài liệu, điểm danh, chấm điểm tự luận, phòng học live, đơn xin nghỉ.
- **Học viên (hoc_vien)** — học theo lộ trình, tham gia bài kiểm tra có **giám sát thi (camera + fullscreen + chống tab-switch)**, xem kết quả và tiến độ.

### Tính năng nổi bật

| Tính năng | Mô tả |
|---|---|
| Giám sát thi online | Snapshot camera định kỳ, ép fullscreen, log vi phạm, tự động nộp khi vượt ngưỡng |
| Tổng hợp kết quả 5 chiến lược | Trung bình, cao nhất, chọn lượt thi cụ thể, cuối cùng, theo chuyên cần |
| Phòng học live | Tích hợp Google Meet, ghi nhận điểm danh thực tế qua link tham gia |
| Ngân hàng câu hỏi | Trắc nghiệm + tự luận, import từ Excel/Word/PDF, tái sử dụng giữa nhiều đề |
| Phê duyệt 2 lớp | Giảng viên gửi nội dung → admin duyệt → phát hành cho học viên |
| Đơn xin nghỉ | Giảng viên xin nghỉ → admin duyệt → tự động cập nhật lịch học |

---

## 2. Stack công nghệ

- **Ngôn ngữ**: PHP 8.2
- **Framework**: Laravel 12
- **Cơ sở dữ liệu**: MySQL 8 / MariaDB (XAMPP)
- **Frontend**: Blade + Bootstrap 5 + jQuery + Vite
- **Test**: PHPUnit 11 + RefreshDatabase
- **Code style**: Laravel Pint (PSR-12)
- **CI/CD**: GitHub Actions
- **Email/SMS Notification**: hệ thống `NotificationService` nội bộ

---

## 3. Yêu cầu hệ thống

- PHP **>= 8.2** (cần extension: `mbstring`, `dom`, `fileinfo`, `pdo_mysql`, `zip`, `gd`)
- Composer >= 2.5
- Node.js >= 18 + npm
- MySQL >= 8 (hoặc MariaDB tương đương)
- (Khuyến nghị) XAMPP cho phát triển trên Windows

---

## 4. Hướng dẫn cài đặt

### Bước 1 — Clone repo

```bash
git clone https://github.com/vinhdth225793-crypto/thuctap_khaitri.git
cd thuctap_khaitri
```

### Bước 2 — Cài dependency

```bash
composer install
npm install
```

### Bước 3 — Cấu hình `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Mở `.env`, sửa thông tin database:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thuctap_khaitri
DB_USERNAME=root
DB_PASSWORD=
```

Tạo database trong phpMyAdmin (hoặc lệnh CLI):

```sql
CREATE DATABASE thuctap_khaitri CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Bước 4 — Migrate + Seed dữ liệu mẫu

```bash
php artisan migrate --seed
```

Lệnh này sẽ:
- Tạo toàn bộ schema (54 migration chính)
- Seed dữ liệu mẫu: tài khoản demo, khoá học, module, lịch học, bài kiểm tra…

### Bước 5 — Build frontend & chạy server

```bash
npm run build      # build asset cho production
php artisan serve  # mở http://127.0.0.1:8000
```

Khi phát triển dev:

```bash
composer dev   # chạy đồng thời artisan serve + queue + pail + vite
```

---

## 5. Tài khoản demo

> Tài khoản dưới đây được tạo bởi `DemoSeeder` khi chạy `php artisan migrate --seed`.

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Quản trị viên | `admin@khaitri.local` | `password` |
| Giảng viên | `gv1@khaitri.local` | `password` |
| Học viên | `hv1@khaitri.local` | `password` |

> Truy cập trang đăng nhập: `http://127.0.0.1:8000/dang-nhap`

---

## 6. Cấu trúc thư mục chính

```
app/
├── Console/Commands/        # Artisan commands (giám sát buổi học, sync lịch)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/           # 9 controller quản trị (Dashboard, Cài đặt, Phê duyệt…)
│   │   ├── GiangVien/       # 11 controller giảng viên
│   │   ├── HocVien/         # 2 controller học viên
│   │   └── …
│   └── Middleware/          # CheckAdmin / CheckGiangVien / CheckHocVien
├── Models/                  # 38 Eloquent model (đặt tên tiếng Việt)
├── Observers/               # ModuleHocObserver
└── Services/                # 41+ service domain (ExamSurveillance, Scheduling, …)

database/
├── migrations/              # 54 migration + 3 thư mục archive legacy
└── seeders/                 # DemoSeeder, NhomNganhSeeder, …

resources/views/
├── components/              # Blade component (sidebar-*, stat-tile, confirm-modal…)
├── layouts/app.blade.php
└── pages/
    ├── admin/
    ├── giang-vien/
    └── hoc-vien/

routes/
└── web.php                  # Route cho 3 vai trò

tests/
├── Feature/                 # 28 test feature
└── Unit/
```

---

## 7. Tính năng theo vai trò

### 7.1. Quản trị viên (admin)

- **Dashboard** — thống kê người dùng / khoá học / module + việc cần xử lý ngay
- **Quản lý tài khoản** — CRUD học viên / giảng viên + phê duyệt đăng ký mới
- **Đào tạo** — nhóm ngành, khoá học, module, phân công giảng viên
- **Lịch học** — sắp xếp buổi học, theo dõi tiến độ
- **Phê duyệt** — bài giảng, tài nguyên thư viện, đề thi, kết quả học tập
- **Đơn xin nghỉ** — phê duyệt đơn nghỉ giảng viên + tự cập nhật lịch
- **Cài đặt** — thông tin hệ thống, banner, mạng xã hội, giảng viên hiển thị trang chủ

### 7.2. Giảng viên (giang_vien)

- **Lộ trình giảng dạy** — xem khoá học được phân công, vào dạy
- **Lịch dạy** — buổi học sắp tới, điểm danh check-in/check-out
- **Bài giảng & thư viện tài nguyên** — soạn nội dung, gửi duyệt
- **Bài kiểm tra** — tạo đề (trắc nghiệm / tự luận / hỗn hợp), import câu hỏi từ file, cấu hình giám sát
- **Chấm điểm** — chấm tự luận thủ công, hậu kiểm vi phạm giám sát
- **Phòng học live** — tạo link Google Meet, theo dõi học viên tham gia
- **Đơn xin nghỉ** — gửi đơn, theo dõi trạng thái duyệt

### 7.3. Học viên (hoc_vien)

- **Khoá học của tôi** — nội dung, lịch học, tài liệu, bài giảng
- **Bài kiểm tra** — làm bài trực tuyến với giám sát camera + fullscreen
- **Hoạt động & tiến độ** — theo dõi hoàn thành buổi học, điểm số
- **Kết quả học tập** — xem điểm chính thức theo từng module/khoá
- **Khám phá** — gửi yêu cầu vào lớp mới

---

## 8. Lệnh thường dùng

```bash
# Test
php artisan test                           # chạy toàn bộ test
php artisan test --filter=ExamFlow         # chạy test cụ thể
php artisan test --coverage                # với coverage (cần pcov/xdebug)

# Code style
vendor/bin/pint                            # format toàn codebase
vendor/bin/pint --test                     # check không sửa (CI dùng)

# Reset database
php artisan migrate:fresh --seed           # tạo lại toàn bộ schema + seed mới

# Scheduled command (cron) — giám sát buổi học, đồng bộ lịch
php artisan schedule:work                  # chạy ở dev
# Production: thêm cron `* * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1`
```

---

## 9. CI/CD

Mỗi lần push hoặc PR vào `main`, GitHub Actions sẽ tự động chạy:

1. Cài PHP 8.2 + Composer dependencies
2. Kiểm tra code style với **Laravel Pint**
3. Chạy toàn bộ test PHPUnit với SQLite in-memory

Workflow: [.github/workflows/ci.yml](.github/workflows/ci.yml)

---

## 10. Quy ước code

- **Naming**: Tiếng Việt không dấu, snake_case cho bảng/cột (`nguoi_dung`, `khoa_hoc`, `bai_kiem_tra`); PascalCase cho class.
- **Service layer**: Logic nghiệp vụ phức tạp đặt trong [`app/Services/`](app/Services), không nhồi vào controller.
- **Authorization**: Middleware role-based + trait `XacThucGiangVienBaiKiemTra` cho check phân công.
- **Commit message**: Conventional Commits (`feat:`, `fix:`, `refactor:`, `style:`, `chore:`, `ci:`, `docs:`).
- **Code style**: PSR-12 / Laravel preset, kiểm tra bằng Pint.

---

## 11. Tác giả

| Thông tin | Chi tiết |
|---|---|
| Sinh viên | **Mai Phạm Phước Vinh** |
| MSSV | DTH225793 |
| Lớp | DH22TH (Công nghệ Thông tin) |
| Khoa | Công nghệ Thông tin |
| Trường | Đại học An Giang |
| Giảng viên hướng dẫn | **ThS. Nguyễn Thị Lan Quyên** |
| Cơ quan thực tập | Hệ thống Giáo dục & Đào tạo Khai Trí — Trung tâm Tin học Khai Trí An Giang |
| Thời gian thực tập | 23/02/2026 – 19/04/2026 |

---

## 12. License

MIT License. Xem [LICENSE](LICENSE) (nếu có) hoặc theo Laravel framework license.
