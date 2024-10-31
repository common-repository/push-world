<?php
namespace pushworld\api;

interface PushWorldApiInterface {

    /**
     * Create new push message
     *
     * @param $platformCode
     * @param array $multicast
     * @param array $subscribers
     */
    public function multicastSend($platformCode, $multicast, $subscribers = array());
}
