@extends('layouts.highboard-dashboard')

@section('title', 'Form Submissions - ' . $dynamicForm->title)
@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ $dynamicForm->title }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('highboard.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('highboard.dynamic-forms.index') }}">Dynamic Forms</a></li>
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
                        <input type="text" class="form-control form-control-sm" value="{{ $dynamicForm->getFormUrl() }}" id="formLink" readonly>
                        <button class="btn btn-outline-primary btn-sm" type="button" onclick="copyLink()">
                            <i class="fe fe-copy"></i>
                        </button>
                    </div>
                    
                    <p class="mt-3 mb-1"><strong>Share Submissions Link:</strong></p>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" value="{{ $dynamicForm->getSharedSubmissionsUrl() }}" id="submissionsLink" readonly>
                        <button class="btn btn-outline-info btn-sm" type="button" onclick="copySubmissionsLink()">
                            <i class="fe fe-share-2"></i>
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
                    @if($submissions->total() > 0)
                        <a href="{{ route('highboard.dynamic-forms.export', $dynamicForm) }}" class="btn btn-success btn-sm">
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
                    <form method="GET" action="{{ route('highboard.dynamic-forms.show', $dynamicForm) }}" class="mb-4">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input type="text" name="search" class="form-control" placeholder="Search all fields..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-4">
                                <select name="payment_status" class="form-select">
                                    <option value="">All Payment Statuses</option>
                                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </form>
                    @if($submissions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped border-bottom">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Submitted At</th>
                                        <th>Accepted By</th>
                                        <th>Payment</th>
                                        @foreach($orderedFields as $fieldName => $fieldConfig)
                                            <th>{{ $fieldConfig['label'] }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($submissions as $submission)
                                        <tr class="{{ $submission->is_payed ? 'table-warning' : '' }}">
                                            <td>{{ $submission->id }}</td>
                                            <td class="text-nowrap">{{ $submission->created_at->format('M d, Y H:i') }}</td>
                                            <td class="text-nowrap">
                                                @if($submission->accepted_by)
                                                    <span class="badge bg-success-transparent rounded-pill text-success p-2 px-3">
                                                        <i class="fe fe-user-check me-1"></i> {{ $submission->accepted_by }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <form action="{{ route('highboard.dynamic-forms.submissions.toggle-payment', $submission) }}" method="POST" class="d-inline toggle-payment-form">
                                                    @csrf
                                                    <button type="button" class="btn btn-sm btn-{{ $submission->is_payed ? 'warning' : 'outline-warning' }} btn-toggle-payment" data-status="{{ $submission->is_payed ? 'unpaid' : 'paid' }}">
                                                        <i class="fe fe-dollar-sign"></i> {{ $submission->is_payed ? 'Paid' : 'Mark as Paid' }}
                                                    </button>
                                                </form>
                                            </td>
                                            @foreach($orderedFields as $fieldName => $fieldConfig)
                                                <td>
                                                    @php $value = $submission->data[$fieldName] ?? '—'; @endphp
                                                    @if(is_array($value))
                                                        {{ implode(', ', $value) }}
                                                    @elseif($fieldConfig['type'] === 'file' && $value !== '—')
                                                        <a href="{{ asset('storage/' . $value) }}" target="_blank" class="btn btn-sm btn-outline-info" title="View Uploaded Image">
                                                            <i class="fe fe-image"></i> View Image
                                                        </a>
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </td>
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

    function copySubmissionsLink() {
        const input = document.getElementById('submissionsLink');
        input.select();
        navigator.clipboard.writeText(input.value);
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'Shareable submissions link copied to clipboard',
            timer: 2000
        });
    }

    document.querySelectorAll('.btn-toggle-payment').forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('.toggle-payment-form');
            const targetStatus = this.getAttribute('data-status');
            
            Swal.fire({
                title: 'Confirm Payment Status',
                text: `Are you sure you want to mark this submission as ${targetStatus}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, change it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection
