 @extends('layouts.main')

@section('content')
@php
    $videoCollection = collect($videos ?? []);

    if ($videos instanceof \Illuminate\Pagination\AbstractPaginator) {
        $videoCollection = $videos->getCollection();
    }

    $allVideosCount = $videos instanceof \Illuminate\Pagination\LengthAwarePaginator ? $videos->total() : $videoCollection->count();
    $hasProcessingVideo = (bool) ($hasProcessingVideo ?? false);
    $completedCount = $videoCollection->where('processed.value', 'completed')->count();
    $failedCount = $videoCollection->where('processed.value', 'failed')->count();
@endphp

<div class="min-h-screen bg-gray-50 px-4 py-8 dark:bg-[#0f0f0f]">
    <div class="mx-auto max-w-6xl space-y-8">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818] md:p-8">
            <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">{{ __('site.my_videos_heading') }}</h1>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('site.my_videos_subtitle') }}</p>
                </div>

                <a href="{{ route('videos.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700">
                    <i class="fas fa-upload text-xs"></i>
                    {{ __('site.upload_video') }}
                </a>
            </div>

            <div class="mt-5 rounded-xl border border-blue-200 bg-blue-50/70 px-4 py-3 dark:border-blue-900/40 dark:bg-blue-900/10">
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="font-medium text-blue-700 dark:text-blue-300">{{ __('site.processing_videos') }}:</span>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $hasProcessingVideo ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200' : 'bg-gray-100 text-gray-700 dark:bg-[#2b2b2b] dark:text-gray-300' }}">
                        {{ $hasProcessingVideo ? __('site.processing_exists_yes') : __('site.processing_exists_no') }}
                    </span>
                </div>
            </div>

            <div class="mt-4 grid gap-3 md:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-[#2e2e2e] dark:bg-[#121212]">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('site.all_videos') }}</p>
                    <p class="mt-1 text-2xl font-semibold">{{ $allVideosCount }}</p>
                </div>

                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/40 dark:bg-emerald-900/10">
                    <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">{{ __('site.completed_videos') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-emerald-700 dark:text-emerald-200">{{ $completedCount }}</p>
                </div>

                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-900/40 dark:bg-rose-900/10">
                    <p class="text-xs uppercase tracking-wide text-rose-700 dark:text-rose-300">{{ __('site.failed_videos') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-rose-700 dark:text-rose-200">{{ $failedCount }}</p>
                </div>
            </div>

            @if ($failedCount > 0)
                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900/40 dark:bg-amber-900/10 dark:text-amber-200">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-triangle-exclamation mt-0.5"></i>
                        <p>{{ __('site.moderation_failure_hint') }}</p>
                    </div>
                </div>
            @endif
        </section>

        @if ($videoCollection->isEmpty())
            <section class="rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center dark:border-[#303030] dark:bg-[#141414]">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-950/40 dark:text-red-300">
                    <i class="fas fa-film"></i>
                </div>
                <h2 class="text-xl font-semibold">{{ __('site.no_videos_yet_title') }}</h2>
                <p class="mx-auto mt-2 max-w-lg text-sm text-gray-500 dark:text-gray-400">{{ __('site.no_videos_yet_description') }}</p>
                <a href="{{ route('videos.create') }}"
                    class="mt-6 inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700">
                    <i class="fas fa-upload text-xs"></i>
                    {{ __('site.upload_video') }}
                </a>
            </section>
        @else
            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($videoCollection as $video)
                    @php
                        $thumbnailUrl = $video->thumbnailDisplayUrl();
                        $statusValue = $video->processed?->value ?? 'processing';

                        $statusStyle = match ($statusValue) {
                            'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                            'failed' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                            default => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                        };

                        $statusLabel = match ($statusValue) {
                            'completed' => __('site.status_completed'),
                            'failed' => __('site.status_failed'),
                            default => __('site.status_processing'),
                        };

                        $hours = (int) ($video->hours ?? 0);
                        $minutes = (int) ($video->minutes ?? 0);
                        $seconds = (int) ($video->seconds ?? 0);
                        $duration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

                        $hasPlayableVideo = $video->primaryVideo() && $statusValue === 'completed';
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

                            <span class="absolute left-3 top-3 rounded-full px-3 py-1 text-xs font-medium {{ $statusStyle }}">
                                {{ $statusLabel }}
                            </span>

                            <span class="absolute bottom-3 right-3 rounded-md bg-black/75 px-2 py-1 text-xs font-medium text-white">
                                {{ $duration }}
                            </span>
                        </div>

                        <div class="space-y-3 p-4">
                            <h3 class="line-clamp-2 text-sm font-semibold leading-6 md:text-base">{{ $video->title }}</h3>

                            @if (!empty($video->description))
                                <p class="line-clamp-2 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $video->description }}</p>
                            @endif

                            <div class="grid grid-cols-2 gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <p>{{ __('site.video_orientation') }}</p>
                                <p class="text-right font-medium text-gray-700 dark:text-gray-200">
                                    {{ $video->longitudinal ? __('site.orientation_portrait') : __('site.orientation_landscape') }}
                                </p>

                                <p>{{ __('site.created_at_label') }}</p>
                                <p class="text-right font-medium text-gray-700 dark:text-gray-200">{{ optional($video->created_at)->diffForHumans() }}</p>
                            </div>

                            <div class="flex items-center justify-between gap-2 pt-1">
                                <span class="text-xs text-gray-500 dark:text-gray-400">#{{ $video->id }}</span>

                                @if ($hasPlayableVideo)
                                    <a href="{{ route('videos.show', $video) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:border-red-500 hover:text-red-600 dark:border-[#333] dark:text-gray-200 dark:hover:border-red-600 dark:hover:text-red-400">
                                        <i class="fas fa-eye"></i>
                                        {{ __('site.watch') }}
                                    </a>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <details class="group w-full">
                                    <summary class="cursor-pointer list-none rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-gray-700 transition hover:border-red-500 hover:text-red-600 dark:border-[#333] dark:text-gray-200 dark:hover:border-red-600 dark:hover:text-red-400">
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fas fa-pen"></i>
                                            {{ __('site.edit_video') }}
                                        </span>
                                    </summary>

                                    <form action="{{ route('videos.update', $video) }}" method="POST" enctype="multipart/form-data" class="mt-3 space-y-3 rounded-lg border border-gray-200 p-3 dark:border-[#2e2e2e]">
                                        @csrf
                                        @method('PUT')

                                        <div class="space-y-1">
                                            <label for="video-title-{{ $video->id }}" class="text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                                {{ __('site.video_title') }}
                                            </label>
                                            <input
                                                id="video-title-{{ $video->id }}"
                                                type="text"
                                                name="title"
                                                value="{{ $video->title }}"
                                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs text-gray-700 focus:border-red-500 focus:outline-none dark:border-[#333] dark:bg-[#101010] dark:text-gray-200">
                                        </div>

                                        <div class="space-y-1">
                                            <label for="video-description-{{ $video->id }}" class="text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                                {{ __('site.video_description') }}
                                            </label>
                                            <textarea
                                                id="video-description-{{ $video->id }}"
                                                name="description"
                                                rows="3"
                                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs text-gray-700 focus:border-red-500 focus:outline-none dark:border-[#333] dark:bg-[#101010] dark:text-gray-200">{{ $video->description }}</textarea>
                                        </div>

                                        <div class="space-y-1">
                                            <label for="video-image-{{ $video->id }}" class="text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                                {{ __('site.video_thumbnail') }}
                                            </label>
                                            <input
                                                id="video-image-{{ $video->id }}"
                                                type="file"
                                                name="image"
                                                accept="image/*"
                                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs text-gray-700 focus:border-red-500 focus:outline-none dark:border-[#333] dark:bg-[#101010] dark:text-gray-200">
                                        </div>

                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-red-700">
                                            <i class="fas fa-floppy-disk"></i>
                                            {{ __('site.update_video') }}
                                        </button>
                                    </form>
                                </details>

                                <form action="{{ route('videos.destroy', $video) }}" method="POST" onsubmit="return confirm(@json(__('site.confirm_delete_video')))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-3 py-2 text-xs font-medium text-rose-700 transition hover:bg-rose-50 dark:border-rose-900/40 dark:text-rose-300 dark:hover:bg-rose-900/20">
                                        <i class="fas fa-trash"></i>
                                        {{ __('site.delete_video') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            @if ($videos instanceof \Illuminate\Contracts\Pagination\Paginator || $videos instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                <div class="pt-2">
                    {{ $videos->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

<script>
    (function() {
        function disableSubmitButton(btn) {
            if (btn.dataset.processing === 'true') return;
            btn.dataset.processing = 'true';
            btn.disabled = true;
            const originalHTML = btn.innerHTML;
            const label = btn.textContent.trim();
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (label || '');
        }

        document.addEventListener('submit', function(e) {
            const form = e.target.closest('form');
            if (!form) return;
            const btn = form.querySelector('button[type="submit"]');
            if (!btn) return;
            disableSubmitButton(btn);
        });
    })();
</script>

@endsection
