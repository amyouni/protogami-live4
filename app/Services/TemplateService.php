<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TemplateService
{
    public function path(): string
    {
        return base_path('docs/templates');
    }

    /**
     * @return list<array{name: string, filename: string}>
     */
    public function list(): array
    {
        if (! File::isDirectory($this->path())) {
            return [];
        }

        return array_values(collect(File::files($this->path()))
            ->filter(fn ($file) => $file->getExtension() === 'json')
            ->map(function ($file) {
                $data = json_decode($file->getContents(), true);

                return [
                    'name' => (string) ($data['name'] ?? $file->getFilenameWithoutExtension()),
                    'filename' => $file->getFilenameWithoutExtension(),
                ];
            })
            ->all());
    }

    /**
     * @return array<string, mixed>|null
     */
    public function load(string $filename): ?array
    {
        $filename = $this->sanitize($filename);
        $fullPath = $this->path().'/'.$filename.'.json';

        if (! File::exists($fullPath)) {
            return null;
        }

        $data = json_decode(File::get($fullPath), true);

        if (! is_array($data) || ! isset($data['pages'])) {
            return null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function save(string $filename, array $data): string
    {
        $filename = $this->sanitize($filename);

        File::ensureDirectoryExists($this->path());

        $contents = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        File::put($this->path().'/'.$filename.'.json', (string) $contents);

        return $filename;
    }

    public function delete(string $filename): bool
    {
        $filename = $this->sanitize($filename);
        $fullPath = $this->path().'/'.$filename.'.json';

        if (! File::exists($fullPath)) {
            return false;
        }

        return File::delete($fullPath);
    }

    public function sanitize(string $filename): string
    {
        return Str::slug(pathinfo($filename, PATHINFO_FILENAME));
    }
}
