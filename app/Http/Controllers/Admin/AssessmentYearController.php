<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentYearController extends Controller
{
    /**
     * Display a listing of assessment years.
     */
    public function index(): View
    {
        if (auth()->user()?->can('assessment_years.view')) {
            $this->authorize('assessment_years.view');
        }

        $query = AssessmentYear::query();

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $assessmentYears = $query->orderBy('sort_order')->orderBy('name')->paginate(15);

        return view('admin.assessment_years.index', compact('assessmentYears'));
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        if (auth()->user()?->can('assessment_years.create')) {
            $this->authorize('assessment_years.create');
        }

        return view('admin.assessment_years.create');
    }

    /**
     * Store record.
     */
    public function store(Request $request): RedirectResponse
    {
        if (auth()->user()?->can('assessment_years.create')) {
            $this->authorize('assessment_years.create');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:assessment_years,name'],
            'code' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $assessmentYear = AssessmentYear::create($validated);

        return redirect()->route('admin.assessment-years.index')
            ->with('success', "Assessment Year \"{$assessmentYear->name}\" created successfully.");
    }

    /**
     * Show edit form.
     */
    public function edit(AssessmentYear $assessmentYear): View
    {
        if (auth()->user()?->can('assessment_years.edit')) {
            $this->authorize('assessment_years.edit');
        }

        return view('admin.assessment_years.edit', compact('assessmentYear'));
    }

    /**
     * Update record.
     */
    public function update(Request $request, AssessmentYear $assessmentYear): RedirectResponse
    {
        if (auth()->user()?->can('assessment_years.edit')) {
            $this->authorize('assessment_years.edit');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:assessment_years,name,' . $assessmentYear->id],
            'code' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $assessmentYear->update($validated);

        return redirect()->route('admin.assessment-years.index')
            ->with('success', "Assessment Year \"{$assessmentYear->name}\" updated successfully.");
    }

    /**
     * Toggle status.
     */
    public function toggleStatus(AssessmentYear $assessmentYear): RedirectResponse
    {
        if (auth()->user()?->can('assessment_years.edit')) {
            $this->authorize('assessment_years.edit');
        }

        $newStatus = $assessmentYear->status === 'active' ? 'inactive' : 'active';
        $assessmentYear->update(['status' => $newStatus]);

        return redirect()->back()
            ->with('success', "Assessment Year \"{$assessmentYear->name}\" status changed to {$newStatus}.");
    }

    /**
     * Remove assessment year.
     */
    public function destroy(AssessmentYear $assessmentYear): RedirectResponse
    {
        if (auth()->user()?->can('assessment_years.delete')) {
            $this->authorize('assessment_years.delete');
        }

        $assessmentYear->delete();

        return redirect()->route('admin.assessment-years.index')
            ->with('success', "Assessment Year \"{$assessmentYear->name}\" deleted successfully.");
    }
}
