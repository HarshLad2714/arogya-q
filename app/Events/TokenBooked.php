<?php

namespace App\Events;

use App\Models\Token;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TokenBooked
{
    use Dispatchable, SerializesModels;

    public function __construct(public Token $token) {}
}
