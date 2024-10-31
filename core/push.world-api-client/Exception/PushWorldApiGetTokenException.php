<?php
namespace pushworld\api\Exception;
class PushWorldApiGetTokenException extends PushWorldApiException
{
    public function __construct($message)
    {
        parent::__construct(sprintf($message));
    }
}
