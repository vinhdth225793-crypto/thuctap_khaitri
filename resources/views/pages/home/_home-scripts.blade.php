@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const menuButton = document.getElementById('menuButton');
        const siteNav = document.getElementById('siteNav');
        const headerActions = document.getElementById('headerActions');
        const siteHeader = document.getElementById('siteHeader');
        const navLinks = Array.from(document.querySelectorAll('[data-scroll-link]'));
        const sections = navLinks
            .map((link) => document.getElementById(link.dataset.scrollLink))
            .filter(Boolean);

        if (menuButton) {
            menuButton.addEventListener('click', function () {
                const isOpen = siteNav?.classList.toggle('is-open') ?? false;
                headerActions?.classList.toggle('is-open', isOpen);
                menuButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }

        // ===== Live search + suggest dropdown =====
        const searchWrap = document.getElementById('headerSearchWrap');
        const searchToggle = document.getElementById('headerSearchToggle');
        const searchPanel = document.getElementById('headerSearchPanel');
        const searchInput = document.getElementById('headerSearchInput');
        const searchSuggest = document.getElementById('headerSearchSuggest');
        const searchSuggestStatus = document.getElementById('searchSuggestStatus');
        const searchSuggestBody = document.getElementById('searchSuggestBody');

        const closeSearch = () => {
            searchPanel?.setAttribute('hidden', '');
            searchSuggest?.setAttribute('hidden', '');
            searchToggle?.setAttribute('aria-expanded', 'false');
        };

        if (searchToggle && searchPanel) {
            searchToggle.addEventListener('click', function () {
                const isHidden = searchPanel.hasAttribute('hidden');
                if (isHidden) {
                    searchPanel.removeAttribute('hidden');
                    searchToggle.setAttribute('aria-expanded', 'true');
                    searchInput?.focus();
                    if (searchInput?.value.trim().length >= 2) {
                        searchSuggest?.removeAttribute('hidden');
                    }
                } else {
                    closeSearch();
                }
            });
            document.addEventListener('click', function (event) {
                if (searchWrap && !searchWrap.contains(event.target)) {
                    closeSearch();
                }
            });
        }

        let searchTimer = null;
        let searchAbort = null;

        const renderSuggest = (data) => {
            if (!searchSuggestBody || !searchSuggestStatus) return;

            const total = (data.courses?.length || 0) + (data.instructors?.length || 0);
            if (total === 0) {
                searchSuggestStatus.style.display = 'none';
                searchSuggestBody.innerHTML = `
                    <div class="search-suggest-empty">
                        <i class="fas fa-magnifying-glass"></i>
                        Không tìm thấy kết quả phù hợp với "<strong>${escapeHtml(data.q)}</strong>"
                    </div>`;
                return;
            }

            searchSuggestStatus.style.display = 'none';
            let html = '';

            if (data.courses?.length) {
                html += `<div class="search-suggest-section"><h4><i class="fas fa-book-open"></i> Khóa học (${data.courses.length})</h4>`;
                data.courses.forEach(c => {
                    html += `<a class="suggest-row" href="${escapeHtml(c.url)}">
                        <img src="${escapeHtml(c.image)}" alt="">
                        <span class="suggest-copy">
                            <strong>${escapeHtml(c.title)}</strong>
                            <small><span class="suggest-tag">${escapeHtml(c.level_label)}</span> ${escapeHtml(c.category)} · ${escapeHtml(c.code)}</small>
                        </span>
                    </a>`;
                });
                html += `</div>`;
            }

            if (data.instructors?.length) {
                html += `<div class="search-suggest-section"><h4><i class="fas fa-chalkboard-user"></i> Giảng viên (${data.instructors.length})</h4>`;
                data.instructors.forEach(g => {
                    const avatarHtml = g.avatar
                        ? `<img src="${escapeHtml(g.avatar)}" alt="">`
                        : `<span class="suggest-init">${escapeHtml(g.initial)}</span>`;
                    html += `<a class="suggest-row" href="#instructors">
                        ${avatarHtml}
                        <span class="suggest-copy">
                            <strong>${escapeHtml(g.name)}</strong>
                            <small>${escapeHtml(g.degree)} · ${escapeHtml(g.specialty)}</small>
                        </span>
                    </a>`;
                });
                html += `</div>`;
            }

            searchSuggestBody.innerHTML = html;
        };

        const escapeHtml = (str) => String(str ?? '').replace(/[&<>"']/g, s => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[s]));

        if (searchInput && searchSuggest) {
            searchInput.addEventListener('input', function () {
                const q = searchInput.value.trim();
                clearTimeout(searchTimer);

                if (q.length < 2) {
                    searchSuggestStatus.style.display = '';
                    searchSuggestBody.innerHTML = '';
                    searchSuggest.removeAttribute('hidden');
                    return;
                }

                searchSuggestStatus.style.display = '';
                searchSuggestStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Đang tìm...</span>';
                searchSuggest.removeAttribute('hidden');

                searchTimer = setTimeout(() => {
                    if (searchAbort) searchAbort.abort();
                    searchAbort = new AbortController();

                    fetch(`{{ route('api.search-suggest') }}?q=${encodeURIComponent(q)}`, { signal: searchAbort.signal })
                        .then(r => r.json())
                        .then(renderSuggest)
                        .catch(err => {
                            if (err.name !== 'AbortError') {
                                searchSuggestStatus.innerHTML = '<i class="fas fa-triangle-exclamation"></i> <span>Không tải được kết quả</span>';
                            }
                        });
                }, 240);
            });

            searchInput.addEventListener('focus', () => {
                if (searchInput.value.trim().length >= 2) {
                    searchSuggest.removeAttribute('hidden');
                }
            });
        }

        // ===== Notification bell =====
        const notifWrap = document.getElementById('headerNotifWrap');
        const notifToggle = document.getElementById('headerNotifToggle');
        const notifPopover = document.getElementById('headerNotifPopover');
        const notifBody = document.getElementById('headerNotifBody');
        const notifBadge = document.getElementById('headerNotifBadge');
        let notifLoaded = false;

        const fetchNotifications = () => {
            if (!notifToggle) return;
            const url = notifToggle.dataset.notifUrl;
            if (!url) return;

            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    if (notifBadge) {
                        if (data.unread > 0) {
                            notifBadge.textContent = data.unread > 99 ? '99+' : data.unread;
                            notifBadge.removeAttribute('hidden');
                        } else {
                            notifBadge.setAttribute('hidden', '');
                        }
                    }

                    if (!notifBody) return;
                    if (!data.items || data.items.length === 0) {
                        notifBody.innerHTML = `<div class="notif-empty">
                            <i class="fas fa-bell-slash"></i>
                            Chưa có thông báo nào
                        </div>`;
                        return;
                    }

                    const iconMap = {
                        he_thong: 'fa-cog', khoa_hoc: 'fa-book-open', lich_hoc: 'fa-calendar',
                        bai_kiem_tra: 'fa-clipboard-list', diem_danh: 'fa-user-check',
                    };

                    notifBody.innerHTML = data.items.map(item => {
                        const ico = iconMap[item.type] || 'fa-bell';
                        return `<a class="notif-item ${item.is_read ? '' : 'is-unread'}" href="${escapeHtml(item.url)}">
                            <span class="notif-icon-wrap"><i class="fas ${ico}"></i></span>
                            <div class="notif-content">
                                <strong>${escapeHtml(item.title)}</strong>
                                <p>${escapeHtml(item.preview)}</p>
                                <time>${escapeHtml(item.time_ago)}</time>
                            </div>
                        </a>`;
                    }).join('');
                })
                .catch(() => {
                    if (notifBody) notifBody.innerHTML = '<div class="notif-empty"><i class="fas fa-triangle-exclamation"></i> Không tải được thông báo</div>';
                });
        };

        if (notifToggle && notifPopover) {
            // Load badge ngay khi page load
            fetchNotifications();

            notifToggle.addEventListener('click', function (event) {
                event.stopPropagation();
                const isHidden = notifPopover.hasAttribute('hidden');
                if (isHidden) {
                    notifPopover.removeAttribute('hidden');
                    notifToggle.setAttribute('aria-expanded', 'true');
                    if (!notifLoaded) {
                        fetchNotifications();
                        notifLoaded = true;
                    }
                    // đóng user popover nếu đang mở
                    document.getElementById('headerUserPopover')?.setAttribute('hidden', '');
                    document.getElementById('headerUserWrap')?.classList.remove('is-open');
                } else {
                    notifPopover.setAttribute('hidden', '');
                    notifToggle.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('click', function (event) {
                if (notifWrap && !notifWrap.contains(event.target)) {
                    notifPopover.setAttribute('hidden', '');
                    notifToggle.setAttribute('aria-expanded', 'false');
                }
            });

            // Refresh badge mỗi 60 giây
            setInterval(fetchNotifications, 60000);
        }

        // ===== User avatar dropdown =====
        const userWrap = document.getElementById('headerUserWrap');
        const userToggle = document.getElementById('headerUserToggle');
        const userPopover = document.getElementById('headerUserPopover');

        if (userToggle && userPopover && userWrap) {
            userToggle.addEventListener('click', function (event) {
                event.stopPropagation();
                const isHidden = userPopover.hasAttribute('hidden');
                if (isHidden) {
                    userPopover.removeAttribute('hidden');
                    userWrap.classList.add('is-open');
                    userToggle.setAttribute('aria-expanded', 'true');
                    // đóng notif popover nếu đang mở
                    document.getElementById('headerNotifPopover')?.setAttribute('hidden', '');
                } else {
                    userPopover.setAttribute('hidden', '');
                    userWrap.classList.remove('is-open');
                    userToggle.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('click', function (event) {
                if (!userWrap.contains(event.target)) {
                    userPopover.setAttribute('hidden', '');
                    userWrap.classList.remove('is-open');
                    userToggle.setAttribute('aria-expanded', 'false');
                }
            });
        }

        // ===== Scroll progress bar =====
        const progressBar = document.getElementById('siteProgressBar');
        if (progressBar) {
            const updateProgress = () => {
                const scrollTop = window.scrollY || document.documentElement.scrollTop;
                const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const pct = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
                progressBar.style.width = `${Math.min(100, pct)}%`;
            };
            window.addEventListener('scroll', updateProgress, { passive: true });
            updateProgress();
        }

        // ===== Animated stats counter =====
        const counters = document.querySelectorAll('[data-counter]');
        if (counters.length && 'IntersectionObserver' in window) {
            const animateCounter = (el) => {
                const target = parseInt(el.dataset.counter, 10) || 0;
                if (target === 0) {
                    el.textContent = '0';
                    return;
                }
                const duration = 1400;
                const start = performance.now();
                const formatter = new Intl.NumberFormat('vi-VN');

                const tick = (now) => {
                    const progress = Math.min(1, (now - start) / duration);
                    // ease-out cubic
                    const eased = 1 - Math.pow(1 - progress, 3);
                    const current = Math.round(target * eased);
                    el.textContent = formatter.format(current);
                    if (progress < 1) requestAnimationFrame(tick);
                    else el.textContent = formatter.format(target);
                };
                requestAnimationFrame(tick);
            };

            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting && !entry.target.dataset.counted) {
                        entry.target.dataset.counted = '1';
                        animateCounter(entry.target);
                    }
                });
            }, { threshold: 0.4 });

            counters.forEach((el) => counterObserver.observe(el));
        } else if (counters.length) {
            counters.forEach((el) => {
                el.textContent = new Intl.NumberFormat('vi-VN').format(parseInt(el.dataset.counter, 10) || 0);
            });
        }

        const setActiveLink = function (sectionId) {
            navLinks.forEach((link) => {
                const isActive = link.dataset.scrollLink === sectionId;
                link.classList.toggle('is-active', isActive);
                if (isActive) {
                    link.setAttribute('aria-current', 'true');
                } else {
                    link.removeAttribute('aria-current');
                }
            });
        };

        const syncNavState = function () {
            siteHeader?.classList.toggle('is-scrolled', window.scrollY > 12);

            let activeSection = navLinks[0]?.dataset.scrollLink;
            const triggerLine = window.innerHeight * 0.36;

            sections.forEach((section) => {
                const rect = section.getBoundingClientRect();
                if (rect.top <= triggerLine && rect.bottom > 120) {
                    activeSection = section.id;
                }
            });

            if (activeSection) {
                setActiveLink(activeSection);
            }
        };

        navLinks.forEach((link) => {
            link.addEventListener('click', function () {
                siteNav?.classList.remove('is-open');
                headerActions?.classList.remove('is-open');
                menuButton?.setAttribute('aria-expanded', 'false');
            });
        });

        const bannerSlider = document.querySelector('[data-banner-slider]');
        if (bannerSlider) {
            const slides = Array.from(bannerSlider.querySelectorAll('[data-banner-slide]'));
            const dots = Array.from(bannerSlider.querySelectorAll('[data-banner-dot]'));
            const prevButton = bannerSlider.querySelector('[data-banner-prev]');
            const nextButton = bannerSlider.querySelector('[data-banner-next]');
            let activeIndex = 0;
            let slideTimer = null;

            const showSlide = function (index) {
                if (!slides.length) {
                    return;
                }

                activeIndex = (index + slides.length) % slides.length;
                slides.forEach((slide, slideIndex) => {
                    slide.classList.toggle('is-active', slideIndex === activeIndex);
                });
                dots.forEach((dot, dotIndex) => {
                    dot.classList.toggle('is-active', dotIndex === activeIndex);
                });
            };

            const startSlider = function () {
                if (slides.length < 2) {
                    return;
                }

                slideTimer = window.setInterval(() => {
                    showSlide(activeIndex + 1);
                }, 4500);
            };

            const resetSlider = function () {
                if (slideTimer) {
                    window.clearInterval(slideTimer);
                }
                startSlider();
            };

            prevButton?.addEventListener('click', function () {
                showSlide(activeIndex - 1);
                resetSlider();
            });

            nextButton?.addEventListener('click', function () {
                showSlide(activeIndex + 1);
                resetSlider();
            });

            dots.forEach((dot) => {
                dot.addEventListener('click', function () {
                    showSlide(Number(dot.dataset.bannerDot || 0));
                    resetSlider();
                });
            });

            bannerSlider.addEventListener('mouseenter', function () {
                if (slideTimer) {
                    window.clearInterval(slideTimer);
                }
            });
            bannerSlider.addEventListener('mouseleave', startSlider);

            showSlide(0);
            startSlider();
        }

        syncNavState();
        window.addEventListener('scroll', syncNavState, { passive: true });
        window.addEventListener('resize', syncNavState);
    });
</script>
@endpush
