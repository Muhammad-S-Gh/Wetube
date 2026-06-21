@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-8 dark:bg-[#0f0f0f]">
    <div class="mx-auto max-w-6xl space-y-8">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818] md:p-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">{{ __('site.explore_heading') }}</h1>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('site.explore_subtitle') }}</p>
                </div>

                @auth
                    <a href="{{ route('videos.create') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700">
                        <i class="fas fa-upload text-xs"></i>
                        {{ __('site.upload_video') }}
                    </a>
                @endauth
            </div>
        </section>

        <!-- Search -->
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
            <form method="GET" action="{{ route('explore') }}" class="flex flex-col gap-3 md:flex-row md:items-end">
                <div class="flex-1">
                    <label for="video-search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('site.search_by_video_name') }}</label>
                    <input
                        id="video-search"
                        name="search"
                        type="search"
                        value="{{ $search ?? '' }}"
                        placeholder="{{ __('site.search_by_video_name') }}"
                        class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 focus:border-red-500 focus:outline-none dark:border-[#333] dark:bg-[#101010] dark:text-gray-200">
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-red-700">
                        <i class="fas fa-search text-xs"></i>
                        {{ __('site.search') }}
                    </button>

                    @if (!empty($search))
                        <a href="{{ route('explore') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-[#333] dark:text-gray-200 dark:hover:bg-[#202020]">
                            {{ __('site.cancel') }}
                        </a>
                    @endif
                </div>
            </form>
        </section>

        @if ($videos->isEmpty())
            <section class="rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center dark:border-[#303030] dark:bg-[#141414]">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-600 dark:bg-[#202020] dark:text-gray-300">
                    <i class="fas fa-compass"></i>
                </div>
                <h2 class="text-xl font-semibold">{{ __('site.explore_empty_title') }}</h2>
                <p class="mx-auto mt-2 max-w-lg text-sm text-gray-500 dark:text-gray-400">{{ __('site.explore_empty_description') }}</p>
            </section>
        @else
            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($videos as $video)
                    @php
                        $duration = sprintf(
                            '%02d:%02d:%02d',
                            (int) ($video->hours ?? 0),
                            (int) ($video->minutes ?? 0),
                            (int) ($video->seconds ?? 0)
                        );
                    @endphp

                    <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-[#262626] dark:bg-[#181818]">
                        <div class="relative aspect-video bg-gray-100 dark:bg-[#111]">
                            @if ($video->thumbnailDisplayUrl())
                                <img src="{{ $video->thumbnailDisplayUrl() }}" alt="{{ $video->title }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-gray-400">
                                    <i class="fas fa-photo-film text-2xl"></i>
                                </div>
                            @endif

                            <span class="absolute bottom-3 right-3 rounded-md bg-black/75 px-2 py-1 text-xs font-medium text-white">
                                {{ $duration }}
                            </span>
                        </div>

                        <div class="space-y-3 p-4">
                            <h3 class="line-clamp-2 text-sm font-semibold leading-6 md:text-base">{{ $video->title }}</h3>

                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('site.creator') }}: <span class="font-medium text-gray-700 dark:text-gray-200">{{ $video->user?->name ?? '-' }}</span>
                            </p>

                            <div class="grid grid-cols-3 gap-2 text-center text-xs">
                                <div class="rounded-lg border border-gray-200 bg-gray-50 px-2 py-2 dark:border-[#2e2e2e] dark:bg-[#121212]">
                                    <p class="text-gray-500 dark:text-gray-400">{{ __('site.views') }}</p>
                                    <p class="mt-1 font-semibold">{{ $video->viewers_count }}</p>
                                </div>
                                <div class="rounded-lg border border-gray-200 bg-gray-50 px-2 py-2 dark:border-[#2e2e2e] dark:bg-[#121212]">
                                    <p class="text-gray-500 dark:text-gray-400">{{ __('site.likes') }}</p>
                                    <p class="mt-1 font-semibold">{{ $video->likes_count }}</p>
                                </div>
                                <div class="rounded-lg border border-gray-200 bg-gray-50 px-2 py-2 dark:border-[#2e2e2e] dark:bg-[#121212]">
                                    <p class="text-gray-500 dark:text-gray-400">{{ __('site.comments') }}</p>
                                    <p class="mt-1 font-semibold">{{ $video->comments_count }}</p>
                                </div>
                            </div>

                            <a href="{{ route('videos.show', $video) }}"
                                class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:border-red-500 hover:text-red-600 dark:border-[#333] dark:text-gray-200 dark:hover:border-red-600 dark:hover:text-red-400">
                                <i class="fas fa-eye"></i>
                                {{ __('site.watch') }}
                            </a>
                        </div>
                    </article>
                @endforeach
            </section>

            <div class="pt-2">
                {{ $videos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
