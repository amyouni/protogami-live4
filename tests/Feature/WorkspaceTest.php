<?php

use App\Models\User;
use Livewire\Livewire;

it('redirects guests from the workspace', function () {
    $response = $this->get(route('workspace'));

    $response->assertRedirect(route('login'));
});

it('allows authenticated users to access the workspace', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('workspace'));

    $response->assertOk();
    $response->assertSee(__('Sitemap'));
});

it('starts with a default home page', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::proto.workspace')
        ->assertSet('selectedPageId', 'home')
        ->assertSee('Home');
});

it('loads a template via route parameter', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::proto.workspace', ['template' => 'football-site'])
        ->assertSet('templateName', 'Football Site')
        ->assertSee('Fixtures');
});

it('deletes a page with its children', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test('pages::proto.workspace', ['template' => 'football-site'])
        ->set('deletingPageId', 'teams')
        ->call('deletePage');

    $pageIds = array_column($component->get('pages'), 'id');

    expect($pageIds)->not->toContain('teams')
        ->not->toContain('first-team')
        ->not->toContain('youth-team');
});

it('adds and removes sections', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test('pages::proto.workspace')
        ->call('addSection', 'content', 'grid');

    $sections = collect($component->get('pages'))->firstWhere('id', 'home')['sections'];
    expect(collect($sections)->pluck('preset'))->toContain('grid');

    $gridSection = collect($sections)->firstWhere('preset', 'grid');
    $component->call('removeSection', $gridSection['id']);

    $sections = collect($component->get('pages'))->firstWhere('id', 'home')['sections'];
    expect(collect($sections)->pluck('preset'))->not->toContain('grid');
});

it('allows authenticated users to save templates', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::proto.workspace')
        ->set('saveFilename', 'test-save-template')
        ->call('saveTemplate')
        ->assertHasNoErrors();

    expect(file_exists(base_path('docs/templates/test-save-template.json')))->toBeTrue();

    unlink(base_path('docs/templates/test-save-template.json'));
});

it('exports all pages as a ZIP download', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::proto.workspace')
        ->call('export')
        ->assertFileDownloaded('untitled.zip');
});

it('shows the export modal with format options', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::proto.workspace')
        ->assertSee(__('Export Site'))
        ->assertSee(__('HTML (Standalone)'))
        ->assertSee(__('Laravel Livewire'))
        ->assertSee(__('Laravel Inertia + Vue'))
        ->assertSee(__('Laravel Inertia + React'));
});

it('exports multiple pages as separate HTML files in ZIP', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::proto.workspace')
        ->set('newPageName', 'About')
        ->call('addPage')
        ->call('export')
        ->assertFileDownloaded('untitled.zip');
});

it('switches page via select-page-from-preview event', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::proto.workspace', ['template' => 'football-site'])
        ->dispatch('select-page-from-preview', id: 'teams')
        ->assertSet('selectedPageId', 'teams');
});

it('loads a branding preset', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::proto.workspace')
        ->set('currentBrandingPreset', 'ocean')
        ->assertSet('branding.primary_color', '#0ea5e9')
        ->assertSet('branding.font_family', 'Inter');
});

it('allows authenticated users to save branding presets', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::proto.workspace')
        ->set('saveBrandingName', 'test-save-preset')
        ->call('saveBranding')
        ->assertHasNoErrors();

    expect(file_exists(base_path('docs/branding/test-save-preset.json')))->toBeTrue();

    unlink(base_path('docs/branding/test-save-preset.json'));
});

it('generates preview HTML with dynamic nav and link interception', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test('pages::proto.workspace')
        ->set('newPageName', 'About')
        ->call('addPage');

    $html = $component->get('previewHtml');

    expect($html)->toContain('data-page="home"')
        ->and($html)->toContain('data-page="about"')
        ->and($html)->toContain('select-page-from-preview');
});

it('toggles panel visibility', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::proto.workspace')
        ->assertSet('showSitemap', true)
        ->call('togglePanel', 'sitemap')
        ->assertSet('showSitemap', false)
        ->call('togglePanel', 'sitemap')
        ->assertSet('showSitemap', true)
        ->call('togglePanel', 'sections')
        ->assertSet('showSections', false)
        ->call('togglePanel', 'branding')
        ->assertSet('showBranding', false);
});

it('sets preview width', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::proto.workspace')
        ->assertSet('previewWidth', 'desktop')
        ->call('setPreviewWidth', 'mobile')
        ->assertSet('previewWidth', 'mobile')
        ->call('setPreviewWidth', 'laptop')
        ->assertSet('previewWidth', 'laptop');
});

it('saves current branding as light theme', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::proto.workspace')
        ->set('branding.primary_color', '#ff0000')
        ->call('saveLightTheme')
        ->assertSet('lightThemeBranding.primary_color', '#ff0000');
});

it('saves current branding as dark theme', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::proto.workspace')
        ->set('branding.primary_color', '#ff0000')
        ->call('saveDarkTheme')
        ->assertSet('darkThemeBranding.primary_color', '#ff0000');
});

it('uses dark theme branding in preview when set', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test('pages::proto.workspace')
        ->set('darkThemeBranding', [
            'primary_color' => '#0ea5e9',
            'secondary_color' => '#0369a1',
            'accent_color' => '#f59e0b',
            'background_color' => '#1a1a2e',
            'text_color' => '#e4e4e7',
            'font_family' => 'Inter',
        ])
        ->call('setPreviewTheme', 'dark');

    $html = $component->get('previewHtml');

    expect($html)->toContain('#1a1a2e');
});

it('uses light theme branding in preview when set', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test('pages::proto.workspace')
        ->set('lightThemeBranding', [
            'primary_color' => '#0ea5e9',
            'secondary_color' => '#0369a1',
            'accent_color' => '#f59e0b',
            'background_color' => '#f0f9ff',
            'text_color' => '#0c4a6e',
            'font_family' => 'Inter',
        ])
        ->call('setPreviewTheme', 'light');

    $html = $component->get('previewHtml');

    expect($html)->toContain('#f0f9ff');
});

it('reflects live branding changes in preview immediately', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test('pages::proto.workspace')
        ->set('branding.background_color', '#abcdef');

    $html = $component->get('previewHtml');

    expect($html)->toContain('#abcdef');
});
