<?php

use App\Models\User;

it('renders the landing page for guests', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee(__('Start Building'));
    $response->assertSee(route('login'));
});

it('shows dashboard link for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk();
    $response->assertSee(__('Go to Dashboard'));
    $response->assertSee(route('dashboard'));
});
