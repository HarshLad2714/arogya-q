<?php

namespace Tests\Unit;

use App\Services\WaitTimeCalculator;
use PHPUnit\Framework\TestCase;

class WaitTimeCalculatorTest extends TestCase
{
    public function test_it_counts_patients_ahead_before_anyone_is_called(): void
    {
        $wait = new WaitTimeCalculator;

        $this->assertSame(0, $wait->ahead(1, 0));
        $this->assertSame(4, $wait->ahead(5, 0));
        $this->assertSame(48, $wait->minutes(5, 0, 12));
    }

    public function test_it_treats_the_current_token_as_being_served(): void
    {
        $wait = new WaitTimeCalculator;

        $this->assertSame(0, $wait->ahead(4, 3));
        $this->assertSame(0, $wait->ahead(3, 3));
        $this->assertSame(5, $wait->ahead(9, 3));
        $this->assertSame(60, $wait->minutes(9, 3, 12));
    }
}
