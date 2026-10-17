<?php

namespace App\Services;

use App\Models\CmaProfile;
use App\Models\Lead;
use App\Models\User;
use App\Support\CmaProfileData;

/**
 * Persists the CMA tab submitted with the lead form, inside the lead
 * create/update transaction.
 */
class CmaProfileSaver
{
    public static function permissionFor(Lead $lead): string
    {
        return $lead->cmaProfile?->saved_at ? 'cma.update' : 'cma.create';
    }

    /**
     * @param array<string, mixed> $submitted the request's `cma` array
     */
    public function save(Lead $lead, array $submitted, User $user): CmaProfile
    {
        $data = CmaProfileData::normalize($submitted);

        $row = CmaProfile::firstOrNew(['lead_id' => $lead->id]);
        $row->fill($data + [
            'linked_overrides' => CmaProfileData::overridesFor($lead, $data),
            'updated_by' => $user->id,
            'saved_at' => now(),
        ]);
        $row->created_by ??= $user->id;
        $row->save();

        $lead->setRelation('cmaProfile', $row);

        return $row;
    }
}
