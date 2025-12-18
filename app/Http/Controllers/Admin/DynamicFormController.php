<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DynamicForm;
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
        $forms = DynamicForm::withCount('submissions')->latest()->paginate(15);
        return view('admin.dynamic-forms.index', compact('forms'));
    }

    /**
     * Show the form for creating a new dynamic form.
     */
    public function create()
    {
        $availableFields = DynamicForm::AVAILABLE_FIELDS;
        return view('admin.dynamic-forms.create', compact('availableFields'));
    }

    /**
     * Store a newly created dynamic form.
     */
    public function store(Request $request)
    {
        // Parse JSON fields from hidden input
        $fields = json_decode($request->input('fields'), true) ?? [];
        $request->merge(['fields' => $fields]);

        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'subdomain' => 'required|string|max:100|unique:dynamic_forms,subdomain|regex:/^[a-z0-9-]+$/',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string',
            'fields.*.order' => 'required|integer',
            'is_active' => 'boolean',
        ], [
            'subdomain.regex' => 'Subdomain can only contain lowercase letters, numbers, and hyphens.',
        ]);

        DynamicForm::create([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'subdomain' => $request->subdomain,
            'fields' => $request->fields,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.dynamic-forms.index')
            ->with('success', 'Dynamic form created successfully.');
    }

    /**
     * Display the specified dynamic form with submissions.
     */
    public function show(DynamicForm $dynamicForm)
    {
        $submissions = $dynamicForm->submissions()->latest()->paginate(20);
        return view('admin.dynamic-forms.show', compact('dynamicForm', 'submissions'));
    }

    /**
     * Show the form for editing the specified dynamic form.
     */
    public function edit(DynamicForm $dynamicForm)
    {
        $availableFields = DynamicForm::AVAILABLE_FIELDS;
        return view('admin.dynamic-forms.edit', compact('dynamicForm', 'availableFields'));
    }

    /**
     * Update the specified dynamic form.
     */
    public function update(Request $request, DynamicForm $dynamicForm)
    {
        // Parse JSON fields from hidden input
        $fields = json_decode($request->input('fields'), true) ?? [];
        $request->merge(['fields' => $fields]);

        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'subdomain' => 'required|string|max:100|unique:dynamic_forms,subdomain,' . $dynamicForm->id . '|regex:/^[a-z0-9-]+$/',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string',
            'fields.*.order' => 'required|integer',
            'is_active' => 'boolean',
        ], [
            'subdomain.regex' => 'Subdomain can only contain lowercase letters, numbers, and hyphens.',
        ]);

        $dynamicForm->update([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'subdomain' => $request->subdomain,
            'fields' => $request->fields,
            'is_active' => $request->boolean('is_active', true),
        ]);

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
     * Export form submissions to Excel.
     */
    public function exportSubmissions(DynamicForm $dynamicForm)
    {
        $filename = 'form_submissions_' . str_replace(' ', '_', $dynamicForm->title) . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new DynamicFormSubmissionExport($dynamicForm), $filename);
    }
}
