@extends('layouts.landing')

@section('header')
<div class="jumps-prevent" style="padding-top: 67.5px;"></div>
@endsection

@section('title', 'IADC Suez - '.$event->name)

@section('content')
    <style>
        body {
            background-color: #f0f0f5 !important;
        }
        .top {
            background-color: white !important;
        }
    </style>
    <div class="container container-fluid my-5">

        <div class="row">
            <div class="col-xl-{{ $event->partners->count() > 0 ? '8' : '12' }}">
                <div class="card">
                    <img class="card-img-top " src="{{ asset('storage/' . $event->image) }}" alt="Card image cap">
                    <div class="card-body">
                        <div class="d-md-flex">
                            <a href="javascript:void(0);" class="d-flex me-2 mb-2"><i class="fe fe-calendar fs-16 me-1 p-3 bg-primary-transparent text-primary bradius"></i>
                                <div class="mt-0 mt-3 ms-1 text-muted font-weight-semibold">{{ \Carbon\Carbon::parse($event->date_from)->format('d-m-Y') }} @if($event->date_to) to {{ \Carbon\Carbon::parse($event->date_to)->format('d-m-Y') }} @endif</div>
                            </a>
                            <a href="javascript:void(0);" class="d-flex me-2 mb-2"><i class="fa fa-location-arrow fs-16 me-1 p-3 bg-primary-transparent text-primary bradius"></i>
                                <div class="mt-0 mt-3 ms-1 text-muted font-weight-semibold">{{ $event->place }} </div>
                            </a>
                            @if($event->attendees_number)
                                <a href="javascript:void(0);" class="d-flex me-2"><i class="fa fa-users fs-16 me-1 p-3 bg-primary-transparent text-primary bradius"></i>
                                    <div class="mt-0 mt-3 ms-1 text-muted font-weight-semibold">+{{ $event->attendees_number }}</div>
                                </a>
                            @endif
                            @if($event->register_active)
                                <div class="ms-auto">
                                    <a href="{{ $event->register_link }}" class="btn btn-primary">
                                        <i class="fe fe-user-plus"></i> Register Now
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <h3><a href="javascript:void(0)">{{ $event->name }}</a></h3>
                        <p class="card-text">{!! $event->description !!}</p>
                    </div>
                </div>
            </div>
            @if($event->partners->count() > 0)
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Sponsors</div>
                        </div>
                        <div class="card-body">
                            <div class="">
                                @foreach ($event->partners as $partner)
                                <div class="d-flex overflow-visible mb-2">
                                    <img class="bradius me-3" style="height: 64px;" src="{{ asset('storage/' . $partner->image) }}" alt="avatar-img">
                                    <div class="media-body valign-middle" style="padding-top: 18px;">
                                        <span class="fw-semibold badge bg-success-transparent rounded-pill text-success p-2 px-3">{{ $partner->type }} Sponsor</a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection