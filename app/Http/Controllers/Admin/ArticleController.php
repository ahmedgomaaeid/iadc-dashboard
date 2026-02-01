<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    use \App\Traits\ImageUploadTrait;
    /**
     * Display a listing of articles.
     */
    public function index()
    {
        $articles = Article::orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.articles.index', compact('articles'));
    }

    /**
     * Show the form for creating a new article.
     */
    public function create()
    {
        $types = Article::TYPES;
        return view('admin.articles.form', ['article' => null, 'types' => $types]);
    }

    /**
     * Store a newly created article in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'content' => 'nullable|string',
            'type' => 'required|in:' . implode(',', array_keys(Article::TYPES)),
            'author' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $this->uploadImage($request->file('image'), 'articles');
        }

        $validated['is_active'] = $request->has('is_active');

        $article = Article::create($validated);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article created successfully.');
    }

    /**
     * Show the form for editing the specified article.
     */
    public function edit($id)
    {
        $article = Article::findOrFail($id);
        $types = Article::TYPES;

        return view('admin.articles.form', compact('article', 'types'));
    }

    /**
     * Update the specified article in storage.
     */
    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'content' => 'nullable|string',
            'type' => 'required|in:' . implode(',', array_keys(Article::TYPES)),
            'author' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image handled by trait if passed, but here we can just pass it directly if we want, or keep logic. 
            // The trait handles deletion if we pass the old path.
            $validated['image'] = $this->uploadImage($request->file('image'), 'articles', $article->image);
        }

        $validated['is_active'] = $request->has('is_active');

        $article->update($validated);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article updated successfully.');
    }

    /**
     * Remove the specified article from storage.
     */
    public function destroy($id)
    {
        $article = Article::findOrFail($id);

        // Delete image
        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }

        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article deleted successfully.');
    }

    /**
     * Toggle article active status.
     */
    public function toggleStatus($id)
    {
        $article = Article::findOrFail($id);
        $article->update(['is_active' => !$article->is_active]);

        $status = $article->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.articles.index')
            ->with('success', "Article {$status} successfully.");
    }
}
