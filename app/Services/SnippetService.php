<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class SnippetService
{
    /**
     * @var array<string, array<string, string>>
     */
    public const PRESETS = [
        'navbar' => [
            'items_left' => 'Items on Left',
            'items_right' => 'Items on Right',
            'centered' => 'Centered',
            'split' => 'Split',
        ],
        'content' => [
            'hero' => 'Hero',
            'grid' => 'Grid',
            'list' => 'List',
            'single_column' => 'Single Column',
            'features' => 'Features',
        ],
        'footer' => [
            'simple' => 'Simple',
            'multi_column' => 'Multi Column',
            'minimal' => 'Minimal',
        ],
    ];

    public function path(): string
    {
        return base_path('docs/snippets');
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function presets(): array
    {
        return self::PRESETS;
    }

    /**
     * @return array<string, string>
     */
    public function presetsFor(string $type): array
    {
        return self::PRESETS[$type] ?? [];
    }

    public function load(string $type, string $preset): ?string
    {
        if (! isset(self::PRESETS[$type][$preset])) {
            return null;
        }

        $fullPath = $this->path().'/'.$type.'_'.$preset.'.blade.php';

        if (! File::exists($fullPath)) {
            return null;
        }

        return File::get($fullPath);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function render(string $type, string $preset, array $data = []): ?string
    {
        if (! isset(self::PRESETS[$type][$preset])) {
            return null;
        }

        $fullPath = $this->path().'/'.$type.'_'.$preset.'.blade.php';

        if (! File::exists($fullPath)) {
            return null;
        }

        return view()->file($fullPath, $data)->render();
    }
}
