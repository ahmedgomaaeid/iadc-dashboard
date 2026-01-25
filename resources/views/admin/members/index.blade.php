@extends('layouts.admin-dashboard')

@section('title', 'Members Management')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Members Management</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Members</li>
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
                    <h3 class="card-title">All Members</h3>
                    <div>
                        <button type="button" class="btn btn-success me-2 mb-2" id="bulk-activate" style="display: none;">
                            <i class="fe fe-check me-2"></i>Activate Selected
                        </button>
                        <button type="button" class="btn btn-danger me-2 mb-2" id="bulk-deactivate" style="display: none;">
                            <i class="fe fe-x me-2"></i>Deactivate Selected
                        </button>
                        <a href="{{ route('admin.members.export') }}" class="btn btn-success me-2 mb-2">
                            <i class="fe fe-download me-2"></i>Export Excel
                        </a>
                        <a href="{{ route('admin.members.create') }}" class="btn btn-primary mb-2">
                            <i class="fe fe-plus me-2"></i>Add New Member
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form action="{{ route('admin.members.index') }}" method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="committee_id" class="form-select">
                                    <option value="">All Committees</option>
                                    @foreach($committees as $committee)
                                        <option value="{{ $committee->id }}" {{ request('committee_id') == $committee->id ? 'selected' : '' }}>
                                            {{ $committee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fe fe-search me-2"></i>Search
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">
                                        <input type="checkbox" class="" id="select-all">
                                    </th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>University</th>
                                    <th>Faculty</th>
                                    <th>Academic Year</th>
                                    <th>Committees</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($members as $member)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="member-checkbox" value="{{ $member->id }}">
                                        </td>
                                        <td>{{ $member->id }}</td>
                                        <td>{{ $member->name }}</td>
                                        <td>{{ $member->email }}</td>
                                        <td>{{ $member->phone ?? 'N/A' }}</td>
                                        <td>{{ $member->university ?? 'N/A' }}</td>
                                        <td>{{ $member->faculty ?? 'N/A' }}</td>
                                        <td>{{ $member->academic_year ?? 'N/A' }}</td>
                                        <td>
                                            @forelse($member->committees as $committee)
                                                <span class="badge bg-info">{{ $committee->name }}</span>
                                            @empty
                                                <span class="badge bg-secondary">No Committee</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            @if($member->is_active)
                                                <span class="badge bg-success status-badge" data-id="{{ $member->id }}">Active</span>
                                            @else
                                                <span class="badge bg-danger status-badge" data-id="{{ $member->id }}">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $member->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.members.edit', $member) }}" 
                                                   class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fe fe-edit"></i>
                                                </a>
                                                
                                                <button type="button" class="btn btn-sm btn-warning toggle-status-btn" 
                                                        data-id="{{ $member->id }}"
                                                        data-url="{{ route('admin.members.toggle-status', $member) }}"
                                                        title="Toggle Status">
                                                    <i class="fe fe-{{ $member->is_active ? 'eye-off' : 'eye' }} status-icon" data-id="{{ $member->id }}"></i>
                                                </button>
                                                
                                                <form action="{{ route('admin.members.destroy', $member) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this member?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            title="Delete">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center">No members found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $members->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.member-checkbox');
        const bulkActivateBtn = document.getElementById('bulk-activate');
        const bulkDeactivateBtn = document.getElementById('bulk-deactivate');

        // Toggle Status AJAX
        document.querySelectorAll('.toggle-status-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const url = this.dataset.url;
                const icon = document.querySelector(`.status-icon[data-id="${id}"]`);
                const badge = document.querySelector(`.status-badge[data-id="${id}"]`);

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // Update Icon
                        if(data.is_active) {
                            icon.classList.remove('fe-eye');
                            icon.classList.add('fe-eye-off');
                            badge.classList.remove('bg-danger');
                            badge.classList.add('bg-success');
                            badge.textContent = 'Active';
                        } else {
                            icon.classList.remove('fe-eye-off');
                            icon.classList.add('fe-eye');
                            badge.classList.remove('bg-success');
                            badge.classList.add('bg-danger');
                            badge.textContent = 'Inactive';
                        }
                        
                        // Optional: Show toast
                        // alert(data.message); 
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });

        // Bulk Selection Logic
        function updateBulkButtons() {
            const selectedCount = document.querySelectorAll('.member-checkbox:checked').length;
            if(selectedCount > 0) {
                bulkActivateBtn.style.display = 'inline-block';
                bulkDeactivateBtn.style.display = 'inline-block';
            } else {
                bulkActivateBtn.style.display = 'none';
                bulkDeactivateBtn.style.display = 'none';
            }
        }

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkButtons();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkButtons);
        });

        // Bulk Actions
        function performBulkAction(active) {
            const selectedIds = Array.from(document.querySelectorAll('.member-checkbox:checked')).map(cb => cb.value);
            
            if(selectedIds.length === 0) return;

            if(!confirm(`Are you sure you want to ${active ? 'activate' : 'deactivate'} ${selectedIds.length} members?`)) return;

            fetch('{{ route("admin.members.bulk-status") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ids: selectedIds,
                    active: active
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload(); // Reload to reflect changes
                }
            })
            .catch(error => console.error('Error:', error));
        }

        bulkActivateBtn.addEventListener('click', () => performBulkAction(true));
        bulkDeactivateBtn.addEventListener('click', () => performBulkAction(false));
    });
</script>
@endsection
