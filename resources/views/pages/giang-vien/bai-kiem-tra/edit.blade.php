@extends('layouts.app', ['title' => 'Cau hinh bai kiem tra'])

@section('content')
@php
    $selectedQuestionIds = $baiKiemTra->chiTietCauHois->pluck('ngan_hang_cau_hoi_id')->map(fn ($id) => (int) $id)->all();
    $scoreByQuestionId = $baiKiemTra->chiTietCauHois->mapWithKeys(fn ($item) => [$item->ngan_hang_cau_hoi_id => $item->diem_so]);
@endphp

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ $baiKiemTra->tieu_de }}</h2>
            <p class="text-muted mb-0">{{ $baiKiemTra->khoaHoc->ten_khoa_hoc ?? 'Khong ro khoa hoc' }} • {{ $baiKiemTra->moduleHoc->ten_module ?? 'De cuoi khoa / dung chung' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('giang-vien.khoa-hoc.show', $baiKiemTra->lich_hoc_id ? $baiKiemTra->lichHoc->module_hoc_id : ($baiKiemTra->module_hoc_id ?? 0)) }}" class="btn btn-outline-primary">Quay lai</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card vip-card h-100">
                <div class="card-body">
                    <div class="mb-3"><strong>Loai bai:</strong> {{ $baiKiemTra->loai_bai_kiem_tra_label }}</div>
                    <div class="mb-3"><strong>Trang thai duyet:</strong> {{ $baiKiemTra->trang_thai_duyet_label }}</div>
                    <div class="mb-3"><strong>Trang thai phat hanh:</strong> {{ $baiKiemTra->trang_thai_phat_hanh_label }}</div>
                    <div class="mb-3"><strong>Tong diem hien tai:</strong> {{ number_format((float) $baiKiemTra->tong_diem, 2) }}</div>
                    <div class="mb-4"><strong>Cau hoi da chon:</strong> {{ $baiKiemTra->chiTietCauHois->count() }}</div>

                    <form action="{{ route('giang-vien.bai-kiem-tra.submit', $baiKiemTra->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">Gui admin duyet</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card vip-card mb-4">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Thong tin bai kiem tra</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('giang-vien.bai-kiem-tra.update', $baiKiemTra->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Tieu de</label>
                                <input type="text" name="tieu_de" value="{{ old('tieu_de', $baiKiemTra->tieu_de) }}" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Thoi gian lam bai</label>
                                <input type="number" min="1" max="300" name="thoi_gian_lam_bai" value="{{ old('thoi_gian_lam_bai', $baiKiemTra->thoi_gian_lam_bai) }}" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ngay mo</label>
                                <input type="datetime-local" name="ngay_mo" value="{{ old('ngay_mo', optional($baiKiemTra->ngay_mo)->format('Y-m-d\\TH:i')) }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ngay dong</label>
                                <input type="datetime-local" name="ngay_dong" value="{{ old('ngay_dong', optional($baiKiemTra->ngay_dong)->format('Y-m-d\\TH:i')) }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">So lan duoc lam</label>
                                <input type="number" min="1" max="10" name="so_lan_duoc_lam" value="{{ old('so_lan_duoc_lam', $baiKiemTra->so_lan_duoc_lam) }}" class="form-control" required>
                            </div>
                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check mt-4">
                                    <input type="checkbox" name="randomize_questions" value="1" class="form-check-input" @checked(old('randomize_questions', $baiKiemTra->randomize_questions))>
                                    <label class="form-check-label">Xao tron thu tu cau hoi</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Mo ta / huong dan</label>
                                <textarea name="mo_ta" rows="4" class="form-control">{{ old('mo_ta', $baiKiemTra->mo_ta) }}</textarea>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-semibold">Chon cau hoi cho de</h5>
                            <span class="badge bg-light text-dark">{{ $availableQuestions->count() }} cau hoi kha dung</span>
                        </div>

                        @forelse($availableQuestions as $question)
                            @php
                                $questionId = (int) $question->id;
                                $isSelected = in_array($questionId, old('question_ids', $selectedQuestionIds), true);
                                $scoreValue = old('question_scores.' . $questionId, $scoreByQuestionId[$questionId] ?? $question->diem_mac_dinh);
                            @endphp
                            <div class="border rounded-3 p-3 mb-3">
                                <div class="d-flex flex-wrap justify-content-between gap-3 mb-2">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="question-{{ $questionId }}" name="question_ids[]" value="{{ $questionId }}" @checked($isSelected)>
                                        <label class="form-check-label fw-semibold" for="question-{{ $questionId }}">
                                            [{{ $question->ma_cau_hoi }}] {{ \Illuminate\Support\Str::limit(strip_tags($question->noi_dung), 160) }}
                                        </label>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-light text-dark">{{ $question->loai_cau_hoi_label }}</span>
                                        <span class="badge bg-light text-dark">{{ $question->muc_do_label }}</span>
                                    </div>
                                </div>
                                <div class="row g-3 align-items-start">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Diem cau nay</label>
                                        <input type="number" step="0.25" min="0.25" name="question_scores[{{ $questionId }}]" value="{{ $scoreValue }}" class="form-control">
                                    </div>
                                    <div class="col-md-9">
                                        @if($question->loai_cau_hoi === 'trac_nghiem')
                                            <div class="small text-muted">
                                                @foreach($question->dapAns as $dapAn)
                                                    <div>{{ $dapAn->ky_hieu }}. {{ $dapAn->noi_dung }} @if($dapAn->is_dap_an_dung)<strong class="text-success">(Dung)</strong>@endif</div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="small text-muted">{{ $question->goi_y_tra_loi ?: 'Cau tu luan, giang vien se cham tay sau khi hoc vien nop bai.' }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-warning">Chua co cau hoi san sang trong ngan hang cho pham vi bai kiem tra nay.</div>
                        @endforelse

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Luu cau hinh de</button>
                            <a href="{{ route('giang-vien.cham-diem.index') }}" class="btn btn-light border">Sang khu vuc cham diem</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card vip-card">
                <div class="card-header border-0">
                    <h5 class="mb-0 fw-semibold">Bai lam gan day</h5>
                </div>
                <div class="card-body">
                    @forelse($baiKiemTra->baiLams as $baiLam)
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="fw-semibold">{{ $baiLam->hocVien->ho_ten ?? 'Hoc vien' }}</div>
                            <div class="small text-muted">Lan {{ $baiLam->lan_lam_thu }} • {{ $baiLam->trang_thai_label }} • {{ $baiLam->nop_luc?->format('d/m/Y H:i') ?? 'Chua nop' }}</div>
                            <div class="small text-muted">Diem: {{ $baiLam->diem_so !== null ? number_format((float) $baiLam->diem_so, 2) : 'Chua cham' }}</div>
                        </div>
                    @empty
                        <div class="text-muted">Chua co bai lam nao cho de nay.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
