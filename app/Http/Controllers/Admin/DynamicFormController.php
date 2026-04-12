<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DynamicForm;
use App\Models\Highboard;
use App\Exports\DynamicFormSubmissionExport;
use App\Models\DynamicFormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class DynamicFormController extends Controller
{
    use \App\Traits\ImageUploadTrait;
    /**
     * Display a listing of dynamic forms.
     */
    public function index()
    {
        $forms = DynamicForm::withCount('submissions')->latest()->paginate(15);
        return view('admin.dynamic-forms.index', compact('forms'));
    }

    /**
     * Show the form for creating a new dynamic form.
     */
    public function create()
    {
        $availableFields = DynamicForm::AVAILABLE_FIELDS;
        $highboards = Highboard::active()->get();
        return view('admin.dynamic-forms.create', compact('availableFields', 'highboards'));
    }

    /**
     * Store a newly created dynamic form.
     */
    public function store(Request $request)
    {
        // Parse JSON fields from hidden input
        $fields = json_decode($request->input('fields'), true) ?? [];
        $sections = json_decode($request->input('sections'), true) ?? [];
        $request->merge(['fields' => $fields, 'sections' => $sections]);

        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'subdomain' => 'required|string|max:100|unique:dynamic_forms,subdomain|regex:/^[a-z0-9-]+$/',
            'form_image' => 'nullable|image|max:2048',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string',
            'fields.*.order' => 'required|integer',
            'fields.*.section_id' => 'nullable|string',
            // Allow label and other attributes for custom fields
            'fields.*.label' => 'nullable|string',
            'fields.*.type' => 'nullable|string',
            'fields.*.options' => 'nullable|array',
            'fields.*.options.*' => 'string',
            'fields.*.placeholder' => 'nullable|string',
            'fields.*.required' => 'boolean',
            'fields.*.depends_on' => 'nullable|string',
            'fields.*.depends_value' => 'nullable|string',
            'sections' => 'nullable|array',
            'sections.*.id' => 'required|string',
            'sections.*.name' => 'required|string|max:255',
            'sections.*.order' => 'required|integer',
            'is_active' => 'boolean',
            'highboards' => 'nullable|array',
            'highboards.*' => 'exists:highboards,id',
        ], [
            'subdomain.regex' => 'Subdomain can only contain lowercase letters, numbers, and hyphens.',
        ]);

        $data = [
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'subdomain' => $request->subdomain,
            'fields' => $request->fields,
            'sections' => $request->sections,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('form_image')) {
            $data['form_image'] = $this->uploadImage($request->file('form_image'), 'dynamic-forms');
        }

        $form = DynamicForm::create($data);

        $form->highboards()->sync($request->input('highboards', []));

        return redirect()->route('admin.dynamic-forms.index')
            ->with('success', 'Dynamic form created successfully.');
    }

    /**
     * Display the specified dynamic form with submissions.
     */
    public function show(Request $request, DynamicForm $dynamicForm)
    {
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
        return view('admin.dynamic-forms.show', compact('dynamicForm', 'submissions'));
    }

    /**
     * Show the form for editing the specified dynamic form.
     */
    public function edit(DynamicForm $dynamicForm)
    {
        $availableFields = DynamicForm::AVAILABLE_FIELDS;
        $highboards = Highboard::active()->get();
        return view('admin.dynamic-forms.edit', compact('dynamicForm', 'availableFields', 'highboards'));
    }

    /**
     * Update the specified dynamic form.
     */
    public function update(Request $request, DynamicForm $dynamicForm)
    {
        // Parse JSON fields from hidden input
        $fields = json_decode($request->input('fields'), true) ?? [];
        $sections = json_decode($request->input('sections'), true) ?? [];
        $request->merge(['fields' => $fields, 'sections' => $sections]);

        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'subdomain' => 'required|string|max:100|unique:dynamic_forms,subdomain,' . $dynamicForm->id . '|regex:/^[a-z0-9-]+$/',
            'form_image' => 'nullable|image|max:2048',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string',
            'fields.*.order' => 'required|integer',
            'fields.*.section_id' => 'nullable|string',
            // Allow label and other attributes for custom fields
            'fields.*.label' => 'nullable|string',
            'fields.*.type' => 'nullable|string',
            'fields.*.options' => 'nullable|array',
            'fields.*.options.*' => 'string',
            'fields.*.placeholder' => 'nullable|string',
            'fields.*.required' => 'boolean',
            'fields.*.depends_on' => 'nullable|string',
            'fields.*.depends_value' => 'nullable|string',
            'sections' => 'nullable|array',
            'sections.*.id' => 'required|string',
            'sections.*.name' => 'required|string|max:255',
            'sections.*.order' => 'required|integer',
            'is_active' => 'boolean',
            'highboards' => 'nullable|array',
            'highboards.*' => 'exists:highboards,id',
        ], [
            'subdomain.regex' => 'Subdomain can only contain lowercase letters, numbers, and hyphens.',
        ]);

        $data = [
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'subdomain' => $request->subdomain,
            'fields' => $request->fields,
            'sections' => $request->sections,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('form_image')) {
            $data['form_image'] = $this->uploadImage($request->file('form_image'), 'dynamic-forms', $dynamicForm->form_image);
        }

        $dynamicForm->update($data);

        $dynamicForm->highboards()->sync($request->input('highboards', []));

        return redirect()->route('admin.dynamic-forms.index')
            ->with('success', 'Dynamic form updated successfully.');
    }

    /**
     * Remove the specified dynamic form.
     */
    public function destroy(DynamicForm $dynamicForm)
    {
        $dynamicForm->delete();
        return redirect()->route('admin.dynamic-forms.index')
            ->with('success', 'Dynamic form deleted successfully.');
    }

    /**
     * Toggle the active status of a dynamic form.
     */
    public function toggleActive(DynamicForm $dynamicForm)
    {
        $dynamicForm->is_active = !$dynamicForm->is_active;
        $dynamicForm->save();

        return back()->with('success', 'Form status updated successfully.');
    }

    /**
     * Toggle payment status of a submission.
     */
    public function togglePayment(DynamicFormSubmission $submission)
    {
        $newState = !$submission->is_payed;
        $submission->update([
            'is_payed' => $newState,
            'accepted_by' => $newState ? auth()->guard('admin')->user()->name : null
        ]);

        return back()->with('success', 'Payment status updated successfully.');
    }

    /**
     * Export form submissions to Excel.
     */
    public function exportSubmissions(DynamicForm $dynamicForm)
    {
        $filename = 'form_submissions_' . str_replace(' ', '_', $dynamicForm->title) . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new DynamicFormSubmissionExport($dynamicForm), $filename);
    }

    /**
     * Delete a submission.
     */
    public function destroySubmission(DynamicFormSubmission $submission)
    {
        $submission->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Submission deleted successfully.']);
        }

        return back()->with('success', 'Submission deleted successfully.');
    }
}
