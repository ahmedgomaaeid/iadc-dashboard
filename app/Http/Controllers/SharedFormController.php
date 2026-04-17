<?php

namespace App\Http\Controllers;

use App\Models\DynamicForm;
use App\Exports\DynamicFormSubmissionExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Maatwebsite\Excel\Facades\Excel;

class SharedFormController extends Controller
{
    /**
     * Display the submissions for a shared dynamic form.
     */
    public function showSubmissions(Request $request, $encryptedId)
    {
        try {
            $id = Crypt::decryptString($encryptedId);
        } catch (DecryptException $e) {
            abort(404, 'Invalid or expired link.');
        }

        $dynamicForm = DynamicForm::findOrFail($id);
        
        // Ensure form is active
        if (!$dynamicForm->is_active) {
            abort(404, 'This form is currently inactive.');
        }

        $submissions = $dynamicForm->submissions()->latest()->paginate(20);

        return view('shared-forms.submissions', compact('dynamicForm', 'submissions', 'encryptedId'));
    }

    /**
     * Export form submissions to Excel for a shared link.
     */
    public function exportSubmissions(Request $request, $encryptedId)
    {
        try {
            $id = Crypt::decryptString($encryptedId);
        } catch (DecryptException $e) {
            abort(404, 'Invalid or expired link.');
        }

        $dynamicForm = DynamicForm::findOrFail($id);

        if (!$dynamicForm->is_active) {
            abort(404, 'This form is currently inactive.');
        }

        $filename = 'form_submissions_' . str_replace(' ', '_', $dynamicForm->title) . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new DynamicFormSubmissionExport($dynamicForm, $request->all()), $filename);
    }
}
