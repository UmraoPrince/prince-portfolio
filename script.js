/* ============================================
   PRINCE VERIFIED PORTFOLIO - MAIN SCRIPT
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {

    // ---- Loading Screen ----
    const loader = document.getElementById('loader');
    if (loader) {
        window.addEventListener('load', () => {
            setTimeout(() => {
                loader.classList.add('hidden');
            }, 600);
        });
        // Fallback: hide after 3s even if load event is slow
        setTimeout(() => {
            loader.classList.add('hidden');
        }, 3000);
    }

    // ---- Particle Background ----
    const canvas = document.getElementById('particleCanvas');
    if (canvas) {
        initParticles(canvas);
    }

    // ---- Navigation Scroll Effect ----
    const nav = document.getElementById('mainNav');
    if (nav) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        }, { passive: true });
    }

    // ---- Mobile Nav Toggle ----
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', () => {
            navToggle.classList.toggle('open');
            navLinks.classList.toggle('open');
        });
        // Close menu when a link is clicked
        navLinks.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navToggle.classList.remove('open');
                navLinks.classList.remove('open');
            });
        });
    }

    // ---- Typing Animation ----
    const typingEl = document.querySelector('.hero-typing');
    if (typingEl) {
        initTyping(typingEl);
    }

    // ---- Scroll Reveal (fade-up) ----
    const fadeEls = document.querySelectorAll('.fade-up');
    if (fadeEls.length) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, i) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('visible');
                    }, i * 80);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        fadeEls.forEach(el => observer.observe(el));
    }

    // ---- Certificate Card Stagger Animation ----
    const certCards = document.querySelectorAll('.cert-card');
    if (certCards.length) {
        const cardObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry, i) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('visible');
                    }, i * 120);
                    cardObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        certCards.forEach(card => cardObserver.observe(card));
    }

    // ---- Copy Verification Link ----
    const copyBtns = document.querySelectorAll('[data-copy]');
    copyBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const text = btn.getAttribute('data-copy');
            navigator.clipboard.writeText(text).then(() => {
                showToast('Verification link copied to clipboard!', 'success');
            }).catch(() => {
                // Fallback for older browsers
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                showToast('Verification link copied!', 'success');
            });
        });
    });

    // ---- Admin Search (live filter) ----
    const searchInput = document.getElementById('adminSearch');
    const searchTable = document.getElementById('adminTable');
    if (searchInput && searchTable) {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.toLowerCase().trim();
            const rows = searchTable.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    // ---- Smooth Scroll for anchor links ----
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', (e) => {
            const target = document.querySelector(anchor.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

});


/* ============================================
   PARTICLE SYSTEM
   ============================================ */
function initParticles(canvas) {
    const ctx = canvas.getContext('2d');
    let width, height, particles, mouse;
    const PARTICLE_COUNT = 70;
    const CONNECT_DIST = 140;
    const MOUSE_RADIUS = 120;

    mouse = { x: null, y: null };

    function resize() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    }

    function createParticles() {
        particles = [];
        for (let i = 0; i < PARTICLE_COUNT; i++) {
            particles.push({
                x: Math.random() * width,
                y: Math.random() * height,
                vx: (Math.random() - 0.5) * 0.5,
                vy: (Math.random() - 0.5) * 0.5,
                r: Math.random() * 2 + 0.8,
                opacity: Math.random() * 0.5 + 0.2
            });
        }
    }

    function animate() {
        ctx.clearRect(0, 0, width, height);

        particles.forEach(p => {
            // Move
            p.x += p.vx;
            p.y += p.vy;

            // Wrap around edges
            if (p.x < 0) p.x = width;
            if (p.x > width) p.x = 0;
            if (p.y < 0) p.y = height;
            if (p.y > height) p.y = 0;

            // Mouse repulsion
            if (mouse.x !== null) {
                const dx = p.x - mouse.x;
                const dy = p.y - mouse.y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < MOUSE_RADIUS) {
                    const force = (MOUSE_RADIUS - dist) / MOUSE_RADIUS * 0.02;
                    p.vx += dx / dist * force;
                    p.vy += dy / dist * force;
                }
            }

            // Dampen velocity
            p.vx *= 0.998;
            p.vy *= 0.998;

            // Draw particle
            ctx.beginPath();
            ctx.arc(p.x, p.y, Math.max(0.5, p.r), 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(0, 210, 255, ' + p.opacity + ')';
            ctx.fill();
        });

        // Draw connections
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < CONNECT_DIST) {
                    const alpha = (1 - dist / CONNECT_DIST) * 0.15;
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = 'rgba(106, 17, 203, ' + alpha + ')';
                    ctx.lineWidth = 0.6;
                    ctx.stroke();
                }
            }
        }

        requestAnimationFrame(animate);
    }

    resize();
    createParticles();
    animate();

    window.addEventListener('resize', () => {
        resize();
        createParticles();
    });

    window.addEventListener('mousemove', (e) => {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
    });

    window.addEventListener('mouseleave', () => {
        mouse.x = null;
        mouse.y = null;
    });
}


/* ============================================
   TYPING ANIMATION
   ============================================ */
function initTyping(el) {
    const phrases = [
        'Full Stack Developer',
        'Certified Professional',
        'Tech Enthusiast',
        'Problem Solver',
        'Open Source Contributor'
    ];

    let phraseIdx = 0;
    let charIdx = 0;
    let isDeleting = false;
    let typeSpeed = 80;

    function type() {
        const current = phrases[phraseIdx];

        if (isDeleting) {
            el.innerHTML = current.substring(0, charIdx - 1) + '<span class="cursor"></span>';
            charIdx--;
            typeSpeed = 40;
        } else {
            el.innerHTML = current.substring(0, charIdx + 1) + '<span class="cursor"></span>';
            charIdx++;
            typeSpeed = 80;
        }

        if (!isDeleting && charIdx === current.length) {
            typeSpeed = 2000; // Pause at end
            isDeleting = true;
        } else if (isDeleting && charIdx === 0) {
            isDeleting = false;
            phraseIdx = (phraseIdx + 1) % phrases.length;
            typeSpeed = 400; // Pause before next phrase
        }

        setTimeout(type, typeSpeed);
    }

    type();
}


/* ============================================
   TOAST NOTIFICATION
   ============================================ */
function showToast(message, type) {
    type = type || 'success';
    // Remove existing toasts
    document.querySelectorAll('.toast').forEach(t => t.remove());

    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + message;
    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.add('show');
    });

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}