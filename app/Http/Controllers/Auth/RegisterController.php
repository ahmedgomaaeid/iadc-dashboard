<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Committee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm(Request $request)
    {
        $committees = Committee::active()->orderBy('name')->get();
        return view('user.auth.register', compact('committees'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'string', Password::min(8)],
            'committees' => 'required|array',
            'committees.*' => 'exists:committees,id',
            'phone' => 'nullable|string|max:20',
            'university' => 'nullable|string|max:255',
            'faculty' => 'nullable|string|max:255',
            'academic_year' => 'nullable|string|max:255',
        ]);

        // Set user as inactive by default (requires admin approval)
        $validated['is_active'] = false;

        // Hash the password
        $validated['password'] = Hash::make($validated['password']);

        // Create the user
        $user = User::create($validated);

        // Attach selected committees
        $user->committees()->attach($validated['committees']);

        return redirect()->route('register')->with('registration_success', true);

    }
}
