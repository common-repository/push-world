<?php
namespace pushworld\api\Exception;
class PushWorldApiValidationException extends PushWorldApiException
{
    public function __construct($message)
    {
        parent::__construct(sprintf($message));
    }
}
