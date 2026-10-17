<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactEnquiryController extends Controller
{
    /**
     * Display a listing of contact enquiries.
     */
    public function index(Request $request): View
    {
        $this->authorize('contact_enquiry.view');

        $query = ContactEnquiry::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('loan_requirement', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $enquiries = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total' => ContactEnquiry::count(),
            'new' => ContactEnquiry::where('status', ContactEnquiry::STATUS_NEW)->count(),
            'contacted' => ContactEnquiry::where('status', ContactEnquiry::STATUS_CONTACTED)->count(),
            'follow_up' => ContactEnquiry::where('status', ContactEnquiry::STATUS_FOLLOW_UP)->count(),
            'converted' => ContactEnquiry::where('status', ContactEnquiry::STATUS_CONVERTED)->count(),
            'closed' => ContactEnquiry::where('status', ContactEnquiry::STATUS_CLOSED)->count(),
        ];

        return view('admin.contact_enquiries.index', compact('enquiries', 'stats'));
    }

    /**
     * Display the specified contact enquiry.
     */
    public function show(ContactEnquiry $contactEnquiry): View
    {
        $this->authorize('contact_enquiry.view');

        return view('admin.contact_enquiries.show', compact('contactEnquiry'));
    }

    /**
     * Update the status of the contact enquiry.
     */
    public function updateStatus(Request $request, ContactEnquiry $contactEnquiry): RedirectResponse
    {
        $this->authorize('contact_enquiry.update');

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(array_keys(ContactEnquiry::STATUSES))],
        ]);

        $contactEnquiry->update([
            'status' => $validated['status'],
        ]);

        return redirect()->back()
            ->with('success', "Enquiry status updated to \"{$contactEnquiry->status}\".");
    }

    /**
     * Update the contact enquiry details or notes.
     */
    public function update(Request $request, ContactEnquiry $contactEnquiry): RedirectResponse
    {
        $this->authorize('contact_enquiry.update');

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(array_keys(ContactEnquiry::STATUSES))],
            'full_name' => ['sometimes', 'required', 'string', 'max:150'],
            'mobile' => ['sometimes', 'required', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'loan_requirement' => ['nullable', 'string', 'max:150'],
            'message' => ['nullable', 'string'],
        ]);

        $contactEnquiry->update($validated);

        return redirect()->route('admin.contact-enquiries.show', $contactEnquiry)
            ->with('success', 'Contact enquiry updated successfully.');
    }

    /**
     * Remove the specified contact enquiry.
     */
    public function destroy(ContactEnquiry $contactEnquiry): RedirectResponse
    {
        $this->authorize('contact_enquiry.delete');

        $name = $contactEnquiry->full_name;
        $contactEnquiry->delete();

        return redirect()->route('admin.contact-enquiries.index')
            ->with('success', "Enquiry from \"{$name}\" was successfully deleted.");
    }
}
