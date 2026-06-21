@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-8 dark:bg-[#0f0f0f]">
    <div class="mx-auto max-w-6xl space-y-8">
        <!-- Channel Header -->
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818] md:p-8">
            <div class="flex flex-col items-start gap-6 md:flex-row md:items-end md:justify-between">
                <div class="flex items-start gap-4">
                    <img
                        src="{{ $user->profile_photo_url }}"
                        alt="{{ $user->name }}"
                        class="h-20 w-20 rounded-full border-2 border-gray-200 object-cover dark:border-[#333]">

                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">{{ $user->name }}'s Channel</h1>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('site.joined_on') }} {{ $user->created_at->format('Y-m-d') }}
                        </p>
                    </div>
                </div>

                @if ($videos->count() === 0)
                    <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-600 dark:border-[#303030] dark:bg-[#202020] dark:text-gray-300">
                        <i class="fas fa-info-circle"></i>
                        {{ __('site.no_videos_yet') }}
                    </div>
                @else
                    <div class="flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-900/10 dark:text-emerald-300">
                        <i class="fas fa-check-circle"></i>
                        {{ $videos->total() }} {{ __('site.completed_videos') }}
                    </div>
                @endif
            </div>
        </section>

        <!-- Videos Grid -->
        @if ($videos->isEmpty())
            <section class="rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center dark:border-[#303030] dark:bg-[#141414]">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-600 dark:bg-[#202020] dark:text-gray-300">
                    <i class="fas fa-video"></i>
                </div>
                <h2 class="text-xl font-semibold">{{ __('site.no_videos_available') }}</h2>
                <p class="mx-auto mt-2 max-w-lg text-sm text-gray-500 dark:text-gray-400">
                    {{ $user->name }} hasn't uploaded any videos yet.
                </p>
            </section>
        @else
            <section class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($videos as $video)
                    @php
                        $thumbnailUrl = $video->thumbnailDisplayUrl();
                    @endphp

                    <a href="{{ route('videos.show', $video->id) }}" class="group">
                        <article class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-[#262626] dark:bg-[#181818]">
                            <div class="relative aspect-video bg-gray-100 dark:bg-[#111]">
                                @if ($thumbnailUrl)
                                    <img src="{{ $thumbnailUrl }}" alt="{{ $video->title }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-gray-400">
                                        <i class="fas fa-image text-2xl"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-3 p-3">
                                <h3 class="line-clamp-2 text-sm font-medium group-hover:text-red-600 dark:group-hover:text-red-500">
                                    {{ $video->title }}
                                </h3>

                                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $video->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </article>
                    </a>
                @endforeach
            </section>

            <!-- Pagination -->
            <div class="pt-2">
                {{ $videos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
