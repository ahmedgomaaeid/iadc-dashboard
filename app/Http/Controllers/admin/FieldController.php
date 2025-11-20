<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Field;
use Illuminate\Http\Request;

class FieldController extends Controller
{
    /**
     * Display a listing of fields
     */
    public function index()
    {
        $fields = Field::withCount('committees')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.fields.index', compact('fields'));
    }

    /**
     * Show the form for creating a new field
     */
    public function create()
    {
        return view('admin.fields.form', ['field' => null]);
    }

    /**
     * Store a newly created field in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:fields,name',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        Field::create($validated);

        return redirect()->route('admin.fields.index')
            ->with('success', 'Field created successfully.');
    }

    /**
     * Show the form for editing the specified field
     */
    public function edit(Field $field)
    {
        return view('admin.fields.form', compact('field'));
    }

    /**
     * Update the specified field in database
     */
    public function update(Request $request, Field $field)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:fields,name,' . $field->id,
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $field->update($validated);

        return redirect()->route('admin.fields.index')
            ->with('success', 'Field updated successfully.');
    }

    /**
     * Soft delete the specified field (set is_active to false)
     */
    public function destroy(Field $field)
    {
        $field->update(['is_active' => false]);
        
        // Also deactivate all committees under this field
        $field->committees()->update(['is_active' => false]);

        return redirect()->route('admin.fields.index')
            ->with('success', 'Field deactivated successfully.');
    }

    /**
     * Toggle field active status
     */
    public function toggleStatus(Field $field)
    {
        $field->update(['is_active' => !$field->is_active]);

        $status = $field->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.fields.index')
            ->with('success', "Field {$status} successfully.");
    }
}
