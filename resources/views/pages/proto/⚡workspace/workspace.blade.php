<div
    x-data="{}"
    class="flex h-screen flex-col bg-zinc-50 dark:bg-zinc-950"
>
    <!-- Top App Bar -->
    <header class="flex items-center justify-between gap-4 border-b border-zinc-200 bg-white px-4 py-2.5 dark:border-zinc-700 dark:bg-zinc-900">
        <!-- Left: Logo + Template -->
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-zinc-900 text-white dark:bg-white dark:text-zinc-900">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1" />
                        <rect x="14" y="3" width="7" height="7" rx="1" />
                        <rect x="3" y="14" width="7" height="7" rx="1" />
                        <rect x="14" y="14" width="7" height="7" rx="1" />
                    </svg>
                </div>
            </a>
            <flux:select wire:model.live="currentTemplate" placeholder="{{ __('Choose template...') }}" class="min-w-[160px]" size="sm">
                @foreach ($this->templates as $template)
                    <flux:select.option value="{{ $template['filename'] }}">{{ $template['name'] }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <!-- Center: Page name -->
        <div class="flex items-center gap-2">
            @if ($this->selectedPage)
                <flux:input
                    value="{{ $this->selectedPage['name'] }}"
                    wire:change="updateSelectedPageName($event.target.value)"
                    class="max-w-[200px]"
                    size="sm"
                />
            @else
                <span class="text-sm text-zinc-400">{{ __('No page selected') }}</span>
            @endif
        </div>

        <!-- Right: Actions + Panel toggles -->
        <div class="flex items-center gap-1.5">
            <flux:button variant="ghost" size="sm" icon="arrow-down-tray" wire:click="export">
                {{ __('Export') }}
            </flux:button>
            @auth
                <flux:modal.trigger name="saveTemplate">
                    <flux:button variant="primary" size="sm" icon="bookmark">{{ __('Save') }}</flux:button>
                </flux:modal.trigger>
            @else
                <flux:tooltip content="{{ __('Sign in to save templates') }}">
                    <flux:button variant="ghost" size="sm" href="{{ route('login') }}" wire:navigate icon="lock-closed">
                        {{ __('Sign in') }}
                    </flux:button>
                </flux:tooltip>
            @endauth

            <div class="mx-1 h-5 w-px bg-zinc-200 dark:bg-zinc-700"></div>

            <!-- Panel toggle buttons -->
            <flux:tooltip content="{{ __('Toggle Sitemap') }}">
                <flux:button
                    variant="ghost"
                    size="sm"
                    icon="map"
                    wire:click="togglePanel('sitemap')"
                    :class="$showSitemap ? 'text-blue-600 dark:text-blue-400' : ''"
                />
            </flux:tooltip>
            <flux:tooltip content="{{ __('Toggle Sections') }}">
                <flux:button
                    variant="ghost"
                    size="sm"
                    icon="squares-2x2"
                    wire:click="togglePanel('sections')"
                    :class="$showSections ? 'text-blue-600 dark:text-blue-400' : ''"
                />
            </flux:tooltip>
            <flux:tooltip content="{{ __('Toggle Branding') }}">
                <flux:button
                    variant="ghost"
                    size="sm"
                    icon="swatch"
                    wire:click="togglePanel('branding')"
                    :class="$showBranding ? 'text-blue-600 dark:text-blue-400' : ''"
                />
            </flux:tooltip>
        </div>
    </header>

    <!-- Main Stage -->
    <div class="flex flex-1 overflow-hidden">
        <!-- Left: Stacked Sitemap + Sections -->
        @if ($showSitemap || $showSections)
            <div class="flex w-64 shrink-0 flex-col border-e border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Sitemap Panel -->
                @if ($showSitemap)
                    <div class="flex flex-col {{ $showSections ? 'h-1/2' : 'flex-1' }} overflow-hidden">
                        <div class="flex items-center justify-between border-b border-zinc-200 px-3 py-2.5 dark:border-zinc-700">
                            <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Sitemap') }}</h2>
                            <div class="flex items-center gap-1">
                                <flux:modal.trigger name="addPage">
                                    <flux:button variant="ghost" size="sm" icon="plus" class="h-6 w-6 p-0" />
                                </flux:modal.trigger>
                                <flux:button variant="ghost" size="sm" icon="chevron-left" class="h-6 w-6 p-0" wire:click="togglePanel('sitemap')" />
                            </div>
                        </div>
                        <div class="flex-1 overflow-y-auto px-2 py-2">
                            <ul wire:sort="updatePageOrder" wire:sort:group="pages" wire:sort:group-id="" class="min-h-4 space-y-0.5">
                                @foreach ($this->pageTree as $node)
                                    @include('pages.proto._partials.sitemap-node', ['node' => $node, 'depth' => 0])
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Sections Panel -->
                @if ($showSections)
                    <div class="flex flex-1 flex-col overflow-hidden {{ $showSitemap ? 'border-t border-zinc-200 dark:border-zinc-700' : '' }}">
                        <div class="flex items-center justify-between border-b border-zinc-200 px-3 py-2.5 dark:border-zinc-700">
                            <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Sections') }}</h2>
                            <div class="flex items-center gap-1">
                                @if ($this->selectedPage)
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="plus" class="h-6 w-6 p-0" />
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
                                @endif
                                <flux:button variant="ghost" size="sm" icon="chevron-left" class="h-6 w-6 p-0" wire:click="togglePanel('sections')" />
                            </div>
                        </div>
                        <div class="flex-1 overflow-y-auto p-2">
                            @if ($this->selectedPage)
                                <ul wire:sort="updateSectionOrder" class="space-y-2">
                                    @forelse ($this->selectedPage['sections'] as $section)
                                        <li
                                            wire:key="section-{{ $section['id'] }}"
                                            wire:sort:item="{{ $section['id'] }}"
                                            class="rounded-lg border border-zinc-200 bg-zinc-50 p-2.5 dark:border-zinc-700 dark:bg-zinc-800"
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
                                            {{ __('No sections yet.') }}
                                        </li>
                                    @endforelse
                                </ul>
                            @else
                                <p class="py-8 text-center text-sm text-zinc-400">{{ __('Select a page first.') }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Center: Page tabs + Preview Canvas -->
        <main class="flex flex-1 flex-col overflow-hidden">
            @if ($this->selectedPage)
                <!-- Page tabs -->
                <div class="flex items-center gap-1 border-b border-zinc-200 bg-zinc-100 px-2 py-1.5 dark:border-zinc-700 dark:bg-zinc-900">
                    @foreach ($this->pageTree as $node)
                        <button
                            type="button"
                            wire:key="tab-{{ $node['id'] }}"
                            wire:click="selectPage('{{ $node['id'] }}')"
                            @class([
                                'rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
                                'bg-white text-zinc-900 shadow-sm dark:bg-zinc-800 dark:text-zinc-100' => $selectedPageId === $node['id'],
                                'text-zinc-500 hover:bg-white/60 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800/60' => $selectedPageId !== $node['id'],
                            ])
                        >
                            {{ $node['name'] }}
                        </button>
                    @endforeach
                </div>

                <!-- Preview canvas -->
                <div class="flex-1 bg-zinc-200 p-4 dark:bg-zinc-950">
                    <iframe
                        x-data="{ scrollPos: 0 }"
                        x-on:load="
                            $el.contentWindow.scrollTo(0, scrollPos);
                            setTimeout(() => $el.contentWindow.scrollTo(0, scrollPos), 150);
                            $el.contentWindow.addEventListener('scroll', () => scrollPos = $el.contentWindow.scrollY);
                        "
                        srcdoc="{{ $this->previewHtml }}"
                        class="mx-auto h-full w-full max-w-5xl rounded-lg border border-zinc-300 bg-white shadow-lg dark:border-zinc-700"
                        title="{{ __('Page preview') }}"
                    ></iframe>
                </div>
            @else
                <div class="flex flex-1 items-center justify-center text-zinc-400">
                    {{ __('Select or add a page to start editing.') }}
                </div>
            @endif
        </main>

        <!-- Right: Branding -->
        @if ($showBranding)
            <aside class="flex w-60 shrink-0 flex-col border-s border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-200 px-3 py-2.5 dark:border-zinc-700">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Branding') }}</h2>
                    <flux:button variant="ghost" size="sm" icon="chevron-right" class="h-6 w-6 p-0" wire:click="togglePanel('branding')" />
                </div>

                <div class="flex-1 space-y-4 overflow-y-auto p-3">
                    <flux:select wire:model.live="currentBrandingPreset" placeholder="{{ __('Branding preset...') }}" size="sm">
                        @foreach ($this->brandingPresets as $preset)
                            <flux:select.option value="{{ $preset['filename'] }}">{{ $preset['name'] }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    @auth
                        <flux:modal.trigger name="saveBranding">
                            <flux:button variant="ghost" size="sm" icon="bookmark" class="w-full">{{ __('Save Branding') }}</flux:button>
                        </flux:modal.trigger>
                    @endauth

                    <flux:input type="color" label="{{ __('Primary') }}" wire:model.live="branding.primary_color" size="sm" />
                    <flux:input type="color" label="{{ __('Secondary') }}" wire:model.live="branding.secondary_color" size="sm" />
                    <flux:input type="color" label="{{ __('Accent') }}" wire:model.live="branding.accent_color" size="sm" />
                    <flux:input type="color" label="{{ __('Background') }}" wire:model.live="branding.background_color" size="sm" />
                    <flux:input type="color" label="{{ __('Text') }}" wire:model.live="branding.text_color" size="sm" />

                    <flux:select label="{{ __('Font Family') }}" wire:model.live="branding.font_family" size="sm">
                        <flux:select.option value="Inter">Inter</flux:select.option>
                        <flux:select.option value="Roboto">Roboto</flux:select.option>
                        <flux:select.option value="Poppins">Poppins</flux:select.option>
                        <flux:select.option value="Merriweather">Merriweather</flux:select.option>
                    </flux:select>
                </div>
            </aside>
        @endif
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

    <!-- Save Branding Modal -->
    @auth
        <flux:modal name="saveBranding" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Save Branding Preset') }}</flux:heading>
                    <flux:subheading>{{ __('Save the current colors and font as a reusable preset.') }}</flux:subheading>
                </div>
                <flux:input label="{{ __('Preset name') }}" wire:model="saveBrandingName" placeholder="{{ __('e.g. Ocean Blue') }}" />
                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button variant="primary" wire:click="saveBranding">{{ __('Save') }}</flux:button>
                </div>
            </div>
        </flux:modal>
    @endauth
</div>
