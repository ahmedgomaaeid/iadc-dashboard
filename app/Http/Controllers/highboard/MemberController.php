<?php

namespace App\Http\Controllers\highboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Committee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class MemberController extends Controller
{
    /**
     * Display a listing of members in highboard's field
     */
    public function index()
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        $members = User::where('field_id', $fieldId)
            ->with(['committees', 'field'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('highboard.members.index', compact('members'));
    }

    /**
     * Show the form for creating a new member
     */
    public function create()
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        $committees = Committee::where('field_id', $fieldId)
            ->active()
            ->orderBy('name')
            ->get();

        return view('highboard.members.form', ['member' => null, 'committees' => $committees]);
    }

    /**
     * Store a newly created member in database
     */
    public function store(Request $request)
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'string', Password::min(8)],
            'committees' => 'required|array',
            'committees.*' => 'exists:committees,id',
            'is_active' => 'boolean',
        ]);

        // Verify committees belong to highboard's field
        $validCommitteeIds = Committee::where('field_id', $fieldId)
            ->whereIn('id', $validated['committees'])
            ->pluck('id')
            ->toArray();

        if (count($validCommitteeIds) !== count($validated['committees'])) {
            return back()->withErrors(['committees' => 'Invalid committee selection.']);
        }

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['password'] = Hash::make($validated['password']);
        $validated['field_id'] = $fieldId; // Auto-assign field

        $user = User::create($validated);
        $user->committees()->attach($validated['committees']);

        return redirect()->route('highboard.members.index')
            ->with('success', 'Member created successfully.');
    }

    /**
     * Show the form for editing the specified member
     */
    public function edit($id)
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        // Ensure member belongs to highboard's field
        $member = User::where('field_id', $fieldId)->findOrFail($id);

        $committees = Committee::where('field_id', $fieldId)
            ->active()
            ->orderBy('name')
            ->get();

        return view('highboard.members.form', compact('member', 'committees'));
    }

    /**
     * Update the specified member in database
     */
    public function update(Request $request, $id)
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        // Ensure member belongs to highboard's field
        $member = User::where('field_id', $fieldId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $member->id,
            'phone' => 'nullable|string|max:20',
            'password' => ['nullable', 'string', Password::min(8)],
            'committees' => 'required|array',
            'committees.*' => 'exists:committees,id',
            'is_active' => 'boolean',
        ]);

        // Verify committees belong to highboard's field
        $validCommitteeIds = Committee::where('field_id', $fieldId)
            ->whereIn('id', $validated['committees'])
            ->pluck('id')
            ->toArray();

        if (count($validCommitteeIds) !== count($validated['committees'])) {
            return back()->withErrors(['committees' => 'Invalid committee selection.']);
        }

        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['field_id'] = $fieldId; // Ensure field doesn't change

        $member->update($validated);
        $member->committees()->sync($validated['committees']);

        return redirect()->route('highboard.members.index')
            ->with('success', 'Member updated successfully.');
    }

    /**
     * Soft delete the specified member
     */
    public function destroy($id)
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        // Ensure member belongs to highboard's field
        $member = User::where('field_id', $fieldId)->findOrFail($id);

        $member->update(['is_active' => false]);

        return redirect()->route('highboard.members.index')
            ->with('success', 'Member deactivated successfully.');
    }

    /**
     * Toggle member active status
     */
    public function toggleStatus($id)
    {
        $highboard = Auth::guard('highboard')->user();
        $fieldId = $highboard->field_id;

        // Ensure member belongs to highboard's field
        $member = User::where('field_id', $fieldId)->findOrFail($id);

        $member->update(['is_active' => !$member->is_active]);

        $status = $member->is_active ? 'activated' : 'deactivated';

        return redirect()->route('highboard.members.index')
            ->with('success', "Member {$status} successfully.");
    }
}
