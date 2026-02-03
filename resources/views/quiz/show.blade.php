<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registration - IADC Suez Drilling Camp</title>
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
        <link rel="icon" type="image/x-icon" href="{{ asset('images/IADC Icon.png') }}">
        <style>
            .registration-container {
                max-width: 700px;
                margin: 50px auto;
                padding: 0;
            }

            .registration-card {
                background-color: #fff;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                overflow: hidden;
            }

            .card-header {
                background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
                color: white;
                padding: 40px 30px;
                text-align: center;
            }

            .card-header h1 {
                font-size: 2rem;
                font-weight: 700;
                margin-bottom: 10px;
            }

            .card-header p {
                font-size: 1rem;
                opacity: 0.9;
                margin: 0;
            }

            .card-body {
                padding: 40px 30px;
            }

            .form-label {
                font-weight: 600;
                color: #374151;
                margin-bottom: 8px;
                font-size: 0.95rem;
            }

            .form-control {
                border: 2px solid #e5e7eb;
                border-radius: 10px;
                padding: 12px 15px;
                font-size: 1rem;
                transition: all 0.3s ease;
            }

            .form-control:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            }

            .input-group {
                position: relative;
            }

            .input-icon {
                position: absolute;
                left: 15px;
                top: 50%;
                transform: translateY(-50%);
                color: #9ca3af;
                z-index: 10;
            }

            .form-control.with-icon {
                padding-left: 45px;
            }

            .btn-register {
                background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
                border: none;
                border-radius: 10px;
                padding: 15px;
                font-size: 1.1rem;
                font-weight: 600;
                color: white;
                transition: all 0.3s ease;
                margin-top: 20px;
            }

            .btn-register:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
            }

            .alert {
                border-radius: 10px;
                border: none;
            }

            .alert-success {
                background-color: #d1fae5;
                color: #065f46;
            }

            .alert-danger {
                background-color: #fee2e2;
                color: #991b1b;
            }

            .alert ul {
                margin-bottom: 0;
                padding-left: 20px;
            }

            @media (max-width: 768px) {
                .card-header h1 {
                    font-size: 1.5rem;
                }

                .card-body {
                    padding: 30px 20px;
                }
            }

            /* Quiz Styles */
            .timer-container {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 15px;
                padding: 20px;
                text-align: center;
                margin-bottom: 25px;
                color: white;
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            }

            .timer-label {
                font-size: 0.9rem;
                opacity: 0.9;
                margin-bottom: 5px;
            }

            .timer-value {
                font-size: 3rem;
                font-weight: 700;
                margin: 0;
                line-height: 1;
            }

            .timer-value.warning {
                color: #fbbf24;
                animation: pulse 0.5s ease-in-out infinite;
            }

            .timer-value.danger {
                color: #ef4444;
                animation: pulse 0.3s ease-in-out infinite;
            }

            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }

            .question-container {
                background: #f9fafb;
                border-radius: 15px;
                padding: 25px;
                margin-bottom: 25px;
                border: 2px solid #e5e7eb;
                user-select: none;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
            }

            .question-number {
                font-size: 0.9rem;
                color: var(--primary-color);
                font-weight: 600;
                margin-bottom: 10px;
            }

            .question-text {
                font-size: 1.3rem;
                font-weight: 600;
                color: #1f2937;
                margin: 0;
            }

            .choices-container {
                display: grid;
                gap: 15px;
                margin-top: 20px;
            }

            .choice-btn {
                background: white;
                border: 3px solid #e5e7eb;
                border-radius: 12px;
                padding: 18px 20px;
                text-align: left;
                font-size: 1.05rem;
                font-weight: 500;
                color: #374151;
                transition: all 0.3s ease;
                cursor: pointer;
                display: flex;
                align-items: center;
                position: relative;
                user-select: none;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
            }

            .choice-btn:hover:not(:disabled) {
                border-color: var(--primary-color);
                background: #eff6ff;
                transform: translateX(5px);
            }

            .choice-btn:disabled {
                cursor: not-allowed;
                opacity: 0.6;
            }

            .choice-letter {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                background: var(--primary-color);
                color: white;
                border-radius: 8px;
                font-weight: 700;
                font-size: 1.1rem;
                margin-right: 15px;
                flex-shrink: 0;
            }

            .choice-btn.selected {
                border-color: var(--primary-color);
                background: #dbeafe;
            }

            .choice-btn.correct {
                border-color: #10b981;
                background: #d1fae5;
            }

            .choice-btn.correct .choice-letter {
                background: #10b981;
            }

            .choice-btn.incorrect {
                border-color: #ef4444;
                background: #fee2e2;
            }

            .choice-btn.incorrect .choice-letter {
                background: #ef4444;
            }

            .score-badge {
                display: inline-block;
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                color: white;
                padding: 8px 20px;
                border-radius: 25px;
                font-weight: 600;
                font-size: 1rem;
                margin-bottom: 15px;
            }

            .feedback-container {
                margin-top: 20px;
                padding: 20px;
                border-radius: 12px;
                animation: slideIn 0.3s ease;
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .feedback-correct {
                background: #d1fae5;
                border: 2px solid #10b981;
                color: #065f46;
            }

            .feedback-incorrect {
                background: #fee2e2;
                border: 2px solid #ef4444;
                color: #991b1b;
            }

            .feedback-timeout {
                background: #fef3c7;
                border: 2px solid #f59e0b;
                color: #92400e;
            }

            .next-question-btn {
                background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
                border: none;
                border-radius: 10px;
                padding: 12px 30px;
                font-size: 1rem;
                font-weight: 600;
                color: white;
                margin-top: 15px;
                transition: all 0.3s ease;
            }

            .next-question-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
            }

            .final-results {
                text-align: center;
                padding: 40px 20px;
            }

            .final-score {
                font-size: 4rem;
                font-weight: 700;
                color: var(--primary-color);
                margin: 20px 0;
            }

            .result-message {
                font-size: 1.5rem;
                font-weight: 600;
                margin-bottom: 10px;
            }

            .result-details {
                font-size: 1.1rem;
                color: #6b7280;
                margin-bottom: 30px;
            }

            .loading-spinner {
                display: inline-block;
                width: 40px;
                height: 40px;
                border: 4px solid #f3f4f6;
                border-top: 4px solid var(--primary-color);
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    </head>
    <body>
        <header class="header">
            <nav class="container">
                <img src="{{ asset('images/logo.png') }}" alt="IADC Logo" class="logo">
            </nav>
        </header>

        <main class="container">
            <div class="registration-container">
                <div class="registration-card">
                    <div class="card-header">
                        <h1><i class="fas fa-graduation-cap"></i>Registration</h1>
                        <p>IADC Suez Drilling Camp - Join Our Program</p>
                    </div>

                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <strong>Please fix the following errors:</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div>
                            @if($quizCount == null || $quizCount <= 0)
                                {{-- The quiz is close wait until admin open it --}}
                                <p class="text-center text-danger">The quiz is currently closed. Please wait until the administrator opens it.</p>
                            @else
                                <div id="quiz_inputs_container">
                                    <div class="mb-3">
                                        <label for="participant_name" class="form-label">Full Name</label>
                                        <input type="text" id="participant_name" name="name" class="form-control" placeholder="Your Full Name" value="{{ $userName }}" @if($userName) readonly @endif required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="participant_email" class="form-label">Email Address</label>
                                        <input type="email" id="participant_email" name="email" class="form-control" placeholder="Your Email Address" value="{{ $userEmail }}" @if($userEmail) readonly @endif required>
                                    </div>
                                    <div class="d-grid">
                                        <button type="button" id="submit-button" class="btn btn-register">
                                            <i class="fas fa-paper-plane"></i> Start Quiz
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="footer">
            <p>Explore Your Potential</p>
        </footer>

        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            // Quiz configuration
            const QUIZ_ID = {{ $id }};
            const TOTAL_QUESTIONS = {{ $quizCount }};
            let participantId = null;
            let currentQuestionNumber = 1;
            let timerInterval = null;
            let questionStartTime = null;

            // Prevent copy, cut, and right-click on quiz content
            document.addEventListener('DOMContentLoaded', function() {
                const submitButton = document.getElementById('submit-button');
                if (submitButton) {
                    submitButton.addEventListener('click', handleParticipantRegistration);
                }

                // Disable right-click context menu
                document.addEventListener('contextmenu', function(e) {
                    const quizContainer = document.getElementById('quiz_inputs_container');
                    if (quizContainer && quizContainer.contains(e.target)) {
                        e.preventDefault();
                        return false;
                    }
                });

                // Disable copy and cut
                document.addEventListener('copy', function(e) {
                    const quizContainer = document.getElementById('quiz_inputs_container');
                    if (quizContainer && quizContainer.contains(e.target)) {
                        e.preventDefault();
                        return false;
                    }
                });

                document.addEventListener('cut', function(e) {
                    const quizContainer = document.getElementById('quiz_inputs_container');
                    if (quizContainer && quizContainer.contains(e.target)) {
                        e.preventDefault();
                        return false;
                    }
                });

                // Disable keyboard shortcuts for copying
                document.addEventListener('keydown', function(e) {
                    const quizContainer = document.getElementById('quiz_inputs_container');
                    if (quizContainer && quizContainer.contains(document.activeElement)) {
                        // Ctrl+C, Ctrl+X, Ctrl+A
                        if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'x' || e.key === 'a')) {
                            e.preventDefault();
                            return false;
                        }
                    }
                });
            });

            // Handle participant registration
            async function handleParticipantRegistration() {
                const nameInput = document.getElementById('participant_name');
                const name = nameInput.value.trim();
                const emailInput = document.getElementById('participant_email');
                const email = emailInput.value.trim();

                if (!name) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Name Required',
                        text: 'Please enter your name to start the quiz.'
                    });
                    return;
                }

                if (!email) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Email Required',
                        text: 'Please enter your email to start the quiz.'
                    });
                    return;
                }

                try {
                    // Show loading
                    Swal.fire({
                        title: 'Starting Quiz...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Register participant
                    const response = await fetch(`/api/quizzes/${QUIZ_ID}/addParticipant`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            name: name,
                            email: email,
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Failed to register participant');
                    }

                    participantId = data.participant_id;

                    // Close loading and start quiz
                    Swal.close();
                    await loadQuestion(currentQuestionNumber);

                } catch (error) {
                    console.error('Registration error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Registration Failed',
                        text: error.message || 'Something went wrong. Please try again.'
                    });
                }
            }

            // Load a question
            async function loadQuestion(questionNumber) {
                try {
                    const container = document.getElementById('quiz_inputs_container');

                    // Show loading state
                    container.innerHTML = `
                        <div style="text-align: center; padding: 40px;">
                            <div class="loading-spinner"></div>
                            <p style="margin-top: 20px; color: #6b7280;">Loading question ${questionNumber}...</p>
                        </div>
                    `;

                    // Fetch question from API
                    const response = await fetch(
                        `/api/quizzes/${QUIZ_ID}/getQuestion/${questionNumber}?participant_id=${participantId}`
                    );

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Failed to load question');
                    }

                    // Store start time
                    questionStartTime = data.started_at;

                    // Render question
                    renderQuestion(data.question, data.time_limit);

                    // Start timer
                    startTimer(data.time_limit);

                } catch (error) {
                    console.error('Load question error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to load question'
                    });
                }
            }

            // Render question HTML
            function renderQuestion(question, timeLimit) {
                const container = document.getElementById('quiz_inputs_container');

                container.innerHTML = `
                    <!-- Timer -->
                    <div class="timer-container">
                        <div class="timer-label">Time Remaining</div>
                        <h2 class="timer-value" id="timer-display">${timeLimit}</h2>
                    </div>

                    <!-- Question -->
                    <div class="question-container">
                        <div class="question-number">
                            Question ${question.number} of ${TOTAL_QUESTIONS}
                        </div>
                        <p class="question-text">${escapeHtml(question.question)}</p>
                    </div>

                    <!-- Choices -->
                    <div class="choices-container">
                        <button class="choice-btn" data-answer="A" onclick="selectAnswer('A')">
                            <span class="choice-letter">A</span>
                            <span>${escapeHtml(question.options.a)}</span>
                        </button>
                        <button class="choice-btn" data-answer="B" onclick="selectAnswer('B')">
                            <span class="choice-letter">B</span>
                            <span>${escapeHtml(question.options.b)}</span>
                        </button>
                        <button class="choice-btn" data-answer="C" onclick="selectAnswer('C')">
                            <span class="choice-letter">C</span>
                            <span>${escapeHtml(question.options.c)}</span>
                        </button>
                        <button class="choice-btn" data-answer="D" onclick="selectAnswer('D')">
                            <span class="choice-letter">D</span>
                            <span>${escapeHtml(question.options.d)}</span>
                        </button>
                    </div>

                    <!-- Feedback area (hidden initially) -->
                    <div id="feedback-area"></div>
                `;
            }

            // Start countdown timer
            function startTimer(seconds) {
                let timeLeft = seconds;
                const timerDisplay = document.getElementById('timer-display');

                // Clear any existing timer
                if (timerInterval) {
                    clearInterval(timerInterval);
                }

                timerInterval = setInterval(() => {
                    timeLeft--;
                    timerDisplay.textContent = timeLeft;

                    // Change color based on time remaining
                    if (timeLeft <= 5) {
                        timerDisplay.classList.add('danger');
                        timerDisplay.classList.remove('warning');
                    } else if (timeLeft <= 10) {
                        timerDisplay.classList.add('warning');
                        timerDisplay.classList.remove('danger');
                    } else {
                        timerDisplay.classList.remove('warning', 'danger');
                    }

                    // Time's up
                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        handleTimeout();
                    }
                }, 1000);
            }

            // Handle timeout
            function handleTimeout() {
                // Disable all choice buttons
                const choiceBtns = document.querySelectorAll('.choice-btn');
                choiceBtns.forEach(btn => btn.disabled = true);

                // Move directly to next question
                handleNextQuestion();
            }

            // Select answer
            function selectAnswer(answer) {
                // Remove previous selection
                const choiceBtns = document.querySelectorAll('.choice-btn');
                choiceBtns.forEach(btn => btn.classList.remove('selected'));

                // Mark selected
                const selectedBtn = document.querySelector(`.choice-btn[data-answer="${answer}"]`);
                selectedBtn.classList.add('selected');

                // Submit answer
                submitAnswer(answer);
            }

            // Submit answer
            async function submitAnswer(answer) {
                try {
                    // Disable all buttons
                    const choiceBtns = document.querySelectorAll('.choice-btn');
                    choiceBtns.forEach(btn => btn.disabled = true);

                    // Stop timer
                    if (timerInterval) {
                        clearInterval(timerInterval);
                    }

                    // Submit to API
                    const response = await fetch(`/api/quizzes/${QUIZ_ID}/answerQuestion`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            participant_id: participantId,
                            question_number: currentQuestionNumber,
                            answer: answer
                        })
                    });

                    const data = await response.json();

                    // Don't update or track score on frontend anymore
                    // totalScore = data.score || 0;

                    // Move directly to next question
                    handleNextQuestion();

                } catch (error) {
                    console.error('Submit answer error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to submit answer. Please try again.'
                    });
                }
            }

            // Show feedback after answer
            function showFeedback(data, userAnswer) {
                const feedbackArea = document.getElementById('feedback-area');
                const isCorrect = data.is_correct;
                const correctAnswer = data.correct_answer;

                // Highlight correct and incorrect answers
                const choiceBtns = document.querySelectorAll('.choice-btn');
                choiceBtns.forEach(btn => {
                    const btnAnswer = btn.getAttribute('data-answer');
                    if (btnAnswer === correctAnswer) {
                        btn.classList.add('correct');
                    }
                    if (btnAnswer === userAnswer && !isCorrect) {
                        btn.classList.add('incorrect');
                    }
                });

                // Show brief feedback message (without next button)
                const feedbackClass = isCorrect ? 'feedback-correct' : 'feedback-incorrect';
                const icon = isCorrect ? 'fa-check-circle' : 'fa-times-circle';
                const title = isCorrect ? 'Correct!' : 'Incorrect';

                feedbackArea.innerHTML = `
                    <div class="feedback-container ${feedbackClass}">
                        <h4><i class="fas ${icon}"></i> ${title}</h4>
                        <p>
                            ${isCorrect ? 'Great job!' : `The correct answer is <strong>${correctAnswer}</strong>.`}
                        </p>
                        <p><small>Moving to next question...</small></p>
                    </div>
                `;
            }

            // Handle next question
            async function handleNextQuestion() {
                currentQuestionNumber++;

                if (currentQuestionNumber <= TOTAL_QUESTIONS) {
                    await loadQuestion(currentQuestionNumber);
                } else {
                    showFinalResults();
                }
            }

            // Show final results
            function showFinalResults() {
                const container = document.getElementById('quiz_inputs_container');

                container.innerHTML = `
                    <div class="final-results">
                        <div style="font-size: 5rem; margin-bottom: 20px;">🎉</div>
                        <h2 class="result-message">Quiz Completed!</h2>
                        <div class="result-details">
                            Thank you for completing the exam. Your responses have been recorded.
                        </div>
                    </div>
                `;

                // Show completion message
                Swal.fire({
                    icon: 'success',
                    title: 'Exam Completed!',
                    html: 'Thank you for participating. Your answers have been submitted successfully.',
                    confirmButtonText: 'Done'
                });
            }

            // Utility function to escape HTML
            function escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, m => map[m]);
            }
        </script>
    </body>
</html>
