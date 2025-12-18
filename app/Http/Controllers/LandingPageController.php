<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class LandingPageController extends Controller
{
    public function index()
    {
        $upcoming_events = Event::where('date_from', '>=', now())->where('type', 'event')->orderBy('id', 'desc')->get();
        $past_events = Event::where('date_from', '<', now())->where('type', 'event')->orderBy('id', 'desc')->get();
        $visits = Event::where('type', 'visit')->orderBy('id', 'desc')->get();
        return view('welcome', compact('upcoming_events', 'past_events', 'visits'));
    }
    public function eventPreview($id)
    {
        $event = Event::with('partners')->findOrFail($id);
        return view('landing.events.preview', compact('event'));
    }
}
