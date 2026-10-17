<?php

namespace App\Services;

use App\Models\ApplicationForm;
use App\Models\Lead;
use App\Models\User;
use App\Support\ApplicationFormData;

/**
 * Persists the Application Form tab's submission for a lead. Called from the
 * lead create/update flow, inside that flow's transaction, so the lead and
 * its Application Form are saved together or not at all.
 */
class ApplicationFormSaver
{
    /**
     * Whether this user may save the tab, and with which permission.
     */
    public static function permissionFor(Lead $lead): string
    {
        return $lead->applicationForm?->saved_at ? 'application_form.update' : 'application_form.create';
    }

    /**
     * @param array<string, mixed> $submitted the request's `af` array
     */
    public function save(Lead $lead, array $submitted, User $user): ApplicationForm
    {
        $data = ApplicationFormData::normalize($submitted);

        // The lead has just been saved, so its values are the ones the form's
        // linked fields are measured against.
        $lead->unsetRelation('city');
        $overrides = ApplicationFormData::overridesFor($lead, $data);

        $form = ApplicationForm::firstOrNew(['lead_id' => $lead->id]);
        $form->fill($data + [
            'linked_overrides' => $overrides,
            'updated_by' => $user->id,
            'saved_at' => now(),
        ]);
        $form->created_by ??= $user->id;
        $form->save();

        $lead->setRelation('applicationForm', $form);

        return $form;
    }
}
