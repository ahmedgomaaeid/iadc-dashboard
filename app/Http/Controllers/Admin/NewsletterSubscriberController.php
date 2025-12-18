<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterSubscriberController extends Controller
{
    /**
     * Display a listing of subscribers.
     */
    public function index()
    {
        $subscribers = NewsletterSubscriber::latest()->paginate(20);
        $totalActive = NewsletterSubscriber::active()->count();
        $totalInactive = NewsletterSubscriber::where('is_active', false)->count();
        return view('admin.newsletter-subscribers.index', compact('subscribers', 'totalActive', 'totalInactive'));
    }

    /**
     * Toggle subscriber status.
     */
    public function toggleStatus($id)
    {
        $subscriber = NewsletterSubscriber::findOrFail($id);
        $subscriber->update(['is_active' => !$subscriber->is_active]);
        
        $status = $subscriber->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Subscriber {$status} successfully.");
    }

    /**
     * Remove the specified subscriber.
     */
    public function destroy($id)
    {
        $subscriber = NewsletterSubscriber::findOrFail($id);
        $subscriber->delete();
        
        return redirect()->route('admin.newsletter-subscribers.index')
            ->with('success', 'Subscriber removed successfully.');
    }

    /**
     * Export subscribers to CSV.
     */
    public function export()
    {
        $subscribers = NewsletterSubscriber::active()->orderBy('email')->get();
        
        $filename = 'newsletter_subscribers_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($subscribers) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Email', 'Subscribed At']);
            
            // Data rows
            foreach ($subscribers as $subscriber) {
                fputcsv($file, [
                    $subscriber->email,
                    $subscriber->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
