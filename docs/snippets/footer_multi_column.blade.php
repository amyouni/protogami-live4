<footer class="border-t border-black/10 py-12" style="background-color: var(--brand-bg)">
    <div class="mx-auto grid max-w-7xl grid-cols-2 gap-8 px-6 sm:grid-cols-4">
        <div>
            <span class="text-lg font-bold" style="color: var(--brand-primary)">Brand</span>
            <p class="mt-3 text-sm opacity-70" style="color: var(--brand-text)">Building great experiences since 2026.</p>
        </div>
        @php
            $navChunks = array_chunk($navItems ?? [], max(1, ceil(count($navItems ?? []) / 3)));
        @endphp
        @foreach ($navChunks as $chunk)
            <div>
                <h4 class="text-sm font-semibold" style="color: var(--brand-text)">Pages</h4>
                <ul class="mt-3 space-y-2 text-sm opacity-70" style="color: var(--brand-text)">
                    @foreach ($chunk as $item)
                        <li><a href="#" data-page="{{ $item['id'] }}" class="hover:opacity-70">{{ $item['name'] }}</a></li>
                        @foreach ($item['children'] as $child)
                            <li class="ps-3"><a href="#" data-page="{{ $child['id'] }}" class="hover:opacity-70">{{ $child['name'] }}</a></li>
                        @endforeach
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</footer>
