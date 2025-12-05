@extends('layouts.admin-dashboard')

@section('title', 'Form Submissions - ' . $dynamicForm->title)
@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ $dynamicForm->title }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.dynamic-forms.index') }}">Dynamic Forms</a></li>
                <li class="breadcrumb-item active" aria-current="page">Submissions</li>
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
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Form Details</h3>
                </div>
                <div class="card-body">
                    <p><strong>Title:</strong> {{ $dynamicForm->title }}</p>
                    <p><strong>Subtitle:</strong> {{ $dynamicForm->subtitle ?? '—' }}</p>
                    <p><strong>Status:</strong> 
                        @if($dynamicForm->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </p>
                    <p><strong>Total Submissions:</strong> {{ $submissions->total() }}</p>
                    <p><strong>Form Link:</strong></p>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" value="{{ route('form.show', $dynamicForm) }}" id="formLink" readonly>
                        <button class="btn btn-outline-primary btn-sm" type="button" onclick="copyLink()">
                            <i class="fe fe-copy"></i>
                        </button>
                    </div>
                    <hr>
                    <p class="mb-2"><strong>Selected Fields:</strong></p>
                    @php $orderedFields = $dynamicForm->getOrderedFields(); @endphp
                    @foreach($orderedFields as $fieldName => $fieldConfig)
                        <span class="badge bg-info me-1 mb-1">
                            <i class="fas {{ $fieldConfig['icon'] }} me-1"></i>{{ $fieldConfig['label'] }}
                        </span>
                    @endforeach
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.dynamic-forms.edit', $dynamicForm) }}" class="btn btn-primary btn-sm">
                        <i class="fe fe-edit me-1"></i>Edit Form
                    </a>
                    @if($submissions->total() > 0)
                        <a href="{{ route('admin.dynamic-forms.export', $dynamicForm) }}" class="btn btn-success btn-sm">
                            <i class="fe fe-download me-1"></i>Export Excel
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Submissions ({{ $submissions->total() }})</h3>
                </div>
                <div class="card-body">
                    @if($submissions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped border-bottom">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Submitted At</th>
                                        @foreach($orderedFields as $fieldName => $fieldConfig)
                                            <th>{{ $fieldConfig['label'] }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($submissions as $submission)
                                        <tr>
                                            <td>{{ $submission->id }}</td>
                                            <td class="text-nowrap">{{ $submission->created_at->format('M d, Y H:i') }}</td>
                                            @foreach($orderedFields as $fieldName => $fieldConfig)
                                                <td>{{ $submission->data[$fieldName] ?? '—' }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            {{ $submissions->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fe fe-inbox text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mt-3">No submissions yet. Share the form link to start collecting responses.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function copyLink() {
        const input = document.getElementById('formLink');
        input.select();
        navigator.clipboard.writeText(input.value);
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'Form link copied to clipboard',
            timer: 2000
        });
    }
</script>
@endsection
