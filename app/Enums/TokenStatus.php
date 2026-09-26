<?php

namespace App\Enums;

enum TokenStatus: string
{
    case Booked = 'booked';
    case Arrived = 'arrived';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return __('ui.status.'.$this->value);
    }
}
