// js/api.js
// Central API client — all fetch calls go through here

const BASE_URL = 'http://localhost:8000/api'; // Change to your backend URL

/**
 * Core request helper. Attaches Bearer token automatically.
 * Throws on non-2xx (or handles 401 by redirecting to login).
 */
async function request(method, endpoint, body = null) {
  const token = localStorage.getItem('np_token');

  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const options = { method, headers };
  if (body) options.body = JSON.stringify(body);

  const response = await fetch(`${BASE_URL}${endpoint}`, options);

  // Token expired / invalid — force logout
  if (response.status === 401) {
    localStorage.removeItem('np_token');
    localStorage.removeItem('np_user');
    window.location.href = '/index.html';
    throw new Error('Unauthenticated');
  }

  const data = await response.json();

  if (!response.ok) {
    // Pass validation errors through
    const err = new Error(data.message || 'Request failed');
    err.status = response.status;
    err.errors = data.errors || {};
    throw err;
  }

  return data;
}

// ─── AUTH ────────────────────────────────────
export const register = (name, email, password, password_confirmation) =>
  request('POST', '/register', { name, email, password, password_confirmation });

export const login = (email, password) =>
  request('POST', '/login', { email, password });

export const logout = () =>
  request('POST', '/logout');

// ─── GAME ────────────────────────────────────
export const generateQuestion = () =>
  request('GET', '/generate-question');

export const submitAnswer = (answer) =>
  request('POST', '/submit-answer', { answer });

export const getScore = () =>
  request('GET', '/get-score');

export const startSession = (difficulty) =>
  request('POST', '/start-session', { difficulty });

export const endGame = () =>
  request('POST', '/end-game');

// ─── LEADERBOARD ─────────────────────────────
export const getLeaderboard = (difficulty = null) => {
  const qs = difficulty ? `?difficulty=${difficulty}` : '';
  return request('GET', `/leaderboard${qs}`);
};

// ─── USER STATS ──────────────────────────────
export const getStats = () =>
  request('GET', '/stats');
