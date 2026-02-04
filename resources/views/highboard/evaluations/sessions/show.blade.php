@extends('layouts.highboard-dashboard')

@section('content')
<div class="page-header">
    <h1 class="page-title">Evaluations for: {{ $googleSession->title }}</h1>
    <div>
        <a href="{{ route('highboard.evaluations.sessions.index') }}" class="btn btn-default">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-header border-bottom">
                <h3 class="card-title">User Feedback</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom" id="responsive-datatable">
                        <thead>
                            <tr>
                                <th class="wd-20p border-bottom-0">User</th>
                                <th class="wd-15p border-bottom-0">Rating</th>
                                <th class="wd-40p border-bottom-0">Message</th>
                                <th class="wd-15p border-bottom-0">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($evaluations as $evaluation)
                            <tr>
                                <td>
                                    <div class="d-flex">
                                        <span class="avatar avatar-md brround me-3" style="background-image: url('{{ $evaluation->user_image ?? asset('assets/images/users/default.jpg') }}')"></span>
                                        <div class="me-3 mt-0 mt-sm-1 d-block">
                                            <h6 class="mb-1 fs-14">{{ $evaluation->user_name }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="rating-stars-display">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fe fe-star {{ $i <= $evaluation->rating ? 'text-warning' : 'text-muted' }}"></i>
                                        @endfor
                                        <span class="ms-2">({{ $evaluation->rating }}/5)</span>
                                    </div>
                                </td>
                                <td>
                                    @if($evaluation->message)
                                        {{Str::limit($evaluation->message, 100)}}
                                        @if(strlen($evaluation->message) > 100)
                                            <button type="button" class="btn btn-sm btn-link p-0" data-bs-toggle="modal" data-bs-target="#messageModal{{$evaluation->id}}">Read More</button>
                                            
                                            <!-- Message Modal -->
                                            <div class="modal fade" id="messageModal{{$evaluation->id}}" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Evaluate Message</h5>
                                                            <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">×</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>{{ $evaluation->message }}</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted">No message provided.</span>
                                    @endif
                                </td>
                                <td>{{ $evaluation->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No evaluations found for this session.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .text-warning { color: #f1c40f !important; }
</style>
@endsection
