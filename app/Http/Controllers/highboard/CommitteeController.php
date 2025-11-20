<?php

namespace App\Http\Controllers\highboard;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommitteeController extends Controller
{
    /**
     * Display a listing of committees in highboard's field
     */
    public function index()
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        $committees = Committee::where('field_id', $fieldId)
            ->withCount(['users' => function($query) {
                $query->where('is_active', true);
            }])
            ->withCount(['boards' => function($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('highboard.committees.index', compact('committees'));
    }

    /**
     * Show the form for creating a new committee
     */
    public function create()
    {
        return view('highboard.committees.form', ['committee' => null]);
    }

    /**
     * Store a newly created committee in database
     */
    public function store(Request $request)
    {
        $highboard = Auth::guard('highboard')->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['field_id'] = $highboard->field_id; // Auto-assign field

        Committee::create($validated);

        return redirect()->route('highboard.committees.index')
            ->with('success', 'Committee created successfully.');
    }

    /**
     * Show the form for editing the specified committee
     */
    public function edit($id)
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        // Ensure committee belongs to highboard's field
        $committee = Committee::where('field_id', $fieldId)->findOrFail($id);

        return view('highboard.committees.form', compact('committee'));
    }

    /**
     * Update the specified committee in database
     */
    public function update(Request $request, $id)
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        // Ensure committee belongs to highboard's field
        $committee = Committee::where('field_id', $fieldId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $committee->update($validated);

        return redirect()->route('highboard.committees.index')
            ->with('success', 'Committee updated successfully.');
    }

    /**
     * Soft delete the specified committee (set is_active to false)
     */
    public function destroy($id)
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        // Ensure committee belongs to highboard's field
        $committee = Committee::where('field_id', $fieldId)->findOrFail($id);

        $committee->update(['is_active' => false]);

        return redirect()->route('highboard.committees.index')
            ->with('success', 'Committee deactivated successfully.');
    }

    /**
     * Toggle committee active status
     */
    public function toggleStatus($id)
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        // Ensure committee belongs to highboard's field
        $committee = Committee::where('field_id', $fieldId)->findOrFail($id);

        $committee->update(['is_active' => !$committee->is_active]);

        $status = $committee->is_active ? 'activated' : 'deactivated';

        return redirect()->route('highboard.committees.index')
            ->with('success', "Committee {$status} successfully.");
    }
}
