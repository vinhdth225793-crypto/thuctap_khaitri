<?php

namespace App\Http\Controllers;

use App\Models\ThongBao;
use Illuminate\Http\Request;

class ThongBaoController extends Controller
{
    /**
     * Danh sách thông báo của người dùng hiện tại
     */
    public function index()
    {
        $thongBaos = ThongBao::where('nguoi_nhan_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Đánh dấu tất cả là đã đọc khi vào trang danh sách
        ThongBao::where('nguoi_nhan_id', auth()->id())
            ->where('da_doc', 0)
            ->update(['da_doc' => 1]);

        return view('pages.thong-bao.index', compact('thongBaos'));
    }

    /**
     * Đọc một thông báo và chuyển hướng
     */
    public function docMot($id)
    {
        $tb = ThongBao::where('id', $id)
            ->where('nguoi_nhan_id', auth()->id())
            ->firstOrFail();
            
        $tb->update(['da_doc' => 1]);

        return redirect($tb->url ?? route('thong-bao.index'));
    }
}
