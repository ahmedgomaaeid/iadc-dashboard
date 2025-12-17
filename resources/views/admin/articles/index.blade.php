@extends('layouts.admin-dashboard')

@section('title', 'Articles Management')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Articles Management</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Articles</li>
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
                    <h3 class="card-title">All Articles</h3>
                    <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>Add New Article
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($articles as $article)
                                    <tr>
                                        <td>{{ $article->id }}</td>
                                        <td>
                                            @if($article->image)
                                                <img src="{{ asset('storage/' . $article->image) }}" 
                                                     alt="{{ $article->name }}" 
                                                     class="rounded"
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <span class="text-muted">No image</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $article->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $article->excerpt }}</small>
                                        </td>
                                        <td>
                                            @switch($article->type)
                                                @case('drilling')
                                                    <span class="badge bg-primary">Drilling</span>
                                                    @break
                                                @case('production')
                                                    <span class="badge bg-success">Production</span>
                                                    @break
                                                @case('reservoir')
                                                    <span class="badge bg-info">Reservoir</span>
                                                    @break
                                                @case('sustainability')
                                                    <span class="badge bg-warning text-dark">Sustainability</span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td>{{ $article->author }}</td>
                                        <td>
                                            @if($article->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $article->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.articles.edit', $article) }}" 
                                                   class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fe fe-edit"></i>
                                                </a>
                                                
                                                <form action="{{ route('admin.articles.toggle-status', $article) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" 
                                                            title="Toggle Status">
                                                        <i class="fe fe-{{ $article->is_active ? 'eye-off' : 'eye' }}"></i>
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('admin.articles.destroy', $article) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this article?');">
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
                                        <td colspan="8" class="text-center">No articles found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $articles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
