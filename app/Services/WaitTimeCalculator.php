<?php

namespace App\Services;

class WaitTimeCalculator
{
    public function ahead(int $tokenNumber, int $currentToken): int
    {
        if ($tokenNumber <= $currentToken) {
            return 0;
        }

        if ($currentToken <= 0) {
            return max(0, $tokenNumber - 1);
        }

        return max(0, $tokenNumber - $currentToken - 1);
    }

    public function minutes(int $tokenNumber, int $currentToken, int $avgMinutes): int
    {
        return $this->ahead($tokenNumber, $currentToken) * max(0, $avgMinutes);
    }
}
