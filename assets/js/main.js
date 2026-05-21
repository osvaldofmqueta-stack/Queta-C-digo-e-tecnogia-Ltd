// =========================================
// QUETA CÓDIGO E TECNOLOGIA — MAIN JS
// =========================================

document.addEventListener('DOMContentLoaded', function () {
    initScrollProgress();
    initPageLoader();
    initCookieBanner();
    initTheme();
    initCarousel();
    initStickyHeader();
    initMobileMenu();
    initSearchFilter();
    initSmoothScroll();
    initClientNotif();
    initScrollReveal();
    initCounterAnimation();
    initRippleEffect();
    initParticleHero();
    initTypewriter();
    initTiltCards();
    initVisitorCounter();
});

// =========================================
// PAGE LOADER
// =========================================
function initPageLoader() {
    const loader = document.getElementById('page-loader');
    if (!loader) return;
    window.addEventListener('load', () => {
        setTimeout(() => {
            loader.classList.add('loaded');
            setTimeout(() => loader.remove(), 500);
        }, 400);
    });
    setTimeout(() => {
        loader.classList.add('loaded');
        setTimeout(() => loader.remove(), 500);
    }, 2000);
}

// =========================================
// SCROLL PROGRESS BAR
// =========================================
function initScrollProgress() {
    const bar = document.getElementById('scroll-progress');
    if (!bar) return;
    window.addEventListener('scroll', () => {
        const scrollTop = window.pageYOffset;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const pct = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
        bar.style.width = pct + '%';
    }, { passive: true });
}

// =========================================
// SCROLL REVEAL (Intersection Observer)
// =========================================
function initScrollReveal() {
    const elements = document.querySelectorAll('.reveal');
    if (!elements.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    elements.forEach((el, i) => {
        const delay = el.dataset.delay || (i % 4) * 80;
        el.style.transitionDelay = delay + 'ms';
        observer.observe(el);
    });
}

// =========================================
// COUNTER ANIMATION
// =========================================
function initCounterAnimation() {
    const counters = document.querySelectorAll('.counter-num');
    if (!counters.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => observer.observe(counter));
}

function animateCounter(el) {
    const target = parseInt(el.dataset.target || el.textContent, 10);
    const suffix = el.dataset.suffix || '';
    const duration = 1800;
    const start = performance.now();

    function update(now) {
        const elapsed = now - start;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = Math.round(eased * target);
        el.textContent = current.toLocaleString('pt-PT') + suffix;
        if (progress < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
}

// =========================================
// PARTICLE HERO
// =========================================
function initParticleHero() {
    const canvas = document.getElementById('hero-particles');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let particles = [];
    let animFrame;

    function resize() {
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
    }

    function createParticles() {
        particles = [];
        const count = Math.floor((canvas.width * canvas.height) / 12000);
        for (let i = 0; i < count; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                r: Math.random() * 2 + 0.5,
                vx: (Math.random() - 0.5) * 0.4,
                vy: (Math.random() - 0.5) * 0.4,
                alpha: Math.random() * 0.4 + 0.1
            });
        }
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        particles.forEach(p => {
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(255,255,255,${p.alpha})`;
            ctx.fill();

            p.x += p.vx;
            p.y += p.vy;

            if (p.x < 0) p.x = canvas.width;
            if (p.x > canvas.width) p.x = 0;
            if (p.y < 0) p.y = canvas.height;
            if (p.y > canvas.height) p.y = 0;
        });

        // Connect nearby particles
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 100) {
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = `rgba(255,255,255,${0.08 * (1 - dist / 100)})`;
                    ctx.lineWidth = 0.5;
                    ctx.stroke();
                }
            }
        }

        animFrame = requestAnimationFrame(draw);
    }

    resize();
    createParticles();
    draw();

    window.addEventListener('resize', () => {
        resize();
        createParticles();
    });
}

// =========================================
// TYPEWRITER EFFECT
// =========================================
function initTypewriter() {
    const el = document.getElementById('typewriter');
    if (!el) return;

    const words = el.dataset.words ? el.dataset.words.split('|') : [];
    if (!words.length) return;

    let wordIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    let timeout;

    function type() {
        const word = words[wordIndex % words.length];
        const current = isDeleting
            ? word.substring(0, charIndex - 1)
            : word.substring(0, charIndex + 1);

        el.textContent = current;

        if (!isDeleting && current === word) {
            isDeleting = true;
            timeout = setTimeout(type, 2000);
            return;
        }
        if (isDeleting && current === '') {
            isDeleting = false;
            wordIndex++;
            timeout = setTimeout(type, 300);
            return;
        }

        charIndex = isDeleting ? charIndex - 1 : charIndex + 1;
        timeout = setTimeout(type, isDeleting ? 60 : 100);
    }

    setTimeout(type, 1200);
}

// =========================================
// RIPPLE EFFECT ON BUTTONS
// =========================================
function initRippleEffect() {
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-primary, .btn-secondary, .plano-btn, .btn-footer-demo');
        if (!btn) return;

        const ripple = document.createElement('span');
        ripple.className = 'btn-ripple';
        const rect = btn.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        ripple.style.cssText = `
            width:${size}px; height:${size}px;
            left:${e.clientX - rect.left - size / 2}px;
            top:${e.clientY - rect.top - size / 2}px;
        `;
        btn.style.position = 'relative';
        btn.style.overflow = 'hidden';
        btn.appendChild(ripple);
        setTimeout(() => ripple.remove(), 700);
    });
}

// =========================================
// TILT EFFECT ON CARDS
// =========================================
function initTiltCards() {
    const cards = document.querySelectorAll('.plano-card, .app-card');
    cards.forEach(card => {
        card.addEventListener('mousemove', e => {
            const rect = card.getBoundingClientRect();
            const cx = rect.left + rect.width / 2;
            const cy = rect.top + rect.height / 2;
            const dx = (e.clientX - cx) / (rect.width / 2);
            const dy = (e.clientY - cy) / (rect.height / 2);
            card.style.transform = `perspective(800px) rotateY(${dx * 4}deg) rotateX(${-dy * 4}deg) translateY(-4px)`;
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
            card.style.transition = 'transform 0.4s ease';
            setTimeout(() => card.style.transition = '', 400);
        });
    });
}

// =========================================
// VISITOR COUNTER
// =========================================
function initVisitorCounter() {
    const el = document.getElementById('visitor-count');
    if (!el) return;

    const fd = new FormData();
    fd.append('acao', 'visita');
    fd.append('pagina', window.location.pathname);

    fetch('/api/chat.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.total !== undefined) {
                el.textContent = d.total.toLocaleString('pt-PT');
            }
        })
        .catch(() => {
            fetch('/api/chat.php?acao=total_visitas')
                .then(r => r.json())
                .then(d => { if (d.total !== undefined) el.textContent = d.total.toLocaleString('pt-PT'); });
        });
}

// =========================================
// COOKIE BANNER
// =========================================
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

// =========================================
// CAROUSEL — Crossfade + Ken Burns + Progress
// =========================================
function initCarousel() {
    const carousel = document.querySelector('.hero-carousel');
    if (!carousel) return;

    const slides = carousel.querySelectorAll('.carousel-slide');
    const progBars = carousel.querySelectorAll('.carousel-prog-bar');
    const counter = carousel.querySelector('.carousel-counter-current');
    if (slides.length === 0) return;

    let current = 0;
    let autoPlay;
    let isTransitioning = false;
    const DURATION = 5500;

    function pad(n) { return String(n).padStart(2, '0'); }

    function updateProgress(idx) {
        progBars.forEach((b, i) => {
            b.classList.toggle('active', i === idx);
            // restart animation
            const fill = b.querySelector('.carousel-prog-bar-fill');
            if (fill) {
                fill.style.animation = 'none';
                fill.offsetHeight; // reflow
                if (i === idx) fill.style.animation = `progFill ${DURATION}ms linear forwards`;
                else fill.style.animation = 'none';
            }
        });
        if (counter) counter.textContent = pad(idx + 1);
    }

    function goTo(index) {
        if (isTransitioning && slides.length > 1) return;
        isTransitioning = true;
        const prev = current;
        current = (index + slides.length) % slides.length;

        // Crossfade
        slides[prev].classList.remove('active-slide');
        slides[current].classList.add('active-slide');

        updateProgress(current);
        setTimeout(() => { isTransitioning = false; }, 900);
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function startAutoPlay() { autoPlay = setInterval(next, DURATION); }
    function stopAutoPlay() { clearInterval(autoPlay); }

    // Progress bar click
    progBars.forEach((b, i) => b.addEventListener('click', () => { stopAutoPlay(); goTo(i); startAutoPlay(); }));

    // Arrow buttons
    const prevBtn = carousel.querySelector('.carousel-btn.prev');
    const nextBtn = carousel.querySelector('.carousel-btn.next');
    if (prevBtn) prevBtn.addEventListener('click', () => { stopAutoPlay(); prev(); startAutoPlay(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { stopAutoPlay(); next(); startAutoPlay(); });

    // Touch / swipe
    let touchStartX = 0;
    carousel.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; stopAutoPlay(); }, { passive: true });
    carousel.addEventListener('touchend', e => {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) diff > 0 ? next() : prev();
        startAutoPlay();
    });

    // Keyboard
    document.addEventListener('keydown', e => {
        if (e.key === 'ArrowLeft') { stopAutoPlay(); prev(); startAutoPlay(); }
        if (e.key === 'ArrowRight') { stopAutoPlay(); next(); startAutoPlay(); }
    });

    // Init
    slides.forEach((s, i) => { if (i !== 0) s.classList.remove('active-slide'); });
    slides[0].classList.add('active-slide');
    updateProgress(0);
    startAutoPlay();
}

// =========================================
// STICKY HEADER
// =========================================
function initStickyHeader() {
    const header = document.getElementById('site-header');
    if (!header) return;
    window.addEventListener('scroll', () => {
        header.classList.toggle('scrolled', window.scrollY > 20);
    }, { passive: true });
}

// =========================================
// MOBILE MENU (Modern Drawer)
// =========================================
function toggleMobileMenu() {
    const drawer = document.getElementById('mobile-drawer');
    const toggle = document.getElementById('nav-toggle');
    const overlay = document.getElementById('nav-overlay');
    if (!drawer) return;
    const isOpen = drawer.classList.contains('open');
    if (isOpen) {
        closeMobileMenu();
    } else {
        drawer.classList.add('open');
        toggle && toggle.classList.add('open');
        overlay && overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeMobileMenu() {
    const drawer = document.getElementById('mobile-drawer');
    const toggle = document.getElementById('nav-toggle');
    const overlay = document.getElementById('nav-overlay');
    if (drawer) drawer.classList.remove('open');
    if (toggle) toggle.classList.remove('open');
    if (overlay) overlay.classList.remove('active');
    document.body.style.overflow = '';
}

function initMobileMenu() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeMobileMenu();
    });
}

// Legacy compat
function toggleMenu() { toggleMobileMenu(); }

// =========================================
// SEARCH FILTER
// =========================================
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

// =========================================
// SMOOTH SCROLL
// =========================================
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                const offset = 80;
                const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({ top, behavior: 'smooth' });
                closeMobileMenu();
            }
        });
    });
}

// =========================================
// MODAL
// =========================================
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

// =========================================
// CONFIRM DELETE
// =========================================
function confirmDelete(url, msg) {
    if (confirm(msg || 'Tem certeza que deseja eliminar este item?')) {
        window.location.href = url;
    }
}

// =========================================
// VIDEO PLAYER
// =========================================
function playVideo(videoUrl) {
    const wrapper = document.getElementById('video-wrapper');
    if (wrapper && videoUrl) {
        wrapper.innerHTML = `<iframe src="${videoUrl}?autoplay=1" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width:100%;height:100%;border:none;"></iframe>`;
    }
}

// =========================================
// TOGGLE ACTIVE
// =========================================
function toggleActive(el) {
    if (el) el.classList.toggle('active');
}

// =========================================
// IMAGE PREVIEW
// =========================================
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0] && preview) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    }
}

// =========================================
// CLIENT SOCIAL PROOF NOTIFICATION
// =========================================
let _notifIndex = 0;
let _notifTimer = null;
let _notifClosed = false;

function initClientNotif() {
    if (typeof _clientesNotif === 'undefined' || !_clientesNotif.length) return;
    if (sessionStorage.getItem('notif_closed')) return;
    setTimeout(showNextNotif, 4000);
}

function showNextNotif() {
    if (_notifClosed || typeof _clientesNotif === 'undefined') return;
    const widget = document.getElementById('client-notif');
    if (!widget) return;

    const c = _clientesNotif[_notifIndex % _clientesNotif.length];
    _notifIndex++;

    const imgEl      = document.getElementById('notif-logo-img');
    const initialsEl = document.getElementById('notif-logo-initials');
    const escolaEl   = document.getElementById('notif-escola');
    const planEl     = document.getElementById('notif-plan');
    const locEl      = document.getElementById('notif-loc');

    if (c.logo) {
        imgEl.src = '/' + c.logo;
        imgEl.style.display = 'block';
        initialsEl.style.display = 'none';
    } else {
        imgEl.style.display = 'none';
        initialsEl.textContent = c.nome_escola.substring(0, 2).toUpperCase();
        initialsEl.style.background = c.plano_cor;
        initialsEl.style.display = 'flex';
    }

    escolaEl.textContent = c.nome_escola;
    planEl.innerHTML = `<span style="background:${c.plano_cor};color:white;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">${c.plano_emoji} ${c.plano}</span>`;
    locEl.innerHTML = c.localizacao ? `<i class="fas fa-map-marker-alt"></i> ${c.localizacao}` : '';

    widget.classList.remove('notif-hiding');
    widget.classList.add('notif-visible');

    _notifTimer = setTimeout(() => {
        widget.classList.add('notif-hiding');
        setTimeout(() => {
            widget.classList.remove('notif-visible', 'notif-hiding');
            setTimeout(showNextNotif, 2000);
        }, 500);
    }, 5500);
}

function closeClientNotif() {
    _notifClosed = true;
    sessionStorage.setItem('notif_closed', '1');
    clearTimeout(_notifTimer);
    const widget = document.getElementById('client-notif');
    if (widget) { widget.classList.add('notif-hiding'); setTimeout(() => widget.classList.remove('notif-visible','notif-hiding'), 500); }
}

// =========================================
// SHOW ALERT
// =========================================
function showAlert(msg, type = 'success') {
    const div = document.createElement('div');
    div.className = `alert alert-${type}`;
    div.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${msg}`;
    const content = document.querySelector('.admin-content') || document.querySelector('.page-content');
    if (content) { content.prepend(div); setTimeout(() => div.remove(), 5000); }
}

// =========================================
// DARK / LIGHT THEME
// =========================================
function initTheme() {
    const saved = localStorage.getItem('theme') || 'light';
    applyTheme(saved, false);
}

function toggleTheme() {
    const current = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    applyTheme(next, true);
    localStorage.setItem('theme', next);
}

function applyTheme(theme, animate) {
    const body = document.body;
    const icon = document.getElementById('theme-icon');
    if (animate) {
        body.style.transition = 'background 0.35s ease, color 0.35s ease';
        setTimeout(() => { body.style.transition = ''; }, 400);
    }
    if (theme === 'dark') {
        body.classList.add('dark-mode');
        if (icon) { icon.className = 'fas fa-sun'; }
    } else {
        body.classList.remove('dark-mode');
        if (icon) { icon.className = 'fas fa-moon'; }
    }
}

// CSS helpers
const _dynStyle = document.createElement('style');
_dynStyle.textContent = `
    @keyframes slideDown { from { transform: translateY(0); } to { transform: translateY(100%); } }
    .btn-ripple {
        position: absolute; border-radius: 50%;
        background: rgba(255,255,255,0.3);
        transform: scale(0);
        animation: rippleAnim 0.65s linear;
        pointer-events: none; z-index: 10;
    }
    @keyframes rippleAnim {
        to { transform: scale(3); opacity: 0; }
    }
`;
document.head.appendChild(_dynStyle);
