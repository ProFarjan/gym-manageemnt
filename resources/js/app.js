import './bootstrap';
import * as Bootstrap from 'bootstrap';
import { Chart, registerables } from 'chart.js';
import AOS from 'aos';
import flatpickr from 'flatpickr';

Chart.register(...registerables);

window.Bootstrap = Bootstrap;
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

    document.querySelectorAll('.dob-datepicker').forEach(attachDatepicker);

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
                    .then((html) => { modalBody.innerHTML = html; })
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

    const billActionModalEl = document.getElementById('billActionModal');
    if (billActionModalEl) {
        const billActionModal = new window.Bootstrap.Modal(billActionModalEl);
        const modalTitle = document.getElementById('billActionModalLabel');
        const modalBody = document.getElementById('billActionModalBody');
        const printBtn = document.getElementById('billActionModalPrintBtn');

        printBtn?.addEventListener('click', () => window.print());

        document.querySelectorAll('[data-modal-url]').forEach((link) => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                modalTitle.textContent = link.dataset.modalTitle || '';
                modalBody.innerHTML = '<div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm"></div> Loading...</div>';
                printBtn?.classList.add('d-none');
                billActionModal.show();

                fetch(link.dataset.modalUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then((response) => {
                        if (!response.ok) throw new Error('Request failed');
                        return response.text();
                    })
                    .then((html) => {
                        modalBody.innerHTML = html;
                        modalBody.querySelectorAll('.invoice-date-picker').forEach(attachDatepicker);
                        // Only the View panel is printable — its markup always
                        // includes the letterhead header built for print.
                        printBtn?.classList.toggle('d-none', !modalBody.querySelector('.bill-print-header'));
                    })
                    .catch(() => {
                        modalBody.innerHTML = '<div class="alert alert-danger mb-0">Failed to load. Please try again.</div>';
                    });
            });
        });
    }

    // Create Bill form: AJAX searchable member picker. Plain fetch() against
    // the server-side search endpoint — no dropdown library involved, so
    // there's nothing that depends on jQuery/Select2 loading correctly
    // inside a dynamically-injected Bootstrap modal. Delegated since the
    // form is injected into the modal via fetch, same as everything else on
    // this page.
    document.addEventListener('input', (e) => {
        if (!e.target.classList.contains('member-ajax-input')) return;

        const wrapper = e.target.closest('.member-ajax-select');
        const resultsEl = wrapper?.querySelector('.member-ajax-results');
        const valueInput = wrapper?.querySelector('.member-ajax-value');
        if (!wrapper || !resultsEl || !valueInput) return;

        valueInput.value = '';

        const query = e.target.value.trim();

        clearTimeout(wrapper._memberSearchTimeout);
        wrapper._memberSearchTimeout = setTimeout(() => {
            const url = new URL(wrapper.dataset.ajaxUrl, window.location.origin);
            if (query) url.searchParams.set('q', query);

            resultsEl.innerHTML = '<span class="list-group-item text-muted">Searching…</span>';
            resultsEl.classList.remove('d-none');

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then((response) => {
                    if (!response.ok) throw new Error('Request failed');
                    return response.json();
                })
                .then((data) => {
                    const results = data.results || [];
                    resultsEl.innerHTML = results
                        .map((m) => `<button type="button" class="list-group-item list-group-item-action" data-id="${m.id}" data-text="${String(m.text).replace(/"/g, '&quot;')}">${m.text}</button>`)
                        .join('') || '<span class="list-group-item text-muted">No matches</span>';
                    resultsEl.classList.remove('d-none');
                })
                .catch(() => {
                    resultsEl.innerHTML = '<span class="list-group-item text-danger">Search failed — please try again.</span>';
                });
        }, 300);
    });

    document.addEventListener('click', (e) => {
        const option = e.target.closest('.member-ajax-results button[data-id]');
        if (!option) return;

        const wrapper = option.closest('.member-ajax-select');
        const input = wrapper?.querySelector('.member-ajax-input');
        const valueInput = wrapper?.querySelector('.member-ajax-value');
        const resultsEl = wrapper?.querySelector('.member-ajax-results');
        if (!wrapper || !input || !valueInput || !resultsEl) return;

        input.value = option.dataset.text;
        valueInput.value = option.dataset.id;
        resultsEl.classList.add('d-none');
        resultsEl.innerHTML = '';
    });

    document.addEventListener('focusin', (e) => {
        const wrapper = e.target.closest('.member-ajax-select');
        if (!wrapper || !e.target.classList.contains('member-ajax-input')) return;

        // Re-show whatever was already loaded when the field regains focus,
        // so clicking back in doesn't require retyping to see the list.
        const resultsEl = wrapper.querySelector('.member-ajax-results');
        if (resultsEl && resultsEl.innerHTML.trim()) {
            resultsEl.classList.remove('d-none');
        } else {
            wrapper.querySelector('.member-ajax-input').dispatchEvent(new Event('input', { bubbles: true }));
        }
    });

    document.addEventListener('click', (e) => {
        document.querySelectorAll('.member-ajax-select').forEach((wrapper) => {
            if (wrapper.contains(e.target)) return;
            wrapper.querySelector('.member-ajax-results')?.classList.add('d-none');
        });
    });

    // Shared flatpickr attachment for any text input that wants a calendar
    // picker (member Date of Birth on the full-page member forms, Invoice
    // Date inside the AJAX-loaded Create Bill modal).
    function attachDatepicker(input) {
        flatpickr(input, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd M Y',
            minDate: input.dataset.min || null,
            maxDate: input.dataset.max || null,
            allowInput: false,
            disableMobile: true,
        });
    }

    const billDeleteModalEl = document.getElementById('billDeleteModal');
    if (billDeleteModalEl) {
        const billDeleteModal = new window.Bootstrap.Modal(billDeleteModalEl);
        const deleteForm = document.getElementById('billDeleteForm');
        const deleteName = document.getElementById('billDeleteName');

        document.querySelectorAll('[data-delete-url]').forEach((link) => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                deleteForm.action = link.dataset.deleteUrl;
                deleteName.textContent = link.dataset.deleteName || 'this bill';
                billDeleteModal.show();
            });
        });
    }

    // Bill Pay form is injected into the modal via fetch(), so it doesn't exist
    // yet at DOMContentLoaded — listen via delegation instead of a direct binding.
    // A discount here counts *toward* settling the bill rather than against it,
    // so this is Amount + Discount (not a "net cash" subtraction).
    document.addEventListener('input', (e) => {
        if (e.target.id !== 'billPayAmount' && e.target.id !== 'billPayDiscount') return;

        const form = e.target.closest('form');
        const totalEl = form?.querySelector('#billPayTotalSettled');
        if (!totalEl) return;

        const amount = parseFloat(form.querySelector('#billPayAmount')?.value) || 0;
        const discount = parseFloat(form.querySelector('#billPayDiscount')?.value) || 0;
        const settled = amount + discount;
        totalEl.textContent = settled.toFixed(2);

        const balanceDue = parseFloat(form.dataset.balanceDue) || 0;
        const warningEl = form.querySelector('#billPayOverageWarning');
        const exceeds = settled > balanceDue + 0.01;
        totalEl.classList.toggle('text-danger', exceeds);
        warningEl?.classList.toggle('d-none', !exceeds);
    });

    // Create Bill form: dynamic line items. Each row's Total is qty × unit
    // price, recomputed live; Sub Total/Grand Total are recomputed across
    // all rows whenever any row or the Discount field changes. Delegated
    // since the form is injected into the modal via fetch.
    function recalcBillTotals(form) {
        let subtotal = 0;
        form.querySelectorAll('.bill-item-row').forEach((row) => {
            const qty = parseFloat(row.querySelector('.bill-item-qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.bill-item-price')?.value) || 0;
            const total = qty * price;
            const totalEl = row.querySelector('.bill-item-total');
            if (totalEl) totalEl.value = total.toFixed(2);
            subtotal += total;
        });

        const discount = parseFloat(form.querySelector('#billDiscountInput')?.value) || 0;
        const grandTotal = Math.max(0, subtotal - discount);

        const subtotalEl = form.querySelector('#billSubTotal');
        const grandTotalEl = form.querySelector('#billGrandTotal');
        if (subtotalEl) subtotalEl.textContent = subtotal.toFixed(2);
        if (grandTotalEl) grandTotalEl.textContent = grandTotal.toFixed(2);
    }

    document.addEventListener('input', (e) => {
        if (
            !e.target.classList.contains('bill-item-qty') &&
            !e.target.classList.contains('bill-item-price') &&
            e.target.id !== 'billDiscountInput'
        ) return;

        const form = e.target.closest('form');
        if (form) recalcBillTotals(form);
    });

    document.addEventListener('click', (e) => {
        if (e.target.id !== 'billItemAddRow') return;

        const form = e.target.closest('form');
        const template = form?.querySelector('#billItemRowTemplate');
        const body = form?.querySelector('#billItemsBody');
        if (!template || !body) return;

        body.appendChild(template.content.cloneNode(true));
    });

    document.addEventListener('click', (e) => {
        const removeBtn = e.target.closest('.bill-item-remove');
        if (!removeBtn) return;

        const form = removeBtn.closest('form');
        const body = form?.querySelector('#billItemsBody');
        if (!body) return;

        // Always leave at least one row so the form stays submittable.
        if (body.querySelectorAll('.bill-item-row').length <= 1) return;

        removeBtn.closest('.bill-item-row')?.remove();
        recalcBillTotals(form);
    });

    // Generic AJAX search/filter + pagination for any ".ajax-panel" (the
    // Members index table, and the "View Payments" / "Attendance Records"
    // modal panels). Any control inside the panel tagged [data-ajax-param]
    // feeds the fetch as a query param — text inputs debounced, selects fire
    // immediately. Only ".ajax-panel-results" is ever swapped, so a search
    // box or dropdown never loses focus/value on every keystroke or change.
    function collectAjaxParams(panel, overrides) {
        const params = {};
        panel.querySelectorAll('[data-ajax-param]').forEach((el) => {
            if (el.value) params[el.dataset.ajaxParam] = el.value;
        });
        return Object.assign(params, overrides);
    }

    function loadAjaxPanel(panel, params) {
        const resultsEl = panel.querySelector('.ajax-panel-results');
        if (!resultsEl) return;

        const url = new URL(panel.dataset.panelUrl, window.location.origin);
        Object.entries(params).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                url.searchParams.set(key, value);
            }
        });

        resultsEl.style.opacity = '0.5';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then((response) => {
                if (!response.ok) throw new Error('Request failed');
                return response.text();
            })
            .then((html) => {
                resultsEl.innerHTML = html;
                resultsEl.style.opacity = '1';
            })
            .catch(() => {
                resultsEl.style.opacity = '1';
            });
    }

    document.addEventListener('input', (e) => {
        if (!e.target.matches('[data-ajax-param]') || e.target.tagName !== 'INPUT') return;
        const panel = e.target.closest('.ajax-panel');
        if (!panel) return;

        clearTimeout(panel._ajaxSearchTimeout);
        panel._ajaxSearchTimeout = setTimeout(() => {
            loadAjaxPanel(panel, collectAjaxParams(panel, { page: 1 }));
        }, 400);
    });

    document.addEventListener('change', (e) => {
        if (!e.target.matches('[data-ajax-param]') || e.target.tagName !== 'SELECT') return;
        const panel = e.target.closest('.ajax-panel');
        if (!panel) return;

        loadAjaxPanel(panel, collectAjaxParams(panel, { page: 1 }));
    });

    document.addEventListener('click', (e) => {
        const link = e.target.closest('.ajax-panel-results .pagination a[href]');
        if (!link) return;
        e.preventDefault();

        const panel = link.closest('.ajax-panel');
        if (!panel) return;

        const page = new URL(link.href).searchParams.get('page') || 1;
        loadAjaxPanel(panel, collectAjaxParams(panel, { page }));
    });
});
