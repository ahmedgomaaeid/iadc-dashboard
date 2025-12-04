@extends('layouts.highboard-dashboard')

@section('content')
    @include('highboard.quizzes.form', ['quiz' => $quiz])
@endsection
