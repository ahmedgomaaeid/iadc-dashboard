@extends('layouts.highboard-dashboard')

@section('content')
    @include('highboard.questions.form', ['quiz' => $quiz])
@endsection
