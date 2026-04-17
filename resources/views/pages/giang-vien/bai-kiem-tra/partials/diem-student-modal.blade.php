@php
    $exam = $card['exam'];
    $studentModalId = 'scoreStudentsModal-' . $card['id'];
@endphp

@if($exam)
    <div class="modal fade score-students-modal" id="{{ $studentModalId }}" tabindex="-1" aria-labelledby="{{ $studentModalId }}Label" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header score-modal-header">
                    <div>
                        <div class="small text-uppercase fw-bold opacity-75 mb-1">Danh sách học viên làm bài</div>
                        <h5 class="modal-title fw-bold" id="{{ $studentModalId }}Label">{{ $exam->tieu_de }}</h5>
                        <div class="small opacity-75 mt-1">
                            {{ $exam->khoaHoc?->ma_khoa_hoc ?? 'KH' }} - {{ $exam->khoaHoc?->ten_khoa_hoc ?? 'Không rõ khóa học' }}
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6 col-lg-3">
                            <div class="score-modal-stat">
                                <div class="small text-muted fw-bold text-uppercase">Học viên</div>
                                <div class="fs-4 fw-bold">{{ $card['student_count'] }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="score-modal-stat">
                                <div class="small text-muted fw-bold text-uppercase">Lượt làm</div>
                                <div class="fs-4 fw-bold">{{ $card['attempt_count'] }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="score-modal-stat">
                                <div class="small text-muted fw-bold text-uppercase">Đã chấm</div>
                                <div class="fs-4 fw-bold text-success">{{ $card['graded_count'] }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="score-modal-stat">
                                <div class="small text-muted fw-bold text-uppercase">Điểm TB</div>
                                <div class="fs-4 fw-bold text-primary">{{ $card['average_score'] !== null ? number_format((float) $card['average_score'], 2) : '--' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Học viên</th>
                                        <th class="text-center">Lần làm</th>
                                        <th class="text-center">Điểm</th>
                                        <th>Trạng thái</th>
                                        <th>Nộp lúc</th>
                                        <th class="text-end pe-4">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($card['attempts'] as $baiLam)
                                        @php
                                            $studentName = $baiLam->hocVien->ho_ten ?? 'Không rõ học viên';
                                            $initial = mb_substr($studentName, 0, 1);
                                            $gradingStatus = $gradingStatusMap[$baiLam->trang_thai_cham] ?? ['label' => 'Không rõ', 'class' => 'secondary'];
                                        @endphp
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="score-modal-student-avatar">{{ $initial }}</div>
                                                    <div>
                                                        <div class="fw-bold">{{ $studentName }}</div>
                                                        <div class="small text-muted">{{ $baiLam->hocVien->email ?? 'Chưa có email' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge text-bg-light border">#{{ $baiLam->lan_lam_thu }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($baiLam->diem_so !== null)
                                                    <div class="fw-bold text-success fs-5">{{ number_format((float) $baiLam->diem_so, 2) }}</div>
                                                    <div class="small text-muted">/ {{ number_format((float) ($exam->tong_diem ?? 10), 2) }}</div>
                                                @else
                                                    <span class="badge text-bg-warning">Chờ chấm</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge text-bg-{{ $gradingStatus['class'] }}">{{ $gradingStatus['label'] }}</span>
                                                @if($exam->co_giam_sat)
                                                    <div class="small mt-2">
                                                        <span class="badge bg-{{ $baiLam->trang_thai_giam_sat_color }}">{{ $baiLam->trang_thai_giam_sat_label }}</span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="small fw-semibold">{{ optional($baiLam->nop_luc)->format('d/m/Y H:i') ?? 'Chưa nộp' }}</div>
                                                <div class="small text-muted">Bắt đầu: {{ optional($baiLam->bat_dau_luc)->format('d/m/Y H:i') ?? 'Không rõ' }}</div>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="{{ route('giang-vien.cham-diem.show', $baiLam->id) }}" class="btn btn-sm {{ $baiLam->need_manual_grading ? 'btn-primary' : 'btn-outline-primary' }}">
                                                    <i class="fas {{ $baiLam->need_manual_grading ? 'fa-marker' : 'fa-eye' }} me-1"></i>
                                                    {{ $baiLam->need_manual_grading ? 'Chấm bài' : 'Xem bài' }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
@endif
