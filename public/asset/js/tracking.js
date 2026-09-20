(() => {
  let config = {};
  try { config = JSON.parse(document.getElementById('trackingConfig')?.textContent || '{}'); } catch (_) {}
  const sentPurchases = new Set();
  let checkoutStarted = false;
  const safely = fn => { try { fn(); } catch (_) {} };
  const ecommerce = (event, data) => {
    if (config.google_analytics_id && typeof window.gtag === 'function') {
      safely(() => window.gtag('event', event, { ...data, send_to: config.google_analytics_id }));
    }
    if (config.google_tag_manager_id) {
      window.dataLayer = window.dataLayer || [];
      safely(() => { window.dataLayer.push({ ecommerce: null }); window.dataLayer.push({ event: 'store_' + event, ecommerce: data }); });
    }
    const names = { view_item: 'ViewContent', begin_checkout: 'InitiateCheckout', purchase: 'Purchase' };
    const contents = data.items.map(item => ({ id: item.item_id, quantity: item.quantity, item_price: item.price }));
    if (config.meta_pixel_id && typeof window.fbq === 'function') {
      safely(() => window.fbq('track', names[event], {
        currency: data.currency, value: data.value, content_type: 'product',
        content_ids: data.items.map(item => item.item_id), contents,
      }, data.transaction_id ? { eventID: 'order-' + data.transaction_id } : {}));
    }
    if (config.tiktok_pixel_id && typeof window.ttq?.track === 'function') {
      safely(() => window.ttq.track(names[event], {
        currency: data.currency, value: data.value, content_type: 'product',
        contents: data.items.map(item => ({ content_id: item.item_id, content_name: item.item_name, price: item.price, quantity: item.quantity })),
      }, data.transaction_id ? { event_id: 'order-' + data.transaction_id } : {}));
    }
  };
  const selectedProduct = () => {
    const product = document.querySelector('.product-radio:checked');
    if (!product) return null;
    const quantity = Number(document.getElementById('qty')?.value) || 1;
    const price = Number(product.dataset.price) || 0;
    return { currency: 'BDT', value: price * quantity, items: [{ item_id: product.value, item_name: product.dataset.name, price, quantity }] };
  };
  window.storeTracking = {
    activity(event) {
      if (event === 'page_view') return; // Provider bootstrap already records page views.
      const data = selectedProduct();
      if ((event === 'form_start' || event === 'order_button_click') && data) {
        if (!checkoutStarted) {
          checkoutStarted = true;
          ecommerce('begin_checkout', data);
        }
      }
      else if (config.google_analytics_id && typeof window.gtag === 'function') safely(() => window.gtag('event', event, { send_to: config.google_analytics_id }));
    },
    productView() { const data = selectedProduct(); if (data) ecommerce('view_item', data); },
    purchase(data) {
      if (!data?.transaction_id || !Array.isArray(data.items) || !data.items.length || !Number.isFinite(data.value)) return;
      const key = 'tracked-order-' + data.transaction_id;
      if (sentPurchases.has(key)) return;
      try { if (sessionStorage.getItem(key)) return; } catch (_) {}
      ecommerce('purchase', data);
      if (config.google_ads_id && config.google_ads_conversion_label && typeof window.gtag === 'function') {
        safely(() => window.gtag('event', 'conversion', {
          send_to: config.google_ads_id + '/' + config.google_ads_conversion_label,
          transaction_id: data.transaction_id, currency: data.currency, value: data.value,
        }));
      }
      sentPurchases.add(key);
      try { sessionStorage.setItem(key, '1'); } catch (_) {}
    },
  };
  window.storeTracking.productView();
})();
