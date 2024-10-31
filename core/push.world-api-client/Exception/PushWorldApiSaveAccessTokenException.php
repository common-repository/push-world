<?php
namespace pushworld\api\Exception;
class PushWorldApiSaveAccessTokenException extends PushWorldApiException
{
    public function __construct($message)
    {
        parent::__construct(sprintf($message));
    }
}
