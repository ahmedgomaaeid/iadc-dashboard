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
            
            if ($fieldConfig['required']) {
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
            }

            $rules[$fieldName] = implode('|', $fieldRules);
        }

        $validated = $request->validate($rules);

        // Store submission
        DynamicFormSubmission::create([
            'dynamic_form_id' => $form->id,
            'data' => $validated,
        ]);

        return back()->with('registration_success', 'Thank you! Your submission has been received.');
    }
}
