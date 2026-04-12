<?php

namespace App\Http\Controllers\Highboard;

use App\Http\Controllers\Controller;
use App\Models\DynamicForm;
use App\Models\DynamicFormSubmission;
use App\Exports\DynamicFormSubmissionExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DynamicFormController extends Controller
{
    /**
     * Display a listing of dynamic forms.
     */
    public function index()
    {
        $forms = auth()->guard('highboard')->user()->dynamicForms()->where('is_active', true)->withCount('submissions')->latest()->paginate(15);
        return view('highboard.dynamic-forms.index', compact('forms'));
    }

    /**
     * Display the specified dynamic form with submissions.
     */
    public function show(Request $request, $id)
    {
        $dynamicForm = auth()->guard('highboard')->user()->dynamicForms()->where('is_active', true)->findOrFail($id);

        $query = $dynamicForm->submissions()->latest();

        if ($request->filled('search')) {
            $query->where('data', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'paid') {
                $query->where('is_payed', true);
            } elseif ($request->payment_status === 'unpaid') {
                $query->where('is_payed', false);
            }
        }

        $submissions = $query->paginate(20)->withQueryString();
        return view('highboard.dynamic-forms.show', compact('dynamicForm', 'submissions'));
    }

    /**
     * Toggle payment status of a submission.
     */
    public function togglePayment($submissionId)
    {
        $submission = DynamicFormSubmission::findOrFail($submissionId);
        // Ensure the highboard has access to the form that owns this submission
        auth()->guard('highboard')->user()->dynamicForms()->where('is_active', true)->findOrFail($submission->dynamic_form_id);

        $newState = !$submission->is_payed;
        $submission->update([
            'is_payed' => $newState,
            'accepted_by' => $newState ? auth()->guard('highboard')->user()->name : null
        ]);

        return back()->with('success', 'Payment status updated successfully.');
    }

    /**
     * Export form submissions to Excel.
     */
    public function exportSubmissions($id)
    {
        $dynamicForm = auth()->guard('highboard')->user()->dynamicForms()->where('is_active', true)->findOrFail($id);

        $filename = 'form_submissions_' . str_replace(' ', '_', $dynamicForm->title) . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new DynamicFormSubmissionExport($dynamicForm), $filename);
    }

    /**
     * Delete a submission.
     */
    public function destroySubmission($submissionId)
    {
        $submission = DynamicFormSubmission::findOrFail($submissionId);
        // Ensure the highboard has access to the form that owns this submission
        auth()->guard('highboard')->user()->dynamicForms()->where('is_active', true)->findOrFail($submission->dynamic_form_id);

        $submission->delete();

        return back()->with('success', 'Submission deleted successfully.');
    }
}
