@extends('layouts.board-dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Quiz Questions</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('board.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Quiz Questions</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="mb-1">{{ $quiz->name }}</h4>
                    @if ($quiz->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-primary">Inactive</span>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <a  class="btn btn-primary" id="copyQuizLink" data-clipboard-text="{{ route('quiz.show', $quiz) }}">
                        <i class="fas fa-copy me-1"></i> Copy Link
                    </a>
                    <a href="{{ route('board.quizzes.leaderboard', $quiz) }}" class="btn btn-info">
                        <i class="fas fa-trophy me-1"></i> Leaderboard
                    </a>
                    <form action="{{ route('board.quizzes.toggle-active', $quiz) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn {{ $quiz->is_active ? 'btn-primary' : 'btn-success' }}">
                            {{ $quiz->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <a href="{{ route('board.quizzes.index') }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Questions</span>
                    <div class="d-flex gap-2">
                         <button class="btn btn-warning btn-sm" onclick="importQuestionsAi()">Import AI Questions</button>
                        <a href="{{ route('board.quizzes.questions.create', $quiz) }}" class="btn btn-primary btn-sm">Add Question</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Question</th>
                                    <th style="width:35%">Options</th>
                                    <th style="width:10%" class="text-center">Time</th>
                                    <th style="width:15%" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($quiz->questions as $question)
                                    <tr>
                                        <td class="fw-semibold">{{ $question->question }}</td>
                                        <td>
                                            <div class="small">
                                                <span class="me-2 {{ $question->correct_option === 'a' ? 'fw-bold text-success' : '' }}">A) {{ $question->option_a }}</span>
                                                <span class="me-2 {{ $question->correct_option === 'b' ? 'fw-bold text-success' : '' }}">B) {{ $question->option_b }}</span>
                                                <span class="me-2 {{ $question->correct_option === 'c' ? 'fw-bold text-success' : '' }}">C) {{ $question->option_c }}</span>
                                                <span class="me-2 {{ $question->correct_option === 'd' ? 'fw-bold text-success' : '' }}">D) {{ $question->option_d }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $question->time_limit ?? 30 }}s</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('board.questions.edit', $question) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                            <form action="{{ route('board.questions.destroy', $question) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this question?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No questions yet. Add your first question.</td>
                                    </tr>
                                @endforelse
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
        copyButton = document.getElementById('copyQuizLink');
        copyButton.addEventListener('click', function() {
            navigator.clipboard.writeText(copyButton.dataset.clipboardText);
            // show toast
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Quiz link copied to clipboard',
            });
        });

        function importQuestionsAi() {
            Swal.fire({
                title: 'Import Questions via AI',
                input: 'textarea',
                inputLabel: 'Paste text containing questions',
                inputPlaceholder: 'Paste your text here...',
                inputAttributes: {
                    'aria-label': 'Paste your text here'
                },
                showCancelButton: true,
                confirmButtonText: 'Generate',
                showLoaderOnConfirm: true,
                preConfirm: (text) => {
                    return fetch('{{ route('board.quizzes.questions.ai-import', $quiz) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ questions_text: text })
                    })
                    .then(response => {
                         if (!response.ok) {
                            return response.json().then(err => { throw new Error(err.message || response.statusText) });
                        }
                        return response.json()
                    })
                    .catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error}`
                        )
                    })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                     if(result.value.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: result.value.message,
                            icon: 'success'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                         Swal.fire({
                            title: 'Error!',
                            text: result.value.message || 'Something went wrong',
                            icon: 'error'
                        });
                    }
                }
            })
        }
    </script>
@endsection
