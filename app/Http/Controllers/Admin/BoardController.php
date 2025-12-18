<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Committee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class BoardController extends Controller
{
    /**
     * Display a listing of board members
     */
    public function index()
    {
        $boards = Board::with(['committee', 'field'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.boards.index', compact('boards'));
    }

    /**
     * Show the form for creating a new board member
     */
    public function create()
    {
        $committees = Committee::active()->with('field')->orderBy('name')->get();
        return view('admin.boards.form', ['board' => null, 'committees' => $committees]);
    }

    /**
     * Store a newly created board member in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:boards,email',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'string', Password::min(8)],
            'committee_id' => 'required|exists:committees,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['password'] = Hash::make($validated['password']);

        // Get the field_id from the selected committee
        $committee = Committee::find($validated['committee_id']);
        $validated['field_id'] = $committee->field_id;

        Board::create($validated);

        return redirect()->route('admin.boards.index')
            ->with('success', 'Board member created successfully.');
    }

    /**
     * Show the form for editing the specified board member
     */
    public function edit(Board $board)
    {
        $committees = Committee::active()->with('field')->orderBy('name')->get();
        return view('admin.boards.form', compact('board', 'committees'));
    }

    /**
     * Update the specified board member in database
     */
    public function update(Request $request, Board $board)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:boards,email,' . $board->id,
            'phone' => 'nullable|string|max:20',
            'password' => ['nullable', 'string', Password::min(8)],
            'committee_id' => 'required|exists:committees,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Get the field_id from the selected committee
        $committee = Committee::find($validated['committee_id']);
        $validated['field_id'] = $committee->field_id;

        $board->update($validated);

        return redirect()->route('admin.boards.index')
            ->with('success', 'Board member updated successfully.');
    }

    /**
     * Soft delete the specified board member (set is_active to false)
     */
    public function destroy(Board $board)
    {
        $board->update(['is_active' => false]);

        return redirect()->route('admin.boards.index')
            ->with('success', 'Board member deactivated successfully.');
    }

    /**
     * Toggle board member active status
     */
    public function toggleStatus(Board $board)
    {
        $board->update(['is_active' => !$board->is_active]);

        $status = $board->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.boards.index')
            ->with('success', "Board member {$status} successfully.");
    }
}
