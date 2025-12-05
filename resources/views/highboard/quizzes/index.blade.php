@extends('layouts.highboard-dashboard')

@section('title', 'Quizzes')
@section('content')
    <div class="page-header">
        <h1 class="page-title">Quizzes</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('highboard.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Quizzes</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">All Quizzes</h3>
                    <a href="{{ route('highboard.quizzes.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>Add New Quiz
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th>Quiz Name</th>
                                    <th>Visibility</th>
                                    <th>Committee</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quizzes as $quiz)
                                    <tr>
                                        <td class="fw-semibold">{{ $quiz->name }}</td>
                                        <td>
                                            @if ($quiz->visibility === 'global')
                                                <span class="badge bg-info"><i class="fe fe-globe me-1"></i> Global</span>
                                            @else
                                                <span class="badge bg-warning"><i class="fe fe-lock me-1"></i> Private</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($quiz->visibility === 'private' && $quiz->committee)
                                                <span class="badge bg-light text-dark">{{ $quiz->committee->name }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($quiz->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('highboard.quizzes.leaderboard', $quiz) }}" class="btn btn-info btn-sm" title="View Leaderboard">
                                                <i class="fas fa-trophy"></i> Leaderboard
                                            </a>
                                            <a href="#" class="btn btn-outline-danger btn-sm copyQuizLink" data-clipboard-text="{{ route('quiz.show', $quiz) }}">
                                                <i class="fe fe-copy"></i> Copy Link
                                            </a> 
                                            <a href="#" class="btn btn-outline-dark btn-sm download-qr" data-url="{{ route('quiz.show', $quiz) }}" data-name="quiz-{{ $quiz->id }}-qr">
                                                <i class="fas fa-qrcode"></i> QR
                                            </a> 
                                            <a href="{{ route('highboard.quizzes.show', $quiz) }}" class="btn btn-outline-info btn-sm">View</a>
                                            <a href="{{ route('highboard.quizzes.edit', $quiz) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                            <form action="{{ route('highboard.quizzes.toggle-active', $quiz) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm {{ $quiz->is_active ? 'btn-warning' : 'btn-success' }}">
                                                    {{ $quiz->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('highboard.quizzes.destroy', $quiz) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this quiz?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No quizzes found. Create your first quiz.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $quizzes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
    <script>
        document.querySelectorAll('.copyQuizLink').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                navigator.clipboard.writeText(this.dataset.clipboardText);
                // show toast
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'Quiz link copied to clipboard',
                    timer: 2000
                });
            });
        });

        document.querySelectorAll('.download-qr').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.dataset.url;
                const name = this.dataset.name;
                
                const qrCode = new QRCodeStyling({
                    width: 700,
                    height: 700,
                    type: "svg",
                    data: url,
                    image: "{{ asset('assets/images/brand/logo-2.svg') }}",
                    dotsOptions: {
                        color: "#B4120D",
                        type: "dots"
                    },
                    backgroundOptions: {
                        color: "#fff",
                    },
                    imageOptions: {
                        crossOrigin: "anonymous",
                        margin: 10
                    }
                });

                qrCode.download({ name: name, extension: "png" });
            });
        });
    </script>
@endsection