<?php

namespace App\Services;

use App\Models\DprReport;
use App\Models\Lead;
use App\Models\User;
use App\Support\DprData;

/**
 * Persists the DPR tab submitted with the lead form, inside the lead
 * create/update transaction.
 */
class DprReportSaver
{
    public static function permissionFor(Lead $lead): string
    {
        return $lead->dprReport?->saved_at ? 'dpr.update' : 'dpr.create';
    }

    /**
     * @param array<string, mixed> $submitted the request's `dpr` array
     */
    public function save(Lead $lead, array $submitted, User $user): DprReport
    {
        $data = DprData::normalize($submitted);

        $lead->unsetRelation('bank');

        $row = DprReport::firstOrNew(['lead_id' => $lead->id]);

        // Fields without a control on the form (e.g. manufacturing process, working capital, market
        // potential) keep their stored values instead of being blanked by a save.
        foreach (array_merge(array_keys(DprData::SCALARS), DprData::JSON_FIELDS) as $key) {
            if (! array_key_exists($key, $submitted)) {
                unset($data[$key]);
            }
        }
        $row->fill($data + [
            'linked_overrides' => DprData::overridesFor($lead, $data),
            'updated_by' => $user->id,
            'saved_at' => now(),
        ]);
        $row->created_by ??= $user->id;
        $row->save();

        $lead->setRelation('dprReport', $row);

        return $row;
    }
}
