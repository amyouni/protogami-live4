<div class="flex min-h-screen flex-col">
    <header class="flex items-center justify-between px-6 py-4 sm:px-10">
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-zinc-900 text-white dark:bg-white dark:text-zinc-900">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1" />
                    <rect x="14" y="3" width="7" height="7" rx="1" />
                    <rect x="3" y="14" width="7" height="7" rx="1" />
                    <rect x="14" y="14" width="7" height="7" rx="1" />
                </svg>
            </div>
            <span class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ config('app.name', 'Proto') }}</span>
        </div>
        <nav class="flex items-center gap-3">
            @auth
                <flux:button variant="ghost" href="{{ route('dashboard') }}" wire:navigate>{{ __('Dashboard') }}</flux:button>
            @else
                <flux:button variant="ghost" href="{{ route('login') }}" wire:navigate>{{ __('Log in') }}</flux:button>
            @endauth
        </nav>
    </header>

    <main class="flex flex-1 flex-col items-center justify-center px-6 text-center">
        <h1 class="max-w-3xl text-4xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 sm:text-6xl">
            {{ __('Build your site from ready-made templates') }}
        </h1>
        <p class="mt-6 max-w-xl text-lg text-zinc-500 dark:text-zinc-400">
            {{ __('Pick a template, shape your sitemap, customize every section, and export clean Tailwind code.') }}
        </p>
        <div class="mt-10">
            @auth
                <flux:button variant="primary" size="base" href="{{ route('dashboard') }}" wire:navigate icon="rocket-launch">
                    {{ __('Go to Dashboard') }}
                </flux:button>
            @else
                <flux:button variant="primary" size="base" href="{{ route('login') }}" wire:navigate icon="rocket-launch">
                    {{ __('Start Building') }}
                </flux:button>
            @endauth
        </div>
    </main>

    <footer class="px-6 py-6 text-center text-sm text-zinc-400 dark:text-zinc-500">
        {{ __('Templates and snippets stored as files — ready for your GitHub repo.') }}
    </footer>
</div>
