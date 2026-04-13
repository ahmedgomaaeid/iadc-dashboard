<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Article;
use App\Models\Magazine;

class LandingPageController extends Controller
{
    public function index()
    {
        $upcoming_events = Event::where('date_from', '>=', now())->where('type', 'event')->orderBy('id', 'desc')->get();
        $past_events = Event::where('date_from', '<', now())->where('type', 'event')->orderBy('id', 'desc')->get();
        $visits = Event::where('type', 'visit')->orderBy('id', 'desc')->get();
        $articles = Article::active()->latest()->take(3)->get();
        $magazines = Magazine::active()->latest()->get();
        return view('welcome', compact('upcoming_events', 'past_events', 'visits', 'articles', 'magazines'));
    }

    public function eventPreview($id)
    {
        $event = Event::with(['partners', 'communityPartners', 'images', 'links'])->findOrFail($id);
        return view('landing.events.preview', compact('event'));
    }

    public function articlesList(Request $request)
    {
        $query = Article::active();
        
        if ($request->has('type') && array_key_exists($request->type, Article::TYPES)) {
            $query->where('type', $request->type);
        }
        
        $articles = $query->latest()->paginate(12);
        $types = Article::TYPES;
        return view('landing.articles.index', compact('articles', 'types'));
    }

    public function articlePreview($id)
    {
        $article = Article::active()->findOrFail($id);
        $relatedArticles = Article::active()
            ->where('id', '!=', $id)
            ->where('type', $article->type)
            ->latest()
            ->take(3)
            ->get();
        return view('landing.articles.preview', compact('article', 'relatedArticles'));
    }

    public function magazineViewer($id)
    {
        $magazine = Magazine::active()->findOrFail($id);
        return view('landing.magazines.viewer', compact('magazine'));
    }

    public function privacyPolicy()
    {
        return view('privacy-policy');
    }
}
