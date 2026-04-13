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

        if (in_array(strtolower($form->subdomain), ['pulse', 'peaks'])) {
            $redirect->with('is_pulse', true);
            $redirect->with('pulse_submission_id', $submission->id);
            $redirect->with('pulse_subdomain', strtolower($form->subdomain));
            
            // Generate the finalized image with user photo and name
            $userPhotoPath = null;
            $userName = '';
            
            foreach ($orderedFields as $fieldName => $fieldConfig) {
                if ($fieldConfig['type'] === 'file' && isset($validated[$fieldName])) {
                    $userPhotoPath = $validated[$fieldName];
                }
                if (($fieldConfig['type'] === 'text' || $fieldConfig['type'] === 'string') 
                    && str_contains(strtolower($fieldName), 'name') 
                    && isset($validated[$fieldName])) {
                    if (empty($userName)) {
                        $userName = $validated[$fieldName];
                    }
                }
            }

            try {
                $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                $isPeaks = strtolower($form->subdomain) === 'peaks';
                
                if ($isPeaks) {
                    $baseCard = $manager->read(public_path('images/peakscard.jpeg'));
                } else {
                    $baseCard = $manager->read(public_path('images/LinkedIn.jpg.jpeg'));
                }
                
                // Resize and place user photo or default
                $photoPath = $userPhotoPath ? storage_path('app/public/' . $userPhotoPath) : public_path('images/default linked_in.png');
                if (file_exists($photoPath)) {
                        $photo = $manager->read($photoPath);
                        
                        if ($isPeaks) {
                            // Peaks photo: 511x511 center crop
                            $photo->cover(511, 511, 'center');
                            $radius = 255; // Circular
                            $xOffset = 1277;
                            $yOffset = 248;
                        } else {
                            // Pulse photo: height 404, max width 454
                            $photo->scale(height: 404);
                            if ($photo->width() > 454) {
                                $photo->crop(454, 404, position: 'center');
                            }
                            $radius = 40;
                            $xOffset = 1853 - $photo->width();
                            $yOffset = 301;
                        }
                        
                        // Apply border radius
                        $gdImage = $photo->core()->native();
                        $width = imagesx($gdImage);
                        $height = imagesy($gdImage);
                        imagealphablending($gdImage, false);
                        imagesavealpha($gdImage, true);
                        $transparent = imagecolorallocatealpha($gdImage, 255, 255, 255, 127);
                        for ($x = 0; $x < $width; $x++) {
                            for ($y = 0; $y < $height; $y++) {
                                // Full circle distance check for Peaks, normal rounded rect for Pulse
                                if ($isPeaks) {
                                    $cx = $width / 2;
                                    $cy = $height / 2;
                                    if (pow($x - $cx, 2) + pow($y - $cy, 2) > pow($radius, 2)) {
                                        imagesetpixel($gdImage, $x, $y, $transparent);
                                    }
                                } else {
                                    if ($x < $radius && $y < $radius) {
                                        if (pow($x - $radius + 1, 2) + pow($y - $radius + 1, 2) > pow($radius, 2)) imagesetpixel($gdImage, $x, $y, $transparent);
                                    } elseif ($x >= $width - $radius && $y < $radius) {
                                        if (pow($x - ($width - $radius), 2) + pow($y - $radius + 1, 2) > pow($radius, 2)) imagesetpixel($gdImage, $x, $y, $transparent);
                                    } elseif ($x < $radius && $y >= $height - $radius) {
                                        if (pow($x - $radius + 1, 2) + pow($y - ($height - $radius), 2) > pow($radius, 2)) imagesetpixel($gdImage, $x, $y, $transparent);
                                    } elseif ($x >= $width - $radius && $y >= $height - $radius) {
                                        if (pow($x - ($width - $radius), 2) + pow($y - ($height - $radius), 2) > pow($radius, 2)) imagesetpixel($gdImage, $x, $y, $transparent);
                                    }
                                }
                            }
                        }
                        imagealphablending($gdImage, true);

                        $baseCard->place($photo, 'top-left', $xOffset, $yOffset);
                    }

                    // Write user name
                    if (!empty($userName)) {
                        $nameParts = explode(' ', trim($userName));
                        $twoNames = implode(' ', array_slice($nameParts, 0, 2));
                        
                        $nameX = $isPeaks ? 699 : 895;
                        $nameY = $isPeaks ? 380 : 383;
                        $fontSize = $isPeaks ? 67.5 : 60;
                        
                        $baseCard->text($twoNames, $nameX, $nameY, function($font) use ($fontSize) {
                            $font->file(public_path('fonts/MyriadArabic-Bold.otf'));
                            $font->size($fontSize);
                            $font->color('#ffffff');
                            $font->align('left');
                            $font->valign('top');
                        });
                    }

                    // Save the final card
                    $filename = strtolower($subdomain) . '_' . uniqid() . '.jpg';
                    $generatedPath = 'dynamic_form_uploads/' . $filename;
                    $baseCard->save(storage_path('app/public/' . $generatedPath), quality: 90);
                    
                    // Update submission to use this new card instead of original upload
                    $newData = $submission->data;
                    foreach ($orderedFields as $fieldName => $fieldConfig) {
                        if ($fieldConfig['type'] === 'file') {
                            $newData[$fieldName] = $generatedPath;
                            break;
                        }
                    }
                    $submission->update(['data' => $newData]);

                    $redirect->with('pulse_image', $generatedPath);

                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Pulse image generation failed', ['error' => $e->getMessage()]);
                    if ($userPhotoPath) {
                        $redirect->with('pulse_image', $userPhotoPath);
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
            'imagePath' => $imagePath,
            'subdomain' => strtolower($form->subdomain)
        ]);
    }

    /**
     * Save the selected package for a peaks submission.
     */
    public function savePackage(Request $request, $subdomain)
    {
        $request->validate([
            'submission_id' => 'required|exists:dynamic_form_submissions,id',
            'package' => 'required|in:1,2,3',
            'payment_method' => 'required|in:vodafone,instapay,cash',
        ]);

        $submission = DynamicFormSubmission::findOrFail($request->submission_id);

        $packageNames = [
            '1' => 'Package 1 - 180 L.E',
            '2' => 'Package 2 - 100 L.E',
            '3' => 'Package 3 - 60 L.E',
        ];
        
        $paymentNames = [
            'vodafone' => 'Vodafone Cash',
            'instapay' => 'Instapay',
            'cash' => 'Cash',
        ];

        $data = $submission->data;
        $data['_selected_package'] = $request->package;
        $data['_selected_package_name'] = $packageNames[$request->package] ?? '';
        $data['_selected_payment'] = $request->payment_method;
        $data['_selected_payment_name'] = $paymentNames[$request->payment_method] ?? '';
        
        $submission->update(['data' => $data]);

        return response()->json(['success' => true]);
    }
}
