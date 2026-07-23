<div
  id="chatbotWidget"
  data-chatbot-widget
  data-chatbot-locale="{{ app()->getLocale() }}"
  data-categories-endpoint="{{ route('storefront.chatbot.categories.index') }}"
  data-category-products-template="{{ route('storefront.chatbot.categories.products.index', ['category' => '__CATEGORY__']) }}"
  data-category-fallback-template="{{ route('storefront.chatbot.categories.fallback-products.index', ['category' => '__CATEGORY__']) }}"
  data-product-variants-template="{{ route('storefront.chatbot.products.variants.index', ['product' => '__PRODUCT__']) }}"
  data-cart-items-endpoint="{{ route('storefront.chatbot.cart-items.store') }}"
  data-checkout-url="{{ route('storefront.checkout.show') }}"
></div>

<button
  type="button"
  class="chatbot-float chatbot-trigger"
  id="chatbotTrigger"
  aria-controls="chatbotPanel"
  aria-expanded="false"
  aria-label="{{ __('storefront.chatbot.trigger_aria') }}"
>
  <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
    <path d="M17.7530511 13.999921C18.9956918 13.999921 20.0030511 15.0072804 20.0030511 16.249921L20.0030511 17.1550008C20.0030511 18.2486786 19.5255957 19.2878579 18.6957793 20.0002733C17.1303315 21.344244 14.8899962 22.0010712 12 22.0010712C9.11050247 22.0010712 6.87168436 21.3444691 5.30881727 20.0007885C4.48019625 19.2883988 4.00354153 18.2500002 4.00354153 17.1572408L4.00354153 16.249921C4.00354153 15.0072804 5.01090084 13.999921 6.25354153 13.999921L17.7530511 13.999921ZM17.7530511 15.499921L6.25354153 15.499921C5.83932796 15.499921 5.50354153 15.8357075 5.50354153 16.249921L5.50354153 17.1572408C5.50354153 17.8128951 5.78953221 18.4359296 6.28670709 18.8633654C7.5447918 19.9450082 9.44080155 20.5010712 12 20.5010712C14.5599799 20.5010712 16.4578003 19.9446634 17.7186879 18.8621641C18.2165778 18.4347149 18.5030511 17.8112072 18.5030511 17.1550005L18.5030511 16.249921C18.5030511 15.8357075 18.1672647 15.499921 17.7530511 15.499921ZM11.8985607 2.00734093L12.0003312 2.00049432C12.380027 2.00049432 12.6938222 2.2826482 12.7434846 2.64872376L12.7503312 2.75049432L12.7495415 3.49949432L16.25 3.5C17.4926407 3.5 18.5 4.50735931 18.5 5.75L18.5 10.254591C18.5 11.4972317 17.4926407 12.504591 16.25 12.504591L7.75 12.504591C6.50735931 12.504591 5.5 11.4972317 5.5 10.254591L5.5 5.75C5.5 4.50735931 6.50735931 3.5 7.75 3.5L11.2495415 3.49949432L11.2503312 2.75049432C11.2503312 2.37079855 11.5324851 2.05700336 11.8985607 2.00734093ZM16.25 5L7.75 5C7.33578644 5 7 5.33578644 7 5.75L7 10.254591C7 10.6688046 7.33578644 11.004591 7.75 11.004591L16.25 11.004591C16.6642136 11.004591 17 10.6688046 17 10.254591L17 5.75C17 5.33578644 16.6642136 5 16.25 5ZM9.74928905 6.5C10.4392523 6.5 10.9985781 7.05932576 10.9985781 7.74928905C10.9985781 8.43925235 10.4392523 8.99857811 9.74928905 8.99857811C9.05932576 8.99857811 8.5 8.43925235 8.5 7.74928905C8.5 7.05932576 9.05932576 6.5 9.74928905 6.5ZM14.2420255 6.5C14.9319888 6.5 15.4913145 7.05932576 15.4913145 7.74928905C15.4913145 8.43925235 14.9319888 8.99857811 14.2420255 8.99857811C13.5520622 8.99857811 12.9927364 8.43925235 12.9927364 7.74928905C12.9927364 7.05932576 13.5520622 6.5 14.2420255 6.5Z"/>
  </svg>
</button>

<section class="chatbot-panel" id="chatbotPanel" aria-hidden="true" hidden>
    <header class="chatbot-panel__header">
      <div>
        <p class="chatbot-panel__eyebrow">{{ $frontendBrand['name'] }}</p>
        <h2 class="chatbot-panel__title">{{ __('storefront.chatbot.title') }}</h2>
      </div>
      <button type="button" class="chatbot-panel__close" id="chatbotClose" aria-label="{{ __('storefront.chatbot.actions.close_chat') }}">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
          <line x1="18" y1="6" x2="6" y2="18"/>
          <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </header>

    <div class="chatbot-panel__messages" id="chatbotMessages"></div>
    <div class="chatbot-panel__composer" id="chatbotComposer"></div>
  </section>

<style>
  .chatbot-float { position:fixed; right:24px; bottom:24px; width:58px; height:58px; display:inline-flex; align-items:center; justify-content:center; background:var(--white); color:var(--black); border:1px solid var(--line-mid); border-radius:0; box-shadow:0 16px 32px rgb(var(--shadow-rgb) / .24); z-index:1200; transition:transform .25s ease, box-shadow .25s ease, background-color .25s ease, color .25s ease, border-color .25s ease; cursor:pointer; }
  .chatbot-float:hover { transform:translateY(-4px) scale(1.03); box-shadow:0 22px 40px rgb(var(--shadow-rgb) / .32); background:var(--black); color:var(--white); border-color:var(--line-strong); }
  .chatbot-trigger { left:auto; right:24px; z-index:1200; }
  .chatbot-panel { position:fixed; right:24px; bottom:96px; z-index:1299; width:min(380px, calc(100vw - 32px)); height:min(620px, calc(100vh - 120px)); display:flex; flex-direction:column; border:1px solid var(--line-soft); background:rgb(var(--black-rgb) / .98); box-shadow:var(--panel-shadow-soft); overflow:hidden; }
  .chatbot-panel.open { display:flex; }
  .chatbot-panel[hidden] { display:none !important; }
  .chatbot-panel__header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:18px 18px 16px; border-bottom:1px solid var(--line-soft); background:linear-gradient(180deg, rgb(var(--white-rgb) / .08), rgb(var(--white-rgb) / .03)); }
  .chatbot-panel__eyebrow { margin:0 0 6px; font-size:11px; letter-spacing:.16em; text-transform:uppercase; color:var(--gray-light); font-weight:900; }
  .chatbot-panel__title { margin:0; font-size:18px; font-weight:900; }
  .chatbot-panel__close { color:var(--gray-light); background:transparent; border:0; display:inline-flex; align-items:center; justify-content:center; }
  .chatbot-panel__messages { flex:1; overflow-y:auto; padding:18px; display:flex; flex-direction:column; gap:14px; }
  .chatbot-panel__composer { border-top:1px solid var(--line-soft); padding:14px 18px 18px; background:rgb(var(--white-rgb) / .03); }
  .chatbot-message { max-width:88%; padding:12px 14px; line-height:1.7; font-size:14px; border:1px solid var(--line-soft); }
  .chatbot-message--bot { align-self:flex-start; background:rgb(var(--white-rgb) / .05); color:var(--white); }
  .chatbot-message--user { align-self:flex-end; background:var(--white); color:var(--black); border-color:var(--white); }
  .chatbot-choice-list { display:flex; flex-direction:column; gap:10px; }
  .chatbot-choice-scroller { display:grid; grid-auto-flow:column; grid-auto-columns:92px; gap:10px; overflow-x:auto; overflow-y:hidden; padding-bottom:8px; scrollbar-width:thin; scrollbar-color:rgb(var(--white-rgb) / .35) transparent; }
  .chatbot-choice-scroller::-webkit-scrollbar { height:6px; }
  .chatbot-choice-scroller::-webkit-scrollbar-track { background:transparent; }
  .chatbot-choice-scroller::-webkit-scrollbar-thumb { background:rgb(var(--white-rgb) / .28); }
  .chatbot-choice-button { width:100%; display:block; text-align:start; padding:14px 16px; border:1px solid rgb(var(--white-rgb) / .16); background:linear-gradient(180deg, rgb(var(--white-rgb) / .08), rgb(var(--white-rgb) / .03)); color:var(--white); box-shadow:0 10px 18px rgb(var(--shadow-rgb) / .16); cursor:pointer; transition:border-color .2s ease, background-color .2s ease, transform .2s ease, box-shadow .2s ease; }
  .chatbot-choice-button:hover { border-color:rgb(var(--white-rgb) / .34); background:linear-gradient(180deg, rgb(var(--white-rgb) / .14), rgb(var(--white-rgb) / .06)); transform:translateY(-1px); box-shadow:0 14px 24px rgb(var(--shadow-rgb) / .24); }
  .chatbot-choice-button:focus-visible { outline:2px solid var(--white); outline-offset:2px; }
  .chatbot-choice-button__meta { display:block; margin-top:6px; font-size:12px; color:var(--gray-light); }
  .chatbot-choice-button--card { min-height:118px; padding:0; overflow:hidden; }
  .chatbot-choice-button--card .chatbot-choice-button__meta { margin-top:4px; }
  .chatbot-card { display:flex; flex-direction:column; min-height:118px; }
  .chatbot-card__media { position:relative; aspect-ratio:1 / 1; width:100%; max-height:72px; background:rgb(var(--white-rgb) / .05); border-bottom:1px solid var(--line-soft); display:flex; align-items:center; justify-content:center; overflow:hidden; color:var(--gray-light); font-size:10px; }
  .chatbot-card__media img { width:100%; height:100%; object-fit:cover; }
  .chatbot-card__body { padding:8px 8px 10px; }
  .chatbot-card__title { display:block; font-size:11px; font-weight:800; line-height:1.4; }
  .chatbot-product-choice { display:grid; grid-template-columns:56px minmax(0,1fr); gap:12px; align-items:center; }
  .chatbot-product-choice__media { width:56px; height:56px; background:rgb(var(--white-rgb) / .05); overflow:hidden; border:1px solid var(--line-soft); display:flex; align-items:center; justify-content:center; color:var(--gray-light); font-size:11px; }
  .chatbot-product-choice__media img { width:100%; height:100%; object-fit:cover; }
  .chatbot-quantity { display:flex; flex-direction:column; gap:12px; }
  .chatbot-quantity__controls { display:flex; align-items:center; gap:10px; }
  .chatbot-quantity__step { width:42px; height:42px; border:1px solid rgb(var(--white-rgb) / .16); background:rgb(var(--white-rgb) / .06); color:var(--white); font-size:22px; cursor:pointer; transition:transform .2s ease, background-color .2s ease, border-color .2s ease; }
  .chatbot-quantity__step:hover { transform:translateY(-1px); background:rgb(var(--white-rgb) / .12); border-color:rgb(var(--white-rgb) / .28); }
  .chatbot-quantity__value { min-width:52px; text-align:center; padding:11px 10px; border:1px solid var(--line-mid); background:rgb(var(--white-rgb) / .04); color:var(--white); font-weight:800; }
  .chatbot-quantity__submit { width:100%; }
  .chatbot-actions { display:flex; flex-wrap:wrap; justify-content:center; align-items:center; gap:10px; }
  .chatbot-action-button { flex:0 0 auto; width:46px; height:46px; padding:0; border:1px solid rgb(var(--white-rgb) / .16); background:linear-gradient(180deg, rgb(var(--white-rgb) / .08), rgb(var(--white-rgb) / .03)); color:var(--white); box-shadow:0 10px 18px rgb(var(--shadow-rgb) / .16); cursor:pointer; transition:transform .2s ease, background-color .2s ease, border-color .2s ease, box-shadow .2s ease; display:inline-flex; align-items:center; justify-content:center; }
  .chatbot-action-button:hover { transform:translateY(-1px); background:linear-gradient(180deg, rgb(var(--white-rgb) / .14), rgb(var(--white-rgb) / .06)); border-color:rgb(var(--white-rgb) / .34); box-shadow:0 14px 24px rgb(var(--shadow-rgb) / .24); }
  .chatbot-action-button svg { width:20px; height:20px; }
  .chatbot-empty-actions { display:flex; flex-direction:column; gap:10px; }
  .chatbot-loading { color:var(--gray-light); font-size:13px; }
  html[dir="ltr"] .chatbot-float,
  html[dir="ltr"] .chatbot-trigger { right:auto; left:24px; }
  html[dir="ltr"] .chatbot-panel { left:24px; right:auto; }
  @media (max-width:768px) {
    .chatbot-float,
    .chatbot-trigger { right:16px; bottom:16px; width:52px; height:52px; left:auto; }
    html[dir="ltr"] .chatbot-float,
    html[dir="ltr"] .chatbot-trigger { right:auto; left:16px; }
    .chatbot-panel { right:16px; bottom:82px; height:min(72vh, 560px); }
    html[dir="ltr"] .chatbot-panel { left:16px; right:auto; }
  }
</style>

@push('scripts')
<script>
(function () {
  const widget = document.getElementById('chatbotWidget');

  if (!widget) {
    return;
  }

  const trigger = document.getElementById('chatbotTrigger');
  const closeButton = document.getElementById('chatbotClose');
  const panel = document.getElementById('chatbotPanel');
  const messages = document.getElementById('chatbotMessages');
  const composer = document.getElementById('chatbotComposer');

  const endpoints = {
    categories: widget.dataset.categoriesEndpoint,
    categoryProducts: widget.dataset.categoryProductsTemplate,
    categoryFallback: widget.dataset.categoryFallbackTemplate,
    productVariants: widget.dataset.productVariantsTemplate,
    cartItems: widget.dataset.cartItemsEndpoint,
    checkout: widget.dataset.checkoutUrl,
  };

  const translations = {
    greeting: @json(__('storefront.chatbot.greeting')),
    intro: @json(__('storefront.chatbot.intro')),
    loading: @json(__('storefront.chatbot.loading')),
    quantityPrompt: @json(__('storefront.chatbot.prompts.quantity')),
    quantityLabel: @json(__('storefront.chatbot.quantity_label')),
    productsCountLabel: @json(__('storefront.chatbot.products_count_label')),
    stockLabel: @json(__('storefront.chatbot.stock_label')),
    confirmQuantity: @json(__('storefront.chatbot.actions.confirm_quantity')),
    retry: @json(__('storefront.chatbot.actions.retry')),
    goodbye: @json(__('storefront.chatbot.goodbye')),
    genericError: @json(__('storefront.chatbot.errors.generic')),
    checkout: @json(__('storefront.chatbot.actions.checkout')),
    addMore: @json(__('storefront.chatbot.actions.add_more_products')),
    closeChat: @json(__('storefront.chatbot.actions.close_chat')),
    backToProducts: @json(__('storefront.chatbot.actions.back_to_products')),
    loadFallbackProducts: @json(__('storefront.chatbot.actions.load_fallback_products')),
    selectAnotherCategory: @json(__('storefront.chatbot.actions.select_another_category')),
  };

  const state = {
    open: false,
    selectedCategory: null,
    selectedProduct: null,
    selectedVariant: null,
    selectedQuantity: 1,
  };

  function scrollMessages() {
    messages.scrollTop = messages.scrollHeight;
  }

  function pushMessage(kind, text) {
    const node = document.createElement('div');
    node.className = `chatbot-message chatbot-message--${kind}`;
    node.textContent = text;
    messages.appendChild(node);
    scrollMessages();
  }

  function setComposer(node) {
    composer.innerHTML = '';

    if (!node) {
      scrollMessages();
      return;
    }

    composer.appendChild(node);
    requestAnimationFrame(scrollMessages);
  }

  function buildChoiceButton(label, meta, onClick, extraClass = '') {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = `chatbot-choice-button ${extraClass}`.trim();

    const labelNode = document.createElement('span');
    labelNode.textContent = label;
    button.appendChild(labelNode);

    if (meta) {
      const metaNode = document.createElement('span');
      metaNode.className = 'chatbot-choice-button__meta';
      metaNode.textContent = meta;
      button.appendChild(metaNode);
    }

    button.addEventListener('click', onClick);

    return button;
  }

  function renderCategories(categories) {
    const list = document.createElement('div');
    list.className = 'chatbot-choice-scroller';

    categories.forEach((category) => {
      const countLabel = translations.productsCountLabel.replace('__COUNT__', String(category.products_count));
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'chatbot-choice-button chatbot-choice-button--card';

      const card = document.createElement('div');
      card.className = 'chatbot-card';

      const media = document.createElement('div');
      media.className = 'chatbot-card__media';

      if (category.image_url) {
        const image = document.createElement('img');
        image.src = category.image_url;
        image.alt = category.name;
        media.appendChild(image);
      } else {
        media.textContent = 'IMG';
      }

      if (product.label) {
        const label = document.createElement('span');
        label.className = 'badge';
        label.textContent = product.label;
        media.appendChild(label);
      }

      const body = document.createElement('div');
      body.className = 'chatbot-card__body';

      const title = document.createElement('span');
      title.className = 'chatbot-card__title';
      title.textContent = category.name;

      const meta = document.createElement('span');
      meta.className = 'chatbot-choice-button__meta';
      meta.textContent = countLabel;

      body.appendChild(title);
      body.appendChild(meta);
      card.appendChild(media);
      card.appendChild(body);
      button.appendChild(card);
      button.addEventListener('click', () => chooseCategory(category));

      list.appendChild(button);
    });

    setComposer(list);
  }

  function renderProducts(products) {
    const list = document.createElement('div');
    list.className = 'chatbot-choice-scroller';

    products.forEach((product) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'chatbot-choice-button chatbot-choice-button--card';

      const layout = document.createElement('div');
      layout.className = 'chatbot-card';

      const media = document.createElement('div');
      media.className = 'chatbot-card__media';

      if (product.image_url) {
        const image = document.createElement('img');
        image.src = product.image_url;
        image.alt = product.name;
        media.appendChild(image);
      } else {
        media.textContent = 'IMG';
      }

      const body = document.createElement('div');
      body.className = 'chatbot-card__body';

      const title = document.createElement('span');
      title.className = 'chatbot-card__title';
      title.textContent = product.name;

      const meta = document.createElement('span');
      meta.className = 'chatbot-choice-button__meta';
      meta.textContent = product.badge
        ? `${product.price_label} • ${product.badge}`
        : product.price_label;

      body.appendChild(title);
      body.appendChild(meta);
      layout.appendChild(media);
      layout.appendChild(body);
      button.appendChild(layout);
      button.addEventListener('click', () => chooseProduct(product));

      list.appendChild(button);
    });

    setComposer(list);
  }

  function renderVariants(variants) {
    const list = document.createElement('div');
    list.className = 'chatbot-choice-list';

    variants.forEach((variant) => {
      const meta = `${variant.price_label} • ${translations.stockLabel.replace('__COUNT__', String(variant.stock_quantity))}`;

      list.appendChild(buildChoiceButton(
        variant.name,
        meta,
        () => chooseVariant(variant),
      ));
    });

    setComposer(list);
  }

  function renderEmptyActions(actions, retryHandler = null) {
    const list = document.createElement('div');
    list.className = 'chatbot-empty-actions';

    actions.forEach((action) => {
      list.appendChild(buildChoiceButton(action.label, '', () => handleAction(action.action)));
    });

    if (retryHandler) {
      list.appendChild(buildChoiceButton(translations.retry, '', retryHandler));
    }

    setComposer(list);
  }

  function renderQuantityStep() {
    const container = document.createElement('div');
    container.className = 'chatbot-quantity';
    container.innerHTML = `
      <div class="chatbot-quantity__controls">
        <button type="button" class="chatbot-quantity__step" data-qty-step="-1">-</button>
        <div class="chatbot-quantity__value" id="chatbotQuantityValue">${state.selectedQuantity}</div>
        <button type="button" class="chatbot-quantity__step" data-qty-step="1">+</button>
      </div>
      <button type="button" class="btn-primary chatbot-quantity__submit"><span>${translations.confirmQuantity}</span></button>
    `;

    const valueNode = container.querySelector('#chatbotQuantityValue');
    const min = 1;
    const max = Number(state.selectedVariant?.stock_quantity || 1);

    container.querySelectorAll('[data-qty-step]').forEach((button) => {
      button.addEventListener('click', () => {
        const diff = Number(button.dataset.qtyStep || 0);
        state.selectedQuantity = Math.min(max, Math.max(min, state.selectedQuantity + diff));
        valueNode.textContent = String(state.selectedQuantity);
      });
    });

    container.querySelector('.chatbot-quantity__submit')?.addEventListener('click', submitCartItem);

    setComposer(container);
  }

  function renderPostAddActions(actions) {
    const container = document.createElement('div');
    container.className = 'chatbot-actions';

    actions.forEach((action) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'chatbot-action-button';
      button.setAttribute('aria-label', action.label);
      button.title = action.label;
      button.innerHTML = actionIcon(action.action);
      button.addEventListener('click', () => handleAction(action.action));
      container.appendChild(button);
    });

    setComposer(container);
  }

  function actionIcon(action) {
    if (action === 'checkout') {
      return `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M6 7h15l-1.5 8h-12z"/>
          <path d="M6 7 5 4H3"/>
          <circle cx="9" cy="19" r="1.25"/>
          <circle cx="18" cy="19" r="1.25"/>
        </svg>
      `;
    }

    if (action === 'add_more_products') {
      return `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M21 12a9 9 0 1 1-2.64-6.36"/>
          <path d="M21 3v6h-6"/>
        </svg>
      `;
    }

    return `
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M18 6 6 18"/>
        <path d="M6 6l12 12"/>
      </svg>
    `;
  }

  function renderLoading() {
    const loading = document.createElement('div');
    loading.className = 'chatbot-loading';
    loading.textContent = translations.loading;
    setComposer(loading);
  }

  async function fetchJson(url, retryHandler = null) {
    renderLoading();

    try {
      const response = await fetch(url, {
        headers: {
          Accept: 'application/json',
        },
      });

      const data = await response.json().catch(() => ({}));

      if (!response.ok) {
        throw new Error(data.message || translations.genericError);
      }

      return data;
    } catch (error) {
      pushMessage('bot', error.message || translations.genericError);
      renderEmptyActions([], retryHandler);
      return null;
    }
  }

  function openChat() {
    panel.hidden = false;
    panel.classList.add('open');
    panel.setAttribute('aria-hidden', 'false');
    trigger.setAttribute('aria-expanded', 'true');
    state.open = true;

    if (!messages.childElementCount) {
      bootConversation();
    }
  }

  function closeChat() {
    panel.classList.remove('open');
    panel.setAttribute('aria-hidden', 'true');
    trigger.setAttribute('aria-expanded', 'false');
    panel.hidden = true;
    state.open = false;
  }

  function resetFlow() {
    state.selectedCategory = null;
    state.selectedProduct = null;
    state.selectedVariant = null;
    state.selectedQuantity = 1;
  }

  async function bootConversation() {
    resetFlow();
    pushMessage('bot', translations.greeting);
    pushMessage('bot', translations.intro);
    await loadCategories();
  }

  async function loadCategories() {
    const data = await fetchJson(endpoints.categories, loadCategories);

    if (!data) {
      return;
    }

    pushMessage('bot', data.prompt);
    renderCategories(data.categories || []);
  }

  async function chooseCategory(category) {
    state.selectedCategory = category;
    state.selectedProduct = null;
    state.selectedVariant = null;
    state.selectedQuantity = 1;
    pushMessage('user', category.name);

    const url = endpoints.categoryProducts.replace('__CATEGORY__', category.slug);
    const data = await fetchJson(url, () => chooseCategory(category));

    if (!data) {
      return;
    }

    pushMessage('bot', data.prompt);

    if ((data.state || '') === 'empty') {
      renderEmptyActions(data.actions || []);
      return;
    }

    renderProducts(data.products || []);
  }

  async function chooseProduct(product) {
    state.selectedProduct = product;
    state.selectedVariant = null;
    state.selectedQuantity = 1;
    pushMessage('user', product.name);

    const url = endpoints.productVariants.replace('__PRODUCT__', product.slug);
    const data = await fetchJson(url, () => chooseProduct(product));

    if (!data) {
      return;
    }

    pushMessage('bot', data.prompt);

    if ((data.state || '') === 'empty') {
      renderEmptyActions(data.actions || []);
      return;
    }

    renderVariants(data.variants || []);
  }

  function chooseVariant(variant) {
    state.selectedVariant = variant;
    state.selectedQuantity = 1;
    pushMessage('user', variant.name);
    pushMessage('bot', `${translations.quantityPrompt} ${variant.name}`);
    renderQuantityStep();
  }

  async function submitCartItem() {
    if (!state.selectedProduct || !state.selectedVariant) {
      return;
    }

    pushMessage('user', `${translations.quantityLabel} ${state.selectedQuantity}`);
    renderLoading();

    try {
      const response = await fetch(endpoints.cartItems, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': @json(csrf_token()),
        },
        body: JSON.stringify({
          product_id: state.selectedProduct.id,
          product_variant_id: state.selectedVariant.id,
          quantity: state.selectedQuantity,
        }),
      });

      const data = await response.json().catch(() => ({}));

      if (!response.ok) {
        const errorMessage = data.message || Object.values(data.errors || {}).flat()[0] || translations.genericError;
        throw new Error(errorMessage);
      }

      if (typeof refreshNavbarCartSummary === 'function') {
        refreshNavbarCartSummary();
      }

      pushMessage('bot', data.message || translations.genericError);
      pushMessage('bot', translations.goodbye);
      renderPostAddActions(data.actions || []);
    } catch (error) {
      pushMessage('bot', error.message || translations.genericError);
      renderEmptyActions([{ action: 'back_to_products', label: translations.backToProducts }], submitCartItem);
    }
  }

  async function handleAction(action) {
    if (action === 'load_fallback_products' && state.selectedCategory) {
      pushMessage('user', translations.loadFallbackProducts);
      const data = await fetchJson(
        endpoints.categoryFallback.replace('__CATEGORY__', state.selectedCategory.slug),
        () => handleAction(action)
      );

      if (!data) {
        return;
      }

      pushMessage('bot', data.prompt);

      if ((data.state || '') === 'empty') {
        renderEmptyActions(data.actions || []);
        return;
      }

      renderProducts(data.products || []);
      return;
    }

    if (action === 'select_another_category') {
      pushMessage('user', translations.selectAnotherCategory);
      await loadCategories();
      return;
    }

    if (action === 'back_to_products') {
      pushMessage('user', translations.backToProducts);

      if (state.selectedCategory) {
        await chooseCategory(state.selectedCategory);
      } else {
        await loadCategories();
      }

      return;
    }

    if (action === 'checkout') {
      pushMessage('user', translations.checkout);
      window.location.href = endpoints.checkout;
      return;
    }

    if (action === 'add_more_products') {
      pushMessage('user', translations.addMore);
      await loadCategories();
      return;
    }

    if (action === 'close_chat') {
      pushMessage('user', translations.closeChat);
      closeChat();
    }
  }

  trigger?.addEventListener('click', () => {
    if (state.open) {
      closeChat();
    } else {
      openChat();
    }
  });

  closeButton?.addEventListener('click', closeChat);
})();
</script>
@endpush
