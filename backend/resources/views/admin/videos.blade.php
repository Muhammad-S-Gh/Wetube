@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-8 dark:bg-[#0f0f0f]">
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ __('site.admin_video_management_title') }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('site.admin_video_management_subtitle') }}</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-300 dark:bg-[#303030] dark:hover:bg-[#404040]">
                    <i class="fas fa-arrow-left"></i>
                    {{ __('site.back') }}
                </a>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
            <form method="GET" action="{{ route('admin.videos') }}" class="flex flex-col gap-3 md:flex-row md:items-end">
                <div class="flex-1">
                    <label for="video-search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('site.search') }}</label>
                    <input
                        id="video-search"
                        name="search"
                        type="search"
                        value="{{ $search ?? '' }}"
                        placeholder="{{ __('site.search') }}"
                        class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 focus:border-red-500 focus:outline-none dark:border-[#333] dark:bg-[#101010] dark:text-gray-200">
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-red-700">
                        <i class="fas fa-search text-xs"></i>
                        {{ __('site.search') }}
                    </button>

                    @if (!empty($search))
                        <a href="{{ route('admin.videos') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-[#333] dark:text-gray-200 dark:hover:bg-[#202020]">
                            {{ __('site.cancel') }}
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <!-- Videos Grid -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($videos as $video)
                @php
                    $videoStatusValue = $video->title === 'BLOCKED' ? 'blocked' : $video->processed->value;

                    $videoStatusStyle = match ($videoStatusValue) {
                        'blocked' => 'background-color: #dc2626; color: #ffffff;',
                        'completed' => 'background-color: #059669; color: #ffffff;',
                        'processing' => 'background-color: #d97706; color: #ffffff;',
                        default => 'background-color: #4b5563; color: #ffffff;',
                    };

                    $videoStatusLabel = match ($videoStatusValue) {
                        'blocked' => __('site.admin_blocked'),
                        'completed' => __('site.status_completed'),
                        'processing' => __('site.status_processing'),
                        default => __('site.status_failed'),
                    };
                @endphp
                <div class="rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md dark:border-[#262626] dark:bg-[#181818]">
                    <!-- Video Thumbnail -->
                    <div class="relative h-40 bg-gray-900">
                        @if($video->thumbnailDisplayUrl())
                            <img src="{{ $video->thumbnailDisplayUrl() }}" alt="{{ $video->title }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center bg-gray-800">
                                <i class="fas fa-video text-4xl text-gray-600"></i>
                            </div>
                        @endif

                        <!-- Status Badge -->
                        <div class="absolute right-2 top-2">
                            <span class="inline-block rounded-full px-2 py-1 text-xs font-semibold" style="{{ $videoStatusStyle }}">
                                {{ $videoStatusLabel }}
                            </span>
                        </div>

                        <!-- Duration -->
                        @if($video->hours || $video->minutes || $video->seconds)
                            <div class="absolute bottom-2 right-2 rounded bg-black/70 px-2 py-1 text-xs font-semibold text-white">
                                {{ sprintf('%02d:%02d:%02d', $video->hours, $video->minutes, $video->seconds) }}
                            </div>
                        @endif
                    </div>

                    <!-- Video Info -->
                    <div class="space-y-4 p-4">
                        <!-- Title & User -->
                        <div>
                            <h3 class="font-semibold line-clamp-2">{{ $video->title }}</h3>
                            <p class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mt-1">
                                <img src="{{ $video->user->profile_photo_url }}" alt="{{ $video->user->name }}" class="h-5 w-5 rounded-full">
                                {{ $video->user->name }}
                            </p>
                        </div>

                        <!-- Metadata -->
                        <div class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
                            <p>
                                <i class="fas fa-calendar text-gray-500"></i>
                                {{ $video->created_at->format('M d, Y') }}
                            </p>
                            @if($video->media?->count() > 0)
                                <p>
                                    <i class="fas fa-film text-gray-500"></i>
                                    {{ __('site.admin_media_files_count', ['count' => $video->media->count()]) }}
                                </p>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-200 dark:border-[#303030]">
                            <!-- View Button -->
                            <a href="{{ route('admin.videos.show', $video) }}" class="flex-1 inline-flex items-center justify-center gap-1 rounded-lg bg-blue-100 px-3 py-2 text-xs font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/40">
                                <i class="fas fa-eye"></i>
                                {{ __('site.watch') }}
                            </a>

                            @if($video->title === 'BLOCKED')
                                <!-- Block/unblock actions removed -->
                            @endif

                            <!-- Delete Button -->
                            <form method="POST" action="{{ route('admin.videos.delete', $video) }}" style="flex: 1;">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-1 rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-900/40" onclick="return confirm(@json(__('site.admin_confirm_delete_video')))">
                                    <i class="fas fa-trash"></i>
                                    {{ __('site.delete') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-gray-200 bg-gray-50 py-12 text-center dark:border-[#303030] dark:bg-[#181818]">
                    <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('site.admin_no_videos_found') }}</p>
                </div>
            @endforelse
        </section>

        <!-- Pagination -->
        @if ($videos->hasPages())
            <div class="flex justify-center">
                {{ $videos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
