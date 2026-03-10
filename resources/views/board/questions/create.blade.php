@extends('layouts.highboard-dashboard')

@section('content')
    @include('board.questions.form', ['quiz' => $quiz])
@endsection
