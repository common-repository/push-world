<?php
namespace pushworld\api\Exception;
class PushWorldApiForbbidenException extends PushWorldApiException
{
    public function __construct($message)
    {
        parent::__construct(sprintf($message));
    }
}
