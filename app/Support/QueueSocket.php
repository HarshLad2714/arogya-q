<?php

namespace App\Support;

class QueueSocket
{
    public static function url(): string
    {
        $scheme = request()->isSecure() ? 'wss' : 'ws';
        $port = (int) config('arogya.queue_engine.ws_port', 8082);

        return $scheme.'://'.request()->getHost().':'.$port;
    }
}
