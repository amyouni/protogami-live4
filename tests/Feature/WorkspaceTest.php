<?php

use App\Models\User;
use Livewire\Livewire;

it('renders the workspace for guests', function () {
    $response = $this->get(route('workspace'));

    $response->assertOk();
    $response->assertSee(__('Sitemap'));
});

it('starts with a default home page', function () {
    Livewire::test('pages::proto.workspace')
        ->assertSet('selectedPageId', 'home')
        ->assertSee('Home');
});

it('loads a template', function () {
    Livewire::test('pages::proto.workspace')
        ->set('currentTemplate', 'football-site')
        ->assertSet('templateName', 'Football Site')
        ->assertSee('Fixtures');
});

it('adds a page', function () {
    Livewire::test('pages::proto.workspace')
        ->set('newPageName', 'About Us')
        ->call('addPage')
        ->assertSee('About Us');
});

it('deletes a page with its children', function () {
    $component = Livewire::test('pages::proto.workspace')
        ->set('currentTemplate', 'football-site')
        ->set('deletingPageId', 'teams')
        ->call('deletePage');

    $pageIds = array_column($component->get('pages'), 'id');

    expect($pageIds)->not->toContain('teams')
        ->not->toContain('first-team')
        ->not->toContain('youth-team');
});

it('adds and removes sections', function () {
    $component = Livewire::test('pages::proto.workspace')
        ->call('addSection', 'content', 'grid');

    $sections = collect($component->get('pages'))->firstWhere('id', 'home')['sections'];
    expect(collect($sections)->pluck('preset'))->toContain('grid');

    $gridSection = collect($sections)->firstWhere('preset', 'grid');
    $component->call('removeSection', $gridSection['id']);

    $sections = collect($component->get('pages'))->firstWhere('id', 'home')['sections'];
    expect(collect($sections)->pluck('preset'))->not->toContain('grid');
});

it('forbids guests from saving templates', function () {
    Livewire::test('pages::proto.workspace')
        ->set('saveFilename', 'my-template')
        ->call('saveTemplate')
        ->assertForbidden();
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

it('exports the selected page as a download', function () {
    Livewire::test('pages::proto.workspace')
        ->call('export')
        ->assertFileDownloaded('home.html');
});

it('switches page via select-page-from-preview event', function () {
    Livewire::test('pages::proto.workspace')
        ->set('currentTemplate', 'football-site')
        ->dispatch('select-page-from-preview', id: 'teams')
        ->assertSet('selectedPageId', 'teams');
});

it('loads a branding preset', function () {
    Livewire::test('pages::proto.workspace')
        ->set('currentBrandingPreset', 'ocean')
        ->assertSet('branding.primary_color', '#0ea5e9')
        ->assertSet('branding.font_family', 'Inter');
});

it('forbids guests from saving branding presets', function () {
    Livewire::test('pages::proto.workspace')
        ->set('saveBrandingName', 'test-preset')
        ->call('saveBranding')
        ->assertForbidden();
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
    $component = Livewire::test('pages::proto.workspace')
        ->set('newPageName', 'About')
        ->call('addPage');

    $html = $component->get('previewHtml');

    expect($html)->toContain('data-page="home"')
        ->and($html)->toContain('data-page="about"')
        ->and($html)->toContain('select-page-from-preview');
});

it('toggles panel visibility', function () {
    Livewire::test('pages::proto.workspace')
        ->assertSet('showSitemap', true)
        ->call('togglePanel', 'sitemap')
        ->assertSet('showSitemap', false)
        ->call('togglePanel', 'sitemap')
        ->assertSet('showSitemap', true)
        ->call('togglePanel', 'sections')
        ->assertSet('showSections', false)
        ->call('togglePanel', 'branding')
        ->assertSet('showBranding', false)
        ->call('togglePanel', 'theme')
        ->assertSet('showTheme', false);
});

it('sets preview width', function () {
    Livewire::test('pages::proto.workspace')
        ->assertSet('previewWidth', 'desktop')
        ->call('setPreviewWidth', 'mobile')
        ->assertSet('previewWidth', 'mobile')
        ->call('setPreviewWidth', 'laptop')
        ->assertSet('previewWidth', 'laptop');
});

it('selects a light theme preset without changing branding', function () {
    Livewire::test('pages::proto.workspace')
        ->set('branding.primary_color', '#ff0000')
        ->set('lightThemePreset', 'ocean')
        ->assertSet('branding.primary_color', '#ff0000')
        ->assertSet('lightThemePreset', 'ocean');
});

it('selects a dark theme preset without changing branding', function () {
    Livewire::test('pages::proto.workspace')
        ->set('branding.primary_color', '#ff0000')
        ->set('darkThemePreset', 'ocean')
        ->assertSet('branding.primary_color', '#ff0000')
        ->assertSet('darkThemePreset', 'ocean');
});

it('switches preview theme and uses dark theme preset in preview', function () {
    $component = Livewire::test('pages::proto.workspace')
        ->set('darkThemePreset', 'ocean')
        ->call('setPreviewTheme', 'dark');

    $html = $component->get('previewHtml');

    expect($html)->toContain('#0ea5e9');
});

it('uses light theme preset in preview when set', function () {
    $component = Livewire::test('pages::proto.workspace')
        ->set('lightThemePreset', 'ocean');

    $html = $component->get('previewHtml');

    expect($html)->toContain('#0ea5e9');
});

it('uses inline branding in preview when no theme preset is set', function () {
    $component = Livewire::test('pages::proto.workspace')
        ->set('branding.background_color', '#abcdef');

    $html = $component->get('previewHtml');

    expect($html)->toContain('#abcdef');
});
