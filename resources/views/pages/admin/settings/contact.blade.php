@extends('layouts.app')

@section('title', 'Thông tin liên hệ - Cài đặt hệ thống')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-container {
        font-size: 1rem;
        min-height: 200px;
    }
    .ql-editor {
        min-height: 200px;
        background-color: #fff;
    }
    .editor-label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #333;
    }
</style>
@endpush

@section('content')
<div class="container">
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <strong><i class="fas fa-exclamation-circle me-2"></i> Có lỗi xảy ra!</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-phone me-2"></i> Thông tin liên hệ
                    </h4>
                </div>
                <div class="card-body">
                    <!-- site name form -->
                    <form action="{{ route('admin.settings.contact.save') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card mb-3">
                            <div class="card-header">Tên trang web</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">Nhập tên trang web</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name"
                                           placeholder="Tên hiển thị trên navbar" value="{{ $settings['site_name'] ?? '' }}">
                                </div>
                                <button type="submit" class="btn btn-primary">Lưu tên</button>
                            </div>
                        </div>
                    </form>

                    <!-- logo form -->
                    <form action="{{ route('admin.settings.contact.save') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card mb-3">
                            <div class="card-header">Logo trang web</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="site_logo" class="form-label">Logo hiện tại</label>
                                    <input type="file" class="form-control" id="site_logo" name="site_logo" accept="image/*">
                                    @if(!empty($settings['site_logo']))
                                        <div class="mt-2">
                                            <img src="{{ asset($settings['site_logo']) }}" alt="Logo hiện tại" style="max-height: 80px;">
                                        </div>
                                    @endif
                                </div>
                                <button type="submit" class="btn btn-primary">Lưu logo</button>
                            </div>
                        </div>
                    </form>

                    <!-- hotline form -->
                    <form action="{{ route('admin.settings.contact.save') }}" method="POST">
                        @csrf
                        <div class="card mb-3">
                            <div class="card-header">Hotline</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="site_hotline" class="form-label">Hotline hiện tại</label>
                                    <input type="tel" class="form-control" id="hotline" name="hotline"
                                           placeholder="Số điện thoại hotline" value="{{ $settings['hotline'] ?? '' }}">
                                </div>
                                <button type="submit" class="btn btn-primary">Lưu hotline</button>
                            </div>
                        </div>
                    </form>

                    <!-- email form -->
                    <form action="{{ route('admin.settings.contact.save') }}" method="POST">
                        @csrf
                        <div class="card mb-3">
                            <div class="card-header">Email</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="site_email" class="form-label">Email hiện tại</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           placeholder="Email chính" value="{{ $settings['email'] ?? '' }}">
                                </div>
                                <button type="submit" class="btn btn-primary">Lưu email</button>
                            </div>
                        </div>
                    </form>

                    <!-- address form -->
                    <form action="{{ route('admin.settings.contact.save') }}" method="POST">
                        @csrf
                        <div class="card mb-3">
                            <div class="card-header">Địa chỉ</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="editor-label">Địa chỉ hiện tại</label>
                                    <div id="address-editor" class="ql-editor" style="background-color: white;">
                                        {!! $settings['address'] ?? '' !!}
                                    </div>
                                    <input type="hidden" id="address" name="address">
                                </div>
                                <button type="submit" class="btn btn-primary">Lưu địa chỉ</button>
                            </div>
                        </div>
                    </form>

                    <!-- general notification content form -->
                    <form action="{{ route('admin.settings.contact.save') }}" method="POST">
                        @csrf
                        <div class="card mb-3">
                            <div class="card-header">Nội dung thông báo chung</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="editor-label">Nội dung thông báo chung hiện tại</label>
                                    <div id="general-notification-toolbar" class="ql-toolbar ql-snow"></div>
                                    <div id="general-notification-editor" class="ql-container ql-snow">
                                        <div class="ql-editor">
                                            {!! $settings['general_notification'] ?? '' !!}
                                        </div>
                                    </div>
                                    <input type="hidden" id="general_notification" name="general_notification">
                                </div>
                                <button type="submit" class="btn btn-primary">Lưu thông báo</button>
                            </div>
                        </div>
                    </form>

                    {{-- include banner slider component --}}

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.settings') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    // Initialize Quill editors
    const quillEditors = {};
    
    // Rich Text Editor cho Address
    quillEditors.address = new Quill('#address-editor', {
        theme: 'snow',
        placeholder: 'Nhập địa chỉ...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'header': 1 }, { 'header': 2 }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'font': [] }],
                [{ 'align': [] }],
                ['clean'],
                ['link', 'image']
            ]
        }
    });

    // Rich Text Editor cho General Notification
    quillEditors.generalNotification = new Quill('#general-notification-editor', {
        theme: 'snow',
        placeholder: 'Nhập nội dung thông báo chung...',
        modules: {
            toolbar: '#general-notification-toolbar'
        }
    });

    // Cấu hình toolbar cho General Notification
    const generalNotificationToolbar = document.querySelector('#general-notification-toolbar');
    generalNotificationToolbar.classList.add('ql-toolbar', 'ql-snow');
    const toolbarButtons = `
        <span class="ql-formats">
            <button class="ql-bold" title="Bold"></button>
            <button class="ql-italic" title="Italic"></button>
            <button class="ql-underline" title="Underline"></button>
            <button class="ql-strike" title="Strike"></button>
        </span>
        <span class="ql-formats">
            <select class="ql-header">
                <option selected>Paragraph</option>
                <option value="1">Heading 1</option>
                <option value="2">Heading 2</option>
                <option value="3">Heading 3</option>
            </select>
        </span>
        <span class="ql-formats">
            <button class="ql-blockquote" title="Blockquote"></button>
            <button class="ql-code-block" title="Code Block"></button>
        </span>
        <span class="ql-formats">
            <button class="ql-list" value="ordered" title="Ordered List"></button>
            <button class="ql-list" value="bullet" title="Bullet List"></button>
            <button class="ql-indent" value="-1" title="Decrease Indent"></button>
            <button class="ql-indent" value="+1" title="Increase Indent"></button>
        </span>
        <span class="ql-formats">
            <select class="ql-size">
                <option value="small">Small</option>
                <option selected>Normal</option>
                <option value="large">Large</option>
                <option value="huge">Huge</option>
            </select>
        </span>
        <span class="ql-formats">
            <select class="ql-color">
                <option selected></option>
                <option value="red">Red</option>
                <option value="blue">Blue</option>
                <option value="green">Green</option>
                <option value="orange">Orange</option>
                <option value="purple">Purple</option>
            </select>
            <select class="ql-background">
                <option selected></option>
                <option value="red">Red</option>
                <option value="blue">Blue</option>
                <option value="green">Green</option>
                <option value="yellow">Yellow</option>
            </select>
        </span>
        <span class="ql-formats">
            <button class="ql-link" title="Link"></button>
            <button class="ql-image" title="Image"></button>
        </span>
        <span class="ql-formats">
            <button class="ql-clean" title="Clear Format"></button>
        </span>
    `;
    
    generalNotificationToolbar.innerHTML = toolbarButtons;
    
    // Re-initialize Quill với toolbar
    quillEditors.generalNotification = new Quill('#general-notification-editor', {
        theme: 'snow',
        placeholder: 'Nhập nội dung thông báo chung...',
        modules: {
            toolbar: '#general-notification-toolbar'
        }
    });

    // Lắng nghe sự kiện submit của form
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            // Lấy tên input từ form
            const addressInput = this.querySelector('input[name="address"]');
            const notificationInput = this.querySelector('input[name="general_notification"]');

            // Gán dữ liệu từ editor vào input hidden
            if (addressInput) {
                addressInput.value = quillEditors.address.root.innerHTML;
            }
            if (notificationInput) {
                notificationInput.value = quillEditors.generalNotification.root.innerHTML;
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert-success').forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });


</script>
@endpush

@endsection