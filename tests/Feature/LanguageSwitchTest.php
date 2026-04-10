<?php

namespace Tests\Feature;

use Tests\TestCase;

class LanguageSwitchTest extends TestCase
{
    public function test_language_route_sets_bangla_locale_in_session(): void
    {
        $response = $this->from('/')->get('/language/bn');

        $response->assertRedirect('/');
        $response->assertSessionHas('locale', 'bn');

        $this->get('/')
            ->assertOk()
            ->assertSee('হোম', false);
    }

    public function test_language_route_sets_english_locale_in_session(): void
    {
        $this->withSession(['locale' => 'bn']);

        $response = $this->from('/')->get('/language/en');

        $response->assertRedirect('/');
        $response->assertSessionHas('locale', 'en');

        $this->get('/')
            ->assertOk()
            ->assertSee('Home', false);
    }
}
