<?php

namespace App\Services;

use App\Models\KhoaHoc;
use App\Models\ModuleHoc;
use App\Models\NguoiDung;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class QuestionBankCourseCatalogService
{
    public function getAccessibleKhoaHocs(NguoiDung $user): Collection
    {
        $query = KhoaHoc::query()
            ->select(['id', 'ten_khoa_hoc', 'ma_khoa_hoc', 'loai', 'khoa_hoc_mau_id'])
            ->orderBy('ten_khoa_hoc');

        if ($user->isGiangVien()) {
            $giangVien = $user->giangVien;
            $khoaHocIds = $giangVien
                ? $giangVien->khoaHocDuocPhanCong()->pluck('khoa_hoc.id')->unique()->toArray()
                : [];

            $query->whereIn('id', $khoaHocIds);
        }

        return $query->get();
    }

    public function getAccessibleModules(Collection $khoaHocs): Collection
    {
        if ($khoaHocs->isEmpty()) {
            return collect();
        }

        return ModuleHoc::query()
            ->join('khoa_hoc', 'khoa_hoc.id', '=', 'module_hoc.khoa_hoc_id')
            ->whereIn('module_hoc.khoa_hoc_id', $khoaHocs->pluck('id'))
            ->orderBy('module_hoc.khoa_hoc_id')
            ->orderBy('module_hoc.thu_tu_module')
            ->get([
                'module_hoc.id',
                'module_hoc.khoa_hoc_id',
                'module_hoc.ten_module',
                'module_hoc.ma_module',
                'khoa_hoc.loai as khoa_hoc_loai',
            ]);
    }

    public function buildCatalogForUser(NguoiDung $user): array
    {
        $khoaHocs = $this->getAccessibleKhoaHocs($user);

        return [
            'khoaHocs' => $khoaHocs,
            'sampleCourses' => $khoaHocs->where('loai', 'mau')->values(),
            'activeCourses' => $khoaHocs->where('loai', 'hoat_dong')->values(),
            'modules' => $this->getAccessibleModules($khoaHocs),
        ];
    }

    public function normalizeCourseType(?string $courseType): ?string
    {
        $normalized = trim((string) $courseType);

        return in_array($normalized, ['mau', 'hoat_dong'], true)
            ? $normalized
            : null;
    }

    public function resolveCourseType(?string $requestedType, ?int $courseId = null): ?string
    {
        $normalized = $this->normalizeCourseType($requestedType);
        if ($normalized !== null) {
            return $normalized;
        }

        if ($courseId === null) {
            return null;
        }

        return KhoaHoc::query()
            ->whereKey($courseId)
            ->value('loai');
    }

    public function ensureCourseMatchesType(int $courseId, ?string $courseType): KhoaHoc
    {
        $khoaHoc = KhoaHoc::query()
            ->select(['id', 'ten_khoa_hoc', 'ma_khoa_hoc', 'loai', 'khoa_hoc_mau_id'])
            ->findOrFail($courseId);

        $normalized = $this->normalizeCourseType($courseType);
        if ($normalized !== null && $khoaHoc->loai !== $normalized) {
            throw ValidationException::withMessages([
                'course_type' => 'Khóa học không thuộc loại đã chọn.',
            ]);
        }

        if ($normalized !== null && $khoaHoc->loai !== $normalized) {
            throw ValidationException::withMessages([
                'course_type' => 'Khóa học không thuộc loại đã chọn.',
            ]);
        }

        return $khoaHoc;
    }

    public function courseTypeLabel(?string $courseType): string
    {
        if ($courseType === 'mau') {
            return 'Khóa học mẫu';
        }

        if ($courseType === 'hoat_dong') {
            return 'Khóa học hoạt động';
        }

        return 'Chưa xác định';

        return match ($courseType) {
            'mau' => 'Khóa học mẫu',
            'hoat_dong' => 'Khóa học hoạt động',
            default => 'Chưa xác định',
        };
    }

    public function courseTypeBadgeColor(?string $courseType): string
    {
        return match ($courseType) {
            'mau' => 'info',
            'hoat_dong' => 'primary',
            default => 'secondary',
        };
    }
}
