<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameSession;
use App\Models\Score;
use App\Services\PatternService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function __construct(protected PatternService $patternService) {}

    // ─── POST /api/start-session ─────────────────
    /**
     * Create (or reset) a game session for the current user.
     */
    public function startSession(Request $request)
    {
        $data = $request->validate([
            'difficulty' => ['required', 'in:easy,medium,hard'],
        ]);

        $session = GameSession::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'current_score'  => 0,
                'lives'          => 3,
                'correct_streak' => 0,
                'wrong_streak'   => 0,
                'difficulty'     => $data['difficulty'],
                'current_answer' => null,
            ]
        );

        return response()->json([
            'message'    => 'Session started.',
            'difficulty' => $session->difficulty,
            'lives'      => $session->lives,
            'score'      => $session->current_score,
        ]);
    }

    // ─── GET /api/generate-question ──────────────
    /**
     * Generate a pattern question and store answer server-side.
     * Answer is NEVER sent to the frontend.
     */
    public function generateQuestion(Request $request)
    {
        $user    = $request->user();
        $session = GameSession::where('user_id', $user->id)->firstOrFail();

        ['sequence' => $sequence, 'answer' => $answer, 'type' => $type]
            = $this->patternService->generate($session->difficulty);

        // Store hashed answer server-side only
        $session->current_answer = $answer;
        $session->save();

        return response()->json([
            'sequence'     => $sequence,
            'pattern_type' => $type,
            // answer is intentionally omitted
        ]);
    }

    // ─── POST /api/submit-answer ─────────────────
    /**
     * Validate answer, update score/lives/streak, apply difficulty engine.
     */
    public function submitAnswer(Request $request)
    {
        $data = $request->validate([
            'answer' => ['required', 'numeric'],
        ]);

        $user    = $request->user();
        $session = GameSession::where('user_id', $user->id)->lockForUpdate()->firstOrFail();

        if ($session->current_answer === null) {
            return response()->json(['message' => 'No active question.'], 422);
        }

        $correct        = intval($data['answer']) === intval($session->current_answer);
        $correctAnswer  = intval($session->current_answer);

        if ($correct) {
            $session->current_score  += 10;
            $session->correct_streak += 1;
            $session->wrong_streak    = 0;
        } else {
            $session->current_score  = max(0, $session->current_score - 5);
            $session->wrong_streak   += 1;
            $session->correct_streak  = 0;
            $session->lives          -= 1;
        }

        // Difficulty engine
        $session->difficulty = $this->patternService->adjustDifficulty(
            $session->difficulty,
            $session->correct_streak,
            $session->wrong_streak
        );

        $session->current_answer = null; // invalidate after use (anti-cheat)
        $session->save();

        $gameOver = $session->lives <= 0;

        if ($gameOver) {
            $this->saveScore($user->id, $session->current_score, $session->difficulty);
        }

        return response()->json([
            'correct'        => $correct,
            'correct_answer' => $correct ? null : $correctAnswer,
            'score'          => $session->current_score,
            'lives'          => $session->lives,
            'correct_streak' => $session->correct_streak,
            'wrong_streak'   => $session->wrong_streak,
            'difficulty'     => $session->difficulty,
            'game_over'      => $gameOver,
        ]);
    }

    // ─── POST /api/end-game ───────────────────────
    /**
     * Force-end game (time up or manual exit). Save score.
     */
    public function endGame(Request $request)
    {
        $user    = $request->user();
        $session = GameSession::where('user_id', $user->id)->first();

        if (!$session) {
            return response()->json(['message' => 'No active session.'], 404);
        }

        $this->saveScore($user->id, $session->current_score, $session->difficulty);

        $session->current_answer = null;
        $session->save();

        return response()->json([
            'message' => 'Game ended.',
            'score'   => $session->current_score,
        ]);
    }

    // ─── GET /api/get-score ───────────────────────
    public function getScore(Request $request)
    {
        $session = GameSession::where('user_id', $request->user()->id)->first();

        return response()->json([
            'score'      => $session?->current_score ?? 0,
            'lives'      => $session?->lives ?? 3,
            'difficulty' => $session?->difficulty ?? 'easy',
        ]);
    }

    // ─── GET /api/leaderboard ─────────────────────
    public function leaderboard(Request $request)
    {
        $difficulty = $request->query('difficulty');

        $query = Score::select('scores.*', 'users.name as user_name')
            ->join('users', 'users.id', '=', 'scores.user_id')
            ->orderByDesc('scores.score')
            ->limit(20);

        if ($difficulty && in_array($difficulty, ['easy', 'medium', 'hard'])) {
            $query->where('scores.difficulty', $difficulty);
        }

        // Dedup: best score per user per difficulty
        $data = $query->get()->unique(fn($r) => $r->user_id . $r->difficulty)->values();

        return response()->json(['data' => $data]);
    }

    // ─── GET /api/stats ───────────────────────────
    public function stats(Request $request)
    {
        $userId = $request->user()->id;

        $best        = Score::where('user_id', $userId)->max('score');
        $gamesPlayed = Score::where('user_id', $userId)->count();

        // Global rank
        $rank = Score::select('user_id', DB::raw('MAX(score) as best'))
            ->groupBy('user_id')
            ->orderByDesc('best')
            ->get()
            ->pluck('user_id')
            ->search($userId);

        $rank = $rank !== false ? $rank + 1 : null;

        return response()->json([
            'best_score'   => $best,
            'games_played' => $gamesPlayed,
            'rank'         => $rank,
        ]);
    }

    // ─── PRIVATE ─────────────────────────────────
    private function saveScore(int $userId, int $score, string $difficulty): void
    {
        Score::create([
            'user_id'    => $userId,
            'score'      => $score,
            'difficulty' => $difficulty,
        ]);
    }
}
