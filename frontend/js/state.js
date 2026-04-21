// js/state.js
// Simple centralized state for the game

const state = {
  token: null,
  user: null,
  score: 0,
  lives: 3,
  difficulty: 'easy',
  correctStreak: 0,
  wrongStreak: 0,
  timerSeconds: 60,
  isGameRunning: false,
  currentQuestion: null,
};

const listeners = {};

export function getState() {
  return { ...state };
}

export function setState(patch) {
  Object.assign(state, patch);
  // Notify subscribers
  Object.keys(patch).forEach(key => {
    if (listeners[key]) {
      listeners[key].forEach(fn => fn(state[key], state));
    }
  });
}

export function subscribe(key, fn) {
  if (!listeners[key]) listeners[key] = [];
  listeners[key].push(fn);
}

export function resetGameState() {
  setState({
    score: 0,
    lives: 3,
    correctStreak: 0,
    wrongStreak: 0,
    timerSeconds: 60,
    isGameRunning: false,
    currentQuestion: null,
  });
}
