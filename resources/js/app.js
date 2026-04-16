// app.js
import './bootstrap';

class VIPApp {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initComponents();
        this.setupGlobalHandlers();
        this.checkDarkMode();
    }

    setupEventListeners() {
        // Back to top button
        const backToTop = document.getElementById('backToTop');
        if (backToTop) {
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    backToTop.classList.add('show');
                } else {
                    backToTop.classList.remove('show');
                }
            });

            backToTop.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', () => {
            this.setupFormValidation();
            this.setupPasswordToggle();
            this.setupFileUpload();
        });
    }

    initComponents() {
        // Initialize tooltips
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

            // Initialize popovers
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
        }

        // Setup animations
        this.setupAnimations();
    }

    setupGlobalHandlers() {
        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                if (!alert.classList.contains('alert-permanent')) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            });
        }, 5000);

        // Close alerts on click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-close')) {
                e.target.closest('.alert').remove();
            }
        });

        // Prevent form resubmission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    }

    setupFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }

    setupPasswordToggle() {
        document.querySelectorAll('.password-toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    }

    setupFileUpload() {
        document.querySelectorAll('.file-upload').forEach(upload => {
            const input = upload.querySelector('input[type="file"]');
            const label = upload.querySelector('.file-upload-label');
            const preview = upload.querySelector('.file-preview');

            input.addEventListener('change', function(e) {
                const fileName = this.files[0]?.name || 'Chưa chọn file';
                label.textContent = fileName;

                if (preview && this.files[0]) {
                    if (this.files[0].type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.src = e.target.result;
                            preview.style.display = 'block';
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                }
            });
        });
    }

    setupAnimations() {
        // Intersection Observer for scroll animations
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe elements with data-animate attribute
        document.querySelectorAll('[data-animate]').forEach(el => observer.observe(el));
    }

    checkDarkMode() {
        // Check for dark mode preference
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
        const darkModeToggle = document.getElementById('darkModeToggle');

        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', () => {
                document.body.classList.toggle('dark-mode');
                localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
            });

            // Check local storage for saved preference
            const savedDarkMode = localStorage.getItem('darkMode');
            if (savedDarkMode === 'true') {
                document.body.classList.add('dark-mode');
            }
        }
    }

    // Utility methods
    static showNotification(type, message, duration = 5000) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show vip-alert animate__animated animate__slideInDown" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        const container = document.getElementById('alert-container') || document.body;
        const alert = document.createElement('div');
        alert.innerHTML = alertHtml;
        container.prepend(alert.firstElementChild);

        setTimeout(() => {
            const alertElement = container.querySelector('.alert');
            if (alertElement) {
                alertElement.style.opacity = '0';
                setTimeout(() => alertElement.remove(), 500);
            }
        }, duration);
    }

    static formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('vi-VN', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    static formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    static debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    static throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
}

// Initialize the app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.VIPApp = new VIPApp();
    
    // GSAP animations
    if (typeof gsap !== 'undefined') {
        // Animate header on scroll
        gsap.to('.navbar', {
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
            backdropFilter: 'blur(10px)',
            duration: 0.3,
            scrollTrigger: {
                trigger: 'body',
                start: 'top -100',
                end: 'bottom -100',
                scrub: true
            }
        });
    }
});

// API Helper Class
class API {
    static async request(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin'
        };

        const response = await fetch(url, { ...defaultOptions, ...options });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        }

        return response.text();
    }

    static get(url) {
        return this.request(url, { method: 'GET' });
    }

    static post(url, data) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    static put(url, data) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    static delete(url) {
        return this.request(url, { method: 'DELETE' });
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { VIPApp, API };
}

// Global error handling
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    VIPApp.showNotification('danger', 'Đã xảy ra lỗi. Vui lòng thử lại sau.');
});

// Offline/Online detection
window.addEventListener('online', () => {
    VIPApp.showNotification('success', 'Kết nối internet đã được khôi phục');
});

window.addEventListener('offline', () => {
    VIPApp.showNotification('warning', 'Mất kết nối internet. Vui lòng kiểm tra lại');
});

// Service Worker Registration (if supported)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
            .catch(() => {});
    });
}
