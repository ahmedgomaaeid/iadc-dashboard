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

<form action="{{ $formAction }}" method="POST" id="dynamicFormBuilder">
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
                        <label for="subdomain" class="form-label">Subdomain <span class="text-danger">*</span></label>
                        @php
                            $domain = str_replace(['http://', 'https://'], '', env('APP_URL'));
                            $domain = str_replace('www.', '', $domain);
                        @endphp
                        <div class="input-group">
                            <input type="text" class="form-control" id="subdomain" name="subdomain" 
                                value="{{ old('subdomain', $dynamicForm->subdomain ?? '') }}" required
                                placeholder="e.g., registration-2025" pattern="[a-z0-9-]+"
                                oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '')">
                            <span class="input-group-text">.form.{{ $domain }}</span>
                        </div>
                        <small class="text-muted">Only lowercase letters, numbers, and hyphens allowed. URL will be: <strong id="urlPreview">https://{subdomain}.form.{{ $domain }}</strong></small>
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
                const config = availableFields[field.name];
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

    function removeField(fieldName) {
        const index = selectedFields.findIndex(f => f.name === fieldName);
        if (index > -1) {
            selectedFields.splice(index, 1);
            
            const fieldItem = document.querySelector(`.card-body > .field-item[data-field="${fieldName}"]`);
            if (fieldItem) {
                fieldItem.classList.remove('selected');
                const checkbox = document.getElementById(`check_${fieldName}`);
                if (checkbox) checkbox.checked = false;
            }
            
            renderSelectedFields();
        }
    }

    function updateFieldsOrder() {
        const items = container.querySelectorAll('.field-item[data-field]');
        selectedFields = [];
        items.forEach((item, index) => {
            selectedFields.push({
                name: item.dataset.field,
                order: index + 1
            });
        });
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
