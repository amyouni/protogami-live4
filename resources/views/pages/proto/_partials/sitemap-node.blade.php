<li
    wire:key="page-{{ $node['id'] }}"
    wire:sort:item="{{ $node['id'] }}"
    x-data="{ expanded: true }"
>
    <div
        @class([
            'group flex items-center gap-1 rounded-md px-2 py-1.5',
            'bg-blue-50 dark:bg-blue-950' => $selectedPageId === $node['id'],
            'hover:bg-zinc-100 dark:hover:bg-zinc-800' => $selectedPageId !== $node['id'],
        ])
    >
        <div wire:sort:handle class="cursor-grab text-zinc-300 dark:text-zinc-600">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
        </div>

        @if (count($node['children']) > 0)
            <button type="button" x-on:click="expanded = !expanded" class="text-zinc-400" wire:sort:ignore>
                <svg class="h-3.5 w-3.5 transition-transform" x-bind:class="expanded ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 5l7 7-7 7" />
                </svg>
            </button>
        @else
            <span class="w-3.5"></span>
        @endif

        <button
            type="button"
            wire:click="selectPage('{{ $node['id'] }}')"
            class="flex-1 truncate text-start text-sm text-zinc-700 dark:text-zinc-200"
            wire:sort:ignore
        >
            {{ $node['name'] }}
        </button>

        <div wire:sort:ignore class="opacity-0 group-hover:opacity-100">
            <flux:button
                variant="ghost"
                size="sm"
                icon="trash"
                class="h-6 w-6 p-0 text-red-400"
                wire:click="$set('deletingPageId', '{{ $node['id'] }}')"
                x-on:click="$flux.modal('confirmDeletePage').show()"
            />
        </div>
    </div>

    <ul
        x-show="expanded"
        wire:sort="updatePageOrder"
        wire:sort:group="pages"
        wire:sort:group-id="{{ $node['id'] }}"
        class="ms-5 min-h-2 space-y-0.5 border-s border-zinc-200 ps-2 dark:border-zinc-700"
    >
        @foreach ($node['children'] as $child)
            @include('pages.proto._partials.sitemap-node', ['node' => $child, 'depth' => ($depth ?? 0) + 1])
        @endforeach
    </ul>
</li>
