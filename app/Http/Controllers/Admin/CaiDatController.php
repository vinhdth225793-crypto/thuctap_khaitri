<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiangVien;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class CaiDatController extends Controller
{
    public function caiDat()
    {
        $settings = [
            'site_name' => config('app.name', 'Hệ thống Quản lý'),
            'site_email' => config('mail.from.address', 'admin@example.com'),
            'items_per_page' => config('app.items_per_page', 20),
            'enable_registration' => config('app.enable_registration', true),
            'maintenance_mode' => config('app.maintenance_mode', false),
        ];

        return view('pages.admin.settings.cai-dat', compact('settings'));
    }

    public function luuCaiDat(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_email' => 'required|email',
            'items_per_page' => 'required|integer|min:5|max:100',
            'enable_registration' => 'required|boolean',
            'maintenance_mode' => 'required|boolean',
        ]);

        $envPath = base_path('.env');

        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);

            foreach ($validated as $key => $value) {
                $envKey = 'APP_'.strtoupper($key);
                $envValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;

                if (strpos($envContent, "{$envKey}=") !== false) {
                    $envContent = preg_replace(
                        "/^{$envKey}=.*/m",
                        "{$envKey}={$envValue}",
                        $envContent
                    );
                } else {
                    $envContent .= "\n{$envKey}={$envValue}";
                }
            }

            file_put_contents($envPath, $envContent);
        }

        return redirect()->route('admin.settings')
            ->with('success', 'Cập nhật cài đặt hệ thống thành công.');
    }

    public function showSettings()
    {
        $settings = [
            'hotline' => SystemSetting::get('hotline', ''),
            'email' => SystemSetting::get('email', ''),
            'facebook' => SystemSetting::get('facebook', ''),
            'zalo' => SystemSetting::get('zalo', ''),
        ];

        $instructors = GiangVien::with('nguoiDung')->get();

        return view('pages.admin.settings.cai-dat', [
            'settings' => $settings,
            'instructors' => $instructors,
        ]);
    }

    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'hotline' => 'nullable|string',
            'email' => 'nullable|email',
            'facebook' => 'nullable|url',
            'zalo' => 'nullable|url',
            'address' => 'nullable|string',
            'general_notification' => 'nullable|string',
        ]);

        if ($request->hasFile('site_logo')) {
            $file = $request->file('site_logo');
            $filename = time().'_logo.'.$file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $validated['site_logo'] = 'images/'.$filename;
        }

        foreach ($validated as $key => $value) {
            if ($key === 'site_logo' && ! $request->hasFile('site_logo')) {
                continue;
            }

            if ($request->exists($key) || $request->hasFile($key)) {
                SystemSetting::set($key, $value ?? '');
            }
        }

        $currentRoute = $request->route()->getName();

        if (str_contains($currentRoute, 'contact')) {
            return redirect()->route('admin.settings.contact')
                ->with('success', 'Thông tin liên hệ đã được cập nhật thành công!');
        } elseif (str_contains($currentRoute, 'social')) {
            return redirect()->route('admin.settings.social')
                ->with('success', 'Mạng xã hội đã được cập nhật thành công!');
        } else {
            return redirect()->route('admin.settings')
                ->with('success', 'Cài đặt hệ thống đã được cập nhật thành công!');
        }
    }

    public function saveInstructorSettings(Request $request)
    {
        $instructorIds = $request->get('instructors', []);
        GiangVien::query()->update(['hien_thi_trang_chu' => false]);

        if (! empty($instructorIds)) {
            GiangVien::whereIn('id', $instructorIds)
                ->update(['hien_thi_trang_chu' => true]);
        }

        return redirect()->route('admin.settings.instructors')
            ->with('success', 'Chọn giảng viên hiển thị trên trang chủ đã được cập nhật thành công!');
    }

    public function showContactSettings()
    {
        $settings = [
            'site_name' => SystemSetting::get('site_name', ''),
            'site_logo' => SystemSetting::get('site_logo', ''),
            'hotline' => SystemSetting::get('hotline', ''),
            'email' => SystemSetting::get('email', ''),
            'address' => SystemSetting::get('address', ''),
            'general_notification' => SystemSetting::get('general_notification', ''),
            'banner_images' => collect(json_decode(SystemSetting::get('banner_images', '[]'), true) ?: [])
                ->map(fn ($p) => asset($p))
                ->toArray(),
        ];

        return view('pages.admin.settings.contact', compact('settings'));
    }

    public function showSocialSettings()
    {
        $settings = [
            'facebook' => SystemSetting::get('facebook', ''),
            'zalo' => SystemSetting::get('zalo', ''),
        ];

        return view('pages.admin.settings.social', compact('settings'));
    }

    public function showInstructorSettings()
    {
        $instructors = GiangVien::with('nguoiDung')->get();

        return view('pages.admin.settings.instructors', compact('instructors'));
    }
}
