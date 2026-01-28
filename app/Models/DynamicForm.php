<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DynamicForm extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'subdomain',
        'fields',
        'is_active',
    ];

    protected $casts = [
        'fields' => 'array',
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
     * Get the full URL for this form using subdomain
     * Format: https://{subdomain}.form.iadcsuez.org
     */
    public function getFormUrl(): string
    {
        return 'https://' . $this->subdomain . '.form.iadcsuez.org';
    }
}
