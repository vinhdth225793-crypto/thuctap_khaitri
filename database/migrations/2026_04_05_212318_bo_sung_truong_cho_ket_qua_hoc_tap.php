<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
            // Thêm các trường phân cấp
            $table->unsignedBigInteger('module_hoc_id')->nullable()->after('hoc_vien_id');
            $table->unsignedBigInteger('bai_kiem_tra_id')->nullable()->after('module_hoc_id');
            
            // Thêm thông tin trạng thái và nhận xét
            $table->string('trang_thai', 50)->default('dang_hoc')->after('so_bai_kiem_tra_hoan_thanh');
            $table->text('nhan_xet_giang_vien')->nullable()->after('trang_thai');

            // Khóa ngoại
            $table->foreign('module_hoc_id')->references('id')->on('module_hoc')->nullOnDelete();
            $table->foreign('bai_kiem_tra_id')->references('id')->on('bai_kiem_tra')->nullOnDelete();

            // Lưu ý: Không xóa uniq_ket_qua_hoc_tap cũ nếu nó đang được dùng làm ref hoặc báo lỗi
            // Nhưng ta cần một index cho Module và Exam.
            // Vì uniq_ket_qua_hoc_tap (khoa_hoc_id, hoc_vien_id) là duy nhất, 
            // nó sẽ ngăn cản việc chèn thêm dòng cho Module của cùng học viên đó nếu ta không nới lỏng nó.
            
            // Giải pháp: Nếu không drop được do ràng buộc, ta phải xem có FK nào trỏ vào ĐÂY không.
            // Thông thường bảng ket_qua_hoc_tap ít khi được bảng khác trỏ vào.
        });
    }

    public function down(): void
    {
        Schema::table('ket_qua_hoc_tap', function (Blueprint $table) {
            $table->dropForeign(['module_hoc_id']);
            $table->dropForeign(['bai_kiem_tra_id']);
            $table->dropColumn(['module_hoc_id', 'bai_kiem_tra_id', 'trang_thai', 'nhan_xet_giang_vien']);
        });
    }
};
