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
