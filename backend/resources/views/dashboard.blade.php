<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>NumPattern — Admin Dashboard Realtime</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #0a0a0b; --surface: #111114; --surface-2: #18181c;
      --border: #2a2a30; --accent: #f5a623; --accent-green: #39d98a;
      --accent-blue: #4a9eff; --accent-2: #ff5f3d; --danger: #ff3d3d;
      --text: #f0ede8; --text-muted: #7a7880; --text-dim: #3d3c44;
      --font-display: 'Syne', sans-serif; --font-mono: 'DM Mono', monospace;
      --radius: 6px; --radius-lg: 12px;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: var(--font-display); background: var(--bg); color: var(--text); min-height: 100vh; }

    .dash-layout { display: grid; grid-template-columns: 220px 1fr; min-height: 100vh; }

    .sidebar {
      background: var(--surface);
      border-right: 1px solid var(--border);
      padding: 28px 20px;
      display: flex; flex-direction: column; gap: 32px;
    }
    .sidebar-brand { font-size: 1.4rem; font-weight: 800; letter-spacing: -0.04em; }
    .sidebar-brand span { color: var(--accent); }
    .sidebar-label { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.2em; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; }
    .sidebar-nav { display: flex; flex-direction: column; gap: 4px; }
    .nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 12px; border-radius: var(--radius);
      color: var(--text-muted); font-size: 0.88rem; font-weight: 700;
      text-decoration: none; transition: all 0.15s;
    }
    .nav-item.active, .nav-item:hover { background: rgba(245,166,35,0.08); color: var(--accent); }
    .nav-icon { font-size: 1rem; width: 20px; text-align: center; }

    .dash-main { display: flex; flex-direction: column; overflow: hidden; }

    .topbar {
      display: flex; align-items: center; justify-content: space-between;
      padding: 20px 32px; border-bottom: 1px solid var(--border);
    }
    .topbar-title { font-size: 1.3rem; font-weight: 800; letter-spacing: -0.02em; }
    .topbar-sub { font-size: 0.8rem; color: var(--text-muted); font-family: var(--font-mono); }
    .topbar-right { display: flex; align-items: center; gap: 12px; }
    .admin-badge {
      background: rgba(245,166,35,0.1); border: 1px solid var(--accent);
      color: var(--accent); border-radius: 100px; font-size: 0.7rem;
      font-family: var(--font-mono); letter-spacing: 0.1em; padding: 4px 12px;
    }
    .realtime-badge {
      background: rgba(57,217,138,0.1);
      border: 1px solid var(--accent-green);
      color: var(--accent-green);
      border-radius: 100px;
      font-size: 0.7rem;
      padding: 4px 12px;
      margin-left: 10px;
    }
    .live-indicator {
      display: inline-block;
      width: 8px;
      height: 8px;
      background: #39d98a;
      border-radius: 50%;
      animation: pulse 1.5s infinite;
      margin-right: 6px;
    }
    @keyframes pulse {
      0% { opacity: 1; transform: scale(1); }
      100% { opacity: 0; transform: scale(1.5); }
    }

    .dash-content { padding: 28px 32px; flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 28px; }

    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .stat-card {
      background: var(--surface); border: 1px solid var(--border);
      border-radius: var(--radius-lg); padding: 20px;
    }
    .sc-label { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.15em; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px; }
    .sc-value { font-family: var(--font-mono); font-size: 2rem; font-weight: 500; color: var(--accent); }
    .sc-sub { font-size: 0.72rem; color: var(--text-muted); font-family: var(--font-mono); margin-top: 4px; }

    .live-section {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      overflow: hidden;
    }
    .section-header {
      padding: 16px 20px;
      border-bottom: 1px solid var(--border);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .section-title { font-weight: 700; font-size: 0.9rem; }

    .filter-bar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .filter-label { font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em; color: var(--text-muted); text-transform: uppercase; }
    .filter-btns { display: flex; gap: 6px; }
    .filter-btn {
      background: var(--surface); border: 1px solid var(--border);
      border-radius: var(--radius); color: var(--text-muted);
      font-family: var(--font-display); font-size: 0.78rem; font-weight: 700;
      letter-spacing: 0.06em; padding: 7px 16px; cursor: pointer; text-decoration: none;
      transition: all 0.15s;
    }
    .filter-btn:hover { color: var(--text); border-color: var(--text-muted); }
    .filter-btn.active { background: rgba(245,166,35,0.1); border-color: var(--accent); color: var(--accent); }

    .table-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; }
    .table-head { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--border); }
    .table-title { font-weight: 700; font-size: 0.9rem; }
    .table-count { font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted); }
    table { width: 100%; border-collapse: collapse; }
    thead th {
      text-align: left; padding: 12px 20px; font-size: 0.65rem;
      font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase;
      color: var(--text-muted); border-bottom: 1px solid var(--border);
    }
    tbody tr { border-bottom: 1px solid rgba(42,42,48,0.5); transition: background 0.15s; }
    tbody tr:hover { background: var(--surface-2); }
    td { padding: 12px 20px; font-size: 0.88rem; }
    .td-rank { font-family: var(--font-mono); color: var(--text-muted); font-size: 0.8rem; }
    .td-name { font-weight: 700; }
    .td-score { font-family: var(--font-mono); font-size: 1rem; color: var(--accent); font-weight: 500; }
    .diff-pill {
      display: inline-block; padding: 2px 10px; border-radius: 100px;
      font-family: var(--font-mono); font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
    }
    .diff-pill.easy { background: rgba(57,217,138,0.1); color: #39d98a; }
    .diff-pill.medium { background: rgba(74,158,255,0.1); color: #4a9eff; }
    .diff-pill.hard { background: rgba(255,95,61,0.1); color: #ff5f3d; }
    .medal { font-size: 1rem; }

    .pagination { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 20px; }
    .page-btn {
      background: var(--surface-2); border: 1px solid var(--border);
      border-radius: var(--radius); color: var(--text-muted);
      font-family: var(--font-mono); font-size: 0.8rem; padding: 6px 12px;
      cursor: pointer; text-decoration: none; transition: all 0.15s;
    }
    .page-btn:hover, .page-btn.active { background: rgba(245,166,35,0.1); border-color: var(--accent); color: var(--accent); }
    .page-btn.disabled { opacity: 0.3; pointer-events: none; }

    .table-empty { padding: 48px; text-align: center; color: var(--text-muted); font-family: var(--font-mono); font-size: 0.85rem; }
    .refresh-note { font-size: 0.7rem; color: var(--text-muted); font-family: var(--font-mono); }

    @media (max-width: 900px) {
      .dash-layout { grid-template-columns: 1fr; }
      .sidebar { display: none; }
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
  </style>
</head>
<body>
<div class="dash-layout">

  <aside class="sidebar">
    <div class="sidebar-brand">Num<span>Pattern</span></div>
    <nav>
      <div class="sidebar-label">Navigation</div>
      <div class="sidebar-nav">
        <a class="nav-item active" href="/dashboard">
          <span class="nav-icon">🏆</span> Leaderboard
        </a>
      </div>
    </nav>
  </aside>

  <div class="dash-main">

    <div class="topbar">
      <div>
        <div class="topbar-title">Admin Dashboard</div>
          <div class="topbar-sub">
              Server Time: {{ $current_time }}
              <span class="realtime-badge"><span class="live-indicator"></span> LIVE</span>
          </div>
      </div>
      <div class="topbar-right">
        <span class="admin-badge">ADMIN</span>
      </div>
    </div>

    <div class="dash-content">

      <!-- STATS -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="sc-label">Total Players</div>
          <div class="sc-value">{{ number_format($stats['total_users']) }}</div>
        </div>
        <div class="stat-card">
          <div class="sc-label">Games Played</div>
          <div class="sc-value">{{ number_format($stats['total_games']) }}</div>
        </div>
        <div class="stat-card">
          <div class="sc-label">Top Score</div>
          <div class="sc-value">{{ number_format($stats['top_score']) }}</div>
        </div>
        <div class="stat-card">
          <div class="sc-label">Active Players</div>
          <div class="sc-value" id="activePlayersCount">{{ $stats['active_players'] }}</div>
        </div>
      </div>

      <!-- LIVE GAMES SECTION -->
      <div class="live-section">
        <div class="section-header">
          <span class="section-title"><span class="live-indicator"></span> LIVE GAMES</span>
          <span class="refresh-note">Auto-refresh every 5 seconds</span>
        </div>
        <div id="activeGamesContainer">
          @if($activeGames->count() > 0)
          <table>
            <thead>
              <tr><th>Player</th><th>Score</th><th>Lives</th><th>Difficulty</th><th>Last Active</th></tr>
            </thead>
            <tbody id="activeGamesTable">
              @foreach($activeGames as $game)
              <tr>
                <td class="td-name">{{ $game->user->name }}</td>
                <td class="td-score">{{ $game->current_score }}</td>
                <td>{{ $game->lives }} ❤️</td>
                <td><span class="diff-pill {{ $game->difficulty }}">{{ $game->difficulty }}</span></td>
                <td class="refresh-note">{{ $game->last_active_time }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
          @else
          <div class="table-empty">No active games at the moment</div>
          @endif
        </div>
      </div>

      <!-- RECENT SCORES SECTION -->
      <div class="live-section">
        <div class="section-header">
          <span class="section-title">📊 RECENT SCORES</span>
        </div>
        <div id="recentScoresContainer">
          @if($recentScores->count() > 0)
          <table>
            <thead>
              <tr><th>Player</th><th>Score</th><th>Difficulty</th><th>Time</th></tr>
            </thead>
            <tbody id="recentScoresTable">
              @foreach($recentScores as $score)
              <tr>
                <td class="td-name">{{ $score->user->name }}</td>
                <td class="td-score">{{ $score->score }}</td>
                <td><span class="diff-pill {{ $score->difficulty }}">{{ $score->difficulty }}</span></td>
                <td class="refresh-note">{{ $score->formatted_time }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
          @else
          <div class="table-empty">No scores recorded yet</div>
          @endif
        </div>
      </div>

      <!-- FILTER -->
      <div class="filter-bar">
        <span class="filter-label">Filter by difficulty:</span>
        <div class="filter-btns">
          <a href="/dashboard" class="filter-btn {{ !$difficulty ? 'active' : '' }}">All</a>
          <a href="/dashboard?difficulty=easy" class="filter-btn {{ $difficulty === 'easy' ? 'active' : '' }}">Easy</a>
          <a href="/dashboard?difficulty=medium" class="filter-btn {{ $difficulty === 'medium' ? 'active' : '' }}">Medium</a>
          <a href="/dashboard?difficulty=hard" class="filter-btn {{ $difficulty === 'hard' ? 'active' : '' }}">Hard</a>
        </div>
      </div>

      <!-- LEADERBOARD TABLE -->
      <div class="table-card">
        <div class="table-head">
          <span class="table-title">🏆 Leaderboard</span>
          <span class="table-count">{{ $leaderboard->total() }} entries</span>
        </div>

        @if($leaderboard->isEmpty())
          <div class="table-empty">No scores recorded yet.</div>
        @else
          <table>
            <thead>
              <tr><th>#</th><th>Player</th><th>Email</th><th>Score</th><th>Difficulty</th><th>Date</th></tr>
            </thead>
            <tbody>
              @foreach($leaderboard as $i => $row)
                @php $rank = $leaderboard->firstItem() + $i; $medals = ['🥇','🥈','🥉']; @endphp
                <tr>
                  <td class="td-rank">@if($rank <= 3)<span class="medal">{{ $medals[$rank-1] }}</span>@else #{{ $rank }} @endif</td>
                  <td class="td-name">{{ $row->user_name }}</td>
                  <td class="refresh-note">{{ $row->user_email }}</td>
                  <td class="td-score">{{ number_format($row->score) }}</td>
                  <td><span class="diff-pill {{ $row->difficulty }}">{{ $row->difficulty }}</span></td>
                  <td class="refresh-note">{{ \Carbon\Carbon::parse($row->created_at)->format('H:i:s d/m/Y') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>

          @if($leaderboard->hasPages())
          <div class="pagination">
            @if($leaderboard->onFirstPage()) <span class="page-btn disabled">← Prev</span>
            @else <a href="{{ $leaderboard->previousPageUrl() }}" class="page-btn">← Prev</a> @endif

            @foreach($leaderboard->getUrlRange(1, $leaderboard->lastPage()) as $page => $url)
              <a href="{{ $url }}" class="page-btn {{ $page == $leaderboard->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach

            @if($leaderboard->hasMorePages()) <a href="{{ $leaderboard->nextPageUrl() }}" class="page-btn">Next →</a>
            @else <span class="page-btn disabled">Next →</span> @endif
          </div>
          @endif
        @endif
      </div>

    </div>
  </div>
</div>

<script>
  function fetchRealtimeData() {
    fetch('/dashboard/realtime')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Update active players count
          const activeCountEl = document.getElementById('activePlayersCount');
          if (activeCountEl) activeCountEl.textContent = data.stats.active_players;
          
          // Update active games table
          const activeTable = document.getElementById('activeGamesTable');
          if (activeTable && data.active_games.length > 0) {
            activeTable.innerHTML = data.active_games.map(game => `
              <tr>
                <td class="td-name">${game.player}</td>
                <td class="td-score">${game.score}</td>
                <td>${game.lives} ❤️</td>
                <td><span class="diff-pill ${game.difficulty}">${game.difficulty}</span></td>
                <td class="refresh-note">${game.last_active}</td>
              </tr>
            `).join('');
          } else if (activeTable && data.active_games.length === 0) {
            activeTable.innerHTML = '<tr><td colspan="5" class="table-empty">No active games</td></tr>';
          }
          
          // Update recent scores table
          const recentTable = document.getElementById('recentScoresTable');
          if (recentTable && data.recent_scores.length > 0) {
            recentTable.innerHTML = data.recent_scores.map(score => `
              <tr>
                <td class="td-name">${score.player}</td>
                <td class="td-score">${score.score}</td>
                <td><span class="diff-pill ${score.difficulty}">${score.difficulty}</span></td>
                <td class="refresh-note">${score.time}</td>
              </tr>
            `).join('');
          }
          
          // Update timestamp
          const serverTimeEl = document.querySelector('.topbar-sub');
          if (serverTimeEl && data.server_time) {
            serverTimeEl.innerHTML = `Server Time: ${data.server_time} <span class="realtime-badge"><span class="live-indicator"></span> LIVE</span>`;
          }
        }
      })
      .catch(error => console.error('Realtime fetch error:', error));
  }
  
  setInterval(fetchRealtimeData, 5000);
  fetchRealtimeData();
</script>
</body>
</html>