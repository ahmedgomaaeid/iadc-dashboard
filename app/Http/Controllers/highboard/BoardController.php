<?php

namespace App\Http\Controllers\Highboard;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Committee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class BoardController extends Controller
{
    /**
     * Display a listing of board members in highboard's field
     */
    public function index()
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        $boards = Board::where('field_id', $fieldId)
            ->with(['committee', 'field'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('highboard.boards.index', compact('boards'));
    }

    /**
     * Show the form for creating a new board member
     */
    public function create()
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        $committees = Committee::where('field_id', $fieldId)
            ->active()
            ->orderBy('name')
            ->get();

        return view('highboard.boards.form', ['board' => null, 'committees' => $committees]);
    }

    /**
     * Store a newly created board member in database
     */
    public function store(Request $request)
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:boards,email',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'string', Password::min(8)],
            'committee_id' => 'required|exists:committees,id',
            'is_active' => 'boolean',
        ]);

        // Verify committee belongs to highboard's field
        $committee = Committee::where('field_id', $fieldId)
            ->findOrFail($validated['committee_id']);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['password'] = Hash::make($validated['password']);
        $validated['field_id'] = $fieldId; // Auto-assign field

        Board::create($validated);

        return redirect()->route('highboard.boards.index')
            ->with('success', 'Board member created successfully.');
    }

    /**
     * Show the form for editing the specified board member
     */
    public function edit($id)
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        // Ensure board belongs to highboard's field
        $board = Board::where('field_id', $fieldId)->findOrFail($id);

        $committees = Committee::where('field_id', $fieldId)
            ->active()
            ->orderBy('name')
            ->get();

        return view('highboard.boards.form', compact('board', 'committees'));
    }

    /**
     * Update the specified board member in database
     */
    public function update(Request $request, $id)
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        // Ensure board belongs to highboard's field
        $board = Board::where('field_id', $fieldId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:boards,email,' . $board->id,
            'phone' => 'nullable|string|max:20',
            'password' => ['nullable', 'string', Password::min(8)],
            'committee_id' => 'required|exists:committees,id',
            'is_active' => 'boolean',
        ]);

        // Verify committee belongs to highboard's field
        $committee = Committee::where('field_id', $fieldId)
            ->findOrFail($validated['committee_id']);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['field_id'] = $fieldId; // Ensure field doesn't change

        $board->update($validated);

        return redirect()->route('highboard.boards.index')
            ->with('success', 'Board member updated successfully.');
    }

    /**
     * Soft delete the specified board member
     */
    public function destroy($id)
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        // Ensure board belongs to highboard's field
        $board = Board::where('field_id', $fieldId)->findOrFail($id);

        $board->update(['is_active' => false]);

        return redirect()->route('highboard.boards.index')
            ->with('success', 'Board member deactivated successfully.');
    }

    /**
     * Toggle board member active status
     */
    public function toggleStatus($id)
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        // Ensure board belongs to highboard's field
        $board = Board::where('field_id', $fieldId)->findOrFail($id);

        $board->update(['is_active' => !$board->is_active]);

        $status = $board->is_active ? 'activated' : 'deactivated';

        return redirect()->route('highboard.boards.index')
            ->with('success', "Board member {$status} successfully.");
    }
}
