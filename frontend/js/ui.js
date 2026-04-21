// js/ui.js
// DOM manipulation helpers — keep logic out of ui.js

export const $ = (sel, ctx = document) => ctx.querySelector(sel);
export const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

export function showError(el, msg) {
  if (!el) return;
  el.textContent = msg;
}

export function clearErrors(...els) {
  els.forEach(el => { if (el) el.textContent = ''; });
}

export function showFormError(el, msg) {
  if (!el) return;
  el.textContent = msg;
  el.classList.add('show');
}

export function clearFormError(el) {
  if (!el) return;
  el.textContent = '';
  el.classList.remove('show');
}

export function setLoading(btn, loading) {
  const text = btn.querySelector('.btn-text');
  const loader = btn.querySelector('.btn-loader');
  btn.disabled = loading;
  if (text) text.classList.toggle('hidden', loading);
  if (loader) loader.classList.toggle('hidden', !loading);
}

export function updateLives(livesEl, count) {
  // count = number of remaining lives (1..3)
  livesEl.forEach((el, i) => {
    if (i < count) {
      el.classList.remove('lost');
    } else {
      el.classList.add('lost');
    }
  });
}

export function buildSequenceRow(container, sequence) {
  container.innerHTML = '';
  sequence.forEach((num, i) => {
    const span = document.createElement('span');
    span.className = 'seq-num';
    span.textContent = num;
    span.style.animationDelay = `${i * 0.08}s`;
    container.appendChild(span);

    if (i < sequence.length - 1) {
      const sep = document.createElement('span');
      sep.className = 'seq-sep';
      sep.textContent = ', ';
      container.appendChild(sep);
    }
  });
}

export function animateScore(el, isPositive) {
  el.classList.remove('score-bump', 'score-drop');
  void el.offsetWidth; // reflow
  el.classList.add(isPositive ? 'score-bump' : 'score-drop');
}

export function showFeedback(el, correct, correctAnswer = null) {
  el.classList.remove('hidden', 'correct', 'wrong');
  if (correct) {
    el.textContent = '✓ Correct! +10';
    el.classList.add('correct');
  } else {
    el.textContent = correctAnswer !== null
      ? `✗ Wrong. Answer was ${correctAnswer}. −5`
      : '✗ Wrong! −5';
    el.classList.add('wrong');
  }
}

export function hideFeedback(el) {
  el.classList.add('hidden');
}

export function setStreakText(el, correctStreak, wrongStreak) {
  if (correctStreak >= 3) {
    el.textContent = `🔥 ${correctStreak} streak — leveling up!`;
    el.style.color = 'var(--accent-green)';
  } else if (wrongStreak >= 2) {
    el.textContent = `❄️ Cooling down...`;
    el.style.color = 'var(--accent-blue)';
  } else if (correctStreak > 0) {
    el.textContent = `⚡ ${correctStreak} correct in a row`;
    el.style.color = 'var(--text-muted)';
  } else {
    el.textContent = '';
  }
}
