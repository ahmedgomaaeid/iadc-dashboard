@extends('layouts.admin-dashboard')
@section('title', 'WellSharp Quizzes')
@section('content')
    <div class="page-header">
        <h1 class="page-title">WellSharp Quizzes</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">WellSharp Quizzes</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">All WellSharp Quizzes</h3>
                    <a href="{{ route('admin.wellsharp_quizzes.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>Add New Quiz
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th>Quiz Name</th>
                                    <th>Questions</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quizzes as $quiz)
                                    <tr>
                                        <td class="fw-semibold">{{ $quiz->name }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $quiz->questions->count() }} questions</span>
                                        </td>
                                        <td>
                                            @if ($quiz->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.wellsharp_quizzes.control', $quiz) }}" class="btn btn-info btn-sm" title="Control Panel">
                                                <i class="fas fa-gamepad"></i> Control Panel
                                            </a>
                                            <a href="#" class="btn btn-outline-danger btn-sm copyPresentLink" data-clipboard-text="{{ route('wellsharp.present', $quiz) }}">
                                                <i class="fe fe-copy"></i> Copy Presentation Link
                                            </a>
                                            <a href="{{ route('admin.wellsharp_quizzes.show', $quiz) }}" class="btn btn-outline-info btn-sm">View</a>
                                            <a href="{{ route('admin.wellsharp_quizzes.edit', $quiz) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                            <form action="{{ route('admin.wellsharp_quizzes.toggle-active', $quiz) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm {{ $quiz->is_active ? 'btn-warning' : 'btn-success' }}">
                                                    {{ $quiz->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.wellsharp_quizzes.destroy', $quiz) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this quiz?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No WellSharp quizzes found. Create your first quiz.</td>
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
    <script>
        document.querySelectorAll('.copyPresentLink').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                navigator.clipboard.writeText(this.dataset.clipboardText);
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'Presentation link copied to clipboard',
                    timer: 2000
                });
            });
        });
    </script>
@endsection
