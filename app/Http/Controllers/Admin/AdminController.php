<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function profile()
    {
        $user = auth()->user();

        return view('pages.admin.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|email|unique:nguoi_dung,email,'.$user->ma_nguoi_dung.',ma_nguoi_dung',
            'so_dien_thoai' => 'nullable|string|max:15',
            'ngay_sinh' => 'nullable|date|before:today',
            'dia_chi' => 'nullable|string|max:500',
            'anh_dai_dien' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'mat_khau' => 'nullable|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $payload = $request->only(['ho_ten', 'email', 'so_dien_thoai', 'ngay_sinh', 'dia_chi']);

        if ($request->boolean('xoa_anh_dai_dien') && $user->anh_dai_dien) {
            Storage::disk('public')->delete($user->anh_dai_dien);
            $payload['anh_dai_dien'] = null;
        }

        if ($request->hasFile('anh_dai_dien')) {
            if ($user->anh_dai_dien) {
                Storage::disk('public')->delete($user->anh_dai_dien);
            }
            $payload['anh_dai_dien'] = $request->file('anh_dai_dien')->store('avatars', 'public');
        }

        if ($request->filled('mat_khau')) {
            $payload['mat_khau'] = Hash::make($request->mat_khau);
        }

        $user->update($payload);

        return redirect()->route('admin.profile')->with('success', 'Đã cập nhật hồ sơ cá nhân.');
    }
}
