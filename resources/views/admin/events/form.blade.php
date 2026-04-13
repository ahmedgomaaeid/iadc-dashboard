@extends('layouts.admin-dashboard')

@section('title', isset($event) ? 'Edit Event/Visit' : 'Create Event/Visit')

@section('css')
<style>
    .partner-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        border: 1px solid #dee2e6;
        position: relative;
        transition: all 0.3s ease;
        cursor: grab;
    }
    .partner-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .partner-card .remove-partner {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
    }
    .partner-card .drag-handle {
        position: absolute;
        top: 10px;
        left: 10px;
        cursor: grab;
        color: #6c757d;
        font-size: 18px;
        z-index: 10;
    }
    .partner-card .drag-handle:hover {
        color: #B4120D;
    }
    .partner-card .order-badge {
        position: absolute;
        top: 10px;
        left: 40px;
        background: #B4120D;
        color: #fff;
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 12px;
        z-index: 10;
    }
    .existing-partner-card {
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        border: 1px solid #a5d6a7;
    }
    .partner-type-badge {
        display: inline-block;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .type-main { background: linear-gradient(135deg, #ffd700 0%, #ffb800 100%); color: #000; }
    .type-diamond { background: linear-gradient(135deg, #b9f2ff 0%, #89cff0 100%); color: #000; }
    .type-platinum { background: linear-gradient(135deg, #e5e4e2 0%, #c0c0c0 100%); color: #000; }
    .type-golden { background: linear-gradient(135deg, #ffd700 0%, #daa520 100%); color: #000; }
    .type-silver { background: linear-gradient(135deg, #c0c0c0 0%, #a8a8a8 100%); color: #000; }
    .type-technical { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
    .type-catering { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: #fff; }
    .type-transportation { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: #000; }
    .type-printing { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: #000; }
    .image-preview {
        width: 100%;
        max-width: 200px;
        height: 150px;
        object-fit: cover;
        margin-top: 10px;
        border: 2px solid #dee2e6;
    }
    .event-image-preview {
        width: 100%;
        max-width: 400px;
        height: 200px;
        object-fit: cover;
        margin-top: 10px;
        border: 3px solid #dee2e6;
    }
    #existing-community-partners-container,
    #community-partners-container,
    #existing-partners-container,
    #partners-container {
        max-height: 500px;
        overflow-y: auto;
        padding-right: 10px;
    }
    .sortable-ghost {
        opacity: 0.4;
    }
    .sortable-chosen {
        box-shadow: 0 8px 25px rgba(180, 18, 13, 0.3);
    }
    /* Gallery Styles */
    .gallery-item {
        position: relative;
        display: inline-block;
        margin: 5px;
        border-radius: 8px;
        overflow: hidden;
        cursor: grab;
        transition: all 0.3s ease;
    }
    .gallery-item:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .gallery-item img {
        width: 180px;
        height: 135px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #dee2e6;
    }
    .gallery-item .remove-gallery-image {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(220, 53, 69, 0.9);
        border: none;
        color: white;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        font-size: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .gallery-item:hover .remove-gallery-image {
        opacity: 1;
    }
    .gallery-item .gallery-order {
        position: absolute;
        bottom: 5px;
        left: 5px;
        background: rgba(180, 18, 13, 0.9);
        color: white;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 8px;
    }
    #gallery-container {
        display: flex;
        flex-wrap: wrap;
        min-height: 100px;
        padding: 10px;
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        margin-top: 10px;
    }
    #gallery-container.dragover {
        border-color: #B4120D;
        background: #fff5f5;
    }
    .gallery-empty-state {
        width: 100%;
        text-align: center;
        color: #adb5bd;
        padding: 20px;
    }
</style>
@endsection

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ isset($event) ? 'Edit Event/Visit' : 'Create Event/Visit' }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.events.index') }}">Events & Visits</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ isset($event) ? 'Edit' : 'Create' }}
                </li>
            </ol>
        </div>
    </div>

    <form id="event-form" action="{{ isset($event) ? route('admin.events.update', $event) : route('admin.events.store') }}" 
          method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($event))
            @method('PUT')
        @endif

        <div class="row">
            <!-- Main Event Details -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ isset($event) ? 'Edit Event/Visit' : 'New Event/Visit' }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $event->name ?? '') }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('type') is-invalid @enderror" 
                                        id="type" 
                                        name="type" 
                                        required>
                                    <option value="event" {{ old('type', $event->type ?? 'event') === 'event' ? 'selected' : '' }}>Event</option>
                                    <option value="visit" {{ old('type', $event->type ?? '') === 'visit' ? 'selected' : '' }}>Visit</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control summernote @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description">{{ old('description', $event->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="date_from" class="form-label">Date From <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('date_from') is-invalid @enderror" 
                                       id="date_from" 
                                       name="date_from" 
                                       value="{{ old('date_from', isset($event) ? $event->date_from->format('Y-m-d') : '') }}" 
                                       required>
                                @error('date_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="date_to" class="form-label">Date To <small class="text-muted">(Optional)</small></label>
                                <input type="date" 
                                       class="form-control @error('date_to') is-invalid @enderror" 
                                       id="date_to" 
                                       name="date_to" 
                                       value="{{ old('date_to', isset($event) && $event->date_to ? $event->date_to->format('Y-m-d') : '') }}">
                                @error('date_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Leave empty for single-day events</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="place" class="form-label">Place <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('place') is-invalid @enderror" 
                                       id="place" 
                                       name="place" 
                                       value="{{ old('place', $event->place ?? '') }}" 
                                       required>
                                @error('place')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="attendees_number" class="form-label">Expected Attendees <small class="text-muted">(Optional)</small></label>
                                <input type="number" 
                                       class="form-control @error('attendees_number') is-invalid @enderror" 
                                       id="attendees_number" 
                                       name="attendees_number" 
                                       value="{{ old('attendees_number', $event->attendees_number ?? '') }}"
                                       min="0"
                                       placeholder="e.g. 500">
                                @error('attendees_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="register_link" class="form-label">Register Button Link</label>
                                <input type="url" 
                                       class="form-control @error('register_link') is-invalid @enderror" 
                                       id="register_link" 
                                       name="register_link" 
                                       value="{{ old('register_link', $event->register_link ?? '') }}"
                                       placeholder="https://example.com/register">
                                @error('register_link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3 d-flex align-items-center">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="register_active" 
                                           name="register_active" 
                                           value="1"
                                           {{ old('register_active', $event->register_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="register_active">
                                        Register Button Active
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Event Image</label>
                            <input type="file" 
                                   class="form-control @error('image') is-invalid @enderror" 
                                   id="image" 
                                   name="image"
                                   accept="image/*"
                                   onchange="previewEventImage(this)">
                            @if(isset($event) && $event->image)
                                <img src="{{ asset('storage/' . $event->image) }}" 
                                     alt="Current event image" 
                                     id="event-image-preview" 
                                     class="event-image-preview">
                            @else
                                <img src="" alt="" id="event-image-preview" class="event-image-preview" style="display: none;">
                            @endif
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Gallery Images Section -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fe fe-image me-1"></i>Event Gallery 
                                <small class="text-muted">(Multiple images, drag to reorder)</small>
                            </label>
                            <input type="file" 
                                   class="form-control @error('gallery.*') is-invalid @enderror" 
                                   id="gallery" 
                                   name="gallery[]"
                                   accept="image/*"
                                   multiple
                                   onchange="previewNewGalleryImages(this)">
                            @error('gallery.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            
                            <div id="gallery-container">
                                @if(isset($event) && $event->images->count() > 0)
                                    @foreach($event->images as $index => $image)
                                        <div class="gallery-item" data-image-id="{{ $image->id }}">
                                            <img src="{{ asset('storage/' . $image->image) }}" alt="Gallery image">
                                            <button type="button" class="remove-gallery-image" onclick="deleteGalleryImage({{ $image->id }})">
                                                <i class="fe fe-x"></i>
                                            </button>
                                            <span class="gallery-order">{{ $index + 1 }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="gallery-empty-state" id="gallery-empty-state">
                                        <i class="fe fe-image" style="font-size: 32px;"></i>
                                        <p class="mb-0 mt-2">No gallery images yet</p>
                                        <small>Upload images to create a carousel</small>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Preview for new uploads -->
                            <div id="new-gallery-preview" class="mt-2" style="display: flex; flex-wrap: wrap;"></div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $event->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                            <small class="text-muted">Inactive events will not be displayed on the public site</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Partners Section -->
            <div class="col-lg-4">
                <!-- Community Partners Section (full-width separate row above partners) -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0"><i class="fe fe-users me-2"></i>Community Partners</h3>
                        <button type="button" class="btn btn-sm btn-success" onclick="addCommunityPartner()">
                            <i class="fe fe-plus me-1"></i>Add Community Partner
                        </button>
                    </div>
                    <div class="card-body">
                        @if(isset($event) && $event->communityPartners->count() > 0)
                            <h6 class="text-muted mb-3"><i class="fe fe-move me-1"></i>Existing Community Partners (Drag to Reorder)</h6>
                            <div id="existing-community-partners-container" style="display:flex; flex-wrap:wrap; gap:12px; padding:4px;">
                                @foreach($event->communityPartners as $index => $partner)
                                    <div class="partner-card existing-partner-card" id="existing-community-partner-{{ $partner->id }}" data-partner-id="{{ $partner->id }}"
                                        style="width:160px; min-height:auto; flex-shrink:0;">
                                        <span class="drag-handle" style="position:absolute;top:6px;left:8px;"><i class="fe fe-menu"></i></span>
                                        <span class="order-badge" style="left:30px;">{{ $index + 1 }}</span>
                                        <button type="button" class="btn btn-sm btn-danger remove-partner"
                                                onclick="deleteCommunityPartner({{ $partner->id }})">
                                            <i class="fe fe-x"></i>
                                        </button>
                                        <div class="text-center" style="padding-top:10px;">
                                            <img src="{{ asset('storage/' . $partner->image) }}"
                                                alt="Community Partner"
                                                style="width:120px;height:90px;object-fit:contain;border-radius:8px;border:1px solid #dee2e6;">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <hr>
                        @endif

                        <!-- New Community Partners Container -->
                        <div id="community-partners-container" style="display:flex; flex-wrap:wrap; gap:12px; padding:4px;"></div>
                        <p class="text-muted small mt-2 mb-0"><i class="fe fe-info me-1"></i>Upload logos only — no type required for community partners.</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Partners</h3>
                        <button type="button" class="btn btn-sm btn-success" onclick="addPartner()">
                            <i class="fe fe-plus me-1"></i>Add Partner
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Existing Partners -->
                        @if(isset($event) && $event->partners->count() > 0)
                            <h6 class="text-muted mb-3"><i class="fe fe-move me-1"></i>Existing Partners (Drag to Reorder)</h6>
                            <div id="existing-partners-container">
                                @foreach($event->partners as $index => $partner)
                                    <div class="partner-card existing-partner-card" id="existing-partner-{{ $partner->id }}" data-partner-id="{{ $partner->id }}">
                                        <span class="drag-handle"><i class="fe fe-menu"></i></span>
                                        <span class="order-badge">{{ $index + 1 }}</span>
                                        <button type="button" class="btn btn-sm btn-danger remove-partner"
                                                onclick="deletePartner({{ $partner->id }})">
                                            <i class="fe fe-x"></i>
                                        </button>
                                        <div class="text-center">
                                            <img src="{{ asset('storage/' . $partner->image) }}"
                                                 alt="Partner"
                                                 class="image-preview mb-2">
                                            <div>
                                                <span class="partner-type-badge type-{{ $partner->type }}">
                                                    {{ $partner->type_name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <hr>
                        @endif

                        <!-- New Partners Container -->
                        <div id="partners-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Links Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Related Links</div>
                    </div>
                    <div class="card-body">
                        <label class="form-label d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold"><i class="fe fe-link me-1"></i>Links</span>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" onclick="addLink()">
                                <i class="fe fe-plus me-1"></i>Add Link
                            </button>
                        </label>
                        <div id="links-container">
                            @if(isset($event) && $event->links->count() > 0)
                                @foreach($event->links as $index => $link)
                                    <div class="row g-2 mb-2 link-row align-items-center" id="link-row-{{ $index }}">
                                        <div class="col-md-5">
                                            <div class="input-group">
                                                <span class="input-group-text bg-white"><i class="fe fe-type"></i></span>
                                                <input type="text" class="form-control" name="links[{{ $index }}][name]" placeholder="Link Title" value="{{ $link->name }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <span class="input-group-text bg-white"><i class="fe fe-link"></i></span>
                                                <input type="url" class="form-control" name="links[{{ $index }}][url]" placeholder="URL (https://...)" value="{{ $link->url }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeLink({{ $index }})">
                                                <i class="fe fe-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <small class="text-muted ms-1">Add external resources, registration forms, or related documents.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fe fe-save me-2"></i>{{ isset($event) ? 'Update' : 'Create' }} Event/Visit
                    </button>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-secondary btn-lg">
                        <i class="fe fe-x me-2"></i>Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    let partnerIndex = 0;

    $(document).ready(function() {
        // Initialize Summernote
        $('#description').summernote({
            placeholder: 'Write your event description here...',
            tabsize: 2,
            height: 250,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'italic', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            callbacks: {
                onChange: function(contents, $editable) {
                    $('#description').val(contents);
                }
            }
        });

        // Ensure content is synced on form submit
        $('#event-form').on('submit', function() {
            if ($('#description').summernote('isEmpty')) {
                $('#description').val('');
            } else {
                $('#description').val($('#description').summernote('code'));
            }
        });

        // Initialize Sortable for existing community partners
        const existingCommunityPartnersContainer = document.getElementById('existing-community-partners-container');
        if (existingCommunityPartnersContainer) {
            new Sortable(existingCommunityPartnersContainer, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                handle: '.drag-handle',
                onEnd: function() {
                    updateCommunityPartnerOrder();
                }
            });
        }

        // Initialize Sortable for existing partners
        const existingPartnersContainer = document.getElementById('existing-partners-container');
        if (existingPartnersContainer) {
            new Sortable(existingPartnersContainer, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                handle: '.drag-handle',
                onEnd: function() {
                    updatePartnerOrder();
                }
            });
        }

        // Initialize Sortable for gallery images
        const galleryContainer = document.getElementById('gallery-container');
        if (galleryContainer) {
            new Sortable(galleryContainer, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                filter: '.gallery-empty-state',
                onEnd: function() {
                    updateGalleryOrder();
                }
            });
        }
    });

    function updatePartnerOrder() {
        const container = document.getElementById('existing-partners-container');
        if (!container) return;

        const partnerCards = container.querySelectorAll('.partner-card[data-partner-id]');
        const order = [];

        // Update order badges and collect IDs
        partnerCards.forEach((card, index) => {
            const partnerId = card.dataset.partnerId;
            order.push(partnerId);

            // Update the order badge
            const badge = card.querySelector('.order-badge');
            if (badge) {
                badge.textContent = index + 1;
            }
        });

        // Save the new order via AJAX
        $.ajax({
            url: '{{ route("admin.events.partners.update-order") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                order: order
            },
            success: function(response) {
                // Order saved successfully - optional: show toast notification
                console.log('Partner order updated successfully');
            },
            error: function(xhr) {
                console.error('Error updating partner order:', xhr);
                alert('Error updating partner order. Please try again.');
            }
        });
    }

    function addCommunityPartner() {
        const container = document.getElementById('community-partners-container');

        const html = `
            <div style="position:relative; width:170px; flex-shrink:0;" id="community-partner-${partnerIndex}">
                <div style="background:#f8f9fa; border:1px solid #dee2e6; border-radius:12px; padding:12px; text-align:center;">
                    <button type="button" onclick="removeCommunityPartner(${partnerIndex})"
                        style="position:absolute;top:6px;right:6px;background:rgba(220,53,69,0.9);border:none;color:white;width:22px;height:22px;border-radius:50%;font-size:13px;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:2;">
                        &times;
                    </button>
                    <img src="" alt="" id="community-preview-${partnerIndex}"
                         style="width:120px;height:90px;object-fit:contain;border:1px solid #dee2e6;display:none;margin-bottom:8px;">
                    <label style="font-size:0.78rem;font-weight:600;color:#6c757d;display:block;margin-bottom:6px;">Logo</label>
                    <input type="file"
                           class="form-control form-control-sm"
                           name="community_partners[${partnerIndex}][image]"
                           accept="image/*"
                           required
                           onchange="previewCommunityPartnerImage(this, ${partnerIndex})">
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', html);
        partnerIndex++;
    }

    function removeCommunityPartner(index) {
        const element = document.getElementById(`community-partner-${index}`);
        if (element) element.remove();
    }

    function previewCommunityPartnerImage(input, index) {
        const preview = document.getElementById(`community-preview-${index}`);
        if (input.files && input.files[0] && preview) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function updateCommunityPartnerOrder() {
        const container = document.getElementById('existing-community-partners-container');
        if (!container) return;

        const cards = container.querySelectorAll('.partner-card[data-partner-id]');
        const order = [];

        cards.forEach((card, index) => {
            order.push(card.dataset.partnerId);
            const badge = card.querySelector('.order-badge');
            if (badge) badge.textContent = index + 1;
        });

        $.ajax({
            url: '{{ route("admin.events.partners.update-order") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', order: order },
            success: function() { console.log('Community partner order updated'); },
            error: function(xhr) { console.error('Error:', xhr); }
        });
    }

    function deleteCommunityPartner(partnerId) {
        if (!confirm('Are you sure you want to remove this community partner?')) return;

        $.ajax({
            url: `/admin/events/partners/${partnerId}`,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function() {
                $(`#existing-community-partner-${partnerId}`).fadeOut(300, function() {
                    $(this).remove();
                });
            },
            error: function(xhr) {
                alert('Error deleting community partner. Please try again.');
                console.error(xhr);
            }
        });
    }

    function addPartner() {
        const container = document.getElementById('partners-container');
        const partnerTypes = @json($partnerTypes);
        
        let typeOptions = '';
        for (const [key, value] of Object.entries(partnerTypes)) {
            typeOptions += `<option value="${key}">${value}</option>`;
        }

        const partnerHtml = `
            <div class="partner-card" id="partner-${partnerIndex}">
                <button type="button" class="btn btn-sm btn-danger remove-partner" onclick="removePartner(${partnerIndex})">
                    <i class="fe fe-x"></i>
                </button>
                <div class="mb-3">
                    <label class="form-label">Partner Type <span class="text-danger">*</span></label>
                    <select class="form-control" name="partners[${partnerIndex}][type]" required>
                        <option value="">Select Type</option>
                        ${typeOptions}
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Partner Logo <span class="text-danger">*</span></label>
                    <input type="file" 
                           class="form-control" 
                           name="partners[${partnerIndex}][image]" 
                           accept="image/*"
                           required
                           onchange="previewPartnerImage(this, ${partnerIndex})">
                    <img src="" alt="" id="partner-preview-${partnerIndex}" class="image-preview" style="display: none;">
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', partnerHtml);
        partnerIndex++;
    }

    function removePartner(index) {
        const element = document.getElementById(`partner-${index}`);
        if (element) {
            element.remove();
        }
    }

    function previewEventImage(input) {
        const preview = document.getElementById('event-image-preview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewPartnerImage(input, index) {
        const preview = document.getElementById(`partner-preview-${index}`);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function deletePartner(partnerId) {
        if (!confirm('Are you sure you want to remove this partner?')) {
            return;
        }
        
        $.ajax({
            url: `/admin/events/partners/${partnerId}`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            },
            success: function(response) {
                $(`#existing-partner-${partnerId}`).fadeOut(300, function() {
                    $(this).remove();
                    // Update order badges after removal
                    updateOrderBadges();
                });
            },
            error: function(xhr) {
                alert('Error deleting partner. Please try again.');
                console.error(xhr);
            }
        });
    }

    function updateOrderBadges() {
        const container = document.getElementById('existing-partners-container');
        if (!container) return;

        const partnerCards = container.querySelectorAll('.partner-card[data-partner-id]');
        partnerCards.forEach((card, index) => {
            const badge = card.querySelector('.order-badge');
            if (badge) {
                badge.textContent = index + 1;
            }
        });
    }

    // Gallery Functions
    function updateGalleryOrder() {
        const container = document.getElementById('gallery-container');
        if (!container) return;

        const galleryItems = container.querySelectorAll('.gallery-item[data-image-id]');
        const order = [];

        // Update order badges and collect IDs
        galleryItems.forEach((item, index) => {
            const imageId = item.dataset.imageId;
            order.push(imageId);

            // Update the order badge
            const badge = item.querySelector('.gallery-order');
            if (badge) {
                badge.textContent = index + 1;
            }
        });

        if (order.length === 0) return;

        // Save the new order via AJAX
        $.ajax({
            url: '{{ route("admin.events.images.update-order") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                order: order
            },
            success: function(response) {
                console.log('Gallery order updated successfully');
            },
            error: function(xhr) {
                console.error('Error updating gallery order:', xhr);
                alert('Error updating gallery order. Please try again.');
            }
        });
    }

    function deleteGalleryImage(imageId) {
        if (!confirm('Are you sure you want to remove this image?')) {
            return;
        }
        
        $.ajax({
            url: `/admin/events/images/${imageId}`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            },
            success: function(response) {
                const item = document.querySelector(`.gallery-item[data-image-id="${imageId}"]`);
                if (item) {
                    item.style.transition = 'opacity 0.3s, transform 0.3s';
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        item.remove();
                        updateGalleryOrderBadges();
                        checkGalleryEmpty();
                    }, 300);
                }
            },
            error: function(xhr) {
                alert('Error deleting image. Please try again.');
                console.error(xhr);
            }
        });
    }

    function updateGalleryOrderBadges() {
        const container = document.getElementById('gallery-container');
        if (!container) return;

        const galleryItems = container.querySelectorAll('.gallery-item[data-image-id]');
        galleryItems.forEach((item, index) => {
            const badge = item.querySelector('.gallery-order');
            if (badge) {
                badge.textContent = index + 1;
            }
        });
    }

    function checkGalleryEmpty() {
        const container = document.getElementById('gallery-container');
        const items = container.querySelectorAll('.gallery-item[data-image-id]');
        const emptyState = document.getElementById('gallery-empty-state');
        
        if (items.length === 0 && !emptyState) {
            container.innerHTML = `
                <div class="gallery-empty-state" id="gallery-empty-state">
                    <i class="fe fe-image" style="font-size: 32px;"></i>
                    <p class="mb-0 mt-2">No gallery images yet</p>
                    <small>Upload images to create a carousel</small>
                </div>
            `;
        }
    }

    function previewNewGalleryImages(input) {
        const previewContainer = document.getElementById('new-gallery-preview');
        previewContainer.innerHTML = '';
        
        if (input.files && input.files.length > 0) {
            // Hide empty state if visible
            const emptyState = document.getElementById('gallery-empty-state');
            if (emptyState) {
                emptyState.style.display = 'none';
            }

            Array.from(input.files).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewHtml = `
                        <div class="gallery-item" style="opacity: 0.7; border: 2px dashed #B4120D;">
                            <img src="${e.target.result}" alt="New image">
                            <span class="gallery-order" style="background: rgba(40, 167, 69, 0.9);">New</span>
                        </div>
                    `;
                    previewContainer.insertAdjacentHTML('beforeend', previewHtml);
                };
                reader.readAsDataURL(file);
            });
        }
    }
    let linkIndex = {{ isset($event) ? $event->links->count() : 0 }};

    function addLink() {
        const container = document.getElementById('links-container');
        const html = `
            <div class="row g-2 mb-2 link-row align-items-center" id="link-row-${linkIndex}">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fe fe-type"></i></span>
                        <input type="text" class="form-control" name="links[${linkIndex}][name]" placeholder="Link Title" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fe fe-link"></i></span>
                        <input type="url" class="form-control" name="links[${linkIndex}][url]" placeholder="URL (https://...)" required>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeLink(${linkIndex})">
                        <i class="fe fe-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        linkIndex++;
    }

    function removeLink(index) {
        const element = document.getElementById(`link-row-${index}`);
        if (element) {
            element.remove();
        }
    }
</script>
@endsection
