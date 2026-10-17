<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\ApplicationFormGenerator;
use App\Support\ApplicationFormData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationFormController extends Controller
{
    private const TAB = 'application-form';

    /**
     * The form lives in the Application Form tab of the lead's edit page;
     * this is the permission-checked entry point that opens it.
     */
    public function show(Lead $lead): RedirectResponse
    {
        $this->authorize('application_form.view');

        return redirect()->route('admin.leads.edit', $lead)->with('open_tab', self::TAB);
    }

    public function generate(Lead $lead, ApplicationFormGenerator $generator): RedirectResponse
    {
        $this->authorize('application_form.generate');

        $missing = ApplicationFormData::missingRequired(ApplicationFormData::for($lead));
        if ($missing) {
            return redirect()->route('admin.leads.edit', $lead)
                ->withErrors(['af_generate' => 'Fill in these fields before generating: ' . implode(', ', $missing) . '.'])
                ->with('open_tab', self::TAB);
        }

        $generator->generate($lead);

        return redirect()->route('admin.leads.edit', $lead)
            ->with('success', 'Application Form generated successfully.')
            ->with('open_tab', self::TAB);
    }

    public function download(Lead $lead): StreamedResponse
    {
        $this->authorize('application_form.download');

        // The path is derived from the route's lead, never from the request
        // or from a stored path, so one lead's file can't be reached via another.
        $path = ApplicationFormGenerator::relativePath($lead);
        $disk = Storage::disk(ApplicationFormGenerator::DISK);

        abort_unless($lead->applicationForm?->generated_path === $path && $disk->exists($path), 404, 'Application Form has not been generated yet.');

        return $disk->download($path, 'Application_Form_' . $lead->id . '.xlsx');
    }
}
