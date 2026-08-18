<?php

namespace App\Http\Controllers\Concerns;

trait HasPaginationPerPage
{
    /**
     * Resolve the requested page size, clamped to a fixed allow-list so a
     * tampered query string can't force an unbounded/expensive query.
     */
    protected function perPage(int $default = 15): int
    {
        $requested = (int) request('per_page', $default);

        return in_array($requested, [10, 25, 50, 100], true) ? $requested : $default;
    }
}
