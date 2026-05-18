// =========================================
// QUETA CÓDIGO E TECNOLOGIA — MAIN JS
// =========================================

document.addEventListener('DOMContentLoaded', function () {
    initCookieBanner();
    initCarousel();
    initStickyHeader();
    initMobileMenu();
    initSearchFilter();
    initSmoothScroll();
});

// COOKIE BANNER
function initCookieBanner() {
    const consent = localStorage.getItem('cookie_consent');
    if (!consent) {
        setTimeout(() => {
            const banner = document.getElementById('cookie-banner');
            if (banner) banner.style.display = 'block';
        }, 1500);
    }
}

function acceptCookies() {
    localStorage.setItem('cookie_consent', 'accepted');
    hideCookieBanner();
}

function rejectCookies() {
    localStorage.setItem('cookie_consent', 'rejected');
    hideCookieBanner();
}

function hideCookieBanner() {
    const banner = document.getElementById('cookie-banner');
    if (banner) {
        banner.style.animation = 'slideDown 0.3s ease forwards';
        setTimeout(() => banner.style.display = 'none', 300);
    }
}

// CAROUSEL
function initCarousel() {
    const carousel = document.querySelector('.hero-carousel');
    if (!carousel) return;

    const track = carousel.querySelector('.carousel-track');
    const slides = carousel.querySelectorAll('.carousel-slide');
    const dots = carousel.querySelectorAll('.carousel-dot');
    if (!track || slides.length === 0) return;

    let current = 0;
    let autoPlay;

    function goTo(index) {
        current = (index + slides.length) % slides.length;
        track.style.transform = `translateX(-${current * 100}%)`;
        dots.forEach((d, i) => d.classList.toggle('active', i === current));
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function startAutoPlay() {
        autoPlay = setInterval(next, 5000);
    }

    function stopAutoPlay() {
        clearInterval(autoPlay);
    }

    dots.forEach((dot, i) => dot.addEventListener('click', () => { stopAutoPlay(); goTo(i); startAutoPlay(); }));

    const prevBtn = carousel.querySelector('.carousel-btn.prev');
    const nextBtn = carousel.querySelector('.carousel-btn.next');
    if (prevBtn) prevBtn.addEventListener('click', () => { stopAutoPlay(); prev(); startAutoPlay(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { stopAutoPlay(); next(); startAutoPlay(); });

    // Touch swipe
    let touchStartX = 0;
    carousel.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; stopAutoPlay(); }, { passive: true });
    carousel.addEventListener('touchend', e => {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) diff > 0 ? next() : prev();
        startAutoPlay();
    });

    goTo(0);
    startAutoPlay();
}

// STICKY HEADER
function initStickyHeader() {
    const header = document.getElementById('site-header');
    if (!header) return;
    window.addEventListener('scroll', () => {
        header.classList.toggle('scrolled', window.scrollY > 20);
    });
}

// MOBILE MENU
function toggleMenu() {
    const menu = document.getElementById('nav-menu');
    const toggle = document.getElementById('nav-toggle');
    if (!menu) return;
    menu.classList.toggle('open');
    toggle.classList.toggle('open');
    document.body.style.overflow = menu.classList.contains('open') ? 'hidden' : '';
}

function initMobileMenu() {
    document.addEventListener('click', function (e) {
        const menu = document.getElementById('nav-menu');
        const toggle = document.getElementById('nav-toggle');
        if (menu && toggle && !menu.contains(e.target) && !toggle.contains(e.target) && menu.classList.contains('open')) {
            menu.classList.remove('open');
            toggle.classList.remove('open');
            document.body.style.overflow = '';
        }
    });
}

// SEARCH FILTER
function initSearchFilter() {
    const searchInput = document.getElementById('search-input');
    if (!searchInput) return;
    const items = document.querySelectorAll('[data-searchable]');
    searchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = q === '' || text.includes(q) ? '' : 'none';
        });
    });
}

// SMOOTH SCROLL
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                const offset = 80;
                const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({ top, behavior: 'smooth' });
                const menu = document.getElementById('nav-menu');
                if (menu) { menu.classList.remove('open'); document.body.style.overflow = ''; }
            }
        });
    });
}

// MODAL
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) { modal.classList.add('active'); document.body.style.overflow = 'hidden'; }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) { modal.classList.remove('active'); document.body.style.overflow = ''; }
}

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// CONFIRM DELETE
function confirmDelete(url, msg) {
    if (confirm(msg || 'Tem certeza que deseja eliminar este item?')) {
        window.location.href = url;
    }
}

// VIDEO PLAYER
function playVideo(videoUrl) {
    const wrapper = document.getElementById('video-wrapper');
    if (wrapper && videoUrl) {
        wrapper.innerHTML = `<iframe src="${videoUrl}?autoplay=1" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
    }
}

// TOGGLE ACTIVE
function toggleActive(el) {
    if (el) el.classList.toggle('active');
}

// IMAGE PREVIEW
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0] && preview) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    }
}

// SHOW ALERT
function showAlert(msg, type = 'success') {
    const div = document.createElement('div');
    div.className = `alert alert-${type}`;
    div.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${msg}`;
    const content = document.querySelector('.admin-content') || document.querySelector('.page-content');
    if (content) { content.prepend(div); setTimeout(() => div.remove(), 5000); }
}

// CSS animation for cookie banner hide
const style = document.createElement('style');
style.textContent = `@keyframes slideDown { from { transform: translateY(0); } to { transform: translateY(100%); } }`;
document.head.appendChild(style);
