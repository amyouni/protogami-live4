<nav class="border-b border-black/10" style="background-color: var(--brand-bg)">
    <div class="mx-auto flex max-w-7xl items-center gap-8 px-6 py-4">
        <a href="#" data-page="home" class="text-xl font-bold" style="color: var(--brand-primary)">Brand</a>
        <div class="flex items-center gap-6 text-sm font-medium" style="color: var(--brand-text)">
            @foreach ($navItems ?? [] as $item)
                @if (count($item['children']) > 0)
                    <div class="group relative">
                        <a href="#" data-page="{{ $item['id'] }}" class="hover:opacity-70">{{ $item['name'] }} ▾</a>
                        <div class="absolute left-0 top-full z-10 hidden min-w-36 rounded-md border border-black/10 py-1 shadow-lg group-hover:block" style="background-color: var(--brand-bg)">
                            @foreach ($item['children'] as $child)
                                <a href="#" data-page="{{ $child['id'] }}" class="block px-4 py-1.5 hover:opacity-70">{{ $child['name'] }}</a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="#" data-page="{{ $item['id'] }}" class="hover:opacity-70">{{ $item['name'] }}</a>
                @endif
            @endforeach
        </div>
    </div>
</nav>
