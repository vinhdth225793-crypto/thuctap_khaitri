@push('styles')
<style>
    :root {
        --ink: #151a2d;
        --muted: #5f6780;
        --line: #dfe5ff;
        --paper: #ffffff;
        --soft: #f5f7ff;
        --brand: #4361ee;
        --brand-dark: #2f46c9;
        --brand-tint: #eef2ff;
        --brand-tint-strong: #dfe6ff;
        --accent: #4cc9f0;
        --success: #22c55e;
        --info: #4cc9f0;
        --warn: #f8961e;
        --danger: #f72585;
        --sun: #fbbf24;
        --shadow: 0 14px 40px rgba(67, 97, 238, 0.14);
        --shadow-soft: 0 6px 20px rgba(67, 97, 238, 0.08);
    }

    body {
        background: var(--soft);
        color: var(--ink);
        letter-spacing: 0;
    }

    html {
        scroll-padding-top: 96px;
    }

    a { color: inherit; }

    .home-container {
        width: min(1180px, calc(100% - 32px));
        margin: 0 auto;
    }

    .site-header .home-container {
        width: min(1440px, calc(100% - 32px));
    }

    .site-announcement {
        background: var(--brand-dark);
        color: #fff;
        padding: 10px 0;
        font-size: 14px;
    }

    .site-announcement .home-container {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .site-announcement p { margin: 0; }

    .site-header {
        position: sticky;
        top: 0;
        z-index: 50;
        background: rgba(255, 255, 255, 0.94);
        border-bottom: 1px solid var(--line);
        backdrop-filter: blur(14px);
        box-shadow: 0 1px 0 rgba(67, 97, 238, 0.04);
        transition: box-shadow 0.2s ease, background 0.2s ease;
    }

    .site-header.is-scrolled {
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 14px 34px rgba(67, 97, 238, 0.12);
    }

    .header-row {
        min-height: 86px;
        display: grid;
        grid-template-columns: minmax(280px, 340px) minmax(0, 1fr) auto;
        align-items: center;
        gap: 14px;
        padding: 8px 0;
    }

    .brand-link {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        min-width: 0;
    }

    .site-header .brand-link {
        width: 100%;
        max-width: 340px;
        min-height: 66px;
        gap: 14px;
        padding: 8px 12px 8px 8px;
        border: 1px solid rgba(67, 97, 238, 0.16);
        border-radius: 8px;
        background: linear-gradient(135deg, #ffffff 0%, #eef2ff 100%);
        box-shadow: 0 10px 26px rgba(67, 97, 238, 0.12);
    }

    .brand-copy {
        min-width: 0;
        flex: 1 1 auto;
    }

    .brand-mark {
        width: 58px;
        height: 58px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
        color: #fff;
        flex: 0 0 auto;
        overflow: hidden;
        box-shadow: 0 12px 24px rgba(67, 97, 238, 0.28);
        outline: 3px solid rgba(67, 97, 238, 0.12);
    }

    .brand-mark img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: #fff;
        padding: 3px;
    }

    .brand-link strong {
        display: block;
        font-size: 22px;
        color: var(--brand-dark);
        line-height: 1.2;
        font-weight: 900;
        text-transform: uppercase;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .brand-link small {
        display: block;
        color: #303b78;
        font-size: 13px;
        margin-top: 3px;
        font-weight: 800;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .site-nav,
    .header-actions,
    .contact-shortcuts,
    .course-actions,
    .role-actions,
    .social-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .site-nav {
        justify-self: center;
        width: fit-content;
        max-width: 100%;
        justify-content: flex-start;
        gap: 2px;
        padding: 4px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: rgba(238, 242, 255, 0.72);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.72);
        flex-wrap: nowrap;
        overflow: visible;
    }

    .site-nav a,
    .btn-soft,
    .btn-light-outline,
    .btn-main,
    .course-filter button,
    .hub-search button {
        min-height: 40px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-weight: 700;
        border: 0;
        padding: 10px 16px;
        white-space: nowrap;
    }

    .site-nav a {
        flex: 0 0 auto;
        color: var(--muted);
        gap: 6px;
        min-height: 38px;
        padding: 8px 12px;
        font-size: 14px;
        transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .site-nav a i {
        font-size: 14px;
        flex: 0 0 auto;
    }

    .site-nav a span {
        overflow: visible;
        text-overflow: clip;
        white-space: nowrap;
    }

    .site-nav a:hover,
    .site-nav a.is-active {
        background: var(--brand);
        color: #fff;
        box-shadow: 0 8px 18px rgba(67, 97, 238, 0.24);
    }

    .site-nav a:hover {
        transform: translateY(-1px);
    }

    .header-actions form { margin: 0; }

    .header-actions {
        justify-self: end;
        justify-content: flex-end;
        flex-wrap: nowrap;
        gap: 8px;
    }

    .header-actions .btn-soft,
    .header-actions .btn-main {
        min-height: 38px;
        padding: 9px 13px;
        font-size: 14px;
    }

    .account-chip {
        min-height: 64px;
        max-width: 280px;
        display: inline-flex;
        align-items: center;
        gap: 13px;
        padding: 7px 12px 7px 7px;
        border: 1px solid rgba(67, 97, 238, 0.16);
        border-radius: 8px;
        background: linear-gradient(135deg, #ffffff 0%, #eef2ff 100%);
        color: var(--ink);
        text-decoration: none;
        box-shadow: 0 10px 26px rgba(67, 97, 238, 0.12);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .account-chip:hover {
        border-color: rgba(67, 97, 238, 0.42);
        color: var(--ink);
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(67, 97, 238, 0.16);
    }

    .account-avatar {
        width: 50px;
        height: 50px;
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        overflow: hidden;
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
        color: #fff;
        font-size: 20px;
        font-weight: 900;
        box-shadow: 0 12px 24px rgba(67, 97, 238, 0.28);
        outline: 3px solid rgba(67, 97, 238, 0.12);
    }

    .account-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .account-copy {
        min-width: 0;
        display: grid;
        gap: 3px;
    }

    .account-copy strong,
    .account-copy small {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .account-copy strong {
        color: var(--brand-dark);
        font-size: 16px;
        font-weight: 900;
        line-height: 1.18;
    }

    .account-copy small {
        color: #303b78;
        font-size: 12px;
        font-weight: 800;
        line-height: 1.18;
    }

    .hub-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 18px;
    }

    .contact-shortcuts {
        position: fixed;
        right: 18px;
        bottom: 22px;
        z-index: 70;
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 6px;
        border: 1px solid rgba(67, 97, 238, 0.14);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 14px 34px rgba(67, 97, 238, 0.16);
        backdrop-filter: blur(12px);
        flex-wrap: nowrap;
    }

    .contact-icon {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid var(--line);
        background: #fff;
        color: var(--brand);
        text-decoration: none;
        box-shadow: 0 8px 20px rgba(67, 97, 238, 0.08);
        transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }

    .contact-icon:hover {
        background: var(--brand);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(67, 97, 238, 0.24);
    }

    .btn-main,
    .course-filter button,
    .hub-search button {
        background: var(--brand);
        color: #fff;
    }

    .btn-main:hover,
    .course-filter button:hover,
    .hub-search button:hover {
        background: var(--brand-dark);
        color: #fff;
    }

    .btn-soft {
        background: var(--brand-tint);
        color: var(--brand);
    }

    .btn-soft:hover {
        background: var(--brand-tint-strong);
        color: var(--brand-dark);
    }

    .btn-light-outline {
        border: 1px solid rgba(255, 255, 255, 0.5);
        background: rgba(255, 255, 255, 0.14);
        color: #fff;
        backdrop-filter: blur(10px);
    }

    .btn-light-outline:hover {
        background: #fff;
        color: var(--brand-dark);
    }

    .menu-button {
        display: none;
        width: 42px;
        height: 42px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: #fff;
        padding: 9px;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 5px;
    }

    .menu-button span {
        display: block;
        width: 20px;
        height: 2px;
        background: var(--ink);
        margin: 0;
        transition: transform 0.2s ease, opacity 0.2s ease;
    }

    .menu-button[aria-expanded="true"] span:nth-child(1) {
        transform: translateY(7px) rotate(45deg);
    }

    .menu-button[aria-expanded="true"] span:nth-child(2) {
        opacity: 0;
    }

    .menu-button[aria-expanded="true"] span:nth-child(3) {
        transform: translateY(-7px) rotate(-45deg);
    }

    .home-hub {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        color: #fff;
        background-image:
            linear-gradient(90deg, rgba(16, 22, 52, 0.96) 0%, rgba(47, 70, 201, 0.76) 48%, rgba(67, 97, 238, 0.18) 100%),
            linear-gradient(180deg, rgba(16, 22, 52, 0.22), rgba(16, 22, 52, 0.74)),
            var(--hub-image);
        background-size: cover;
        background-position: center;
    }

    .home-hub::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.08) 1px, transparent 1px);
        background-size: 48px 48px;
        mask-image: linear-gradient(90deg, rgba(0, 0, 0, 0.75), transparent 78%);
    }

    .hub-inner {
        min-height: calc(100svh - 86px);
        padding: 30px 0 26px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 18px;
    }

    .hub-spotlight {
        display: grid;
        grid-template-columns: minmax(0, 1.25fr) minmax(340px, 420px);
        align-items: stretch;
        gap: 18px;
    }

    .hub-copy {
        max-width: 820px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .hub-badge-row,
    .hub-highlights {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 5px 10px;
        border-radius: 8px;
        background: rgba(67, 97, 238, 0.12);
        color: var(--brand);
        font-size: 13px;
        font-weight: 800;
    }

    .home-hub .eyebrow {
        background: rgba(255, 255, 255, 0.14);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(10px);
    }

    .hub-order-badge {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 5px 10px;
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.82);
        font-size: 13px;
        font-weight: 800;
        backdrop-filter: blur(10px);
    }

    .hub-copy h1 {
        font-size: 48px;
        line-height: 1.08;
        margin: 16px 0;
        font-weight: 900;
        max-width: 860px;
        text-shadow: 0 16px 34px rgba(0, 0, 0, 0.28);
    }

    .hub-copy p {
        font-size: 16px;
        line-height: 1.6;
        max-width: 680px;
        margin: 0;
        color: rgba(255, 255, 255, 0.88);
    }

    .hub-capabilities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        margin-top: 28px;
    }

    .cap-item {
        display: flex;
        gap: 14px;
        padding: 16px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 12px;
        backdrop-filter: blur(8px);
        transition: transform 0.2s ease, background 0.2s ease;
    }

    .cap-item:hover {
        background: rgba(255, 255, 255, 0.12);
        transform: translateY(-2px);
    }

    .cap-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--brand);
        color: #fff;
        border-radius: 10px;
        font-size: 18px;
        box-shadow: 0 8px 16px rgba(67, 97, 238, 0.24);
    }

    .cap-text {
        min-width: 0;
    }

    .cap-text strong {
        display: block;
        font-size: 15px;
        font-weight: 800;
        margin-bottom: 4px;
        color: #fff;
    }

    .cap-text span {
        display: block;
        font-size: 13px;
        line-height: 1.4;
        color: rgba(255, 255, 255, 0.72);
    }

    .hub-panel {
        padding: 20px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.14);
        box-shadow: 0 24px 60px rgba(16, 22, 52, 0.28);
        backdrop-filter: blur(20px);
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .hub-panel-header {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #fff;
        font-weight: 800;
        font-size: 15px;
    }

    .hub-panel-header i {
        color: #9be7c7;
    }

    .hub-search-box {
        display: flex;
        gap: 8px;
        background: #fff;
        padding: 6px;
        border-radius: 10px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .hub-search-box input {
        flex: 1;
        border: 0;
        padding: 8px 12px;
        font-size: 14px;
        outline: none;
        color: var(--ink);
    }

    .hub-search-box button {
        background: var(--brand);
        color: #fff;
        border: 0;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 800;
        font-size: 13px;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .hub-search-box button:hover {
        background: var(--brand-dark);
    }

    .hub-quick-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .stat-box {
        padding: 12px 8px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 10px;
        text-align: center;
    }

    .stat-box strong {
        display: block;
        font-size: 18px;
        color: #fff;
        line-height: 1.2;
    }

    .stat-box span {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 800;
        color: rgba(255, 255, 255, 0.6);
        margin-top: 4px;
    }

    .hub-highlight-card {
        margin-top: 4px;
        padding: 14px;
        background: #fff;
        border-radius: 12px;
        color: var(--ink);
        display: flex;
        flex-direction: column;
        gap: 12px;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
    }

    .card-label {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        color: var(--brand);
        letter-spacing: 0.5px;
    }

    .card-content {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .card-content img {
        width: 64px;
        height: 64px;
        border-radius: 8px;
        object-fit: cover;
        flex: 0 0 auto;
    }

    .card-content h3 {
        margin: 0;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.3;
        color: var(--ink);
    }

    .card-content p {
        margin: 4px 0 0;
        font-size: 12px;
        line-height: 1.4;
        color: var(--muted);
    }

    .card-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 13px;
        font-weight: 800;
        color: var(--brand);
        text-decoration: none;
        padding-top: 10px;
        border-top: 1px solid var(--line);
    }

    .card-link:hover {
        color: var(--brand-dark);
    }

    .hub-panel {
        padding: 12px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.14);
        box-shadow: 0 24px 60px rgba(16, 22, 52, 0.28);
        backdrop-filter: blur(16px);
    }

    .hub-search {
        width: 100%;
        background: rgba(255, 255, 255, 0.97);
        color: var(--ink);
        padding: 12px;
        border-radius: 8px;
        box-shadow: 0 12px 30px rgba(16, 22, 52, 0.12);
    }

    .hub-search label {
        display: block;
        font-weight: 800;
        margin: 0 0 7px;
    }

    .hub-search-row,
    .course-filter {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) 190px 130px;
        gap: 10px;
    }

    .hub-panel .hub-search-row {
        grid-template-columns: 1fr;
    }

    .hub-search input,
    .hub-search select,
    .course-filter input,
    .course-filter select {
        width: 100%;
        min-height: 40px;
        border: 1px solid var(--line);
        border-radius: 8px;
        padding: 10px 12px;
        color: var(--ink);
        background: #fff;
    }

    .hub-metrics {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        max-width: none;
        margin-top: 10px;
    }

    .hub-metrics div,
    .path-item,
    .role-panel,
    .update-card,
    .category-chip,
    .course-card,
    .flow-item,
    .instructor-card,
    .contact-register-card,
    .contact-row,
    .empty-panel,
    .timeline-panel,
    .learning-strip {
        border: 1px solid var(--line);
        border-radius: 8px;
        background: var(--paper);
    }

    .hub-metrics div {
        padding: 11px;
        background: rgba(255, 255, 255, 0.16);
        border-color: rgba(255, 255, 255, 0.24);
        backdrop-filter: blur(10px);
    }

    .hub-metrics strong {
        display: block;
        font-size: 22px;
        line-height: 1;
    }

    .hub-metrics span {
        display: block;
        margin-top: 5px;
        color: rgba(255, 255, 255, 0.78);
        font-size: 13px;
    }

    .hub-featured-course {
        margin-top: 10px;
        padding: 12px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.14);
        color: #fff;
    }

    .hub-featured-course > span,
    .hub-next-steps > strong {
        display: block;
        color: rgba(255, 255, 255, 0.74);
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .hub-featured-course > strong {
        display: block;
        margin-top: 7px;
        font-size: 16px;
        line-height: 1.3;
        font-weight: 900;
    }

    .hub-featured-course p {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, 0.78);
        font-size: 13px;
        line-height: 1.45;
    }

    .hub-featured-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 9px;
    }

    .hub-featured-tags em {
        padding: 5px 8px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.16);
        color: #fff;
        font-size: 12px;
        font-style: normal;
        font-weight: 800;
    }

    .hub-featured-course a {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-top: 9px;
        color: #fff;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
    }

    .hub-next-steps {
        display: grid;
        gap: 6px;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid rgba(255, 255, 255, 0.18);
    }

    .hub-next-steps a {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 7px 9px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.95);
        color: var(--brand-dark);
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
    }

    .hub-next-steps a:hover {
        background: #fff;
        color: var(--brand);
    }

    .banner-slider-section {
        background: #fff;
        padding: 28px 0;
    }

    .banner-slider-card {
        position: relative;
        min-height: 340px;
        overflow: hidden;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: var(--paper);
        box-shadow: var(--shadow);
    }

    .banner-slider-track,
    .banner-slide {
        min-height: inherit;
    }

    .banner-slide {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: flex-end;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.35s ease;
    }

    .banner-slide.is-active {
        opacity: 1;
        pointer-events: auto;
    }

    .banner-slide::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(21, 26, 45, 0.78), rgba(21, 26, 45, 0.28), rgba(21, 26, 45, 0.08));
        z-index: 1;
    }

    .banner-slide img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .banner-slide-copy {
        position: relative;
        z-index: 2;
        max-width: 620px;
        padding: 42px;
        color: #fff;
    }

    .banner-slide-copy span {
        display: inline-flex;
        min-height: 28px;
        align-items: center;
        padding: 5px 10px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.16);
        font-size: 13px;
        font-weight: 800;
    }

    .banner-slide-copy h2 {
        margin: 14px 0 10px;
        font-size: 34px;
        line-height: 1.16;
        font-weight: 900;
    }

    .banner-slide-copy p {
        margin: 0 0 20px;
        color: rgba(255, 255, 255, 0.86);
        line-height: 1.7;
    }

    .banner-slider-control {
        position: absolute;
        top: 50%;
        z-index: 3;
        width: 40px;
        height: 40px;
        border: 1px solid rgba(255, 255, 255, 0.38);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.9);
        color: var(--brand-dark);
        transform: translateY(-50%);
    }

    .banner-slider-control.prev {
        left: 14px;
    }

    .banner-slider-control.next {
        right: 14px;
    }

    .banner-slider-dots {
        position: absolute;
        right: 22px;
        bottom: 18px;
        z-index: 3;
        display: flex;
        gap: 7px;
    }

    .banner-slider-dots button {
        width: 10px;
        height: 10px;
        padding: 0;
        border: 0;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.5);
    }

    .banner-slider-dots button.is-active {
        width: 24px;
        background: #fff;
    }

    .role-panel-wrap,
    .guest-paths,
    .updates-section,
    .categories-section,
    .courses-section,
    .learning-flow,
    .instructors-section,
    .contact-section {
        padding: 64px 0;
    }

    .role-panel-wrap {
        padding-top: 36px;
        padding-bottom: 24px;
    }

    .guest-paths {
        background: #fff;
        padding: 28px 0;
    }

    .path-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    .path-item {
        padding: 22px;
        text-decoration: none;
        color: var(--ink);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .path-item:hover,
    .course-card:hover,
    .update-card:hover,
    .instructor-card:hover,
    .category-chip:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow);
    }

    .path-item i {
        color: var(--accent);
        font-size: 24px;
        margin-bottom: 14px;
    }

    .path-item strong,
    .path-item span {
        display: block;
    }

    .path-item span {
        color: var(--muted);
        margin-top: 8px;
        line-height: 1.6;
    }

    .section-heading {
        max-width: 760px;
        margin-bottom: 28px;
    }

    .section-heading.split {
        max-width: none;
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 24px;
    }

    .section-heading h2,
    .role-panel-heading h2,
    .contact-copy h2 {
        font-size: 34px;
        line-height: 1.2;
        margin: 12px 0;
        font-weight: 800;
    }

    .section-heading p,
    .role-panel-heading p,
    .contact-copy p {
        color: var(--muted);
        line-height: 1.7;
        margin: 0;
    }

    .updates-section,
    .courses-section,
    .instructors-section {
        background: #fff;
    }

    .updates-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        grid-auto-rows: 1fr;
        gap: 18px;
        align-items: stretch;
    }

    .update-card {
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .update-card img {
        width: 100%;
        aspect-ratio: 16 / 9;
        object-fit: cover;
        background: #e8edff;
        flex-shrink: 0;
    }

    .update-body {
        padding: 18px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex: 1;
        min-height: 0;
    }

    .update-body h3 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: calc(20px * 1.3 * 2);
    }

    .update-body p {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin: 0;
    }

    .update-body a {
        margin-top: auto;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding-top: 4px;
    }

    .update-body span,
    .course-category,
    .teacher-meta {
        color: var(--brand);
        font-weight: 800;
        font-size: 13px;
    }

    .update-body h3,
    .course-body h3,
    .flow-item h3,
    .instructor-card h3 {
        font-size: 20px;
        line-height: 1.3;
        margin: 8px 0;
        font-weight: 800;
    }

    .course-body h3 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: calc(20px * 1.3 * 2);
    }

    .update-body p,
    .course-body p,
    .flow-item p,
    .instructor-card p {
        color: var(--muted);
        line-height: 1.6;
    }

    .course-body > p {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin: 0;
    }

    .course-category {
        margin: 0;
        font-weight: 700;
        font-size: 13px;
        color: var(--brand);
    }

    .update-body a {
        color: var(--brand);
        font-weight: 800;
        text-decoration: none;
    }

    .category-list {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
    }

    .category-chip {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 18px;
        text-decoration: none;
        color: var(--ink);
    }

    .category-chip strong {
        color: var(--brand);
        white-space: nowrap;
    }

    .category-chip.active {
        border-color: var(--brand);
        background: var(--brand-tint);
    }

    .course-filter {
        grid-template-columns: minmax(220px, 1fr) 190px 150px 120px;
        margin: 0 0 24px;
    }

    .course-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        grid-auto-rows: 1fr;
        gap: 22px;
        align-items: stretch;
    }

    .course-card {
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .course-card img {
        width: 100%;
        aspect-ratio: 16 / 9;
        object-fit: cover;
        background: #e8edff;
        flex-shrink: 0;
    }

    .course-body {
        padding: 18px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        flex: 1;
        min-height: 0;
    }

    .course-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .course-tags span {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 800;
    }

    .tone-good { background: var(--brand-tint); color: var(--brand); }
    .tone-warm { background: var(--brand-tint-strong); color: var(--brand-dark); }
    .tone-alert { background: #d6dfff; color: #253ab8; }
    .tone-info { background: var(--brand-tint); color: var(--brand-dark); }

    .course-facts {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin: auto 0 0;
    }

    .course-facts div {
        background: var(--soft);
        border-radius: 8px;
        padding: 10px;
        min-width: 0;
    }

    .course-facts dt {
        color: var(--muted);
        font-size: 12px;
        margin-bottom: 2px;
    }

    .course-facts dd {
        margin: 0;
        font-weight: 800;
        font-size: 13px;
        overflow-wrap: anywhere;
    }

    .course-actions,
    .role-actions,
    .social-row {
        flex-wrap: wrap;
    }

    .course-actions {
        display: flex;
        gap: 10px;
        margin-top: 4px;
    }

    .pagination-wrap {
        margin-top: 28px;
        display: flex;
        justify-content: center;
    }

    .learning-flow {
        background: var(--soft);
    }

    .flow-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
    }

    .flow-item {
        padding: 24px;
    }

    .flow-item span {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--accent);
        color: #fff;
        font-weight: 800;
    }

    .instructor-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
    }

    .instructor-card {
        overflow: hidden;
    }

    .instructor-card img,
    .avatar-fallback {
        width: 100%;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        background: #e9eeff;
    }

    .avatar-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 56px;
        font-weight: 800;
        color: var(--brand);
    }

    .instructor-card div {
        padding: 18px;
    }

    .instructor-card strong {
        color: var(--brand);
    }

    .contact-section {
        background: #f0f3ff;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: minmax(300px, 0.88fr) minmax(460px, 1.12fr);
        gap: 40px;
        align-items: start;
    }

    .contact-info-panel {
        display: grid;
        gap: 18px;
    }

    .contact-list {
        display: grid;
        gap: 12px;
    }

    .contact-row {
        display: flex;
        gap: 14px;
        padding: 18px;
        text-decoration: none;
        color: var(--ink);
    }

    .contact-row i {
        color: var(--accent);
        font-size: 22px;
        margin-top: 2px;
        width: 28px;
    }

    .contact-row strong,
    .contact-row span {
        display: block;
    }

    .contact-row strong {
        margin-bottom: 4px;
    }

    .contact-row p {
        margin: 0;
    }

    .social-row a {
        border-radius: 8px;
        padding: 11px 14px;
        background: var(--brand);
        color: #fff;
        text-decoration: none;
        font-weight: 800;
    }

    .social-row {
        flex-wrap: wrap;
    }

    .contact-register-card {
        padding: 22px;
        box-shadow: var(--shadow);
        background:
            linear-gradient(135deg, rgba(67, 97, 238, 0.08), rgba(255, 255, 255, 0.96)),
            #fff;
    }

    .contact-register-head {
        margin-bottom: 18px;
    }

    .contact-register-head h3 {
        margin: 8px 0;
        font-size: 24px;
        line-height: 1.25;
        font-weight: 900;
        color: var(--ink);
    }

    .contact-register-head p {
        margin: 0;
        color: var(--muted);
        line-height: 1.65;
    }

    .contact-register-form {
        display: grid;
        gap: 12px;
    }

    .contact-form-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .contact-field {
        display: grid;
        gap: 6px;
        margin: 0;
        min-width: 0;
    }

    .contact-field span,
    .contact-field-label {
        font-size: 13px;
        font-weight: 800;
        color: #303b78;
    }

    .contact-register-form input:not([type="radio"]):not([type="checkbox"]) {
        width: 100%;
        min-height: 42px;
        border: 1px solid var(--line);
        border-radius: 8px;
        padding: 10px 12px;
        background: #fff;
        color: var(--ink);
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .contact-register-form input:not([type="radio"]):not([type="checkbox"]):focus {
        border-color: var(--brand);
        box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.13);
    }

    .contact-register-form small {
        color: #dc2626;
        font-weight: 700;
    }

    .contact-form-feedback {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 14px;
        font-weight: 700;
        line-height: 1.45;
    }

    .contact-form-feedback.success {
        color: #155e75;
        background: #e0f2fe;
        border: 1px solid #bae6fd;
    }

    .contact-role-group {
        display: grid;
        gap: 8px;
    }

    .contact-role-options {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .contact-role-option {
        min-height: 44px;
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 10px 12px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
    }

    .contact-role-option input,
    .contact-terms input {
        flex: 0 0 auto;
        accent-color: var(--brand);
    }

    .contact-role-option span {
        color: var(--ink);
        font-weight: 800;
        font-size: 14px;
    }

    .contact-role-option i {
        color: var(--brand);
        margin-right: 5px;
    }

    .contact-terms {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: var(--muted);
        line-height: 1.55;
        font-size: 14px;
        margin: 0;
    }

    .contact-terms a {
        color: var(--brand);
        font-weight: 800;
        text-decoration: none;
    }

    .contact-register-form .btn-main,
    .contact-register-card > .btn-main {
        width: 100%;
        justify-content: center;
        border: 0;
        margin-top: 2px;
    }

    .role-panel {
        padding: 24px;
        box-shadow: var(--shadow);
    }

    .role-panel-heading {
        max-width: 760px;
        margin-bottom: 20px;
    }

    .role-grid,
    .student-dashboard-grid,
    .teacher-dashboard-grid {
        display: grid;
        gap: 16px;
    }

    .role-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .role-tile {
        border: 1px solid var(--line);
        border-radius: 8px;
        padding: 20px;
        text-decoration: none;
        color: var(--ink);
        background: var(--soft);
    }

    .role-tile strong {
        display: block;
        font-size: 34px;
        margin: 12px 0 4px;
    }

    .role-icon {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
    }

    .role-icon.info { background: var(--brand); }
    .role-icon.warm { background: var(--brand-dark); }
    .role-icon.good { background: #5a73f1; }

    .student-dashboard-grid {
        grid-template-columns: 0.9fr 1.1fr 1fr;
    }

    .teacher-dashboard-grid {
        grid-template-columns: 1.4fr 0.6fr;
    }

    .learning-strip {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        overflow: hidden;
    }

    .learning-strip.vertical {
        grid-template-columns: 1fr;
    }

    .metric-block {
        padding: 18px;
        border-right: 1px solid var(--line);
    }

    .metric-block:last-child {
        border-right: 0;
    }

    .learning-strip.vertical .metric-block {
        border-right: 0;
        border-bottom: 1px solid var(--line);
    }

    .learning-strip.vertical .metric-block:last-child {
        border-bottom: 0;
    }

    .metric-block strong {
        display: block;
        font-size: 30px;
        color: var(--brand);
    }

    .metric-block span {
        color: var(--muted);
        font-size: 13px;
    }

    .timeline-panel {
        overflow: hidden;
    }

    .panel-title {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--line);
        font-weight: 800;
    }

    .panel-title i {
        color: var(--accent);
    }

    .timeline-row {
        display: flex;
        gap: 12px;
        align-items: center;
        padding: 14px 16px;
        text-decoration: none;
        color: var(--ink);
        border-bottom: 1px solid var(--line);
    }

    .timeline-row:last-child {
        border-bottom: 0;
    }

    .timeline-row:hover {
        background: var(--soft);
    }

    .timeline-row time {
        width: 68px;
        flex: 0 0 68px;
        border-radius: 8px;
        background: var(--brand-tint);
        padding: 8px;
        text-align: center;
    }

    .timeline-row time strong,
    .timeline-row time span,
    .timeline-row span strong,
    .timeline-row span small {
        display: block;
    }

    .timeline-row time span,
    .timeline-row span small {
        color: var(--muted);
        font-size: 12px;
        margin-top: 2px;
    }

    .timeline-row.compact {
        justify-content: space-between;
    }

    .empty-mini {
        padding: 18px;
        color: var(--muted);
    }

    .empty-panel {
        padding: 24px;
        color: var(--muted);
    }

    .empty-panel.wide {
        grid-column: 1 / -1;
    }

    .site-footer {
        background: #111633;
        color: rgba(255, 255, 255, 0.78);
        padding-top: 44px;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: 1.5fr 0.8fr 0.8fr;
        gap: 34px;
    }

    .footer-brand strong {
        color: #fff;
    }

    .footer-brand small {
        color: rgba(255, 255, 255, 0.68);
    }

    .site-footer p {
        max-width: 420px;
        line-height: 1.7;
        margin: 18px 0 0;
    }

    .site-footer nav {
        display: grid;
        gap: 10px;
    }

    .site-footer nav strong {
        color: #fff;
        margin-bottom: 4px;
    }

    .site-footer nav a {
        color: rgba(255, 255, 255, 0.72);
        text-decoration: none;
    }

    .site-footer nav a:hover {
        color: #fff;
    }

    .footer-bottom {
        margin-top: 36px;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        padding: 16px 0;
        font-size: 14px;
    }

    @media (max-width: 1080px) {
        .course-grid,
        .instructor-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .updates-grid,
        .student-dashboard-grid {
            grid-template-columns: 1fr 1fr;
        }

        .learning-strip {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 1460px) {
        .account-chip + .btn-soft {
            display: none;
        }
    }

    @media (max-width: 1360px) {
        .header-row {
            grid-template-columns: minmax(0, 1fr) auto;
            min-height: 68px;
            gap: 10px;
        }

        .menu-button {
            display: inline-flex;
            justify-self: end;
        }

        .site-nav,
        .header-actions {
            grid-column: 1 / -1;
            display: none;
            width: 100%;
        }

        .site-nav.is-open {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            max-width: none;
            padding: 8px;
            margin-top: 2px;
        }

        .header-actions.is-open {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: stretch;
            gap: 8px;
            padding-bottom: 12px;
        }

        .header-actions.is-open .account-chip {
            grid-column: 1 / -1;
            width: 100%;
            max-width: none;
        }

        .header-actions.is-open .account-avatar {
            width: 50px;
            height: 50px;
        }

        .site-nav.is-open a {
            justify-content: flex-start;
            width: 100%;
            padding: 10px 12px;
        }

        .header-actions.is-open .btn-soft,
        .header-actions.is-open .btn-main,
        .header-actions.is-open form,
        .header-actions.is-open form button {
            width: 100%;
        }

        .hub-inner {
            min-height: calc(100svh - 72px);
            padding: 24px 0;
        }

        .hub-spotlight {
            grid-template-columns: minmax(0, 1fr) minmax(320px, 390px);
            gap: 14px;
        }

        .hub-panel {
            max-width: none;
        }

        .hub-copy h1 {
            font-size: 42px;
        }

        .hub-info-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            max-width: 100%;
        }

        .banner-slider-card {
            min-height: 300px;
        }

        .banner-slide-copy {
            max-width: none;
            padding: 32px;
        }

        .banner-slide-copy h2 {
            font-size: 28px;
        }

        .hub-search-row,
        .course-filter,
        .path-grid,
        .category-list,
        .flow-grid,
        .role-grid,
        .teacher-dashboard-grid,
        .contact-grid,
        .footer-grid {
            grid-template-columns: 1fr;
        }

        .section-heading.split {
            display: block;
        }
    }

    @media (max-width: 1080px) {
        .hub-spotlight {
            grid-template-columns: 1fr;
        }

        .hub-panel {
            max-width: 760px;
        }
    }

    @media (max-width: 640px) {
        .home-container {
            width: min(100% - 24px, 1180px);
        }

        .site-header .home-container {
            width: min(100% - 24px, 1440px);
        }

        .contact-shortcuts {
            right: 12px;
            bottom: 14px;
            gap: 6px;
            padding: 5px;
        }

        .contact-icon {
            width: 38px;
            height: 38px;
        }

        .site-header .brand-link {
            max-width: min(100%, 340px);
            min-height: 60px;
            padding: 7px 10px 7px 7px;
        }

        .brand-mark {
            width: 50px;
            height: 50px;
        }

        .brand-link strong {
            font-size: 18px;
        }

        .brand-link small {
            display: none;
        }

        .site-nav.is-open,
        .header-actions.is-open {
            grid-template-columns: 1fr;
        }

        .hub-copy h1 {
            font-size: 32px;
        }

        .hub-copy p {
            font-size: 16px;
        }

        .hub-inner {
            min-height: 520px;
            padding: 42px 0;
        }

        .hub-panel {
            padding: 10px;
        }

        .hub-highlights {
            gap: 8px;
        }

        .hub-highlights span {
            width: 100%;
            justify-content: flex-start;
        }

        .hub-info-grid {
            grid-template-columns: 1fr;
        }

        .hub-info-grid div {
            min-height: auto;
        }

        .hub-metrics {
            grid-template-columns: 1fr;
        }

        .banner-slider-section {
            padding: 18px 0;
        }

        .banner-slider-card {
            min-height: 280px;
        }

        .banner-slide-copy {
            padding: 24px 18px 48px;
        }

        .banner-slide-copy h2 {
            font-size: 22px;
        }

        .banner-slide-copy p {
            font-size: 14px;
        }

        .banner-slider-control {
            top: auto;
            bottom: 14px;
            width: 34px;
            height: 34px;
            transform: none;
        }

        .banner-slider-control.prev {
            left: 14px;
        }

        .banner-slider-control.next {
            left: 54px;
            right: auto;
        }

        .course-grid,
        .instructor-grid,
        .updates-grid,
        .student-dashboard-grid,
        .contact-form-row,
        .contact-role-options {
            grid-template-columns: 1fr;
        }

        .learning-strip {
            grid-template-columns: 1fr;
        }

        .metric-block {
            border-right: 0;
            border-bottom: 1px solid var(--line);
        }

        .metric-block:last-child {
            border-bottom: 0;
        }

        .section-heading h2,
        .role-panel-heading h2,
        .contact-copy h2 {
            font-size: 26px;
        }
    }

    /* ========== About / Why us section ========== */
    .about-section {
        padding: 80px 0 40px;
    }

    .about-grid {
        display: grid;
        grid-template-columns: 1fr 1.1fr;
        gap: 48px;
        align-items: center;
        margin-top: 32px;
    }

    .about-visual {
        position: relative;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: var(--shadow);
        background: linear-gradient(135deg, var(--brand-tint) 0%, #e0f2fe 100%);
        aspect-ratio: 4/3;
    }

    .about-visual img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .about-visual .floating-card {
        position: absolute;
        background: #fff;
        border-radius: 16px;
        padding: 14px 18px;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
    }

    .about-visual .floating-card.fc-top {
        top: 24px;
        right: 24px;
    }

    .about-visual .floating-card.fc-bottom {
        bottom: 24px;
        left: 24px;
    }

    .about-visual .floating-card .fc-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        font-size: 18px;
    }

    .about-visual .floating-card .fc-icon.is-brand {
        background: var(--brand-tint);
        color: var(--brand);
    }

    .about-visual .floating-card .fc-icon.is-success {
        background: rgba(34, 197, 94, 0.12);
        color: var(--success);
    }

    .about-visual .floating-card strong {
        display: block;
        font-size: 15px;
        color: var(--ink);
    }

    .about-visual .floating-card span {
        color: var(--muted);
        font-size: 12px;
    }

    .about-content h2 {
        font-size: 36px;
        line-height: 1.2;
        margin: 12px 0 18px;
        color: var(--ink);
    }

    .about-content p {
        color: var(--muted);
        line-height: 1.7;
        margin-bottom: 24px;
    }

    .about-pillars {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 28px;
    }

    .about-pillar {
        display: flex;
        gap: 14px;
        padding: 18px;
        border-radius: 16px;
        background: var(--paper);
        border: 1px solid var(--line);
        transition: all 0.2s ease;
    }

    .about-pillar:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-soft);
        border-color: var(--brand-tint-strong);
    }

    .about-pillar .pillar-icon {
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        font-size: 18px;
        color: #fff;
    }

    .about-pillar .pillar-icon.tone-brand { background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%); }
    .about-pillar .pillar-icon.tone-info { background: linear-gradient(135deg, var(--info) 0%, #1e90ff 100%); }
    .about-pillar .pillar-icon.tone-warn { background: linear-gradient(135deg, var(--warn) 0%, #f59e0b 100%); }
    .about-pillar .pillar-icon.tone-success { background: linear-gradient(135deg, var(--success) 0%, #16a34a 100%); }

    .about-pillar strong {
        display: block;
        margin-bottom: 4px;
        color: var(--ink);
        font-size: 15px;
    }

    .about-pillar span {
        color: var(--muted);
        font-size: 13px;
        line-height: 1.55;
    }

    /* ========== Stats highlight section ========== */
    .stats-highlight {
        padding: 60px 0;
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
        position: relative;
        overflow: hidden;
    }

    .stats-highlight::before {
        content: '';
        position: absolute;
        top: -120px;
        right: -120px;
        width: 360px;
        height: 360px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.06);
    }

    .stats-highlight::after {
        content: '';
        position: absolute;
        bottom: -180px;
        left: -180px;
        width: 480px;
        height: 480px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
    }

    .stats-highlight .home-container {
        position: relative;
        z-index: 1;
    }

    .stats-highlight-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }

    .stat-highlight-item {
        text-align: center;
        color: #fff;
        padding: 18px 12px;
    }

    .stat-highlight-item .stat-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 14px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.16);
        display: grid;
        place-items: center;
        font-size: 24px;
        backdrop-filter: blur(8px);
    }

    .stat-highlight-item strong {
        display: block;
        font-size: 40px;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 6px;
    }

    .stat-highlight-item span {
        font-size: 14px;
        opacity: 0.9;
        font-weight: 500;
    }

    /* ========== FAQ section ========== */
    .faq-section {
        padding: 80px 0;
    }

    .faq-list {
        max-width: 820px;
        margin: 36px auto 0;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .faq-item {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .faq-item[open] {
        border-color: var(--brand);
        box-shadow: var(--shadow-soft);
    }

    .faq-item summary {
        list-style: none;
        cursor: pointer;
        padding: 18px 22px;
        font-weight: 600;
        color: var(--ink);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        font-size: 15px;
    }

    .faq-item summary::-webkit-details-marker { display: none; }

    .faq-item summary::after {
        content: '\f078';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: var(--brand-tint);
        color: var(--brand);
        display: grid;
        place-items: center;
        font-size: 12px;
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }

    .faq-item[open] summary::after {
        transform: rotate(180deg);
    }

    .faq-item .faq-body {
        padding: 0 22px 20px;
        color: var(--muted);
        line-height: 1.7;
        font-size: 14px;
    }

    /* ========== About / Stats responsive ========== */
    @media (max-width: 900px) {
        .about-grid {
            grid-template-columns: 1fr;
            gap: 32px;
        }

        .about-content h2 {
            font-size: 28px;
        }

        .about-pillars {
            grid-template-columns: 1fr;
        }

        .stats-highlight-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .stat-highlight-item strong {
            font-size: 32px;
        }
    }

    @media (max-width: 540px) {
        .about-visual .floating-card {
            display: none;
        }

        .stat-highlight-item .stat-icon {
            width: 52px;
            height: 52px;
            font-size: 20px;
        }
    }

    /* ========== Topbar mini (phía trên header) ========== */
    .site-topbar {
        background: linear-gradient(90deg, var(--brand-dark) 0%, var(--brand) 100%);
        color: rgba(255, 255, 255, 0.92);
        font-size: 13px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .topbar-row {
        min-height: 38px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 6px 0;
    }

    .topbar-info,
    .topbar-social {
        display: flex;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
    }

    .topbar-link {
        color: #fff;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        opacity: 0.9;
        transition: opacity 0.2s ease;
    }

    .topbar-link:hover {
        opacity: 1;
        color: #fff;
    }

    .topbar-link i {
        width: 18px;
        height: 18px;
        display: grid;
        place-items: center;
        background: rgba(255, 255, 255, 0.16);
        border-radius: 6px;
        font-size: 11px;
    }

    .topbar-static { cursor: default; }
    .topbar-static:hover { opacity: 0.9; }

    .topbar-social-label {
        color: rgba(255, 255, 255, 0.7);
        font-size: 12px;
        font-weight: 600;
    }

    .topbar-social-icon {
        width: 26px;
        height: 26px;
        display: grid;
        place-items: center;
        background: rgba(255, 255, 255, 0.14);
        color: #fff;
        border-radius: 7px;
        text-decoration: none;
        font-size: 12px;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .topbar-social-icon:hover {
        background: rgba(255, 255, 255, 0.28);
        color: #fff;
        transform: translateY(-1px);
    }

    /* ========== Dropdown khóa học ========== */
    .nav-dropdown {
        position: relative;
    }

    /* Bridge area trong suốt phủ vùng gap giữa nút và menu — fix bug "menu biến mất khi rê chuột xuống" */
    .nav-dropdown::before {
        content: '';
        position: absolute;
        top: 100%;
        left: -20px;
        right: -20px;
        height: 18px;
        opacity: 0;
        pointer-events: none;
        z-index: 59;
    }

    .nav-dropdown:hover::before,
    .nav-dropdown:focus-within::before {
        opacity: 1;
        pointer-events: auto;
    }

    .nav-dropdown > .nav-dropdown-toggle .nav-caret {
        font-size: 10px;
        margin-left: 2px;
        transition: transform 0.2s ease;
    }

    .nav-dropdown:hover > .nav-dropdown-toggle .nav-caret,
    .nav-dropdown:focus-within > .nav-dropdown-toggle .nav-caret {
        transform: rotate(180deg);
    }

    .nav-dropdown-menu {
        position: absolute;
        top: calc(100% + 10px);
        left: 50%;
        transform: translateX(-50%) translateY(-6px);
        width: 540px;
        max-width: 92vw;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 16px;
        box-shadow: 0 24px 60px rgba(67, 97, 238, 0.18);
        padding: 18px;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 0.2s ease, transform 0.2s ease;
        z-index: 60;
    }

    .nav-dropdown-menu::before {
        content: '';
        position: absolute;
        top: -10px;
        left: 50%;
        transform: translateX(-50%) rotate(45deg);
        width: 14px;
        height: 14px;
        background: #fff;
        border-top: 1px solid var(--line);
        border-left: 1px solid var(--line);
    }

    .nav-dropdown:hover > .nav-dropdown-menu,
    .nav-dropdown:focus-within > .nav-dropdown-menu {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transform: translateX(-50%) translateY(0);
    }

    .nav-dropdown-head {
        padding: 4px 4px 12px;
        border-bottom: 1px dashed var(--line);
        margin-bottom: 12px;
    }

    .nav-dropdown-head .eyebrow {
        display: block;
        font-size: 11px;
        color: var(--brand);
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .nav-dropdown-head strong {
        display: block;
        color: var(--ink);
        font-size: 15px;
        font-weight: 800;
    }

    .nav-dropdown-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .nav-dropdown-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 10px;
        text-decoration: none;
        color: var(--ink);
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .nav-dropdown-item:hover {
        background: var(--brand-tint);
        color: var(--ink);
        transform: translateX(2px);
    }

    .ndi-icon {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
        color: #fff;
        display: grid;
        place-items: center;
        font-size: 14px;
    }

    .ndi-copy strong {
        display: block;
        color: var(--ink);
        font-size: 14px;
        font-weight: 700;
        line-height: 1.3;
    }

    .ndi-copy small {
        display: block;
        color: var(--muted);
        font-size: 11.5px;
        font-weight: 600;
        margin-top: 2px;
    }

    .nav-dropdown-foot {
        padding-top: 12px;
        margin-top: 12px;
        border-top: 1px dashed var(--line);
        display: flex;
        justify-content: center;
    }

    .nav-dropdown-foot .btn-soft {
        font-size: 13px;
        gap: 6px;
    }

    /* ========== Header search ========== */
    .header-search-toggle {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        border: 1px solid var(--line);
        background: #fff;
        color: var(--brand);
        cursor: pointer;
        display: grid;
        place-items: center;
        transition: all 0.2s ease;
    }

    .header-search-toggle:hover {
        background: var(--brand-tint);
        border-color: var(--brand);
        transform: translateY(-1px);
    }

    .header-search-panel {
        display: none;
        align-items: center;
        gap: 8px;
        background: #fff;
        border: 1px solid var(--brand-tint-strong);
        border-radius: 10px;
        padding: 4px 4px 4px 12px;
        box-shadow: 0 8px 22px rgba(67, 97, 238, 0.14);
        min-width: 280px;
    }

    .header-search-panel:not([hidden]) {
        display: inline-flex;
        animation: slideInSearch 0.18s ease;
    }

    @keyframes slideInSearch {
        from { opacity: 0; transform: translateX(8px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .header-search-panel > i:first-child {
        color: var(--brand);
        font-size: 14px;
    }

    .header-search-panel input {
        flex: 1;
        border: 0;
        outline: 0;
        font-size: 14px;
        background: transparent;
        min-width: 0;
        height: 32px;
        color: var(--ink);
    }

    .header-search-panel button {
        width: 32px;
        height: 32px;
        border: 0;
        border-radius: 8px;
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
        color: #fff;
        cursor: pointer;
        display: grid;
        place-items: center;
        font-size: 12px;
        transition: transform 0.2s ease;
    }

    .header-search-panel button:hover {
        transform: translateX(2px);
    }

    /* ========== Brand link micro-interaction ========== */
    .site-header .brand-link {
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .site-header .brand-link:hover {
        border-color: rgba(67, 97, 238, 0.45);
        box-shadow: 0 14px 32px rgba(67, 97, 238, 0.22);
        transform: translateY(-1px);
    }

    .brand-mark {
        transition: transform 0.3s ease;
    }

    .site-header .brand-link:hover .brand-mark {
        transform: rotate(-6deg) scale(1.06);
    }

    /* ========== Topbar responsive ========== */
    @media (max-width: 1080px) {
        .topbar-info { gap: 12px; }
        .topbar-link span { font-size: 12px; }
        .topbar-static { display: none; }
        .nav-dropdown-menu { width: 460px; }
    }

    @media (max-width: 820px) {
        .site-topbar { display: none; }
        .header-search-panel { min-width: 220px; }
        .nav-dropdown-menu {
            width: 320px;
            position: fixed;
            left: 50%;
            top: 80px;
            transform: translateX(-50%) translateY(-6px);
        }
        .nav-dropdown:hover > .nav-dropdown-menu,
        .nav-dropdown:focus-within > .nav-dropdown-menu {
            transform: translateX(-50%) translateY(0);
        }
        .nav-dropdown-grid { grid-template-columns: 1fr; }
        .nav-dropdown-menu::before { display: none; }
    }

    @media (max-width: 540px) {
        .header-search-toggle { display: none; }
    }

    /* ========== Scroll progress bar ========== */
    .site-progress {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: rgba(67, 97, 238, 0.06);
        z-index: 100;
        pointer-events: none;
    }

    .site-progress-bar {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, var(--brand) 0%, var(--accent) 50%, var(--success) 100%);
        box-shadow: 0 0 12px rgba(67, 97, 238, 0.4);
        transition: width 0.1s ease-out;
        border-radius: 0 3px 3px 0;
    }

    /* ========== Topbar pill (Sắp khai giảng) ========== */
    .topbar-extras {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .topbar-pill {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 4px 12px 4px 22px;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 999px;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        backdrop-filter: blur(8px);
        transition: all 0.2s ease;
    }

    .topbar-pill:hover {
        background: rgba(255, 255, 255, 0.28);
        color: #fff;
        transform: translateY(-1px);
    }

    .topbar-pill i { font-size: 11px; }

    .topbar-pulse {
        position: absolute;
        left: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #fbbf24;
        box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.7);
        animation: topbarPulse 1.6s infinite;
    }

    @keyframes topbarPulse {
        0% { box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.7); }
        70% { box-shadow: 0 0 0 8px rgba(251, 191, 36, 0); }
        100% { box-shadow: 0 0 0 0 rgba(251, 191, 36, 0); }
    }

    /* ========== Sticky compact header ========== */
    .site-header.is-scrolled .header-row {
        min-height: 64px;
    }

    .site-header.is-scrolled .brand-link {
        min-height: 50px;
        padding: 4px 10px 4px 6px;
    }

    .site-header.is-scrolled .brand-mark {
        width: 44px;
        height: 44px;
    }

    .site-header.is-scrolled .brand-link strong {
        font-size: 18px;
    }

    .site-header.is-scrolled .brand-link small {
        display: none;
    }

    .site-header.is-scrolled .account-chip {
        min-height: 48px;
    }

    .site-header.is-scrolled .account-avatar {
        width: 38px;
        height: 38px;
        font-size: 16px;
    }

    /* ========== Mega menu khóa học ========== */
    .nav-mega-menu {
        position: absolute;
        top: calc(100% + 10px);
        left: 50%;
        transform: translateX(-50%) translateY(-6px);
        width: 920px;
        max-width: 95vw;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 18px;
        box-shadow: 0 28px 70px rgba(67, 97, 238, 0.22);
        padding: 22px;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 0.2s ease, transform 0.2s ease;
        z-index: 60;
    }

    .nav-mega-menu::before {
        content: '';
        position: absolute;
        top: -10px;
        left: 50%;
        transform: translateX(-50%) rotate(45deg);
        width: 14px;
        height: 14px;
        background: #fff;
        border-top: 1px solid var(--line);
        border-left: 1px solid var(--line);
    }

    .nav-dropdown.is-mega:hover > .nav-mega-menu,
    .nav-dropdown.is-mega:focus-within > .nav-mega-menu {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transform: translateX(-50%) translateY(0);
    }

    .nav-mega-grid {
        display: grid;
        grid-template-columns: 1.1fr 1.1fr 1fr;
        gap: 18px;
    }

    .nav-mega-col {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .nav-mega-head {
        padding: 0 4px 10px;
        border-bottom: 1px dashed var(--line);
    }

    .nav-mega-head .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        color: var(--brand);
        font-weight: 800;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .nav-mega-head strong {
        display: block;
        color: var(--ink);
        font-size: 14px;
        font-weight: 800;
    }

    .nav-mega-list {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .nav-mega-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 9px 10px;
        border-radius: 10px;
        text-decoration: none;
        color: var(--ink);
        transition: all 0.2s ease;
    }

    .nav-mega-item:hover {
        background: var(--brand-tint);
        color: var(--ink);
    }

    .nav-mega-item:hover .ndi-arrow {
        opacity: 1;
        transform: translateX(0);
    }

    .ndi-arrow {
        margin-left: auto;
        opacity: 0;
        transform: translateX(-4px);
        transition: all 0.2s ease;
        color: var(--brand);
        font-size: 11px;
    }

    .nav-mega-course {
        display: flex;
        gap: 10px;
        padding: 8px;
        border-radius: 10px;
        text-decoration: none;
        color: var(--ink);
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .nav-mega-course:hover {
        background: var(--soft);
        border-color: var(--brand-tint-strong);
        color: var(--ink);
    }

    .nav-mega-course img {
        width: 64px;
        height: 48px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
        background: var(--brand-tint);
    }

    .nmc-copy {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
        flex: 1;
    }

    .nmc-copy strong {
        font-size: 13px;
        font-weight: 700;
        line-height: 1.3;
        color: var(--ink);
    }

    .nmc-copy small {
        font-size: 11.5px;
        color: var(--muted);
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        flex-wrap: wrap;
    }

    .dot-sep { opacity: 0.5; }

    .nav-mega-feature {
        background: linear-gradient(135deg, var(--brand-tint) 0%, #e0f2fe 100%);
        border-radius: 14px;
        padding: 14px;
    }

    .nav-mega-hero {
        position: relative;
        display: block;
        border-radius: 12px;
        overflow: hidden;
        aspect-ratio: 4/3;
        text-decoration: none;
        box-shadow: 0 12px 24px rgba(67, 97, 238, 0.15);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .nav-mega-hero:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 30px rgba(67, 97, 238, 0.22);
    }

    .nav-mega-hero img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .nav-mega-hero-overlay {
        position: absolute;
        inset: 0;
        padding: 12px;
        background: linear-gradient(0deg, rgba(15, 23, 42, 0.82) 0%, rgba(15, 23, 42, 0.2) 50%, transparent 100%);
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        color: #fff;
    }

    .nav-hero-badge {
        align-self: flex-start;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        background: var(--sun);
        color: #78350f;
        font-size: 11px;
        font-weight: 800;
        border-radius: 999px;
        margin-bottom: auto;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .nav-mega-hero-overlay strong {
        font-size: 14px;
        line-height: 1.3;
        margin-top: 8px;
        font-weight: 800;
    }

    .nav-mega-hero-overlay small {
        font-size: 11.5px;
        opacity: 0.92;
        margin-top: 4px;
    }

    .nav-mega-cta {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-top: 12px;
    }

    .nav-mega-cta .btn-main,
    .nav-mega-cta .btn-soft {
        width: 100%;
        padding: 9px 12px;
        font-size: 13px;
        gap: 6px;
    }

    /* ========== Live search suggest ========== */
    .header-search-wrap {
        position: relative;
    }

    .search-suggest {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: 420px;
        max-width: 92vw;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 14px;
        box-shadow: 0 24px 50px rgba(67, 97, 238, 0.22);
        overflow: hidden;
        z-index: 60;
        animation: searchSuggestIn 0.2s ease;
    }

    @keyframes searchSuggestIn {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .search-suggest-status {
        padding: 14px 16px;
        background: var(--brand-tint);
        color: var(--brand-dark);
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .search-suggest-section {
        padding: 10px 6px;
    }

    .search-suggest-section + .search-suggest-section {
        border-top: 1px dashed var(--line);
    }

    .search-suggest-section h4 {
        font-size: 11px;
        text-transform: uppercase;
        color: var(--muted);
        font-weight: 800;
        letter-spacing: 0.8px;
        padding: 4px 10px;
        margin: 0 0 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .suggest-row {
        display: flex;
        gap: 10px;
        align-items: center;
        padding: 8px 10px;
        border-radius: 10px;
        text-decoration: none;
        color: var(--ink);
        transition: background 0.15s ease;
    }

    .suggest-row:hover {
        background: var(--soft);
        color: var(--ink);
    }

    .suggest-row img,
    .suggest-row .suggest-init {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        object-fit: cover;
        background: var(--brand-tint);
        flex-shrink: 0;
        display: grid;
        place-items: center;
        color: var(--brand);
        font-weight: 800;
        font-size: 16px;
    }

    .suggest-row .suggest-copy {
        flex: 1;
        min-width: 0;
    }

    .suggest-row .suggest-copy strong {
        display: block;
        font-size: 13.5px;
        line-height: 1.3;
        color: var(--ink);
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .suggest-row .suggest-copy small {
        display: block;
        margin-top: 2px;
        font-size: 11.5px;
        color: var(--muted);
        font-weight: 600;
    }

    .suggest-tag {
        display: inline-block;
        padding: 2px 6px;
        background: var(--brand-tint);
        color: var(--brand);
        border-radius: 4px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .search-suggest-empty {
        padding: 30px 20px;
        text-align: center;
        color: var(--muted);
        font-size: 13px;
    }

    .search-suggest-empty i {
        font-size: 24px;
        color: var(--brand);
        opacity: 0.5;
        margin-bottom: 8px;
        display: block;
    }

    /* ========== Notification bell + popover ========== */
    .header-notif-wrap,
    .header-user-wrap {
        position: relative;
    }

    .header-icon-btn {
        position: relative;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #fff;
        border: 1px solid var(--line);
        color: var(--brand);
        cursor: pointer;
        display: grid;
        place-items: center;
        font-size: 16px;
        transition: all 0.2s ease;
    }

    .header-icon-btn:hover {
        background: var(--brand-tint);
        border-color: var(--brand);
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(67, 97, 238, 0.16);
    }

    .header-icon-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        border-radius: 999px;
        background: var(--danger);
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        display: grid;
        place-items: center;
        box-shadow: 0 0 0 2px #fff;
        animation: badgePop 0.3s ease;
    }

    @keyframes badgePop {
        from { transform: scale(0); }
        to { transform: scale(1); }
    }

    .notif-popover,
    .user-popover {
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        width: 360px;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 14px;
        box-shadow: 0 24px 50px rgba(67, 97, 238, 0.2);
        overflow: hidden;
        z-index: 60;
        animation: searchSuggestIn 0.2s ease;
    }

    .notif-popover-head,
    .user-popover-head {
        padding: 14px 18px;
        background: linear-gradient(135deg, var(--brand-tint) 0%, #e0f2fe 100%);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid var(--line);
    }

    .notif-popover-head strong {
        font-size: 15px;
        color: var(--ink);
    }

    .link-mini {
        font-size: 12px;
        color: var(--brand);
        text-decoration: none;
        font-weight: 700;
    }

    .link-mini:hover { color: var(--brand-dark); }

    .notif-popover-body {
        max-height: 380px;
        overflow-y: auto;
    }

    .notif-loading,
    .notif-empty {
        padding: 28px 20px;
        text-align: center;
        color: var(--muted);
        font-size: 13px;
    }

    .notif-empty i {
        font-size: 32px;
        color: var(--brand);
        opacity: 0.4;
        display: block;
        margin-bottom: 10px;
    }

    .notif-item {
        display: flex;
        gap: 10px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--line);
        text-decoration: none;
        color: var(--ink);
        transition: background 0.15s ease;
        position: relative;
    }

    .notif-item:hover { background: var(--soft); color: var(--ink); }
    .notif-item:last-child { border-bottom: 0; }

    .notif-item.is-unread {
        background: rgba(67, 97, 238, 0.04);
    }

    .notif-item.is-unread::before {
        content: '';
        position: absolute;
        left: 6px;
        top: 50%;
        transform: translateY(-50%);
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--danger);
    }

    .notif-icon-wrap {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: var(--brand-tint);
        color: var(--brand);
        display: grid;
        place-items: center;
        flex-shrink: 0;
        font-size: 14px;
    }

    .notif-content {
        flex: 1;
        min-width: 0;
    }

    .notif-content strong {
        display: block;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.35;
        color: var(--ink);
        margin-bottom: 2px;
    }

    .notif-content p {
        font-size: 12px;
        color: var(--muted);
        line-height: 1.45;
        margin: 0 0 4px;
    }

    .notif-content time {
        font-size: 11px;
        color: var(--brand);
        font-weight: 700;
    }

    /* ========== User avatar dropdown ========== */
    .account-chip.is-button {
        border: 1px solid rgba(67, 97, 238, 0.16);
        background: linear-gradient(135deg, #ffffff 0%, #eef2ff 100%);
        cursor: pointer;
        font-family: inherit;
        position: relative;
        padding-right: 14px;
    }

    .account-caret {
        font-size: 11px;
        color: var(--muted);
        margin-left: 4px;
        transition: transform 0.2s ease;
    }

    .header-user-wrap.is-open .account-caret {
        transform: rotate(180deg);
    }

    .user-popover-head {
        align-items: flex-start;
        padding: 18px;
    }

    .user-popover-head .account-avatar.lg {
        width: 56px;
        height: 56px;
        font-size: 22px;
        flex-shrink: 0;
    }

    .user-popover-head > div {
        flex: 1;
        min-width: 0;
    }

    .user-popover-head strong {
        display: block;
        font-size: 15px;
        color: var(--ink);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-popover-head small {
        display: block;
        font-size: 12px;
        color: var(--muted);
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-popover-role {
        display: inline-block;
        margin-top: 6px;
        padding: 2px 8px;
        background: rgba(67, 97, 238, 0.12);
        color: var(--brand);
        font-size: 11px;
        font-weight: 800;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .user-popover-list {
        padding: 8px;
    }

    .user-popover-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 10px;
        text-decoration: none;
        color: var(--ink);
        font-size: 13.5px;
        font-weight: 600;
        transition: all 0.15s ease;
    }

    .user-popover-item:hover {
        background: var(--brand-tint);
        color: var(--brand-dark);
        transform: translateX(2px);
    }

    .user-popover-item i {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--soft);
        color: var(--brand);
        display: grid;
        place-items: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    .user-popover-item:hover i {
        background: var(--brand);
        color: #fff;
    }

    .user-popover-foot {
        padding: 10px;
        border-top: 1px solid var(--line);
        background: var(--soft);
    }

    .user-popover-logout {
        width: 100%;
        padding: 10px;
        border: 0;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--danger) 0%, #d11d6f 100%);
        color: #fff;
        font-weight: 800;
        font-size: 13.5px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .user-popover-logout:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(247, 37, 133, 0.3);
    }

    /* ========== CTA pulse animation ========== */
    .btn-pulse {
        position: relative;
        overflow: visible;
    }

    .btn-pulse::after {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: inherit;
        border: 2px solid var(--brand);
        animation: btnPulseRing 2s ease-out infinite;
        pointer-events: none;
    }

    @keyframes btnPulseRing {
        0% { opacity: 1; transform: scale(1); }
        100% { opacity: 0; transform: scale(1.18); }
    }

    /* ========== Responsive cho navbar nâng cấp ========== */
    @media (max-width: 1280px) {
        .nav-mega-menu { width: 820px; }
        .header-icon-btn { width: 40px; height: 40px; }
    }

    @media (max-width: 1080px) {
        .nav-mega-menu { width: 720px; padding: 16px; }
        .nav-mega-grid { grid-template-columns: 1fr 1fr; }
        .nav-mega-feature { grid-column: 1 / -1; }
        .nav-mega-hero { aspect-ratio: 16/6; }

        .user-popover-head small,
        .account-chip.is-button .account-copy small {
            display: none;
        }
    }

    @media (max-width: 820px) {
        .nav-mega-menu {
            width: 96vw;
            position: fixed;
            left: 50%;
            top: 88px;
            transform: translateX(-50%) translateY(-6px);
            padding: 16px;
        }
        .nav-dropdown.is-mega:hover > .nav-mega-menu,
        .nav-dropdown.is-mega:focus-within > .nav-mega-menu {
            transform: translateX(-50%) translateY(0);
        }
        .nav-mega-grid { grid-template-columns: 1fr; }
        .nav-mega-menu::before { display: none; }
        .search-suggest, .notif-popover, .user-popover {
            position: fixed;
            top: 70px;
            right: 8px;
            left: 8px;
            width: auto;
            max-width: none;
        }
    }

    /* ======================================================
       HERO HUB — NÂNG CẤP
       ====================================================== */

    /* Decorative floating blobs + sparkles */
    .hub-decor {
        position: absolute;
        inset: 0;
        overflow: hidden;
        pointer-events: none;
        z-index: 0;
    }

    .hub-blob {
        position: absolute;
        border-radius: 50%;
        filter: blur(60px);
        opacity: 0.55;
        animation: hubBlobFloat 14s ease-in-out infinite;
    }

    .hub-blob-1 {
        width: 320px;
        height: 320px;
        top: -80px;
        right: 10%;
        background: radial-gradient(circle, rgba(76, 201, 240, 0.6) 0%, transparent 70%);
        animation-delay: 0s;
    }

    .hub-blob-2 {
        width: 380px;
        height: 380px;
        bottom: -120px;
        left: 8%;
        background: radial-gradient(circle, rgba(67, 97, 238, 0.55) 0%, transparent 70%);
        animation-delay: -4s;
    }

    .hub-blob-3 {
        width: 220px;
        height: 220px;
        top: 30%;
        right: 28%;
        background: radial-gradient(circle, rgba(251, 191, 36, 0.35) 0%, transparent 70%);
        animation-delay: -8s;
        opacity: 0.45;
    }

    @keyframes hubBlobFloat {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(30px, -20px) scale(1.06); }
        66% { transform: translate(-20px, 16px) scale(0.96); }
    }

    .hub-sparkle {
        position: absolute;
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.85);
        box-shadow: 0 0 12px 3px rgba(255, 255, 255, 0.6);
        animation: hubSparkle 3s ease-in-out infinite;
    }

    .hub-sparkle-1 { top: 18%; left: 22%; animation-delay: 0s; }
    .hub-sparkle-2 { top: 38%; right: 12%; animation-delay: -0.8s; }
    .hub-sparkle-3 { bottom: 28%; left: 42%; animation-delay: -1.6s; width: 6px; height: 6px; }
    .hub-sparkle-4 { top: 62%; right: 36%; animation-delay: -2.2s; }

    @keyframes hubSparkle {
        0%, 100% { opacity: 0; transform: scale(0.4); }
        50% { opacity: 1; transform: scale(1.2); }
    }

    /* Eyebrow shine + dot */
    .eyebrow-shine {
        position: relative;
        overflow: hidden;
        gap: 8px;
    }

    .eyebrow-shine::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 60%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
        animation: eyebrowShine 3.5s ease-in-out infinite;
    }

    @keyframes eyebrowShine {
        0% { left: -100%; }
        50%, 100% { left: 130%; }
    }

    .eyebrow-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--success);
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
        animation: eyebrowDot 1.6s ease-out infinite;
        flex-shrink: 0;
    }

    @keyframes eyebrowDot {
        0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }

    /* Trust bar */
    .hub-trust-bar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 18px;
        margin: 18px 0 22px;
        padding: 12px 16px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 14px;
        backdrop-filter: blur(12px);
        font-size: 13px;
        color: rgba(255, 255, 255, 0.92);
    }

    .trust-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .trust-item strong {
        color: #fff;
        font-weight: 800;
    }

    .trust-divider {
        width: 1px;
        height: 24px;
        background: rgba(255, 255, 255, 0.22);
    }

    .trust-stars {
        display: flex;
        gap: 2px;
        color: var(--sun);
        font-size: 13px;
    }

    .trust-avatars {
        display: flex;
        align-items: center;
    }

    .trust-avatars img,
    .trust-avatars .trust-avatar-fallback {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 2px solid #fff;
        background: var(--brand);
        margin-left: -8px;
        object-fit: cover;
        display: grid;
        place-items: center;
        color: #fff;
        font-weight: 800;
        font-size: 11px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);
    }

    .trust-avatars > *:first-child { margin-left: 0; }

    .trust-shield {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--success) 0%, #16a34a 100%);
        color: #fff;
        display: grid;
        place-items: center;
        font-size: 12px;
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
    }

    /* Ghost light button (CTA thứ 3) */
    .btn-ghost-light {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        min-height: 40px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.22);
        color: #fff;
        font-weight: 700;
        font-size: 14px;
        text-decoration: none;
        backdrop-filter: blur(10px);
        transition: all 0.2s ease;
    }

    .btn-ghost-light:hover {
        background: rgba(255, 255, 255, 0.18);
        border-color: rgba(255, 255, 255, 0.4);
        color: #fff;
        transform: translateY(-2px);
    }

    .btn-ghost-light i { color: var(--sun); }

    .hub-panel {
        position: relative;
    }

    /* Strip uy tín 2 cột thay cho floating deco cards (gọn trong panel, không lệch layout) */
    .panel-trust-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        padding: 4px 0;
    }

    .panel-trust-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 12px;
        backdrop-filter: blur(8px);
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .panel-trust-item:hover {
        background: rgba(255, 255, 255, 0.16);
        transform: translateY(-1px);
    }

    .ptr-icon {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        color: #fff;
        font-size: 14px;
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.18);
    }

    .ptr-icon-warm { background: linear-gradient(135deg, var(--sun) 0%, #f59e0b 100%); }
    .ptr-icon-success { background: linear-gradient(135deg, var(--success) 0%, #16a34a 100%); }

    .ptr-copy {
        display: flex;
        flex-direction: column;
        line-height: 1.25;
        min-width: 0;
    }

    .ptr-copy strong {
        font-size: 13px;
        font-weight: 800;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ptr-copy small {
        font-size: 10.5px;
        color: rgba(255, 255, 255, 0.72);
        font-weight: 600;
    }

    /* Hub panel header live dot */
    .panel-live-dot {
        margin-left: auto;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--success);
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
        animation: eyebrowDot 1.6s ease-out infinite;
    }

    /* Hub search box upgrade */
    .hub-search-box {
        position: relative;
    }

    .hub-search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--brand);
        z-index: 1;
        font-size: 14px;
        pointer-events: none;
    }

    .hub-search-box input {
        padding-left: 38px !important;
    }

    .hub-search-box button {
        gap: 6px;
    }

    /* Trending tags */
    .hub-trending {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin: 6px 0 14px;
    }

    .hub-trending-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #fff;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .hub-trending-label i {
        color: var(--sun);
        font-size: 13px;
        text-shadow: 0 0 12px rgba(251, 191, 36, 0.7);
        animation: tagFireGlow 1.6s ease-in-out infinite;
    }

    @keyframes tagFireGlow {
        0%, 100% { transform: scale(1); text-shadow: 0 0 12px rgba(251, 191, 36, 0.6); }
        50% { transform: scale(1.15); text-shadow: 0 0 16px rgba(251, 191, 36, 1); }
    }

    .hub-trending-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .hub-tag {
        display: inline-flex;
        align-items: center;
        padding: 5px 12px;
        background: rgba(255, 255, 255, 0.95);
        color: var(--brand-dark);
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
        transition: all 0.2s ease;
    }

    .hub-tag:hover {
        background: var(--sun);
        color: #78350f;
        border-color: var(--sun);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(251, 191, 36, 0.5);
    }

    /* Hub highlight card upgrade — link, hot badge, mini stats */
    .hub-highlight-link {
        display: block;
        text-decoration: none;
        color: inherit;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .hub-highlight-link:hover {
        transform: translateY(-3px);
        color: inherit;
    }

    .hub-highlight-link .card-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .card-label-flame {
        animation: flameWiggle 1.6s ease-in-out infinite;
        display: inline-block;
    }

    @keyframes flameWiggle {
        0%, 100% { transform: rotate(-6deg); }
        50% { transform: rotate(6deg); }
    }

    .card-thumb {
        position: relative;
        flex-shrink: 0;
    }

    .card-hot-badge {
        position: absolute;
        top: -6px;
        left: -6px;
        padding: 3px 8px;
        background: linear-gradient(135deg, var(--danger) 0%, #d11d6f 100%);
        color: #fff;
        font-size: 10px;
        font-weight: 900;
        border-radius: 6px;
        letter-spacing: 0.8px;
        box-shadow: 0 4px 10px rgba(247, 37, 133, 0.45);
        animation: hotBadgePulse 1.6s ease-in-out infinite;
    }

    @keyframes hotBadgePulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.08); }
    }

    .card-mini-stats {
        display: flex;
        gap: 10px;
        margin-top: 6px;
        font-size: 11px;
        color: var(--muted);
        font-weight: 600;
    }

    .card-mini-stats span {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .card-mini-stats i {
        color: var(--brand);
        font-size: 10px;
    }

    /* Hub stats counter prep — sẽ animate từ 0 lên giá trị thật */
    .stat-box strong {
        font-variant-numeric: tabular-nums;
    }

    /* Wave divider */
    .hub-wave {
        position: relative;
        line-height: 0;
        margin-top: -1px;
    }

    .hub-wave svg {
        display: block;
        width: 100%;
        height: 80px;
    }

    /* Responsive nâng cấp hero */
    @media (max-width: 1080px) {
        .hub-trust-bar { font-size: 12px; gap: 12px; }
        .trust-divider { display: none; }
        .btn-ghost-light { display: none; }
        .ptr-copy strong { font-size: 12px; }
        .ptr-copy small { font-size: 10px; }
    }

    @media (max-width: 820px) {
        .hub-trust-bar { padding: 10px 12px; }
        .panel-trust-row { grid-template-columns: 1fr; }
        .hub-blob { opacity: 0.35; }
        .hub-wave svg { height: 50px; }
    }
</style>
@endpush
