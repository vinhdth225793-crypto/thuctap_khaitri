{{-- resources/views/components/banner-slider.blade.php --}}

@once
@push('styles')
<style>
    /* Banner Slide Styles */
    .carousel-container {
        max-width: 100%;
        margin: 0 auto;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .carousel-inner {
        min-height: 300px;
    }
    
    .carousel-item img {
        width: 100%;
        height: 350px;
        object-fit: cover;
    }
    
    .banner-images-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    
    .banner-image-item {
        position: relative;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .banner-image-item:hover {
        transform: scale(1.05);
    }
    
    .banner-image-item img {
        width: 100%;
        height: 120px;
        object-fit: cover;
    }
    
    .banner-delete-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(220, 53, 69, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        padding: 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        transition: background 0.3s;
    }
    
    .banner-delete-btn:hover {
        background: rgba(220, 53, 69, 1);
    }
    
    .banner-upload-area {
        border: 2px dashed #007bff;
        border-radius: 6px;
        padding: 30px;
        text-align: center;
        background-color: #f8f9ff;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    .banner-upload-area:hover {
        background-color: #e7eeff;
    }
    
    .banner-upload-area.dragover {
        background-color: #d4e0f7;
        border-color: #0056b3;
    }
</style>
@endpush
@endonce

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-images me-2"></i> Quản Lý Banner Slide</h5>
    </div>
    <div class="card-body">
        <form id="bannerForm" action="{{ route('admin.settings.contact.save') }}" method="POST">
            @csrf
            <input type="hidden" name="banners" id="bannersInput">
        </form>
        <!-- Preview Carousel -->
        <div class="mb-4">
            <label class="editor-label">Xem Trước Banner</label>
            <div id="bannerCarousel" class="carousel slide carousel-container" data-bs-ride="carousel">
                <div class="carousel-inner" id="carousel-inner">
                    <div class="carousel-item active">
                        <div style="width: 100%; height: 350px; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                            <span><i class="fas fa-image me-2"></i> Chưa có ảnh nào</span>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            </div>
        </div>

        <!-- Upload Area -->
        <div class="mb-4">
            <label class="editor-label">Tải Lên Ảnh Banner</label>
            <div class="banner-upload-area" id="bannerUploadArea">
                <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #007bff; margin-bottom: 10px; display: block;"></i>
                <p class="mb-0"><strong>Kéo và thả ảnh vào đây</strong></p>
                <small class="text-muted">hoặc click để chọn ảnh</small>
                <input type="file" id="bannerFileInput" accept="image/*" multiple style="display: none;">
            </div>
        </div>

        <!-- Existing Banners Grid -->
        <div>
            <label class="editor-label">Các Banner Hiện Tại</label>
            <div class="banner-images-grid" id="bannerImagesGrid">
                <!-- JavaScript sẽ fill nay -->
            </div>
        </div>

        <div class="mt-3">
            <button type="button" class="btn btn-success" id="saveBannersBtn">
                <i class="fas fa-save me-2"></i> Lưu Banner
            </button>
            <small class="text-muted d-block mt-2">
                <i class="fas fa-info-circle me-1"></i> Hỗ trợ định dạng: JPG, PNG, GIF. Tối đa 5MB/ảnh
            </small>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('banner-slider component loaded');
        // ==================== BANNER MANAGEMENT ====================
        // initialize banner array from server variable if provided
        let bannerImages = [];
        @if(isset($existingBanners) && is_array($existingBanners))
            bannerImages = @json($existingBanners);
        @endif
        const maxFileSize = 5 * 1024 * 1024; // 5MB
        const maxBanners = 10;

        // Initialize banner preview on page load
        function initBannerPreview() {
            const carouselInner = document.getElementById('carousel-inner');
            
            if (bannerImages.length === 0) {
                carouselInner.innerHTML = `
                    <div class="carousel-item active">
                        <div style="width: 100%; height: 350px; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                            <span><i class="fas fa-image me-2"></i> Chưa có ảnh nào</span>
                        </div>
                    </div>
                `;
            } else {
                carouselInner.innerHTML = bannerImages.map((img, index) => `
                    <div class="carousel-item ${index === 0 ? 'active' : ''}">
                        <img src="${img}" alt="Banner ${index + 1}">
                    </div>
                `).join('');
            }
            updateBannerGrid();
        }

        // Update banner grid display
        function updateBannerGrid() {
            const grid = document.getElementById('bannerImagesGrid');
            if (bannerImages.length === 0) {
                grid.innerHTML = '<p class="text-muted text-center w-100">Chưa có ảnh nào. Tải lên ảnh để tạo slide.</p>';
            } else {
                grid.innerHTML = bannerImages.map((img, index) => `
                    <div class="banner-image-item">
                        <img src="${img}" alt="Banner ${index + 1}">
                        <button type="button" class="banner-delete-btn" onclick="deleteBannerImage(${index})" title="Xóa ảnh">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                `).join('');
            }
        }

        // Delete banner image
        function deleteBannerImage(index) {
            if (confirm('Bạn có chắc muốn xóa ảnh này?')) {
                bannerImages.splice(index, 1);
                initBannerPreview();
            }
        }

        // Handle file selection
        document.getElementById('bannerFileInput').addEventListener('change', function(e) {
            handleBannerFiles(e.target.files);
            this.value = ''; // Reset input
        });

        // Handle drag and drop
        const uploadArea = document.getElementById('bannerUploadArea');
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            handleBannerFiles(e.dataTransfer.files);
        });

        uploadArea.addEventListener('click', () => {
            document.getElementById('bannerFileInput').click();
        });

        // Process uploaded files
        function handleBannerFiles(files) {
            if (bannerImages.length >= maxBanners) {
                alert(`Tối đa ${maxBanners} ảnh. Vui lòng xóa ảnh cũ trước khi thêm mới.`);
                return;
            }

            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) {
                    alert('Vui lòng chỉ chọn file ảnh');
                    return;
                }

                if (file.size > maxFileSize) {
                    alert(`Ảnh "${file.name}" quá lớn (tối đa 5MB)`);
                    return;
                }

                if (bannerImages.length >= maxBanners) {
                    alert(`Đã đạt giới hạn ${maxBanners} ảnh`);
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    bannerImages.push(e.target.result);
                    initBannerPreview();
                };
                reader.readAsDataURL(file);
            });
        }

        // Save banners
        document.getElementById('saveBannersBtn').addEventListener('click', function() {
            console.log('saveBannersBtn clicked, current count', bannerImages.length);
            if (bannerImages.length === 0) {
                alert('Vui lòng chọn ít nhất một ảnh');
                return;
            }

// submit the hidden form with JSON payload
        document.getElementById('bannersInput').value = JSON.stringify(bannerImages);
        document.getElementById('bannerForm').submit();
        });

        // Initialize preview after DOM fully loaded
        initBannerPreview();
    });
</script>
@endpush
@endonce