<script>
const pricingContextStorageKey = 'storefrontPricingContext:v1';
const pricingContextStorageTtlMs = 24 * 60 * 60 * 1000;
const pricingContextEndpoint = @json(route('storefront.pricing.context', ['locale' => app()->getLocale()]));
let frontendPricingContext = normalizePricingContext(@json($frontendPricingContext));

function showToast(message) {
  const toast = document.getElementById('globalToast');

  if (!toast || !message) {
    return;
  }

  toast.textContent = message;
  toast.classList.add('show');

  window.setTimeout(() => {
    toast.classList.remove('show');
  }, 2800);
}

function openLoginModal() {
  document.getElementById('loginModal')?.classList.add('open');
}

function closeLoginModal() {
  document.getElementById('loginModal')?.classList.remove('open');
}

function openSearchModal() {
  closeNavbarActionsDropdown();
  closeNavbarCartDropdown();
  document.getElementById('searchModal')?.classList.add('open');
  window.setTimeout(() => document.getElementById('searchModalInput')?.focus(), 60);
}

function closeSearchModal() {
  document.getElementById('searchModal')?.classList.remove('open');
}

document.getElementById('loginModal')?.addEventListener('click', function (event) {
  if (event.target === this) {
    closeLoginModal();
  }
});

document.getElementById('searchModal')?.addEventListener('click', function (event) {
  if (event.target === this) {
    closeSearchModal();
  }
});

if (@json((bool) ($errors->has('email') && session('auth_modal_tab') === 'login'))) {
  openLoginModal();
}

if (@json(session('success'))) {
  showToast(@json(session('success')));
}

function toggleMobileMenu() {
  document.getElementById('mobileMenu')?.classList.toggle('open');
  document.getElementById('mobileOverlay')?.classList.toggle('open');
}

function toggleMobileAccountMenu() {
  const menu = document.getElementById('mobileAccountMenu');
  const trigger = document.getElementById('mobileAccountTrigger');

  if (!menu || !trigger) {
    return;
  }

  const isOpen = menu.classList.toggle('open');
  trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
}

function toggleNavbarActionsDropdown() {
  closeNavbarCartDropdown();

  const menu = document.getElementById('navbarActionsMenu');
  const trigger = document.getElementById('navbarActionsTrigger');

  if (!menu || !trigger) {
    return;
  }

  const isOpen = !menu.classList.contains('open');
  menu.classList.toggle('open', isOpen);
  trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
}

function closeNavbarActionsDropdown() {
  const menu = document.getElementById('navbarActionsMenu');
  const trigger = document.getElementById('navbarActionsTrigger');

  if (!menu || !trigger) {
    return;
  }

  menu.classList.remove('open');
  trigger.setAttribute('aria-expanded', 'false');
}

function normalizePricingContext(context) {
  const normalized = {
    base_currency: context?.base_currency || 'BHD',
    detected_country_code: context?.detected_country_code || null,
    detected_currency: context?.detected_currency || null,
    rate: Number(context?.rate || 0),
    rate_date: context?.rate_date || null,
    enabled: Boolean(context?.enabled),
  };

  if (!normalized.enabled || !normalized.detected_currency || !Number.isFinite(normalized.rate) || normalized.rate <= 0) {
    return {
      ...normalized,
      rate: null,
      enabled: false,
    };
  }

  return normalized;
}

function readStoredPricingContext() {
  try {
    const raw = localStorage.getItem(pricingContextStorageKey);

    if (!raw) {
      return null;
    }

    const parsed = JSON.parse(raw);

    if (!parsed?.saved_at || (Date.now() - Number(parsed.saved_at)) > pricingContextStorageTtlMs) {
      localStorage.removeItem(pricingContextStorageKey);
      return null;
    }

    return normalizePricingContext(parsed.context || {});
  } catch (error) {
    return null;
  }
}

function rememberPricingContext(context) {
  try {
    localStorage.setItem(pricingContextStorageKey, JSON.stringify({
      saved_at: Date.now(),
      context,
    }));
  } catch (error) {
  }
}

function clearStoredPricingContext() {
  try {
    localStorage.removeItem(pricingContextStorageKey);
  } catch (error) {
  }
}

function formatCurrencyAmount(amount, currency) {
  try {
    return new Intl.NumberFormat(document.documentElement.lang || 'en', {
      style: 'currency',
      currency,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(amount);
  } catch (error) {
    return `${amount.toFixed(2)} ${currency}`;
  }
}

function renderPriceNode(root) {
  if (!root) {
    return;
  }

  const primaryNode = root.querySelector('[data-bhd-primary]');
  const bhdAmount = Number(root.dataset.bhdAmount || 0);
  const bhdCurrency = root.dataset.bhdCurrency || 'BHD';

  if (!primaryNode || !Number.isFinite(bhdAmount)) {
    return;
  }

  if (!root.dataset.basePriceLabel) {
    root.dataset.basePriceLabel = primaryNode.textContent.trim();
  }

  if (!frontendPricingContext.enabled || bhdCurrency !== 'BHD') {
    primaryNode.textContent = root.dataset.basePriceLabel || formatCurrencyAmount(bhdAmount, bhdCurrency);
    return;
  }

  const convertedAmount = bhdAmount * frontendPricingContext.rate;
  primaryNode.textContent = formatCurrencyAmount(convertedAmount, frontendPricingContext.detected_currency);
}

function renderAllPriceNodes() {
  document.querySelectorAll('[data-price-root]').forEach((node) => {
    renderPriceNode(node);
  });
}

async function ensurePricingContext() {
  const storedContext = readStoredPricingContext();

  if (storedContext?.enabled) {
    frontendPricingContext = storedContext;
    renderAllPriceNodes();
    return;
  }

  if (frontendPricingContext.enabled) {
    rememberPricingContext(frontendPricingContext);
    renderAllPriceNodes();
    return;
  }

  try {
    const response = await fetch(pricingContextEndpoint, {
      headers: {
        'Accept': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Failed to load pricing context.');
    }

    const payload = await response.json();
    frontendPricingContext = normalizePricingContext(payload?.pricing || {});

    if (frontendPricingContext.enabled) {
      rememberPricingContext(frontendPricingContext);
    } else {
      clearStoredPricingContext();
    }
  } catch (error) {
    frontendPricingContext = normalizePricingContext({});
  }

  renderAllPriceNodes();
}

async function refreshNavbarCartSummary() {
  try {
    const response = await fetch(@json(route('storefront.cart.summary', ['locale' => app()->getLocale()])), {
      headers: {
        'Accept': 'application/json',
      },
    });

    if (!response.ok) {
      return;
    }

    const data = await response.json();
    const cart = data.cart || {};
    const itemsCount = Number(cart.items_count || 0);
    const subtotal = Number(cart.subtotal || 0);
    const currency = cart.currency || @json($frontendCartSummary['currency']);
    const cartCount = document.getElementById('cartCount');
    const cartSummaryCount = document.getElementById('cartSummaryCount');
    const cartSummarySubtotal = document.getElementById('cartSummarySubtotal');
    const cartSummarySubtotalPrimary = cartSummarySubtotal?.querySelector('[data-bhd-primary]');
    const cartSummaryEmpty = document.getElementById('cartSummaryEmpty');
    const cartCheckoutButton = document.getElementById('cartCheckoutButton');

    if (cartCount) {
      cartCount.textContent = String(itemsCount);
    }

    if (cartSummaryCount) {
      cartSummaryCount.textContent = String(itemsCount);
    }

    if (cartSummarySubtotal && cartSummarySubtotalPrimary) {
      cartSummarySubtotal.dataset.bhdAmount = subtotal.toFixed(2);
      cartSummarySubtotal.dataset.bhdCurrency = currency;
      cartSummarySubtotal.dataset.basePriceLabel = `${subtotal.toFixed(2)} ${currency}`;
      cartSummarySubtotalPrimary.textContent = cartSummarySubtotal.dataset.basePriceLabel;
      renderPriceNode(cartSummarySubtotal);
    }

    if (cartSummaryEmpty) {
      cartSummaryEmpty.classList.toggle('hidden', itemsCount > 0);
    }

    if (cartCheckoutButton) {
      cartCheckoutButton.classList.toggle('is-disabled', itemsCount === 0);
    }
  } catch (error) {
  }
}

async function toggleNavbarCartDropdown() {
  closeNavbarActionsDropdown();

  const menu = document.getElementById('navbarCartMenu');
  const trigger = document.getElementById('navbarCartTrigger');

  if (!menu || !trigger) {
    return;
  }

  const isOpen = !menu.classList.contains('open');

  if (isOpen) {
    await refreshNavbarCartSummary();
  }

  menu.classList.toggle('open', isOpen);
  trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
}

function closeNavbarCartDropdown() {
  const menu = document.getElementById('navbarCartMenu');
  const trigger = document.getElementById('navbarCartTrigger');

  if (!menu || !trigger) {
    return;
  }

  menu.classList.remove('open');
  trigger.setAttribute('aria-expanded', 'false');
}

document.addEventListener('click', function (event) {
  const dropdown = document.querySelector('.navbar-actions-dropdown');
  const cartDropdown = document.querySelector('.navbar-cart-dropdown');

  if (dropdown && !dropdown.contains(event.target)) {
    closeNavbarActionsDropdown();
  }

  if (cartDropdown && !cartDropdown.contains(event.target)) {
    closeNavbarCartDropdown();
  }
});

document.addEventListener('keydown', function (event) {
  if (event.key === 'Escape') {
    closeSearchModal();
  }
});

function closeWelcomeCouponPopup() {
  localStorage.setItem('welcomeCouponDismissed', '1');
  document.getElementById('welcomeCouponPopup')?.classList.remove('open');
}

if (document.getElementById('welcomeCouponPopup') && localStorage.getItem('welcomeCouponDismissed') !== '1') {
  window.setTimeout(() => {
    document.getElementById('welcomeCouponPopup')?.classList.add('open');
  }, 5000);
}

document.getElementById('welcomeCouponPopup')?.addEventListener('click', function (event) {
  if (event.target === this) {
    closeWelcomeCouponPopup();
  }
});

document.addEventListener('DOMContentLoaded', function () {
  renderAllPriceNodes();
  ensurePricingContext();
});

async function submitWelcomeCoupon(event) {
  event.preventDefault();

  const form = document.getElementById('welcomeCouponForm');
  const status = document.getElementById('welcomeCouponStatus');
  const button = form?.querySelector('button[type="submit"]');

  if (! form || ! status) {
    return;
  }

  const formData = new FormData(form);

  if (button) {
    button.disabled = true;
  }

  status.textContent = '';

  try {
    const response = await fetch(@json(route('storefront.welcome-coupon.store')), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': @json(csrf_token()),
      },
      body: JSON.stringify({
        email: formData.get('email'),
      }),
    });

    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.message || @json(__('storefront.welcome_coupon.failed_message')));
    }

    showToast(data.message || @json(__('storefront.welcome_coupon.sent_message')));
    form.reset();
    document.getElementById('welcomeCouponPopup')?.remove();
  } catch (error) {
    status.textContent = error.message || @json(__('storefront.welcome_coupon.failed_message'));
  } finally {
    if (button) {
      button.disabled = false;
    }
  }
}

window.addEventListener('scroll', () => {
  const navbar = document.querySelector('.navbar');

  if (!navbar) {
    return;
  }

  navbar.style.background = window.scrollY > 50 ? 'var(--backdrop-nav-strong)' : 'var(--backdrop-nav)';
});

if (window.gsap && window.ScrollTrigger) {
  gsap.registerPlugin(ScrollTrigger);

  document.querySelectorAll('.reveal').forEach((element, index) => {
    gsap.fromTo(element, { opacity: 0, y: 30 }, {
      opacity: 1,
      y: 0,
      duration: 0.8,
      ease: 'power3.out',
      delay: (index % 4) * 0.08,
      scrollTrigger: {
        trigger: element,
        start: 'top 85%',
        once: true,
      },
    });
  });
}
</script>
