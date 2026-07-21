<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class TripRequestCannotBeUpdatedException extends HttpException
{
    public function __construct(string $currentStatus, string $requestedStatus)
    {
        parent::__construct(
            400,
            "Não é possível alterar de '{$currentStatus}' para '{$requestedStatus}'."
        );
    }
}
