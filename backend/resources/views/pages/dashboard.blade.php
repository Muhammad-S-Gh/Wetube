@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-8 dark:bg-[#0f0f0f]">
    <div class="mx-auto max-w-6xl space-y-8">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818] md:p-8">
            <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">{{ __('site.dashboard_heading') }}</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('site.dashboard_subtitle') }}</p>

            <div class="mt-5 grid gap-3 md:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-[#2e2e2e] dark:bg-[#121212]">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('site.all_videos') }}</p>
                    <p class="mt-1 text-2xl font-semibold">{{ $stats['total'] }}</p>
                </div>

                <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900/40 dark:bg-blue-900/10">
                    <p class="text-xs uppercase tracking-wide text-blue-700 dark:text-blue-300">{{ __('site.processing_videos') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-blue-700 dark:text-blue-200">{{ $stats['processing'] }}</p>
                </div>

                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/40 dark:bg-emerald-900/10">
                    <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">{{ __('site.completed_videos') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-emerald-700 dark:text-emerald-200">{{ $stats['completed'] }}</p>
                </div>

                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-900/40 dark:bg-rose-900/10">
                    <p class="text-xs uppercase tracking-wide text-rose-700 dark:text-rose-300">{{ __('site.failed_videos') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-rose-700 dark:text-rose-200">{{ $stats['failed'] }}</p>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <a href="{{ route('videos.create') }}" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-[#262626] dark:bg-[#181818]">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-600 dark:bg-red-900/20 dark:text-red-300">
                        <i class="fas fa-upload"></i>
                    </span>
                    <div>
                        <h2 class="font-semibold">{{ __('site.upload_video') }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('site.upload_video_hint') }}</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('videos.index') }}" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-[#262626] dark:bg-[#181818]">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-300">
                        <i class="fas fa-film"></i>
                    </span>
                    <div>
                        <h2 class="font-semibold">{{ __('site.my_videos') }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('site.manage_videos_hint') }}</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('explore') }}" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-[#262626] dark:bg-[#181818]">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/20 dark:text-amber-300">
                        <i class="fas fa-compass"></i>
                    </span>
                    <div>
                        <h2 class="font-semibold">{{ __('site.explore') }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('site.explore_hint') }}</p>
                    </div>
                </div>
            </a>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818] md:p-8">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold">{{ __('site.my_latest_uploads') }}</h2>
                <a href="{{ route('videos.index') }}" class="text-sm font-medium text-red-600 hover:text-red-700">
                    {{ __('site.see_all_videos') }}
                </a>
            </div>

            @if ($recentVideos->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('site.no_videos_yet_description') }}</p>
            @else
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($recentVideos as $video)
                        @php $thumbnailUrl = $video->thumbnailDisplayUrl(); @endphp

                        <article class="overflow-hidden rounded-xl border border-gray-200 dark:border-[#2e2e2e]">
                            <div class="aspect-video bg-gray-100 dark:bg-[#111]">
                                @if ($video->thumbnailDisplayUrl())
                                    <img src="{{ $video->thumbnailDisplayUrl() }}" alt="{{ $video->title }}" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="space-y-2 p-3">
                                <h3 class="line-clamp-2 text-sm font-medium">{{ $video->title }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ optional($video->created_at)->diffForHumans() }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
