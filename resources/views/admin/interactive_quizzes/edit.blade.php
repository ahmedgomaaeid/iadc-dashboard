@extends('layouts.admin-dashboard')

@section('content')
    @include('admin.interactive_quizzes.form', ['quiz' => $quiz])
@endsection
