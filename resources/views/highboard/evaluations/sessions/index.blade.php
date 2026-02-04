@extends('layouts.highboard-dashboard')

@section('content')
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-header border-bottom">
                <h3 class="card-title">Session Evaluations</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom" id="basic-datatable">
                        <thead>
                            <tr>
                                <th class="wd-15p border-bottom-0">Session Title</th>
                                <th class="wd-15p border-bottom-0">Committee</th>
                                <th class="wd-20p border-bottom-0">Date</th>
                                <th class="wd-15p border-bottom-0">Avg Rating (%)</th>
                                <th class="wd-10p border-bottom-0">Evaluations</th>
                                <th class="wd-25p border-bottom-0">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $session)
                            <tr>
                                <td>{{ $session->title }}</td>
                                <td>{{ $session->committee ? $session->committee->name : 'N/A' }}</td>
                                <td>{{ $session->start_time->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="progress progress-md mb-3">
                                        <div class="progress-bar bg-primary" style="width: {{ $session->average_percentage }}%">{{ $session->average_percentage }}%</div>
                                    </div>
                                </td>
                                <td>{{ $session->evaluations_count }}</td>
                                <td>
                                    <a href="{{ route('highboard.evaluations.sessions.show', $session->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fe fe-eye me-2"></i>Show Evaluations
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $sessions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
