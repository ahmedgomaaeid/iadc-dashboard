@extends('layouts.board-dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-dark text-white">
                <div class="card-header border-0 bg-transparent">
                    <h3 class="card-title text-success"><i class="fas fa-chart-line me-2"></i>Committee Participation Evaluation</h3>
                    <p class="text-muted mb-0">Evaluate members of your committee (Scale 1-10)</p>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success bg-success text-white border-0">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('board.evaluations.participation.store') }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Current Participation Score (1-10)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        @php
                                            // Ideally pass this from controller, but query here for quickness or use relationship
                                            $existingEval = \App\Models\UserEvaluation::where('user_id', $user->id)
                                                ->where('committee_id', auth('board')->user()->committee_id)
                                                ->where('type', 'participation')
                                                ->first();
                                            $score = $existingEval ? $existingEval->score : 5;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($user->image)
                                                        <img src="{{ asset('storage/' . $user->image) }}" alt="{{ $user->name }}" class="rounded-circle me-3" width="40" height="40">
                                                    @else
                                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                            <span class="text-white">{{ substr($user->name, 0, 1) }}</span>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold">{{ $user->name }}</div>
                                                        <small class="text-muted">{{ $user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="width: 300px;">
                                                <input type="hidden" name="evaluations[{{ $loop->index }}][user_id]" value="{{ $user->id }}">
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2 text-muted">1</span>
                                                    <input type="range" class="form-range flex-grow-1" min="1" max="10" step="0.5" 
                                                           id="scoreInput_{{ $user->id }}"
                                                           name="evaluations[{{ $loop->index }}][score]" 
                                                           value="{{ $score }}"
                                                           oninput="document.getElementById('scoreVal_{{ $user->id }}').innerText = this.value">
                                                    <span class="ms-2 text-muted">10</span>
                                                    <span class="badge bg-success ms-3" style="width: 40px; font-size: 1rem;" id="scoreVal_{{ $user->id }}">
                                                        {{ $score }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">
                                                No members found in your committee.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        @if($users->count() > 0)
                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-success px-4">
                                    <i class="fas fa-save me-2"></i>Save Participation Scores
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
