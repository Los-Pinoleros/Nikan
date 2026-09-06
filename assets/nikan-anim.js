/* ===== NIKAN - Animaciones globales ===== */
(function () {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) return;

    /* ---------- Brillo del cursor (verde en todas las páginas, incluida la portada) ---------- */
    (function () {
        if (!window.matchMedia('(pointer: fine)').matches) return;
        var cur = document.createElement('div');
        cur.id = 'nikan-cursor';
        document.body.appendChild(cur);
        var x = innerWidth / 2, y = innerHeight / 2, tx = x, ty = y;
        addEventListener('mousemove', function (e) {
            tx = e.clientX; ty = e.clientY;
            cur.classList.add('on');
        }, { passive: true });
        addEventListener('mouseleave', function () { cur.classList.remove('on'); });
        (function loop() {
            x += (tx - x) * 0.08;
            y += (ty - y) * 0.08;
            cur.style.left = x + 'px';
            cur.style.top = y + 'px';
            requestAnimationFrame(loop);
        })();
    })();

    /* La portada (inicio) queda sin animaciones: se muestra tal cual era. */
    var page = new URLSearchParams(location.search).get('page') || 'inicio';
    if (page === 'inicio') return;

    /* ---------- Preloader ---------- */
    (function () {
        var loader = document.createElement('div');
        loader.id = 'nikan-loader';
        loader.innerHTML =
            '<video class="nikan-loader-video" src="assets/anima.mp4" autoplay muted playsinline></video>';
        document.body.insertBefore(loader, document.body.firstChild);
        setTimeout(function () {
            loader.classList.add('done');
            setTimeout(function () { loader.remove(); }, 800);
        }, 4000);
    })();

    /* ---------- Auroras de fondo ---------- */
    var auroras = [
        'nikan-aurora nikan-aurora--1',
        'nikan-aurora nikan-aurora--2',
        'nikan-aurora nikan-aurora--3'
    ];
    auroras.forEach(function (cls) {
        var d = document.createElement('div');
        d.className = cls;
        document.body.insertBefore(d, document.body.firstChild);
    });

    /* ---------- Partículas doradas flotantes ---------- */
    (function () {
        var canvas = document.createElement('canvas');
        canvas.id = 'nikan-velo';
        document.body.appendChild(canvas);
        var ctx = canvas.getContext('2d');
        var W, H, DPR = Math.min(devicePixelRatio || 1, 2);
        var parts = [], MAX = 55;
        var mouseX = -9999, mouseY = -9999;

        function resize() {
            W = canvas.width = innerWidth * DPR;
            H = canvas.height = innerHeight * DPR;
            canvas.style.width = innerWidth + 'px';
            canvas.style.height = innerHeight + 'px';
            parts = [];
            for (var i = 0; i < MAX; i++) {
                parts.push({
                    x: Math.random() * W,
                    y: Math.random() * H,
                    r: (Math.random() * 2.2 + 0.8) * DPR,
                    vy: (Math.random() * 0.25 + 0.06) * DPR,
                    vx: (Math.random() - 0.5) * 0.18 * DPR,
                    a: Math.random() * Math.PI * 2,
                    tw: Math.random() * 0.04 + 0.01
                });
            }
        }
        addEventListener('resize', resize);
        addEventListener('mousemove', function (e) {
            mouseX = e.clientX * DPR;
            mouseY = e.clientY * DPR;
        }, { passive: true });
        addEventListener('mouseleave', function () { mouseX = mouseY = -9999; });

        function tick() {
            ctx.clearRect(0, 0, W, H);
            for (var i = 0; i < parts.length; i++) {
                var p = parts[i];
                p.y -= p.vy;
                p.x += p.vx + Math.sin(p.a) * 0.15 * DPR;
                p.a += p.tw;
                var dx = mouseX - p.x, dy = mouseY - p.y;
                var dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 140 * DPR) {
                    p.x -= (dx / dist) * 0.35 * DPR;
                    p.y -= (dy / dist) * 0.35 * DPR;
                }
                if (p.y < -30) { p.y = H + 30; p.x = Math.random() * W; }
                if (p.x < -30) p.x = W + 30;
                if (p.x > W + 30) p.x = -30;
                var alpha = 0.12 + Math.sin(p.a * 3) * 0.08 + (dist < 140 * DPR ? 0.25 : 0);
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(201, 169, 79, ' + Math.max(0, Math.min(0.8, alpha)) + ')';
                ctx.fill();
            }
            requestAnimationFrame(tick);
        }
        resize();
        tick();
    })();

    /* ---------- Header compacto al hacer scroll ---------- */
    var header = document.querySelector('.header');
    if (header) {
        var onScroll = function () {
            header.classList.toggle('header--scrolled', scrollY > 40);
        };
        addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* ---------- Reveal al hacer scroll ---------- */
    var revealTargets = [
        '.artec-card', '.lite-card', '.poe-card', '.aut-card',
        '.det-mini', '.detl-mini', '.detp-mini',
        '.arte-texto', '.lite-texto', '.poe-texto', '.aut-texto',
        '.det-info', '.detl-info', '.detp-info'
    ];
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
            if (en.isIntersecting) {
                var el = en.target;
                el.classList.add('in-view');
                io.unobserve(el);
                setTimeout(function () {
                    el.removeAttribute('data-reveal');
                    el.style.opacity = '';
                    el.style.transform = '';
                }, 1000);
            }
        });
    }, { threshold: 0.12 });
    revealTargets.forEach(function (sel) {
        document.querySelectorAll(sel).forEach(function (el) {
            if (!el.dataset.reveal) el.dataset.reveal = '';
            if ([
                '.arte-texto', '.lite-texto', '.poe-texto', '.aut-texto',
                '.det-info', '.detl-info', '.detp-info'
            ].indexOf(sel) !== -1) el.setAttribute('data-reveal', 'left');
            else if (Math.random() < 0.3) el.setAttribute('data-reveal', 'zoom');
            io.observe(el);
        });
    });

    /* ---------- Títulos con letras en cascada ---------- */
    var cascadeHosts = '.arte-texto, .lite-texto, .poe-texto, .aut-texto, .det-info, .detl-info, .detp-info';
    document.querySelectorAll(cascadeHosts).forEach(function (host) {
        var h1 = host.querySelector('h1');
        if (!h1 || h1.querySelector('.nikan-char')) return;
        host.classList.add('nikan-cascade');
        var i = 0;
        var walk = function (node) {
            Array.prototype.forEach.call(node.childNodes, function (child) {
                if (child.nodeType === 3) {
                    var frag = document.createDocumentFragment();
                    var text = child.nodeValue;
                    for (var c = 0; c < text.length; c++) {
                        var ch = text.charAt(c);
                        var span = document.createElement('span');
                        span.className = 'nikan-char';
                        if (ch === ' ') { span.innerHTML = '&nbsp;'; }
                        else { span.textContent = ch; }
                        span.style.setProperty('--d', (i * 0.035) + 's');
                        frag.appendChild(span);
                        i++;
                    }
                    node.replaceChild(frag, child);
                } else if (child.nodeType === 1) {
                    walk(child);
                }
            });
        };
        walk(h1);
        setTimeout(function () { host.classList.add('ran'); }, 200);
    });

    /* ---------- Enlaces magnéticos ---------- */
    document.querySelectorAll('.header__link, .btn, .det-back, .detl-back, .detp-back').forEach(function (el) {
        el.classList.add('magnetic');
        el.addEventListener('mousemove', function (e) {
            var r = el.getBoundingClientRect();
            var mx = e.clientX - r.left - r.width / 2;
            var my = e.clientY - r.top - r.height / 2;
            el.style.transform = 'translate(' + mx * 0.18 + 'px, ' + my * 0.28 + 'px)';
        });
        el.addEventListener('mouseleave', function () {
            el.style.transform = '';
        });
    });

    /* ---------- Spotlight que sigue al cursor en tarjetas ---------- */
    document.querySelectorAll('.artec-card, .lite-card, .poe-card, .aut-card, .det-mini, .detl-mini, .detp-mini').forEach(function (card) {
        card.classList.add('spot-card');
        card.addEventListener('mousemove', function (e) {
            var r = card.getBoundingClientRect();
            card.style.setProperty('--mx', ((e.clientX - r.left) / r.width * 100) + '%');
            card.style.setProperty('--my', ((e.clientY - r.top) / r.height * 100) + '%');
        });
    });
})();