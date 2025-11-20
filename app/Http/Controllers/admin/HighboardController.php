<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Highboard;
use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class HighboardController extends Controller
{
    /**
     * Display a listing of highboard members
     */
    public function index()
    {
        $highboards = Highboard::with('field')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.highboards.index', compact('highboards'));
    }

    /**
     * Show the form for creating a new highboard member
     */
    public function create()
    {
        $fields = Field::active()->orderBy('name')->get();
        return view('admin.highboards.form', ['highboard' => null, 'fields' => $fields]);
    }

    /**
     * Store a newly created highboard member in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:highboards,email',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'string', Password::min(8)],
            'field_id' => 'required|exists:fields,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['password'] = Hash::make($validated['password']);

        Highboard::create($validated);

        return redirect()->route('admin.highboards.index')
            ->with('success', 'Highboard member created successfully.');
    }

    /**
     * Show the form for editing the specified highboard member
     */
    public function edit(Highboard $highboard)
    {
        $fields = Field::active()->orderBy('name')->get();
        return view('admin.highboards.form', compact('highboard', 'fields'));
    }

    /**
     * Update the specified highboard member in database
     */
    public function update(Request $request, Highboard $highboard)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:highboards,email,' . $highboard->id,
            'phone' => 'nullable|string|max:20',
            'password' => ['nullable', 'string', Password::min(8)],
            'field_id' => 'required|exists:fields,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $highboard->update($validated);

        return redirect()->route('admin.highboards.index')
            ->with('success', 'Highboard member updated successfully.');
    }

    /**
     * Soft delete the specified highboard member (set is_active to false)
     */
    public function destroy(Highboard $highboard)
    {
        $highboard->update(['is_active' => false]);

        return redirect()->route('admin.highboards.index')
            ->with('success', 'Highboard member deactivated successfully.');
    }

    /**
     * Toggle highboard member active status
     */
    public function toggleStatus(Highboard $highboard)
    {
        $highboard->update(['is_active' => !$highboard->is_active]);

        $status = $highboard->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.highboards.index')
            ->with('success', "Highboard member {$status} successfully.");
    }
}
