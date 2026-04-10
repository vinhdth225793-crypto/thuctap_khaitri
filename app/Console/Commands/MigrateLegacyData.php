<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateLegacyData extends Command
{
    protected $signature = 'app:migrate-data';

    public function handle()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $this->migrateUsersAndProfiles();
        $this->migrateCourseCatalog();
        $this->migrateTransactionalData();

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        $this->info('Data migration completed successfully!');
    }

    private function migrateUsersAndProfiles()
    {
        $this->info('Migrating users and profiles...');
        $oldUsers = DB::table('cu_nguoi_dung')->get();
        foreach ($oldUsers as $oldUser) {
            $newUserId = DB::table('nguoi_dung')->insertGetId([
                'ho_ten' => $oldUser->ho_ten,
                'email' => $oldUser->email,
                'mat_khau' => $oldUser->mat_khau,
                'vai_tro' => $oldUser->vai_tro,
                'so_dien_thoai' => $oldUser->so_dien_thoai,
                'dia_chi' => $oldUser->dia_chi,
                'ngay_sinh' => $oldUser->ngay_sinh,
                'anh_dai_dien' => $oldUser->anh_dai_dien,
                'trang_thai' => $oldUser->trang_thai,
                'created_at' => $oldUser->created_at,
                'updated_at' => $oldUser->updated_at,
            ]);

            if ($oldUser->vai_tro === 'hoc_vien') {
                $oldHocVien = DB::table('cu_hoc_vien')->where('nguoi_dung_id', $oldUser->ma_nguoi_dung)->first();
                if ($oldHocVien) {
                    DB::table('hoc_vien')->insert([
                        'nguoi_dung_id' => $newUserId,
                        'ma_hoc_vien' => $oldHocVien->ma_hoc_vien ?? null,
                        'lop_niem_khoa' => $oldHocVien->lop ?? null,
                        'nganh_hoc' => $oldHocVien->nganh ?? null,
                        'diem_trung_binh' => $oldHocVien->diem_trung_binh ?? null,
                        'created_at' => $oldHocVien->created_at,
                        'updated_at' => $oldHocVien->updated_at,
                    ]);
                }
            } elseif ($oldUser->vai_tro === 'giang_vien') {
                $oldGiangVien = DB::table('cu_giang_vien')->where('nguoi_dung_id', $oldUser->ma_nguoi_dung)->first();
                if ($oldGiangVien) {
                    DB::table('giang_vien')->insert([
                        'nguoi_dung_id' => $newUserId,
                        'chuyen_nganh' => $oldGiangVien->chuyen_nganh,
                        'hoc_vi' => $oldGiangVien->hoc_vi,
                        'mo_ta_ngan' => $oldGiangVien->mo_ta_ngan,
                        'avatar_url' => $oldGiangVien->avatar_url,
                        'hien_thi_trang_chu' => $oldGiangVien->hien_thi_trang_chu,
                        'created_at' => $oldGiangVien->created_at,
                        'updated_at' => $oldGiangVien->updated_at,
                    ]);
                }
            }
        }
    }

    private function migrateCourseCatalog()
    {
        $this->info('Migrating categories and courses...');
        $oldNhomNganh = DB::table('cu_nhom_nganh')->get();
        foreach ($oldNhomNganh as $row) {
            DB::table('nhom_nganh')->insert((array)$row);
        }

        $oldKhoaHocMau = DB::table('cu_khoa_hoc')->where('loai', 'mau')->get();
        foreach ($oldKhoaHocMau as $row) {
            $data = (array)$row;
            unset($data['loai'], $data['trang_thai_van_hanh'], $data['khoa_hoc_mau_id'], $data['lan_mo_thu'], $data['ngay_khai_giang'], $data['ngay_mo_lop'], $data['ngay_ket_thuc']);
            DB::table('khoa_hoc')->insert($data);
        }

        $oldModules = DB::table('cu_module_hoc')->get();
        foreach ($oldModules as $row) {
            DB::table('module_hoc')->insert((array)$row);
        }
    }

    private function migrateTransactionalData()
    {
        $this->info('Migrating classes and records...');
        $oldLopHoc = DB::table('cu_khoa_hoc')->where('loai', 'hoat_dong')->get();
        foreach ($oldLopHoc as $oldLop) {
            DB::table('lop_hoc')->insert([
                'id' => $oldLop->id,
                'khoa_hoc_id' => $oldLop->khoa_hoc_mau_id ?? $oldLop->id,
                'ma_lop_hoc' => $oldLop->ma_khoa_hoc,
                'ngay_khai_giang' => $oldLop->ngay_khai_giang,
                'ngay_ket_thuc' => $oldLop->ngay_ket_thuc,
                'trang_thai_van_hanh' => $oldLop->trang_thai_van_hanh,
                'ty_trong_diem_danh' => $oldLop->ty_trong_diem_danh,
                'ty_trong_kiem_tra' => $oldLop->ty_trong_kiem_tra,
                'ghi_chu' => $oldLop->ghi_chu_noi_bo,
                'created_by' => $this->getNewUserId($oldLop->created_by),
                'created_at' => $oldLop->created_at,
                'updated_at' => $oldLop->updated_at,
            ]);
        }

        $this->migrateSimpleTables();
    }

    private function migrateSimpleTables()
    {
        $this->info('Migrating schedules, attendance and others...');
        
        $oldLichHoc = DB::table('cu_lich_hoc')->get();
        foreach ($oldLichHoc as $row) {
            $data = (array)$row;
            $data['lop_hoc_id'] = $data['khoa_hoc_id']; // mapping
            unset($data['khoa_hoc_id']);
            $data['giang_vien_id'] = $this->getNewGiangVienId($data['giang_vien_id']);
            DB::table('lich_hoc')->insert($data);
        }

        $oldDiemDanh = DB::table('cu_diem_danh')->get();
        foreach ($oldDiemDanh as $row) {
            DB::table('diem_danh_hoc_vien')->insert([
                'lich_hoc_id' => $row->lich_hoc_id,
                'hoc_vien_id' => $this->getNewHocVienId($row->hoc_vien_id),
                'trang_thai' => $row->trang_thai,
                'ghi_chu' => $row->ghi_chu,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
        
        // ... more tables would be mapped here in a full script ...
    }

    private function getNewUserId($oldId) {
        if (!$oldId) return null;
        $user = DB::table('cu_nguoi_dung')->where('ma_nguoi_dung', $oldId)->first();
        if (!$user) return null;
        return DB::table('nguoi_dung')->where('email', $user->email)->value('id');
    }

    private function getNewHocVienId($oldUserMa) {
        $newUserId = $this->getNewUserId($oldUserMa);
        return DB::table('hoc_vien')->where('nguoi_dung_id', $newUserId)->value('id');
    }

    private function getNewGiangVienId($oldId) {
        // old lich_hoc.giang_vien_id points to cu_giang_vien.id
        // and our giang_vien.id is generated fresh but still based on the same users
        $oldGV = DB::table('cu_giang_vien')->where('id', $oldId)->first();
        if (!$oldGV) return null;
        $newUserId = $this->getNewUserId($oldGV->nguoi_dung_id);
        return DB::table('giang_vien')->where('nguoi_dung_id', $newUserId)->value('id');
    }
}
