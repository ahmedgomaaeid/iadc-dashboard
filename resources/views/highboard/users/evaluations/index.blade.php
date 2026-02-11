@extends('layouts.highboard-dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-2 text-gray-800">User Evaluations</h1>
            <p class="mb-4">View and track user performance across committees.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('highboard.users.evaluations.index') }}" method="GET" class="form-inline">
                <div class="form-group mb-2 mr-3">
                    <label for="committee_id" class="mr-2">Committee:</label>
                    <select name="committee_id" id="committee_id" class="form-control">
                        <option value="">All Committees</option>
                        @foreach($committees as $committee)
                            <option value="{{ $committee->id }}" {{ request('committee_id') == $committee->id ? 'selected' : '' }}>
                                {{ $committee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group mb-2 mr-3">
                    <label for="search" class="mr-2">Search:</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="User Name or Email" value="{{ request('search') }}">
                </div>
                
                <button type="submit" class="btn btn-primary mb-2">Filter</button>
            </form>
        </div>
    </div>

    <!-- Evaluations Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Evaluations List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Committee</th>
                            <th>Meetings (10%)</th>
                            <th>Participation (%)</th>
                            <th>Quizzes (%)</th>
                            <th>Tasks (10%)</th>
                            <th>Total (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            @foreach($user->committees->whereIn('id', $committees->pluck('id')) as $committee)
                                @if(request('committee_id') && $committee->id != request('committee_id'))
                                    @continue
                                @endif
                                
                                @php
                                    $stats = $committeeStats[$committee->id] ?? ['max_meeting_score' => 0, 'max_task_score' => 0, 'max_quiz_score' => 0];
                                    
                                    // 1. Meetings (Joining + Interaction)
                                    $meetingScore = $user->userEvaluations()
                                        ->where('committee_id', $committee->id)
                                        ->whereIn('type', ['joining_meeting', 'interaction'])
                                        ->sum('score');

                                    // Meeting %: (Score / (Sessions * 10)) * 10   -> effectively (Score / Sessions)
                                    // Requirement: "count from user_evaluations table ... / num of sessions in this committee * 10"
                                    // Wait, if it's "* 10" at the end, it implies scaling to 10?
                                    // "get avarage of that ... / num of sessions ... * 10"
                                    // I'll assume they want a score out of 10.
                                    
                                    $meetingMax = $stats['max_meeting_score']; // Sessions * 10
                                    $meetingPercentage = $meetingMax > 0 ? ($meetingScore / $meetingMax) * 100 : 0;
                                    $meetingLabel = round($meetingPercentage, 1); // This is 0-100%
                                    // But column header says "10%", so maybe scale to 10?
                                    // Let's stick to percentage as "persentage in lable".
                                    
                                    
                                    // 2. Participation
                                    // "count of participation score / count of max score for this committee_id"
                                    // Wait, "count of max score" might mean "Sum of max_score column in user_evaluations"?
                                    // Yes, that makes sense for dynamic participation items.
                                    $participationScore = $user->userEvaluations()
                                        ->where('committee_id', $committee->id)
                                        ->where('type', 'participation')
                                        ->sum('score');
                                        
                                    $participationMax = $user->userEvaluations()
                                        ->where('committee_id', $committee->id)
                                        ->where('type', 'participation')
                                        ->sum('max_score');
                                        
                                    $participationPercentage = $participationMax > 0 ? ($participationScore / $participationMax) * 100 : 0;
                                    

                                    // 3. Quizzes
                                    // "count of quiz_submission score / num of quizzes * num of question in each quiz"
                                    // Denominator is Total Questions.
                                    // Numerator is User's Total Quiz Score.
                                    $quizScore = $user->userEvaluations()
                                        ->where('committee_id', $committee->id)
                                        ->where('type', 'quiz')
                                        ->sum('score');
                                        
                                    $quizMax = $stats['max_quiz_score'];
                                    $quizPercentage = $quizMax > 0 ? ($quizScore / $quizMax) * 100 : 0;
                                    
                                    
                                    // 4. Tasks
                                    // "count of task_submission score ... / num of tasks ... * 10"
                                    $taskScore = $user->userEvaluations()
                                        ->where('committee_id', $committee->id)
                                        ->where('type', 'task_submission')
                                        ->sum('score');
                                        
                                    $taskMax = $stats['max_task_score'];
                                    $taskPercentage = $taskMax > 0 ? ($taskScore / $taskMax) * 100 : 0;


                                    // Total Percentage
                                    // "get average of that" -> Average of these 4 percentages? Or specific weights?
                                    // "get avarage of that" suggests (P1 + P2 + P3 + P4) / 4.
                                    $totalPercentage = ($meetingPercentage + $participationPercentage + $quizPercentage + $taskPercentage) / 4;
                                    
                                @endphp
                                
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->phone }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $committee->name }}</td>
                                    <td>
                                        <div class="progress mb-1" style="height: 20px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $meetingPercentage }}%;" aria-valuenow="{{ $meetingPercentage }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ round($meetingPercentage, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress mb-1" style="height: 20px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $participationPercentage }}%;" aria-valuenow="{{ $participationPercentage }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ round($participationPercentage, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress mb-1" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $quizPercentage }}%;" aria-valuenow="{{ $quizPercentage }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ round($quizPercentage, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress mb-1" style="height: 20px;">
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $taskPercentage }}%;" aria-valuenow="{{ $taskPercentage }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ round($taskPercentage, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="font-weight-bold">
                                        {{ round($totalPercentage, 1) }}%
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
