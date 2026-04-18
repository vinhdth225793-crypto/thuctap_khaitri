@extends('layouts.app', ['title' => 'Chi tiet ket qua hoc tap'])

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <a href="{{ route('admin.ket-qua.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
                <i class="fas fa-arrow-left me-1"></i> Quay lai
            </a>
            <h2 class="fw-bold mb-1">{{ $khoa_hoc->ten_khoa_hoc }}</h2>
            <p class="text-muted mb-0">{{ $khoa_hoc->ma_khoa_hoc }} - {{ $khoa_hoc->phuong_thuc_danh_gia_label }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <div class="bg-white border rounded-3 px-3 py-2 text-center">
                <div class="small text-muted text-uppercase fw-bold">Hoc vien</div>
                <div class="fs-5 fw-bold">{{ $summary['student_count'] }}</div>
            </div>
            <div class="bg-white border rounded-3 px-3 py-2 text-center">
                <div class="small text-muted text-uppercase fw-bold">Da co diem</div>
                <div class="fs-5 fw-bold">{{ $summary['course_result_count'] }}</div>
            </div>
            <div class="bg-white border rounded-3 px-3 py-2 text-center">
                <div class="small text-muted text-uppercase fw-bold">Diem TB</div>
                <div class="fs-5 fw-bold">{{ $summary['average_score'] !== null ? number_format((float) $summary['average_score'], 2) : '--' }}</div>
            </div>
            <div class="bg-white border rounded-3 px-3 py-2 text-center">
                <div class="small text-muted text-uppercase fw-bold">Cho duyet</div>
                <div class="fs-5 fw-bold text-warning">{{ $summary['pending_approval_count'] ?? 0 }}</div>
            </div>
            <div class="bg-white border rounded-3 px-3 py-2 text-center">
                <div class="small text-muted text-uppercase fw-bold">Luu ho so</div>
                <div class="fs-5 fw-bold text-success">{{ $summary['archived_count'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Hoc vien</th>
                        <th class="text-center">Diem danh</th>
                        <th class="text-center">Kiem tra</th>
                        <th class="text-center">Tong ket</th>
                        <th>Drill down</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($student_results as $row)
                        @php
                            $student = $row['student'];
                            $courseResult = $row['course_result'];
                            $moduleResults = $row['module_results'];
                            $examResults = $row['exam_results'];
                            $attemptsByExam = $row['attempts_by_exam'] ?? collect();
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ $student?->ho_ten ?? 'Khong ro hoc vien' }}</div>
                                <div class="small text-muted">{{ $student?->email ?? '' }}</div>
                                @if($courseResult?->aggregation_strategy_used)
                                    <div class="small text-primary">Course strategy: {{ $courseResult->aggregation_strategy_used }}</div>
                                @endif
                            </td>
                            <td class="text-center">{{ $courseResult?->diem_diem_danh !== null ? number_format((float) $courseResult->diem_diem_danh, 2) : '--' }}</td>
                            <td class="text-center">{{ $courseResult?->diem_kiem_tra !== null ? number_format((float) $courseResult->diem_kiem_tra, 2) : '--' }}</td>
                            <td class="text-center">
                                <div class="fw-bold text-primary">{{ $courseResult?->diem_tong_ket !== null ? number_format((float) $courseResult->diem_tong_ket, 2) : '--' }}</div>
                                @if($courseResult?->diem_giang_vien_chot !== null)
                                    <div class="small text-success">Chot: {{ number_format((float) $courseResult->diem_giang_vien_chot, 2) }}</div>
                                @endif
                                <span class="badge text-bg-light border">{{ $courseResult?->trang_thai ?? 'chua co' }}</span>
                            </td>
                            <td style="min-width: 420px;">
                                <div class="accordion" id="admin-result-{{ $student?->ma_nguoi_dung ?? $loop->index }}">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#modules-{{ $student?->ma_nguoi_dung ?? $loop->index }}">
                                                Module ({{ $moduleResults->count() }})
                                            </button>
                                        </h2>
                                        <div id="modules-{{ $student?->ma_nguoi_dung ?? $loop->index }}" class="accordion-collapse collapse">
                                            <div class="accordion-body small">
                                                @forelse($moduleResults as $moduleResult)
                                                    <div class="border-bottom py-2">
                                                        <div class="fw-bold">{{ $moduleResult->moduleHoc?->ten_module ?? 'Module' }}</div>
                                                        <div>Diem: {{ $moduleResult->diem_tong_ket !== null ? number_format((float) $moduleResult->diem_tong_ket, 2) : '--' }}</div>
                                                        @if($moduleResult->diem_giang_vien_chot !== null)
                                                            <div class="text-success">Diem GV chot: {{ number_format((float) $moduleResult->diem_giang_vien_chot, 2) }}</div>
                                                        @endif
                                                        <div class="text-muted">Strategy: {{ $moduleResult->aggregation_strategy_used ?? 'all_exams_average' }}</div>
                                                        <div class="mt-1">
                                                            <span class="badge text-bg-light border">{{ $moduleResult->trang_thai_chot_label }}</span>
                                                            <span class="badge text-bg-light border">{{ $moduleResult->trang_thai_duyet_label }}</span>
                                                            @if($moduleResult->luu_ho_so_luc)
                                                                <span class="badge text-bg-success">Ho so: {{ $moduleResult->luu_ho_so_luc->format('d/m/Y H:i') }}</span>
                                                            @endif
                                                        </div>
                                                        @if($moduleResult->trang_thai_duyet === 'cho_duyet')
                                                            <div class="d-flex gap-2 flex-wrap mt-2">
                                                                <form method="POST" action="{{ route('admin.ket-qua.approve', $moduleResult->id) }}" class="d-flex gap-1">
                                                                    @csrf
                                                                    <input type="text" name="ghi_chu_duyet" class="form-control form-control-sm" placeholder="Ghi chu admin">
                                                                    <button class="btn btn-sm btn-success" type="submit">Duyet</button>
                                                                </form>
                                                                <form method="POST" action="{{ route('admin.ket-qua.reject', $moduleResult->id) }}" class="d-flex gap-1">
                                                                    @csrf
                                                                    <input type="text" name="ghi_chu_duyet" class="form-control form-control-sm" placeholder="Ly do tra ve">
                                                                    <button class="btn btn-sm btn-outline-danger" type="submit">Tra ve</button>
                                                                </form>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <div class="text-muted">Chua co ket qua module.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#exams-{{ $student?->ma_nguoi_dung ?? $loop->index }}">
                                                Bai kiem tra ({{ $examResults->count() }})
                                            </button>
                                        </h2>
                                        <div id="exams-{{ $student?->ma_nguoi_dung ?? $loop->index }}" class="accordion-collapse collapse">
                                            <div class="accordion-body small">
                                                @forelse($examResults as $examResult)
                                                    <div class="border-bottom py-2">
                                                        <div class="fw-bold">{{ $examResult->baiKiemTra?->tieu_de ?? 'Bai kiem tra' }}</div>
                                                        <div>Diem chinh thuc: {{ $examResult->diem_kiem_tra !== null ? number_format((float) $examResult->diem_kiem_tra, 2) : '--' }}</div>
                                                        <div class="text-muted">Attempt strategy: {{ $examResult->attempt_strategy_used ?? 'highest_score' }}</div>
                                                        @foreach(($attemptsByExam[$examResult->bai_kiem_tra_id] ?? collect()) as $attempt)
                                                            @php
                                                                $officialIds = collect($examResult->source_attempt_ids ?: []);
                                                                if ($examResult->source_attempt_id) {
                                                                    $officialIds->push((int) $examResult->source_attempt_id);
                                                                }
                                                            @endphp
                                                            <div class="ms-3 text-muted">
                                                                Lan {{ $attempt->lan_lam_thu }}:
                                                                {{ $attempt->diem_so !== null ? number_format((float) $attempt->diem_so, 2) : 'cho cham' }}
                                                                - {{ $attempt->trang_thai_cham }}
                                                                @if($officialIds->map(fn ($id) => (int) $id)->contains((int) $attempt->id))
                                                                    <span class="badge text-bg-primary">chinh thuc</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @empty
                                                    <div class="text-muted">Chua co ket qua bai kiem tra.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">Chua co hoc vien trong khoa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
