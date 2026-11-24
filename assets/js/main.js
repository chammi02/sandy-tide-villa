/* Sandy Tide Villa - client-side behaviour:
   mobile menu, date restrictions, form validation, live price calculation, AJAX availability check. */
document.addEventListener('DOMContentLoaded', () => {

  /* ---------- Mobile navigation ---------- */
  const toggle = document.getElementById('navToggle');
  const nav = document.getElementById('mainNav');
  if (toggle) toggle.addEventListener('click', () => {
    const open = nav.classList.toggle('open');
    toggle.setAttribute('aria-expanded', open);
  });

  /* ---------- Helpers ---------- */
  const iso = d => new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
  const addDays = (s, n) => { const d = new Date(s + 'T00:00:00'); d.setDate(d.getDate() + n); return iso(d); };
  const today = iso(new Date());
  const nightsBetween = (a, b) => Math.round((new Date(b + 'T00:00:00') - new Date(a + 'T00:00:00')) / 86400000);
  const fmt = n => 'LKR ' + Number(n).toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  /** Show messages with DOM manipulation. */
  function showMessages(form, list, type = 'error') {
    const box = form.querySelector('.js-messages');
    if (!box) return;
    box.innerHTML = '';
    list.forEach(text => {
      const div = document.createElement('div');
      div.className = 'alert alert-' + type;
      div.textContent = text;            // textContent => no HTML injection
      box.appendChild(div);
    });
  }
  const markInvalid = (el, bad) => el && el.classList.toggle('invalid', bad);

  /* ---------- Date pickers: block past dates and invalid ranges ---------- */
  document.querySelectorAll('input[name="check_in"]').forEach(inEl => {
    const form = inEl.closest('form');
    const outEl = form.querySelector('input[name="check_out"]');
    inEl.min = today;
    const sync = () => {
      outEl.min = inEl.value ? addDays(inEl.value, 1) : addDays(today, 1);
      if (outEl.value && inEl.value && outEl.value <= inEl.value) outEl.value = '';
    };
    inEl.addEventListener('change', sync); sync();
  });

  /* ---------- Search forms: validate before submit ---------- */
  document.querySelectorAll('form[data-date-form]').forEach(form => {
    form.addEventListener('submit', ev => {
      const i = form.check_in.value, o = form.check_out.value;
      if (!i || !o || o <= i || i < today) { ev.preventDefault(); alert('Please choose a valid check-in date and a later check-out date.'); }
    });
  });

  /* ---------- Registration validation ---------- */
  const reg = document.getElementById('registerForm');
  if (reg) reg.addEventListener('submit', ev => {
    const errs = [];
    const f = reg.elements;
    if (f.name.value.trim().length < 2) errs.push('Please enter your full name.');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(f.email.value.trim())) errs.push('Please enter a valid email address.');
    if (!/^\+?[0-9 ]{9,15}$/.test(f.contact.value.trim())) errs.push('Contact number must have 9-15 digits.');
    if (f.password.value.length < 8 || !/[A-Za-z]/.test(f.password.value) || !/[0-9]/.test(f.password.value)) errs.push('Password needs 8+ characters with letters and numbers.');
    if (f.password.value !== f.password2.value) errs.push('Passwords do not match.');
    ['name', 'email', 'contact', 'password', 'password2'].forEach(n => markInvalid(f[n], false));
    if (errs.length) { ev.preventDefault(); showMessages(reg, errs); }
  });

  /* ---------- Login validation ---------- */
  const login = document.getElementById('loginForm');
  if (login) login.addEventListener('submit', ev => {
    const errs = [];
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(login.email.value.trim())) errs.push('Please enter a valid email address.');
    if (!login.password.value) errs.push('Please enter your password.');
    if (errs.length) { ev.preventDefault(); showMessages(login, errs); }
  });

  /* ---------- Booking form: live cost + AJAX availability ---------- */
  const form = document.getElementById('bookingForm');
  if (form) {
    const rates = JSON.parse(form.dataset.rates || '[]');
    const roomEl = form.room_id, inEl = form.check_in, outEl = form.check_out, guestsEl = form.guests;
    const summary = document.getElementById('summary');
    let available = true, timer;

    /** Estimated cost, night by night, using the same seasonal rules as the server. */
    function estimate(base, a, b) {
      let total = 0;
      for (let d = a; d < b; d = addDays(d, 1)) {
        const r = rates.find(x => d >= x.s && d <= x.e);
        total += base * (r ? r.m : 1);
      }
      return total;
    }
    function update() {
      const opt = roomEl.selectedOptions[0];
      const base = opt && opt.dataset.price ? parseFloat(opt.dataset.price) : 0;
      if (opt && opt.dataset.max) guestsEl.max = opt.dataset.max;
      summary.className = 'summary';
      if (!base || !inEl.value || !outEl.value) { summary.textContent = 'Select a room and dates to see your total.'; return; }
      const n = nightsBetween(inEl.value, outEl.value);
      if (n < 1) { summary.className = 'summary bad'; summary.textContent = 'Check-out must be after check-in.'; available = false; return; }
      summary.innerHTML = '';
      const p = document.createElement('div');
      p.innerHTML = `<strong>${n}</strong> night(s) &middot; Estimated total: <strong class="total">${fmt(estimate(base, inEl.value, outEl.value))}</strong>`;
      const status = document.createElement('div'); status.id = 'availStatus'; status.textContent = 'Checking availability…';
      summary.append(p, status);
      clearTimeout(timer);
      timer = setTimeout(checkAvailability, 300);
    }
    /** AJAX call to api/check_availability.php (server confirms price and availability). */
    function checkAvailability() {
      const q = new URLSearchParams({ room_id: roomEl.value, check_in: inEl.value, check_out: outEl.value });
      fetch(form.dataset.api + '?' + q)
        .then(r => r.json())
        .then(d => {
          const s = document.getElementById('availStatus'); if (!s) return;
          available = !!(d.ok && d.available);
          s.textContent = d.message;
          summary.classList.toggle('ok', available);
          summary.classList.toggle('bad', !available);
        })
        .catch(() => { const s = document.getElementById('availStatus'); if (s) s.textContent = 'Could not check availability right now.'; });
    }
    [roomEl, inEl, outEl].forEach(el => el.addEventListener('change', update));
    update();

    form.addEventListener('submit', ev => {
      const errs = [];
      const opt = roomEl.selectedOptions[0];
      if (!roomEl.value) errs.push('Please select a room.');
      if (!inEl.value || !outEl.value) errs.push('Please choose check-in and check-out dates.');
      else if (inEl.value < today) errs.push('Check-in cannot be in the past.');
      else if (outEl.value <= inEl.value) errs.push('Check-out must be after check-in.');
      else if (nightsBetween(inEl.value, outEl.value) > 30) errs.push('Maximum stay is 30 nights.');
      const g = parseInt(guestsEl.value, 10), max = opt && opt.dataset.max ? parseInt(opt.dataset.max, 10) : 10;
      if (!g || g < 1 || g > max) errs.push(`This room allows 1 to ${max} guests.`);
      if (form.elements['name'].value.trim().length < 2) errs.push('Please enter the guest name.');
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.value.trim())) errs.push('Please enter a valid email address.');
      if (!/^\+?[0-9 ]{9,15}$/.test(form.phone.value.trim())) errs.push('Please enter a valid contact number.');
      if (!errs.length && !available) errs.push('This room is not available for the selected dates.');
      markInvalid(roomEl, !roomEl.value); markInvalid(inEl, !inEl.value); markInvalid(outEl, !outEl.value);
      if (errs.length) { ev.preventDefault(); showMessages(form, errs); window.scrollTo({ top: form.offsetTop - 80, behavior: 'smooth' }); }
    });
  }
});
