<?php

namespace Tests\Feature;

use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sidebar_groups_approval_features_under_one_menu(): void
    {
        $admin = NguoiDung::create([
            'ho_ten' => 'Admin Sidebar',
            'email' => 'admin-sidebar@example.com',
            'mat_khau' => bcrypt('password'),
            'vai_tro' => 'admin',
            'trang_thai' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('data-bs-target="#approvalGroup"', false);
        $response->assertSee('Phê duyệt');
        $response->assertSee(route('admin.phe-duyet-tai-khoan.index'), false);
        $response->assertSee(route('admin.kiem-tra-online.phe-duyet.index'), false);
        $response->assertSee(route('admin.bai-giang.index'), false);
        $response->assertSee(route('admin.giang-vien-don-xin-nghi.index'), false);
    }
}
