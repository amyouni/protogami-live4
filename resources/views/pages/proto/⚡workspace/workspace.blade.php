<div class="flex h-screen flex-col">
    <!-- Header -->
    <header class="flex items-center justify-between gap-4 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-zinc-900 text-white dark:bg-white dark:text-zinc-900">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1" />
                        <rect x="14" y="3" width="7" height="7" rx="1" />
                        <rect x="3" y="14" width="7" height="7" rx="1" />
                        <rect x="14" y="14" width="7" height="7" rx="1" />
                    </svg>
                </div>
                <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ config('app.name', 'Proto') }}</span>
            </a>
            <flux:select wire:model.live="currentTemplate" placeholder="{{ __('Choose template...') }}" class="min-w-[180px]">
                @foreach ($this->templates as $template)
                    <flux:select.option value="{{ $template['filename'] }}">{{ $template['name'] }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="flex items-center gap-2">
            <flux:button variant="ghost" icon="arrow-down-tray" wire:click="export">
                {{ __('Export') }}
            </flux:button>
            @auth
                <flux:modal.trigger name="saveTemplate">
                    <flux:button variant="primary" icon="bookmark">{{ __('Save Template') }}</flux:button>
                </flux:modal.trigger>
            @else
                <flux:tooltip content="{{ __('Sign in to save templates') }}">
                    <flux:button variant="ghost" href="{{ route('login') }}" wire:navigate icon="lock-closed">
                        {{ __('Sign in to save') }}
                    </flux:button>
                </flux:tooltip>
            @endauth
        </div>
    </header>

    <!-- Body: 3 panels -->
    <div class="flex flex-1 overflow-hidden">
        <!-- Left: Sitemap -->
        <aside class="flex w-72 shrink-0 flex-col border-e border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between px-4 py-3">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Sitemap') }}</h2>
                <flux:modal.trigger name="addPage">
                    <flux:button variant="ghost" size="sm" icon="plus" class="h-7 w-7 p-0" />
                </flux:modal.trigger>
            </div>
            <div class="flex-1 overflow-y-auto px-2 pb-4">
                <ul wire:sort="updatePageOrder" wire:sort:group="pages" wire:sort:group-id="" class="min-h-4 space-y-0.5">
                    @foreach ($this->pageTree as $node)
                        @include('pages.proto._partials.sitemap-node', ['node' => $node, 'depth' => 0])
                    @endforeach
                </ul>
            </div>

            <!-- Page thumbnails row -->
            <div class="border-t border-zinc-200 p-3 dark:border-zinc-700">
                <p class="mb-2 text-xs font-medium uppercase tracking-wider text-zinc-400">{{ __('Pages') }}</p>
                <div class="flex gap-2 overflow-x-auto pb-1">
                    @foreach ($pages as $page)
                        <button
                            type="button"
                            wire:key="thumb-{{ $page['id'] }}"
                            wire:click="selectPage('{{ $page['id'] }}')"
                            @class([
                                'flex h-16 w-14 shrink-0 flex-col items-center justify-center gap-1 rounded-md border text-center',
                                'border-blue-500 bg-blue-50 dark:bg-blue-950' => $selectedPageId === $page['id'],
                                'border-zinc-200 hover:border-zinc-400 dark:border-zinc-700' => $selectedPageId !== $page['id'],
                            ])
                        >
                            <svg class="h-5 w-5 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <span class="w-full truncate px-1 text-[10px] text-zinc-600 dark:text-zinc-300">{{ $page['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </aside>

        <!-- Center: Editor + Preview -->
        <main class="flex flex-1 flex-col overflow-hidden">
            @if ($this->selectedPage)
                <div class="flex items-center justify-between gap-4 border-b border-zinc-200 px-4 py-2.5 dark:border-zinc-700">
                    <flux:input
                        value="{{ $this->selectedPage['name'] }}"
                        wire:change="updateSelectedPageName($event.target.value)"
                        class="max-w-[220px]"
                        size="sm"
                    />
                    <flux:dropdown>
                        <flux:button size="sm" icon="plus" icon:trailing="chevron-down">{{ __('Add Section') }}</flux:button>
                        <flux:menu>
                            @foreach ($this->presets as $type => $presetList)
                                <flux:menu.submenu heading="{{ ucfirst($type) }}">
                                    @foreach ($presetList as $presetKey => $presetLabel)
                                        <flux:menu.item wire:click="addSection('{{ $type }}', '{{ $presetKey }}')">
                                            {{ $presetLabel }}
                                        </flux:menu.item>
                                    @endforeach
                                </flux:menu.submenu>
                            @endforeach
                        </flux:menu>
                    </flux:dropdown>
                </div>

                <div class="flex flex-1 overflow-hidden">
                    <!-- Section list -->
                    <div class="w-64 shrink-0 overflow-y-auto border-e border-zinc-200 p-3 dark:border-zinc-700">
                        <p class="mb-2 text-xs font-medium uppercase tracking-wider text-zinc-400">{{ __('Sections') }}</p>
                        <ul wire:sort="updateSectionOrder" class="space-y-2">
                            @forelse ($this->selectedPage['sections'] as $section)
                                <li
                                    wire:key="section-{{ $section['id'] }}"
                                    wire:sort:item="{{ $section['id'] }}"
                                    class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div wire:sort:handle class="cursor-grab text-zinc-400">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                                            </div>
                                            <span class="text-sm font-medium capitalize text-zinc-900 dark:text-zinc-100">{{ $section['type'] }}</span>
                                        </div>
                                        <div wire:sort:ignore>
                                            <flux:button
                                                variant="ghost"
                                                size="sm"
                                                icon="x-mark"
                                                class="h-6 w-6 p-0"
                                                wire:click="removeSection('{{ $section['id'] }}')"
                                            />
                                        </div>
                                    </div>
                                    <div class="mt-2" wire:sort:ignore>
                                        <flux:select
                                            size="sm"
                                            wire:change="updateSectionPreset('{{ $section['id'] }}', $event.target.value)"
                                        >
                                            @foreach ($this->presets[$section['type']] ?? [] as $presetKey => $presetLabel)
                                                <flux:select.option value="{{ $presetKey }}" :selected="$section['preset'] === $presetKey">
                                                    {{ $presetLabel }}
                                                </flux:select.option>
                                            @endforeach
                                        </flux:select>
                                    </div>
                                </li>
                            @empty
                                <li class="rounded-lg border border-dashed border-zinc-300 p-4 text-center text-sm text-zinc-400 dark:border-zinc-600">
                                    {{ __('No sections yet. Add one above.') }}
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    <!-- Live preview -->
                    <div class="flex-1 bg-zinc-100 p-4 dark:bg-zinc-950">
                        <iframe
                            srcdoc="{{ $this->previewHtml }}"
                            class="h-full w-full rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700"
                            title="{{ __('Page preview') }}"
                        ></iframe>
                    </div>
                </div>
            @else
                <div class="flex flex-1 items-center justify-center text-zinc-400">
                    {{ __('Select or add a page to start editing.') }}
                </div>
            @endif
        </main>

        <!-- Right: Branding -->
        <aside class="w-64 shrink-0 space-y-4 overflow-y-auto border-s border-zinc-200 p-4 dark:border-zinc-700">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Branding') }}</h2>

            <flux:input type="color" label="{{ __('Primary') }}" wire:model.live="branding.primary_color" />
            <flux:input type="color" label="{{ __('Secondary') }}" wire:model.live="branding.secondary_color" />
            <flux:input type="color" label="{{ __('Accent') }}" wire:model.live="branding.accent_color" />
            <flux:input type="color" label="{{ __('Background') }}" wire:model.live="branding.background_color" />
            <flux:input type="color" label="{{ __('Text') }}" wire:model.live="branding.text_color" />

            <flux:select label="{{ __('Font Family') }}" wire:model.live="branding.font_family">
                <flux:select.option value="Inter">Inter</flux:select.option>
                <flux:select.option value="Roboto">Roboto</flux:select.option>
                <flux:select.option value="Poppins">Poppins</flux:select.option>
                <flux:select.option value="Merriweather">Merriweather</flux:select.option>
            </flux:select>
        </aside>
    </div>

    <!-- Add Page Modal -->
    <flux:modal name="addPage" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Add Page') }}</flux:heading>
                <flux:subheading>{{ __('Add a new page to your sitemap.') }}</flux:subheading>
            </div>
            <flux:input label="{{ __('Page name') }}" wire:model="newPageName" placeholder="{{ __('e.g. About Us') }}" />
            <flux:select label="{{ __('Parent page') }}" wire:model="newPageParentId">
                <flux:select.option value="">{{ __('None (top level)') }}</flux:select.option>
                @foreach ($pages as $page)
                    <flux:select.option value="{{ $page['id'] }}">{{ $page['name'] }}</flux:select.option>
                @endforeach
            </flux:select>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="addPage">{{ __('Add Page') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Delete Page Modal -->
    <flux:modal name="confirmDeletePage" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete Page') }}</flux:heading>
                <flux:subheading>{{ __('This will delete the page and all its child pages. This cannot be undone.') }}</flux:subheading>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="deletePage" x-on:click="$flux.modal('confirmDeletePage').close()">
                    {{ __('Delete') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Save Template Modal -->
    @auth
        <flux:modal name="saveTemplate" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Save Template') }}</flux:heading>
                    <flux:subheading>{{ __('Save the current design as a template file.') }}</flux:subheading>
                </div>
                <flux:input label="{{ __('Template name') }}" wire:model="templateName" />
                <flux:input label="{{ __('Filename') }}" wire:model="saveFilename" placeholder="{{ __('e.g. my-template') }}" />
                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button variant="primary" wire:click="saveTemplate">{{ __('Save') }}</flux:button>
                </div>
            </div>
        </flux:modal>
    @endauth
</div>
