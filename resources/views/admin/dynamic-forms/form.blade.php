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
    .section-item {
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 10px;
        padding: 10px 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
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
                        <label for="subtitle" class="form-label">Form Title</label>
                        <input type="text" class="form-control" id="subtitle" name="subtitle" 
                            value="{{ old('subtitle', $dynamicForm->subtitle ?? '') }}"
                            placeholder="e.g., Join our community today">
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Form Subtitle<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                            value="{{ old('title', $dynamicForm->title ?? '') }}" required
                            placeholder="e.g., Registration Form">
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
                    <div class="mb-3">
                        <label for="highboards" class="form-label">Assign to Highboards <span class="text-muted">(Optional)</span></label>
                        <select class="form-select select2" id="highboards" name="highboards[]" multiple data-placeholder="Select highboards...">
                            @foreach($highboards ?? [] as $highboard)
                                <option value="{{ $highboard->id }}" {{ (isset($dynamicForm) && $dynamicForm->highboards->contains($highboard->id)) ? 'selected' : (is_array(old('highboards')) && in_array($highboard->id, old('highboards')) ? 'selected' : '') }}>
                                    {{ $highboard->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold CTRL (or CMD) to select multiple. Leave empty to prevent highboard access.</small>
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
                        <label for="custom_field_type" class="form-label">Field Type</label>
                        <select class="form-select" id="custom_field_type">
                            <option value="text" selected>Text Input</option>
                            <option value="select">Select Dropdown</option>
                            <option value="email">Email</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="textarea">Text Area</option>
                            <option value="file">Image Upload</option>
                        </select>
                    </div>

                    <div id="options_container" class="mb-3" style="display: none;">
                        <label class="form-label">Options</label>
                        <div id="options_list">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control option-input" placeholder="Option Value">
                                <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addOptionInput()">
                            <i class="fas fa-plus me-1"></i> Add Option
                        </button>
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
                    <h3 class="card-title">Form Sections <span class="text-muted">(Optional)</span></h3>
                    <p class="text-muted mb-0 small">Group fields into a multi-step wizard. Skip for a single-page form.</p>
                </div>
                <div class="card-body">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="newSectionName" placeholder="e.g. Personal Information">
                        <button class="btn btn-outline-primary" type="button" onclick="addSection()"><i class="fas fa-plus"></i> Add Section</button>
                    </div>
                    <div id="sectionsList">
                        <!-- Sections list goes here -->
                    </div>
                </div>
            </div>

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
    <input type="hidden" name="sections" id="sectionsInput">
</form>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    const availableFields = @json($availableFields);
    let selectedFields = @json($isEdit ? $dynamicForm->fields : []);
    let formSections = @json($isEdit ? ($dynamicForm->sections ?? []) : []);
    
    const container = document.getElementById('selectedFieldsContainer');
    const emptyState = document.getElementById('emptyState');
    const fieldsInput = document.getElementById('fieldsInput');
    const fieldTypeSelect = document.getElementById('custom_field_type');
    const optionsContainer = document.getElementById('options_container');

    // Initialize Sortable
    const sortable = new Sortable(container, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        handle: '.drag-handle',
        filter: '.empty-state',
        onEnd: updateFieldsOrder
    });

    // Initial render
    renderSections();
    renderSelectedFields();
    
    // Handle Field Type Change
    if(fieldTypeSelect) {
        fieldTypeSelect.addEventListener('change', function() {
            if (this.value === 'select') {
                optionsContainer.style.display = 'block';
            } else {
                optionsContainer.style.display = 'none';
            }
        });
    }

    function addOptionInput() {
        const optionsList = document.getElementById('options_list');
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `
            <input type="text" class="form-control option-input" placeholder="Option Value">
            <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        optionsList.appendChild(div);
    }

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
                        icon: field.icon || 'fa-pen', // Default icon
                        placeholder: field.placeholder || '',
                        options: field.options || []
                    };
                }

                if (!config) return;
                
                let details = `<span class="field-type">${config.type.charAt(0).toUpperCase() + config.type.slice(1)} ${config.required ? '• Required' : '• Optional'}</span>`;
                
                if (config.type === 'select' && config.options && config.options.length > 0) {
                     details += `<br><span class="text-muted small">Options: ${config.options.join(', ')}</span>`;
                }

                let sectionDropdownUrl = "";
                if (formSections.length > 0) {
                    sectionDropdownUrl = `<div class="mt-2"><select class="form-select form-select-sm" onchange="updateFieldSection('${field.name}', this.value)"><option value="">Default (No Section)</option>`;
                    formSections.forEach(sec => {
                        const selected = (field.section_id === sec.id) ? 'selected' : '';
                        sectionDropdownUrl += `<option value="${sec.id}" ${selected}>${sec.name}</option>`;
                    });
                    sectionDropdownUrl += `</select></div>`;
                }

                // Build an array of selectable fields that appear BEFORE this current field
                let dependsOptions = `<option value="">None (Always show)</option>`;
                selectedFields.slice(0, index).forEach(prevField => {
                    let prevConfig = availableFields[prevField.name];
                    if (!prevConfig && prevField.name.startsWith('custom_')) {
                        prevConfig = { label: prevField.label, type: prevField.type };
                    }
                    if (prevConfig && prevConfig.type === 'select') {
                        const selected = (field.depends_on === prevField.name) ? 'selected' : '';
                        dependsOptions += `<option value="${prevField.name}" ${selected}>${prevConfig.label}</option>`;
                    }
                });

                let conditionBlock = `
                    <div class="mt-2 p-2" style="background:#f1f5f9; border-radius:6px; font-size:12px;">
                        <div class="d-flex align-items-center">
                            <label class="me-2 text-muted fw-bold">Depends On:</label>
                            <select class="form-select form-select-sm d-inline-block w-auto me-2" onchange="updateFieldCondition('${field.name}', 'depends_on', this.value)">
                                ${dependsOptions}
                            </select>
                            <input type="text" class="form-control form-control-sm d-inline-block w-auto" placeholder="Required Value" value="${field.depends_value || ''}" onchange="updateFieldCondition('${field.name}', 'depends_value', this.value)" ${field.depends_on ? '' : 'style="display:none;"'} id="depends_val_${field.name}">
                        </div>
                    </div>
                `;

                const div = document.createElement('div');
                div.className = 'field-item d-flex align-items-center selected';
                div.dataset.field = field.name;
                div.innerHTML = `
                    <span class="drag-handle"><i class="fe fe-menu"></i></span>
                    <span class="order-badge">${index + 1}</span>
                    <span class="field-icon">
                        <i class="fas ${config.icon}"></i>
                    </span>
                    <div style="flex:1; margin-right: 15px;">
                        <span class="field-label">${config.label}</span>
                        <br>
                        ${details}
                        ${sectionDropdownUrl}
                        ${conditionBlock}
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
        const typeSelect = document.getElementById('custom_field_type');
        const placeholderInput = document.getElementById('custom_field_placeholder');
        const requiredInput = document.getElementById('custom_field_required');
        const label = labelInput.value.trim();
        const placeholder = placeholderInput.value.trim();
        const type = typeSelect ? typeSelect.value : 'text';
        
        if (!label) {
            alert('Please enter a field label');
            return;
        }

        let options = [];
        if (type === 'select') {
            document.querySelectorAll('.option-input').forEach(input => {
                if(input.value.trim()) {
                    options.push(input.value.trim());
                }
            });
            
            if (options.length === 0) {
                alert('Please add at least one option for the select field.');
                return;
            }
        }

        const timestamp = new Date().getTime();
        const fieldName = `custom_${timestamp}`;
        
        let icon = 'fa-pen';
        if (type === 'select') icon = 'fa-list';
        if (type === 'file') icon = 'fa-image';
        
        selectedFields.push({
            name: fieldName,
            label: label,
            type: type,
            required: requiredInput.checked,
            icon: icon,
            placeholder: placeholder,
            order: selectedFields.length + 1,
            options: options
        });

        // Reset inputs
        labelInput.value = '';
        placeholderInput.value = '';
        if (placeholderInput) placeholderInput.value = '';
        requiredInput.checked = false;
        if(typeSelect) typeSelect.value = 'text';
        
        // Reset options
        if (optionsContainer) {
            optionsContainer.style.display = 'none';
            document.getElementById('options_list').innerHTML = `
                <div class="input-group mb-2">
                    <input type="text" class="form-control option-input" placeholder="Option Value">
                    <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        }

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

    function updateFieldSection(fieldName, sectionId) {
        const field = selectedFields.find(f => f.name === fieldName);
        if (field) {
            field.section_id = sectionId || null;
            updateFieldsInput();
        }
    }

    function updateFieldCondition(fieldName, key, value) {
        const field = selectedFields.find(f => f.name === fieldName);
        if (field) {
            if (key === 'depends_on') {
                field.depends_on = value || null;
                const valInput = document.getElementById(`depends_val_${fieldName}`);
                if (valInput) valInput.style.display = value ? 'inline-block' : 'none';
            } else if (key === 'depends_value') {
                field.depends_value = value || null;
            }
            updateFieldsInput();
        }
    }

    function addSection() {
        const input = document.getElementById('newSectionName');
        const name = input.value.trim();
        if (!name) return;
        
        const timestamp = new Date().getTime();
        formSections.push({
            id: 'section_' + timestamp,
            name: name,
            order: formSections.length + 1
        });
        
        input.value = '';
        renderSections();
        renderSelectedFields();
    }

    function removeSection(id) {
        if (!confirm('Are you sure you want to remove this section? Fields in it will become unassigned.')) return;
        
        formSections = formSections.filter(s => s.id !== id);
        selectedFields.forEach(f => {
            if (f.section_id === id) f.section_id = null;
        });
        
        renderSections();
        renderSelectedFields();
    }

    function renderSections() {
        const list = document.getElementById('sectionsList');
        if(!list) return;
        list.innerHTML = '';
        
        formSections.forEach((sec, index) => {
            sec.order = index + 1; // Update order just in case
            const div = document.createElement('div');
            div.className = 'section-item';
            div.innerHTML = `
                <div>
                    <span class="order-badge">${index + 1}</span>
                    <strong>${sec.name}</strong>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSection('${sec.id}')">
                    <i class="fe fe-trash"></i>
                </button>
            `;
            list.appendChild(div);
        });
        
        const sectionsInput = document.getElementById('sectionsInput');
        if(sectionsInput) sectionsInput.value = JSON.stringify(formSections);
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
