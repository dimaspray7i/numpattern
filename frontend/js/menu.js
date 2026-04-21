// js/menu.js
// Menu page controller

import { logout, getLeaderboard, getStats } from './api.js';
import { requireAuth, getUser, clearSession } from './auth.js';
import { $ } from './ui.js';

if (!requireAuth()) throw new Error('Redirect to login');

const user = getUser();

// ── User greeting ──────────────────────────────
const userNameEl = $('#userName');
if (userNameEl && user) userNameEl.textContent = user.name;

// ── Logout ────────────────────────────────────
$('#logoutBtn')?.addEventListener('click', async () => {
  try { await logout(); } catch (_) {}
  clearSession();
  window.location.href = '/index.html';
});

// ── Difficulty select ─────────────────────────
let selectedDifficulty = 'easy';

document.querySelectorAll('.diff-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.diff-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    selectedDifficulty = btn.dataset.diff;
  });
});

// ── Start game ────────────────────────────────
$('#startGameBtn')?.addEventListener('click', () => {
  sessionStorage.setItem('np_difficulty', selectedDifficulty);
  window.location.href = '/game.html';
});

// ── Load stats ────────────────────────────────
async function loadStats() {
  try {
    const { best_score, games_played, rank } = await getStats();
    const fmt = v => (v !== null && v !== undefined) ? v : '—';
    $('#statBest').textContent = fmt(best_score);
    $('#statGames').textContent = fmt(games_played);
    $('#statRank').textContent = rank ? `#${rank}` : '—';
  } catch (_) {
    // Stats optional — fail silently
  }
}

// ── Leaderboard ───────────────────────────────
let currentLbDiff = null;

async function loadLeaderboard(difficulty = null) {
  const lbEl = $('#leaderboard');
  if (!lbEl) return;
  lbEl.innerHTML = '<div class="lb-loading">Loading…</div>';

  try {
    const { data } = await getLeaderboard(difficulty);
    renderLeaderboard(lbEl, data || []);
  } catch (_) {
    lbEl.innerHTML = '<div class="lb-empty">Could not load leaderboard.</div>';
  }
}

function renderLeaderboard(container, rows) {
  if (!rows.length) {
    container.innerHTML = '<div class="lb-empty">No scores yet. Be the first!</div>';
    return;
  }

  const medals = ['gold', 'silver', 'bronze'];

  container.innerHTML = rows.map((row, i) => `
    <div class="lb-row">
      <span class="lb-rank ${medals[i] || ''}">
        ${i < 3 ? ['🥇','🥈','🥉'][i] : `#${i+1}`}
      </span>
      <span class="lb-name">${escHtml(row.user_name || 'Unknown')}</span>
      <span class="lb-diff-badge ${row.difficulty}">${row.difficulty}</span>
      <span class="lb-score">${row.score}</span>
    </div>
  `).join('');
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

// LB filter tabs
document.querySelectorAll('.lb-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.lb-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    const diff = tab.dataset.diff === 'all' ? null : tab.dataset.diff;
    currentLbDiff = diff;
    loadLeaderboard(diff);
  });
});

// ── Init ──────────────────────────────────────
loadStats();
loadLeaderboard(null);
