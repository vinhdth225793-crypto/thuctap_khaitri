<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = App\Models\NguoiDung::where('vai_tro', 'giang_vien')->whereDoesntHave('giangVien')->get();
foreach($users as $user) {
    App\Models\GiangVien::create([
        'nguoi_dung_id' => $user->ma_nguoi_dung,
        'chuyen_nganh' => 'Chưa cập nhật',
        'hoc_vi' => 'Chưa cập nhật',
        'so_gio_day' => 0,
        'hien_thi_trang_chu' => false,
        'mo_ta_ngan' => null,
        'avatar_url' => null,
    ]);
    echo 'Created GiangVien record for: ' . $user->ho_ten . PHP_EOL;
}
echo 'Done!';
?>