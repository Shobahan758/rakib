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
  window.storeTracking?.productView();
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
const successModal = document.getElementById('orderSuccessModal');
let successReturnFocus;
const showOrderSuccess = (purchasedProductId) => {
  if (!successModal) return;
  let availableCount = 0;
  successModal.querySelectorAll('[data-recommendation-id]').forEach(card => {
    card.hidden = card.dataset.recommendationId === String(purchasedProductId);
    if (!card.hidden) availableCount++;
  });
  document.getElementById('successProductsEmpty').hidden = availableCount > 0;
  successReturnFocus = document.activeElement;
  successModal.showModal();
  successModal.scrollTop = 0;
};
successModal?.querySelectorAll('[data-close-success]').forEach(button => {
  button.addEventListener('click', () => successModal.close());
});
successModal?.addEventListener('close', () => successReturnFocus?.focus({ preventScroll: true }));
successModal?.querySelectorAll('.modal-quantity-control').forEach(control => {
  const quantity = control.querySelector('.modal-product-quantity');
  const clampQuantity = value => Math.min(9999, Math.max(1, Number.parseInt(value, 10) || 1));
  control.querySelector('.modal-qty-minus')?.addEventListener('click', () => { quantity.value = clampQuantity(Number(quantity.value) - 1); });
  control.querySelector('.modal-qty-plus')?.addEventListener('click', () => { quantity.value = clampQuantity(Number(quantity.value) + 1); });
  quantity?.addEventListener('change', () => { quantity.value = clampQuantity(quantity.value); });
});
successModal?.querySelectorAll('[data-buy-product]').forEach(button => {
  button.dataset.originalLabel = button.innerHTML;
  button.addEventListener('click', async () => {
    if (button.disabled) return;
    const status = document.getElementById('modalOrderStatus');
    const quantity = button.closest('[data-recommendation-id]').querySelector('.modal-product-quantity');
    if (!quantity.reportValidity()) return;
    status.hidden = false;
    status.className = 'alert alert-info';
    if (!successModal.dataset.addonUrl) {
      status.textContent = 'আগে মূল অর্ডারটি সম্পন্ন করুন।';
      return;
    }
    const label = button.innerHTML;
    button.disabled = true;
    button.textContent = successModal.dataset.addingLabel || 'যোগ করা হচ্ছে...';
    status.textContent = successModal.dataset.addingMessage || 'আপনার অর্ডারের সঙ্গে পণ্যটি যুক্ত করা হচ্ছে...';
    try {
      const response = await fetch(successModal.dataset.addonUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ product_id: button.dataset.buyProduct, quantity: Number(quantity.value) }),
      });
      const result = await response.json().catch(() => ({}));
      if (response.status === 419) throw new Error('সেশন শেষ হয়েছে। পেজ রিফ্রেশ করে আবার চেষ্টা করুন।');
      if (response.status === 403) throw new Error('পণ্য যোগ করার সময়সীমা শেষ হয়েছে। মূল অর্ডারটি আবার দেবেন না; আমাদের সাথে যোগাযোগ করুন।');
      if (response.status === 429) throw new Error('অনেকবার চেষ্টা হয়েছে। এক মিনিট পরে আবার চেষ্টা করুন।');
      if (response.status >= 500) throw new Error('সার্ভারে সমস্যা হয়েছে। মূল অর্ডারটি আবার দেবেন না; আমাদের সাথে যোগাযোগ করুন।');
      if (response.status !== 201 || !result.order_id) throw new Error(result.message || 'পণ্য যোগ করা যায়নি। আবার চেষ্টা করুন।');
      button.textContent = successModal.dataset.addedLabel || '✓ অর্ডারে যুক্ত হয়েছে';
      quantity.disabled = true;
      status.className = 'modal-order-confirmation';
      status.textContent = `${successModal.dataset.confirmTitle || '🎉 ধন্যবাদ! আপনার অর্ডারটি সফলভাবে সম্পন্ন হয়েছে'}\n${successModal.dataset.confirmDescription || 'খুব শীঘ্রই আমাদের টিম আপনার সাথে যোগাযোগ করবে ইনশাআল্লাহ।'}\n\n${result.message}\n${successModal.dataset.totalLabel || 'মোট:'} ${currencySymbol}${Number(result.delivery_total).toLocaleString('en-US')}`;
      if (result.redirect_url && typeof window !== 'undefined') window.location.assign(result.redirect_url);
    } catch (error) {
      status.className = 'alert alert-danger';
      status.textContent = error.message;
      button.disabled = false;
      button.innerHTML = label;
    }
  });
});
if (successModal?.dataset.openOnLoad === 'true') showOrderSuccess();
form?.addEventListener('submit', async (event) => {
  event.preventDefault();
  if (form.querySelector('[type="submit"]').disabled) return;

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
  window.incompleteCheckout?.pause();
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

    if (response.status !== 201 || !Number.isSafeInteger(result.order_id) || result.order_id < 1) {
      throw new Error('অর্ডার নিশ্চিত করা যায়নি। অনুগ্রহ করে আমাদের সাথে যোগাযোগ করুন।');
    }

    status.textContent = form.dataset.successMessage || result.message || 'ধন্যবাদ! আপনার অর্ডারটি সফলভাবে গ্রহণ করা হয়েছে।';
    status.className = 'alert alert-success mt-3 mb-0 p-3 text-center fw-bold fs-6';
    form.classList.remove('was-validated');
    window.storeTracking?.purchase(result.tracking);
    window.incompleteCheckout?.complete();
    if (successModal) {
      successModal.dataset.addonUrl = result.addon_url || '';
      successModal.querySelectorAll('[data-buy-product]').forEach(button => {
        button.disabled = false;
        button.innerHTML = button.dataset.originalLabel || button.innerHTML;
      });
      successModal.querySelectorAll('.modal-product-quantity').forEach(input => { input.disabled = false; input.value = '1'; });
      document.getElementById('modalOrderStatus').hidden = true;
    }
    showOrderSuccess(productIdInput.value);
  } catch (error) {
    status.textContent = error.message || form.dataset.errorMessage || 'অর্ডার পাঠানো যায়নি। আবার চেষ্টা করুন।';
    status.className = 'alert alert-danger mt-3 mb-0 p-3 text-center fw-bold';
    status.scrollIntoView({ behavior: 'smooth', block: 'center' });
  } finally {
    window.incompleteCheckout?.resume();
    button.disabled = false;
    button.innerHTML = originalLabel;
  }
});
