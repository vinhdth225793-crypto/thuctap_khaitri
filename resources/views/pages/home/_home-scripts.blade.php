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
