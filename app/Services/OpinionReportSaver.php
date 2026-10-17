<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\OpinionReport;
use App\Models\User;
use App\Support\OpinionReportData;

/**
 * Persists the Opinion Report tab submitted with the lead form, inside the
 * lead create/update transaction.
 */
class OpinionReportSaver
{
    public static function permissionFor(Lead $lead): string
    {
        return $lead->opinionReport?->saved_at ? 'or.update' : 'or.create';
    }

    /**
     * @param array<string, mixed> $submitted the request's `opr` array
     */
    public function save(Lead $lead, array $submitted, User $user): OpinionReport
    {
        $data = OpinionReportData::normalize($submitted);

        $lead->unsetRelation('city');
        $lead->unsetRelation('bank');

        $row = OpinionReport::firstOrNew(['lead_id' => $lead->id]);
        $row->fill($data + [
            'linked_overrides' => OpinionReportData::overridesFor($lead, $data),
            'updated_by' => $user->id,
            'saved_at' => now(),
        ]);
        $row->created_by ??= $user->id;
        $row->save();

        $lead->setRelation('opinionReport', $row);

        return $row;
    }
}
