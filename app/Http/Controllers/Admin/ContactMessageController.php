<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    /**
     * Display a listing of contact messages.
     */
    public function index()
    {
        $messages = ContactMessage::latest()->paginate(15);
        $unreadCount = ContactMessage::unread()->count();
        return view('admin.contact-messages.index', compact('messages', 'unreadCount'));
    }

    /**
     * Display the specified message.
     */
    public function show($id)
    {
        $message = ContactMessage::findOrFail($id);
        
        // Mark as read when viewed
        if (!$message->is_read) {
            $message->update(['is_read' => true]);
        }
        
        return view('admin.contact-messages.show', compact('message'));
    }

    /**
     * Mark message as read.
     */
    public function markAsRead($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['is_read' => true]);
        
        return redirect()->back()->with('success', 'Message marked as read.');
    }

    /**
     * Mark message as unread.
     */
    public function markAsUnread($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['is_read' => false]);
        
        return redirect()->back()->with('success', 'Message marked as unread.');
    }

    /**
     * Remove the specified message.
     */
    public function destroy($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();
        
        return redirect()->route('admin.contact-messages.index')
            ->with('success', 'Message deleted successfully.');
    }
}
