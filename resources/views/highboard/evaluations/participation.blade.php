@extends('layouts.highboard-dashboard')

@section('content')
<div class="container-fluid">
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">Participation Evaluation</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('highboard.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('highboard.evaluations.index') }}">Evaluations</a></li>
                <li class="breadcrumb-item active" aria-current="page">Participation</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title"><i class="fe fe-users me-2"></i>Evaluate Committee Participation</h3>
                    <div class="card-options">
                        <span class="text-muted">Score Range: 1-10</span>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('highboard.evaluations.participation.store') }}" method="POST">
                        @csrf
                        
                        <!-- Event Details -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="committee_id" class="form-label fw-bold">
                                    <i class="fe fe-briefcase me-1"></i>Committee <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="committee_id" name="committee_id" required onchange="this.form.submit()">
                                    @foreach($committees as $committee)
                                        <option value="{{ $committee->id }}" {{ $selectedCommitteeId == $committee->id ? 'selected' : '' }}>
                                            {{ $committee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="event_name" class="form-label fw-bold">
                                    <i class="fe fe-tag me-1"></i>Event/Activity Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="event_name" name="event_name" 
                                       placeholder="e.g., Monthly Meeting, Workshop, Training Session" 
                                       value="{{ old('event_name') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="evaluation_date" class="form-label fw-bold">
                                    <i class="fe fe-calendar me-1"></i>Evaluation Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="evaluation_date" name="evaluation_date" 
                                       value="{{ old('evaluation_date', date('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Members Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Member</th>
                                        <th>Participation Score (1-10)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($user->image)
                                                        <img src="{{ asset('storage/' . $user->image) }}" alt="{{ $user->name }}" class="rounded-circle me-3" width="40" height="40">
                                                    @else
                                                        <div class="rounded-circle bg-primary-transparent d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                            <span class="text-primary fw-bold">{{ substr($user->name, 0, 1) }}</span>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold">{{ $user->name }}</div>
                                                        <small class="text-muted">{{ $user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="width: 350px;">
                                                <input type="hidden" name="evaluations[{{ $loop->index }}][user_id]" value="{{ $user->id }}">
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2 text-muted">1</span>
                                                    <input type="range" class="form-range flex-grow-1" min="1" max="10" step="0.5" 
                                                           id="scoreInput_{{ $user->id }}"
                                                           name="evaluations[{{ $loop->index }}][score]" 
                                                           value="5"
                                                           oninput="document.getElementById('scoreVal_{{ $user->id }}').innerText = this.value">
                                                    <span class="ms-2 text-muted">10</span>
                                                    <span class="badge bg-success ms-3" style="width: 45px; font-size: 1rem;" id="scoreVal_{{ $user->id }}">
                                                        5
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-5">
                                                <i class="fe fe-users fs-50 d-block mb-3"></i>
                                                No members found in this committee.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        @if($users->count() > 0)
                            <div class="text-end mt-4">
                                <a href="{{ route('highboard.evaluations.index') }}" class="btn btn-outline-secondary me-2">
                                    <i class="fe fe-x me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-success px-4">
                                    <i class="fe fe-save me-2"></i>Save Participation Scores
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Prevent form submit on committee change, just reload with new committee
document.getElementById('committee_id').addEventListener('change', function() {
    window.location.href = '{{ route("highboard.evaluations.participation") }}?committee_id=' + this.value;
});
</script>
@endsection
