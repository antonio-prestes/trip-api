<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ForbiddenException extends HttpException
{
    public function __construct(string $message = 'Forbidden', \Throwable $previous = null)
    {
        parent::__construct(403, $message, $previous);
    }
}
