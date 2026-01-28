@php
    $isEdit = isset($dynamicForm);
    $formAction = $isEdit 
        ? route('admin.dynamic-forms.update', $dynamicForm) 
        : route('admin.dynamic-forms.store');
    $formTitle = $isEdit ? 'Edit Dynamic Form' : 'Create Dynamic Form';
    
    // Get currently selected fields for edit mode
    $selectedFields = $isEdit ? collect($dynamicForm->fields)->pluck('name')->toArray() : [];
@endphp

@section('css')
<style>
    .field-item {
        padding: 12px 15px;
        margin-bottom: 8px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        cursor: grab;
        background: #fff;
        transition: all 0.2s ease;
    }
    .field-item:hover {
        border-color: #B4120D;
        box-shadow: 0 2px 8px rgba(180, 18, 13, 0.1);
    }
    .field-item.selected {
        border-color: #B4120D;
        background: linear-gradient(135deg, #fff5f5 0%, #fff 100%);
    }
    .field-item .drag-handle {
        cursor: grab;
        color: #adb5bd;
        margin-right: 10px;
    }
    .field-item .field-icon {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        background: linear-gradient(135deg, #B4120D 0%, #8b0e0a 100%);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 14px;
    }
    .field-item .field-label {
        font-weight: 600;
        color: #374151;
    }
    .field-item .field-type {
        font-size: 12px;
        color: #6b7280;
    }
    .field-item .form-check {
        margin-left: auto;
    }
    .selected-fields-container {
        min-height: 200px;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 15px;
        background: #f8f9fa;
    }
    .selected-fields-container.dragover {
        border-color: #B4120D;
        background: #fff5f5;
    }
    .selected-fields-container .empty-state {
        text-align: center;
        color: #adb5bd;
        padding: 40px;
    }
    .sortable-ghost {
        opacity: 0.4;
    }
    .order-badge {
        background: #B4120D;
        color: #fff;
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 12px;
        margin-right: 8px;
    }
</style>
@endsection

<div class="page-header">
    <h1 class="page-title">{{ $formTitle }}</h1>
    <div>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.dynamic-forms.index') }}">Dynamic Forms</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $isEdit ? 'Edit' : 'Create' }}</li>
        </ol>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ $formAction }}" method="POST" id="dynamicFormBuilder" enctype="multipart/form-data">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Form Details</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Form Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                            value="{{ old('title', $dynamicForm->title ?? '') }}" required
                            placeholder="e.g., Registration Form">
                    </div>
                    <div class="mb-3">
                        <label for="subtitle" class="form-label">Form Subtitle</label>
                        <input type="text" class="form-control" id="subtitle" name="subtitle" 
                            value="{{ old('subtitle', $dynamicForm->subtitle ?? '') }}"
                            placeholder="e.g., Join our community today">
                    </div>
                    <div class="mb-3">
                        <label for="form_image" class="form-label">Form Image <span class="text-muted">(Optional)</span></label>
                        <input type="file" class="form-control" id="form_image" name="form_image" accept="image/*">
                        <small class="text-muted">Recommended: Landscape orientation. Max size: 2MB.</small>
                        @if(isset($dynamicForm) && $dynamicForm->form_image)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $dynamicForm->form_image) }}" alt="Current Form Image" class="img-thumbnail" style="max-height: 150px;">
                            </div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="subdomain" class="form-label">Subdomain <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="subdomain" name="subdomain" 
                                value="{{ old('subdomain', $dynamicForm->subdomain ?? '') }}" required
                                placeholder="e.g., registration-2025" pattern="[a-z0-9-]+"
                                oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '')">
                            <span class="input-group-text">.form.iadcsuez.org</span>
                        </div>
                        <small class="text-muted">Only lowercase letters, numbers, and hyphens allowed. URL will be: <strong>https://{subdomain}.form.iadcsuez.org</strong></small>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                {{ old('is_active', $dynamicForm->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Form is Active</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Available Fields</h3>
                    <p class="text-muted mb-0 small">Click to add fields to your form</p>
                </div>
                <div class="card-body">
                    @foreach($availableFields as $fieldName => $fieldConfig)
                        <div class="field-item d-flex align-items-center {{ in_array($fieldName, $selectedFields) ? 'selected' : '' }}" 
                            data-field="{{ $fieldName }}" onclick="toggleField('{{ $fieldName }}')">
                            <span class="field-icon">
                                <i class="fas {{ $fieldConfig['icon'] }}"></i>
                            </span>
                            <div>
                                <span class="field-label">{{ $fieldConfig['label'] }}</span>
                                <br>
                                <span class="field-type">{{ ucfirst($fieldConfig['type']) }} {{ $fieldConfig['required'] ? '• Required' : '• Optional' }}</span>
                            </div>
                            <div class="form-check ms-auto">
                                <input class="form-check-input" type="checkbox" id="check_{{ $fieldName }}" 
                                    {{ in_array($fieldName, $selectedFields) ? 'checked' : '' }}>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Custom Fields</h3>
                    <p class="text-muted mb-0 small">Add fields with your own labels</p>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="custom_field_label" class="form-label">Field Label</label>
                        <input type="text" class="form-control" id="custom_field_label" placeholder="e.g. Flight Number">
                    </div>

                    <div class="mb-3">
                        <label for="custom_field_placeholder" class="form-label">Placeholder</label>
                        <input type="text" class="form-control" id="custom_field_placeholder" placeholder="e.g. Enter your flight number">
                    </div>
                    
                    <div class="mb-3">
                        <label for="custom_field_required" class="form-label form-check-label d-flex align-items-center">
                            <input type="checkbox" class="form-check-input me-2" id="custom_field_required">
                            Required Field
                        </label>
                    </div>

                    <button type="button" class="btn btn-outline-primary w-100" onclick="addCustomField()">
                        <i class="fas fa-plus me-2"></i>Add Custom Field
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Selected Fields (Drag to Reorder)</h3>
                    <p class="text-muted mb-0 small">Drag fields to change their order</p>
                </div>
                <div class="card-body">
                    <div class="selected-fields-container" id="selectedFieldsContainer">
                        <div class="empty-state" id="emptyState">
                            <i class="fe fe-inbox" style="font-size: 48px;"></i>
                            <p class="mt-2">Click on fields to add them here</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fe fe-save me-2"></i>{{ $isEdit ? 'Update Form' : 'Create Form' }}
                    </button>
                    <a href="{{ route('admin.dynamic-forms.index') }}" class="btn btn-outline-secondary btn-lg w-100 mt-2">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden input for fields data -->
    <input type="hidden" name="fields" id="fieldsInput">
</form>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    const availableFields = @json($availableFields);
    let selectedFields = @json($isEdit ? $dynamicForm->fields : []);
    
    const container = document.getElementById('selectedFieldsContainer');
    const emptyState = document.getElementById('emptyState');
    const fieldsInput = document.getElementById('fieldsInput');

    // Initialize Sortable
    const sortable = new Sortable(container, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        handle: '.drag-handle',
        filter: '.empty-state',
        onEnd: updateFieldsOrder
    });

    // Initial render
    renderSelectedFields();

    function toggleField(fieldName) {
        const index = selectedFields.findIndex(f => f.name === fieldName);
        const fieldItem = document.querySelector(`.field-item[data-field="${fieldName}"]`);
        const checkbox = document.getElementById(`check_${fieldName}`);
        
        if (index > -1) {
            // Remove field
            selectedFields.splice(index, 1);
            fieldItem.classList.remove('selected');
            checkbox.checked = false;
        } else {
            // Add field
            selectedFields.push({
                name: fieldName,
                order: selectedFields.length + 1
            });
            fieldItem.classList.add('selected');
            checkbox.checked = true;
        }
        
        renderSelectedFields();
    }

    function renderSelectedFields() {
        // Remove existing field items (keep empty state)
        container.querySelectorAll('.field-item').forEach(el => el.remove());
        
        if (selectedFields.length === 0) {
            emptyState.style.display = 'block';
        } else {
            emptyState.style.display = 'none';
            
            // Sort by order
            selectedFields.sort((a, b) => a.order - b.order);
            
            
            selectedFields.forEach((field, index) => {
                // Determine config: either from availableFields or custom config stored in field object
                let config = availableFields[field.name];
                
                // If it's a custom field (not in availableFields), use the stored config
                if (!config && field.name.startsWith('custom_')) {
                    config = {
                        label: field.label,
                        type: field.type || 'text',
                        required: field.required,
                        icon: field.icon || 'fa-pen',
                        placeholder: field.placeholder || ''
                    };
                }

                if (!config) return;
                
                const div = document.createElement('div');
                div.className = 'field-item d-flex align-items-center selected';
                div.dataset.field = field.name;
                div.innerHTML = `
                    <span class="drag-handle"><i class="fe fe-menu"></i></span>
                    <span class="order-badge">${index + 1}</span>
                    <span class="field-icon">
                        <i class="fas ${config.icon}"></i>
                    </span>
                    <div>
                        <span class="field-label">${config.label}</span>
                        <br>
                        <span class="field-type">${config.type.charAt(0).toUpperCase() + config.type.slice(1)} ${config.required ? '• Required' : '• Optional'}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="removeField('${field.name}')">
                        <i class="fe fe-x"></i>
                    </button>
                `;
                container.appendChild(div);
            });
        }
        
        updateFieldsInput();
    }

    function addCustomField() {
        const labelInput = document.getElementById('custom_field_label');
        const placeholderInput = document.getElementById('custom_field_placeholder');
        const requiredInput = document.getElementById('custom_field_required');
        const label = labelInput.value.trim();
        const placeholder = placeholderInput.value.trim();
        
        if (!label) {
            alert('Please enter a field label');
            return;
        }

        const timestamp = new Date().getTime();
        const fieldName = `custom_${timestamp}`;
        
        selectedFields.push({
            name: fieldName,
            label: label,
            type: 'text',
            required: requiredInput.checked,
            icon: 'fa-pen', // Default icon for custom fields
            placeholder: placeholder,
            order: selectedFields.length + 1
        });

        // Reset inputs
        labelInput.value = '';
        placeholderInput.value = '';
        requiredInput.checked = false;

        renderSelectedFields();
    }

    function removeField(fieldName) {
        const index = selectedFields.findIndex(f => f.name === fieldName);
        if (index > -1) {
            selectedFields.splice(index, 1);
            
            // Handle clearing checkbox if it was a predefined field
            if (document.getElementById(`check_${fieldName}`)) {
                const fieldItem = document.querySelector(`.card-body > .field-item[data-field="${fieldName}"]`);
                if (fieldItem) {
                    fieldItem.classList.remove('selected');
                }
                const checkbox = document.getElementById(`check_${fieldName}`);
                if (checkbox) checkbox.checked = false;
            }
            
            renderSelectedFields();
        }
    }

    function updateFieldsOrder() {
        const items = container.querySelectorAll('.field-item[data-field]');
        const newSelectedFields = [];
        
        items.forEach((item, index) => {
            const fieldName = item.dataset.field;
            // Find existing field data to preserve custom props (label, etc.)
            const existingField = selectedFields.find(f => f.name === fieldName);
            
            if (existingField) {
                newSelectedFields.push({
                    ...existingField, // Keep all properties (important for custom fields)
                    order: index + 1
                });
            }
        });
        
        selectedFields = newSelectedFields;
        renderSelectedFields();
    }

    function updateFieldsInput() {
        fieldsInput.value = JSON.stringify(selectedFields);
    }

    // Form validation
    document.getElementById('dynamicFormBuilder').addEventListener('submit', function(e) {
        if (selectedFields.length === 0) {
            e.preventDefault();
            alert('Please select at least one field for the form.');
            return false;
        }
    });
</script>
@endsection
