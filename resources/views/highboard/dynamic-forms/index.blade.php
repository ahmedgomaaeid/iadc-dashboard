@extends('layouts.highboard-dashboard')

@section('title', 'Dynamic Forms')
@section('content')
    <div class="page-header">
        <h1 class="page-title">Dynamic Forms</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('highboard.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dynamic Forms</li>
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
                    <h3 class="card-title">Available Forms Data</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Subdomain</th>
                                    <th>Fields</th>
                                    <th>Submissions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($forms as $form)
                                    <tr>
                                        <td class="fw-semibold">{{ $form->subtitle }}</td>
                                        <td><code>{{ $form->subdomain }}</code></td>
                                        <td>
                                            @php $fieldCount = count($form->fields ?? []); @endphp
                                            <span class="badge bg-info">{{ $fieldCount }} field{{ $fieldCount !== 1 ? 's' : '' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $form->submissions_count }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('highboard.dynamic-forms.show', $form) }}" class="btn btn-outline-info btn-sm">
                                                <i class="fe fe-eye"></i> View Submissions
                                            </a>
                                            <a href="#" class="btn btn-outline-primary btn-sm copyFormLink" data-clipboard-text="{{ $form->getFormUrl() }}" title="Copy Form Link">
                                                <i class="fe fe-copy"></i> Copy Link
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No active forms available at this time.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $forms->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.copyFormLink').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                navigator.clipboard.writeText(this.dataset.clipboardText);
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'Form link copied to clipboard',
                    timer: 2000
                });
            });
        });
    </script>
@endsection
