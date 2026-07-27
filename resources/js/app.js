import './bootstrap';
import * as Bootstrap from 'bootstrap';
import $ from 'jquery';
import { Chart, registerables } from 'chart.js';
import AOS from 'aos';

Chart.register(...registerables);

window.Bootstrap = Bootstrap;
window.$ = window.jQuery = $;
window.Chart = Chart;
window.AOS = AOS;

document.addEventListener('DOMContentLoaded', () => {
    AOS.init({ duration: 700, once: true, offset: 80 });

    const siteNavbar = document.querySelector('.site-navbar');
    if (siteNavbar) {
        const hasHero = document.body.classList.contains('has-hero');
        const toggleScrolled = () => {
            siteNavbar.classList.toggle('scrolled', !hasHero || window.scrollY > 40);
        };
        toggleScrolled();
        window.addEventListener('scroll', toggleScrolled);
    }

    // Count-up animation for [data-counter] elements once they scroll into view
    const counters = document.querySelectorAll('[data-counter]');
    if (counters.length) {
        const animateCounter = (el) => {
            const target = parseInt(el.dataset.counter, 10);
            const suffix = el.dataset.suffix || '';
            const duration = 1400;
            const start = performance.now();

            const step = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.floor(eased * target) + suffix;
                if (progress < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach((el) => observer.observe(el));
    }
});
