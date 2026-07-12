<?php

use App\Services\BrandingService;
use App\Services\SnippetService;
use App\Services\TemplateService;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

return new #[Layout('layouts.builder', ['title' => 'Workspace'])] class extends Component
{
    /** @var array<string, string> */
    public array $branding = [
        'primary_color' => '#3b82f6',
        'secondary_color' => '#1e40af',
        'accent_color' => '#f59e0b',
        'background_color' => '#ffffff',
        'text_color' => '#1f2937',
        'font_family' => 'Inter',
    ];

    /** @var array<string, string> */
    public array $darkBranding = [
        'primary_color' => '#3b82f6',
        'secondary_color' => '#1e40af',
        'accent_color' => '#f59e0b',
        'background_color' => '#1a1a2e',
        'text_color' => '#e4e4e7',
        'font_family' => 'Inter',
    ];

    /** @var list<array<string, mixed>> */
    public array $pages = [];

    public ?string $selectedPageId = null;

    public string $currentTemplate = '';

    public string $templateName = 'Untitled';

    public string $newPageName = '';

    public string $newPageParentId = '';

    public ?string $deletingPageId = null;

    public string $saveFilename = '';

    public string $currentBrandingPreset = '';

    public string $currentDarkBrandingPreset = '';

    public string $saveBrandingName = '';

    public bool $showSitemap = true;

    public bool $showSections = true;

    public bool $showBranding = true;

    public string $previewWidth = 'desktop';

    public string $brandingTab = 'light';

    public string $previewTheme = 'light';

    public function togglePanel(string $panel): void
    {
        match ($panel) {
            'sitemap' => $this->showSitemap = ! $this->showSitemap,
            'sections' => $this->showSections = ! $this->showSections,
            'branding' => $this->showBranding = ! $this->showBranding,
            default => null,
        };
    }

    public function setPreviewWidth(string $width): void
    {
        $this->previewWidth = $width;
    }

    public function setBrandingTab(string $tab): void
    {
        $this->brandingTab = $tab;
    }

    public function setPreviewTheme(string $theme): void
    {
        $this->previewTheme = $theme;
    }

    public function mount(): void
    {
        $this->pages = [
            [
                'id' => 'home',
                'name' => 'Home',
                'parent_id' => null,
                'order' => 0,
                'sections' => [
                    ['id' => Str::random(8), 'type' => 'navbar', 'preset' => 'items_right'],
                    ['id' => Str::random(8), 'type' => 'content', 'preset' => 'hero'],
                    ['id' => Str::random(8), 'type' => 'footer', 'preset' => 'simple'],
                ],
            ],
        ];
        $this->selectedPageId = 'home';
    }

    public function updatedCurrentTemplate(string $filename): void
    {
        if ($filename === '') {
            return;
        }

        $data = app(TemplateService::class)->load($filename);

        if (! $data) {
            Flux::toast(variant: 'danger', text: __('Could not load template.'));

            return;
        }

        $this->templateName = $data['name'] ?? $filename;
        $this->branding = array_merge($this->branding, $data['branding'] ?? []);
        $this->darkBranding = array_merge($this->darkBranding, $data['dark_branding'] ?? []);
        $this->pages = $data['pages'];
        $this->selectedPageId = $this->pages[0]['id'] ?? null;
        $this->saveFilename = $filename;

        Flux::toast(variant: 'success', text: __('Template loaded.'));
    }

    public function selectPage(string $id): void
    {
        $this->selectedPageId = $id;
    }

    #[On('select-page-from-preview')]
    public function selectPageFromPreview(string $id): void
    {
        $this->selectPage($id);
    }

    public function updatedCurrentBrandingPreset(string $filename): void
    {
        if ($filename === '') {
            return;
        }

        $data = app(BrandingService::class)->load($filename);

        if (! $data) {
            Flux::toast(variant: 'danger', text: __('Could not load branding preset.'));

            return;
        }

        unset($data['name']);
        $this->branding = array_merge($this->branding, $data);

        Flux::toast(variant: 'success', text: __('Branding preset applied.'));
    }

    public function updatedCurrentDarkBrandingPreset(string $filename): void
    {
        if ($filename === '') {
            return;
        }

        $data = app(BrandingService::class)->load($filename);

        if (! $data) {
            Flux::toast(variant: 'danger', text: __('Could not load branding preset.'));

            return;
        }

        unset($data['name']);
        $this->darkBranding = array_merge($this->darkBranding, $data);

        Flux::toast(variant: 'success', text: __('Dark branding preset applied.'));
    }

    public function saveBranding(): void
    {
        abort_unless(auth()->check(), 403);

        $this->validate(['saveBrandingName' => 'required|string|max:60']);

        $activeBranding = $this->brandingTab === 'dark' ? $this->darkBranding : $this->branding;

        $filename = app(BrandingService::class)->save($this->saveBrandingName, array_merge(
            ['name' => $this->saveBrandingName],
            $activeBranding
        ));

        if ($this->brandingTab === 'dark') {
            $this->currentDarkBrandingPreset = $filename;
        } else {
            $this->currentBrandingPreset = $filename;
        }
        $this->saveBrandingName = '';

        Flux::modal('saveBranding')->close();
        Flux::toast(variant: 'success', text: __('Branding preset saved.'));
    }

    public function addPage(): void
    {
        $this->validate(['newPageName' => 'required|string|max:60']);

        $id = Str::slug($this->newPageName);
        $existingIds = array_column($this->pages, 'id');
        $suffix = 2;
        $baseId = $id;
        while (in_array($id, $existingIds)) {
            $id = $baseId.'-'.$suffix++;
        }

        $parentId = $this->newPageParentId !== '' ? $this->newPageParentId : null;
        $siblingCount = count(array_filter($this->pages, fn (array $p) => $p['parent_id'] === $parentId));

        $this->pages[] = [
            'id' => $id,
            'name' => $this->newPageName,
            'parent_id' => $parentId,
            'order' => $siblingCount,
            'sections' => [
                ['id' => Str::random(8), 'type' => 'navbar', 'preset' => 'items_right'],
                ['id' => Str::random(8), 'type' => 'footer', 'preset' => 'simple'],
            ],
        ];

        $this->selectedPageId = $id;
        $this->reset('newPageName', 'newPageParentId');
        Flux::modal('addPage')->close();
        Flux::toast(variant: 'success', text: __('Page added.'));
    }

    public function deletePage(): void
    {
        if (! $this->deletingPageId) {
            return;
        }

        $idsToRemove = $this->collectDescendantIds($this->deletingPageId);
        $idsToRemove[] = $this->deletingPageId;

        $this->pages = array_values(array_filter($this->pages, fn (array $p) => ! in_array($p['id'], $idsToRemove)));

        if (in_array($this->selectedPageId, $idsToRemove)) {
            $this->selectedPageId = $this->pages[0]['id'] ?? null;
        }

        $this->deletingPageId = null;
        Flux::toast(variant: 'success', text: __('Page deleted.'));
    }

    public function updateSelectedPageName(string $name): void
    {
        if (trim($name) === '') {
            return;
        }

        foreach ($this->pages as $i => $page) {
            if ($page['id'] === $this->selectedPageId) {
                $this->pages[$i]['name'] = $name;
            }
        }
    }

    public function updatePageOrder(string $id, int $position, ?string $parentId = null): void
    {
        $parentId = $parentId !== '' ? $parentId : null;

        if ($parentId !== null && in_array($parentId, $this->collectDescendantIds($id))) {
            return;
        }

        foreach ($this->pages as $i => $page) {
            if ($page['id'] === $id) {
                $this->pages[$i]['parent_id'] = $parentId;
            }
        }

        $siblings = array_values(array_filter($this->pages, fn (array $p) => $p['parent_id'] === $parentId && $p['id'] !== $id));
        usort($siblings, fn (array $a, array $b) => $a['order'] <=> $b['order']);

        $ordered = array_column($siblings, 'id');
        array_splice($ordered, $position, 0, [$id]);

        foreach ($this->pages as $i => $page) {
            $index = array_search($page['id'], $ordered);
            if ($index !== false) {
                $this->pages[$i]['order'] = $index;
            }
        }
    }

    public function addSection(string $type, string $preset): void
    {
        foreach ($this->pages as $i => $page) {
            if ($page['id'] === $this->selectedPageId) {
                $this->pages[$i]['sections'][] = ['id' => Str::random(8), 'type' => $type, 'preset' => $preset];
            }
        }
    }

    public function removeSection(string $sectionId): void
    {
        foreach ($this->pages as $i => $page) {
            if ($page['id'] === $this->selectedPageId) {
                $this->pages[$i]['sections'] = array_values(
                    array_filter($page['sections'], fn (array $s) => $s['id'] !== $sectionId)
                );
            }
        }
    }

    public function updateSectionPreset(string $sectionId, string $preset): void
    {
        foreach ($this->pages as $i => $page) {
            if ($page['id'] === $this->selectedPageId) {
                foreach ($page['sections'] as $j => $section) {
                    if ($section['id'] === $sectionId) {
                        $this->pages[$i]['sections'][$j]['preset'] = $preset;
                    }
                }
            }
        }
    }

    public function updateSectionOrder(string $sectionId, int $position): void
    {
        foreach ($this->pages as $i => $page) {
            if ($page['id'] === $this->selectedPageId) {
                $sections = array_values(array_filter($page['sections'], fn (array $s) => $s['id'] !== $sectionId));
                $moved = collect($page['sections'])->firstWhere('id', $sectionId);
                if ($moved) {
                    array_splice($sections, $position, 0, [$moved]);
                    $this->pages[$i]['sections'] = $sections;
                }
            }
        }
    }

    public function saveTemplate(): void
    {
        abort_unless(auth()->check(), 403);

        $this->validate(['saveFilename' => 'required|string|max:60']);

        $filename = app(TemplateService::class)->save($this->saveFilename, [
            'name' => $this->templateName,
            'branding' => $this->branding,
            'dark_branding' => $this->darkBranding,
            'pages' => $this->pages,
        ]);

        $this->currentTemplate = $filename;
        $this->saveFilename = $filename;

        Flux::modal('saveTemplate')->close();
        Flux::toast(variant: 'success', text: __('Template saved.'));
    }

    public function export(): ?StreamedResponse
    {
        $page = $this->findPage($this->selectedPageId);

        if (! $page) {
            return null;
        }

        $html = $this->buildPageHtml($page);
        $filename = Str::slug($page['name']).'.html';

        return response()->streamDownload(function () use ($html): void {
            echo $html;
        }, $filename, ['Content-Type' => 'text/html']);
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function templates(): array
    {
        return app(TemplateService::class)->list();
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function brandingPresets(): array
    {
        return app(BrandingService::class)->list();
    }

    /** @return array<string, array<string, string>> */
    #[Computed]
    public function presets(): array
    {
        return app(SnippetService::class)->presets();
    }

    /** @return array<string, mixed>|null */
    #[Computed]
    public function selectedPage(): ?array
    {
        return $this->findPage($this->selectedPageId);
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function pageTree(): array
    {
        return $this->buildTree(null);
    }

    #[Computed]
    public function previewHtml(): string
    {
        $page = $this->findPage($this->selectedPageId);

        return $page ? $this->buildPageHtml($page) : '';
    }

    /** @return array<string, mixed>|null */
    protected function findPage(?string $id): ?array
    {
        if ($id === null) {
            return null;
        }

        return collect($this->pages)->firstWhere('id', $id);
    }

    /** @return list<array<string, mixed>> */
    protected function buildTree(?string $parentId): array
    {
        return collect($this->pages)
            ->filter(fn (array $p) => $p['parent_id'] === $parentId)
            ->sortBy('order')
            ->map(fn (array $p) => array_merge($p, ['children' => $this->buildTree($p['id'])]))
            ->values()
            ->all();
    }

    /** @return list<string> */
    protected function collectDescendantIds(string $id): array
    {
        $ids = [];
        foreach ($this->pages as $page) {
            if ($page['parent_id'] === $id) {
                $ids[] = $page['id'];
                $ids = array_merge($ids, $this->collectDescendantIds($page['id']));
            }
        }

        return $ids;
    }

    /** @return list<array<string, mixed>> */
    protected function navItems(): array
    {
        return collect($this->pages)
            ->filter(fn (array $p) => $p['parent_id'] === null)
            ->sortBy('order')
            ->map(fn (array $p) => [
                'name' => $p['name'],
                'id' => $p['id'],
                'children' => collect($this->pages)
                    ->filter(fn (array $c) => $c['parent_id'] === $p['id'])
                    ->sortBy('order')
                    ->map(fn (array $c) => ['name' => $c['name'], 'id' => $c['id']])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    /** @param array<string, mixed> $page */
    protected function buildPageHtml(array $page): string
    {
        $snippets = app(SnippetService::class);
        $navItems = $this->navItems();

        $body = collect($page['sections'])
            ->map(fn (array $s) => $snippets->render($s['type'], $s['preset'], ['navItems' => $navItems]) ?? '')
            ->implode("\n");

        $b = $this->previewTheme === 'dark' ? $this->darkBranding : $this->branding;
        $fontQuery = Str::replace(' ', '+', $b['font_family']);

        $interceptScript = <<<'JS'
<script>
document.addEventListener('click', function(e) {
    var link = e.target.closest('a');
    if (!link) return;
    e.preventDefault();
    var pageId = link.getAttribute('data-page');
    if (pageId && parent.Livewire) {
        parent.Livewire.dispatch('select-page-from-preview', { id: pageId });
    }
});
</script>
JS;

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$page['name']}</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family={$fontQuery}:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-primary: {$b['primary_color']};
            --brand-secondary: {$b['secondary_color']};
            --brand-accent: {$b['accent_color']};
            --brand-bg: {$b['background_color']};
            --brand-text: {$b['text_color']};
        }
        body { font-family: '{$b['font_family']}', sans-serif; background-color: var(--brand-bg); }
    </style>
</head>
<body>
{$body}
{$interceptScript}
</body>
</html>
HTML;
    }
};
