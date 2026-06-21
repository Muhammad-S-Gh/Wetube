@extends('layouts.main')

@section('content')
@php
    $historyCollection = $history->getCollection();
    $watchedCount = $history->total();
    $latestWatched = $historyCollection->first();
    $creatorsCount = $historyCollection->pluck('user_id')->unique()->count();
@endphp

<div class="min-h-screen bg-gray-50 px-4 py-8 dark:bg-[#0f0f0f]">
    <div class="mx-auto max-w-6xl space-y-8">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818] md:p-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">{{ __('site.watch_history_heading') }}</h1>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('site.watch_history_subtitle') }}</p>
                </div>

                <a href="{{ route('explore') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-red-500 hover:text-red-600 dark:border-[#333] dark:text-gray-200 dark:hover:border-red-600 dark:hover:text-red-400">
                    <i class="fas fa-compass text-xs"></i>
                    {{ __('site.explore') }}
                </a>
            </div>

            <div class="mt-5 grid gap-3 md:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-[#2e2e2e] dark:bg-[#121212]">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('site.watch_history_total') }}</p>
                    <p class="mt-1 text-2xl font-semibold">{{ $watchedCount }}</p>
                </div>

                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900/40 dark:bg-blue-900/10">
                    <p class="text-xs uppercase tracking-wide text-blue-700 dark:text-blue-300">{{ __('site.watch_history_last_watched') }}</p>
                    <p class="mt-1 text-sm font-medium text-blue-700 dark:text-blue-200">{{ $latestWatched ? optional($latestWatched->updated_at)->diffForHumans() : __('site.watch_history_none') }}</p>
                </div>

                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/40 dark:bg-emerald-900/10">
                    <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">{{ __('site.watch_history_creators') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-emerald-700 dark:text-emerald-200">{{ $creatorsCount }}</p>
                </div>
            </div>
        </section>

        @if ($historyCollection->isEmpty())
            <section class="rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center dark:border-[#303030] dark:bg-[#141414]">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-600 dark:bg-[#202020] dark:text-gray-300">
                    <i class="fas fa-clock-rotate-left"></i>
                </div>
                <h2 class="text-xl font-semibold">{{ __('site.watch_history_empty_title') }}</h2>
                <p class="mx-auto mt-2 max-w-lg text-sm text-gray-500 dark:text-gray-400">{{ __('site.watch_history_empty_description') }}</p>
            </section>
        @else
            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($historyCollection as $video)
                    @php
                        $thumbnailUrl = $video->thumbnailDisplayUrl();
                        $duration = sprintf(
                            '%02d:%02d:%02d',
                            (int) ($video->hours ?? 0),
                            (int) ($video->minutes ?? 0),
                            (int) ($video->seconds ?? 0)
                        );
                    @endphp

                    <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-[#262626] dark:bg-[#181818]">
                        <div class="relative aspect-video bg-gray-100 dark:bg-[#111]">
                            @if ($thumbnailUrl)
                                <img src="{{ $thumbnailUrl }}" alt="{{ $video->title }}" class="h-full w-full object-cover">
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

                            <div class="flex items-center gap-3">
                                <img src="{{ $video->user?->profile_photo_url }}" alt="{{ $video->user?->name ?? '' }}" class="h-9 w-9 rounded-full object-cover">
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('site.creator') }}</p>
                                    <p class="truncate text-sm font-medium text-gray-700 dark:text-gray-200">{{ $video->user?->name ?? '-' }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 text-center text-xs">
                                <div class="rounded-lg border border-gray-200 bg-gray-50 px-2 py-2 dark:border-[#2e2e2e] dark:bg-[#121212]">
                                    <p class="text-gray-500 dark:text-gray-400">{{ __('site.views') }}</p>
                                    <p class="mt-1 font-semibold">{{ $video->views_count }}</p>
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

                            <div class="flex items-center justify-between gap-2 pt-1">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('site.watched') }} {{ optional($video->pivot?->updated_at)->diffForHumans() }}
                                </p>

                                <a href="{{ route('videos.show', $video) }}"
                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:border-red-500 hover:text-red-600 dark:border-[#333] dark:text-gray-200 dark:hover:border-red-600 dark:hover:text-red-400">
                                    <i class="fas fa-eye"></i>
                                    {{ __('site.watch') }}
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <div class="pt-2">
                {{ $history->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
