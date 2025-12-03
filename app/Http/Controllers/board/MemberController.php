<?php

namespace App\Http\Controllers\board;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    /**
     * Display a listing of members in the board's committee.
     */
    public function index()
    {
        $board = Auth::guard('board')->user();
        
        $members = User::whereHas('committees', function ($query) use ($board) {
            $query->where('committees.id', $board->committee_id);
        })
        ->with('committees')
        ->orderBy('created_at', 'desc')
        ->paginate(15);
        
        return view('board.members.index', compact('members', 'board'));
    }

    /**
     * Show the form for creating a new member.
     */
    public function create()
    {
        $board = Auth::guard('board')->user();
        return view('board.members.form', compact('board'));
    }

    /**
     * Store a newly created member in storage.
     */
    public function store(Request $request)
    {
        $board = Auth::guard('board')->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'university' => 'nullable|string|max:255',
            'faculty' => 'nullable|string|max:255',
            'academic_year' => 'nullable|string|max:255',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('users', 'public');
        }

        // Hash the password
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        // Create the user
        $user = User::create($validated);
        
        // Attach to the board's committee
        $user->committees()->attach($board->committee_id);

        return redirect()
            ->route('board.members.index')
            ->with('success', 'Member created successfully.');
    }

    /**
     * Show the form for editing the specified member.
     */
    public function edit($id)
    {
        $board = Auth::guard('board')->user();
        
        // Ensure the member belongs to this board's committee
        $member = User::whereHas('committees', function ($query) use ($board) {
            $query->where('committees.id', $board->committee_id);
        })->findOrFail($id);
        
        return view('board.members.form', compact('member', 'board'));
    }

    /**
     * Update the specified member in storage.
     */
    public function update(Request $request, $id)
    {
        $board = Auth::guard('board')->user();
        
        // Ensure the member belongs to this board's committee
        $member = User::whereHas('committees', function ($query) use ($board) {
            $query->where('committees.id', $board->committee_id);
        })->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($member->id)],
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'university' => 'nullable|string|max:255',
            'faculty' => 'nullable|string|max:255',
            'academic_year' => 'nullable|string|max:255',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($member->image) {
                Storage::disk('public')->delete($member->image);
            }
            $validated['image'] = $request->file('image')->store('users', 'public');
        }

        // Hash the password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $member->update($validated);

        // Preserve committees from other committees
        // Board members can only manage their own committee
        $otherCommittees = $member->committees()
            ->where('committees.id', '!=', $board->committee_id)
            ->pluck('committees.id')
            ->toArray();

        // Keep user in board's committee plus any other committees
        $allCommittees = array_unique(array_merge($otherCommittees, [$board->committee_id]));

        $member->committees()->sync($allCommittees);

        return redirect()
            ->route('board.members.index')
            ->with('success', 'Member updated successfully.');
    }

    /**
     * Remove the specified member from storage.
     */
    public function destroy($id)
    {
        $board = Auth::guard('board')->user();
        
        // Ensure the member belongs to this board's committee
        $member = User::whereHas('committees', function ($query) use ($board) {
            $query->where('committees.id', $board->committee_id);
        })->findOrFail($id);

        // Delete image if exists
        if ($member->image) {
            Storage::disk('public')->delete($member->image);
        }

        $member->delete();

        return redirect()
            ->route('board.members.index')
            ->with('success', 'Member deleted successfully.');
    }

    /**
     * Toggle the active status of a member.
     */
    public function toggleStatus($id)
    {
        $board = Auth::guard('board')->user();
        
        // Ensure the member belongs to this board's committee
        $member = User::whereHas('committees', function ($query) use ($board) {
            $query->where('committees.id', $board->committee_id);
        })->findOrFail($id);

        $member->is_active = !$member->is_active;
        $member->save();

        $status = $member->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('board.members.index')
            ->with('success', "Member {$status} successfully.");
    }
}
