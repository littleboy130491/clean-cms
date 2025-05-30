<x-layouts.app :title="$content->title ?? 'Default Page'" :body-classes="$bodyClasses">
    <x-partials.header />
    <main>
        test
        <h1>{{ $content->title ?? 'Default Page' }}</h1>
        <x-instagram-feed :type="request('type', 'all')" />

        {{-- Content goes here --}}

    </main>
    <x-partials.footer />
</x-layouts.app>
