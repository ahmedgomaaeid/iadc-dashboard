@extends('layouts.highboard-dashboard')

@section('content')
    @include('highboard.questions.form', ['question' => $question])
@endsection
