@php
    $availabilityOverview = $availabilityOverview ?? [
        'weekly' => [],
        'specific' => [],
        'active_count' => 0,
    ];

    $weeklyGrouped = collect($availabilityOverview['weekly'] ?? [])->groupBy('label');
    $specificSlots = collect($availabilityOverview['specific'] ?? []);
@endphp

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="mb-1 fw-bold">Tong quan theo tuan</h5>
                    <div class="text-muted small">Nhin nhanh cac khung gio lap lai dang hoat dong trong tuan.</div>
                </div>
                <span class="badge rounded-pill bg-light text-dark border">
                    {{ $availabilityOverview['active_count'] ?? 0 }} khung dang hoat dong
                </span>
            </div>
            <div class="card-body pt-0">
                <div class="row g-3">
                    @foreach(\App\Models\LichHoc::$thuLabels as $label)
                        <div class="col-md-6 col-xl-4">
                            <div class="border rounded-3 bg-light p-3 h-100">
                                <div class="small text-uppercase fw-bold text-muted mb-2">{{ $label }}</div>
                                @forelse($weeklyGrouped->get($label, []) as $slot)
                                    <div class="border rounded-3 bg-white p-2 mb-2">
                                        <div class="fw-bold text-dark">{{ $slot['schedule'] ?? $slot['time'] }}</div>
                                        <div class="small text-muted">{{ $slot['time'] }}</div>
                                        @if(filled($slot['note'] ?? null))
                                            <div class="small text-muted mt-1">{{ $slot['note'] }}</div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="small text-muted">Chua khai bao khung gio lap lai.</div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="mb-1 fw-bold">Slot theo ngay sap toi</h5>
                <div class="text-muted small">Cac khung gio cu the sap dien ra, uu tien cao khi admin sap lich.</div>
            </div>
            <div class="card-body pt-0">
                @forelse($specificSlots as $slot)
                    <div class="border rounded-3 p-3 mb-3 bg-light">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="fw-bold text-dark">{{ $slot['label'] }}</div>
                            <span class="badge bg-white text-dark border">{{ $slot['schedule'] ?? $slot['time'] }}</span>
                        </div>
                        <div class="small text-muted mt-2">{{ $slot['time'] }}</div>
                        @if(filled($slot['note'] ?? null))
                            <div class="small text-muted mt-2">{{ $slot['note'] }}</div>
                        @endif
                    </div>
                @empty
                    <div class="border rounded-3 p-4 bg-light text-center text-muted small">
                        Chua co khung gio theo ngay nao sap toi.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
