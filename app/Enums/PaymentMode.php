<?php

namespace App\Enums;

enum PaymentMode: string
{
    case Online = 'online';
    case Cash = 'cash';

    public function label(): string
    {
        return __('ui.status.'.$this->value);
    }
}
