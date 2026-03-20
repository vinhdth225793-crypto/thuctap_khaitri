@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 text-muted small mb-2">
            <i class="fas fa-home me-1"></i> Admin > Ngân hàng câu hỏi > {{ $title }}
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="fas fa-edit me-2"></i>{{ $title }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ $action }}" method="POST">
                        @csrf
                        @if($method === 'PUT')
                            @method('PUT')
                        @endif

                        <div class="mb-4">
                            <label class="form-label fw-bold">Khóa học <span class="text-danger">*</span></label>
                            <select name="khoa_hoc_id" class="form-select @error('khoa_hoc_id') is-invalid @enderror" required>
                                <option value="">--- Chọn khóa học ---</option>
                                @foreach($khoaHocs as $kh)
                                    <option value="{{ $kh->id }}" {{ (old('khoa_hoc_id', $cauHoi->khoa_hoc_id) == $kh->id) ? 'selected' : '' }}>
                                        [{{ $kh->ma_khoa_hoc }}] {{ $kh->ten_khoa_hoc }}
                                    </option>
                                @endforeach
                            </select>
                            @error('khoa_hoc_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Nội dung câu hỏi <span class="text-danger">*</span></label>
                            <textarea name="noi_dung_cau_hoi" class="form-control @error('noi_dung_cau_hoi') is-invalid @enderror" rows="4" placeholder="Nhập nội dung câu hỏi..." required>{{ old('noi_dung_cau_hoi', $cauHoi->noi_dung_cau_hoi) }}</textarea>
                            @error('noi_dung_cau_hoi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text mt-2 text-warning italic">
                                <i class="fas fa-info-circle me-1"></i>Hệ thống sẽ kiểm tra trùng lặp nội dung câu hỏi trong cùng khóa học.
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold text-success">Đáp án ĐÚNG <span class="text-danger">*</span></label>
                                <input type="text" name="dap_an_dung" class="form-control border-success @error('dap_an_dung') is-invalid @enderror" value="{{ old('dap_an_dung', $cauHoi->dap_an_dung) }}" placeholder="Nhập đáp án đúng..." required>
                                @error('dap_an_dung')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <label class="form-label fw-bold text-danger">Các đáp án SAI (Phải nhập đủ 3 đáp án khác nhau) <span class="text-danger">*</span></label>
                            <div class="col-12">
                                <div class="input-group">
                                    <span class="input-group-text text-danger">Sai 1</span>
                                    <input type="text" name="dap_an_sai_1" class="form-control @error('dap_an_sai_1') is-invalid @enderror" value="{{ old('dap_an_sai_1', $cauHoi->dap_an_sai_1) }}" placeholder="Đáp án sai thứ nhất..." required>
                                    @error('dap_an_sai_1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="input-group">
                                    <span class="input-group-text text-danger">Sai 2</span>
                                    <input type="text" name="dap_an_sai_2" class="form-control @error('dap_an_sai_2') is-invalid @enderror" value="{{ old('dap_an_sai_2', $cauHoi->dap_an_sai_2) }}" placeholder="Đáp án sai thứ hai..." required>
                                    @error('dap_an_sai_2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="input-group">
                                    <span class="input-group-text text-danger">Sai 3</span>
                                    <input type="text" name="dap_an_sai_3" class="form-control @error('dap_an_sai_3') is-invalid @enderror" value="{{ old('dap_an_sai_3', $cauHoi->dap_an_sai_3) }}" placeholder="Đáp án sai thứ ba..." required>
                                    @error('dap_an_sai_3')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.kiem-tra-online.cau-hoi.index') }}" class="btn btn-light border">
                                <i class="fas fa-arrow-left me-1"></i>Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i>Lưu câu hỏi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
