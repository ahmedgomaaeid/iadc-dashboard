<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Committee;
use App\Exports\MemberExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Maatwebsite\Excel\Facades\Excel;

class MemberController extends Controller
{
    /**
     * Display a listing of all members
     */
    public function index(Request $request)
    {
        $query = User::with(['committees', 'committees.field'])
            ->orderBy('created_at', 'desc');

        // Search by name, email, or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by committee
        if ($request->filled('committee_id')) {
            $query->whereHas('committees', function($q) use ($request) {
                $q->where('committees.id', $request->committee_id);
            });
        }
        
        // Filter by status (optional, if you want to add status filter too)
        if ($request->filled('status')) {
             if ($request->status === 'active') {
                 $query->where('is_active', true);
             } elseif ($request->status === 'inactive') {
                 $query->where('is_active', false);
             }
        }

        $members = $query->paginate(15)->withQueryString();

        // Get committees for filter
        $committees = Committee::active()->orderBy('name')->get();

        return view('admin.members.index', compact('members', 'committees'));
    }

    /**
     * Show the form for creating a new member
     */
    public function create()
    {
        // Get all active committees
        $committees = Committee::active()
            ->with('field')
            ->orderBy('name')
            ->get();

        return view('admin.members.form', ['member' => null, 'committees' => $committees]);
    }

    /**
     * Store a newly created member in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'string', Password::min(8)],
            'committees' => 'required|array',
            'committees.*' => 'exists:committees,id',
            'is_active' => 'boolean',
            'university' => 'nullable|string|max:255',
            'faculty' => 'nullable|string|max:255',
            'academic_year' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('users', 'public');
        }

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        $user->committees()->attach($validated['committees']);

        return redirect()->route('admin.members.index')
            ->with('success', 'Member created successfully.');
    }

    /**
     * Show the form for editing the specified member
     */
    public function edit($id)
    {
        $member = User::findOrFail($id);

        // Get all active committees
        $committees = Committee::active()
            ->with('field')
            ->orderBy('name')
            ->get();

        return view('admin.members.form', compact('member', 'committees'));
    }

    /**
     * Update the specified member in database
     */
    public function update(Request $request, $id)
    {
        $member = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $member->id,
            'phone' => 'nullable|string|max:20',
            'password' => ['nullable', 'string', Password::min(8)],
            'committees' => 'required|array',
            'committees.*' => 'exists:committees,id',
            'is_active' => 'boolean',
            'university' => 'nullable|string|max:255',
            'faculty' => 'nullable|string|max:255',
            'academic_year' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($member->image) {
                Storage::disk('public')->delete($member->image);
            }
            $validated['image'] = $request->file('image')->store('users', 'public');
        }

        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $member->update($validated);

        // Admin can manage all committees, so just sync directly
        $member->committees()->sync($validated['committees']);

        return redirect()->route('admin.members.index')
            ->with('success', 'Member updated successfully.');
    }

    /**
     * Delete the specified member
     */
    public function destroy($id)
    {
        $member = User::findOrFail($id);

        // Delete image if exists
        if ($member->image) {
            Storage::disk('public')->delete($member->image);
        }

        $member->delete();

        return redirect()->route('admin.members.index')
            ->with('success', 'Member deleted successfully.');
    }

    /**
     * Toggle member active status
     */
    public function toggleStatus(Request $request, $id)
    {
        $member = User::findOrFail($id);

        $member->update(['is_active' => !$member->is_active]);

        $status = $member->is_active ? 'activated' : 'deactivated';

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Member {$status} successfully.",
                'is_active' => $member->is_active
            ]);
        }

        return redirect()->route('admin.members.index')
            ->with('success', "Member {$status} successfully.");
    }

    /**
     * Bulk update status
     */
    public function bulkStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
            'active' => 'required|boolean'
        ]);

        User::whereIn('id', $request->ids)->update(['is_active' => $request->active]);

        $status = $request->active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . " members {$status} successfully."
        ]);
    }

    /**
     * Export all members to Excel
     */
    public function export()
    {
        $filename = 'members_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new MemberExport(), $filename);
    }
}
