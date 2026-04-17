@extends('layouts.highboard-dashboard')

@section('content')
    @include('highboard.interactive_quizzes.form', ['quiz' => $quiz])
@endsection
