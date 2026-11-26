/**
 * Animaciones de la interfaz pública.
 *
 * Todo el contenido queda visible sin JavaScript: los estados ocultos solo se
 * aplican cuando <html> tiene la clase `js` y se respeta `prefers-reduced-motion`.
 */

const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Revela elementos con `data-reveal` al entrar en el viewport.
 * `data-reveal-delay="120"` añade un retardo en milisegundos.
 */
function initScrollReveal() {
    const elements = document.querySelectorAll('[data-reveal]');

    if (elements.length === 0) {
        return;
    }

    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
        elements.forEach((element) => element.classList.add('is-revealed'));
        return;
    }

    const observer = new IntersectionObserver((entries, instance) => {
        entries.forEach((entry) => {
            if (! entry.isIntersecting) {
                return;
            }

            const delay = Number(entry.target.dataset.revealDelay || 0);
            entry.target.style.transitionDelay = `${delay}ms`;
            entry.target.classList.add('is-revealed');
            instance.unobserve(entry.target);
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    elements.forEach((element) => observer.observe(element));
}

/**
 * Animación de entrada inicial para elementos `data-hero-item`, escalonados.
 */
function initHeroEntrance() {
    const items = document.querySelectorAll('[data-hero-item]');

    items.forEach((item, index) => {
        item.style.transitionDelay = `${120 + index * 110}ms`;
        requestAnimationFrame(() => item.classList.add('is-revealed'));
    });
}

/**
 * Cuenta desde 0 hasta `data-count-to` cuando el elemento es visible.
 */
function initCountUp() {
    const counters = document.querySelectorAll('[data-count-to]');

    if (counters.length === 0) {
        return;
    }

    const animate = (element) => {
        const target = Number(element.dataset.countTo);
        const duration = 1200;
        const start = performance.now();

        const step = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            element.textContent = (element.dataset.countPrefix || '') + Math.round(target * eased).toLocaleString('es-CO');

            if (progress < 1) {
                requestAnimationFrame(step);
            }
        };

        requestAnimationFrame(step);
    };

    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver((entries, instance) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                animate(entry.target);
                instance.unobserve(entry.target);
            }
        });
    });

    counters.forEach((counter) => observer.observe(counter));
}

/**
 * Brillo que sigue al cursor dentro de `data-spotlight` y ligera inclinación 3D
 * en `data-tilt`. Solo en dispositivos con puntero fino.
 */
function initCardEffects() {
    if (prefersReducedMotion() || ! window.matchMedia('(pointer: fine)').matches) {
        return;
    }

    document.querySelectorAll('[data-spotlight]').forEach((card) => {
        const tiltEnabled = card.hasAttribute('data-tilt');
        const maxTilt = 9;

        card.addEventListener('pointermove', (event) => {
            const rect = card.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;

            card.style.setProperty('--spot-x', `${x}px`);
            card.style.setProperty('--spot-y', `${y}px`);

            if (tiltEnabled) {
                const rotateY = ((x / rect.width) - 0.5) * maxTilt * 2;
                const rotateX = ((y / rect.height) - 0.5) * -maxTilt * 2;
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-8px)`;
            }
        });

        card.addEventListener('pointerleave', () => {
            card.style.transform = '';
        });
    });
}

/**
 * Añade la clase `is-scrolled` a la navbar (`data-nav`) al desplazarse.
 */
function initNavScroll() {
    const nav = document.querySelector('[data-nav]');

    if (! nav) {
        return;
    }

    const update = () => nav.classList.toggle('is-scrolled', window.scrollY > 8);

    update();
    window.addEventListener('scroll', update, { passive: true });
}

/**
 * Desplazamiento suave hacia anclas internas (`a[href^="#"]`).
 */
function initSmoothAnchors() {
    if (prefersReducedMotion()) {
        return;
    }

    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', (event) => {
            const target = document.querySelector(anchor.getAttribute('href'));

            if (! target) {
                return;
            }

            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
}

/**
 * Persiana: la imagen marcada con `data-shutter` se abre desde el borde
 * izquierdo mientras la foto se asienta de 1.12 a 1.
 */
function initShutter() {
    document.querySelectorAll('[data-shutter]').forEach((element) => {
        if (prefersReducedMotion()) {
            element.classList.add('is-open');
            return;
        }

        setTimeout(() => element.classList.add('is-open'), 380);
    });
}

/**
 * Entrada escalonada: los hijos directos de `data-stagger` suben y aparecen
 * uno tras otro (75 ms entre cada uno) cuando el bloque entra en pantalla.
 */
function initStagger() {
    const groups = document.querySelectorAll('[data-stagger]');

    groups.forEach((group) => {
        Array.from(group.children).forEach((child, index) => {
            child.style.transitionDelay = `${index * 75}ms`;
        });
    });

    if (prefersReducedMotion() || ! ('IntersectionObserver' in window)) {
        groups.forEach((group) => group.classList.add('is-revealed'));
        return;
    }

    const observer = new IntersectionObserver((entries, instance) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-revealed');
                instance.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

    groups.forEach((group) => observer.observe(group));
}

/**
 * Deriva lenta: las fotos con `data-drift` se desplazan levemente con el
 * scroll, solo mientras están visibles.
 */
function initDrift() {
    const images = Array.from(document.querySelectorAll('[data-drift]'));

    if (images.length === 0 || prefersReducedMotion()) {
        return;
    }

    let ticking = false;

    const update = () => {
        const viewport = window.innerHeight;

        images.forEach((image) => {
            const rect = image.getBoundingClientRect();

            if (rect.bottom < 0 || rect.top > viewport) {
                return;
            }

            const progress = (rect.top + rect.height / 2 - viewport / 2) / viewport;
            image.style.transform = `translate3d(0, ${progress * -28}px, 0) scale(1.08)`;
        });

        ticking = false;
    };

    window.addEventListener('scroll', () => {
        if (! ticking) {
            requestAnimationFrame(update);
            ticking = true;
        }
    }, { passive: true });

    update();
}

/**
 * La fachada y la lista de habitaciones: filtrar por zona sin recargar,
 * mostrar el detalle de cada ventana y abrir la habitación al tocarla.
 */
function initRoomFinder() {
    const finder = document.querySelector('[data-room-finder]');

    if (! finder) {
        return;
    }

    const rows = Array.from(finder.querySelectorAll('[data-room-row]'));
    const floors = Array.from(finder.querySelectorAll('[data-floor]'));
    const chips = Array.from(finder.querySelectorAll('.zone-chip'));
    const caption = finder.querySelector('[data-facade-caption]');
    const countElement = finder.querySelector('[data-free-count]');
    const wordElement = finder.querySelector('[data-free-word]');
    const emptyElement = finder.querySelector('[data-room-empty]');
    const defaultCaption = caption ? caption.textContent.trim() : '';

    const animateRow = (row, index) => {
        if (prefersReducedMotion() || ! row.animate) {
            return;
        }

        row.animate(
            [{ opacity: 0, transform: 'translateY(16px)' }, { opacity: 1, transform: 'none' }],
            { duration: 520, delay: index * 60, easing: 'cubic-bezier(0.22, 1, 0.36, 1)', fill: 'backwards' },
        );
    };

    const applyZone = (zoneId) => {
        let visible = 0;

        rows.forEach((row) => {
            const show = ! zoneId || row.dataset.zona === zoneId;
            row.classList.toggle('is-hidden', ! show);

            if (show) {
                animateRow(row, visible);
                visible += 1;
            }
        });

        floors.forEach((floor) => floor.classList.toggle('is-dimmed', Boolean(zoneId) && floor.dataset.floor !== zoneId));
        chips.forEach((chip) => chip.classList.toggle('is-active', (chip.dataset.zoneLink || '') === zoneId));

        if (countElement) {
            countElement.textContent = visible;
        }

        if (wordElement) {
            wordElement.textContent = visible === 1 ? 'habitación libre' : 'habitaciones libres';
        }

        if (emptyElement) {
            emptyElement.classList.toggle('hidden', visible > 0);
        }

        const url = new URL(window.location.href);

        if (zoneId) {
            url.searchParams.set('zona_id', zoneId);
        } else {
            url.searchParams.delete('zona_id');
        }

        url.hash = 'habitaciones';
        window.history.replaceState({}, '', url);
    };

    finder.querySelectorAll('[data-zone-link]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            applyZone(link.dataset.zoneLink || '');
        });
    });

    finder.querySelectorAll('[data-room]').forEach((roomWindow) => {
        const show = () => {
            finder.querySelectorAll('.facade-window.is-active').forEach((other) => other.classList.remove('is-active'));
            roomWindow.classList.add('is-active');

            if (caption) {
                caption.textContent = roomWindow.dataset.roomLabel;
            }

            rows.forEach((row) => row.classList.toggle('bg-white', row.dataset.roomRow === roomWindow.dataset.room));
        };

        const hide = () => {
            roomWindow.classList.remove('is-active');
            rows.forEach((row) => row.classList.remove('bg-white'));

            if (caption) {
                caption.textContent = defaultCaption;
            }
        };

        const open = () => {
            if (roomWindow.dataset.roomUrl) {
                window.location.href = roomWindow.dataset.roomUrl;
            } else {
                show();
            }
        };

        roomWindow.addEventListener('pointerenter', show);
        roomWindow.addEventListener('pointerleave', hide);
        roomWindow.addEventListener('focus', show);
        roomWindow.addEventListener('blur', hide);
        roomWindow.addEventListener('click', (event) => {
            // En pantallas táctiles el primer toque muestra el detalle y el segundo abre.
            if (event.pointerType === 'touch' && ! roomWindow.classList.contains('was-tapped')) {
                finder.querySelectorAll('.was-tapped').forEach((other) => other.classList.remove('was-tapped'));
                roomWindow.classList.add('was-tapped');
                show();
                return;
            }

            open();
        });
        roomWindow.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                open();
            }
        });
    });
}

/**
 * Fila de momentos: arrastre con el ratón, botones y barra de progreso.
 */
function initMoments() {
    const row = document.querySelector('[data-moments]');

    if (! row) {
        return;
    }

    const section = row.closest('[data-moments-section]');
    const progress = section.querySelector('[data-moments-progress]');
    const prev = section.querySelector('[data-moments-prev]');
    const next = section.querySelector('[data-moments-next]');

    const updateProgress = () => {
        const max = row.scrollWidth - row.clientWidth;
        const ratio = max > 0 ? row.scrollLeft / max : 1;
        const visibleShare = row.clientWidth / row.scrollWidth;
        const share = Math.min(visibleShare + (1 - visibleShare) * ratio, 1);

        progress.style.width = '100%';
        progress.style.transform = `scaleX(${share})`;

        if (prev) prev.disabled = row.scrollLeft <= 2;
        if (next) next.disabled = row.scrollLeft >= max - 2;
    };

    progress.style.transition = 'transform 0.25s ease-out';

    const step = () => (row.querySelector('li')?.getBoundingClientRect().width || 280) + 32;

    prev?.addEventListener('click', () => row.scrollBy({ left: -step(), behavior: 'smooth' }));
    next?.addEventListener('click', () => row.scrollBy({ left: step(), behavior: 'smooth' }));
    row.addEventListener('scroll', () => requestAnimationFrame(updateProgress), { passive: true });
    window.addEventListener('resize', updateProgress);

    // Arrastre con ratón (en pantallas táctiles funciona el desplazamiento nativo)
    let startX = 0;
    let startScroll = 0;
    let dragging = false;

    row.addEventListener('pointerdown', (event) => {
        if (event.pointerType !== 'mouse') {
            return;
        }

        dragging = true;
        startX = event.clientX;
        startScroll = row.scrollLeft;
        row.classList.add('is-dragging');
    });

    window.addEventListener('pointermove', (event) => {
        if (dragging) {
            row.scrollLeft = startScroll - (event.clientX - startX);
        }
    });

    window.addEventListener('pointerup', () => {
        if (dragging) {
            dragging = false;
            row.classList.remove('is-dragging');
        }
    });

    updateProgress();
}

/**
 * Calculadora de la estadía: muestra noches y total según las fechas.
 */
function initStayTotal() {
    const form = document.querySelector('[data-stay-form]');

    if (! form) {
        return;
    }

    const price = Number(form.dataset.price);
    const checkIn = form.querySelector('#fecha_ingreso');
    const checkOut = form.querySelector('#fecha_salida');
    const nightsElement = form.querySelector('[data-stay-nights]');
    const totalElement = form.querySelector('[data-stay-total]');
    const box = form.querySelector('[data-stay-box]');
    let shown = 0;

    const animateTo = (target) => {
        const from = shown;
        const start = performance.now();
        const duration = prefersReducedMotion() ? 0 : 500;

        const frame = (now) => {
            const t = duration ? Math.min((now - start) / duration, 1) : 1;
            const value = Math.round(from + (target - from) * (1 - Math.pow(1 - t, 3)));
            totalElement.textContent = '$' + value.toLocaleString('es-CO');

            if (t < 1) {
                requestAnimationFrame(frame);
            }
        };

        shown = target;
        requestAnimationFrame(frame);
    };

    const update = () => {
        if (checkIn.value) {
            const minOut = new Date(checkIn.value);
            minOut.setDate(minOut.getDate() + 1);
            checkOut.min = minOut.toISOString().slice(0, 10);
        }

        const nights = checkIn.value && checkOut.value
            ? Math.round((new Date(checkOut.value) - new Date(checkIn.value)) / 86400000)
            : 0;

        if (nights > 0) {
            nightsElement.textContent = nights === 1 ? '1 noche' : `${nights} noches`;
            animateTo(nights * price);
            box.classList.remove('opacity-50');
        } else {
            nightsElement.textContent = 'Elija las fechas';
            animateTo(0);
            box.classList.add('opacity-50');
        }
    };

    checkIn.addEventListener('change', update);
    checkOut.addEventListener('change', update);
    update();
}

export function initAnimations() {
    document.documentElement.classList.add('js');

    initHeroEntrance();
    initScrollReveal();
    initCountUp();
    initCardEffects();
    initNavScroll();
    initSmoothAnchors();
    initShutter();
    initStagger();
    initDrift();
    initRoomFinder();
    initMoments();
    initStayTotal();
}
