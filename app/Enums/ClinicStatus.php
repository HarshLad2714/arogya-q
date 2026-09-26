<?php

namespace App\Enums;

enum ClinicStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return __('ui.status.'.$this->value);
    }
}
