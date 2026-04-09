<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;
class DynamicForm extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'subdomain',
        'form_image',
        'fields',
        'sections',
        'is_active',
    ];

    protected $casts = [
        'fields' => 'array',
        'sections' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Available form field definitions
     */
    public const AVAILABLE_FIELDS = [
        'full_name' => [
            'label' => 'Full Name',
            'type' => 'text',
            'required' => true,
            'icon' => 'fa-user',
            'placeholder' => 'Enter your full name',
        ],
        'email' => [
            'label' => 'Email Address',
            'type' => 'email',
            'required' => true,
            'icon' => 'fa-envelope',
            'placeholder' => 'Enter your email address',
        ],
        'phone' => [
            'label' => 'Phone Number',
            'type' => 'tel',
            'required' => true,
            'icon' => 'fa-phone',
            'placeholder' => 'Enter your phone number',
        ],
        'university' => [
            'label' => 'University',
            'type' => 'text',
            'required' => true,
            'icon' => 'fa-university',
            'placeholder' => 'Enter your university',
        ],
        'faculty' => [
            'label' => 'Faculty',
            'type' => 'text',
            'required' => true,
            'icon' => 'fa-graduation-cap',
            'placeholder' => 'Enter your faculty',
        ],
        'academic_year' => [
            'label' => 'Academic Year',
            'type' => 'select',
            'required' => true,
            'icon' => 'fa-calendar',
            'options' => ['Preparation', 'First Year', 'Second Year', 'Third Year', 'Fourth Year', 'Graduation'],
        ],
        'message' => [
            'label' => 'Message',
            'type' => 'textarea',
            'required' => false,
            'icon' => 'fa-comment',
            'placeholder' => 'Enter your message',
        ],
        'national_id' => [
            'label' => 'National ID',
            'type' => 'text',
            'required' => true,
            'icon' => 'fa-id-card',
            'placeholder' => 'Enter your national ID',
        ],
    ];

    /**
     * Get the submissions for this form.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(DynamicFormSubmission::class);
    }

    /**
     * Scope for active forms
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the ordered field definitions for this form
     */
    public function getOrderedFields(): array
    {
        $orderedFields = [];
        $formFields = $this->fields ?? [];

        // Sort by order
        usort($formFields, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

        foreach ($formFields as $field) {
            $fieldName = $field['name'];
            if (isset(self::AVAILABLE_FIELDS[$fieldName])) {
                $orderedFields[$fieldName] = self::AVAILABLE_FIELDS[$fieldName];
            } else {
                // Handle custom fields
                $orderedFields[$fieldName] = [
                    'label' => $field['label'] ?? ucfirst(str_replace('_', ' ', $fieldName)),
                    'type' => $field['type'] ?? 'text', // Default to text for custom fields
                    'required' => filter_var($field['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'icon' => $field['icon'] ?? 'fa-pen', // Default icon
                    'placeholder' => $field['placeholder'] ?? '',
                ];
                
                // Allow options for select fields if we add that support later or via JSON editor manually
                if (isset($field['options'])) {
                    $orderedFields[$fieldName]['options'] = $field['options'];
                }
            }
        }

        return $orderedFields;
    }

    /**
     * Get sections with their fields grouped and ordered.
     * Returns an array of sections, each containing 'id', 'name', 'order', and 'fields'.
     * If no sections exist, returns a single default section with all fields.
     */
    public function getOrderedSections(): array
    {
        $sections = $this->sections ?? [];
        $formFields = $this->fields ?? [];
        $orderedFields = $this->getOrderedFields();

        // If no sections defined, return all fields in one default section
        if (empty($sections)) {
            return [
                [
                    'id' => 'default',
                    'name' => 'Form',
                    'order' => 1,
                    'fields' => $orderedFields,
                ]
            ];
        }

        // Sort sections by order
        usort($sections, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

        // Build a map: field_name => section_id from raw fields data
        $fieldSectionMap = [];
        foreach ($formFields as $field) {
            $fieldSectionMap[$field['name']] = $field['section_id'] ?? null;
        }

        // Group fields into sections
        $result = [];
        foreach ($sections as $section) {
            $sectionFields = [];
            foreach ($orderedFields as $fieldName => $fieldConfig) {
                if (($fieldSectionMap[$fieldName] ?? null) === $section['id']) {
                    $sectionFields[$fieldName] = $fieldConfig;
                }
            }
            $result[] = [
                'id' => $section['id'],
                 'name' => $section['name'],
                'order' => $section['order'],
                'fields' => $sectionFields,
            ];
        }

        // Collect unassigned fields into a default section
        $assignedFieldNames = [];
        foreach ($result as $sec) {
            $assignedFieldNames = array_merge($assignedFieldNames, array_keys($sec['fields']));
        }
        $unassigned = array_diff_key($orderedFields, array_flip($assignedFieldNames));
        if (!empty($unassigned)) {
            array_unshift($result, [
                'id' => 'default',
                'name' => 'General',
                'order' => 0,
                'fields' => $unassigned,
            ]);
        }

        return $result;
    }

    /**
     * Get the full URL for this form using subdomain
     * Format: https://{subdomain}.form.iadcsuez.org
     */
    public function getFormUrl(): string
    {
        return 'https://' . $this->subdomain . '.form.iadcsuez.org';
    }

    /**
     * Get the shareable URL for submissions using an encrypted ID
     */
    public function getSharedSubmissionsUrl(): string
    {
        $encryptedId = Crypt::encryptString((string)$this->id);
        return route('shared-forms.submissions.show', ['encryptedId' => $encryptedId]);
    }
}
