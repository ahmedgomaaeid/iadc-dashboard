@extends('layouts.admin-dashboard')

@section('title', 'View Message')

@section('content')
    <!-- PAGE-HEADER -->
    <div class="page-header">
        <h1 class="page-title">View Message</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.contact-messages.index') }}">Contact Messages</a></li>
                <li class="breadcrumb-item active" aria-current="page">View Message</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->

    <!-- ROW -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Message Details</h3>
                    <div class="btn-group">
                        @if(!$message->is_read)
                            <form action="{{ route('admin.contact-messages.mark-read', $message->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fe fe-check me-1"></i> Mark as Read
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.contact-messages.mark-unread', $message->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning">
                                    <i class="fe fe-mail me-1"></i> Mark as Unread
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('admin.contact-messages.destroy', $message->id) }}" 
                              method="POST" 
                              class="d-inline ms-2"
                              onsubmit="return confirm('Are you sure you want to delete this message?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fe fe-trash-2 me-1"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label text-muted">From</label>
                        <h4 class="mb-0">{{ $message->name }}</h4>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-muted">Email</label>
                        <p class="mb-0">
                            <a href="mailto:{{ $message->email }}" class="fs-16">
                                <i class="fe fe-mail me-2"></i>{{ $message->email }}
                            </a>
                        </p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-muted">Received</label>
                        <p class="mb-0">{{ $message->created_at->format('F d, Y \a\t H:i A') }}</p>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Message</label>
                        <div class="p-4 bg-light rounded">
                            <p class="mb-0" style="white-space: pre-wrap;">{{ $message->message }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-secondary">
                        <i class="fe fe-arrow-left me-1"></i> Back to Messages
                    </a>
                    <a href="mailto:{{ $message->email }}?subject=Re: Your message to IADC" class="btn btn-primary">
                        <i class="fe fe-send me-1"></i> Reply via Email
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Status</h3>
                </div>
                <div class="card-body text-center">
                    @if($message->is_read)
                        <div class="text-success mb-2">
                            <i class="fe fe-check-circle" style="font-size: 48px;"></i>
                        </div>
                        <h5 class="text-success">Read</h5>
                    @else
                        <div class="text-warning mb-2">
                            <i class="fe fe-mail" style="font-size: 48px;"></i>
                        </div>
                        <h5 class="text-warning">Unread</h5>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- ROW END -->
@endsection
