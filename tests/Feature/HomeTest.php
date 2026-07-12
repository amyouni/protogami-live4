<?php

it('renders the landing page for guests', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee(__('Start Building'));
    $response->assertSee(route('workspace'));
});
