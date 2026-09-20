document.addEventListener('DOMContentLoaded', () => {
  const bn = (value) => String(value).replace(/\d/g, d => '০১২৩৪৫৬৭৮৯'[d]);
  const header = document.querySelector('.site-header');
  const progress = document.getElementById('scrollProgress');

  const updateScroll = () => {
    const top = window.scrollY;
    const height = document.documentElement.scrollHeight - window.innerHeight;
    progress.style.width = `${height ? (top / height) * 100 : 0}%`;
    if (header) header.classList.toggle('scrolled', top > 20);
  };
  updateScroll();
  window.addEventListener('scroll', updateScroll, { passive: true });

  if (window.AOS) AOS.init({ duration: 650, once: true, offset: 70, easing: 'ease-out-cubic', disable: window.matchMedia('(prefers-reduced-motion: reduce)').matches });

  document.querySelectorAll('a[href^="#"]').forEach(link => link.addEventListener('click', (event) => {
    const id = link.getAttribute('href');
    if (id === '#') return;
    const target = document.querySelector(id);
    if (!target) return;
    event.preventDefault();
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    const nav = document.querySelector('.navbar-collapse.show');
    if (nav && window.bootstrap) bootstrap.Collapse.getOrCreateInstance(nav).hide();
  }));

  const counters = document.querySelectorAll('.counter');
  const counterObserver = new IntersectionObserver(entries => entries.forEach(entry => {
    if (!entry.isIntersecting || entry.target.dataset.done) return;
    entry.target.dataset.done = 'true';
    const target = Number(entry.target.dataset.target);
    const start = performance.now();
    const tick = now => {
      const p = Math.min((now - start) / 1100, 1);
      entry.target.textContent = bn(Math.floor(target * (1 - Math.pow(1 - p, 3))));
      if (p < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  }), { threshold: .5 });
  counters.forEach(c => counterObserver.observe(c));

  const reviewSlider = document.getElementById('reviewSlider');
  const reviewTrack = document.getElementById('reviewTrack');
  const reviewPrev = document.getElementById('reviewPrev');
  const reviewNext = document.getElementById('reviewNext');
  if (reviewSlider && reviewTrack && reviewTrack.children.length > 1) {
    const realReviewCount = reviewTrack.children.length;
    const firstClone = reviewTrack.firstElementChild.cloneNode(true);
    const lastClone = reviewTrack.lastElementChild.cloneNode(true);
    firstClone.setAttribute('aria-hidden', 'true');
    lastClone.setAttribute('aria-hidden', 'true');
    firstClone.removeAttribute('data-aos');
    lastClone.removeAttribute('data-aos');
    reviewTrack.appendChild(firstClone);
    reviewTrack.insertBefore(lastClone, reviewTrack.firstElementChild);

    let reviewIndex = 1;
    let reviewTimer = null;
    let isSliding = false;
    const slideWidth = () => reviewTrack.firstElementChild.getBoundingClientRect().width + (parseFloat(getComputedStyle(reviewTrack).gap) || 0);
    const moveReviews = (animate = true) => {
      reviewTrack.style.transition = animate ? 'transform .65s ease' : 'none';
      reviewTrack.style.transform = `translate3d(-${reviewIndex * slideWidth()}px, 0, 0)`;
    };
    const slideReviews = (direction = 1) => {
      if (isSliding || document.hidden) return;
      isSliding = true;
      reviewIndex += direction;
      moveReviews();
    };
    const stopReviews = () => {
      if (reviewTimer) clearInterval(reviewTimer);
      reviewTimer = null;
    };
    const startReviews = () => {
      stopReviews();
      reviewTimer = setInterval(() => slideReviews(1), 3000);
    };

    reviewTrack.addEventListener('transitionend', event => {
      if (event.propertyName !== 'transform') return;
      if (reviewIndex === realReviewCount + 1) {
        reviewIndex = 1;
        moveReviews(false);
      } else if (reviewIndex === 0) {
        reviewIndex = realReviewCount;
        moveReviews(false);
      }
      isSliding = false;
    });
    const navigateReviews = direction => {
      stopReviews();
      slideReviews(direction);
      startReviews();
    };
    reviewPrev?.addEventListener('click', () => navigateReviews(-1));
    reviewNext?.addEventListener('click', () => navigateReviews(1));
    reviewSlider.addEventListener('mouseenter', stopReviews);
    reviewSlider.addEventListener('mouseleave', startReviews);
    reviewSlider.addEventListener('touchstart', stopReviews, { passive: true });
    reviewSlider.addEventListener('touchend', startReviews, { passive: true });
    window.addEventListener('resize', () => moveReviews(false));
    document.addEventListener('visibilitychange', () => document.hidden ? stopReviews() : startReviews());
    moveReviews(false);
    startReviews();
  } else {
    reviewPrev?.setAttribute('hidden', '');
    reviewNext?.setAttribute('hidden', '');
  }

  const lightbox = document.getElementById('lightbox');
  const lightboxImage = lightbox.querySelector('img');
  const closeLightbox = () => { lightbox.hidden = true; document.body.style.overflow = ''; };
  document.querySelectorAll('.gallery-item').forEach(item => item.addEventListener('click', () => {
    lightboxImage.src = item.dataset.full;
    lightbox.hidden = false;
    document.body.style.overflow = 'hidden';
    lightbox.querySelector('button').focus();
  }));
  lightbox.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
  lightbox.addEventListener('click', e => { if (e.target === lightbox) closeLightbox(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && !lightbox.hidden) closeLightbox(); });

  const countdown = document.getElementById('countdown');
  let remaining = Number(sessionStorage.getItem('gavitralTimer')) || Number(countdown?.dataset.duration) || (7 * 3600 + 49 * 60 + 32);
  const timerEls = [document.getElementById('hours'), document.getElementById('minutes'), document.getElementById('seconds')];
  const renderTimer = () => {
    if (remaining <= 0) remaining = 8 * 3600;
    const values = [Math.floor(remaining / 3600), Math.floor((remaining % 3600) / 60), remaining % 60];
    timerEls.forEach((el, i) => el.textContent = bn(String(values[i]).padStart(2, '0')));
    sessionStorage.setItem('gavitralTimer', String(remaining));
    remaining--;
  };
  renderTimer(); setInterval(renderTimer, 1000);

  const quantity = document.getElementById('quantity');
  const total = document.getElementById('orderTotal');
  const unitPrice = Number(document.getElementById('orderForm')?.dataset.unitPrice) || 890;
  const quantityPrice = document.getElementById('quantityPrice');
  const quantityMinus = document.getElementById('quantityMinus');
  const quantityPlus = document.getElementById('quantityPlus');
  const updateQuantity = () => {
    const value = Number(quantity.value);
    const formatted = `৳${bn((value * unitPrice).toLocaleString('en-US'))}`;
    total.textContent = formatted;
    quantityPrice.textContent = formatted;
    quantityMinus.disabled = value <= Number(quantity.min);
    quantityPlus.disabled = value >= Number(quantity.max);
  };
  quantityMinus.addEventListener('click', () => { quantity.value = Math.max(Number(quantity.min), Number(quantity.value) - 1); updateQuantity(); });
  quantityPlus.addEventListener('click', () => { quantity.value = Math.min(Number(quantity.max), Number(quantity.value) + 1); updateQuantity(); });
  updateQuantity();

  const form = document.getElementById('orderForm');
  const orderToast = document.getElementById('orderToast');
  const toastTitle = document.getElementById('toastTitle');
  const toastMessage = document.getElementById('toastMessage');
  const toastIcon = document.getElementById('toastIcon');
  const submitButton = form.querySelector('button[type="submit"]');
  const submitButtonHtml = submitButton.innerHTML;
  const incompleteTokenInput = document.getElementById('incompleteToken');
  const incompleteFields = ['name', 'phone', 'email', 'address', 'quantity'];
  const createTrackingToken = () => window.crypto?.randomUUID?.() || 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, char => {
    const random = Math.floor(Math.random() * 16);
    return (char === 'x' ? random : (random & 3) | 8).toString(16);
  });
  let incompleteToken = sessionStorage.getItem('gavitralIncompleteToken') || createTrackingToken();
  let incompleteSaveTimer = null;
  sessionStorage.setItem('gavitralIncompleteToken', incompleteToken);
  incompleteTokenInput.value = incompleteToken;
  const normalizePhone = value => {
    const banglaDigits = '০১২৩৪৫৬৭৮৯';
    let phone = value.replace(/[০-৯]/g, digit => String(banglaDigits.indexOf(digit))).replace(/\D/g, '');
    if (phone.startsWith('88') && phone.length === 13) phone = phone.slice(2);
    return phone;
  };

  const saveIncompleteOrder = async () => {
    const name = form.elements.name;
    const phone = form.elements.phone;
    const email = form.elements.email;
    const normalizedPhone = normalizePhone(phone.value);
    if (name.value.trim().length < 2 || !/^01[3-9][0-9]{8}$/.test(normalizedPhone)) return;

    const data = new FormData();
    data.append('_token', form.elements._token.value);
    data.append('token', incompleteToken);
    incompleteFields.forEach(field => data.append(field, form.elements[field]?.value || ''));
    data.set('phone', normalizedPhone);
    if (email.value.trim() && !email.checkValidity()) data.set('email', '');

    try {
      await fetch(form.dataset.incompleteAction, {
        method: 'POST',
        body: data,
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        keepalive: true,
      });
    } catch (_) {
      // The next field change will retry without interrupting the customer.
    }
  };
  const scheduleIncompleteSave = () => {
    clearTimeout(incompleteSaveTimer);
    incompleteSaveTimer = setTimeout(saveIncompleteOrder, 900);
  };
  incompleteFields.forEach(field => form.elements[field]?.addEventListener('input', scheduleIncompleteSave));
  incompleteFields.forEach(field => form.elements[field]?.addEventListener('change', scheduleIncompleteSave));
  ['name', 'phone'].forEach(field => form.elements[field]?.addEventListener('blur', () => {
    clearTimeout(incompleteSaveTimer);
    saveIncompleteOrder();
  }));
  window.addEventListener('pagehide', () => {
    clearTimeout(incompleteSaveTimer);
    saveIncompleteOrder();
  });

  const showOrderToast = (title, message, success = true) => {
    toastTitle.textContent = title;
    toastMessage.textContent = message;
    toastIcon.className = success ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation';
    toastIcon.style.color = success ? 'var(--primary)' : '#dc3545';
    if (window.bootstrap) bootstrap.Toast.getOrCreateInstance(orderToast, { delay: 6000 }).show();
  };

  form.addEventListener('submit', async event => {
    event.preventDefault();
    event.stopPropagation();
    form.classList.add('was-validated');
    if (!form.checkValidity()) return;

    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> অর্ডার পাঠানো হচ্ছে...';

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      });
      const result = await response.json().catch(() => ({}));

      if (!response.ok) {
        const validationMessage = result.errors ? Object.values(result.errors).flat()[0] : null;
        throw new Error(validationMessage || result.message || 'অর্ডারটি পাঠানো যায়নি। আবার চেষ্টা করুন।');
      }

      showOrderToast(result.message, 'অর্ডার নিশ্চিত করতে শিগগিরই আপনাকে ফোন করা হবে।');
      clearTimeout(incompleteSaveTimer);
      sessionStorage.removeItem('gavitralIncompleteToken');
      form.reset();
      form.classList.remove('was-validated');
      quantity.value = quantity.min;
      updateQuantity();
    } catch (error) {
      showOrderToast('অর্ডার সম্পন্ন হয়নি', error.message, false);
    } finally {
      submitButton.disabled = false;
      submitButton.innerHTML = submitButtonHtml;
    }
  });

  document.querySelectorAll('[data-policy]').forEach(link => link.addEventListener('click', event => {
    event.preventDefault();
    const names = { privacy: 'গোপনীয়তা নীতি', terms: 'ব্যবহারের শর্তাবলি', refund: 'রিফান্ড নীতি' };
    alert(`${names[link.dataset.policy]}\n\nবিস্তারিত নীতিমালা জানতে support@gavitral.com ঠিকানায় যোগাযোগ করুন। অর্ডার প্রক্রিয়ার জন্য দেওয়া তথ্য গোপন রাখা হয় এবং অনুমতি ছাড়া তৃতীয় পক্ষের বিপণনে ব্যবহার করা হয় না।`);
  }));

  const year = document.getElementById('year');
  if (year) year.textContent = bn(new Date().getFullYear());
});
