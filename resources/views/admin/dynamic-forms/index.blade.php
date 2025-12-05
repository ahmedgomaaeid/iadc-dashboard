@extends('layouts.admin-dashboard')

@section('title', 'Dynamic Forms')
@section('content')
    <div class="page-header">
        <h1 class="page-title">Dynamic Forms</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
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
                    <h3 class="card-title">All Dynamic Forms</h3>
                    <a href="{{ route('admin.dynamic-forms.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>Add New Form
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Subtitle</th>
                                    <th>Fields</th>
                                    <th>Submissions</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($forms as $form)
                                    <tr>
                                        <td class="fw-semibold">{{ $form->title }}</td>
                                        <td>{{ $form->subtitle ?? '—' }}</td>
                                        <td>
                                            @php $fieldCount = count($form->fields ?? []); @endphp
                                            <span class="badge bg-info">{{ $fieldCount }} field{{ $fieldCount !== 1 ? 's' : '' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $form->submissions_count }}</span>
                                        </td>
                                        <td>
                                            @if ($form->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-outline-danger btn-sm copyFormLink" data-clipboard-text="{{ route('form.show', $form) }}">
                                                <i class="fe fe-copy"></i> Copy Link
                                            </a>
                                            <a href="{{ route('admin.dynamic-forms.show', $form) }}" class="btn btn-outline-info btn-sm">
                                                <i class="fe fe-eye"></i> View
                                            </a>
                                            <a href="{{ route('admin.dynamic-forms.edit', $form) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fe fe-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.dynamic-forms.toggle-active', $form) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm {{ $form->is_active ? 'btn-warning' : 'btn-success' }}">
                                                    {{ $form->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.dynamic-forms.destroy', $form) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this form and all submissions?')">
                                                    <i class="fe fe-trash-2"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No dynamic forms found. Create your first form.</td>
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
