<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Magazine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MagazineController extends Controller
{
    use \App\Traits\ImageUploadTrait;
    /**
     * Display a listing of the magazines.
     */
    public function index()
    {
        $magazines = Magazine::latest()->paginate(10);
        return view('admin.magazines.index', compact('magazines'));
    }

    /**
     * Show the form for creating a new magazine.
     */
    public function create()
    {
        return view('admin.magazines.form');
    }

    /**
     * Store a newly created magazine in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'pdf_file' => 'nullable|mimes:pdf|max:102400',
            'uploaded_pdf' => 'required_without:pdf_file|string',
            'is_active' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $this->uploadImage($request->file('image'), 'magazines/covers');
        }

        // Handle PDF upload
        if ($request->hasFile('pdf_file')) {
            $validated['pdf_file'] = $request->file('pdf_file')->store('magazines/pdfs', 'public');
        } elseif ($request->filled('uploaded_pdf')) {
            $tempPath = $request->input('uploaded_pdf');
            if (Storage::disk('local')->exists($tempPath)) {
                $fileName = str_replace('temp_uploads/', '', $tempPath);
                $parts = explode('_', $fileName, 2);
                $originalName = count($parts) > 1 ? $parts[1] : $fileName;
                
                $newFileName = time() . '_' . $originalName;
                $newPath = 'magazines/pdfs/' . $newFileName;
                Storage::disk('public')->put($newPath, Storage::disk('local')->get($tempPath));
                Storage::disk('local')->delete($tempPath);
                
                $validated['pdf_file'] = $newPath;
            }
        }

        $validated['is_active'] = $request->has('is_active');
        unset($validated['uploaded_pdf']);

        Magazine::create($validated);

        return redirect()->route('admin.magazines.index')
            ->with('success', 'Magazine created successfully.');
    }

    /**
     * Show the form for editing the specified magazine.
     */
    public function edit($id)
    {
        $magazine = Magazine::findOrFail($id);
        return view('admin.magazines.form', compact('magazine'));
    }

    /**
     * Update the specified magazine in storage.
     */
    public function update(Request $request, $id)
    {
        $magazine = Magazine::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'pdf_file' => 'nullable|mimes:pdf|max:102400',
            'uploaded_pdf' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $this->uploadImage($request->file('image'), 'magazines/covers', $magazine->image);
        }

        // Handle PDF upload
        if ($request->hasFile('pdf_file') || $request->filled('uploaded_pdf')) {
            // Delete old PDF
            if ($magazine->pdf_file) {
                Storage::disk('public')->delete($magazine->pdf_file);
            }
            
            if ($request->hasFile('pdf_file')) {
                $validated['pdf_file'] = $request->file('pdf_file')->store('magazines/pdfs', 'public');
            } elseif ($request->filled('uploaded_pdf')) {
                $tempPath = $request->input('uploaded_pdf');
                if (Storage::disk('local')->exists($tempPath)) {
                    $fileName = str_replace('temp_uploads/', '', $tempPath);
                    $parts = explode('_', $fileName, 2);
                    $originalName = count($parts) > 1 ? $parts[1] : $fileName;
                    
                    $newFileName = time() . '_' . $originalName;
                    $newPath = 'magazines/pdfs/' . $newFileName;
                    Storage::disk('public')->put($newPath, Storage::disk('local')->get($tempPath));
                    Storage::disk('local')->delete($tempPath);
                    
                    $validated['pdf_file'] = $newPath;
                }
            }
        }

        $validated['is_active'] = $request->has('is_active');
        unset($validated['uploaded_pdf']);

        $magazine->update($validated);

        return redirect()->route('admin.magazines.index')
            ->with('success', 'Magazine updated successfully.');
    }

    /**
     * Remove the specified magazine from storage.
     */
    public function destroy($id)
    {
        $magazine = Magazine::findOrFail($id);

        // Delete files
        if ($magazine->image) {
            Storage::disk('public')->delete($magazine->image);
        }
        if ($magazine->pdf_file) {
            Storage::disk('public')->delete($magazine->pdf_file);
        }

        $magazine->delete();

        return redirect()->route('admin.magazines.index')
            ->with('success', 'Magazine deleted successfully.');
    }

    /**
     * Toggle the active status of the specified magazine.
     */
    public function toggleStatus($id)
    {
        $magazine = Magazine::findOrFail($id);
        $magazine->is_active = !$magazine->is_active;
        $magazine->save();

        return redirect()->route('admin.magazines.index')
            ->with('success', 'Magazine status updated successfully.');
    }
}
