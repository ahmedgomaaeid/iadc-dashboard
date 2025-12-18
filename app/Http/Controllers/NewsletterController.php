<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    /**
     * Store a newsletter subscription.
     */
    public function subscribe(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|max:255',
            ]);

            // Check if already subscribed
            $existing = NewsletterSubscriber::where('email', $validated['email'])->first();
            
            if ($existing) {
                if ($existing->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This email is already subscribed to our newsletter.'
                    ], 422);
                } else {
                    // Reactivate subscription
                    $existing->update(['is_active' => true]);
                    return response()->json([
                        'success' => true,
                        'message' => 'Welcome back! Your subscription has been reactivated.'
                    ]);
                }
            }

            NewsletterSubscriber::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for subscribing! You will receive our latest updates.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid email address.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }
}
