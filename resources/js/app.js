import './bootstrap';
import './palette';
import './accessibility';
import './chat';

/**
 * Animación suave al entrar en viewport (solo opacity / transform, ≤200ms en CSS).
 */
document.addEventListener('DOMContentLoaded', () => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15 },
    );

    document.querySelectorAll('.fade-up, .fade-in').forEach((el) => observer.observe(el));
});
