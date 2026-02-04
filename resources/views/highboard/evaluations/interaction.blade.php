@extends('layouts.highboard-dashboard')

@section('content')
<div class="container-fluid">
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">User Interaction Evaluation</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('highboard.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('highboard.sessions.index') }}">Sessions</a></li>
                <li class="breadcrumb-item active" aria-current="page">Interaction Evaluation</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title"><i class="fe fe-users me-2"></i>Evaluate Member Interactions</h3>
                    <div class="card-options">
                        <span class="badge bg-primary-transparent text-primary">{{ $session->title }}</span>
                        @if($session->committee)
                            <span class="badge bg-info-transparent text-info ms-2">{{ $session->committee->name }}</span>
                        @endif
                        @if($session->session_url)
                            <a href="{{ $session->session_url }}" target="_blank" class="btn btn-sm btn-success ms-3 pulse-button">
                                <i class="fe fe-video me-1"></i> Join Meeting
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('highboard.evaluations.interaction.store', $session->id) }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Interaction Score (1-5)</th>
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
                                            <td>
                                                <span class="badge bg-info-transparent text-info">Member</span>
                                            </td>
                                            <td style="width: 200px;">
                                                <input type="hidden" name="evaluations[{{ $loop->index }}][user_id]" value="{{ $user->id }}">
                                                <div class="d-flex align-items-center">
                                                    <input type="range" class="form-range me-2" min="1" max="5" step="0.5" 
                                                           id="scoreInput_{{ $user->id }}"
                                                           name="evaluations[{{ $loop->index }}][score]" 
                                                           value="{{ isset($evaluations[$user->id]) ? $evaluations[$user->id]->score : 5 }}"
                                                           oninput="document.getElementById('scoreVal_{{ $user->id }}').innerText = this.value">
                                                    <span class="badge bg-primary ms-2" style="width: 40px;" id="scoreVal_{{ $user->id }}">
                                                        {{ isset($evaluations[$user->id]) ? $evaluations[$user->id]->score : 5 }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-5">
                                                <i class="fe fe-users fs-50 d-block mb-3"></i>
                                                No users have joined this session yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        @if($users->count() > 0)
                            <div class="text-end mt-3 save-btn-container">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fe fe-save me-2"></i>Save Evaluations
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
    const sessionId = {{ $session->id }};
    let currentUserIds = @json($users->pluck('id')->toArray());

    function updateParticipants() {
        fetch(`{{ route('highboard.evaluations.participants', $session->id) }}`)
            .then(response => response.json())
            .then(data => {
                const newUsers = data.users.filter(user => !currentUserIds.includes(user.id));
                
                if (newUsers.length > 0) {
                    const tbody = document.querySelector('table tbody');
                    
                    // Remove "no users" message row if it exists
                    const emptyRow = tbody.querySelector('tr td[colspan="3"]');
                    if (emptyRow) {
                        emptyRow.closest('tr').remove();
                    }
                    
                    const currentIndex = currentUserIds.length;
                    
                    newUsers.forEach((user, idx) => {
                        const row = createUserRow(user, currentIndex + idx);
                        tbody.insertAdjacentHTML('beforeend', row);
                        currentUserIds.push(user.id);
                    });
                    
                    // Show save button if not already visible
                    let saveButtonContainer = document.querySelector('.save-btn-container');
                    if (!saveButtonContainer) {
                        const formElement = document.querySelector('form');
                        const buttonHtml = `
                            <div class="text-end mt-3 save-btn-container">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fe fe-save me-2"></i>Save Evaluations
                                </button>
                            </div>
                        `;
                        formElement.insertAdjacentHTML('beforeend', buttonHtml);
                    }
                    
                    // Flash notification
                    showNotification(`${newUsers.length} new member(s) joined!`);
                }
            })
            .catch(err => console.error('Error fetching participants:', err));
    }

    function createUserRow(user, index) {
        const avatar = user.image 
            ? `<img src="/storage/${user.image}" alt="${user.name}" class="rounded-circle me-3" width="40" height="40">`
            : `<div class="rounded-circle bg-primary-transparent d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                   <span class="text-primary fw-bold">${user.name.charAt(0)}</span>
               </div>`;
        
        return `
            <tr class="user-row-new">
                <td>
                    <div class="d-flex align-items-center">
                        ${avatar}
                        <div>
                            <div class="fw-bold">${user.name}</div>
                            <small class="text-muted">${user.email}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-info-transparent text-info">Member</span>
                </td>
                <td style="width: 200px;">
                    <input type="hidden" name="evaluations[${index}][user_id]" value="${user.id}">
                    <div class="d-flex align-items-center">
                        <input type="range" class="form-range me-2" min="1" max="5" step="0.5" 
                               id="scoreInput_${user.id}"
                               name="evaluations[${index}][score]" 
                               value="${user.score}"
                               oninput="document.getElementById('scoreVal_${user.id}').innerText = this.value">
                        <span class="badge bg-primary ms-2" style="width: 40px;" id="scoreVal_${user.id}">
                            ${user.score}
                        </span>
                    </div>
                </td>
            </tr>
        `;
    }

    function showNotification(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
        alert.style.zIndex = '9999';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }

    // Poll every 10 seconds
    setInterval(updateParticipants, 10000);
    
    // Highlight new rows temporarily
    document.addEventListener('DOMContentLoaded', () => {
        const style = document.createElement('style');
        style.textContent = `
            .user-row-new {
                animation: highlightRow 2s ease-in-out;
            }
            @keyframes highlightRow {
                0% { background-color: rgba(40, 167, 69, 0.3); }
                100% { background-color: transparent; }
            }
        `;
        document.head.appendChild(style);
    });
</script>
@endsection
