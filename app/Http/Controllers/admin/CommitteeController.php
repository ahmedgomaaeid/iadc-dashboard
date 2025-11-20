<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\Field;
use Illuminate\Http\Request;

class CommitteeController extends Controller
{
    /**
     * Display a listing of committees
     */
    public function index()
    {
        $committees = Committee::with('field')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.committees.index', compact('committees'));
    }

    /**
     * Show the form for creating a new committee
     */
    public function create()
    {
        $fields = Field::active()->orderBy('name')->get();
        return view('admin.committees.form', ['committee' => null, 'fields' => $fields]);
    }

    /**
     * Store a newly created committee in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'field_id' => 'required|exists:fields,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        Committee::create($validated);

        return redirect()->route('admin.committees.index')
            ->with('success', 'Committee created successfully.');
    }

    /**
     * Show the form for editing the specified committee
     */
    public function edit(Committee $committee)
    {
        $fields = Field::active()->orderBy('name')->get();
        return view('admin.committees.form', compact('committee', 'fields'));
    }

    /**
     * Update the specified committee in database
     */
    public function update(Request $request, Committee $committee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'field_id' => 'required|exists:fields,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $committee->update($validated);

        return redirect()->route('admin.committees.index')
            ->with('success', 'Committee updated successfully.');
    }

    /**
     * Soft delete the specified committee (set is_active to false)
     */
    public function destroy(Committee $committee)
    {
        $committee->update(['is_active' => false]);

        return redirect()->route('admin.committees.index')
            ->with('success', 'Committee deactivated successfully.');
    }

    /**
     * Toggle committee active status
     */
    public function toggleStatus(Committee $committee)
    {
        $committee->update(['is_active' => !$committee->is_active]);

        $status = $committee->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.committees.index')
            ->with('success', "Committee {$status} successfully.");
    }
}
