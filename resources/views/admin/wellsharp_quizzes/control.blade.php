@extends('layouts.admin-dashboard')

@section('title', 'WellSharp Control Panel')

@section('content')
    <div class="page-header">
        <h1 class="page-title">WellSharp Control Panel</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.wellsharp_quizzes.index') }}">WellSharp Quizzes</a></li>
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

    <!-- Quiz Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0 text-primary"><i class="fas fa-bolt me-2"></i>{{ $quiz->name }}</h3>
                        <p class="text-muted mb-0">Status: <span id="quiz-status-badge" class="badge bg-secondary">Loading...</span>
                            <span class="ms-2 text-muted small" id="question-counter"></span>
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <button id="btn-next-question" class="btn btn-warning shadow-sm" style="display:none;" onclick="nextQuestion()">
                            <i class="fas fa-step-forward me-2"></i>Show Next Question
                        </button>
                        <button id="btn-skip-question" class="btn btn-secondary shadow-sm" style="display:none;" onclick="skipQuestion()">
                            <i class="fas fa-forward me-2"></i>Skip to Leaderboard
                        </button>
                        <a href="{{ route('wellsharp.present', $quiz) }}" target="_blank" class="btn btn-outline-info shadow-sm">
                            <i class="fas fa-tv me-2"></i>Open Presentation
                        </a>
                        <form action="{{ route('admin.wellsharp_quizzes.clear', $quiz) }}" method="POST" style="display:inline;" onsubmit="return confirm('Clear all session data including participants and scores?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger shadow-sm">
                                <i class="fas fa-trash me-2"></i>Clear Session
                            </button>
                        </form>
                        <a href="{{ route('admin.wellsharp_quizzes.show', $quiz) }}" class="btn btn-outline-secondary shadow-sm">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Participant -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add Participant</h5>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" id="participant-name" class="form-control" placeholder="Enter participant name..." onkeypress="if(event.key==='Enter') addParticipant()">
                        <button class="btn btn-primary" type="button" onclick="addParticipant()">
                            <i class="fas fa-plus me-1"></i> Add
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Current Question Preview</h5>
                </div>
                <div class="card-body" id="question-preview">
                    <p class="text-muted mb-0">No question active</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Participants + Scoring -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list-ol me-2"></i>Participants & Scoring (<span id="participant-count">0</span>)</h5>
                    <div class="spinner-grow spinner-grow-sm text-light" role="status" id="polling-indicator">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="60" class="text-center">Rank</th>
                                    <th>Participant</th>
                                    <th width="120" class="text-center">Score</th>
                                    <th width="280" class="text-center">Award Points</th>
                                    <th width="60" class="text-center">Remove</th>
                                </tr>
                            </thead>
                            <tbody id="leaderboard-body">
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <p>No participants yet. Add participants above.</p>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const quizId = {{ $quiz->id }};
    const stateUrl = `{{ route('admin.wellsharp_quizzes.state', $quiz) }}`;
    const nextQuestionUrl = `{{ route('admin.wellsharp_quizzes.next-question', $quiz) }}`;
    const addParticipantUrl = `{{ route('admin.wellsharp_quizzes.add-participant', $quiz) }}`;
    const addScoreUrl = `{{ route('admin.wellsharp_quizzes.add-score', $quiz) }}`;
    const removeParticipantBaseUrl = `{{ url('admin/wellsharp_quizzes/' . $quiz->id . '/remove-participant') }}`;
    const skipQuestionUrl = `{{ route('admin.wellsharp_quizzes.skip-question', $quiz) }}`;
    const csrfToken = '{{ csrf_token() }}';

    let currentState = 'lobby';

    function fetchState() {
        fetch(stateUrl)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    currentState = data.state.state;
                    updateUI(data.state, data.leaderboard);
                }
            })
            .catch(err => console.error(err));
    }

    function updateUI(stateData, leaderboardData) {
        const badge = document.getElementById('quiz-status-badge');
        const btnNext = document.getElementById('btn-next-question');
        const btnSkip = document.getElementById('btn-skip-question');
        const counter = document.getElementById('question-counter');
        const preview = document.getElementById('question-preview');

        renderLeaderboard(leaderboardData);

        // Update question counter
        if (stateData.total_questions) {
            counter.innerText = `(${stateData.current_question || 0} / ${stateData.total_questions} questions)`;
        }

        if (stateData.state === 'lobby') {
            badge.className = 'badge bg-secondary';
            badge.innerText = 'LOBBY';
            btnNext.style.display = 'block';
            btnSkip.style.display = 'none';
            btnNext.innerHTML = '<i class="fas fa-play me-2"></i>Start Quiz';
            preview.innerHTML = '<p class="text-muted mb-0">No question active — click Start Quiz to begin</p>';
        }
        else if (stateData.state === 'leaderboard') {
            badge.className = 'badge bg-info';
            badge.innerText = 'LEADERBOARD (Showing on Presentation)';
            btnNext.style.display = 'block';
            btnSkip.style.display = 'none';
            btnNext.innerHTML = '<i class="fas fa-step-forward me-2"></i>Show Next Question';
            preview.innerHTML = '<p class="text-info mb-0"><i class="fas fa-trophy me-2"></i>Leaderboard is showing on the presentation screen</p>';
        }
        else if (stateData.state === 'finished') {
            badge.className = 'badge bg-success';
            badge.innerText = 'FINISHED';
            btnNext.style.display = 'none';
            btnSkip.style.display = 'none';
            preview.innerHTML = '<p class="text-success mb-0"><i class="fas fa-check-circle me-2"></i>Quiz is finished! Final leaderboard is showing.</p>';
        }
        else if (stateData.state === 'question') {
            badge.className = 'badge bg-danger';
            badge.innerText = 'QUESTION IN PROGRESS';
            btnNext.style.display = 'none';
            btnSkip.style.display = 'block';

            if (stateData.question_data) {
                const q = stateData.question_data;
                preview.innerHTML = `
                    <div class="mb-2"><strong>Q${stateData.current_question}:</strong> ${q.question}</div>
                    <div class="row g-2">
                        <div class="col-6"><span class="badge bg-danger px-3 py-2 w-100 text-start">A) ${q.options.a}</span></div>
                        <div class="col-6"><span class="badge bg-primary px-3 py-2 w-100 text-start">B) ${q.options.b}</span></div>
                        <div class="col-6"><span class="badge bg-warning text-dark px-3 py-2 w-100 text-start">C) ${q.options.c}</span></div>
                        <div class="col-6"><span class="badge bg-success px-3 py-2 w-100 text-start">D) ${q.options.d}</span></div>
                    </div>
                    <p class="text-danger mt-2 mb-0 small"><i class="fas fa-info-circle me-1"></i>Award points to a participant below to show the leaderboard on the presentation screen</p>
                `;
            }
        }
    }

    function renderLeaderboard(leaderboard) {
        const tbody = document.getElementById('leaderboard-body');
        document.getElementById('participant-count').innerText = leaderboard.length;

        if (leaderboard.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">No participants yet. Add participants above.</td></tr>`;
            return;
        }

        let html = '';
        leaderboard.forEach(p => {
            let rankBadge = p.rank;
            if (p.rank === 1) rankBadge = `<i class="fas fa-crown text-warning fa-lg"></i>`;
            else if (p.rank === 2) rankBadge = `<i class="fas fa-medal text-secondary fa-lg"></i>`;
            else if (p.rank === 3) rankBadge = `<i class="fas fa-award text-danger fa-lg"></i>`;

            html += `<tr>
                <td class="text-center fw-bold align-middle fs-5">${rankBadge}</td>
                <td class="align-middle">
                    <div class="fw-bold fs-5">${p.name}</div>
                </td>
                <td class="text-center align-middle">
                    <span class="badge bg-primary fs-5 px-3 rounded-pill">${p.score}</span>
                </td>
                <td class="text-center align-middle">
                    <button class="btn btn-success btn-sm me-1" onclick="awardPoints('${p.participant_id}', 10)">
                        <i class="fas fa-plus me-1"></i>10
                    </button>
                    <button class="btn btn-primary btn-sm me-1" onclick="awardPoints('${p.participant_id}', 20)">
                        <i class="fas fa-plus me-1"></i>20
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="awardPoints('${p.participant_id}', -5)">
                        <i class="fas fa-minus me-1"></i>5
                    </button>
                </td>
                <td class="text-center align-middle">
                    <button class="btn btn-outline-danger btn-sm" onclick="removeParticipant('${p.participant_id}', '${p.name}')" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>`;
        });
        tbody.innerHTML = html;
    }

    function nextQuestion() {
        const btn = document.getElementById('btn-next-question');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';

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
            fetchState();
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Failed. Retry?';
        });
    }

    function skipQuestion() {
        const btn = document.getElementById('btn-skip-question');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Skipping...';

        fetch(skipQuestionUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            fetchState();
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-forward me-2"></i>Skip to Leaderboard';
        });
    }

    function addParticipant() {
        const input = document.getElementById('participant-name');
        const name = input.value.trim();
        if (!name) return;

        input.disabled = true;

        fetch(addParticipantUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name: name })
        })
        .then(res => res.json())
        .then(data => {
            input.disabled = false;
            if (data.status === 'success') {
                input.value = '';
                fetchState();
            } else {
                Swal.fire('Error', data.message || 'Failed to add participant', 'error');
            }
        })
        .catch(err => {
            input.disabled = false;
            console.error(err);
            Swal.fire('Error', 'Network error', 'error');
        });
    }

    function removeParticipant(participantId, name) {
        if (!confirm(`Remove ${name}?`)) return;

        fetch(`${removeParticipantBaseUrl}/${participantId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            fetchState();
        })
        .catch(err => console.error(err));
    }

    function awardPoints(participantId, points) {
        fetch(addScoreUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                participant_id: participantId,
                points: points
            })
        })
        .then(res => res.json())
        .then(data => {
            fetchState();
        })
        .catch(err => console.error(err));
    }

    // Start polling
    fetchState();
    setInterval(fetchState, 1500);
</script>
@endsection
