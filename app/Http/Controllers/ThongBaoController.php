<?php

namespace App\Http\Controllers;

use App\Models\ThongBao;
use Illuminate\Http\Request;

class ThongBaoController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $filter = $request->query('loc'); // 'chua_doc' | null

        $query = ThongBao::ofUser($userId)->moiNhat();
        if ($filter === 'chua_doc') {
            $query->chuaDoc();
        }

        $thongBaos = $query->paginate(20)->withQueryString();
        $tongChua  = ThongBao::ofUser($userId)->chuaDoc()->count();
        $tongTatCa = ThongBao::ofUser($userId)->count();

        return view('pages.thong-bao.index', compact('thongBaos', 'tongChua', 'tongTatCa', 'filter'));
    }

    /** Đánh dấu đã đọc + redirect (giữ tương thích route cũ docMot) */
    public function docMot($id)
    {
        return $this->markRead((int) $id);
    }

    public function markRead(int $id)
    {
        $userId = auth()->id();
        $tb = ThongBao::ofUser($userId)->findOrFail($id);
        $tb->update(['da_doc' => true]);

        if ($tb->url) {
            return redirect()->to($tb->url);
        }
        return redirect()->route('thong-bao.index');
    }

    public function markAllRead()
    {
        ThongBao::ofUser(auth()->id())->chuaDoc()->update(['da_doc' => true]);
        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }

    public function destroy(int $id)
    {
        ThongBao::ofUser(auth()->id())->where('id', $id)->delete();
        return back()->with('success', 'Đã xóa thông báo.');
    }

    /** API JSON cho dropdown bell */
    public function jsonRecent()
    {
        $userId = auth()->id();
        $items  = ThongBao::ofUser($userId)->moiNhat()->limit(8)->get();
        $tongChua = ThongBao::ofUser($userId)->chuaDoc()->count();

        return response()->json([
            'unread_count' => $tongChua,
            'items' => $items->map(fn ($tb) => [
                'id'        => $tb->id,
                'tieu_de'   => $tb->tieu_de,
                'noi_dung'  => $tb->noi_dung,
                'level'     => $tb->level,
                'icon'      => $tb->icon_class,
                'url'       => $tb->url ? route('thong-bao.read', $tb->id) : null,
                'da_doc'    => (bool) $tb->da_doc,
                'time_ago'  => optional($tb->created_at)->diffForHumans(),
            ]),
        ]);
    }
}
