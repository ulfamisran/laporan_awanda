{{-- CSS + JS utilitas accordion mobile: satu panel terbuka per grup. --}}
@once
    @push('styles')
        <style>
            .mbg-expand-item {
                border: 1px solid #d4e8f4;
                border-radius: 1rem;
                background: #ffffff;
                overflow: hidden;
                box-shadow: 0 1px 2px rgba(26, 74, 107, 0.06);
            }
            .mbg-expand-item.is-expanded {
                border-color: #4a9b7a;
                box-shadow: 0 4px 12px rgba(74, 155, 122, 0.12);
            }
            .mbg-expand-trigger {
                display: flex;
                width: 100%;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.875rem 1rem;
                text-align: left;
                background: transparent;
                border: none;
                cursor: pointer;
            }
            .mbg-expand-item.is-expanded .mbg-expand-trigger {
                background: #f8fbfd;
                border-bottom: 1px solid #d4e8f4;
            }
            .mbg-expand-title {
                font-size: 0.8125rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                color: #1a4a6b;
            }
            .mbg-expand-summary {
                display: block;
                margin-top: 0.2rem;
                font-size: 0.75rem;
                font-weight: 500;
                color: #64748b;
                text-transform: none;
                letter-spacing: normal;
            }
            .mbg-expand-chevron {
                flex-shrink: 0;
                width: 1.25rem;
                height: 1.25rem;
                color: #7fa8c9;
                transition: transform 0.2s ease;
            }
            .mbg-expand-item.is-expanded .mbg-expand-chevron {
                transform: rotate(180deg);
                color: #4a9b7a;
            }
            .mbg-expand-body {
                padding: 1rem;
            }
            .mbg-expand-body[hidden] {
                display: none !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            window.MbgMobileAccordion = (function () {
                function isInteractive(el) {
                    return !!el.closest('button:not(.mbg-expand-trigger), a, input, select, textarea, label, .mbg-no-expand');
                }

                function setExpanded(item, expanded, headerSelector, bodySelector) {
                    const header = item.querySelector(headerSelector);
                    const body = item.querySelector(bodySelector);
                    item.classList.toggle('is-expanded', expanded);
                    if (header) header.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                    if (body) body.hidden = !expanded;
                }

                function collapseAll(items, except, headerSelector, bodySelector) {
                    items.forEach(function (item) {
                        if (item === except) return;
                        setExpanded(item, false, headerSelector, bodySelector);
                    });
                }

                function initGroup(container, options) {
                    if (!container) return null;

                    options = options || {};
                    const itemSelector = options.itemSelector || '[data-expand-item]';
                    const headerSelector = options.headerSelector || '[data-expand-header]';
                    const bodySelector = options.bodySelector || '[data-expand-body]';
                    const singleOpen = options.singleOpen !== false;
                    const items = Array.from(container.querySelectorAll(itemSelector));

                    function toggle(item, forceOpen) {
                        const isExpanded = item.classList.contains('is-expanded');
                        const shouldOpen = typeof forceOpen === 'boolean' ? forceOpen : !isExpanded;

                        if (singleOpen && shouldOpen) {
                            collapseAll(items, item, headerSelector, bodySelector);
                        }

                        setExpanded(item, shouldOpen, headerSelector, bodySelector);
                    }

                    items.forEach(function (item, idx) {
                        const header = item.querySelector(headerSelector);
                        if (!header) return;

                        header.addEventListener('click', function (e) {
                            if (isInteractive(e.target)) return;
                            toggle(item);
                        });

                        if (options.expandFirst && idx === 0) {
                            toggle(item, true);
                        } else if (!item.classList.contains('is-expanded')) {
                            setExpanded(item, false, headerSelector, bodySelector);
                        }
                    });

                    return {
                        items: items,
                        toggle: toggle,
                        collapseAll: function () {
                            collapseAll(items, null, headerSelector, bodySelector);
                        },
                        expandItem: function (item) {
                            toggle(item, true);
                        },
                    };
                }

                function bindSummary(input, summaryEl, fallback) {
                    if (!input || !summaryEl) return;
                    const update = function () {
                        const val = String(input.value || '').trim();
                        summaryEl.textContent = val !== '' ? val : (fallback || 'Belum diisi');
                    };
                    input.addEventListener('input', update);
                    input.addEventListener('change', update);
                    update();
                }

                return { initGroup: initGroup, bindSummary: bindSummary };
            })();
        </script>
    @endpush
@endonce
