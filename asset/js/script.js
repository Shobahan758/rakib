const trackingEndpoint = document.querySelector('meta[name="tracking-endpoint"]')?.content;
const trackingToken = document.querySelector('meta[name="csrf-token"]')?.content;
const recordedTrackingEvents = new Set();

const recordTrackingEvent = (eventName, once = false) => {
  if (!trackingEndpoint || (once && recordedTrackingEvents.has(eventName))) return;
  if (once) recordedTrackingEvents.add(eventName);
  fetch(trackingEndpoint, {
    method: 'POST',
        credentials: 'same-origin',
    keepalive: true,
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': trackingToken },
    body: JSON.stringify({ event: eventName, path: window.location.pathname })
  }).catch(() => {});
};

recordTrackingEvent('page_view');
document.querySelectorAll('a[href="#Order"], #orderForm [type="submit"]').forEach(button => button.addEventListener('click', () => recordTrackingEvent('order_button_click')));
document.querySelectorAll('a.whatsapp').forEach(button => button.addEventListener('click', () => recordTrackingEvent('whatsapp_click')));
document.getElementById('orderForm')?.addEventListener('input', () => recordTrackingEvent('form_start', true), { once: true });

const checkoutSection = document.getElementById('Order');
if (checkoutSection) {
  const checkoutObserver = new IntersectionObserver(entries => {
    if (entries.some(entry => entry.isIntersecting)) {
      recordTrackingEvent('checkout_view', true);
      checkoutObserver.disconnect();
    }
  }, { threshold: .25 });
  checkoutObserver.observe(checkoutSection);
}

// স্ক্রলে নরম রিভিল অ্যানিমেশন
const observer = new IntersectionObserver((entries) => entries.forEach((entry) => { if (entry.isIntersecting) { entry.target.classList.add('show'); observer.unobserve(entry.target); } }), { threshold: .12 });
document.querySelectorAll('.reveal').forEach((item) => observer.observe(item));

// Product selection and quantity
const quantityInput = document.getElementById('qty');
const productIdInput = document.getElementById('productId');
const deliveryAreaInputs = document.querySelectorAll('input[name="delivery_area"]');
const productOptions = document.querySelectorAll('.product-option');
const selectedProductSummary = document.querySelector('.selected-product-summary');
const currencySymbol = selectedProductSummary?.dataset.currency || '৳';
const quantityPrefix = selectedProductSummary?.dataset.quantityPrefix || 'পরিমাণ';
const toBanglaNumber = value => String(value).replace(/\d/g, digit => '০১২৩৪৫৬৭৮৯'[Number(digit)]);
const updateOrderTotal = () => {
  const selectedProduct = document.querySelector('.product-radio:checked');
  const productTotal = Number(selectedProduct?.dataset.price || 0) * Number(quantityInput?.value || 1);
  const deliveryCharge = Number(document.querySelector('input[name="delivery_area"]:checked')?.dataset.charge || 0);
  const totalOutput = document.getElementById('selectedOrderTotal');
  if (totalOutput) totalOutput.textContent = `${currencySymbol}${(productTotal + deliveryCharge).toLocaleString('en-US')}`;
};
const updateSelectedProduct = (option) => {
  const radio = option.querySelector('.product-radio');
  const quantity = option.querySelector('.product-qty');
  radio.checked = true;
  productOptions.forEach(item => item.classList.toggle('selected', item === option));
  productIdInput.value = radio.value;
  quantityInput.value = Math.max(1, Number.parseInt(quantity.value, 10) || 1);
  const summaryImage = document.getElementById('selectedProductImage');
  const summaryName = document.getElementById('selectedProductName');
  const summaryQuantity = document.getElementById('selectedProductQuantity');
  const summaryPrice = document.getElementById('selectedProductPrice');
  if (summaryImage) { summaryImage.src = radio.dataset.image; summaryImage.alt = radio.dataset.name; }
  const infoImage = document.getElementById('orderInfoImage');
  if (infoImage && infoImage.dataset.customImage !== '1' && radio.dataset.image) {
    infoImage.classList.add('swapping');
    setTimeout(() => {
      infoImage.src = radio.dataset.image;
      infoImage.alt = radio.dataset.name;
      infoImage.classList.remove('swapping');
    }, 140);
  }
  if (summaryName) summaryName.textContent = radio.dataset.name;
  if (summaryQuantity) summaryQuantity.textContent = `${quantityPrefix}: ${toBanglaNumber(quantityInput.value)}`;
  const selectedProductTotal = Number(radio.dataset.price || 0) * Number(quantityInput.value || 1);
  if (summaryPrice) summaryPrice.textContent = `${currencySymbol}${selectedProductTotal.toLocaleString('en-US')}`;
  updateOrderTotal();
};
const scrollToOrderForm = () => {
  const orderForm = document.getElementById('orderForm');
  if (!orderForm) return;
  const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
  orderForm.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
};
productOptions.forEach(option => {
  const quantity = option.querySelector('.product-qty');
  const radio = option.querySelector('.product-radio');
  radio.addEventListener('change', () => updateSelectedProduct(option));
  radio.addEventListener('click', () => {
    // Let the label finish selecting/focusing the radio before moving the viewport.
    window.setTimeout(scrollToOrderForm, 80);
  });
  option.querySelector('.product-qty-minus').addEventListener('click', () => {
    quantity.value = Math.max(1, Number.parseInt(quantity.value, 10) - 1 || 1);
    updateSelectedProduct(option);
  });
  option.querySelector('.product-qty-plus').addEventListener('click', () => {
    quantity.value = Math.min(9999, Math.max(1, Number.parseInt(quantity.value, 10) + 1 || 1));
    updateSelectedProduct(option);
  });
  quantity.addEventListener('change', () => {
    quantity.value = Math.min(9999, Math.max(1, Number.parseInt(quantity.value, 10) || 1));
    updateSelectedProduct(option);
  });
});
deliveryAreaInputs.forEach(input => input.addEventListener('change', () => {
  document.querySelectorAll('.delivery-option').forEach(option => option.classList.toggle('selected', option.contains(input)));
  updateOrderTotal();
}));

// Menu card order button connection
document.querySelectorAll('.menu-card .btn-order').forEach((btn, idx) => {
  btn.addEventListener('click', () => {
    if (productOptions[idx]) {
      updateSelectedProduct(productOptions[idx]);
    }
  });
});

// ফোন নম্বর নরমালাইজার (বাংলা ডিজিট ও +88 হ্যান্ডলিং)
const normalizePhone = (val) => {
  const bangla = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
  let p = String(val || '').trim();
  bangla.forEach((b, i) => { p = p.replaceAll(b, i); });
  p = p.replace(/[^0-9]/g, '');
  if (p.startsWith('880')) p = p.slice(2);
  else if (p.startsWith('88') && p.length === 13) p = p.slice(2);
  else if (p.length === 10 && p.startsWith('1')) p = '0' + p;
  return p;
};

const phoneInput = document.getElementById('phone');
phoneInput?.addEventListener('input', () => {
  const clean = normalizePhone(phoneInput.value);
  if (clean && clean !== phoneInput.value && /^[0-9]+$/.test(clean)) {
    phoneInput.value = clean;
  }
});
phoneInput?.addEventListener('blur', () => {
  if (phoneInput.value) {
    phoneInput.value = normalizePhone(phoneInput.value);
  }
});

// অর্ডার ফরম যাচাই ও Laravel backend-এ জমা
const form = document.getElementById('orderForm');
form?.addEventListener('submit', async (event) => {
  event.preventDefault();

  if (phoneInput) {
    phoneInput.value = normalizePhone(phoneInput.value);
  }

  if (!form.checkValidity()) {
    event.stopPropagation();
    form.classList.add('was-validated');
    const firstInvalid = form.querySelector(':invalid');
    if (firstInvalid) {
      firstInvalid.focus();
      firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return;
  }

  const button = form.querySelector('[type="submit"]');
  const status = document.getElementById('formStatus');
  const originalLabel = button.innerHTML;
  button.disabled = true;
  button.textContent = form.dataset.sendingMessage || 'আপনার অর্ডার পাঠানো হচ্ছে...';
  status.classList.add('d-none');

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || form.querySelector('input[name="_token"]')?.value;
    const response = await fetch(form.action, {
      method: 'POST',
        credentials: 'same-origin',
      body: new FormData(form),
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken || ''
      }
    });

    let result = {};
    try {
      result = await response.json();
    } catch (_) {}

    if (response.status === 419) throw new Error('সেশন শেষ হয়েছে। পেজ রিফ্রেশ করে আবার অর্ডার দিন।');
    if (response.status === 429) throw new Error('অনেকবার চেষ্টা হয়েছে। এক মিনিট পরে আবার চেষ্টা করুন।');
    if (response.status >= 500) throw new Error('সার্ভারে সমস্যা হওয়ায় অর্ডার নিশ্চিত করা যায়নি। আমাদের সাথে যোগাযোগ করুন।');
    if (!response.ok) {
      let errMsg = result.message || form.dataset.errorMessage || 'অর্ডার পাঠানো যায়নি। আবার চেষ্টা করুন।';
      if (result.errors) {
        const errorList = Object.values(result.errors).flat();
        if (errorList.length) errMsg = errorList[0];
      }
      throw new Error(errMsg);
    }

    status.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i> ${form.dataset.successMessage || result.message || 'ধন্যবাদ! আপনার অর্ডারটি সফলভাবে গ্রহণ করা হয়েছে।'}`;
    status.className = 'alert alert-success mt-3 mb-0 p-3 text-center fw-bold fs-6';
    status.scrollIntoView({ behavior: 'smooth', block: 'center' });
    form.classList.remove('was-validated');

    const nameField = document.getElementById('name');
    const phoneField = document.getElementById('phone');
    const addressField = document.getElementById('address');
    if (nameField) nameField.value = '';
    if (phoneField) phoneField.value = '';
    if (addressField) addressField.value = '';
  } catch (error) {
    status.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i> ${error.message || form.dataset.errorMessage || 'অর্ডার পাঠানো যায়নি। আবার চেষ্টা করুন।'}`;
    status.className = 'alert alert-danger mt-3 mb-0 p-3 text-center fw-bold';
    status.scrollIntoView({ behavior: 'smooth', block: 'center' });
  } finally {
    button.disabled = false;
    button.innerHTML = originalLabel;
  }
});
