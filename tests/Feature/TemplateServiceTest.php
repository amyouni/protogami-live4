<?php

use App\Services\BrandingService;
use App\Services\SnippetService;
use App\Services\TemplateService;

it('lists shipped templates', function () {
    $templates = app(TemplateService::class)->list();

    expect(array_column($templates, 'filename'))
        ->toContain('football-site')
        ->toContain('blog');
});

it('loads a template with pages', function () {
    $data = app(TemplateService::class)->load('football-site');

    expect($data)->not->toBeNull()
        ->and($data['name'])->toBe('Football Site')
        ->and($data['pages'])->not->toBeEmpty()
        ->and($data['branding'])->toHaveKey('primary_color');
});

it('returns null for a missing template', function () {
    expect(app(TemplateService::class)->load('does-not-exist'))->toBeNull();
});

it('sanitizes filenames against path traversal', function () {
    $service = app(TemplateService::class);

    expect($service->sanitize('../../etc/passwd'))->toBe('passwd')
        ->and($service->sanitize('My Cool Template!'))->toBe('my-cool-template');
});

it('saves and deletes a template round-trip', function () {
    $service = app(TemplateService::class);

    $filename = $service->save('Round Trip Test', [
        'name' => 'Round Trip Test',
        'branding' => ['primary_color' => '#000000'],
        'pages' => [],
    ]);

    expect($filename)->toBe('round-trip-test');

    $loaded = $service->load($filename);
    expect($loaded)->not->toBeNull();

    expect($service->delete($filename))->toBeTrue()
        ->and($service->load($filename))->toBeNull();
});

it('loads snippets for all defined presets', function () {
    $service = app(SnippetService::class);

    foreach ($service->presets() as $type => $presets) {
        foreach (array_keys($presets) as $preset) {
            expect($service->load($type, $preset))
                ->not->toBeNull("Missing snippet file for {$type}_{$preset}");
        }
    }
});

it('returns null for unknown snippet presets', function () {
    expect(app(SnippetService::class)->load('navbar', 'does-not-exist'))->toBeNull();
});

it('renders navbar snippets with dynamic nav items', function () {
    $html = app(SnippetService::class)->render('navbar', 'items_right', [
        'navItems' => [
            ['name' => 'Home', 'id' => 'home', 'children' => []],
            ['name' => 'Teams', 'id' => 'teams', 'children' => [
                ['name' => 'First Team', 'id' => 'first-team'],
            ]],
        ],
    ]);

    expect($html)->toContain('Home')
        ->and($html)->toContain('Teams')
        ->and($html)->toContain('First Team')
        ->and($html)->toContain('data-page="home"')
        ->and($html)->toContain('data-page="first-team"');
});

it('lists shipped branding presets', function () {
    $presets = app(BrandingService::class)->list();

    expect(array_column($presets, 'filename'))
        ->toContain('ocean')
        ->toContain('forest')
        ->toContain('sunset')
        ->toContain('mono-dark');
});

it('loads a branding preset', function () {
    $data = app(BrandingService::class)->load('ocean');

    expect($data)->not->toBeNull()
        ->and($data['name'])->toBe('Ocean')
        ->and($data)->toHaveKey('primary_color')
        ->and($data)->toHaveKey('font_family');
});

it('saves and deletes a branding preset round-trip', function () {
    $service = app(BrandingService::class);

    $filename = $service->save('Test Preset', [
        'name' => 'Test Preset',
        'primary_color' => '#ff0000',
        'secondary_color' => '#00ff00',
        'accent_color' => '#0000ff',
        'background_color' => '#ffffff',
        'text_color' => '#000000',
        'font_family' => 'Inter',
    ]);

    expect($filename)->toBe('test-preset');

    $loaded = $service->load($filename);
    expect($loaded)->not->toBeNull();

    expect($service->delete($filename))->toBeTrue()
        ->and($service->load($filename))->toBeNull();
});
