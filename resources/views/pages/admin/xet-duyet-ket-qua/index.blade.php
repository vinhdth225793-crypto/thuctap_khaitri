@extends('layouts.app', ['title' => 'Phieu xet duyet ket qua'])

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Phieu xet duyet ket qua</h2>
            <p class="text-muted mb-0">Duyet, tu choi va chot diem cuoi khoa do giang vien gui.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <div class="bg-white border rounded-3 px-3 py-2 text-center">
                <div class="small text-muted text-uppercase fw-bold">Cho duyet</div>
                <div class="fs-5 fw-bold text-warning">{{ $summary['submitted'] }}</div>
            </div>
            <div class="bg-white border rounded-3 px-3 py-2 text-center">
                <div class="small text-muted text-uppercase fw-bold">Dang xem</div>
                <div class="fs-5 fw-bold text-info">{{ $summary['reviewing'] }}</div>
            </div>
            <div class="bg-white border rounded-3 px-3 py-2 text-center">
                <div class="small text-muted text-uppercase fw-bold">Da duyet</div>
                <div class="fs-5 fw-bold text-primary">{{ $summary['approved'] }}</div>
            </div>
            <div class="bg-white border rounded-3 px-3 py-2 text-center">
                <div class="small text-muted text-uppercase fw-bold">Da chot</div>
                <div class="fs-5 fw-bold text-success">{{ $summary['finalized'] }}</div>
            </div>
        </div>
    </div>

    @include('components.alert')

    <div class="bg-white border rounded-3 p-3 mb-3">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-end">
            <div>
                <label class="form-label small fw-bold text-muted">Trang thai</label>
                <select name="trang_thai" class="form-select">
                    <option value="">Tat ca</option>
                    @foreach(['submitted' => 'Da gui', 'reviewing' => 'Dang xem', 'approved' => 'Da duyet', 'rejected' => 'Tu choi', 'finalized' => 'Da chot', 'draft' => 'Nhap'] as $value => $label)
                        <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary" type="submit">Loc</button>
            <a href="{{ route('admin.xet-duyet-ket-qua.index') }}" class="btn btn-outline-secondary">Xoa loc</a>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Phieu</th>
                        <th>Khoa hoc</th>
                        <th>Giang vien</th>
                        <th class="text-center">Hoc vien</th>
                        <th>Trang thai</th>
                        <th class="text-end pe-4">Thao tac</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">#{{ $ticket->id }}</div>
                                <div class="small text-muted">{{ $ticket->phuong_an_label }}</div>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $ticket->khoaHoc?->ten_khoa_hoc }}</div>
                                <div class="small text-muted">{{ $ticket->khoaHoc?->ma_khoa_hoc }}</div>
                            </td>
                            <td>
                                <div>{{ $ticket->nguoiLap?->ho_ten }}</div>
                                <div class="small text-muted">{{ $ticket->submitted_at?->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="text-center fw-bold">{{ $ticket->chi_tiets_count }}</td>
                            <td>
                                <span class="badge bg-{{ $ticket->trang_thai_color }}">{{ $ticket->trang_thai_label }}</span>
                                @if($ticket->reject_reason)
                                    <div class="small text-danger mt-1">{{ $ticket->reject_reason }}</div>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.xet-duyet-ket-qua.show', $ticket) }}" class="btn btn-sm btn-primary">
                                    Xem chi tiet
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">Chua co phieu xet duyet nao.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
            <div class="card-footer bg-white">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
