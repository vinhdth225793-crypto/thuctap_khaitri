<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== GIẢNG VIÊN USERS ===\n";
$users = App\Models\NguoiDung::where('vai_tro', 'giang_vien')->get();
foreach($users as $user) {
    echo $user->ho_ten . ' (ID: ' . $user->ma_nguoi_dung . ') - Has GiangVien: ' . ($user->giangVien ? 'Yes' : 'No') . "\n";
}

echo "\n=== GIANGVIEN RECORDS ===\n";
$giangViens = App\Models\GiangVien::with('nguoiDung')->get();
foreach($giangViens as $gv) {
    echo 'GiangVien ID: ' . $gv->id . ' - User: ' . ($gv->nguoiDung ? $gv->nguoiDung->ho_ten : 'No user') . "\n";
}