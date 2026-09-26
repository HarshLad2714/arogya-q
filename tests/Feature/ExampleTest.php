<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_introduces_the_token_board(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('ArogyaQ')
            ->assertSee('Hold your place in line');
    }
}
