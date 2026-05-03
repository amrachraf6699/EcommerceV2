import './bootstrap';
import 'flatpickr/dist/flatpickr.min.css';
import 'tom-select/dist/css/tom-select.default.css';
import 'filepond/dist/filepond.min.css';

import ApexCharts from 'apexcharts';
import flatpickr from 'flatpickr';
import * as FilePond from 'filepond';
import TomSelect from 'tom-select';

let tinyMceLoader;

const initTomSelect = () => {
    document.querySelectorAll('[data-tom-select]').forEach((element) => {
        if (element.dataset.enhanced === 'true') {
            return;
        }

        new TomSelect(element, {
            maxOptions: 300,
            create: false,
            plugins: element.multiple ? ['remove_button'] : [],
            placeholder: element.getAttribute('placeholder') || 'اختر من القائمة',
        });

        element.dataset.enhanced = 'true';
    });
};

const initFilePond = () => {
    document.querySelectorAll('[data-filepond]').forEach((element) => {
        if (element.dataset.enhanced === 'true') {
            return;
        }

        FilePond.create(element, {
            allowMultiple: element.hasAttribute('multiple'),
            credits: false,
            storeAsFile: true,
            labelIdle: 'اسحب الملفات هنا أو <span class="filepond--label-action">اختر من الجهاز</span>',
        });

        element.dataset.enhanced = 'true';
    });
};

const initDatePickers = () => {
    document.querySelectorAll('[data-datepicker]').forEach((element) => {
        if (element.dataset.enhanced === 'true') {
            return;
        }

        flatpickr(element, {
            dateFormat: 'Y-m-d',
            disableMobile: true,
        });

        element.dataset.enhanced = 'true';
    });
};

const initRichTextEditors = () => {
    const editors = Array.from(document.querySelectorAll('[data-rich-text]'));

    if (! editors.length) {
        return;
    }

    tinyMceLoader ??= (async () => {
        const tinymceModule = await import('tinymce');
        const tinymce = tinymceModule.default;

        globalThis.tinymce = tinymce;

        await import('tinymce/icons/default/icons.min.js');
        await import('tinymce/themes/silver/theme.min.js');
        await import('tinymce/models/dom/model.min.js');
        await import('tinymce/skins/ui/oxide/skin.js');
        await import('tinymce/skins/ui/oxide/content.js');
        await import('tinymce/skins/content/default/content.js');
        await import('tinymce/plugins/autoresize');
        await import('tinymce/plugins/code');
        await import('tinymce/plugins/fullscreen');
        await import('tinymce/plugins/image');
        await import('tinymce/plugins/link');
        await import('tinymce/plugins/lists');
        await import('tinymce/plugins/table');

        return tinymce;
    })();

    tinyMceLoader.then((tinymce) => {
        editors.forEach((element, index) => {
            if (! element.id) {
                element.id = `rich-text-${index + 1}`;
            }

            if (tinymce.get(element.id)) {
                return;
            }

            tinymce.init({
                selector: `#${element.id}`,
                height: 520,
                menubar: false,
                license_key: 'gpl',
                plugins: 'lists link image table code fullscreen autoresize',
                toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | alignleft aligncenter alignright | link image table | code fullscreen',
                directionality: 'rtl',
                branding: false,
                promotion: false,
                skin: 'oxide',
                content_css: 'default',
            });
        });
    });
};

const initConfirmTriggers = () => {
    document.querySelectorAll('[data-confirm-trigger]').forEach((button) => {
        if (button.dataset.enhanced === 'true') {
            return;
        }

        button.addEventListener('click', () => {
            window.alert(`${button.dataset.confirmTitle}\n\n${button.dataset.confirmText}`);
        });

        button.dataset.enhanced = 'true';
    });
};

const initLoadingForms = () => {
    document.querySelectorAll('[data-loading-form]').forEach((form) => {
        if (form.dataset.enhanced === 'true') {
            return;
        }

        form.addEventListener('submit', () => {
            const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');

            submitButtons.forEach((button) => {
                if (button.dataset.originalLabel === undefined) {
                    button.dataset.originalLabel = button.tagName === 'INPUT' ? button.value : button.textContent.trim();
                }

                const pendingLabel = button.dataset.loadingLabel;

                if (pendingLabel) {
                    if (button.tagName === 'INPUT') {
                        button.value = pendingLabel;
                    } else {
                        button.textContent = pendingLabel;
                    }
                }

                button.disabled = true;
                button.classList.add('opacity-70', 'cursor-not-allowed');
            });
        });

        form.dataset.enhanced = 'true';
    });
};

const initConfirmedForms = () => {
    document.querySelectorAll('form[data-confirm-title]').forEach((form) => {
        if (form.dataset.confirmEnhanced === 'true') {
            return;
        }

        form.addEventListener('submit', (event) => {
            const title = form.dataset.confirmTitle || 'تأكيد الإجراء';
            const text = form.dataset.confirmText || 'هل تريد المتابعة؟';

            if (! window.confirm(`${title}\n\n${text}`)) {
                event.preventDefault();
            }
        });

        form.dataset.confirmEnhanced = 'true';
    });
};

const showToast = (message, tone = 'success') => {
    if (! message) {
        return;
    }

    let container = document.querySelector('[data-toast-container]');

    if (! container) {
        container = document.createElement('div');
        container.dataset.toastContainer = 'true';
        container.className = 'admin-toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');

    toast.className = `admin-toast admin-toast--${tone === 'error' ? 'error' : 'success'}`;
    toast.textContent = message;

    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.add('is-visible');
    });

    window.setTimeout(() => {
        toast.classList.remove('is-visible');

        window.setTimeout(() => {
            toast.remove();

            if (! container.childElementCount) {
                container.remove();
            }
        }, 250);
    }, 3500);
};

const initToasts = () => {
    const toastMessages = document.querySelectorAll('[data-toast-message]');

    if (! toastMessages.length) {
        return;
    }

    toastMessages.forEach((message) => {
        if (message.dataset.toastEnhanced === 'true') {
            return;
        }

        showToast(message.dataset.toastText || '', message.dataset.toastType === 'error' ? 'error' : 'success');
        message.dataset.toastEnhanced = 'true';
    });
};

const initTopbarBreadcrumbs = () => {};

const initSidebarToggle = () => {
    const sidebar = document.querySelector('[data-admin-sidebar]');
    const overlay = document.querySelector('[data-sidebar-overlay]');
    const toggleButtons = document.querySelectorAll('[data-sidebar-toggle]');
    const closeButtons = document.querySelectorAll('[data-sidebar-close]');

    if (! sidebar || ! overlay) {
        return;
    }

    const openSidebar = () => {
        sidebar.classList.add('is-open');
        overlay.classList.add('is-visible');
        toggleButtons.forEach((button) => button.setAttribute('aria-expanded', 'true'));
        document.body.classList.add('overflow-hidden');
    };

    const closeSidebar = () => {
        sidebar.classList.remove('is-open');
        overlay.classList.remove('is-visible');
        toggleButtons.forEach((button) => button.setAttribute('aria-expanded', 'false'));
        document.body.classList.remove('overflow-hidden');
    };

    toggleButtons.forEach((button) => {
        if (button.dataset.enhanced === 'true') {
            return;
        }

        button.addEventListener('click', () => {
            if (sidebar.classList.contains('is-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        button.dataset.enhanced = 'true';
    });

    closeButtons.forEach((button) => {
        if (button.dataset.enhanced === 'true') {
            return;
        }

        button.addEventListener('click', closeSidebar);
        button.dataset.enhanced = 'true';
    });

    if (overlay.dataset.enhanced !== 'true') {
        overlay.addEventListener('click', closeSidebar);
        overlay.dataset.enhanced = 'true';
    }

    if (document.body.dataset.sidebarEscapeEnhanced !== 'true') {
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeSidebar();
            }
        });

        document.body.dataset.sidebarEscapeEnhanced = 'true';
    }
};

const initAdminTabs = () => {
    document.querySelectorAll('[data-admin-tabs]').forEach((tabsRoot) => {
        if (tabsRoot.dataset.tabsEnhanced === 'true') {
            return;
        }

        const triggers = Array.from(tabsRoot.querySelectorAll('[data-admin-tab-trigger]'));
        const panels = Array.from(tabsRoot.querySelectorAll('[data-admin-tab-panel]'));
        const defaultTab = triggers[0]?.dataset.adminTabTrigger;

        const activateTab = (tabKey) => {
            triggers.forEach((trigger) => {
                const isActive = trigger.dataset.adminTabTrigger === tabKey;
                trigger.classList.toggle('is-active', isActive);
                trigger.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panels.forEach((panel) => {
                panel.classList.toggle('hidden', panel.dataset.adminTabPanel !== tabKey);
            });
        };

        triggers.forEach((trigger) => {
            trigger.addEventListener('click', () => {
                activateTab(trigger.dataset.adminTabTrigger);
            });
        });

        if (defaultTab) {
            activateTab(defaultTab);
        }

        tabsRoot.dataset.tabsEnhanced = 'true';
    });
};

const clearAjaxErrors = (container) => {
    if (! container) {
        return;
    }

    container.classList.add('hidden');
    container.innerHTML = '';
};

const renderAjaxErrors = (container, errors) => {
    if (! container) {
        showToast('Please review the highlighted fields and try again.', 'error');
        return;
    }

    const items = Object.values(errors || {})
        .flat()
        .filter(Boolean)
        .map((message) => `<li>${message}</li>`)
        .join('');

    container.innerHTML = items
        ? `<ul class="list-disc space-y-1 pr-5">${items}</ul>`
        : 'Please review the form and try again.';
    container.classList.remove('hidden');
};

const syncRichTextEditors = (form) => {
    if (! globalThis.tinymce) {
        return;
    }

    form.querySelectorAll('[data-rich-text]').forEach((element) => {
        globalThis.tinymce.get(element.id)?.save();
    });
};

const refreshProductEditorFragments = (payload) => {
    if (payload?.fragments?.basic) {
        const basicPanel = document.getElementById('productBasicPanel');

        if (basicPanel) {
            basicPanel.innerHTML = payload.fragments.basic;
        }
    }

    if (payload?.fragments?.seo) {
        const seoPanel = document.getElementById('productSeoPanel');

        if (seoPanel) {
            seoPanel.innerHTML = payload.fragments.seo;
        }
    }

    if (payload?.fragments?.variants) {
        const variantsPanel = document.getElementById('productVariantsPanel');

        if (variantsPanel) {
            variantsPanel.innerHTML = payload.fragments.variants;
        }
    }

    if (payload?.fragments?.images) {
        const imagesPanel = document.getElementById('productImagesPanel');

        if (imagesPanel) {
            imagesPanel.innerHTML = payload.fragments.images;
        }
    }

    initTomSelect();
    initFilePond();
    initLoadingForms();
    initConfirmedForms();
    initAjaxForms();
};

const initAjaxForms = () => {
    document.querySelectorAll('form[data-ajax-form]').forEach((form) => {
        if (form.dataset.ajaxEnhanced === 'true') {
            return;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            syncRichTextEditors(form);

            const errorBox = form.querySelector('[data-ajax-errors]')
                || form.closest('[data-product-editor]')?.querySelector('[data-product-editor-errors]');

            clearAjaxErrors(errorBox);

            const submitButtons = Array.from(form.querySelectorAll('button[type="submit"], input[type="submit"]'));

            submitButtons.forEach((button) => {
                button.disabled = true;
                button.classList.add('opacity-70', 'cursor-not-allowed');
            });

            try {
                const response = await window.fetch(form.action, {
                    method: form.method || 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                    credentials: 'same-origin',
                });

                const payload = await response.json().catch(() => ({}));

                if (response.status === 422) {
                    renderAjaxErrors(errorBox, payload.errors || {});
                    return;
                }

                if (! response.ok) {
                    throw new Error(payload.message || 'Request failed.');
                }

                clearAjaxErrors(errorBox);
                refreshProductEditorFragments(payload);
                showToast(payload.message || 'Saved successfully.');
            } catch (error) {
                renderAjaxErrors(errorBox, {
                    form: [error.message || 'Unexpected error.'],
                });
            } finally {
                submitButtons.forEach((button) => {
                    button.disabled = false;
                    button.classList.remove('opacity-70', 'cursor-not-allowed');
                });
            }
        });

        form.dataset.ajaxEnhanced = 'true';
    });
};

const initProductVariantRows = () => {
    document.querySelectorAll('[data-product-variants]').forEach((container) => {
        if (container.dataset.variantsEnhanced === 'true') {
            return;
        }

        const list = container.querySelector('[data-variant-list]');
        const template = container.querySelector('[data-variant-template]');
        const addButton = container.querySelector('[data-add-variant]');
        const imageVariantSelect = container.querySelector('[data-image-variant-select]');

        if (! list || ! template || ! addButton) {
            return;
        }

        let nextIndex = list.querySelectorAll('[data-variant-row]').length;

        const getVariantLabel = (row, index) => {
            const name = row.querySelector('[name$="[name]"]')?.value.trim();
            const sku = row.querySelector('[name$="[sku]"]')?.value.trim();

            return name || sku || `نسخة ${index + 1}`;
        };

        const getVariantIndex = (row) => {
            const input = row.querySelector('[name^="variants["]');
            const match = input?.name.match(/^variants\[(\d+)]/);

            return match ? match[1] : '';
        };

        const updateRows = () => {
            const rows = Array.from(list.querySelectorAll('[data-variant-row]'));

            rows.forEach((row, index) => {
                const number = row.querySelector('[data-variant-number]');

                if (number) {
                    number.textContent = index + 1;
                }

                row.querySelector('[data-remove-variant]')?.classList.toggle('hidden', rows.length === 1);
            });

            if (imageVariantSelect) {
                const selectedValue = imageVariantSelect.value;
                imageVariantSelect.replaceChildren(new Option('لكل النسخ', ''));

                rows.forEach((row, index) => {
                    imageVariantSelect.appendChild(new Option(getVariantLabel(row, index), getVariantIndex(row)));
                });

                imageVariantSelect.value = selectedValue;
            }
        };

        addButton.addEventListener('click', () => {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(nextIndex));
            list.appendChild(wrapper.firstElementChild);
            nextIndex += 1;
            updateRows();
        });

        list.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-remove-variant]');

            if (! removeButton) {
                return;
            }

            const rows = list.querySelectorAll('[data-variant-row]');

            if (rows.length <= 1) {
                return;
            }

            removeButton.closest('[data-variant-row]')?.remove();
            updateRows();
        });

        list.addEventListener('input', (event) => {
            if (event.target.matches('[name$="[name]"], [name$="[sku]"]')) {
                updateRows();
            }
        });

        list.addEventListener('change', (event) => {
            if (! event.target.matches('[name$="[is_default]"]') || ! event.target.checked) {
                return;
            }

            list.querySelectorAll('[name$="[is_default]"]').forEach((checkbox) => {
                if (checkbox !== event.target) {
                    checkbox.checked = false;
                }
            });
        });

        updateRows();
        container.dataset.variantsEnhanced = 'true';
    });
};

const initDashboardCharts = () => {
    document.querySelectorAll('[data-dashboard-chart]').forEach((container) => {
        if (container.dataset.enhanced === 'true') {
            return;
        }

        const canvas = container.querySelector('[data-dashboard-chart-canvas]');

        if (! canvas) {
            return;
        }

        const labels = JSON.parse(container.dataset.chartLabels || '[]');
        const orders = JSON.parse(container.dataset.chartOrders || '[]');
        const revenue = JSON.parse(container.dataset.chartRevenue || '[]');

        const chart = new ApexCharts(canvas, {
            chart: {
                type: 'line',
                height: 320,
                toolbar: {
                    show: false,
                },
                foreColor: '#cbd5e1',
            },
            stroke: {
                width: [3, 3],
                curve: 'smooth',
            },
            colors: ['#f59e0b', '#10b981'],
            grid: {
                borderColor: 'rgba(255,255,255,0.08)',
            },
            xaxis: {
                categories: labels,
                labels: {
                    style: {
                        colors: '#94a3b8',
                    },
                },
            },
            yaxis: [
                {
                    title: {
                        text: 'الطلبات',
                        style: {
                            color: '#94a3b8',
                        },
                    },
                    labels: {
                        style: {
                            colors: '#94a3b8',
                        },
                    },
                },
                {
                    opposite: true,
                    title: {
                        text: 'الإيرادات',
                        style: {
                            color: '#94a3b8',
                        },
                    },
                    labels: {
                        style: {
                            colors: '#94a3b8',
                        },
                    },
                },
            ],
            legend: {
                labels: {
                    colors: '#e2e8f0',
                },
            },
            series: [
                {
                    name: 'الطلبات',
                    type: 'line',
                    data: orders,
                },
                {
                    name: 'الإيرادات',
                    type: 'area',
                    data: revenue,
                },
            ],
            fill: {
                type: ['solid', 'gradient'],
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.2,
                    opacityTo: 0.02,
                    stops: [0, 90, 100],
                },
            },
            dataLabels: {
                enabled: false,
            },
            tooltip: {
                theme: 'dark',
            },
        });

        chart.render();
        container.dataset.enhanced = 'true';
    });
};

const initDashboardRangeFilter = () => {
    document.querySelectorAll('[data-dashboard-range-form]').forEach((form) => {
        if (form.dataset.enhanced === 'true') {
            return;
        }

        const select = form.querySelector('[data-dashboard-range-select]');

        if (! select) {
            return;
        }

        const submitRequest = async () => {
            const range = select.value;
            const url = new URL(form.action, window.location.origin);

            url.searchParams.set('range', range);

            select.disabled = true;

            try {
                const response = await window.fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const payload = await response.json().catch(() => ({}));

                if (! response.ok || ! payload.html) {
                    throw new Error('تعذر تحديث البيانات حالياً.');
                }

                const container = document.getElementById('adminDashboardContent');

                if (! container) {
                    throw new Error('تعذر تحديث واجهة لوحة التحكم.');
                }

                container.innerHTML = payload.html;
                window.history.replaceState({}, '', url);

                initDashboardRangeFilter();
                initDashboardCharts();
            } catch (error) {
                showToast(error.message || 'حدث خطأ غير متوقع.', 'error');
            } finally {
                select.disabled = false;
            }
        };

        select.addEventListener('change', submitRequest);
        form.dataset.enhanced = 'true';
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initTomSelect();
    initFilePond();
    initDatePickers();
    initRichTextEditors();
    initConfirmTriggers();
    initLoadingForms();
    initConfirmedForms();
    initToasts();
    initTopbarBreadcrumbs();
    initSidebarToggle();
    initAdminTabs();
    initAjaxForms();
    initProductVariantRows();
    initDashboardRangeFilter();
    initDashboardCharts();
});

window.AdminUi = {
    initTomSelect,
    initFilePond,
    initDatePickers,
    initRichTextEditors,
    initConfirmTriggers,
    initLoadingForms,
    initConfirmedForms,
    initToasts,
    showToast,
    initTopbarBreadcrumbs,
    initSidebarToggle,
    initAdminTabs,
    initAjaxForms,
    initProductVariantRows,
    initDashboardRangeFilter,
    initDashboardCharts,
    FilePond,
};
