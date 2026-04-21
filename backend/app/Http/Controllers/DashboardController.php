<?php

namespace App\Http\Controllers;

use App\Models\Score;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $difficulty = $request->query('difficulty');
        $validDiffs = ['easy', 'medium', 'hard'];

        $query = Score::select(
                'scores.id',
                'scores.score',
                'scores.difficulty',
                'scores.created_at',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->join('users', 'users.id', '=', 'scores.user_id')
            ->orderByDesc('scores.score');

        if ($difficulty && in_array($difficulty, $validDiffs)) {
            $query->where('scores.difficulty', $difficulty);
        }

        $leaderboard = $query->paginate(20)->withQueryString();

        $stats = Cache::remember('dashboard_stats', 60, function () {
            return [
                'total_users'  => User::count(),
                'total_games'  => Score::count(),
                'top_score'    => Score::max('score'),
                'avg_score'    => (int) Score::avg('score'),
            ];
        });

        return view('dashboard', [
            'leaderboard' => $leaderboard,
            'stats'       => $stats,
            'difficulty'  => $difficulty,
        ]);
    }
}
