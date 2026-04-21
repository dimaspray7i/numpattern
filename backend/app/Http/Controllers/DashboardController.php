<?php

namespace App\Http\Controllers;

use App\Models\Score;
use App\Models\User;
use App\Models\GameSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Set timezone ke Asia/Jakarta
        date_default_timezone_set('Asia/Jakarta');
        Carbon::setLocale('id');
    }

    public function index(Request $request)
    {
        $difficulty = $request->query('difficulty');
        $validDiffs = ['easy', 'medium', 'hard'];

        // ============================================================
        // LEADERBOARD - Best score per user (tidak double)
        // ============================================================
        $query = Score::select(
                'scores.user_id',
                'users.name as user_name',
                'users.email as user_email',
                DB::raw('MAX(scores.score) as score'),
                DB::raw('MAX(scores.difficulty) as difficulty'),
                DB::raw('MAX(scores.created_at) as created_at')
            )
            ->join('users', 'users.id', '=', 'scores.user_id')
            ->groupBy('scores.user_id', 'users.name', 'users.email')
            ->orderByDesc('score')
            ->orderBy('created_at', 'asc');

        if ($difficulty && in_array($difficulty, $validDiffs)) {
            $query->where('scores.difficulty', $difficulty);
        }

        $leaderboard = $query->paginate(20)->withQueryString();

        // ============================================================
        // ACTIVE GAMES - User yang sedang bermain
        // ============================================================
        $activeGames = GameSession::with('user')
            ->where('lives', '>', 0)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->unique('user_id')
            ->take(10)
            ->map(function ($game) {
                $game->last_active_time = Carbon::parse($game->updated_at)->format('H:i:s');
                return $game;
            });

        // ============================================================
        // STATISTICS
        // ============================================================
        $stats = [
            'total_users' => User::count(),
            'total_games' => Score::count(),
            'top_score' => Score::max('score') ?? 0,
            'avg_score' => (int) (Score::avg('score') ?? 0),
            'active_players' => GameSession::where('lives', '>', 0)->distinct('user_id')->count('user_id'),
        ];

        // ============================================================
        // RECENT SCORES - Skor terbaru (unique per user)
        // ============================================================
        $recentScores = Score::with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('user_id')
            ->take(10)
            ->map(function ($score) {
                $score->formatted_time = Carbon::parse($score->created_at)->format('H:i:s d/m/Y');
                return $score;
            });

        // ============================================================
        // CURRENT TIME - Waktu server sekarang
        // ============================================================
        $current_time = Carbon::now()->format('H:i:s d/m/Y');

        // ============================================================
        // RETURN VIEW
        // ============================================================
        return view('dashboard', [
            'leaderboard' => $leaderboard,
            'stats' => $stats,
            'difficulty' => $difficulty,
            'activeGames' => $activeGames,
            'recentScores' => $recentScores,
            'current_time' => $current_time,  // <-- PENTING: variabel ini
        ]);
    }

    /**
     * API endpoint untuk realtime data (AJAX polling)
     */
    public function realtimeData(Request $request)
    {
        // Set timezone
        date_default_timezone_set('Asia/Jakarta');
        
        // Active games
        $activeGames = GameSession::with('user')
            ->where('lives', '>', 0)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->unique('user_id')
            ->take(10)
            ->values()
            ->map(function ($game) {
                return [
                    'player' => $game->user->name,
                    'score' => $game->current_score,
                    'lives' => $game->lives,
                    'difficulty' => $game->difficulty,
                    'last_active' => Carbon::parse($game->updated_at)->format('H:i:s'),
                ];
            });

        // Recent scores
        $recentScores = Score::with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('user_id')
            ->take(10)
            ->values()
            ->map(function ($score) {
                return [
                    'player' => $score->user->name,
                    'score' => $score->score,
                    'difficulty' => $score->difficulty,
                    'time' => Carbon::parse($score->created_at)->format('H:i:s d/m/Y'),
                ];
            });

        // Stats
        $stats = [
            'active_players' => GameSession::where('lives', '>', 0)->distinct('user_id')->count('user_id'),
            'total_games_today' => Score::whereDate('created_at', Carbon::today())->count(),
            'highest_score_today' => Score::whereDate('created_at', Carbon::today())->max('score') ?? 0,
        ];

        return response()->json([
            'success' => true,
            'active_games' => $activeGames,
            'recent_scores' => $recentScores,
            'stats' => $stats,
            'server_time' => Carbon::now()->format('H:i:s d/m/Y'),
        ]);
    }
}