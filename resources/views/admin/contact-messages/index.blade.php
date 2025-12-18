@extends('layouts.admin-dashboard')

@section('title', 'Contact Messages')

@section('content')
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">Contact Messages</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Contact Messages</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <!-- ROW -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        All Messages
                        @if($unreadCount > 0)
                            <span class="badge bg-danger ms-2">{{ $unreadCount }} unread</span>
                        @endif
                    </h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="5%">Status</th>
                                    <th width="15%">Name</th>
                                    <th width="20%">Email</th>
                                    <th width="35%">Message</th>
                                    <th width="10%">Date</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($messages as $message)
                                    <tr class="{{ !$message->is_read ? 'table-warning' : '' }}">
                                        <td>{{ $message->id }}</td>
                                        <td class="text-center">
                                            @if(!$message->is_read)
                                                <span class="badge bg-warning">Unread</span>
                                            @else
                                                <span class="badge bg-secondary">Read</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $message->name }}</strong>
                                        </td>
                                        <td>
                                            <a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
                                        </td>
                                        <td>
                                            {{ Str::limit($message->message, 80) }}
                                        </td>
                                        <td>{{ $message->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.contact-messages.show', $message->id) }}" 
                                                   class="btn btn-sm btn-primary" 
                                                   title="View">
                                                    <i class="fe fe-eye"></i>
                                                </a>
                                                @if(!$message->is_read)
                                                    <form action="{{ route('admin.contact-messages.mark-read', $message->id) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="Mark as Read">
                                                            <i class="fe fe-check"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.contact-messages.mark-unread', $message->id) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning" title="Mark as Unread">
                                                            <i class="fe fe-mail"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('admin.contact-messages.destroy', $message->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this message?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fe fe-inbox mb-2" style="font-size: 48px;"></i>
                                                <p class="mb-0">No messages yet.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $messages->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ROW END -->
@endsection
