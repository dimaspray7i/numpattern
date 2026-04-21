// js/main.js
// Auth page controller (index.html)

import { register, login } from './api.js';
import { saveSession, redirectIfAuth } from './auth.js';
import { $, showError, clearErrors, showFormError, clearFormError, setLoading } from './ui.js';

redirectIfAuth();

// ── Floating numbers background ───────────────
(function initFloatingNums() {
  const container = $('#floatingNumbers');
  if (!container) return;
  const nums = Array.from({ length: 30 }, () => Math.floor(Math.random() * 999));
  nums.forEach(n => {
    const el = document.createElement('span');
    el.className = 'float-num';
    el.textContent = n;
    el.style.left = Math.random() * 100 + '%';
    el.style.animationDuration = (8 + Math.random() * 10) + 's';
    el.style.animationDelay = (-Math.random() * 12) + 's';
    container.appendChild(el);
  });
})();

// ── Tab switching ─────────────────────────────
$$('[data-tab]').forEach(btn => {
  btn.addEventListener('click', () => {
    $$('.tab-btn').forEach(b => b.classList.remove('active'));
    $$('.tab-content').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    const tab = document.getElementById(`tab-${btn.dataset.tab}`);
    if (tab) tab.classList.add('active');
  });
});

function $$(...args) { return [...document.querySelectorAll(...args)]; }

// ── LOGIN ─────────────────────────────────────
const loginForm = $('#loginForm');

loginForm?.addEventListener('submit', async (e) => {
  e.preventDefault();

  const emailEl = $('#loginEmail');
  const passEl = $('#loginPassword');
  const emailErrEl = $('#loginEmailErr');
  const passErrEl = $('#loginPasswordErr');
  const formErrEl = $('#loginFormErr');
  const btn = $('#loginBtn');

  clearErrors(emailErrEl, passErrEl);
  clearFormError(formErrEl);

  const email = emailEl.value.trim();
  const password = passEl.value;

  let valid = true;
  if (!email) { showError(emailErrEl, 'Email is required'); valid = false; }
  if (!password) { showError(passErrEl, 'Password is required'); valid = false; }
  if (!valid) return;

  setLoading(btn, true);

  try {
    const { user, token } = await login(email, password);
    saveSession(token, user);
    window.location.href = '/menu.html';
  } catch (err) {
    if (err.errors?.email) showError(emailErrEl, err.errors.email[0]);
    else showFormError(formErrEl, err.message || 'Login failed. Please try again.');
  } finally {
    setLoading(btn, false);
  }
});

// ── REGISTER ──────────────────────────────────
const registerForm = $('#registerForm');

registerForm?.addEventListener('submit', async (e) => {
  e.preventDefault();

  const nameEl = $('#regName');
  const emailEl = $('#regEmail');
  const passEl = $('#regPassword');
  const confirmEl = $('#regPasswordConfirm');
  const btn = $('#registerBtn');

  clearErrors(
    $('#regNameErr'), $('#regEmailErr'),
    $('#regPasswordErr'), $('#regConfirmErr')
  );
  clearFormError($('#regFormErr'));

  const name = nameEl.value.trim();
  const email = emailEl.value.trim();
  const password = passEl.value;
  const password_confirmation = confirmEl.value;

  let valid = true;
  if (!name) { showError($('#regNameErr'), 'Name is required'); valid = false; }
  if (!email) { showError($('#regEmailErr'), 'Email is required'); valid = false; }
  if (!password || password.length < 6) { showError($('#regPasswordErr'), 'Min 6 characters'); valid = false; }
  if (password !== password_confirmation) { showError($('#regConfirmErr'), 'Passwords do not match'); valid = false; }
  if (!valid) return;

  setLoading(btn, true);

  try {
    const { user, token } = await register(name, email, password, password_confirmation);
    saveSession(token, user);
    window.location.href = '/menu.html';
  } catch (err) {
    const errs = err.errors || {};
    if (errs.name) showError($('#regNameErr'), errs.name[0]);
    if (errs.email) showError($('#regEmailErr'), errs.email[0]);
    if (errs.password) showError($('#regPasswordErr'), errs.password[0]);
    if (!Object.keys(errs).length) {
      showFormError($('#regFormErr'), err.message || 'Registration failed.');
    }
  } finally {
    setLoading(btn, false);
  }
});
