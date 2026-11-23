<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A lead-workflow rule was broken (incomplete tabs, ineligible user, stale page...). The message
 * is written for the end user; the code is 403 when the cause is missing authority, else 422.
 */
class WorkflowException extends RuntimeException
{
    public function __construct(string $message, int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
