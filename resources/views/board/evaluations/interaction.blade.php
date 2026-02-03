@extends('layouts.board-dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-dark text-white">
                <div class="card-header border-0 bg-transparent">
                    <h3 class="card-title text-primary"><i class="fas fa-users-cog me-2"></i>User Interaction Evaluation</h3>
                    <p class="text-muted mb-0">Session: {{ $session->title }}</p>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success bg-success text-white border-0">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('board.evaluations.interaction.store', $session->id) }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle">
                                <thead>
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
                                            <td>
                                                <!-- Assuming committee role or just Member -->
                                                <span class="badge bg-info">Member</span>
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
                                            <td colspan="3" class="text-center text-muted py-4">
                                                No users have joined this session yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        @if($users->count() > 0)
                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-2"></i>Save Evaluations
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
        fetch(`{{ route('board.evaluations.participants', $session->id) }}`)
            .then(response => response.json())
            .then(data => {
                const newUsers = data.users.filter(user => !currentUserIds.includes(user.id));
                
                if (newUsers.length > 0) {
                    // Add new users to the table
                    const tbody = document.querySelector('table tbody');
                    const currentIndex = currentUserIds.length;
                    
                    newUsers.forEach((user, idx) => {
                        const row = createUserRow(user, currentIndex + idx);
                        tbody.insertAdjacentHTML('beforeend', row);
                        currentUserIds.push(user.id);
                    });
                    
                    // Flash notification
                    showNotification(`${newUsers.length} new member(s) joined!`);
                }
            })
            .catch(err => console.error('Error fetching participants:', err));
    }

    function createUserRow(user, index) {
        const avatar = user.image 
            ? `<img src="/storage/${user.image}" alt="${user.name}" class="rounded-circle me-3" width="40" height="40">`
            : `<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                   <span class="text-white">${user.name.charAt(0)}</span>
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
                    <span class="badge bg-info">Member</span>
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
