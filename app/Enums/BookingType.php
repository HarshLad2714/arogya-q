<?php

namespace App\Enums;

enum BookingType: string
{
    case Online = 'online';
    case WalkIn = 'walk_in';

    public function label(): string
    {
        return __('ui.status.'.$this->value);
    }
}
