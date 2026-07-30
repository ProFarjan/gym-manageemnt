import './bootstrap';
import * as Bootstrap from 'bootstrap';
import $ from 'jquery';
import { Chart, registerables } from 'chart.js';
import AOS from 'aos';
import flatpickr from 'flatpickr';

Chart.register(...registerables);

window.Bootstrap = Bootstrap;
window.$ = window.jQuery = $;
window.Chart = Chart;
window.AOS = AOS;

// Pay Due modal: Periods (Month) is swapped for a Package picker when Type is
// "package", and Amount auto-fills from the member's plan price (x periods)
// for Monthly/Renewal, or from the selected package's price for Package.
function togglePayDueFields(typeSelect) {
    const form = typeSelect.closest('form');
    const periodsField = form?.querySelector('#payPeriodsField');
    const packageField = form?.querySelector('#payPackageField');
    if (!periodsField || !packageField) return;

    const isPackage = typeSelect.value === 'package';
    periodsField.classList.toggle('d-none', isPackage);
    packageField.classList.toggle('d-none', !isPackage);
}

function updatePayAmount(form) {
    if (!form) return;
    const typeSelect = form.querySelector('#payType');
    const amountInput = form.querySelector('#payAmount');
    if (!typeSelect || !amountInput) return;

    if (typeSelect.value === 'monthly' || typeSelect.value === 'renewal') {
        const planPrice = parseFloat(typeSelect.dataset.planPrice) || 0;
        const periods = parseFloat(form.querySelector('#payPeriods')?.value) || 1;
        amountInput.value = (planPrice * periods).toFixed(2);
    } else if (typeSelect.value === 'package') {
        const packageSelect = form.querySelector('#payPackage');
        const price = parseFloat(packageSelect?.selectedOptions[0]?.dataset.price) || 0;
        amountInput.value = price.toFixed(2);
    } else {
        return;
    }

    amountInput.dispatchEvent(new Event('input'));
}

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

    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        const body = document.body;
        const isMobile = () => window.innerWidth < 992;

        if (!isMobile() && localStorage.getItem('adminSidebarCollapsed') === '1') {
            body.classList.add('sidebar-collapsed');
        }

        sidebarToggle.addEventListener('click', () => {
            if (isMobile()) {
                body.classList.toggle('sidebar-open');
            } else {
                const collapsed = body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('adminSidebarCollapsed', collapsed ? '1' : '0');
            }
        });

        document.addEventListener('click', (e) => {
            if (isMobile() && body.classList.contains('sidebar-open') &&
                !e.target.closest('.admin-sidebar') && !e.target.closest('#sidebarToggle')) {
                body.classList.remove('sidebar-open');
            }
        });

        window.addEventListener('resize', () => {
            if (!isMobile()) {
                body.classList.remove('sidebar-open');
            }
        });
    }

    document.querySelectorAll('.dob-datepicker').forEach((input) => {
        flatpickr(input, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd M Y',
            minDate: input.dataset.min || null,
            maxDate: input.dataset.max || null,
            allowInput: false,
            disableMobile: true,
        });
    });

    document.querySelectorAll('input[type="file"][data-preview]').forEach((input) => {
        const preview = document.getElementById(input.dataset.preview);
        if (!preview) return;

        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    });

    document.querySelectorAll('.table-responsive .dropdown-toggle').forEach((toggleEl) => {
        new window.Bootstrap.Dropdown(toggleEl, {
            popperConfig: { strategy: 'fixed' },
        });
    });

    const memberActionModalEl = document.getElementById('memberActionModal');
    if (memberActionModalEl) {
        const memberActionModal = new window.Bootstrap.Modal(memberActionModalEl);
        const modalTitle = document.getElementById('memberActionModalLabel');
        const modalBody = document.getElementById('memberActionModalBody');

        document.querySelectorAll('[data-modal-url]').forEach((link) => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                modalTitle.textContent = link.dataset.modalTitle || '';
                modalBody.innerHTML = '<div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm"></div> Loading...</div>';
                memberActionModal.show();

                fetch(link.dataset.modalUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then((response) => {
                        if (!response.ok) throw new Error('Request failed');
                        return response.text();
                    })
                    .then((html) => {
                        modalBody.innerHTML = html;
                        const typeSelect = modalBody.querySelector('#payType');
                        if (typeSelect) {
                            togglePayDueFields(typeSelect);
                            updatePayAmount(typeSelect.closest('form'));
                        }
                    })
                    .catch(() => {
                        modalBody.innerHTML = '<div class="alert alert-danger mb-0">Failed to load. Please try again.</div>';
                    });
            });
        });
    }

    const memberDeleteModalEl = document.getElementById('memberDeleteModal');
    if (memberDeleteModalEl) {
        const memberDeleteModal = new window.Bootstrap.Modal(memberDeleteModalEl);
        const deleteForm = document.getElementById('memberDeleteForm');
        const deleteName = document.getElementById('memberDeleteName');

        document.querySelectorAll('[data-delete-url]').forEach((link) => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                deleteForm.action = link.dataset.deleteUrl;
                deleteName.textContent = link.dataset.deleteName || 'this member';
                memberDeleteModal.show();
            });
        });
    }

    // Pay Due form is injected into the modal via fetch(), so it doesn't exist
    // yet at DOMContentLoaded — listen via delegation instead of a direct binding.
    document.addEventListener('input', (e) => {
        if (e.target.id !== 'payAmount' && e.target.id !== 'payDiscount') return;

        const form = e.target.closest('form');
        const subTotalEl = form?.querySelector('#paySubTotal');
        if (!subTotalEl) return;

        const amount = parseFloat(form.querySelector('#payAmount')?.value) || 0;
        const discount = parseFloat(form.querySelector('#payDiscount')?.value) || 0;
        subTotalEl.textContent = (amount - discount).toFixed(2);
    });

    document.addEventListener('change', (e) => {
        if (e.target.id === 'payType') {
            togglePayDueFields(e.target);
            updatePayAmount(e.target.closest('form'));
        } else if (e.target.id === 'payPeriods' || e.target.id === 'payPackage') {
            updatePayAmount(e.target.closest('form'));
        }
    });

    document.addEventListener('input', (e) => {
        if (e.target.id === 'payPeriods') {
            updatePayAmount(e.target.closest('form'));
        }
    });
});
