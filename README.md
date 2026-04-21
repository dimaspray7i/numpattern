# 🔢 NumPattern — Number Pattern Game

**Fullstack Web Game | Laravel 12 API + Vanilla JS Frontend**

A competitive number pattern game where players identify arithmetic, geometric, and incremental sequences under time pressure. Features token-based auth, server-side anti-cheat, adaptive difficulty, and an admin leaderboard dashboard.

---

## 📁 Project Structure

```
numpattern/
├── frontend/               # Static HTML/CSS/JS (served by any web server)
│   ├── index.html          # Auth page (Login / Register)
│   ├── menu.html           # Main menu + leaderboard preview
│   ├── game.html           # Game screen
│   ├── css/
│   │   └── style.css       # All styles (dark industrial theme)
│   └── js/
│       ├── main.js         # Auth page controller
│       ├── menu.js         # Menu page controller
│       ├── game.js         # Game page controller
│       ├── api.js          # Centralized API client (fetch wrapper)
│       ├── auth.js         # Token storage helpers
│       ├── state.js        # Simple reactive state manager
│       └── ui.js           # DOM manipulation utilities
│
└── backend/                # Laravel 12 REST API + Admin Dashboard
    ├── app/
    │   ├── Http/Controllers/
    │   │   ├── Api/
    │   │   │   ├── AuthController.php     # Register / Login / Logout
    │   │   │   └── GameController.php     # Game logic, leaderboard, stats
    │   │   └── DashboardController.php    # Admin dashboard
    │   ├── Models/
    │   │   ├── User.php
    │   │   ├── GameSession.php
    │   │   └── Score.php
    │   └── Services/
    │       └── PatternService.php         # Pattern generation + difficulty engine
    ├── database/
    │   ├── migrations/                    # All table migrations
    │   └── seeders/
    │       └── DatabaseSeeder.php         # Demo data seeder
    ├── resources/views/
    │   └── dashboard/index.blade.php      # Admin dashboard view
    ├── routes/
    │   ├── api.php                        # API routes (Sanctum protected)
    │   └── web.php                        # Dashboard web route
    ├── config/
    │   ├── cors.php                       # CORS configuration
    │   └── sanctum.php                    # Sanctum token config
    ├── bootstrap/app.php                  # Laravel 12 app bootstrap
    ├── composer.json
    └── .env.example
```

---

## ⚙️ Requirements

| Tool | Version |
|------|---------|
| PHP | 8.2+ |
| Composer | 2.x |
| MySQL | 8.0+ |
| Node/npm | (not needed — Vanilla JS, no build step) |
| Any static file server | VS Code Live Server, nginx, Apache, etc. |

---

## 🚀 Setup Guide

### 1. Clone & Setup Backend

```bash
# Enter backend directory
cd numpattern/backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2. Configure Database

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=numpattern       # Create this DB first
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create the database in MySQL:

```sql
CREATE DATABASE numpattern CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Run Migrations & Seed

```bash
# Run all migrations
php artisan migrate

# Optional: seed with demo players & scores
php artisan db:seed
```

### 4. Start Laravel Development Server

```bash
php artisan serve
# API running at: http://localhost:8000
```

### 5. Serve the Frontend

**Option A — VS Code Live Server:**
- Install "Live Server" extension
- Right-click `frontend/index.html` → "Open with Live Server"
- Default URL: `http://127.0.0.1:5500`

**Option B — Python:**
```bash
cd numpattern/frontend
python -m http.server 5500
# Visit: http://localhost:5500
```

**Option C — PHP built-in:**
```bash
cd numpattern/frontend
php -S localhost:5500
```

### 6. Configure CORS (if needed)

Edit `backend/config/cors.php` → add your frontend URL to `allowed_origins`:

```php
'allowed_origins' => [
    'http://localhost:5500',
    'http://127.0.0.1:5500',
    // Add your URL here
],
```

### 7. Configure Frontend API URL

Edit `frontend/js/api.js` line 4:

```js
const BASE_URL = 'http://localhost:8000/api'; // Your backend URL
```

---

## 🌐 API Reference

### Authentication

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/register` | No | Create account |
| POST | `/api/login` | No | Get token |
| POST | `/api/logout` | Yes | Revoke token |

**Register body:**
```json
{
  "name": "Alice",
  "email": "alice@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

**Login response:**
```json
{
  "user": { "id": 1, "name": "Alice", "email": "alice@example.com" },
  "token": "1|abc123..."
}
```

---

### Game

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/start-session` | Yes | Start/reset game session |
| GET | `/api/generate-question` | Yes | Get next pattern |
| POST | `/api/submit-answer` | Yes | Submit answer |
| POST | `/api/end-game` | Yes | Force end + save score |
| GET | `/api/get-score` | Yes | Current session score |

**All authenticated requests must include:**
```
Authorization: Bearer {token}
```

**generate-question response:**
```json
{
  "sequence": [2, 4, 6, 8, 10],
  "pattern_type": "arithmetic"
}
```
> ⚠️ Answer is **never** sent to the frontend. Stored server-side only.

**submit-answer body:**
```json
{ "answer": 12 }
```

**submit-answer response:**
```json
{
  "correct": true,
  "correct_answer": null,
  "score": 10,
  "lives": 3,
  "correct_streak": 1,
  "wrong_streak": 0,
  "difficulty": "easy",
  "game_over": false
}
```

---

### Leaderboard & Stats

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/leaderboard` | Yes | Top 20 scores |
| GET | `/api/leaderboard?difficulty=hard` | Yes | Filtered leaderboard |
| GET | `/api/stats` | Yes | Personal best, games, rank |

---

## 🎮 Game Rules

### Pattern Types

| Type | Example | Description |
|------|---------|-------------|
| Arithmetic | 2, 5, 8, 11, **?** | Constant difference (+3) |
| Geometric | 3, 6, 12, 24, **?** | Constant ratio (×2) |
| Incremental | 1, 3, 7, 13, **?** | Differences increase (+2,+4,+6) |
| Mixed | 5, 10, 7, 14, 9, 18, **?** | Two interleaved series (hard only) |

### Scoring
- ✅ Correct answer: **+10 points**
- ❌ Wrong answer: **−5 points** (min 0) + **−1 life**

### Adaptive Difficulty Engine
| Condition | Effect |
|-----------|--------|
| 3 correct in a row | Difficulty increases |
| 2 wrong in a row | Difficulty decreases |

### Game Over Triggers
- 💀 Lives reach 0
- ⏰ 60-second timer expires

---

## 🔒 Security Features

| Feature | Implementation |
|---------|---------------|
| Password hashing | `Hash::make()` (bcrypt) |
| Token auth | Laravel Sanctum bearer tokens |
| Answer anti-cheat | Answers stored server-side only, nullified after use |
| Input validation | Laravel `$request->validate()` on all endpoints |
| CORS | Whitelist-only allowed origins |
| Rate limiting | Throttle middleware on login/register (10/min) |
| SQL injection | Eloquent ORM (parameterized queries) |

---

## 🖥️ Admin Dashboard

Access at: `http://localhost:8000/dashboard`

**Features:**
- 4 live stat cards (players, games, top score, avg score)
- Filterable leaderboard (All / Easy / Medium / Hard)
- Paginated score table with medals (🥇🥈🥉)
- Cached stats (60-second cache) for performance

---

## 🗄️ Database Schema

```sql
users
  id, name, email, password, timestamps

personal_access_tokens
  (Sanctum managed)

game_sessions
  id, user_id (unique), current_score, lives,
  correct_streak, wrong_streak, difficulty,
  current_answer (hidden), timestamps

scores
  id, user_id, score, difficulty, created_at
  INDEXES: score, user_id, (difficulty, score)
```

---

## 🧠 Architecture Notes

### Frontend
- **ES6 Modules** — no bundler needed; each page imports only what it needs
- **Separation of concerns**: `api.js` (HTTP) | `auth.js` (token) | `state.js` (data) | `ui.js` (DOM) | page controllers
- **Auto logout** — any 401 response clears token and redirects to login

### Backend
- **Service layer** — `PatternService` keeps game logic out of the controller
- **Single session per user** — `updateOrCreate` ensures clean game resets
- **Difficulty as state** — stored in `game_sessions`, adjusted after each answer
- **Cache** — dashboard stats cached 60 seconds to reduce DB load

---

## 🐞 Troubleshooting

| Problem | Solution |
|---------|----------|
| CORS error in browser | Add frontend URL to `config/cors.php` `allowed_origins` |
| 401 on all requests | Check `Authorization: Bearer {token}` header; verify token in DB |
| `No active question` error | Call `/start-session` before `/generate-question` |
| Dashboard shows 0 data | Run `php artisan db:seed` for demo data |
| Mixed content error | Use same protocol (both http or both https) for frontend and backend |

---

## 📋 Quick Start Checklist

```
[ ] MySQL database 'numpattern' created
[ ] .env configured with DB credentials
[ ] composer install
[ ] php artisan key:generate
[ ] php artisan migrate
[ ] php artisan db:seed  (optional)
[ ] php artisan serve
[ ] Frontend served at localhost:5500
[ ] BASE_URL in api.js points to localhost:8000
[ ] CORS allows localhost:5500
[ ] Open index.html → Register → Play!
```

---

*Built for LKS (Lomba Kompetensi Siswa) Web Technology — NumPattern v1.0*
