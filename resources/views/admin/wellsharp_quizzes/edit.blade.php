@extends('layouts.admin-dashboard')

@section('content')
    @include('admin.wellsharp_quizzes.form', ['quiz' => $quiz])
@endsection
