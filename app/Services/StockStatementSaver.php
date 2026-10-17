<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\StockStatement;
use App\Models\User;
use App\Support\StockStatementData;

/**
 * Persists the Stock Statement tab submitted with the lead form, inside the
 * lead create/update transaction.
 */
class StockStatementSaver
{
    public static function permissionFor(Lead $lead): string
    {
        return $lead->stockStatement?->saved_at ? 'sbi_stock_statement.update' : 'sbi_stock_statement.create';
    }

    /**
     * @param array<string, mixed> $submitted the request's `ss` array
     */
    public function save(Lead $lead, array $submitted, User $user): StockStatement
    {
        $data = StockStatementData::normalize($submitted);

        $lead->unsetRelation('city');

        $row = StockStatement::firstOrNew(['lead_id' => $lead->id]);
        $row->fill($data + [
            'linked_overrides' => StockStatementData::overridesFor($lead, $data),
            'updated_by' => $user->id,
            'saved_at' => now(),
        ]);
        $row->created_by ??= $user->id;
        $row->save();

        $lead->setRelation('stockStatement', $row);

        return $row;
    }
}
