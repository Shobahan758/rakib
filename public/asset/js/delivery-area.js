(() => {
  const address = document.getElementById('address');
  const form = document.getElementById('orderForm');
  if (!address || !form) return;

  const updateDeliveryArea = () => {
    const value = address.value.trim();
    if (!value) return;
    const area = /ঢাকা|\b(?:dhaka|dacca)\b/i.test(value) ? 'inside_dhaka' : 'outside_dhaka';
    const option = form.querySelector(`input[name="delivery_area"][value="${area}"]`);
    if (!option || option.checked) return;
    option.checked = true;
    // Reuse the checkout's selected styling and delivery-total calculation.
    option.dispatchEvent(new Event('change', { bubbles: true }));
  };

  address.addEventListener('input', updateDeliveryArea);
  address.addEventListener('change', updateDeliveryArea);
  window.addEventListener('pageshow', updateDeliveryArea);
  updateDeliveryArea();
})();
