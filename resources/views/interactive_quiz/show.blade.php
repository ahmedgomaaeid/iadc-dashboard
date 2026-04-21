<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $quiz->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/IADC Icon.png') }}">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f3f4f6; overflow-x: hidden; user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; }
        .quiz-container { max-width: 800px; margin: 40px auto; padding: 20px; }
        .card-panel { background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); padding: 40px; margin-bottom: 20px; display: none; text-align: center; }
        .card-panel.active { display: block; animation: slideUp 0.4s ease forwards; }
        
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .score-badge { position: absolute; top: 20px; left: 20px; background: #fff; padding: 10px 20px; border-radius: 30px; font-weight: 700; box-shadow: 0 4px 15px rgba(0,0,0,0.1); color: #B4120D; }
        .user-badge { position: absolute; top: 20px; right: 20px; background: #fff; padding: 10px 20px; border-radius: 30px; font-weight: 600; box-shadow: 0 4px 15px rgba(0,0,0,0.1); color: #374151; }

        /* Loader */
        .spinner { width: 50px; height: 50px; border: 5px solid #e5e7eb; border-top-color: #B4120D; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Question View */
        .timer-circle { width: 100px; height: 100px; border-radius: 50%; background: #ef4444; color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700; margin: 0 auto 30px; box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4); }
        .choice-btn { background: #fff; border: 3px solid #e5e7eb; border-radius: 20px; padding: 28px 24px; text-align: center; font-size: 1.5rem; font-weight: 600; width: 100%; margin-bottom: 18px; cursor: pointer; transition: all 0.25s ease; position: relative; }
        .choice-btn:hover { border-color: #B4120D; background: #fee2e2; }
        .choice-btn.selected { border-color: #B4120D; background: #fca5a5; transform: scale(0.97); }
        .choice-btn .letter { background: #B4120D; color: white; width: 48px; height: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 18px; font-size: 1.3rem; }
        .choice-btn .option-text { font-size: 1.3rem; }
        .q-number-badge { background: linear-gradient(135deg, #B4120D 0%, #d61811 100%); color: white; padding: 8px 24px; border-radius: 30px; font-size: 1.1rem; font-weight: 700; display: inline-block; margin-bottom: 16px; }

        .waiting-text { font-size: 1.3rem; color: #4b5563; font-weight: 600; margin-top: 20px; }

        /* Leaderboard View */
        .rank-row { display: flex; align-items: center; padding: 15px; border-bottom: 1px solid #e5e7eb; }
        .rank-row:last-child { border-bottom: none; }
        .rank-num { font-size: 1.5rem; font-weight: 700; color: #6b7280; width: 50px; text-align: center; }
        .rank-name { font-size: 1.2rem; font-weight: 600; flex: 1; text-align: left; }
        .rank-score { font-size: 1.3rem; font-weight: 700; color: #10b981; }

        .my-rank-banner { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-radius: 15px; padding: 20px; margin-bottom: 30px; font-size: 1.2rem; font-weight: 600; display: flex; justify-content: space-around; }
        .my-rank-banner div { text-align: center; }
        .my-rank-banner span { display: block; font-size: 2rem; }
    </style>
</head>
<body>
    <div class="score-badge"><i class="fas fa-star me-2 text-warning"></i>Score: <span id="my-score">0</span></div>
    <div class="user-badge"><i class="fas fa-user-circle me-2 text-secondary"></i>Participant</div>

    <div class="quiz-container">
        
        <!-- INITIAL LOADER -->
        <div id="panel-loading" class="card-panel active">
            <div class="spinner"></div>
            <h4 class="waiting-text">Connecting to server...</h4>
        </div>

        <!-- LOBBY -->
        <div id="panel-lobby" class="card-panel">
            <i class="fas fa-hand-sparkles fa-4x mb-4" style="color: #B4120D;"></i>
            <h2>Welcome to {{ $quiz->name }}</h2>
            <br>
            <div class="spinner" style="border-width:4px;"></div>
            <h4 class="waiting-text" style="color: #B4120D;">Waiting for the admin to start the quiz...</h4>
            <p class="text-muted mt-3">Get ready! Questions will appear here automatically.</p>
        </div>

        <!-- QUESTION -->
        <div id="panel-question" class="card-panel pb-2">
            <div class="q-number-badge">Question <span id="q-number">1</span></div>
            
            <div class="timer-circle" id="q-timer">--</div>
            
            <p class="text-muted mb-4" style="font-size: 1rem;">Select your answer below</p>
            
            <div id="choices-container">
                <button class="choice-btn" onclick="submitAnswer('a')"><span class="letter">A</span><span class="option-text" id="opt-a-text">--</span></button>
                <button class="choice-btn" onclick="submitAnswer('b')"><span class="letter">B</span><span class="option-text" id="opt-b-text">--</span></button>
                <button class="choice-btn" onclick="submitAnswer('c')"><span class="letter">C</span><span class="option-text" id="opt-c-text">--</span></button>
                <button class="choice-btn" onclick="submitAnswer('d')"><span class="letter">D</span><span class="option-text" id="opt-d-text">--</span></button>
            </div>
        </div>

        <!-- WAITING FOR TIME TO UP -->
        <div id="panel-waiting" class="card-panel">
            <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
            <h2>Answer Submitted!</h2>
            <h4 class="waiting-text">Waiting for the timer to run out...</h4>
            <p class="text-muted mt-3">Scores are calculated based on how fast you answer. Points: Time Remaining.</p>
        </div>

        <!-- LEADERBOARD -->
        <div id="panel-leaderboard" class="card-panel p-4">
            <div class="my-rank-banner">
                <div>Rank<br><span id="my-rank">--</span></div>
                <div>Points<br><span id="my-points">0</span></div>
            </div>
            
            <h5 class="text-start fw-bold mb-3 d-flex justify-content-between align-items-center">
                <span><i class="fas fa-trophy text-warning me-2"></i>Live Leaderboard</span>
                <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
            </h5>
            
            <div id="leaderboard-list" class="text-start bg-light rounded-4 border p-2">
                <!-- Data here -->
            </div>

            <div class="mt-4 pt-3 border-top">
                <div class="spinner" style="width: 30px; height: 30px; border-width:3px; margin-bottom:10px;"></div>
                <h5 class="mt-2" style="color: #B4120D;">Waiting for the next question...</h5>
            </div>
        </div>

        <!-- FINISHED -->
        <div id="panel-finished" class="card-panel">
            <i class="fas fa-flag-checkered fa-5x text-success mb-4"></i>
            <h1 class="display-5 fw-bold mb-2">Quiz Finished!</h1>
            <p class="text-muted fs-5 mb-5">Thanks for participating in {{ $quiz->name }}</p>
            
            <div class="my-rank-banner">
                <div>Final Rank<br><span id="final-rank">--</span></div>
                <div>Total Score<br><span id="final-score">0</span></div>
            </div>

            <a href="{{ url('/') }}" class="btn btn-lg mt-4 px-5 rounded-pill text-white" style="background-color: #B4120D;">Go Home</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const stateUrl = `{{ route('interactive_quiz.state', $quiz) }}`;
        const answerUrl = `{{ route('interactive_quiz.answer', $quiz) }}`;
        const csrfToken = '{{ csrf_token() }}';
        const myParticipantId = '{{ $participantId }}';
        
        let currentState = 'loading';
        let currentQuestionNumber = 0;
        let timerInterval = null;
        let hasAnsweredCurrent = false;
        let serverTimeOffset = 0; // server_time - client_time (seconds)

        function switchPanel(panelId) {
            document.querySelectorAll('.card-panel').forEach(p => p.classList.remove('active'));
            document.getElementById(panelId).classList.add('active');
        }

        function calculateTimeRemaining(startTime, timeLimit) {
            let now = Math.floor(Date.now() / 1000) + serverTimeOffset;
            let elapsed = now - startTime;
            let remaining = timeLimit - elapsed;
            return remaining > 0 ? remaining : 0;
        }

        function submitAnswer(ans) {
            if (hasAnsweredCurrent) return;
            hasAnsweredCurrent = true;
            
            // UI Feedback
            const btns = document.querySelectorAll('.choice-btn');
            btns.forEach(b => {
                b.disabled = true;
                if(b.getAttribute('onclick').includes(`('${ans}')`)){
                    b.classList.add('selected');
                }
            });

            // Post
            fetch(answerUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ question_index: currentQuestionNumber, answer: ans })
            }).then(res => res.json()).then(data => {
                if (data.status === 'success') {
                    switchPanel('panel-waiting');
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            }).catch(e => {
                Swal.fire('Network error', 'Failed to submit answer', 'error');
                hasAnsweredCurrent = false;
                btns.forEach(b => b.disabled = false);
            });
        }

        function pollState() {
            fetch(stateUrl)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'unauthorized') {
                        window.location.reload();
                        return;
                    }

                    document.getElementById('my-score').innerText = data.data.my_score;

                    // Sync clock with server
                    if (data.data.server_time) {
                        serverTimeOffset = data.data.server_time - Math.floor(Date.now() / 1000);
                    }

                    const state = data.data.state;
                    
                    if (state === 'lobby') {
                        if (currentState !== 'lobby') switchPanel('panel-lobby');
                    } 
                    else if (state === 'question') {
                        let qNum = data.data.current_question;
                        let isNewQuestion = (qNum !== currentQuestionNumber);
                        currentQuestionNumber = qNum;

                        if (data.data.has_answered) {
                            if (currentState !== 'waiting') switchPanel('panel-waiting');
                        } else {
                            if (currentState !== 'question' || isNewQuestion) {
                                switchPanel('panel-question');
                                hasAnsweredCurrent = false;
                                
                                // Restore buttons
                                document.querySelectorAll('.choice-btn').forEach(b => {
                                    b.disabled = false;
                                    b.classList.remove('selected');
                                });

                                // Setup data (question text hidden from participants - shown on admin screen)
                                if (data.data.question_data) {
                                    const q = data.data.question_data;
                                    document.getElementById('q-number').innerText = qNum;
                                    document.getElementById('opt-a-text').innerText = q.options.a;
                                    document.getElementById('opt-b-text').innerText = q.options.b;
                                    document.getElementById('opt-c-text').innerText = q.options.c;
                                    document.getElementById('opt-d-text').innerText = q.options.d;
                                }

                                // Reset timer
                                if(timerInterval) clearInterval(timerInterval);
                                timerInterval = setInterval(() => {
                                    let rem = calculateTimeRemaining(data.data.start_time, data.data.time_limit);
                                    document.getElementById('q-timer').innerText = rem;
                                    if(rem <= 0) {
                                        clearInterval(timerInterval);
                                        // Timeout, lock selection immediately
                                        hasAnsweredCurrent = true;
                                    }
                                }, 1000);
                            }
                        }
                    }
                    else if (state === 'leaderboard') {
                        if (currentState !== 'leaderboard') switchPanel('panel-leaderboard');
                        
                        // Update Board
                        if (data.data.leaderboard_data) {
                            const top6 = data.data.leaderboard_data.slice(0, 6);
                            let html = '';
                            let myRank = '--';
                            data.data.leaderboard_data.forEach(p => {
                                if(p.participant_id === myParticipantId) {
                                    myRank = p.rank;
                                }
                            });
                            top6.forEach(p => {
                                html += `<div class="rank-row">
                                    <div class="rank-num">#${p.rank}</div>
                                    <div class="rank-name">${p.name} ${p.participant_id === myParticipantId ? '(You)' : ''}</div>
                                    <div class="rank-score">${p.score}</div>
                                </div>`;
                            });
                            document.getElementById('leaderboard-list').innerHTML = html || '<div class="p-3 text-center text-muted">No entries yet.</div>';
                            document.getElementById('my-rank').innerText = myRank;
                            document.getElementById('my-points').innerText = data.data.my_score;
                        }

                    }
                    else if (state === 'finished') {
                        if (currentState !== 'finished') switchPanel('panel-finished');
                        document.getElementById('final-rank').innerText = document.getElementById('my-rank').innerText;
                        document.getElementById('final-score').innerText = data.data.my_score;
                    }

                    currentState = state;
                })
                .catch(err => console.error('Poll error', err));
        }

        // Start polling immediately and then every interval
        pollState();
        setInterval(pollState, 1500);

        // Prevent copying and context menu
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('copy', e => e.preventDefault());
        document.addEventListener('cut', e => e.preventDefault());
        
        // Prevent keyboard shortcuts (Ctrl+C, Ctrl+A, F12, etc.)
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && (e.key === 'c' || e.key === 'a' || e.key === 'x' || e.key === 'v' || e.key === 'u' || e.key === 'p' || e.key === 's')) {
                e.preventDefault();
            }
            if (e.key === 'F12') {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
