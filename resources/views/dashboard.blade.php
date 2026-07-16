<x-layouts::app :title="__('Dashboard')">
    <div class="flex min-h-full w-full flex-col gap-8 p-6">
        <div class="max-w-3xl">
            <flux:heading size="xl">{{ __('Create a New Project') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Start from a template or a blank canvas.') }}</flux:subheading>
        </div>

        <div class="grid max-w-6xl gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <a href="{{ route('workspace') }}" wire:navigate
                class="group flex min-h-40 flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 p-8 transition-colors hover:border-zinc-400 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:border-zinc-600 dark:hover:bg-zinc-800"
            >
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                </div>
                <flux:heading size="sm">{{ __('Blank Project') }}</flux:heading>
                <flux:text class="mt-1 text-center text-sm text-zinc-500 dark:text-zinc-400">{{ __('Start from scratch with a single page.') }}</flux:text>
            </a>

            @foreach ($templates as $template)
                <a href="{{ route('workspace', ['template' => $template['filename']]) }}" wire:navigate
                    class="group flex min-h-40 flex-col rounded-xl border border-zinc-200 bg-white p-6 transition-colors hover:border-zinc-400 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600 dark:hover:bg-zinc-800"
                >
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-zinc-900 text-white dark:bg-white dark:text-zinc-900">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    </div>
                    <flux:heading size="sm">{{ $template['name'] }}</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Open this template in the workspace.') }}</flux:text>
                </a>
            @endforeach
        </div>
    </div>
</x-layouts::app>
