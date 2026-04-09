<?php

namespace App\Http\Controllers;

use App\Models\DynamicForm;
use App\Models\DynamicFormSubmission;
use Illuminate\Http\Request;

class GuestFormController extends Controller
{
    /**
     * Display the form to guests.
     */
    public function show($subdomain)
    {
        $form = DynamicForm::active()->where('subdomain', $subdomain)->firstOrFail();
        $orderedSections = $form->getOrderedSections();

        return view('forms.guest-form', compact('form', 'orderedSections'));
    }

    /**
     * Store the guest's form submission.
     */
    public function submit(Request $request, $subdomain)
    {
        $form = DynamicForm::active()->where('subdomain', $subdomain)->firstOrFail();
        $orderedFields = $form->getOrderedFields();

        // Build validation rules based on form fields
        $rules = [];
        foreach ($orderedFields as $fieldName => $fieldConfig) {
            $fieldRules = [];
            
            $isRequired = $fieldConfig['required'];

            // Evaluate conditional dependency
            if (isset($fieldConfig['depends_on']) && $fieldConfig['depends_on']) {
                $dependsOnField = $fieldConfig['depends_on'];
                $rawDependsValue = $fieldConfig['depends_value'] ?? '';
                $dependsValue = strtolower(trim($rawDependsValue));
                
                $inputValue = strtolower(trim($request->input($dependsOnField, '')));
                
                // If the submitted parent field doesn't match the condition, it means the field was hidden
                // and should not be required.
                if ($inputValue !== $dependsValue || $dependsValue === '') {
                    $isRequired = false;
                }
            }

            if ($isRequired) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($fieldConfig['type']) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'tel':
                    $fieldRules[] = 'string';
                    break;
                case 'textarea':
                case 'text':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:1000';
                    break;
                case 'select':
                    if (isset($fieldConfig['options'])) {
                        $fieldRules[] = 'in:' . implode(',', $fieldConfig['options']);
                    }
                    break;
                case 'file':
                    $fieldRules[] = 'image';
                    $fieldRules[] = 'mimes:jpeg,png,jpg,gif,webp';
                    $fieldRules[] = 'max:5120'; // 5MB
                    break;
            }

            $rules[$fieldName] = implode('|', $fieldRules);
        }

        $validated = $request->validate($rules);

        // Process File Uploads safely
        foreach ($orderedFields as $fieldName => $fieldConfig) {
            if ($fieldConfig['type'] === 'file' && $request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                if ($file->isValid()) {
                    $path = $file->store('dynamic_form_uploads', 'public');
                    $validated[$fieldName] = $path;
                }
            }
        }

        // Store submission
        $submission = DynamicFormSubmission::create([
            'dynamic_form_id' => $form->id,
            'data' => $validated,
        ]);

        $redirect = back()->with('registration_success', 'Thank you! Your submission has been received.');

        if (strtolower($form->subdomain) === 'pulse') {
            $redirect->with('is_pulse', true);
            $redirect->with('pulse_submission_id', $submission->id);
            
            // Find the image uploaded for pulse to display back
            foreach ($orderedFields as $fieldName => $fieldConfig) {
                if ($fieldConfig['type'] === 'file' && isset($validated[$fieldName])) {
                    $redirect->with('pulse_image', $validated[$fieldName]);
                    break;
                }
            }
        }

        return $redirect;
    }

    /**
     * Render an OpenGraph metadata page for LinkedIn link previews.
     */
    public function sharePage($id)
    {
        $submission = DynamicFormSubmission::findOrFail($id);
        
        // Find the image file path from the submission data
        $form = $submission->dynamicForm;
        $orderedFields = $form->getOrderedFields();
        $imagePath = null;
        
        foreach ($orderedFields as $fieldName => $fieldConfig) {
            if ($fieldConfig['type'] === 'file' && isset($submission->data[$fieldName])) {
                $imagePath = $submission->data[$fieldName];
                break;
            }
        }
        
        return view('forms.pulse-share', [
            'submission' => $submission,
            'imagePath' => $imagePath
        ]);
    }
}
