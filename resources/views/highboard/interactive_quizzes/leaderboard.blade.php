@extends('layouts.highboard-dashboard')

@section('title', 'Interactive Quiz Leaderboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Interactive Quiz Control Panel</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('highboard.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('highboard.interactive_quizzes.index') }}">Interactive Quizzes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Control Panel</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Quiz Header Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0 text-primary"><i class="fas fa-play-circle me-2"></i>{{ $quiz->name }}</h3>
                        <p class="text-muted mb-0">Status: <span id="quiz-status-badge" class="badge bg-secondary">Loading...</span></p>
                    </div>
                    <div class="d-flex gap-2">
                        <button id="btn-next-question" class="btn btn-warning shadow-sm" style="display:none;" onclick="nextQuestion()">
                            <i class="fas fa-step-forward me-2"></i>Show Next Question
                        </button>
                        <form action="{{ route('highboard.interactive_quizzes.leaderboard.clear', $quiz) }}" method="POST" style="display:inline;" onsubmit="return confirm('Clear leaderboard?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger shadow-sm">
                                <i class="fas fa-trash me-2"></i>Clear Session
                            </button>
                        </form>
                        <a href="{{ route('highboard.interactive_quizzes.show', $quiz) }}" class="btn btn-outline-secondary shadow-sm">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Question Fullscreen View (Only visible during question state) -->
    <div class="row mb-4" id="active-question-card" style="display:none;">
        <div class="col-12">
            <div class="card shadow border-info" style="border-width: 2px;">
                <div class="card-body text-center p-5">
                    <h5 class="text-muted text-uppercase fw-bold mb-3">Question <span id="active-q-number">--</span></h5>
                    <h2 class="mb-5 display-6 text-dark" id="active-q-text">Loading question...</h2>
                    
                    <div class="row justify-content-center mb-5">
                        <div class="col-md-5 mb-3 text-start"><div class="p-3 border rounded bg-light" id="opt-a">A) --</div></div>
                        <div class="col-md-5 mb-3 text-start"><div class="p-3 border rounded bg-light" id="opt-b">B) --</div></div>
                        <div class="col-md-5 mb-3 text-start"><div class="p-3 border rounded bg-light" id="opt-c">C) --</div></div>
                        <div class="col-md-5 mb-3 text-start"><div class="p-3 border rounded bg-light" id="opt-d">D) --</div></div>
                    </div>
                    
                    <div class="d-inline-flex align-items-center justify-content-center p-4 bg-danger text-white rounded-circle shadow" style="width: 120px; height: 120px;">
                        <h1 class="mb-0 display-4" id="timer-display">--</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboard View -->
    <div class="row" id="leaderboard-card">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list-ol me-2"></i>Live Leaderboard (<span id="participant-count">0</span>)</h5>
                    <div class="spinner-grow spinner-grow-sm text-light" role="status" id="polling-indicator">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="80" class="text-center">Rank</th>
                                    <th>Participant</th>
                                    <th width="150" class="text-center">Score</th>
                                </tr>
                            </thead>
                            <tbody id="leaderboard-body">
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">
                                        <p>Waiting for participants to join...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const quizId = {{ $quiz->id }};
    const stateUrl = `{{ route('highboard.interactive_quizzes.state', $quiz) }}`;
    const nextQuestionUrl = `{{ route('highboard.interactive_quizzes.next-question', $quiz) }}`;
    const csrfToken = '{{ csrf_token() }}';
    
    let currentStateJSON = null;
    let timerInterval = null;

    function fetchState() {
        fetch(stateUrl)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    updateUI(data.state, data.leaderboard);
                }
            })
            .catch(err => console.error(err));
    }

    function calculateTimeRemaining(startTime, timeLimit) {
        let now = Math.floor(Date.now() / 1000);
        let elapsed = now - startTime;
        let remaining = timeLimit - elapsed;
        return remaining > 0 ? remaining : 0;
    }

    function updateTimerDisplay(remaining) {
        document.getElementById('timer-display').innerText = remaining;
        if(remaining === 0) {
            // Re-fetch state immediately so the backend sets it to leaderboard if time is up
            fetchState();
        }
    }

    function renderLeaderboard(leaderboard) {
        const tbody = document.getElementById('leaderboard-body');
        document.getElementById('participant-count').innerText = leaderboard.length;
        
        if (leaderboard.length === 0) {
            tbody.innerHTML = `<tr><td colspan="3" class="text-center py-4 text-muted">No participants yet.</td></tr>`;
            return;
        }

        let html = '';
        leaderboard.forEach(p => {
            let rankBadge = p.rank;
            if(p.rank === 1) rankBadge = `<i class="fas fa-crown text-warning fa-lg"></i>`;
            else if(p.rank === 2) rankBadge = `<i class="fas fa-medal text-secondary fa-lg"></i>`;
            else if(p.rank === 3) rankBadge = `<i class="fas fa-award text-danger fa-lg"></i>`;

            html += `<tr>
                <td class="text-center fw-bold align-middle fs-5">${rankBadge}</td>
                <td class="align-middle">
                    <div class="fw-bold fs-5">${p.name}</div>
                    <div class="text-muted small">${p.email}</div>
                </td>
                <td class="text-center align-middle">
                    <span class="badge bg-primary fs-5 px-3 rounded-pill">${p.score}</span>
                </td>
            </tr>`;
        });
        tbody.innerHTML = html;
    }

    function updateUI(stateData, leaderboardData) {
        const qCard = document.getElementById('active-question-card');
        const lCard = document.getElementById('leaderboard-card');
        const badge = document.getElementById('quiz-status-badge');
        const btnNext = document.getElementById('btn-next-question');

        renderLeaderboard(leaderboardData);

        if (stateData.state === 'lobby') {
            qCard.style.display = 'none';
            lCard.style.display = 'block';
            badge.className = 'badge bg-secondary';
            badge.innerText = 'LOBBY';
            btnNext.style.display = 'block';
            btnNext.innerHTML = '<i class="fas fa-play me-2"></i>Start Quiz';
        } 
        else if (stateData.state === 'leaderboard') {
            qCard.style.display = 'none';
            lCard.style.display = 'block';
            badge.className = 'badge bg-info';
            badge.innerText = 'POST-QUESTION LEADERBOARD';
            btnNext.style.display = 'block';
            btnNext.innerHTML = '<i class="fas fa-step-forward me-2"></i>Show Next Question';
        }
        else if (stateData.state === 'finished') {
            qCard.style.display = 'none';
            lCard.style.display = 'block';
            badge.className = 'badge bg-success';
            badge.innerText = 'FINISHED';
            btnNext.style.display = 'none';
        }
        else if (stateData.state === 'question') {
            qCard.style.display = 'block';
            lCard.style.display = 'none';
            badge.className = 'badge bg-danger pulse';
            badge.innerText = 'QUESTION IN PROGRESS';
            btnNext.style.display = 'none';

            if (stateData.question_data) {
                const q = stateData.question_data;
                document.getElementById('active-q-number').innerText = stateData.current_question;
                document.getElementById('active-q-text').innerText = q.question;
                document.getElementById('opt-a').innerText = 'A) ' + q.options.a;
                document.getElementById('opt-b').innerText = 'B) ' + q.options.b;
                document.getElementById('opt-c').innerText = 'C) ' + q.options.c;
                document.getElementById('opt-d').innerText = 'D) ' + q.options.d;
            }
            
            // Re-sync timer
            if (timerInterval) clearInterval(timerInterval);
            
            let rem = calculateTimeRemaining(stateData.start_time, stateData.time_limit);
            updateTimerDisplay(rem);
            
            timerInterval = setInterval(() => {
                let r = calculateTimeRemaining(stateData.start_time, stateData.time_limit);
                updateTimerDisplay(r);
                if(r <= 0) clearInterval(timerInterval);
            }, 1000);
        }
    }

    function nextQuestion() {
        const btn = document.getElementById('btn-next-question');
        btn.disabled = true;
        btn.innerHTML = 'Loading...';

        fetch(nextQuestionUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if(data.status === 'success' && data.data.state === 'question') {
                const q = data.data.question_data;
                document.getElementById('active-q-number').innerText = data.data.current_question;
                document.getElementById('active-q-text').innerText = q.question;
                document.getElementById('opt-a').innerText = 'A) ' + q.options.a;
                document.getElementById('opt-b').innerText = 'B) ' + q.options.b;
                document.getElementById('opt-c').innerText = 'C) ' + q.options.c;
                document.getElementById('opt-d').innerText = 'D) ' + q.options.d;
                
                // Immediately fetch state to update UI
                fetchState();
            } else if (data.status === 'success' && data.data.state === 'finished') {
                fetchState();
            } else {
                alert('Finished or Error!');
                fetchState();
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = 'Failed. Retry?';
        });
    }

    // Start polling
    fetchState();
    setInterval(fetchState, 1500);

</script>
<style>
    .pulse { animaton: pulse-animation 2s infinite; }
    @keyframes pulse-animation {
        0% { box-shadow: 0 0 0 0px rgba(220, 53, 69, 0.5); }
        100% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    }
</style>
@endsection