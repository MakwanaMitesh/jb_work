<?php

namespace App\Http\Controllers;

use App\Models\ContactEnquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PublicContactController extends Controller
{
    /**
     * Handle public contact enquiry submission from the landing page.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'mobile' => ['required', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'loan_requirement' => ['nullable', 'string', 'max:150'],
            'message' => ['nullable', 'string', 'max:3000'],
        ]);

        $enquiry = ContactEnquiry::create([
            'full_name' => trim(strip_tags($validated['full_name'])),
            'mobile' => trim(strip_tags($validated['mobile'])),
            'email' => isset($validated['email']) ? trim($validated['email']) : null,
            'city' => isset($validated['city']) ? trim(strip_tags($validated['city'])) : null,
            'loan_requirement' => isset($validated['loan_requirement']) ? trim(strip_tags($validated['loan_requirement'])) : null,
            'message' => isset($validated['message']) ? trim(strip_tags($validated['message'])) : null,
            'status' => ContactEnquiry::STATUS_NEW,
        ]);

        $message = 'Thank you for your enquiry! Our financial advisory team will get in touch with you shortly.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'enquiry' => $enquiry,
            ]);
        }

        return redirect()->to(url('/#contact'))
            ->with('contact_success', $message);
    }
}
