<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventImage;
use App\Models\EventLink;
use App\Models\EventPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    use \App\Traits\ImageUploadTrait;
    /**
     * Display a listing of events.
     */
    public function index()
    {
        $events = Event::with('partners')
            ->orderBy('date_from', 'desc')
            ->paginate(15);

        return view('admin.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
        $partnerTypes = EventPartner::TYPES;
        return view('admin.events.form', ['event' => null, 'partnerTypes' => $partnerTypes]);
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'description' => 'nullable|string',
            'type' => 'required|in:event,visit',
            'date_from' => 'required|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'place' => 'required|string|max:255',
            'attendees_number' => 'nullable|integer|min:0',
            'register_link' => 'nullable|url|max:500',
            'register_active' => 'boolean',
            'is_active' => 'boolean',
            'partners' => 'nullable|array',
            'partners.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'partners.*.type' => 'nullable|in:' . implode(',', array_keys(EventPartner::TYPES)),
            'community_partners' => 'nullable|array',
            'community_partners.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'community_partners.*.type' => 'nullable|in:' . implode(',', array_keys(EventPartner::TYPES)),
            'gallery' => 'nullable|array',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'links' => 'nullable|array',
            'links.*.name' => 'required|string|max:255',
            'links.*.url' => 'required|url|max:255',
        ]);

        // Handle event image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $this->uploadImage($request->file('image'), 'events');
        }

        $validated['register_active'] = $request->has('register_active');
        $validated['is_active'] = $request->has('is_active');

        $event = Event::create($validated);

        // Handle partners
        if ($request->has('partners')) {
            $maxOrder = 0;
            foreach ($request->input('partners') as $index => $partnerInput) {
                $partnerType = $partnerInput['type'] ?? null;
                if ($partnerType && $request->hasFile("partners.{$index}.image")) {
                    $partnerImage = $this->uploadImage($request->file("partners.{$index}.image"), 'event-partners');
                    EventPartner::create([
                        'event_id' => $event->id,
                        'image' => $partnerImage,
                        'type' => $partnerType,
                        'category' => 'partner',
                        'sort_order' => $maxOrder++,
                    ]);
                }
            }
        }

        // Handle community partners
        if ($request->has('community_partners')) {
            $maxOrder = 0;
            foreach ($request->input('community_partners') as $index => $partnerInput) {
                $partnerType = $partnerInput['type'] ?? null;
                if ($partnerType && $request->hasFile("community_partners.{$index}.image")) {
                    $partnerImage = $this->uploadImage($request->file("community_partners.{$index}.image"), 'event-partners');
                    EventPartner::create([
                        'event_id' => $event->id,
                        'image' => $partnerImage,
                        'type' => $partnerType,
                        'category' => 'community_partner',
                        'sort_order' => $maxOrder++,
                    ]);
                }
            }
        }

        // Handle gallery images
        if ($request->hasFile('gallery')) {
            $imageOrder = 0;
            foreach ($request->file('gallery') as $galleryImage) {
                $imagePath = $this->uploadImage($galleryImage, 'event-gallery');
                EventImage::create([
                    'event_id' => $event->id,
                    'image' => $imagePath,
                    'sort_order' => $imageOrder++,
                ]);
            }
        }

        // Handle links
        if ($request->has('links')) {
            foreach ($request->input('links') as $link) {
                if (!empty($link['name']) && !empty($link['url'])) {
                    EventLink::create([
                        'event_id' => $event->id,
                        'name' => $link['name'],
                        'url' => $link['url'],
                    ]);
                }
            }
        }

        return redirect()->route('admin.events.index')
            ->with('success', 'Event created successfully.');
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit($id)
    {
        $event = Event::with(['partners' => function($query) {
            $query->orderBy('sort_order');
        }, 'communityPartners' => function($query) {
            $query->orderBy('sort_order');
        }, 'images' => function($query) {
            $query->orderBy('sort_order');
        }])->findOrFail($id);
        $partnerTypes = EventPartner::TYPES;

        return view('admin.events.form', compact('event', 'partnerTypes'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'description' => 'nullable|string',
            'type' => 'required|in:event,visit',
            'date_from' => 'required|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'place' => 'required|string|max:255',
            'attendees_number' => 'nullable|integer|min:0',
            'register_link' => 'nullable|url|max:500',
            'register_active' => 'boolean',
            'is_active' => 'boolean',
            'partners' => 'nullable|array',
            'partners.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'partners.*.type' => 'nullable|in:' . implode(',', array_keys(EventPartner::TYPES)),
            'community_partners' => 'nullable|array',
            'community_partners.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'community_partners.*.type' => 'nullable|in:' . implode(',', array_keys(EventPartner::TYPES)),
            'gallery' => 'nullable|array',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'links' => 'nullable|array',
            'links.*.name' => 'required|string|max:255',
            'links.*.url' => 'required|url|max:255',
        ]);

        // Handle event image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $this->uploadImage($request->file('image'), 'events', $event->image);
        }

        $validated['register_active'] = $request->has('register_active');
        $validated['is_active'] = $request->has('is_active');

        $event->update($validated);

        // Handle links
        // Sync approach: delete all and recreate
        $event->links()->delete();
        
        if ($request->has('links')) {
            foreach ($request->input('links') as $link) {
                if (!empty($link['name']) && !empty($link['url'])) {
                    EventLink::create([
                        'event_id' => $event->id,
                        'name' => $link['name'],
                        'url' => $link['url'],
                    ]);
                }
            }
        }

        // Handle new partners
        if ($request->has('partners')) {
            $maxOrder = $event->partners()->max('sort_order') ?? 0;
            foreach ($request->input('partners') as $index => $partnerInput) {
                $partnerType = $partnerInput['type'] ?? null;
                if ($partnerType && $request->hasFile("partners.{$index}.image")) {
                    $maxOrder++;
                    $partnerImage = $this->uploadImage($request->file("partners.{$index}.image"), 'event-partners');
                    EventPartner::create([
                        'event_id' => $event->id,
                        'image' => $partnerImage,
                        'type' => $partnerType,
                        'category' => 'partner',
                        'sort_order' => $maxOrder,
                    ]);
                }
            }
        }

        // Handle new community partners
        if ($request->has('community_partners')) {
            $maxOrder = $event->communityPartners()->max('sort_order') ?? 0;
            foreach ($request->input('community_partners') as $index => $partnerInput) {
                $partnerType = $partnerInput['type'] ?? null;
                if ($partnerType && $request->hasFile("community_partners.{$index}.image")) {
                    $maxOrder++;
                    $partnerImage = $this->uploadImage($request->file("community_partners.{$index}.image"), 'event-partners');
                    EventPartner::create([
                        'event_id' => $event->id,
                        'image' => $partnerImage,
                        'type' => $partnerType,
                        'category' => 'community_partner',
                        'sort_order' => $maxOrder,
                    ]);
                }
            }
        }

        // Handle gallery images
        if ($request->hasFile('gallery')) {
            $maxImageOrder = $event->images()->max('sort_order') ?? -1;
            foreach ($request->file('gallery') as $galleryImage) {
                $maxImageOrder++;
                $imagePath = $this->uploadImage($galleryImage, 'event-gallery');
                EventImage::create([
                    'event_id' => $event->id,
                    'image' => $imagePath,
                    'sort_order' => $maxImageOrder,
                ]);
            }
        }

        return redirect()->route('admin.events.index')
            ->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy($id)
    {
        $event = Event::findOrFail($id);

        // Delete event image
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }

        // Delete partner images
        foreach ($event->partners as $partner) {
            if ($partner->image) {
                Storage::disk('public')->delete($partner->image);
            }
        }

        // Delete gallery images
        foreach ($event->images as $image) {
            if ($image->image) {
                Storage::disk('public')->delete($image->image);
            }
        }

        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted successfully.');
    }

    /**
     * Toggle event active status.
     */
    public function toggleStatus($id)
    {
        $event = Event::findOrFail($id);
        $event->update(['is_active' => !$event->is_active]);

        $status = $event->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.events.index')
            ->with('success', "Event {$status} successfully.");
    }

    /**
     * Delete a specific partner.
     */
    public function destroyPartner($id)
    {
        $partner = EventPartner::findOrFail($id);

        // Delete partner image
        if ($partner->image) {
            Storage::disk('public')->delete($partner->image);
        }

        $partner->delete();

        return back()->with('success', 'Partner removed successfully.');
    }

    /**
     * Update partner order.
     */
    public function updatePartnerOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:event_partners,id',
        ]);

        foreach ($request->input('order') as $index => $partnerId) {
            EventPartner::where('id', $partnerId)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete a specific gallery image.
     */
    public function destroyImage($id)
    {
        $image = EventImage::findOrFail($id);

        // Delete image file
        if ($image->image) {
            Storage::disk('public')->delete($image->image);
        }

        $image->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Update gallery image order.
     */
    public function updateImageOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:event_images,id',
        ]);

        foreach ($request->input('order') as $index => $imageId) {
            EventImage::where('id', $imageId)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
