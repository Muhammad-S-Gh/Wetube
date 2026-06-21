@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-8 dark:bg-[#0f0f0f]">
    <div class="mx-auto max-w-6xl space-y-8">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818] md:p-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">{{ __('site.channels_heading') }}</h1>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('site.channels_subtitle') }}</p>
                </div>

                <a href="{{ route('videos.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700">
                    <i class="fas fa-upload text-xs"></i>
                    {{ __('site.upload_new_video') }}
                </a>
            </div>
        </section>

        <!-- Search -->
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
            <form method="GET" action="{{ route('channels.index') }}" class="flex flex-col gap-3 md:flex-row md:items-end">
                <div class="flex-1">
                    <label for="channel-search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('site.search_by_user_name') }}</label>
                    <input
                        id="channel-search"
                        name="search"
                        type="search"
                        value="{{ $search ?? '' }}"
                        placeholder="{{ __('site.search_by_user_name') }}"
                        class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 focus:border-red-500 focus:outline-none dark:border-[#333] dark:bg-[#101010] dark:text-gray-200">
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-red-700">
                        <i class="fas fa-search text-xs"></i>
                        {{ __('site.search') }}
                    </button>

                    @if (!empty($search))
                        <a href="{{ route('channels.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-[#333] dark:text-gray-200 dark:hover:bg-[#202020]">
                            {{ __('site.cancel') }}
                        </a>
                    @endif
                </div>
            </form>
        </section>

        @if ($channels->isEmpty())
            <section class="rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center dark:border-[#303030] dark:bg-[#141414]">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-600 dark:bg-[#202020] dark:text-gray-300">
                    <i class="fas fa-film"></i>
                </div>
                <h2 class="text-xl font-semibold">{{ __('site.channels_empty_title') }}</h2>
                <p class="mx-auto mt-2 max-w-lg text-sm text-gray-500 dark:text-gray-400">{{ __('site.channels_empty_description') }}</p>
            </section>
        @else
            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($channels as $channel)
                    @php
                        // We intentionally loaded one latest completed video in controller.
                        // It is used here as a visual preview for each channel card.
                        $latestVideo = $channel->videos->first();
                        $latestThumbnailUrl = $latestVideo?->thumbnailDisplayUrl();
                    @endphp

                    <a href="{{ route('channels.show', $channel->id) }}" class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-[#262626] dark:bg-[#181818]">
                        <article>
                            <div class="relative aspect-video bg-gray-100 dark:bg-[#111]">
                                @if ($latestThumbnailUrl)
                                    <img src="{{ $latestThumbnailUrl }}" alt="{{ $channel->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-gray-400">
                                        <i class="fas fa-photo-film text-2xl"></i>
                                    </div>
                                @endif

                                @if ($latestVideo)
                                    <span class="absolute bottom-3 left-3 rounded-md bg-black/75 px-2 py-1 text-xs font-medium text-white">
                                        {{ __('site.latest_video') }}: {{ \Illuminate\Support\Str::limit($latestVideo->title, 24) }}
                                    </span>
                                @endif
                            </div>

                            <div class="space-y-4 p-4">
                                <div class="flex items-center gap-3">
                                    <img
                                        src="{{ $channel->profile_photo_url }}"
                                        alt="{{ $channel->name }}"
                                    class="h-11 w-11 rounded-full border border-gray-200 object-cover dark:border-[#333]">

                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-semibold md:text-base">
                                        {{ $channel->name }}
                                        @if ($currentUserId === $channel->id)
                                            <span class="ml-1 rounded-md bg-red-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-red-700 dark:bg-red-900/30 dark:text-red-300">{{ __('site.you') }}</span>
                                        @endif
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ __('site.joined_on') }} {{ optional($channel->created_at)->format('Y-m-d') }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-2 text-xs">
                                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 dark:border-emerald-900/40 dark:bg-emerald-900/10">
                                    <p class="text-emerald-700 dark:text-emerald-300">{{ __('site.completed_videos') }}</p>
                                    <p class="mt-1 text-base font-semibold text-emerald-700 dark:text-emerald-200">{{ $channel->videos_count }}</p>
                                </div>
                            </div>
                        </div>
                        </article>
                    </a>
                @endforeach
            </section>

            <div class="pt-2">
                {{ $channels->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
