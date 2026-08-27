<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Visit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    /**
     * Store a newly created visit.
     */
    public function store(Request $request, Lead $lead): RedirectResponse
    {
        $this->authorize('visits.create');

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:users,id'],
            'visit_date' => ['required', 'date'],
            'purpose' => ['required', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:scheduled,completed,cancelled,rescheduled'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'outcome' => ['nullable', 'string', 'max:1000'],
            'next_action' => ['nullable', 'string', 'max:1000'],
            'next_follow_up_date' => ['nullable', 'date'],
        ]);

        $validated['lead_id'] = $lead->id;
        $validated['created_by'] = auth()->id() ?? User::first()?->id ?? 1;

        $visit = Visit::create($validated);

        // Timeline logging
        $lead->logActivity(
            auth()->id() ?? User::first()?->id ?? 1,
            'visit_created',
            "Visit scheduled with employee: " . $visit->employee->name . " on " . $visit->visit_date->format('Y-m-d H:i')
        );

        // Auto-status flow
        if ($visit->status === 'completed') {
            $lead->update(['status' => 'visit_completed']);
            $lead->logActivity(auth()->id() ?? User::first()?->id ?? 1, 'visit_completed', "Visit completed");
        } else if (in_array($visit->status, ['scheduled', 'rescheduled'])) {
            $lead->update(['status' => 'visit_pending']);
        }

        return redirect()->route('admin.leads.show', $lead)
            ->with('success', 'Visit scheduled successfully.');
    }

    /**
     * Update the specified visit.
     */
    public function update(Request $request, Lead $lead, Visit $visit): RedirectResponse
    {
        $this->authorize('visits.edit');

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:users,id'],
            'visit_date' => ['required', 'date'],
            'purpose' => ['required', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:scheduled,completed,cancelled,rescheduled'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'outcome' => ['nullable', 'string', 'max:1000'],
            'next_action' => ['nullable', 'string', 'max:1000'],
            'next_follow_up_date' => ['nullable', 'date'],
        ]);

        $oldStatus = $visit->status;
        $visit->update($validated);

        if ($oldStatus !== $visit->status) {
            $lead->logActivity(
                auth()->id() ?? User::first()?->id ?? 1,
                'visit_updated',
                "Visit status updated from '" . ucfirst($oldStatus) . "' to '" . ucfirst($visit->status) . "'"
            );

            // Auto-status flow
            if ($visit->status === 'completed') {
                $lead->update(['status' => 'visit_completed']);
                $lead->logActivity(auth()->id() ?? User::first()?->id ?? 1, 'visit_completed', "Visit completed");
            } else if ($visit->status === 'cancelled') {
                $lead->logActivity(auth()->id() ?? User::first()?->id ?? 1, 'visit_cancelled', "Visit cancelled");
                
                // If no other scheduled/rescheduled/completed visits exist, set status back to new
                $hasActiveVisits = $lead->visits()->whereIn('status', ['scheduled', 'rescheduled', 'completed'])->exists();
                if (!$hasActiveVisits) {
                    $lead->update(['status' => 'new']);
                }
            } else if (in_array($visit->status, ['scheduled', 'rescheduled'])) {
                $lead->update(['status' => 'visit_pending']);
            }
        }

        return redirect()->route('admin.leads.show', $lead)
            ->with('success', 'Visit updated successfully.');
    }
}
