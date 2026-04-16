<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RepairLegacySchema extends Command
{
    protected $signature = 'app:repair-legacy-schema {--dry-run : Show SQL without executing it}';

    protected $description = 'Add compatibility columns for older local databases without dropping data.';

    public function handle(): int
    {
        if ($this->driver() !== 'mysql') {
            $this->error('This command only supports MySQL legacy databases.');

            return self::FAILURE;
        }

        if (! Schema::hasTable('nguoi_dung')) {
            $this->error('Khong tim thay bang nguoi_dung.');

            return self::FAILURE;
        }

        $this->repairNguoiDung();
        $this->repairPendingAccounts();
        $this->repairCourseCatalog();
        $this->repairLearningContent();
        $this->repairAssessmentColumns();
        $this->repairQuestionBank();
        $this->repairTeacherAssignments();
        $this->repairTeacherLeaveRequests();
        $this->repairHocVien();
        $this->repairHocVienKhoaHoc();
        $this->repairYeuCauHocVien();

        $this->info('Legacy schema repair completed.');

        return self::SUCCESS;
    }

    private function repairNguoiDung(): void
    {
        if (! Schema::hasColumn('nguoi_dung', 'ma_nguoi_dung')) {
            if ($this->driver() === 'mysql') {
                try {
                    $this->statement('ALTER TABLE `nguoi_dung` ADD COLUMN `ma_nguoi_dung` BIGINT UNSIGNED GENERATED ALWAYS AS (`id`) STORED AFTER `id`');
                } catch (Throwable $exception) {
                    $this->warn('Generated column failed, falling back to a copied ma_nguoi_dung column: ' . $exception->getMessage());
                    $this->statement('ALTER TABLE `nguoi_dung` ADD COLUMN `ma_nguoi_dung` BIGINT UNSIGNED NULL AFTER `id`');
                    $this->statement('UPDATE `nguoi_dung` SET `ma_nguoi_dung` = `id` WHERE `ma_nguoi_dung` IS NULL');
                }
            } else {
                $this->statement('ALTER TABLE `nguoi_dung` ADD COLUMN `ma_nguoi_dung` INTEGER NULL');
                $this->statement('UPDATE `nguoi_dung` SET `ma_nguoi_dung` = `id` WHERE `ma_nguoi_dung` IS NULL');
            }
        }

        $this->tryStatement('ALTER TABLE `nguoi_dung` ADD UNIQUE INDEX `nguoi_dung_ma_nguoi_dung_unique` (`ma_nguoi_dung`)');

        if (! Schema::hasColumn('nguoi_dung', 'email_xac_thuc') && Schema::hasColumn('nguoi_dung', 'email_xac_thuc_luc')) {
            $this->statement('ALTER TABLE `nguoi_dung` ADD COLUMN `email_xac_thuc` TIMESTAMP NULL AFTER `trang_thai`');
            $this->statement('UPDATE `nguoi_dung` SET `email_xac_thuc` = `email_xac_thuc_luc` WHERE `email_xac_thuc` IS NULL');
        }
    }

    private function repairPendingAccounts(): void
    {
        if (! Schema::hasTable('tai_khoan_cho_phe_duyet')) {
            $this->statement('CREATE TABLE `tai_khoan_cho_phe_duyet` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `ho_ten` VARCHAR(255) NOT NULL,
                `email` VARCHAR(255) NOT NULL,
                `mat_khau` VARCHAR(255) NOT NULL,
                `vai_tro` VARCHAR(50) NOT NULL DEFAULT \'giang_vien\',
                `so_dien_thoai` VARCHAR(20) NULL,
                `ngay_sinh` DATE NULL,
                `dia_chi` TEXT NULL,
                `trang_thai` VARCHAR(50) NOT NULL DEFAULT \'cho_phe_duyet\',
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `tai_khoan_cho_phe_duyet_email_unique` (`email`)
            )');
        }

        $this->ensurePendingAccountColumns();
        $this->tryStatement('ALTER TABLE `tai_khoan_cho_phe_duyet` ADD UNIQUE INDEX `tai_khoan_cho_phe_duyet_email_unique` (`email`)');
    }

    private function ensurePendingAccountColumns(): void
    {
        if (! Schema::hasColumn('tai_khoan_cho_phe_duyet', 'ho_ten')) {
            $this->statement('ALTER TABLE `tai_khoan_cho_phe_duyet` ADD COLUMN `ho_ten` VARCHAR(255) NULL AFTER `id`');
        }

        if (! Schema::hasColumn('tai_khoan_cho_phe_duyet', 'email')) {
            $this->statement('ALTER TABLE `tai_khoan_cho_phe_duyet` ADD COLUMN `email` VARCHAR(255) NULL AFTER `ho_ten`');
        }

        if (! Schema::hasColumn('tai_khoan_cho_phe_duyet', 'mat_khau')) {
            $this->statement('ALTER TABLE `tai_khoan_cho_phe_duyet` ADD COLUMN `mat_khau` VARCHAR(255) NULL AFTER `email`');
        }

        if (! Schema::hasColumn('tai_khoan_cho_phe_duyet', 'vai_tro')) {
            $this->statement("ALTER TABLE `tai_khoan_cho_phe_duyet` ADD COLUMN `vai_tro` VARCHAR(50) NOT NULL DEFAULT 'giang_vien' AFTER `mat_khau`");
        }

        if (! Schema::hasColumn('tai_khoan_cho_phe_duyet', 'so_dien_thoai')) {
            $this->statement('ALTER TABLE `tai_khoan_cho_phe_duyet` ADD COLUMN `so_dien_thoai` VARCHAR(20) NULL AFTER `vai_tro`');
        }

        if (! Schema::hasColumn('tai_khoan_cho_phe_duyet', 'ngay_sinh')) {
            $this->statement('ALTER TABLE `tai_khoan_cho_phe_duyet` ADD COLUMN `ngay_sinh` DATE NULL AFTER `so_dien_thoai`');
        }

        if (! Schema::hasColumn('tai_khoan_cho_phe_duyet', 'dia_chi')) {
            $this->statement('ALTER TABLE `tai_khoan_cho_phe_duyet` ADD COLUMN `dia_chi` TEXT NULL AFTER `ngay_sinh`');
        }

        if (! Schema::hasColumn('tai_khoan_cho_phe_duyet', 'trang_thai')) {
            $this->statement("ALTER TABLE `tai_khoan_cho_phe_duyet` ADD COLUMN `trang_thai` VARCHAR(50) NOT NULL DEFAULT 'cho_phe_duyet' AFTER `dia_chi`");
        }

        if (! Schema::hasColumn('tai_khoan_cho_phe_duyet', 'created_at')) {
            $this->statement('ALTER TABLE `tai_khoan_cho_phe_duyet` ADD COLUMN `created_at` TIMESTAMP NULL AFTER `trang_thai`');
        }

        if (! Schema::hasColumn('tai_khoan_cho_phe_duyet', 'updated_at')) {
            $this->statement('ALTER TABLE `tai_khoan_cho_phe_duyet` ADD COLUMN `updated_at` TIMESTAMP NULL AFTER `created_at`');
        }
    }

    private function repairLearningContent(): void
    {
        $this->repairLearningResources();
        $this->repairLectures();
        $this->repairLectureResourcePivot();
    }

    private function repairLearningResources(): void
    {
        if (! Schema::hasTable('tai_nguyen_buoi_hoc')) {
            $this->statement('CREATE TABLE `tai_nguyen_buoi_hoc` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `lich_hoc_id` BIGINT UNSIGNED NULL,
                `loai_tai_nguyen` VARCHAR(50) NOT NULL,
                `tieu_de` VARCHAR(255) NOT NULL,
                `mo_ta` TEXT NULL,
                `duong_dan_file` VARCHAR(255) NULL,
                `file_name` VARCHAR(255) NULL,
                `file_extension` VARCHAR(50) NULL,
                `file_size` BIGINT UNSIGNED NULL,
                `mime_type` VARCHAR(255) NULL,
                `link_ngoai` VARCHAR(500) NULL,
                `trang_thai_hien_thi` VARCHAR(50) NOT NULL DEFAULT \'an\',
                `ngay_mo_hien_thi` DATETIME NULL,
                `thu_tu_hien_thi` INT NOT NULL DEFAULT 0,
                `nguoi_tao_id` BIGINT UNSIGNED NULL,
                `vai_tro_nguoi_tao` VARCHAR(50) NULL,
                `trang_thai_duyet` VARCHAR(50) NOT NULL DEFAULT \'da_duyet\',
                `trang_thai_xu_ly` VARCHAR(50) NOT NULL DEFAULT \'khong_ap_dung\',
                `ghi_chu_admin` TEXT NULL,
                `ngay_gui_duyet` DATETIME NULL,
                `ngay_duyet` DATETIME NULL,
                `nguoi_duyet_id` BIGINT UNSIGNED NULL,
                `pham_vi_su_dung` VARCHAR(50) NOT NULL DEFAULT \'ca_nhan\',
                `deleted_at` TIMESTAMP NULL,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_tnbh_duyet_xu_ly` (`trang_thai_duyet`, `trang_thai_xu_ly`),
                INDEX `idx_tnbh_nguoi_tao_pham_vi` (`nguoi_tao_id`, `pham_vi_su_dung`)
            )');
        }

        $this->ensureLearningResourceColumns();
        $this->copyLegacyLearningResources();
        $this->tryStatement('ALTER TABLE `tai_nguyen_buoi_hoc` ADD INDEX `idx_tnbh_duyet_xu_ly` (`trang_thai_duyet`, `trang_thai_xu_ly`)');
        $this->tryStatement('ALTER TABLE `tai_nguyen_buoi_hoc` ADD INDEX `idx_tnbh_nguoi_tao_pham_vi` (`nguoi_tao_id`, `pham_vi_su_dung`)');
    }

    private function ensureLearningResourceColumns(): void
    {
        $this->addNullableBigInteger('tai_nguyen_buoi_hoc', 'lich_hoc_id', 'id');

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'loai_tai_nguyen')) {
            $this->statement("ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `loai_tai_nguyen` VARCHAR(50) NOT NULL DEFAULT 'tai_lieu_khac' AFTER `lich_hoc_id`");
        }

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'tieu_de')) {
            $this->statement("ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `tieu_de` VARCHAR(255) NOT NULL DEFAULT '' AFTER `loai_tai_nguyen`");
        }

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'mo_ta')) {
            $this->statement('ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `mo_ta` TEXT NULL AFTER `tieu_de`');
        }

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'duong_dan_file')) {
            $this->statement('ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `duong_dan_file` VARCHAR(255) NULL AFTER `mo_ta`');
        }

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'file_name')) {
            $this->statement('ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `file_name` VARCHAR(255) NULL AFTER `duong_dan_file`');
        }

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'file_extension')) {
            $this->statement('ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `file_extension` VARCHAR(50) NULL AFTER `file_name`');
        }

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'file_size')) {
            $this->statement('ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `file_size` BIGINT UNSIGNED NULL AFTER `file_extension`');
        }

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'mime_type')) {
            $this->statement('ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `mime_type` VARCHAR(255) NULL AFTER `file_size`');
        }

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'link_ngoai')) {
            $this->statement('ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `link_ngoai` VARCHAR(500) NULL AFTER `mime_type`');
        }

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'trang_thai_hien_thi')) {
            $this->statement("ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `trang_thai_hien_thi` VARCHAR(50) NOT NULL DEFAULT 'an' AFTER `link_ngoai`");
        }

        $this->addNullableDateTime('tai_nguyen_buoi_hoc', 'ngay_mo_hien_thi', 'trang_thai_hien_thi');

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'thu_tu_hien_thi')) {
            $this->statement('ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `thu_tu_hien_thi` INT NOT NULL DEFAULT 0 AFTER `ngay_mo_hien_thi`');
        }

        $this->addNullableBigInteger('tai_nguyen_buoi_hoc', 'nguoi_tao_id', 'thu_tu_hien_thi');

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'vai_tro_nguoi_tao')) {
            $this->statement('ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `vai_tro_nguoi_tao` VARCHAR(50) NULL AFTER `nguoi_tao_id`');
        }

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'trang_thai_duyet')) {
            $this->statement("ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `trang_thai_duyet` VARCHAR(50) NOT NULL DEFAULT 'da_duyet' AFTER `vai_tro_nguoi_tao`");
        }

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'trang_thai_xu_ly')) {
            $this->statement("ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `trang_thai_xu_ly` VARCHAR(50) NOT NULL DEFAULT 'khong_ap_dung' AFTER `trang_thai_duyet`");
        }

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'ghi_chu_admin')) {
            $this->statement('ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `ghi_chu_admin` TEXT NULL AFTER `trang_thai_xu_ly`');
        }

        $this->addNullableDateTime('tai_nguyen_buoi_hoc', 'ngay_gui_duyet', 'ghi_chu_admin');
        $this->addNullableDateTime('tai_nguyen_buoi_hoc', 'ngay_duyet', 'ngay_gui_duyet');
        $this->addNullableBigInteger('tai_nguyen_buoi_hoc', 'nguoi_duyet_id', 'ngay_duyet');

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'pham_vi_su_dung')) {
            $this->statement("ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `pham_vi_su_dung` VARCHAR(50) NOT NULL DEFAULT 'ca_nhan' AFTER `nguoi_duyet_id`");
        }

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'deleted_at')) {
            $this->statement('ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `deleted_at` TIMESTAMP NULL AFTER `pham_vi_su_dung`');
        }

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'created_at')) {
            $this->statement('ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `created_at` TIMESTAMP NULL AFTER `deleted_at`');
        }

        if (! Schema::hasColumn('tai_nguyen_buoi_hoc', 'updated_at')) {
            $this->statement('ALTER TABLE `tai_nguyen_buoi_hoc` ADD COLUMN `updated_at` TIMESTAMP NULL AFTER `created_at`');
        }
    }

    private function copyLegacyLearningResources(): void
    {
        if (! Schema::hasTable('tai_nguyen')) {
            return;
        }

        $sourceColumns = Schema::getColumnListing('tai_nguyen');
        $titleColumn = $this->sourceColumn($sourceColumns, 'tn', 'ten_tai_nguyen', "CONCAT('Tai nguyen ', tn.`id`)");
        $pathColumn = $this->sourceColumn($sourceColumns, 'tn', 'duong_dan');
        $typeColumn = $this->sourceColumn($sourceColumns, 'tn', 'loai_file');
        $sizeColumn = $this->sourceColumn($sourceColumns, 'tn', 'dung_luong');
        $scheduleColumn = $this->sourceColumn($sourceColumns, 'tn', 'lich_hoc_id');
        $creatorColumn = $this->sourceColumn($sourceColumns, 'tn', 'nguoi_dang_id');
        $createdAtColumn = $this->sourceColumn($sourceColumns, 'tn', 'created_at', 'NOW()');
        $updatedAtColumn = $this->sourceColumn($sourceColumns, 'tn', 'updated_at', 'NOW()');
        $visibilityColumn = in_array('hien_thi', $sourceColumns, true)
            ? "CASE WHEN tn.`hien_thi` = 1 THEN 'hien' ELSE 'an' END"
            : "'hien'";
        $scopeColumn = in_array('la_tai_nguyen_he_thong', $sourceColumns, true)
            ? "CASE WHEN tn.`la_tai_nguyen_he_thong` = 1 THEN 'cong_khai' ELSE 'ca_nhan' END"
            : "'ca_nhan'";

        $this->statement("INSERT INTO `tai_nguyen_buoi_hoc`
            (`id`, `lich_hoc_id`, `loai_tai_nguyen`, `tieu_de`, `duong_dan_file`, `file_name`, `file_extension`, `file_size`, `link_ngoai`, `trang_thai_hien_thi`, `nguoi_tao_id`, `trang_thai_duyet`, `trang_thai_xu_ly`, `ngay_duyet`, `pham_vi_su_dung`, `created_at`, `updated_at`)
            SELECT tn.`id`, {$scheduleColumn},
                COALESCE(NULLIF(LOWER({$typeColumn}), ''), 'tai_lieu_khac'),
                COALESCE(NULLIF({$titleColumn}, ''), CONCAT('Tai nguyen ', tn.`id`)),
                CASE WHEN {$pathColumn} LIKE 'http%' THEN NULL ELSE {$pathColumn} END,
                CASE WHEN {$pathColumn} LIKE 'http%' OR {$pathColumn} IS NULL THEN NULL ELSE SUBSTRING_INDEX({$pathColumn}, '/', -1) END,
                COALESCE(NULLIF(LOWER({$typeColumn}), ''), LOWER(SUBSTRING_INDEX({$pathColumn}, '.', -1))),
                {$sizeColumn},
                CASE WHEN {$pathColumn} LIKE 'http%' THEN {$pathColumn} ELSE NULL END,
                {$visibilityColumn},
                {$creatorColumn},
                'da_duyet',
                'khong_ap_dung',
                {$updatedAtColumn},
                {$scopeColumn},
                {$createdAtColumn},
                {$updatedAtColumn}
            FROM `tai_nguyen` tn
            WHERE NOT EXISTS (
                SELECT 1 FROM `tai_nguyen_buoi_hoc` target WHERE target.`id` = tn.`id`
            )");
    }

    private function repairLectures(): void
    {
        if (! Schema::hasTable('bai_giangs')) {
            $this->statement('CREATE TABLE `bai_giangs` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `khoa_hoc_id` BIGINT UNSIGNED NULL,
                `module_hoc_id` BIGINT UNSIGNED NULL,
                `lich_hoc_id` BIGINT UNSIGNED NULL,
                `nguoi_tao_id` BIGINT UNSIGNED NULL,
                `tieu_de` VARCHAR(255) NOT NULL,
                `mo_ta` TEXT NULL,
                `loai_bai_giang` VARCHAR(50) NOT NULL DEFAULT \'tai_lieu\',
                `tai_nguyen_chinh_id` BIGINT UNSIGNED NULL,
                `thu_tu_hien_thi` INT NOT NULL DEFAULT 0,
                `thoi_diem_mo` DATETIME NULL,
                `trang_thai_duyet` VARCHAR(50) NOT NULL DEFAULT \'da_duyet\',
                `trang_thai_cong_bo` VARCHAR(50) NOT NULL DEFAULT \'an\',
                `ghi_chu_admin` TEXT NULL,
                `ngay_gui_duyet` DATETIME NULL,
                `ngay_duyet` DATETIME NULL,
                `nguoi_duyet_id` BIGINT UNSIGNED NULL,
                `deleted_at` TIMESTAMP NULL,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_bai_giangs_module_order` (`module_hoc_id`, `thu_tu_hien_thi`),
                INDEX `idx_bai_giangs_approval_publish` (`trang_thai_duyet`, `trang_thai_cong_bo`)
            )');
        }

        $this->ensureLectureColumns();
        $this->copyLegacyLectures();
        $this->tryStatement('ALTER TABLE `bai_giangs` ADD INDEX `idx_bai_giangs_module_order` (`module_hoc_id`, `thu_tu_hien_thi`)');
        $this->tryStatement('ALTER TABLE `bai_giangs` ADD INDEX `idx_bai_giangs_approval_publish` (`trang_thai_duyet`, `trang_thai_cong_bo`)');
    }

    private function ensureLectureColumns(): void
    {
        $this->addNullableBigInteger('bai_giangs', 'khoa_hoc_id', 'id');
        $this->addNullableBigInteger('bai_giangs', 'module_hoc_id', 'khoa_hoc_id');
        $this->addNullableBigInteger('bai_giangs', 'lich_hoc_id', 'module_hoc_id');
        $this->addNullableBigInteger('bai_giangs', 'nguoi_tao_id', 'lich_hoc_id');

        if (! Schema::hasColumn('bai_giangs', 'tieu_de')) {
            $this->statement("ALTER TABLE `bai_giangs` ADD COLUMN `tieu_de` VARCHAR(255) NOT NULL DEFAULT '' AFTER `nguoi_tao_id`");
        }

        if (! Schema::hasColumn('bai_giangs', 'mo_ta')) {
            $this->statement('ALTER TABLE `bai_giangs` ADD COLUMN `mo_ta` TEXT NULL AFTER `tieu_de`');
        }

        if (! Schema::hasColumn('bai_giangs', 'loai_bai_giang')) {
            $this->statement("ALTER TABLE `bai_giangs` ADD COLUMN `loai_bai_giang` VARCHAR(50) NOT NULL DEFAULT 'tai_lieu' AFTER `mo_ta`");
        }

        $this->addNullableBigInteger('bai_giangs', 'tai_nguyen_chinh_id', 'loai_bai_giang');

        if (! Schema::hasColumn('bai_giangs', 'thu_tu_hien_thi')) {
            $this->statement('ALTER TABLE `bai_giangs` ADD COLUMN `thu_tu_hien_thi` INT NOT NULL DEFAULT 0 AFTER `tai_nguyen_chinh_id`');
        }

        $this->addNullableDateTime('bai_giangs', 'thoi_diem_mo', 'thu_tu_hien_thi');

        if (! Schema::hasColumn('bai_giangs', 'trang_thai_duyet')) {
            $this->statement("ALTER TABLE `bai_giangs` ADD COLUMN `trang_thai_duyet` VARCHAR(50) NOT NULL DEFAULT 'da_duyet' AFTER `thoi_diem_mo`");
        }

        if (! Schema::hasColumn('bai_giangs', 'trang_thai_cong_bo')) {
            $this->statement("ALTER TABLE `bai_giangs` ADD COLUMN `trang_thai_cong_bo` VARCHAR(50) NOT NULL DEFAULT 'an' AFTER `trang_thai_duyet`");
        }

        if (! Schema::hasColumn('bai_giangs', 'ghi_chu_admin')) {
            $this->statement('ALTER TABLE `bai_giangs` ADD COLUMN `ghi_chu_admin` TEXT NULL AFTER `trang_thai_cong_bo`');
        }

        $this->addNullableDateTime('bai_giangs', 'ngay_gui_duyet', 'ghi_chu_admin');
        $this->addNullableDateTime('bai_giangs', 'ngay_duyet', 'ngay_gui_duyet');
        $this->addNullableBigInteger('bai_giangs', 'nguoi_duyet_id', 'ngay_duyet');

        if (! Schema::hasColumn('bai_giangs', 'deleted_at')) {
            $this->statement('ALTER TABLE `bai_giangs` ADD COLUMN `deleted_at` TIMESTAMP NULL AFTER `nguoi_duyet_id`');
        }

        if (! Schema::hasColumn('bai_giangs', 'created_at')) {
            $this->statement('ALTER TABLE `bai_giangs` ADD COLUMN `created_at` TIMESTAMP NULL AFTER `deleted_at`');
        }

        if (! Schema::hasColumn('bai_giangs', 'updated_at')) {
            $this->statement('ALTER TABLE `bai_giangs` ADD COLUMN `updated_at` TIMESTAMP NULL AFTER `created_at`');
        }
    }

    private function copyLegacyLectures(): void
    {
        if (! Schema::hasTable('bai_giang')) {
            return;
        }

        $sourceColumns = Schema::getColumnListing('bai_giang');
        $hasSchedule = in_array('lich_hoc_id', $sourceColumns, true) && Schema::hasTable('lich_hoc');
        $hasClass = in_array('lop_hoc_id', $sourceColumns, true) && Schema::hasTable('lop_hoc') && Schema::hasColumn('lop_hoc', 'khoa_hoc_id');
        $scheduleJoin = $hasSchedule ? 'LEFT JOIN `lich_hoc` lh ON lh.`id` = bg.`lich_hoc_id`' : '';
        $classJoin = $hasClass ? 'LEFT JOIN `lop_hoc` lop ON lop.`id` = bg.`lop_hoc_id`' : '';
        $courseParts = [];

        if ($hasSchedule && Schema::hasColumn('lich_hoc', 'khoa_hoc_id')) {
            $courseParts[] = 'lh.`khoa_hoc_id`';
        }

        if ($hasClass) {
            $courseParts[] = 'lop.`khoa_hoc_id`';
        }

        $courseColumn = $courseParts === [] ? 'NULL' : 'COALESCE(' . implode(', ', $courseParts) . ')';
        $moduleColumn = $this->sourceColumn($sourceColumns, 'bg', 'module_hoc_id');
        $scheduleColumn = $this->sourceColumn($sourceColumns, 'bg', 'lich_hoc_id');
        $creatorColumn = $this->sourceColumn($sourceColumns, 'bg', 'nguoi_dang_id');
        $titleColumn = $this->sourceColumn($sourceColumns, 'bg', 'tieu_de', "CONCAT('Bai giang ', bg.`id`)");
        $contentColumn = $this->sourceColumn($sourceColumns, 'bg', 'noi_dung');
        $videoColumn = $this->sourceColumn($sourceColumns, 'bg', 'video_url');
        $createdAtColumn = $this->sourceColumn($sourceColumns, 'bg', 'created_at', 'NOW()');
        $updatedAtColumn = $this->sourceColumn($sourceColumns, 'bg', 'updated_at', 'NOW()');
        $statusColumn = in_array('trang_thai', $sourceColumns, true)
            ? "CASE WHEN bg.`trang_thai` IN ('nhap', 'cho_duyet', 'da_duyet', 'can_chinh_sua', 'tu_choi') THEN bg.`trang_thai` ELSE 'da_duyet' END"
            : "'da_duyet'";
        $publishColumn = in_array('trang_thai', $sourceColumns, true)
            ? "CASE WHEN bg.`trang_thai` IN ('da_duyet', 'cong_bo', 'hien', 'published') THEN 'da_cong_bo' ELSE 'an' END"
            : "'da_cong_bo'";

        $this->statement("INSERT INTO `bai_giangs`
            (`id`, `khoa_hoc_id`, `module_hoc_id`, `lich_hoc_id`, `nguoi_tao_id`, `tieu_de`, `mo_ta`, `loai_bai_giang`, `thu_tu_hien_thi`, `trang_thai_duyet`, `trang_thai_cong_bo`, `ngay_duyet`, `created_at`, `updated_at`)
            SELECT bg.`id`, {$courseColumn}, {$moduleColumn}, {$scheduleColumn}, {$creatorColumn},
                COALESCE(NULLIF({$titleColumn}, ''), CONCAT('Bai giang ', bg.`id`)),
                {$contentColumn},
                CASE WHEN {$videoColumn} IS NOT NULL AND {$videoColumn} <> '' THEN 'video' ELSE 'tai_lieu' END,
                bg.`id`,
                {$statusColumn},
                {$publishColumn},
                CASE WHEN ({$statusColumn}) = 'da_duyet' THEN {$updatedAtColumn} ELSE NULL END,
                {$createdAtColumn},
                {$updatedAtColumn}
            FROM `bai_giang` bg
            {$scheduleJoin}
            {$classJoin}
            WHERE NOT EXISTS (
                SELECT 1 FROM `bai_giangs` target WHERE target.`id` = bg.`id`
            )");
    }

    private function repairLectureResourcePivot(): void
    {
        if (! Schema::hasTable('bai_giang_tai_nguyen')) {
            $this->statement('CREATE TABLE `bai_giang_tai_nguyen` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `bai_giang_id` BIGINT UNSIGNED NOT NULL,
                `tai_nguyen_id` BIGINT UNSIGNED NOT NULL,
                `vai_tro_tai_nguyen` VARCHAR(50) NOT NULL DEFAULT \'phu\',
                `thu_tu_hien_thi` INT NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `bai_giang_tai_nguyen_unique` (`bai_giang_id`, `tai_nguyen_id`)
            )');
        }

        if (Schema::hasTable('tai_nguyen') && Schema::hasColumn('tai_nguyen', 'bai_giang_id')) {
            $this->statement("INSERT INTO `bai_giang_tai_nguyen`
                (`bai_giang_id`, `tai_nguyen_id`, `vai_tro_tai_nguyen`, `thu_tu_hien_thi`, `created_at`, `updated_at`)
                SELECT tn.`bai_giang_id`, tn.`id`, 'phu', tn.`id`, tn.`created_at`, tn.`updated_at`
                FROM `tai_nguyen` tn
                WHERE tn.`bai_giang_id` IS NOT NULL
                    AND EXISTS (SELECT 1 FROM `bai_giangs` bg WHERE bg.`id` = tn.`bai_giang_id`)
                    AND EXISTS (SELECT 1 FROM `tai_nguyen_buoi_hoc` target_resource WHERE target_resource.`id` = tn.`id`)
                    AND NOT EXISTS (
                        SELECT 1 FROM `bai_giang_tai_nguyen` target
                        WHERE target.`bai_giang_id` = tn.`bai_giang_id` AND target.`tai_nguyen_id` = tn.`id`
                    )");

            $this->statement('UPDATE `bai_giangs` bg SET `tai_nguyen_chinh_id` = (
                SELECT MIN(tn.`id`) FROM `tai_nguyen` tn WHERE tn.`bai_giang_id` = bg.`id`
            ) WHERE bg.`tai_nguyen_chinh_id` IS NULL');
        }

        $this->tryStatement('ALTER TABLE `bai_giang_tai_nguyen` ADD UNIQUE INDEX `bai_giang_tai_nguyen_unique` (`bai_giang_id`, `tai_nguyen_id`)');
    }

    private function repairHocVien(): void
    {
        if (! Schema::hasTable('hoc_vien')) {
            return;
        }

        if (! Schema::hasColumn('hoc_vien', 'lop')) {
            $this->statement('ALTER TABLE `hoc_vien` ADD COLUMN `lop` VARCHAR(255) NULL AFTER `ma_hoc_vien`');
            if (Schema::hasColumn('hoc_vien', 'lop_niem_khoa')) {
                $this->statement('UPDATE `hoc_vien` SET `lop` = `lop_niem_khoa` WHERE `lop` IS NULL');
            }
        }

        if (! Schema::hasColumn('hoc_vien', 'nganh')) {
            $this->statement('ALTER TABLE `hoc_vien` ADD COLUMN `nganh` VARCHAR(255) NULL AFTER `lop`');
            if (Schema::hasColumn('hoc_vien', 'nganh_hoc')) {
                $this->statement('UPDATE `hoc_vien` SET `nganh` = `nganh_hoc` WHERE `nganh` IS NULL');
            }
        }
    }

    private function repairTeacherAssignments(): void
    {
        if (! Schema::hasTable('phan_cong_module_giang_vien')) {
            $this->statement('CREATE TABLE `phan_cong_module_giang_vien` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `khoa_hoc_id` BIGINT UNSIGNED NULL,
                `module_hoc_id` BIGINT UNSIGNED NOT NULL,
                `giang_vien_id` BIGINT UNSIGNED NOT NULL,
                `ngay_phan_cong` DATETIME NULL,
                `trang_thai` VARCHAR(50) NOT NULL DEFAULT \'cho_xac_nhan\',
                `ghi_chu` TEXT NULL,
                `created_by` BIGINT UNSIGNED NULL,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_pcmgv_khoa_hoc` (`khoa_hoc_id`),
                INDEX `idx_pcmgv_module` (`module_hoc_id`),
                INDEX `idx_pcmgv_giang_vien` (`giang_vien_id`)
            )');
        }

        if (! Schema::hasColumn('phan_cong_module_giang_vien', 'khoa_hoc_id')) {
            $this->statement('ALTER TABLE `phan_cong_module_giang_vien` ADD COLUMN `khoa_hoc_id` BIGINT UNSIGNED NULL AFTER `id`');
        }

        if (Schema::hasTable('phan_cong_giang_vien')) {
            $sourceColumns = Schema::getColumnListing('phan_cong_giang_vien');
            $createdByColumn = in_array('created_by', $sourceColumns, true) ? '`pc`.`created_by`' : 'NULL';
            $ngayPhanCongColumn = in_array('ngay_phan_cong', $sourceColumns, true) ? '`pc`.`ngay_phan_cong`' : 'NOW()';
            $ghiChuColumn = in_array('ghi_chu', $sourceColumns, true) ? '`pc`.`ghi_chu`' : 'NULL';
            $createdAtColumn = in_array('created_at', $sourceColumns, true) ? '`pc`.`created_at`' : 'NOW()';
            $updatedAtColumn = in_array('updated_at', $sourceColumns, true) ? '`pc`.`updated_at`' : 'NOW()';

            if (Schema::hasColumn('phan_cong_giang_vien', 'lop_hoc_id') && Schema::hasTable('lop_hoc')) {
                $this->statement("INSERT INTO `phan_cong_module_giang_vien`
                    (`id`, `khoa_hoc_id`, `module_hoc_id`, `giang_vien_id`, `ngay_phan_cong`, `trang_thai`, `ghi_chu`, `created_by`, `created_at`, `updated_at`)
                    SELECT `pc`.`id`, `lh`.`khoa_hoc_id`, `pc`.`module_hoc_id`, `pc`.`giang_vien_id`, {$ngayPhanCongColumn}, `pc`.`trang_thai`, {$ghiChuColumn}, {$createdByColumn}, {$createdAtColumn}, {$updatedAtColumn}
                    FROM `phan_cong_giang_vien` pc
                    LEFT JOIN `lop_hoc` lh ON lh.`id` = pc.`lop_hoc_id`
                    WHERE NOT EXISTS (
                        SELECT 1 FROM `phan_cong_module_giang_vien` target WHERE target.`id` = pc.`id`
                    )");
            } elseif (Schema::hasColumn('phan_cong_giang_vien', 'khoa_hoc_id')) {
                $this->statement("INSERT INTO `phan_cong_module_giang_vien`
                    (`id`, `khoa_hoc_id`, `module_hoc_id`, `giang_vien_id`, `ngay_phan_cong`, `trang_thai`, `ghi_chu`, `created_by`, `created_at`, `updated_at`)
                    SELECT `pc`.`id`, `pc`.`khoa_hoc_id`, `pc`.`module_hoc_id`, `pc`.`giang_vien_id`, {$ngayPhanCongColumn}, `pc`.`trang_thai`, {$ghiChuColumn}, {$createdByColumn}, {$createdAtColumn}, {$updatedAtColumn}
                    FROM `phan_cong_giang_vien` pc
                    WHERE NOT EXISTS (
                        SELECT 1 FROM `phan_cong_module_giang_vien` target WHERE target.`id` = pc.`id`
                    )");
            }
        }

        $this->tryStatement('ALTER TABLE `phan_cong_module_giang_vien` ADD UNIQUE INDEX `uq_module_giang_vien` (`module_hoc_id`, `giang_vien_id`)');
    }

    private function repairTeacherLeaveRequests(): void
    {
        if (! Schema::hasTable('giang_vien_don_xin_nghi')) {
            $this->statement('CREATE TABLE `giang_vien_don_xin_nghi` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `giang_vien_id` BIGINT UNSIGNED NOT NULL,
                `khoa_hoc_id` BIGINT UNSIGNED NULL,
                `module_hoc_id` BIGINT UNSIGNED NULL,
                `lich_hoc_id` BIGINT UNSIGNED NULL,
                `ngay_xin_nghi` DATE NOT NULL,
                `buoi_hoc` VARCHAR(20) NULL,
                `tiet_bat_dau` TINYINT NULL,
                `tiet_ket_thuc` TINYINT NULL,
                `ly_do` TEXT NOT NULL,
                `ghi_chu_phan_hoi` TEXT NULL,
                `trang_thai` VARCHAR(50) NOT NULL DEFAULT \'cho_duyet\',
                `nguoi_duyet_id` BIGINT UNSIGNED NULL,
                `ngay_duyet` TIMESTAMP NULL,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_gv_don_xin_nghi_ngay` (`giang_vien_id`, `ngay_xin_nghi`),
                INDEX `idx_gv_don_xin_nghi_trang_thai` (`trang_thai`, `created_at`)
            )');
        }

        $this->ensureTeacherLeaveRequestColumns();

        if (Schema::hasTable('don_xin_nghi_giang_vien')) {
            $sourceColumns = Schema::getColumnListing('don_xin_nghi_giang_vien');
            $hasLichHocId = in_array('lich_hoc_id', $sourceColumns, true);
            $hasLopHocId = in_array('lop_hoc_id', $sourceColumns, true);

            $joinLichHoc = '';
            $joinLopHoc = '';
            $courseParts = [];
            $dateParts = [];

            if ($hasLichHocId && Schema::hasTable('lich_hoc')) {
                $joinLichHoc = 'LEFT JOIN `lich_hoc` lh ON lh.`id` = dx.`lich_hoc_id`';

                if (Schema::hasColumn('lich_hoc', 'khoa_hoc_id')) {
                    $courseParts[] = 'lh.`khoa_hoc_id`';
                }

                if (Schema::hasColumn('lich_hoc', 'ngay_hoc')) {
                    $dateParts[] = 'lh.`ngay_hoc`';
                }
            }

            if ($hasLopHocId && Schema::hasTable('lop_hoc') && Schema::hasColumn('lop_hoc', 'khoa_hoc_id')) {
                $joinLopHoc = 'LEFT JOIN `lop_hoc` lop ON lop.`id` = dx.`lop_hoc_id`';
                $courseParts[] = 'lop.`khoa_hoc_id`';
            }

            if (in_array('created_at', $sourceColumns, true)) {
                $dateParts[] = 'DATE(dx.`created_at`)';
            }

            $khoaHocColumn = $courseParts === [] ? 'NULL' : 'COALESCE(' . implode(', ', $courseParts) . ')';
            $ngayXinNghiColumn = $dateParts === [] ? 'CURRENT_DATE' : 'COALESCE(' . implode(', ', [...$dateParts, 'CURRENT_DATE']) . ')';
            $moduleHocColumn = $joinLichHoc !== '' && Schema::hasColumn('lich_hoc', 'module_hoc_id') ? 'lh.`module_hoc_id`' : 'NULL';
            $lichHocColumn = $hasLichHocId ? 'dx.`lich_hoc_id`' : 'NULL';
            $buoiHocColumn = $joinLichHoc !== '' && Schema::hasColumn('lich_hoc', 'buoi_hoc') ? 'lh.`buoi_hoc`' : 'NULL';
            $tietBatDauColumn = $joinLichHoc !== '' && Schema::hasColumn('lich_hoc', 'tiet_bat_dau') ? 'lh.`tiet_bat_dau`' : 'NULL';
            $tietKetThucColumn = $joinLichHoc !== '' && Schema::hasColumn('lich_hoc', 'tiet_ket_thuc') ? 'lh.`tiet_ket_thuc`' : 'NULL';
            $lyDoColumn = in_array('ly_do', $sourceColumns, true) ? "COALESCE(dx.`ly_do`, '')" : "''";
            $feedbackColumn = in_array('phan_hoi_admin', $sourceColumns, true) ? 'dx.`phan_hoi_admin`' : 'NULL';
            $reviewerColumn = in_array('nguoi_duyet_id', $sourceColumns, true) ? 'dx.`nguoi_duyet_id`' : 'NULL';
            $createdAtColumn = in_array('created_at', $sourceColumns, true) ? 'dx.`created_at`' : 'NOW()';
            $updatedAtColumn = in_array('updated_at', $sourceColumns, true) ? 'dx.`updated_at`' : 'NOW()';
            $statusColumn = in_array('trang_thai', $sourceColumns, true)
                ? "CASE WHEN dx.`trang_thai` IN ('cho_duyet', 'da_duyet', 'tu_choi') THEN dx.`trang_thai` ELSE 'cho_duyet' END"
                : "'cho_duyet'";

            $this->statement("INSERT INTO `giang_vien_don_xin_nghi`
                (`id`, `giang_vien_id`, `khoa_hoc_id`, `module_hoc_id`, `lich_hoc_id`, `ngay_xin_nghi`, `buoi_hoc`, `tiet_bat_dau`, `tiet_ket_thuc`, `ly_do`, `ghi_chu_phan_hoi`, `trang_thai`, `nguoi_duyet_id`, `ngay_duyet`, `created_at`, `updated_at`)
                SELECT dx.`id`, dx.`giang_vien_id`, {$khoaHocColumn}, {$moduleHocColumn}, {$lichHocColumn}, {$ngayXinNghiColumn}, {$buoiHocColumn}, {$tietBatDauColumn}, {$tietKetThucColumn}, {$lyDoColumn}, {$feedbackColumn}, {$statusColumn}, {$reviewerColumn},
                    CASE WHEN ({$statusColumn}) IN ('da_duyet', 'tu_choi') THEN {$updatedAtColumn} ELSE NULL END,
                    {$createdAtColumn}, {$updatedAtColumn}
                FROM `don_xin_nghi_giang_vien` dx
                {$joinLichHoc}
                {$joinLopHoc}
                WHERE NOT EXISTS (
                    SELECT 1 FROM `giang_vien_don_xin_nghi` target WHERE target.`id` = dx.`id`
                )");
        }

        $this->tryStatement('ALTER TABLE `giang_vien_don_xin_nghi` ADD INDEX `idx_gv_don_xin_nghi_ngay` (`giang_vien_id`, `ngay_xin_nghi`)');
        $this->tryStatement('ALTER TABLE `giang_vien_don_xin_nghi` ADD INDEX `idx_gv_don_xin_nghi_trang_thai` (`trang_thai`, `created_at`)');
    }

    private function ensureTeacherLeaveRequestColumns(): void
    {
        $this->addNullableBigInteger('giang_vien_don_xin_nghi', 'khoa_hoc_id', 'giang_vien_id');
        $this->addNullableBigInteger('giang_vien_don_xin_nghi', 'module_hoc_id', 'khoa_hoc_id');
        $this->addNullableBigInteger('giang_vien_don_xin_nghi', 'lich_hoc_id', 'module_hoc_id');
        $this->addNullableDate('giang_vien_don_xin_nghi', 'ngay_xin_nghi', 'lich_hoc_id');

        if (! Schema::hasColumn('giang_vien_don_xin_nghi', 'buoi_hoc')) {
            $this->statement('ALTER TABLE `giang_vien_don_xin_nghi` ADD COLUMN `buoi_hoc` VARCHAR(20) NULL AFTER `ngay_xin_nghi`');
        }

        if (! Schema::hasColumn('giang_vien_don_xin_nghi', 'tiet_bat_dau')) {
            $this->statement('ALTER TABLE `giang_vien_don_xin_nghi` ADD COLUMN `tiet_bat_dau` TINYINT NULL AFTER `buoi_hoc`');
        }

        if (! Schema::hasColumn('giang_vien_don_xin_nghi', 'tiet_ket_thuc')) {
            $this->statement('ALTER TABLE `giang_vien_don_xin_nghi` ADD COLUMN `tiet_ket_thuc` TINYINT NULL AFTER `tiet_bat_dau`');
        }

        if (! Schema::hasColumn('giang_vien_don_xin_nghi', 'ly_do')) {
            $this->statement('ALTER TABLE `giang_vien_don_xin_nghi` ADD COLUMN `ly_do` TEXT NULL AFTER `tiet_ket_thuc`');
        }

        if (! Schema::hasColumn('giang_vien_don_xin_nghi', 'ghi_chu_phan_hoi')) {
            $this->statement('ALTER TABLE `giang_vien_don_xin_nghi` ADD COLUMN `ghi_chu_phan_hoi` TEXT NULL AFTER `ly_do`');
        }

        if (! Schema::hasColumn('giang_vien_don_xin_nghi', 'trang_thai')) {
            $this->statement("ALTER TABLE `giang_vien_don_xin_nghi` ADD COLUMN `trang_thai` VARCHAR(50) NOT NULL DEFAULT 'cho_duyet' AFTER `ghi_chu_phan_hoi`");
        }

        $this->addNullableBigInteger('giang_vien_don_xin_nghi', 'nguoi_duyet_id', 'trang_thai');

        if (! Schema::hasColumn('giang_vien_don_xin_nghi', 'ngay_duyet')) {
            $this->statement('ALTER TABLE `giang_vien_don_xin_nghi` ADD COLUMN `ngay_duyet` TIMESTAMP NULL AFTER `nguoi_duyet_id`');
        }

        if (! Schema::hasColumn('giang_vien_don_xin_nghi', 'created_at')) {
            $this->statement('ALTER TABLE `giang_vien_don_xin_nghi` ADD COLUMN `created_at` TIMESTAMP NULL AFTER `ngay_duyet`');
        }

        if (! Schema::hasColumn('giang_vien_don_xin_nghi', 'updated_at')) {
            $this->statement('ALTER TABLE `giang_vien_don_xin_nghi` ADD COLUMN `updated_at` TIMESTAMP NULL AFTER `created_at`');
        }
    }


    private function repairCourseCatalog(): void
    {
        if (Schema::hasTable('nhom_nganh') && ! Schema::hasColumn('nhom_nganh', 'ma_nhom_nganh')) {
            $this->statement('ALTER TABLE `nhom_nganh` ADD COLUMN `ma_nhom_nganh` VARCHAR(50) NULL AFTER `id`');
            $this->statement("UPDATE `nhom_nganh` SET `ma_nhom_nganh` = CONCAT('NN', LPAD(`id`, 3, '0')) WHERE `ma_nhom_nganh` IS NULL");
            $this->tryStatement('ALTER TABLE `nhom_nganh` ADD UNIQUE INDEX `nhom_nganh_ma_nhom_nganh_unique` (`ma_nhom_nganh`)');
        }

        if (Schema::hasTable('khoa_hoc')) {
            if (! Schema::hasColumn('khoa_hoc', 'tong_so_module')) {
                $this->statement('ALTER TABLE `khoa_hoc` ADD COLUMN `tong_so_module` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `cap_do`');
                if (Schema::hasTable('module_hoc') && Schema::hasColumn('module_hoc', 'khoa_hoc_id')) {
                    $this->statement('UPDATE `khoa_hoc` kh SET `tong_so_module` = (SELECT COUNT(*) FROM `module_hoc` mh WHERE mh.`khoa_hoc_id` = kh.`id`)');
                }
            }

            $this->addDecimalIfMissing('khoa_hoc', 'ty_trong_diem_danh', 'phuong_thuc_danh_gia', '20.00');
            $this->addDecimalIfMissing('khoa_hoc', 'ty_trong_kiem_tra', 'ty_trong_diem_danh', '80.00');

            if (! Schema::hasColumn('khoa_hoc', 'loai')) {
                $this->statement("ALTER TABLE `khoa_hoc` ADD COLUMN `loai` VARCHAR(20) NOT NULL DEFAULT 'hoat_dong' AFTER `trang_thai`");
            }

            if (! Schema::hasColumn('khoa_hoc', 'trang_thai_van_hanh')) {
                $this->statement("ALTER TABLE `khoa_hoc` ADD COLUMN `trang_thai_van_hanh` VARCHAR(50) NOT NULL DEFAULT 'dang_day' AFTER `loai`");
            }

            $this->addNullableBigInteger('khoa_hoc', 'khoa_hoc_mau_id', 'trang_thai_van_hanh');

            if (! Schema::hasColumn('khoa_hoc', 'lan_mo_thu')) {
                $this->statement('ALTER TABLE `khoa_hoc` ADD COLUMN `lan_mo_thu` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `khoa_hoc_mau_id`');
            }

            $this->addNullableDate('khoa_hoc', 'ngay_khai_giang', 'lan_mo_thu');
            $this->addNullableDate('khoa_hoc', 'ngay_mo_lop', 'ngay_khai_giang');
            $this->addNullableDate('khoa_hoc', 'ngay_ket_thuc', 'ngay_mo_lop');

            if (! Schema::hasColumn('khoa_hoc', 'ghi_chu_noi_bo')) {
                $this->statement('ALTER TABLE `khoa_hoc` ADD COLUMN `ghi_chu_noi_bo` TEXT NULL AFTER `ngay_ket_thuc`');
            }

            if (Schema::hasTable('lop_hoc') && Schema::hasColumn('lop_hoc', 'khoa_hoc_id')) {
                if (Schema::hasColumn('lop_hoc', 'ngay_khai_giang')) {
                    $this->statement('UPDATE `khoa_hoc` kh JOIN (SELECT `khoa_hoc_id`, MIN(`ngay_khai_giang`) AS `ngay_khai_giang` FROM `lop_hoc` GROUP BY `khoa_hoc_id`) lh ON lh.`khoa_hoc_id` = kh.`id` SET kh.`ngay_khai_giang` = lh.`ngay_khai_giang` WHERE kh.`ngay_khai_giang` IS NULL');
                }

                if (Schema::hasColumn('lop_hoc', 'ngay_ket_thuc')) {
                    $this->statement('UPDATE `khoa_hoc` kh JOIN (SELECT `khoa_hoc_id`, MAX(`ngay_ket_thuc`) AS `ngay_ket_thuc` FROM `lop_hoc` GROUP BY `khoa_hoc_id`) lh ON lh.`khoa_hoc_id` = kh.`id` SET kh.`ngay_ket_thuc` = lh.`ngay_ket_thuc` WHERE kh.`ngay_ket_thuc` IS NULL');
                }
            }
        }

        if (Schema::hasTable('module_hoc')) {
            if (! Schema::hasColumn('module_hoc', 'ma_module')) {
                $this->statement('ALTER TABLE `module_hoc` ADD COLUMN `ma_module` VARCHAR(50) NULL AFTER `khoa_hoc_id`');
                $this->statement("UPDATE `module_hoc` SET `ma_module` = CONCAT('MOD-', `id`) WHERE `ma_module` IS NULL");
            }

            if (! Schema::hasColumn('module_hoc', 'thu_tu_module')) {
                $this->statement('ALTER TABLE `module_hoc` ADD COLUMN `thu_tu_module` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `ten_module`');
                $this->statement('UPDATE `module_hoc` SET `thu_tu_module` = `id` WHERE `thu_tu_module` = 1');
            }

            if (! Schema::hasColumn('module_hoc', 'thoi_luong_du_kien')) {
                $this->statement('ALTER TABLE `module_hoc` ADD COLUMN `thoi_luong_du_kien` INT UNSIGNED NULL AFTER `so_buoi`');
                $this->statement('UPDATE `module_hoc` SET `thoi_luong_du_kien` = 90 WHERE `thoi_luong_du_kien` IS NULL');
            }

            if (! Schema::hasColumn('module_hoc', 'trang_thai')) {
                $this->statement('ALTER TABLE `module_hoc` ADD COLUMN `trang_thai` TINYINT(1) NOT NULL DEFAULT 1 AFTER `mo_ta`');
            }
        }

        if (Schema::hasTable('lich_hoc') && ! Schema::hasColumn('lich_hoc', 'khoa_hoc_id')) {
            $this->statement('ALTER TABLE `lich_hoc` ADD COLUMN `khoa_hoc_id` BIGINT UNSIGNED NULL AFTER `id`');
            if (Schema::hasColumn('lich_hoc', 'lop_hoc_id') && Schema::hasTable('lop_hoc') && Schema::hasColumn('lop_hoc', 'khoa_hoc_id')) {
                $this->statement('UPDATE `lich_hoc` lh JOIN `lop_hoc` l ON l.`id` = lh.`lop_hoc_id` SET lh.`khoa_hoc_id` = l.`khoa_hoc_id` WHERE lh.`khoa_hoc_id` IS NULL');
            } elseif (Schema::hasColumn('lich_hoc', 'lop_hoc_id')) {
                $this->statement('UPDATE `lich_hoc` SET `khoa_hoc_id` = `lop_hoc_id` WHERE `khoa_hoc_id` IS NULL');
            }
        }
    }

    private function repairAssessmentColumns(): void
    {
        if (! Schema::hasTable('bai_kiem_tra')) {
            return;
        }

        if (! Schema::hasColumn('bai_kiem_tra', 'khoa_hoc_id')) {
            $this->statement('ALTER TABLE `bai_kiem_tra` ADD COLUMN `khoa_hoc_id` BIGINT UNSIGNED NULL AFTER `id`');

            if (Schema::hasColumn('bai_kiem_tra', 'lop_hoc_id') && Schema::hasTable('lop_hoc') && Schema::hasColumn('lop_hoc', 'khoa_hoc_id')) {
                $this->statement('UPDATE `bai_kiem_tra` bkt JOIN `lop_hoc` lh ON lh.`id` = bkt.`lop_hoc_id` SET bkt.`khoa_hoc_id` = lh.`khoa_hoc_id` WHERE bkt.`khoa_hoc_id` IS NULL');
            }
        }

        if (! Schema::hasColumn('bai_kiem_tra', 'thoi_gian_lam_bai')) {
            $this->statement('ALTER TABLE `bai_kiem_tra` ADD COLUMN `thoi_gian_lam_bai` INT UNSIGNED NOT NULL DEFAULT 60 AFTER `mo_ta`');
            if (Schema::hasColumn('bai_kiem_tra', 'thoi_gian_lam_phut')) {
                $this->statement('UPDATE `bai_kiem_tra` SET `thoi_gian_lam_bai` = `thoi_gian_lam_phut` WHERE `thoi_gian_lam_bai` = 60 AND `thoi_gian_lam_phut` IS NOT NULL');
            }
        }

        if (! Schema::hasColumn('bai_kiem_tra', 'ngay_mo')) {
            $this->statement('ALTER TABLE `bai_kiem_tra` ADD COLUMN `ngay_mo` DATETIME NULL AFTER `thoi_gian_lam_bai`');
            if (Schema::hasColumn('bai_kiem_tra', 'thoi_gian_bat_dau')) {
                $this->statement('UPDATE `bai_kiem_tra` SET `ngay_mo` = `thoi_gian_bat_dau` WHERE `ngay_mo` IS NULL');
            }
        }

        if (! Schema::hasColumn('bai_kiem_tra', 'ngay_dong')) {
            $this->statement('ALTER TABLE `bai_kiem_tra` ADD COLUMN `ngay_dong` DATETIME NULL AFTER `ngay_mo`');
            if (Schema::hasColumn('bai_kiem_tra', 'thoi_gian_ket_thuc')) {
                $this->statement('UPDATE `bai_kiem_tra` SET `ngay_dong` = `thoi_gian_ket_thuc` WHERE `ngay_dong` IS NULL');
            }
        }

        if (! Schema::hasColumn('bai_kiem_tra', 'pham_vi')) {
            $this->statement("ALTER TABLE `bai_kiem_tra` ADD COLUMN `pham_vi` VARCHAR(50) NOT NULL DEFAULT 'module' AFTER `ngay_dong`");
        }

        if (! Schema::hasColumn('bai_kiem_tra', 'loai_bai_kiem_tra')) {
            $this->statement("ALTER TABLE `bai_kiem_tra` ADD COLUMN `loai_bai_kiem_tra` VARCHAR(50) NOT NULL DEFAULT 'module' AFTER `pham_vi`");
        }

        if (! Schema::hasColumn('bai_kiem_tra', 'loai_noi_dung')) {
            $this->statement("ALTER TABLE `bai_kiem_tra` ADD COLUMN `loai_noi_dung` VARCHAR(50) NOT NULL DEFAULT 'tu_luan' AFTER `loai_bai_kiem_tra`");
        }

        if (! Schema::hasColumn('bai_kiem_tra', 'trang_thai_duyet')) {
            $this->statement("ALTER TABLE `bai_kiem_tra` ADD COLUMN `trang_thai_duyet` VARCHAR(50) NOT NULL DEFAULT 'da_duyet' AFTER `loai_noi_dung`");
        }

        if (! Schema::hasColumn('bai_kiem_tra', 'trang_thai_phat_hanh')) {
            $this->statement("ALTER TABLE `bai_kiem_tra` ADD COLUMN `trang_thai_phat_hanh` VARCHAR(50) NOT NULL DEFAULT 'phat_hanh' AFTER `trang_thai_duyet`");
        }

        if (! Schema::hasColumn('bai_kiem_tra', 'tong_diem')) {
            $this->statement('ALTER TABLE `bai_kiem_tra` ADD COLUMN `tong_diem` DECIMAL(8,2) NOT NULL DEFAULT 10.00 AFTER `trang_thai_phat_hanh`');
            if (Schema::hasColumn('bai_kiem_tra', 'diem_dat')) {
                $this->statement('UPDATE `bai_kiem_tra` SET `tong_diem` = GREATEST(`diem_dat`, 10) WHERE `tong_diem` = 10.00 AND `diem_dat` IS NOT NULL');
            }
        }

        if (! Schema::hasColumn('bai_kiem_tra', 'che_do_tinh_diem')) {
            $this->statement("ALTER TABLE `bai_kiem_tra` ADD COLUMN `che_do_tinh_diem` VARCHAR(50) NOT NULL DEFAULT 'thu_cong' AFTER `tong_diem`");
        }

        if (! Schema::hasColumn('bai_kiem_tra', 'so_cau_goi_diem')) {
            $this->statement('ALTER TABLE `bai_kiem_tra` ADD COLUMN `so_cau_goi_diem` INT UNSIGNED NULL AFTER `che_do_tinh_diem`');
        }

        if (! Schema::hasColumn('bai_kiem_tra', 'so_lan_duoc_lam')) {
            $this->statement('ALTER TABLE `bai_kiem_tra` ADD COLUMN `so_lan_duoc_lam` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `so_cau_goi_diem`');
            if (Schema::hasColumn('bai_kiem_tra', 'so_lan_lam_toi_da')) {
                $this->statement('UPDATE `bai_kiem_tra` SET `so_lan_duoc_lam` = `so_lan_lam_toi_da` WHERE `so_lan_duoc_lam` = 1 AND `so_lan_lam_toi_da` IS NOT NULL');
            }
        }

        $this->addBooleanColumn('bai_kiem_tra', 'randomize_questions', 'so_lan_duoc_lam', 'false');
        if (Schema::hasColumn('bai_kiem_tra', 'tron_cau_hoi')) {
            $this->statement('UPDATE `bai_kiem_tra` SET `randomize_questions` = `tron_cau_hoi` WHERE `randomize_questions` = 0');
        }

        $this->addBooleanColumn('bai_kiem_tra', 'randomize_answers', 'randomize_questions', 'false');
        if (Schema::hasColumn('bai_kiem_tra', 'tron_dap_an')) {
            $this->statement('UPDATE `bai_kiem_tra` SET `randomize_answers` = `tron_dap_an` WHERE `randomize_answers` = 0');
        }

        $this->addBooleanColumn('bai_kiem_tra', 'co_giam_sat', 'randomize_answers', 'false');
        $this->addBooleanColumn('bai_kiem_tra', 'bat_buoc_fullscreen', 'co_giam_sat', 'false');
        $this->addBooleanColumn('bai_kiem_tra', 'bat_buoc_camera', 'bat_buoc_fullscreen', 'false');

        if (! Schema::hasColumn('bai_kiem_tra', 'so_lan_vi_pham_toi_da')) {
            $this->statement('ALTER TABLE `bai_kiem_tra` ADD COLUMN `so_lan_vi_pham_toi_da` INT UNSIGNED NOT NULL DEFAULT 3 AFTER `bat_buoc_camera`');
        }

        if (! Schema::hasColumn('bai_kiem_tra', 'chu_ky_snapshot_giay')) {
            $this->statement('ALTER TABLE `bai_kiem_tra` ADD COLUMN `chu_ky_snapshot_giay` INT UNSIGNED NOT NULL DEFAULT 30 AFTER `so_lan_vi_pham_toi_da`');
        }

        $this->addBooleanColumn('bai_kiem_tra', 'tu_dong_nop_khi_vi_pham', 'chu_ky_snapshot_giay', 'false');
        $this->addBooleanColumn('bai_kiem_tra', 'chan_copy_paste', 'tu_dong_nop_khi_vi_pham', 'false');
        $this->addBooleanColumn('bai_kiem_tra', 'chan_chuot_phai', 'chan_copy_paste', 'false');
        $this->addNullableBigInteger('bai_kiem_tra', 'nguoi_duyet_id', 'nguoi_tao_id');
        $this->addNullableDateTime('bai_kiem_tra', 'de_xuat_duyet_luc', 'nguoi_duyet_id');
        $this->addNullableDateTime('bai_kiem_tra', 'duyet_luc', 'de_xuat_duyet_luc');
        $this->addNullableDateTime('bai_kiem_tra', 'phat_hanh_luc', 'duyet_luc');

        if (! Schema::hasColumn('bai_kiem_tra', 'ghi_chu_duyet')) {
            $this->statement('ALTER TABLE `bai_kiem_tra` ADD COLUMN `ghi_chu_duyet` TEXT NULL AFTER `phat_hanh_luc`');
        }

        $this->tryStatement('ALTER TABLE `bai_kiem_tra` ADD INDEX `idx_bai_kiem_tra_course_approval_publish` (`khoa_hoc_id`, `trang_thai_duyet`, `trang_thai_phat_hanh`)');
    }

    private function repairQuestionBank(): void
    {
        if (! Schema::hasTable('ngan_hang_cau_hoi')) {
            $this->statement('CREATE TABLE `ngan_hang_cau_hoi` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `khoa_hoc_id` BIGINT UNSIGNED NULL,
                `module_hoc_id` BIGINT UNSIGNED NULL,
                `nguoi_tao_id` BIGINT UNSIGNED NULL,
                `ma_cau_hoi` VARCHAR(60) NOT NULL,
                `noi_dung` LONGTEXT NOT NULL,
                `loai_cau_hoi` VARCHAR(50) NOT NULL DEFAULT \'trac_nghiem\',
                `kieu_dap_an` VARCHAR(50) NULL,
                `muc_do` VARCHAR(50) NOT NULL DEFAULT \'trung_binh\',
                `diem_mac_dinh` DECIMAL(8,2) NOT NULL DEFAULT 1.00,
                `goi_y_tra_loi` TEXT NULL,
                `giai_thich_dap_an` TEXT NULL,
                `trang_thai` VARCHAR(50) NOT NULL DEFAULT \'san_sang\',
                `co_the_tai_su_dung` TINYINT(1) NOT NULL DEFAULT 1,
                `deleted_at` TIMESTAMP NULL,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `ngan_hang_cau_hoi_ma_cau_hoi_unique` (`ma_cau_hoi`),
                INDEX `idx_nhch_khoa_hoc_trang_thai` (`khoa_hoc_id`, `trang_thai`),
                INDEX `idx_nhch_module_loai` (`module_hoc_id`, `loai_cau_hoi`)
            )');
        }

        $this->ensureQuestionBankColumns();
        $this->copyLegacyQuestions();
        $this->repairQuestionAnswers();
        $this->repairExamQuestionDetails();
        $this->tryStatement('ALTER TABLE `ngan_hang_cau_hoi` ADD UNIQUE INDEX `ngan_hang_cau_hoi_ma_cau_hoi_unique` (`ma_cau_hoi`)');
        $this->tryStatement('ALTER TABLE `ngan_hang_cau_hoi` ADD INDEX `idx_nhch_khoa_hoc_trang_thai` (`khoa_hoc_id`, `trang_thai`)');
        $this->tryStatement('ALTER TABLE `ngan_hang_cau_hoi` ADD INDEX `idx_nhch_module_loai` (`module_hoc_id`, `loai_cau_hoi`)');
    }

    private function ensureQuestionBankColumns(): void
    {
        $this->addNullableBigInteger('ngan_hang_cau_hoi', 'khoa_hoc_id', 'id');
        $this->addNullableBigInteger('ngan_hang_cau_hoi', 'module_hoc_id', 'khoa_hoc_id');
        $this->addNullableBigInteger('ngan_hang_cau_hoi', 'nguoi_tao_id', 'module_hoc_id');

        if (! Schema::hasColumn('ngan_hang_cau_hoi', 'ma_cau_hoi')) {
            $this->statement('ALTER TABLE `ngan_hang_cau_hoi` ADD COLUMN `ma_cau_hoi` VARCHAR(60) NULL AFTER `nguoi_tao_id`');
            $this->statement("UPDATE `ngan_hang_cau_hoi` SET `ma_cau_hoi` = CONCAT('CH-', LPAD(`id`, 6, '0')) WHERE `ma_cau_hoi` IS NULL");
        }

        if (! Schema::hasColumn('ngan_hang_cau_hoi', 'noi_dung')) {
            $this->statement("ALTER TABLE `ngan_hang_cau_hoi` ADD COLUMN `noi_dung` LONGTEXT NOT NULL AFTER `ma_cau_hoi`");
        }

        if (! Schema::hasColumn('ngan_hang_cau_hoi', 'loai_cau_hoi')) {
            $this->statement("ALTER TABLE `ngan_hang_cau_hoi` ADD COLUMN `loai_cau_hoi` VARCHAR(50) NOT NULL DEFAULT 'trac_nghiem' AFTER `noi_dung`");
        }

        if (! Schema::hasColumn('ngan_hang_cau_hoi', 'kieu_dap_an')) {
            $this->statement('ALTER TABLE `ngan_hang_cau_hoi` ADD COLUMN `kieu_dap_an` VARCHAR(50) NULL AFTER `loai_cau_hoi`');
        }

        if (! Schema::hasColumn('ngan_hang_cau_hoi', 'muc_do')) {
            $this->statement("ALTER TABLE `ngan_hang_cau_hoi` ADD COLUMN `muc_do` VARCHAR(50) NOT NULL DEFAULT 'trung_binh' AFTER `kieu_dap_an`");
        }

        if (! Schema::hasColumn('ngan_hang_cau_hoi', 'diem_mac_dinh')) {
            $this->statement('ALTER TABLE `ngan_hang_cau_hoi` ADD COLUMN `diem_mac_dinh` DECIMAL(8,2) NOT NULL DEFAULT 1.00 AFTER `muc_do`');
        }

        if (! Schema::hasColumn('ngan_hang_cau_hoi', 'goi_y_tra_loi')) {
            $this->statement('ALTER TABLE `ngan_hang_cau_hoi` ADD COLUMN `goi_y_tra_loi` TEXT NULL AFTER `diem_mac_dinh`');
        }

        if (! Schema::hasColumn('ngan_hang_cau_hoi', 'giai_thich_dap_an')) {
            $this->statement('ALTER TABLE `ngan_hang_cau_hoi` ADD COLUMN `giai_thich_dap_an` TEXT NULL AFTER `goi_y_tra_loi`');
        }

        if (! Schema::hasColumn('ngan_hang_cau_hoi', 'trang_thai')) {
            $this->statement("ALTER TABLE `ngan_hang_cau_hoi` ADD COLUMN `trang_thai` VARCHAR(50) NOT NULL DEFAULT 'san_sang' AFTER `giai_thich_dap_an`");
        }

        if (! Schema::hasColumn('ngan_hang_cau_hoi', 'co_the_tai_su_dung')) {
            $this->statement('ALTER TABLE `ngan_hang_cau_hoi` ADD COLUMN `co_the_tai_su_dung` TINYINT(1) NOT NULL DEFAULT 1 AFTER `trang_thai`');
        }

        if (! Schema::hasColumn('ngan_hang_cau_hoi', 'deleted_at')) {
            $this->statement('ALTER TABLE `ngan_hang_cau_hoi` ADD COLUMN `deleted_at` TIMESTAMP NULL AFTER `co_the_tai_su_dung`');
        }

        if (! Schema::hasColumn('ngan_hang_cau_hoi', 'created_at')) {
            $this->statement('ALTER TABLE `ngan_hang_cau_hoi` ADD COLUMN `created_at` TIMESTAMP NULL AFTER `deleted_at`');
        }

        if (! Schema::hasColumn('ngan_hang_cau_hoi', 'updated_at')) {
            $this->statement('ALTER TABLE `ngan_hang_cau_hoi` ADD COLUMN `updated_at` TIMESTAMP NULL AFTER `created_at`');
        }
    }

    private function copyLegacyQuestions(): void
    {
        if (! Schema::hasTable('cau_hoi')) {
            return;
        }

        $sourceColumns = Schema::getColumnListing('cau_hoi');
        $courseColumn = $this->sourceColumn($sourceColumns, 'ch', 'khoa_hoc_id');
        $moduleColumn = $this->sourceColumn($sourceColumns, 'ch', 'module_hoc_id');
        $creatorColumn = $this->sourceColumn($sourceColumns, 'ch', 'nguoi_tao_id');
        $contentColumn = $this->sourceColumn($sourceColumns, 'ch', 'noi_dung', "CONCAT('Cau hoi ', ch.`id`)");
        $createdAtColumn = $this->sourceColumn($sourceColumns, 'ch', 'created_at', 'NOW()');
        $updatedAtColumn = $this->sourceColumn($sourceColumns, 'ch', 'updated_at', 'NOW()');
        $questionTypeColumn = in_array('loai_cau_hoi', $sourceColumns, true)
            ? "CASE WHEN ch.`loai_cau_hoi` = 'tu_luan' THEN 'tu_luan' ELSE 'trac_nghiem' END"
            : "'trac_nghiem'";
        $difficultyColumn = in_array('muc_do', $sourceColumns, true)
            ? "CASE WHEN ch.`muc_do` IN ('de', 'trung_binh', 'kho') THEN ch.`muc_do` ELSE 'trung_binh' END"
            : "'trung_binh'";

        $answerTypeColumn = "'mot_dap_an'";
        if (Schema::hasTable('dap_an_cau_hoi') && Schema::hasColumn('dap_an_cau_hoi', 'cau_hoi_id') && Schema::hasColumn('dap_an_cau_hoi', 'la_dap_an_dung')) {
            $answerTypeColumn = "CASE
                WHEN ({$questionTypeColumn}) = 'tu_luan' THEN NULL
                WHEN (SELECT COUNT(*) FROM `dap_an_cau_hoi` da WHERE da.`cau_hoi_id` = ch.`id` AND da.`la_dap_an_dung` = 1) > 1 THEN 'nhieu_dap_an'
                ELSE 'mot_dap_an'
            END";
        }

        $this->statement("INSERT INTO `ngan_hang_cau_hoi`
            (`id`, `khoa_hoc_id`, `module_hoc_id`, `nguoi_tao_id`, `ma_cau_hoi`, `noi_dung`, `loai_cau_hoi`, `kieu_dap_an`, `muc_do`, `diem_mac_dinh`, `trang_thai`, `co_the_tai_su_dung`, `created_at`, `updated_at`)
            SELECT ch.`id`, COALESCE({$courseColumn}, (SELECT MIN(`id`) FROM `khoa_hoc`)), {$moduleColumn}, {$creatorColumn},
                CONCAT('CH-', LPAD(ch.`id`, 6, '0')),
                COALESCE(NULLIF({$contentColumn}, ''), CONCAT('Cau hoi ', ch.`id`)),
                {$questionTypeColumn},
                {$answerTypeColumn},
                {$difficultyColumn},
                1.00,
                'san_sang',
                1,
                {$createdAtColumn},
                {$updatedAtColumn}
            FROM `cau_hoi` ch
            WHERE NOT EXISTS (
                SELECT 1 FROM `ngan_hang_cau_hoi` target WHERE target.`id` = ch.`id`
            )");
    }

    private function repairQuestionAnswers(): void
    {
        if (! Schema::hasTable('dap_an_cau_hoi')) {
            $this->statement('CREATE TABLE `dap_an_cau_hoi` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `ngan_hang_cau_hoi_id` BIGINT UNSIGNED NULL,
                `ky_hieu` VARCHAR(10) NULL,
                `noi_dung` TEXT NULL,
                `is_dap_an_dung` TINYINT(1) NOT NULL DEFAULT 0,
                `thu_tu` INT UNSIGNED NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_dap_an_cau_hoi_order` (`ngan_hang_cau_hoi_id`, `thu_tu`)
            )');

            return;
        }

        $afterQuestionId = Schema::hasColumn('dap_an_cau_hoi', 'cau_hoi_id') ? 'cau_hoi_id' : 'id';
        $this->addNullableBigInteger('dap_an_cau_hoi', 'ngan_hang_cau_hoi_id', $afterQuestionId);

        if (! Schema::hasColumn('dap_an_cau_hoi', 'ky_hieu')) {
            $this->statement('ALTER TABLE `dap_an_cau_hoi` ADD COLUMN `ky_hieu` VARCHAR(10) NULL AFTER `ngan_hang_cau_hoi_id`');
        }

        if (! Schema::hasColumn('dap_an_cau_hoi', 'noi_dung')) {
            $this->statement('ALTER TABLE `dap_an_cau_hoi` ADD COLUMN `noi_dung` TEXT NULL AFTER `ky_hieu`');
        }

        if (! Schema::hasColumn('dap_an_cau_hoi', 'is_dap_an_dung')) {
            $this->statement('ALTER TABLE `dap_an_cau_hoi` ADD COLUMN `is_dap_an_dung` TINYINT(1) NOT NULL DEFAULT 0 AFTER `noi_dung`');
        }

        if (! Schema::hasColumn('dap_an_cau_hoi', 'thu_tu')) {
            $this->statement('ALTER TABLE `dap_an_cau_hoi` ADD COLUMN `thu_tu` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `is_dap_an_dung`');
        }

        if (! Schema::hasColumn('dap_an_cau_hoi', 'created_at')) {
            $this->statement('ALTER TABLE `dap_an_cau_hoi` ADD COLUMN `created_at` TIMESTAMP NULL AFTER `thu_tu`');
        }

        if (! Schema::hasColumn('dap_an_cau_hoi', 'updated_at')) {
            $this->statement('ALTER TABLE `dap_an_cau_hoi` ADD COLUMN `updated_at` TIMESTAMP NULL AFTER `created_at`');
        }

        if (Schema::hasColumn('dap_an_cau_hoi', 'cau_hoi_id')) {
            $this->statement('UPDATE `dap_an_cau_hoi` SET `ngan_hang_cau_hoi_id` = `cau_hoi_id` WHERE `ngan_hang_cau_hoi_id` IS NULL');
            $this->tryStatement('ALTER TABLE `dap_an_cau_hoi` MODIFY COLUMN `cau_hoi_id` BIGINT UNSIGNED NULL');
        }

        if (Schema::hasColumn('dap_an_cau_hoi', 'noi_dung_dap_an')) {
            $this->statement('UPDATE `dap_an_cau_hoi` SET `noi_dung` = `noi_dung_dap_an` WHERE `noi_dung` IS NULL');
            $this->tryStatement('ALTER TABLE `dap_an_cau_hoi` MODIFY COLUMN `noi_dung_dap_an` TEXT NULL');
        }

        if (Schema::hasColumn('dap_an_cau_hoi', 'la_dap_an_dung')) {
            $this->statement('UPDATE `dap_an_cau_hoi` SET `is_dap_an_dung` = `la_dap_an_dung` WHERE `la_dap_an_dung` IS NOT NULL');
        }

        if (Schema::hasColumn('dap_an_cau_hoi', 'cau_hoi_id')) {
            $this->statement('UPDATE `dap_an_cau_hoi` da SET `thu_tu` = (
                SELECT COUNT(*) FROM (
                    SELECT `id`, `cau_hoi_id` FROM `dap_an_cau_hoi`
                ) x WHERE x.`cau_hoi_id` = da.`cau_hoi_id` AND x.`id` <= da.`id`
            ) WHERE da.`thu_tu` IS NULL OR da.`thu_tu` = 1');
        }

        $this->statement('UPDATE `dap_an_cau_hoi` SET `ky_hieu` = CHAR(64 + LEAST(`thu_tu`, 26)) WHERE `ky_hieu` IS NULL AND `thu_tu` BETWEEN 1 AND 26');
        $this->tryStatement('ALTER TABLE `dap_an_cau_hoi` ADD INDEX `idx_dap_an_cau_hoi_order` (`ngan_hang_cau_hoi_id`, `thu_tu`)');
    }

    private function repairExamQuestionDetails(): void
    {
        if (! Schema::hasTable('chi_tiet_bai_kiem_tra')) {
            $this->statement('CREATE TABLE `chi_tiet_bai_kiem_tra` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `bai_kiem_tra_id` BIGINT UNSIGNED NOT NULL,
                `ngan_hang_cau_hoi_id` BIGINT UNSIGNED NOT NULL,
                `thu_tu` INT UNSIGNED NOT NULL DEFAULT 1,
                `diem_so` DECIMAL(8,2) NOT NULL DEFAULT 1.00,
                `bat_buoc` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `uniq_bai_kiem_tra_cau_hoi` (`bai_kiem_tra_id`, `ngan_hang_cau_hoi_id`)
            )');

            return;
        }

        $afterExamId = Schema::hasColumn('chi_tiet_bai_kiem_tra', 'cau_hoi_id') ? 'cau_hoi_id' : 'bai_kiem_tra_id';
        $this->addNullableBigInteger('chi_tiet_bai_kiem_tra', 'ngan_hang_cau_hoi_id', $afterExamId);

        if (Schema::hasColumn('chi_tiet_bai_kiem_tra', 'cau_hoi_id')) {
            $this->statement('UPDATE `chi_tiet_bai_kiem_tra` SET `ngan_hang_cau_hoi_id` = `cau_hoi_id` WHERE `ngan_hang_cau_hoi_id` IS NULL');
            $this->tryStatement('ALTER TABLE `chi_tiet_bai_kiem_tra` MODIFY COLUMN `cau_hoi_id` BIGINT UNSIGNED NULL');
        }

        if (! Schema::hasColumn('chi_tiet_bai_kiem_tra', 'diem_so')) {
            $this->statement('ALTER TABLE `chi_tiet_bai_kiem_tra` ADD COLUMN `diem_so` DECIMAL(8,2) NOT NULL DEFAULT 1.00 AFTER `thu_tu`');
            if (Schema::hasColumn('chi_tiet_bai_kiem_tra', 'diem_toi_da')) {
                $this->statement('UPDATE `chi_tiet_bai_kiem_tra` SET `diem_so` = `diem_toi_da` WHERE `diem_toi_da` IS NOT NULL');
            }
        }

        if (! Schema::hasColumn('chi_tiet_bai_kiem_tra', 'bat_buoc')) {
            $this->statement('ALTER TABLE `chi_tiet_bai_kiem_tra` ADD COLUMN `bat_buoc` TINYINT(1) NOT NULL DEFAULT 1 AFTER `diem_so`');
        }

        if (! Schema::hasColumn('chi_tiet_bai_kiem_tra', 'created_at')) {
            $this->statement('ALTER TABLE `chi_tiet_bai_kiem_tra` ADD COLUMN `created_at` TIMESTAMP NULL AFTER `bat_buoc`');
        }

        if (! Schema::hasColumn('chi_tiet_bai_kiem_tra', 'updated_at')) {
            $this->statement('ALTER TABLE `chi_tiet_bai_kiem_tra` ADD COLUMN `updated_at` TIMESTAMP NULL AFTER `created_at`');
        }

        $this->tryStatement('ALTER TABLE `chi_tiet_bai_kiem_tra` ADD UNIQUE INDEX `uniq_bai_kiem_tra_cau_hoi` (`bai_kiem_tra_id`, `ngan_hang_cau_hoi_id`)');
    }

    private function repairHocVienKhoaHoc(): void
    {
        if (! Schema::hasTable('hoc_vien_khoa_hoc')) {
            return;
        }

        if (! Schema::hasColumn('hoc_vien_khoa_hoc', 'created_by')) {
            $this->statement('ALTER TABLE `hoc_vien_khoa_hoc` ADD COLUMN `created_by` BIGINT UNSIGNED NULL AFTER `ghi_chu`');
            if (Schema::hasColumn('hoc_vien_khoa_hoc', 'nguoi_tao_id')) {
                $this->statement('UPDATE `hoc_vien_khoa_hoc` SET `created_by` = `nguoi_tao_id` WHERE `created_by` IS NULL');
            }
        }
    }

    private function repairYeuCauHocVien(): void
    {
        if (! Schema::hasTable('yeu_cau_hoc_vien')) {
            return;
        }

        if (! Schema::hasColumn('yeu_cau_hoc_vien', 'khoa_hoc_id')) {
            $this->statement('ALTER TABLE `yeu_cau_hoc_vien` ADD COLUMN `khoa_hoc_id` BIGINT UNSIGNED NULL AFTER `id`');

            if (Schema::hasColumn('yeu_cau_hoc_vien', 'lop_hoc_id')) {
                if (Schema::hasTable('lop_hoc') && Schema::hasColumn('lop_hoc', 'khoa_hoc_id')) {
                    $this->statement('UPDATE `yeu_cau_hoc_vien` yc JOIN `lop_hoc` lh ON lh.`id` = yc.`lop_hoc_id` SET yc.`khoa_hoc_id` = lh.`khoa_hoc_id` WHERE yc.`khoa_hoc_id` IS NULL');
                } else {
                    $this->statement('UPDATE `yeu_cau_hoc_vien` SET `khoa_hoc_id` = `lop_hoc_id` WHERE `khoa_hoc_id` IS NULL');
                }
            }
        }

        $this->addNullableBigInteger('yeu_cau_hoc_vien', 'giang_vien_id', 'khoa_hoc_id');
        $this->addNullableJson('yeu_cau_hoc_vien', 'du_lieu_yeu_cau', 'loai_yeu_cau');

        if (! Schema::hasColumn('yeu_cau_hoc_vien', 'ly_do')) {
            $this->statement('ALTER TABLE `yeu_cau_hoc_vien` ADD COLUMN `ly_do` TEXT NULL AFTER `du_lieu_yeu_cau`');
            if (Schema::hasColumn('yeu_cau_hoc_vien', 'noi_dung')) {
                $this->statement('UPDATE `yeu_cau_hoc_vien` SET `ly_do` = `noi_dung` WHERE `ly_do` IS NULL');
            }
        }

        $this->addNullableBigInteger('yeu_cau_hoc_vien', 'admin_duyet_id', 'trang_thai');

        if (! Schema::hasColumn('yeu_cau_hoc_vien', 'thoi_gian_duyet')) {
            $this->statement('ALTER TABLE `yeu_cau_hoc_vien` ADD COLUMN `thoi_gian_duyet` TIMESTAMP NULL AFTER `admin_duyet_id`');
        }
    }

    private function addNullableBigInteger(string $table, string $column, string $after): void
    {
        if (! Schema::hasColumn($table, $column)) {
            $this->statement("ALTER TABLE `{$table}` ADD COLUMN `{$column}` BIGINT UNSIGNED NULL AFTER `{$after}`");
        }
    }

    private function addNullableJson(string $table, string $column, string $after): void
    {
        if (! Schema::hasColumn($table, $column)) {
            $type = $this->driver() === 'mysql' ? 'JSON' : 'TEXT';
            $this->statement("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$type} NULL AFTER `{$after}`");
        }
    }

    private function addNullableDate(string $table, string $column, string $after): void
    {
        if (! Schema::hasColumn($table, $column)) {
            $this->statement("ALTER TABLE `{$table}` ADD COLUMN `{$column}` DATE NULL AFTER `{$after}`");
        }
    }

    private function addNullableDateTime(string $table, string $column, string $after): void
    {
        if (! Schema::hasColumn($table, $column)) {
            $this->statement("ALTER TABLE `{$table}` ADD COLUMN `{$column}` DATETIME NULL AFTER `{$after}`");
        }
    }

    private function addBooleanColumn(string $table, string $column, string $after, string $default): void
    {
        if (! Schema::hasColumn($table, $column)) {
            $this->statement("ALTER TABLE `{$table}` ADD COLUMN `{$column}` TINYINT(1) NOT NULL DEFAULT {$default} AFTER `{$after}`");
        }
    }

    private function addDecimalIfMissing(string $table, string $column, string $after, string $default): void
    {
        if (! Schema::hasColumn($table, $column)) {
            $this->statement("ALTER TABLE `{$table}` ADD COLUMN `{$column}` DECIMAL(5,2) NOT NULL DEFAULT {$default} AFTER `{$after}`");
        }
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function sourceColumn(array $columns, string $alias, string $column, string $fallback = 'NULL'): string
    {
        return in_array($column, $columns, true) ? "`{$alias}`.`{$column}`" : $fallback;
    }

    private function statement(string $sql): void
    {
        if ($this->option('dry-run')) {
            $this->line($sql);

            return;
        }

        DB::statement($sql);
        $this->line($sql);
    }

    private function tryStatement(string $sql): void
    {
        try {
            $this->statement($sql);
        } catch (Throwable $exception) {
            $this->warn('Skipped: ' . $sql);
            $this->warn($exception->getMessage());
        }
    }

    private function driver(): string
    {
        return DB::connection()->getDriverName();
    }
}
