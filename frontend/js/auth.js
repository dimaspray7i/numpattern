// js/auth.js
// Authentication helpers used across pages

export function getToken() {
  return localStorage.getItem('np_token');
}

export function getUser() {
  const raw = localStorage.getItem('np_user');
  try { return raw ? JSON.parse(raw) : null; } catch { return null; }
}

export function saveSession(token, user) {
  localStorage.setItem('np_token', token);
  localStorage.setItem('np_user', JSON.stringify(user));
}

export function clearSession() {
  localStorage.removeItem('np_token');
  localStorage.removeItem('np_user');
}

export function isAuthenticated() {
  return !!getToken();
}

/**
 * Call on protected pages. Redirects to login if not authenticated.
 */
export function requireAuth() {
  if (!isAuthenticated()) {
    window.location.replace('/index.html');
    return false;
  }
  return true;
}

/**
 * Call on auth page. Redirects to menu if already logged in.
 */
export function redirectIfAuth() {
  if (isAuthenticated()) {
    window.location.replace('/menu.html');
  }
}
