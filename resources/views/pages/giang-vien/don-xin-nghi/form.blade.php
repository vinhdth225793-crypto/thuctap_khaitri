@extends('layouts.app')

@section('title', 'Gửi đơn xin nghỉ')

@php
    use App\Support\Scheduling\TeachingPeriodCatalog;
    $periodDefinitions = TeachingPeriodCatalog::periods();
    $sessionOptions = TeachingPeriodCatalog::sessions();
@endphp

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small text-muted text-uppercase fw-bold mb-1">Leave Request</div>
            <h4 class="fw-bold mb-1">Gửi đơn xin nghỉ</h4>
            <div class="text-muted">Bạn có thể gắn trực tiếp vào một buổi học đã sắp hoặc xin off theo ngày và tiết.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('giang-vien.don-xin-nghi.index') }}" class="btn btn-outline-secondary">Danh sách đơn</a>
            <a href="{{ route('giang-vien.lich-giang.index') }}" class="btn btn-outline-primary">Về lịch dạy</a>
        </div>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('giang-vien.don-xin-nghi.store') }}" class="row g-4">
                @csrf

                <div class="col-12">
                    <label class="form-label small fw-bold">Buổi học đã được sắp</label>
                    <select name="lich_hoc_id" class="form-select @error('lich_hoc_id') is-invalid @enderror">
                        <option value="">-- Không gắn buổi học cu the --</option>
                        @foreach($upcomingSchedules as $schedule)
                            <option value="{{ $schedule->id }}" @selected(old('lich_hoc_id', $selectedSchedule?->id) == $schedule->id)>
                                {{ $schedule->ngay_hoc?->format('d/m/Y') }} | {{ $schedule->schedule_range_label }} | {{ $schedule->khoaHoc?->ma_khoa_hoc }} - {{ $schedule->moduleHoc?->ten_module }}
                            </option>
                        @endforeach
                    </select>
                    @error('lich_hoc_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Ngày xin nghỉ</label>
                    <input type="date" name="ngay_xin_nghi" class="form-control @error('ngay_xin_nghi') is-invalid @enderror" value="{{ old('ngay_xin_nghi', $selectedSchedule?->ngay_hoc?->toDateString()) }}">
                    @error('ngay_xin_nghi') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold">Xin nghỉ theo buổi</label>
                    <select name="buoi_hoc" class="form-select @error('buoi_hoc') is-invalid @enderror">
                        <option value="">-- Chọn ca dạy --</option>
                        @foreach($sessionOptions as $key => $session)
                            <option value="{{ $key }}" @selected(old('buoi_hoc', $selectedSchedule?->buoi_hoc) === $key)>{{ $session['label'] }}</option>
                        @endforeach
                    </select>
                    @error('buoi_hoc') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold">Tiết bắt đầu</label>
                    <select name="tiet_bat_dau" class="form-select @error('tiet_bat_dau') is-invalid @enderror">
                        <option value="">--</option>
                        @foreach($periodDefinitions as $period => $definition)
                            <option value="{{ $period }}" @selected((string) old('tiet_bat_dau', $selectedSchedule?->tiet_bat_dau) === (string) $period)>Tiết {{ $period }}</option>
                        @endforeach
                    </select>
                    @error('tiet_bat_dau') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold">Tiết kết thúc</label>
                    <select name="tiet_ket_thuc" class="form-select @error('tiet_ket_thuc') is-invalid @enderror">
                        <option value="">--</option>
                        @foreach($periodDefinitions as $period => $definition)
                            <option value="{{ $period }}" @selected((string) old('tiet_ket_thuc', $selectedSchedule?->tiet_ket_thuc) === (string) $period)>Tiết {{ $period }}</option>
                        @endforeach
                    </select>
                    @error('tiet_ket_thuc') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <div class="border rounded-3 p-3 bg-light small text-muted">
                        Nếu bạn chọn buổi học, hệ thống tự động suy ra khoảng tiết chuẩn. Nếu bạn muốn xin off một phần buổi, hãy chọn tiết bắt đầu và tiết kết thúc.
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-bold">Lý do</label>
                    <textarea name="ly_do" rows="5" class="form-control @error('ly_do') is-invalid @enderror" placeholder="Mô tả rõ lý do bạn cần xin nghỉ và thông tin cần admin lưu ý...">{{ old('ly_do') }}</textarea>
                    @error('ly_do') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 d-flex gap-2 justify-content-end">
                    <a href="{{ route('giang-vien.don-xin-nghi.index') }}" class="btn btn-light border px-4">Huy</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Gửi đơn</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
