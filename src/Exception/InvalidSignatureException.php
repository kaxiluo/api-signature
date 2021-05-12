<?php

declare(strict_types=1);

namespace Kaxiluo\ApiSignature\Exception;

use Throwable;

class InvalidSignatureException extends \Exception
{
    private $invalidField;

    public function __construct($invalidField, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->invalidField = $invalidField;
    }

    public function getInvalidField()
    {
        return $this->invalidField;
    }
}
