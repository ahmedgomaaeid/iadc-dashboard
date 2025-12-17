<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class LandingPageController extends Controller
{
    public function index()
    {
        $upcoming_events = Event::where('date_from', '>=', now())->where('type', 'event')->get();
        $past_events = Event::where('date_from', '<', now())->where('type', 'event')->get();
        $visits = Event::where('type', 'visit')->get();
        return view('welcome', compact('upcoming_events', 'past_events', 'visits'));
    }
    public function eventPreview($id)
    {
        $event = Event::find($id);
        return view('landing.events.preview', compact('event'));
    }
}
