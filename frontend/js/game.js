import { generateQuestion, submitAnswer, endGame, startSession } from './api.js';
import { requireAuth } from './auth.js';
import { $, $$, buildSequenceRow, animateScore, showFeedback, hideFeedback, setStreakText, updateLives } from './ui.js';
import { getState, setState, resetGameState } from './state.js';

if (!requireAuth()) throw new Error('Redirect to login');

// ── DOM refs ──────────────────────────────────
const timerDisplay = $('#timerDisplay');
const timerRing = $('#timerRing');
const scoreDisplay = $('#scoreDisplay');
const patternLoading = $('#patternLoading');
const patternContent = $('#patternContent');
const patternTypeBadge = $('#patternTypeBadge');
const sequenceRow = $('#sequenceRow');
const answerInput = $('#answerInput');
const submitBtn = $('#submitBtn');
const answerFeedback = $('#answerFeedback');
const streakText = $('#streakText');
const diffBadge = $('#diffBadge');
const gameOverModal = $('#gameOverModal');
const modalScore = $('#modalScore');
const modalDifficulty = $('#modalDifficulty');
const modalTitle = $('#modalTitle');
const modalIcon = $('#modalIcon');
const liveHearts = [$$('.live-heart')[0], $$('.live-heart')[1], $$('.live-heart')[2]];

const TOTAL_TIME = 60;
const CIRCUMFERENCE = 163.4; // 2π × 26

let timerInterval = null;
let submitLocked = false;
let feedbackTimeout = null;

// ── Init ──────────────────────────────────────
async function init() {
  const difficulty = sessionStorage.getItem('np_difficulty') || 'easy';
  setState({ difficulty });

  diffBadge.textContent = difficulty.toUpperCase() === 'EASY' ? 'MUDAH' : difficulty.toUpperCase() === 'MEDIUM' ? 'SEDANG' : 'SULIT';
  diffBadge.className = `diff-badge ${difficulty}`;

  resetGameState();
  setState({ difficulty });

  try {
    await startSession(difficulty);
  } catch (err) {
    console.error('Session start failed', err);
  }

  startTimer();
  await loadQuestion();
}

// ── Timer ─────────────────────────────────────
function startTimer() {
  const state = getState();
  setState({ timerSeconds: TOTAL_TIME, isGameRunning: true });
  updateTimerUI(TOTAL_TIME);

  timerInterval = setInterval(() => {
    const { timerSeconds } = getState();
    const next = timerSeconds - 1;
    setState({ timerSeconds: next });
    updateTimerUI(next);

    if (next <= 0) {
      clearInterval(timerInterval);
      triggerGameOver('time');
    }
  }, 1000);
}

function updateTimerUI(seconds) {
  timerDisplay.textContent = seconds;
  const progress = seconds / TOTAL_TIME;
  const offset = CIRCUMFERENCE * (1 - progress);
  timerRing.style.strokeDashoffset = offset;

  timerRing.classList.remove('warning', 'critical');
  timerDisplay.classList.remove('warning', 'critical');

  if (seconds <= 10) {
    timerRing.classList.add('critical');
    timerDisplay.classList.add('critical');
  } else if (seconds <= 20) {
    timerRing.classList.add('warning');
    timerDisplay.classList.add('warning');
  }
}

// ── Load question ─────────────────────────────
async function loadQuestion() {
  patternContent.classList.add('hidden');
  patternLoading.classList.remove('hidden');
  submitBtn.disabled = true;
  answerInput.value = '';
  hideFeedback(answerFeedback);

  try {
    const data = await generateQuestion();
    setState({ currentQuestion: data });
    renderQuestion(data);
  } catch (err) {
    console.error('Failed to load question', err);
    patternLoading.querySelector('.loading-dots').textContent = 'Gagal memuat soal…';
  }
}

// ── Render sequence WITH answer placeholder box ─────────────
function renderQuestion({ sequence, pattern_type }) {
  patternLoading.classList.add('hidden');
  patternContent.classList.remove('hidden');

  const typeLabels = {
    arithmetic: 'Pola Aritmatika',
    geometric: 'Pola Geometri',
    incremental: 'Pola Bertingkat',
    mixed: 'Pola Campuran',
  };
  patternTypeBadge.textContent = typeLabels[pattern_type] || pattern_type;

  // Clear sequence row
  sequenceRow.innerHTML = '';

  // Render each number in sequence
  sequence.forEach((num, index) => {
    const numEl = document.createElement('div');
    numEl.className = 'seq-num';
    numEl.textContent = num;
    numEl.style.animationDelay = `${index * 0.1}s`;
    sequenceRow.appendChild(numEl);

    // Add comma separator (except after last number)
    if (index < sequence.length - 1) {
      const sep = document.createElement('span');
      sep.className = 'seq-sep';
      sep.textContent = ',';
      sequenceRow.appendChild(sep);
    }
  });

  // ➕ TAMBAHKAN KOTAK JAWABAN DENGAN TITIK-TITIK
  const answerBox = document.createElement('div');
  answerBox.className = 'seq-answer-box';
  answerBox.id = 'answerPlaceholder';
  answerBox.setAttribute('aria-label', 'Kotak jawaban yang harus ditebak');
  answerBox.innerHTML = '<span>...</span>';
  sequenceRow.appendChild(answerBox);

  submitBtn.disabled = false;
  answerInput.focus();
}

// ── Submit answer ─────────────────────────────
async function handleSubmit() {
  if (submitLocked) return;
  const answer = answerInput.value.trim();
  if (answer === '' || isNaN(Number(answer))) {
    answerInput.focus();
    return;
  }

  submitLocked = true;
  submitBtn.disabled = true;
  answerInput.disabled = true;

  try {
    const result = await submitAnswer(Number(answer));

    const {
      correct,
      score,
      lives,
      correct_streak,
      wrong_streak,
      difficulty,
      correct_answer,
      game_over,
    } = result;

    setState({ score, lives, correctStreak: correct_streak, wrongStreak: wrong_streak, difficulty });

    // Update score display
    animateScore(scoreDisplay, correct);
    scoreDisplay.textContent = score;

    // Update lives
    updateLives(liveHearts, lives);

    // Show feedback
    showFeedback(answerFeedback, correct, correct ? null : correct_answer);

    // Streak text
    setStreakText(streakText, correct_streak, wrong_streak);

    // Diff badge (may have changed due to difficulty engine)
    diffBadge.textContent = difficulty.toUpperCase() === 'EASY' ? 'MUDAH' : difficulty.toUpperCase() === 'MEDIUM' ? 'SEDANG' : 'SULIT';
    diffBadge.className = `diff-badge ${difficulty}`;

    // Pattern display visual feedback
    const patternDisplay = $('#patternDisplay');
    patternDisplay.classList.remove('correct', 'wrong');
    patternDisplay.classList.add(correct ? 'correct' : 'wrong');

    // Update answer box visual
    const answerBox = document.getElementById('answerPlaceholder');
    if (answerBox) {
      answerBox.classList.remove('correct', 'wrong');
      answerBox.classList.add(correct ? 'correct' : 'wrong');
      if (correct) {
        answerBox.innerHTML = `<span>${correct_answer}</span>`;
      }
    }

    if (game_over || lives <= 0) {
      clearInterval(timerInterval);
      setTimeout(() => triggerGameOver('lives', score), 1200);
      return;
    }

    // Load next after brief delay
    feedbackTimeout = setTimeout(async () => {
      patternDisplay.classList.remove('correct', 'wrong');
      submitLocked = false;
      answerInput.disabled = false;
      await loadQuestion();
    }, 1400);

  } catch (err) {
    console.error('Submit error', err);
    submitLocked = false;
    submitBtn.disabled = false;
    answerInput.disabled = false;
  }
}

submitBtn.addEventListener('click', handleSubmit);
answerInput.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') handleSubmit();
});

// ── Game Over ─────────────────────────────────
async function triggerGameOver(reason, finalScore = null) {
  clearInterval(timerInterval);
  setState({ isGameRunning: false });

  const { score, difficulty } = getState();
  const displayScore = finalScore ?? score;

  try {
    await endGame();
  } catch (_) {}

  // Show modal
  modalScore.textContent = displayScore;
  const diffLabel = difficulty.toUpperCase() === 'EASY' ? 'MUDAH' : difficulty.toUpperCase() === 'MEDIUM' ? 'SEDANG' : 'SULIT';
  modalDifficulty.textContent = `${diffLabel} · ${reason === 'time' ? 'WAKTU HABIS' : 'NYAWA HABIS'}`;
  modalIcon.textContent = reason === 'time' ? '⏰' : '💀';
  modalTitle.textContent = reason === 'time' ? 'WAKTU HABIS' : 'GAME OVER';
  gameOverModal.classList.remove('hidden');
}

// ── Modal buttons ─────────────────────────────
$('#exitBtn')?.addEventListener('click', () => {
  clearInterval(timerInterval);
  window.location.href = '/menu.html';
});

$('#playAgainBtn')?.addEventListener('click', () => {
  gameOverModal.classList.add('hidden');
  init();
});

$('#menuBtn')?.addEventListener('click', () => {
  window.location.href = '/menu.html';
});

// ── Start ─────────────────────────────────────
init();