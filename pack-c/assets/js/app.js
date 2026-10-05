/**
 * Mykid Basic (Package C) — client script.
 *
 * - All interactive elements use [data-action] with one delegated click handler,
 *   so content swapped in from the API keeps working.
 * - The API returns server-rendered HTML fragments; we replace [data-fragment] contents.
 * - Parent pages poll the API so status changes made by the teacher show up live.
 */
(function () {
  'use strict';

  const CFG = window.MYKID || {};
  const DEBUG = Boolean(CFG.debug);
  const POLL_INTERVAL_MS = 6000;
  const TOAST_DURATION_MS = 2600;

  /* ------------------------------------------------------------------------
   * Logging (dev only)
   * --------------------------------------------------------------------- */
  const Log = {
    start(fn, data) { if (DEBUG) console.log(`[MykidC][${fn}] START`, data ?? ''); },
    end(fn, data) { if (DEBUG) console.log(`[MykidC][${fn}] END`, data ?? ''); },
    info(fn, msg, data) { if (DEBUG) console.log(`[MykidC][${fn}] ${msg}`, data ?? ''); },
    error(fn, error, context) { console.error(`[MykidC][${fn}] ERROR`, { error, ...context }); },
  };

  function devGroup(title, url, request, status, response) {
    if (!DEBUG) return;
    console.group(`[DEV] API Response — ${title}`);
    console.log('URL:', url);
    console.log('Request:', request);
    console.log('Status:', status);
    console.log('Response:', response);
    console.groupEnd();
  }

  /** Error whose message is safe to show to users. */
  class UserError extends Error {}

  /* ------------------------------------------------------------------------
   * Small helpers
   * --------------------------------------------------------------------- */
  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

  function setBusy(button, busy) {
    if (!button) return;
    button.disabled = busy;
    button.classList.toggle('is-busy', busy);
  }

  function toast(message, type = 'success') {
    Log.start('toast', { message, type });
    const stack = $('[data-toast-stack]');
    if (!stack || !message) return;

    const icons = { success: '✅', error: '😥', info: '🔔', lock: '🔒' };
    const el = document.createElement('div');
    el.className = `toast toast--${type}`;
    el.setAttribute('role', type === 'error' ? 'alert' : 'status');
    el.innerHTML = `<span class="toast__icon" aria-hidden="true">${icons[type] || icons.success}</span><span class="toast__text"></span>`;
    el.querySelector('.toast__text').textContent = message;
    stack.appendChild(el);

    requestAnimationFrame(() => el.classList.add('is-in'));
    setTimeout(() => {
      el.classList.remove('is-in');
      setTimeout(() => el.remove(), 300);
    }, TOAST_DURATION_MS);
  }

  /* ------------------------------------------------------------------------
   * API
   * --------------------------------------------------------------------- */
  function fragmentKeys() {
    return [...new Set($$('[data-fragment]').map((el) => el.dataset.fragment))];
  }

  function applyFragments(fragments) {
    Object.entries(fragments || {}).forEach(([key, html]) => {
      $$(`[data-fragment="${key}"]`).forEach((el) => {
        el.innerHTML = html;
        el.classList.remove('is-updated');
        void el.offsetWidth; // restart the highlight animation
        el.classList.add('is-updated');
      });
    });
  }

  async function api(action, payload = {}, { quiet = false } = {}) {
    if (!quiet) Log.start('api', { action, payload });
    const request = { action, fragments: fragmentKeys(), ...payload };

    let response;
    let data;
    try {
      response = await fetch(CFG.apiUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CFG.csrf },
        body: JSON.stringify(request),
      });
      data = await response.json();
    } catch (error) {
      Log.error('api', error, { action, payload });
      throw new UserError('ไม่สามารถเชื่อมต่อได้ กรุณาลองใหม่อีกครั้ง');
    }

    if (!quiet || data.changed) devGroup(action, CFG.apiUrl, request, response.status, data);

    if (!data.ok) {
      Log.error('api', data.message, { action, status: response.status });
      if (data.redirect) setTimeout(() => { window.location.href = data.redirect; }, 1200);
      throw new UserError(data.message || 'กรุณาลองใหม่อีกครั้ง');
    }

    if (data.version) CFG.version = data.version;
    if (data.fragments) applyFragments(data.fragments);
    if (!quiet) Log.end('api', { action, version: data.version });
    return data;
  }

  /** Run an API action from a button with busy state + toast feedback. */
  async function runAction(button, action, payload, onSuccess) {
    setBusy(button, true);
    try {
      const data = await api(action, payload);
      if (onSuccess) onSuccess(data);
      toast(data.message);
      return data;
    } catch (error) {
      toast(error instanceof UserError ? error.message : 'กรุณาลองใหม่อีกครั้ง', 'error');
      if (!(error instanceof UserError)) Log.error('runAction', error, { action, payload });
      return null;
    } finally {
      setBusy(button, false);
    }
  }

  /* ------------------------------------------------------------------------
   * Bottom sheets + confirm dialog
   * --------------------------------------------------------------------- */
  let lastFocused = null;

  function openSheet(id) {
    Log.start('openSheet', { id });
    const sheet = document.getElementById(id);
    if (!sheet) return null;
    lastFocused = document.activeElement;
    sheet.classList.add('is-open');
    sheet.setAttribute('aria-hidden', 'false');
    document.body.classList.add('has-sheet');
    const panel = $('.sheet__panel', sheet);
    panel.setAttribute('tabindex', '-1');
    setTimeout(() => panel.focus({ preventScroll: true }), 50);
    return sheet;
  }

  function closeSheet(sheet) {
    const targets = sheet ? [sheet] : $$('.sheet.is-open');
    targets.forEach((s) => {
      s.classList.remove('is-open');
      s.setAttribute('aria-hidden', 'true');
      s.dispatchEvent(new CustomEvent('sheet:close'));
    });
    if (!$('.sheet.is-open')) document.body.classList.remove('has-sheet');
    if (lastFocused && document.contains(lastFocused)) lastFocused.focus({ preventScroll: true });
  }

  function confirmDialog({ title, message, icon = '🗑️', okLabel = 'ยืนยัน' }) {
    return new Promise((resolve) => {
      const sheet = openSheet('sheet-confirm');
      if (!sheet) return resolve(window.confirm(message));

      $('[data-confirm-title]', sheet).textContent = title;
      $('[data-confirm-message]', sheet).textContent = message;
      $('[data-confirm-icon]', sheet).textContent = icon;
      const ok = $('[data-confirm-ok]', sheet);
      ok.textContent = okLabel;

      const cleanup = (result) => {
        ok.removeEventListener('click', onOk);
        sheet.removeEventListener('sheet:close', onClose);
        resolve(result);
      };
      const onOk = () => { cleanup(true); closeSheet(sheet); };
      const onClose = () => cleanup(false);
      ok.addEventListener('click', onOk);
      sheet.addEventListener('sheet:close', onClose);
    });
  }

  /* ------------------------------------------------------------------------
   * Teacher: status
   * --------------------------------------------------------------------- */
  async function setStatus(button) {
    const code = button.dataset.code;
    const scope = button.closest('[data-status-scope]');
    const noteInput = scope ? $('[data-status-note]', scope) : null;
    const note = noteInput ? noteInput.value.trim() : '';
    // Resolve before the API call: the button is replaced when the fragment refreshes.
    const sheet = button.closest('.sheet');
    Log.start('setStatus', { code, note });

    await runAction(button, 'set_status', { code, note }, () => {
      if (noteInput) noteInput.value = '';
      if (sheet) closeSheet(sheet);
    });
    Log.end('setStatus', { code });
  }

  /* ------------------------------------------------------------------------
   * Teacher: activities
   * --------------------------------------------------------------------- */
  function nextHour() {
    const hour = Math.min(new Date().getHours() + 1, 23);
    return `${String(hour).padStart(2, '0')}:00`;
  }

  function openActivityForm(activity) {
    Log.start('openActivityForm', { activity });
    const sheet = openSheet('sheet-activity');
    if (!sheet) return;
    const form = $('[data-activity-form]', sheet);
    const isEdit = Boolean(activity);

    form.reset();
    form.elements.id.value = isEdit ? activity.id : '';
    form.elements.time.value = isEdit ? activity.time : nextHour();
    form.elements.title.value = isEdit ? activity.title : '';
    form.elements.detail.value = isEdit ? activity.detail : '';
    const iconInput = $$('input[name="icon"]', form).find((r) => r.value === (isEdit ? activity.icon : '')) || $('input[name="icon"]', form);
    iconInput.checked = true;

    $('.sheet__title', sheet).textContent = isEdit ? 'แก้ไขกิจกรรม' : 'เพิ่มกิจกรรม';
    $('[data-submit-label]', form).textContent = isEdit ? 'บันทึกการแก้ไข' : 'บันทึกกิจกรรม';
    $('[data-form-error]', form).hidden = true;
  }

  async function submitActivityForm(form) {
    const id = form.elements.id.value;
    const activity = {
      time: form.elements.time.value,
      title: form.elements.title.value.trim(),
      detail: form.elements.detail.value.trim(),
      icon: (form.querySelector('input[name="icon"]:checked') || {}).value,
    };
    Log.start('submitActivityForm', { id, activity });

    const errorEl = $('[data-form-error]', form);
    const clientError = !activity.time ? 'กรุณาระบุเวลา' : !activity.title ? 'กรุณากรอกชื่อกิจกรรม' : '';
    if (clientError) {
      errorEl.textContent = clientError;
      errorEl.hidden = false;
      (activity.time ? form.elements.title : form.elements.time).focus();
      return;
    }

    errorEl.hidden = true;
    await runAction(form.querySelector('[type="submit"]'), id ? 'update_activity' : 'add_activity', { id, activity }, () => {
      closeSheet(form.closest('.sheet'));
    });
    Log.end('submitActivityForm', { id });
  }

  async function deleteActivity(button) {
    const item = button.closest('[data-activity]');
    const activity = JSON.parse(item.dataset.activity);
    Log.start('deleteActivity', { activity });

    const confirmed = await confirmDialog({
      title: 'ลบกิจกรรมนี้?',
      message: `“${activity.time} ${activity.title}” จะถูกลบออกจากตารางวันนี้`,
      okLabel: 'ลบกิจกรรม',
    });
    if (!confirmed) return;
    await runAction(button, 'delete_activity', { id: activity.id });
    Log.end('deleteActivity', { id: activity.id });
  }

  /* ------------------------------------------------------------------------
   * Teacher: food
   * --------------------------------------------------------------------- */
  function toggleMealEdit(button, editing) {
    const card = button.closest('[data-meal]');
    const form = $('[data-meal-form]', card);
    Log.info('toggleMealEdit', editing ? 'edit' : 'cancel', { meal: card.dataset.meal });
    if (!editing) form.reset();
    card.classList.toggle('is-editing', editing);
    form.hidden = !editing;
    if (editing) form.elements.items.focus();
  }

  async function saveMeal(form) {
    const card = form.closest('[data-meal]');
    const meal = card.dataset.meal;
    const items = form.elements.items.value.split('\n').map((s) => s.trim()).filter(Boolean);
    Log.start('saveMeal', { meal, items });

    if (!items.length) {
      toast('กรุณากรอกรายการอาหารอย่างน้อย 1 รายการ', 'error');
      return;
    }
    await runAction(form.querySelector('[type="submit"]'), 'save_meal', { meal, items });
    Log.end('saveMeal', { meal });
  }

  /* ------------------------------------------------------------------------
   * Demo tools
   * --------------------------------------------------------------------- */
  async function resetDemo(button) {
    const confirmed = await confirmDialog({
      title: 'รีเซ็ตข้อมูล Demo?',
      message: 'สถานะ กิจกรรม และเมนูอาหาร จะกลับเป็นค่าเริ่มต้นของ Demo',
      icon: '🔄',
      okLabel: 'รีเซ็ต',
    });
    if (!confirmed) return;
    const data = await runAction(button, 'reset_demo');
    if (data) setTimeout(() => window.location.reload(), 900);
  }

  /* ------------------------------------------------------------------------
   * Login page
   * --------------------------------------------------------------------- */
  function formatPhone(value) {
    const d = value.replace(/\D/g, '').slice(0, 10);
    if (d.length <= 3) return d;
    if (d.length <= 6) return `${d.slice(0, 3)}-${d.slice(3)}`;
    return `${d.slice(0, 3)}-${d.slice(3, 6)}-${d.slice(6)}`;
  }

  function fillPin(boxes, digits) {
    boxes.forEach((box, i) => { box.value = digits[i] || ''; });
    const next = boxes.find((b) => !b.value) || boxes[boxes.length - 1];
    next.focus();
  }

  function initLogin() {
    const form = $('[data-login-form]');
    if (!form) return;
    Log.start('initLogin');

    const boxes = $$('.pin__box', form);
    const phone = $('[data-phone-input]', form);
    const themeMeta = $('meta[name="theme-color"]');

    form.addEventListener('change', (event) => {
      if (event.target.name !== 'role') return;
      const role = event.target.value;
      Log.info('initLogin', 'role changed', { role });
      document.body.classList.remove('theme-teacher', 'theme-parent');
      document.body.classList.add(`theme-${role}`);
      if (themeMeta) themeMeta.content = role === 'teacher' ? '#DFF4FF' : '#FFEAF2';
    });

    phone.addEventListener('input', () => { phone.value = formatPhone(phone.value); });

    boxes.forEach((box, index) => {
      box.addEventListener('input', () => {
        const digits = box.value.replace(/\D/g, '');
        if (digits.length > 1) {
          fillPin(boxes, (boxes.slice(0, index).map((b) => b.value).join('') + digits).slice(0, 6));
          return;
        }
        box.value = digits;
        if (digits && boxes[index + 1]) boxes[index + 1].focus();
      });
      box.addEventListener('keydown', (event) => {
        if (event.key === 'Backspace' && !box.value && boxes[index - 1]) {
          boxes[index - 1].value = '';
          boxes[index - 1].focus();
        }
      });
      box.addEventListener('paste', (event) => {
        event.preventDefault();
        fillPin(boxes, (event.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6));
      });
    });

    form.addEventListener('submit', (event) => {
      const pin = boxes.map((b) => b.value).join('');
      const digits = phone.value.replace(/\D/g, '');
      Log.info('initLogin', 'submit', { phone: digits, pinLength: pin.length });
      if (digits.length !== 10) {
        event.preventDefault();
        toast('กรุณากรอกเบอร์โทรศัพท์ 10 หลัก', 'error');
        phone.focus();
      } else if (pin.length !== 6) {
        event.preventDefault();
        toast('กรุณากรอกรหัส PIN ให้ครบ 6 หลัก', 'error');
        (boxes.find((b) => !b.value) || boxes[0]).focus();
      } else {
        setBusy(form.querySelector('[type="submit"]'), true);
      }
    });

    if (!phone.value) phone.focus({ preventScroll: true });
    Log.end('initLogin');
  }

  /** Demo account selector: pick an account → switch role, fill phone + PIN. */
  function fillDemoAccount(button) {
    const form = button.closest('form');
    const { role, phone, pin } = button.dataset;
    Log.info('fillDemoAccount', 'fill', { role, phone });

    const roleInput = form.querySelector(`input[name="role"][value="${role}"]`);
    if (roleInput && !roleInput.checked) {
      roleInput.checked = true;
      roleInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
    form.querySelector('[data-phone-input]').value = formatPhone(phone);
    fillPin($$('.pin__box', form), pin);
    $$('.demo-account-btn', form).forEach((b) => b.classList.toggle('is-selected', b === button));
    form.querySelector('[type="submit"]').focus({ preventScroll: true });
    toast(`เลือกบัญชี “${button.querySelector('strong').textContent}” แล้ว กด “เข้าสู่ระบบ” ได้เลย`, 'info');
  }

  /* ------------------------------------------------------------------------
   * Live clock + parent polling
   * --------------------------------------------------------------------- */
  function initClock() {
    const clocks = $$('[data-clock]');
    if (!clocks.length) return;
    const tick = () => {
      const now = new Date();
      const text = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
      clocks.forEach((c) => { c.textContent = text; });
    };
    tick();
    setInterval(tick, 15000);
  }

  function initPolling() {
    if (!CFG.poll || !fragmentKeys().length) return;
    Log.info('initPolling', 'enabled', { intervalMs: POLL_INTERVAL_MS, version: CFG.version });

    setInterval(async () => {
      if (document.hidden || $('.sheet.is-open')) return;
      try {
        const data = await api('state', { version: CFG.version }, { quiet: true });
        if (data.changed) toast('มีการอัปเดตใหม่จากคุณครู', 'info');
      } catch (error) {
        Log.error('initPolling', error, { version: CFG.version });
      }
    }, POLL_INTERVAL_MS);
  }

  /* ------------------------------------------------------------------------
   * Event wiring
   * --------------------------------------------------------------------- */
  const ACTIONS = {
    'open-sheet': (btn) => openSheet(btn.dataset.sheet),
    'close-sheet': (btn) => closeSheet(btn.closest('.sheet')),
    'set-status': setStatus,
    'add-activity': () => openActivityForm(null),
    'edit-activity': (btn) => openActivityForm(JSON.parse(btn.closest('[data-activity]').dataset.activity)),
    'delete-activity': deleteActivity,
    'edit-meal': (btn) => toggleMealEdit(btn, true),
    'cancel-meal': (btn) => toggleMealEdit(btn, false),
    'reset-demo': resetDemo,
    'fill-account': fillDemoAccount,
  };

  document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-action]');
    if (!button || button.disabled) return;
    const handler = ACTIONS[button.dataset.action];
    if (!handler) return;
    event.preventDefault();
    Log.info('click', button.dataset.action, { ...button.dataset });
    handler(button);
  });

  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (form.matches('[data-activity-form]')) {
      event.preventDefault();
      submitActivityForm(form);
    } else if (form.matches('[data-meal-form]')) {
      event.preventDefault();
      saveMeal(form);
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && $('.sheet.is-open')) closeSheet();
  });

  document.addEventListener('DOMContentLoaded', () => {
    Log.start('init', { page: CFG.page, role: CFG.role, version: CFG.version });
    initLogin();
    initClock();
    initPolling();
    if (CFG.toast) toast(CFG.toast.message || CFG.toast, CFG.toast.type || 'success');
    Log.end('init');
  });
})();
