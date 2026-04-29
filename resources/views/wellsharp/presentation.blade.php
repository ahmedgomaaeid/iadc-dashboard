<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $quiz->name }} — WellSharp Quiz</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0f0f1a;
            color: #fff;
            overflow: hidden;
            height: 100vh;
            width: 100vw;
        }

        /* ============ LOBBY ============ */
        .screen {
            display: none;
            width: 100vw;
            height: 100vh;
            position: absolute;
            top: 0;
            left: 0;
        }

        .screen.active {
            display: flex;
        }

        #lobby-screen {
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0f1a 0%, #1a1a3e 50%, #0f0f1a 100%);
        }

        #lobby-screen .logo-icon {
            font-size: 5rem;
            color: #B4120D;
            margin-bottom: 2rem;
            animation: pulse-glow 2s ease-in-out infinite;
        }

        #lobby-screen h1 {
            font-size: 4rem;
            font-weight: 900;
            background: linear-gradient(135deg, #B4120D, #ff4444);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        #lobby-screen p {
            font-size: 1.5rem;
            color: #888;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        /* ============ QUESTION SCREEN ============ */
        #question-screen {
            flex-direction: column;
            background: linear-gradient(135deg, #0f0f1a 0%, #1a1a3e 100%);
        }

        .question-header {
            padding: 0.5rem 3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .question-badge {
            background: #B4120D;
            color: white;
            padding: 0.8rem 2.5rem;
            border-radius: 50px;
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .quiz-title-small {
            font-size: 1.1rem;
            color: #666;
            font-weight: 600;
        }

        .question-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0;
            width: 100%;
        }

        .question-text {
            font-size: 4.5rem;
            font-weight: 800;
            text-align: center;
            line-height: 1.2;
            margin-bottom: 2.5rem;
            color: #fff;
            width: 95%;
        }

        .options-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.2rem;
            width: 95%;
        }

        .option-card {
            padding: 1.8rem 2.5rem;
            border-radius: 18px;
            font-size: 2.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 1.2rem;
            transition: all 0.3s ease;
            border: 3px solid transparent;
        }

        .option-card .option-letter {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 1.8rem;
            background: rgba(255,255,255,0.2);
            flex-shrink: 0;
        }

        .option-a { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .option-b { background: linear-gradient(135deg, #3498db, #2980b9); }
        .option-c { background: linear-gradient(135deg, #f39c12, #e67e22); }
        .option-d { background: linear-gradient(135deg, #27ae60, #229954); }

        /* ============ LEADERBOARD SCREEN ============ */
        #leaderboard-screen {
            flex-direction: column;
            background: linear-gradient(135deg, #0f0f1a 0%, #1a1a3e 100%);
        }

        .leaderboard-header {
            padding: 0.5rem 4rem 0.5rem;
            text-align: center;
        }

        .leaderboard-header h1 {
            font-size: 4.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #f1c40f, #e67e22);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .leaderboard-header .subtitle {
            font-size: 1.2rem;
            color: #888;
            margin-top: 0.5rem;
        }

        .leaderboard-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 0.5rem 4rem;
            overflow-y: auto;
        }

        .leaderboard-table {
            width: 95%;
        }

        .lb-row {
            display: flex;
            align-items: center;
            padding: 1.5rem 2.5rem;
            margin-bottom: 0.8rem;
            border-radius: 16px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            transition: all 0.3s ease;
        }

        .lb-row.top-1 {
            background: linear-gradient(135deg, rgba(241,196,15,0.15), rgba(241,196,15,0.05));
            border-color: rgba(241,196,15,0.3);
            transform: scale(1.03);
        }

        .lb-row.top-2 {
            background: linear-gradient(135deg, rgba(192,192,192,0.12), rgba(192,192,192,0.05));
            border-color: rgba(192,192,192,0.25);
            transform: scale(1.015);
        }

        .lb-row.top-3 {
            background: linear-gradient(135deg, rgba(205,127,50,0.12), rgba(205,127,50,0.05));
            border-color: rgba(205,127,50,0.25);
        }

        .lb-rank {
            width: 100px;
            text-align: center;
            font-size: 2.8rem;
            font-weight: 900;
            flex-shrink: 0;
        }

        .lb-rank .rank-icon {
            font-size: 3.2rem;
        }

        .lb-name {
            flex: 1;
            font-size: 2.4rem;
            font-weight: 700;
            padding-left: 1rem;
        }

        .lb-score {
            font-size: 2.8rem;
            font-weight: 900;
            padding: 0.5rem 2rem;
            border-radius: 30px;
            background: linear-gradient(135deg, #B4120D, #ff4444);
            min-width: 130px;
            text-align: center;
        }

        /* ============ FINISHED SCREEN ============ */
        #finished-screen {
            flex-direction: column;
            background: linear-gradient(135deg, #0f0f1a 0%, #1a1a3e 100%);
        }

        .finished-banner {
            text-align: center;
            padding: 0.5rem;
        }

        .finished-banner h1 {
            font-size: 5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #f1c40f, #e67e22, #ff4444);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .finished-banner .trophy-icon {
            font-size: 5rem;
            color: #f1c40f;
            margin-bottom: 0.5rem;
            animation: trophy-bounce 1s ease-in-out infinite;
        }

        /* ============ ANIMATIONS ============ */
        @keyframes pulse-glow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.05); }
        }

        @keyframes trophy-bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes slide-in {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-in {
            animation: slide-in 0.5s ease-out forwards;
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 3px; }
    </style>
</head>
<body>

    <!-- LOBBY SCREEN -->
    <div id="lobby-screen" class="screen active">
        <div class="logo-icon">
            <img src="{{ asset('assets/images/brand/logo.png') }}" alt="Logo" style="height: 10rem; object-fit: contain;">
        </div>
        <h1>{{ $quiz->name }}</h1>
        <p>Waiting to start...</p>
    </div>

    <!-- QUESTION SCREEN -->
    <div id="question-screen" class="screen">
        <div class="question-header">
            <span class="question-badge">Question <span id="q-number">1</span></span>
            <span class="quiz-title-small">{{ $quiz->name }}</span>
        </div>
        <div class="question-body">
            <div class="question-text" id="q-text">Loading question...</div>
            <div class="options-grid">
                <div class="option-card option-a animate-in">
                    <div class="option-letter">A</div>
                    <span id="opt-a">—</span>
                </div>
                <div class="option-card option-b animate-in" style="animation-delay: 0.1s">
                    <div class="option-letter">B</div>
                    <span id="opt-b">—</span>
                </div>
                <div class="option-card option-c animate-in" style="animation-delay: 0.2s">
                    <div class="option-letter">C</div>
                    <span id="opt-c">—</span>
                </div>
                <div class="option-card option-d animate-in" style="animation-delay: 0.3s">
                    <div class="option-letter">D</div>
                    <span id="opt-d">—</span>
                </div>
            </div>
        </div>
    </div>

    <!-- LEADERBOARD SCREEN -->
    <div id="leaderboard-screen" class="screen">
        <div class="leaderboard-header">
            <h1><i class="fas fa-trophy me-3"></i>Leaderboard</h1>
            <div class="subtitle" id="lb-subtitle">Live Rankings</div>
        </div>
        <div class="leaderboard-content">
            <div class="leaderboard-table" id="lb-table">
                <!-- Populated by JS -->
            </div>
        </div>
    </div>

    <!-- FINISHED SCREEN -->
    <div id="finished-screen" class="screen">
        <div class="finished-banner">
            <div class="trophy-icon"><i class="fas fa-trophy"></i></div>
            <h1>Quiz Complete!</h1>
        </div>
        <div class="leaderboard-header">
            <h1 style="font-size: 3.5rem;"><i class="fas fa-medal me-2"></i>Final Results</h1>
        </div>
        <div class="leaderboard-content">
            <div class="leaderboard-table" id="lb-table-final">
                <!-- Populated by JS -->
            </div>
        </div>
    </div>

    <script>
        const stateUrl = `{{ route('wellsharp.present.state', $quiz) }}`;
        let lastStateJSON = '';

        function fetchState() {
            fetch(stateUrl)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        const newJSON = JSON.stringify(data);
                        if (newJSON !== lastStateJSON) {
                            lastStateJSON = newJSON;
                            updatePresentation(data.state, data.leaderboard);
                        }
                    }
                })
                .catch(err => console.error('Poll error:', err));
        }

        function showScreen(screenId) {
            document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
            document.getElementById(screenId).classList.add('active');
        }

        function updatePresentation(stateData, leaderboard) {
            if (stateData.state === 'lobby') {
                showScreen('lobby-screen');
            }
            else if (stateData.state === 'question') {
                showScreen('question-screen');
                if (stateData.question_data) {
                    const q = stateData.question_data;
                    document.getElementById('q-number').innerText = stateData.current_question;
                    document.getElementById('q-text').innerText = q.question;
                    document.getElementById('opt-a').innerText = q.options.a;
                    document.getElementById('opt-b').innerText = q.options.b;
                    document.getElementById('opt-c').innerText = q.options.c;
                    document.getElementById('opt-d').innerText = q.options.d;

                    // Re-trigger animations
                    document.querySelectorAll('.option-card').forEach(card => {
                        card.style.animation = 'none';
                        card.offsetHeight; // trigger reflow
                        card.style.animation = '';
                    });
                }
            }
            else if (stateData.state === 'leaderboard') {
                showScreen('leaderboard-screen');
                renderLeaderboard(leaderboard, 'lb-table');
            }
            else if (stateData.state === 'finished') {
                showScreen('finished-screen');
                renderLeaderboard(leaderboard, 'lb-table-final');
            }
        }

        function renderLeaderboard(leaderboard, containerId) {
            const container = document.getElementById(containerId);
            if (leaderboard.length === 0) {
                container.innerHTML = '<div class="lb-row"><div class="lb-name" style="text-align:center;color:#888;">No participants yet</div></div>';
                return;
            }

            let html = '';
            leaderboard.forEach((p, index) => {
                let topClass = '';
                let rankDisplay = p.rank;

                if (p.rank === 1) {
                    topClass = 'top-1';
                    rankDisplay = '<span class="rank-icon">👑</span>';
                } else if (p.rank === 2) {
                    topClass = 'top-2';
                    rankDisplay = '<span class="rank-icon">🥈</span>';
                } else if (p.rank === 3) {
                    topClass = 'top-3';
                    rankDisplay = '<span class="rank-icon">🥉</span>';
                }

                html += `
                    <div class="lb-row ${topClass} animate-in" style="animation-delay: ${index * 0.08}s">
                        <div class="lb-rank">${rankDisplay}</div>
                        <div class="lb-name">${p.name}</div>
                        <div class="lb-score">${p.score}</div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Start polling
        fetchState();
        setInterval(fetchState, 1500);
    </script>
</body>
</html>
