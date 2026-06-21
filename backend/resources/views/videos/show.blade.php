@extends('layouts.main')

@section('content')
@php
    $thumbnailUrl = $video->thumbnailDisplayUrl();
@endphp

<div class="min-h-screen bg-gray-50 px-4 py-8 dark:bg-[#0f0f0f]">
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex items-center justify-between">
            <a href="{{ auth()->check() ? route('videos.index') : route('explore') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 transition hover:text-red-600 dark:text-gray-300 dark:hover:text-red-400">
                <i class="fas fa-arrow-left"></i>
                {{ auth()->check() ? __('site.back_to_my_videos') : __('site.explore') }}
            </a>

            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $video->processed->value === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : ($video->processed->value === 'failed' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300') }}">
                {{ $video->processed->value === 'completed' ? __('site.status_completed') : ($video->processed->value === 'failed' ? __('site.status_failed') : __('site.status_processing')) }}
            </span>
        </div>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-[#262626] dark:bg-[#181818]">
            <div class="relative bg-black">
                <video
                    id="watch-player"
                    class="aspect-video w-full"
                    controls
                    poster="{{ $thumbnailUrl }}"
                    @if (empty($formatOptions)) disabled @endif>
                </video>
            </div>

            <div class="space-y-4 p-5 md:p-6">
                <h1 class="text-xl font-semibold md:text-2xl">{{ $video->title }}</h1>

                @if (!empty($video->description))
                    <p class="whitespace-pre-line text-sm leading-7 text-gray-600 dark:text-gray-300">{{ $video->description }}</p>
                @endif

                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-[#2f2f2f] dark:bg-[#101010]">
                    <div class="flex items-center gap-3">
                        <img src="{{ $video->user?->profile_photo_url }}" alt="{{ $video->user?->name ?? '' }}" class="h-10 w-10 rounded-full object-cover">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('site.creator') }}</p>
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $video->user?->name ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-xs md:text-sm">
                        <span class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 dark:border-[#333] dark:bg-[#171717]">
                            {{ __('site.views') }}: <strong>{{ $video->viewers_count }}</strong>
                        </span>
                        <span class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 dark:border-[#333] dark:bg-[#171717]">
                            {{ __('site.likes') }}: <strong>{{ $video->likes_count }}</strong>
                        </span>
                        <span class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 dark:border-[#333] dark:bg-[#171717]">
                            {{ __('site.comments') }}: <strong>{{ $video->comments_count }}</strong>
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @auth
                        <form action="{{ route('videos.likes.toggle', $video) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium transition {{ $likedByViewer ? 'border-red-600 bg-red-600 text-white hover:bg-red-700 hover:border-red-700' : 'border-gray-200 bg-white text-gray-700 hover:border-red-500 hover:text-red-600 dark:border-[#333] dark:bg-[#111] dark:text-gray-200 dark:hover:border-red-600 dark:hover:text-red-400' }}">
                                <i class="{{ $likedByViewer ? 'fas' : 'far' }} fa-thumbs-up"></i>
                                {{ $likedByViewer ? __('site.unlike_video') : __('site.like_video') }}
                            </button>
                        </form>

                        <button
                            type="button"
                            onclick="const panel = document.getElementById('report-panel'); const button = document.getElementById('report-button'); panel?.classList.toggle('hidden'); button?.classList.toggle('bg-orange-600'); button?.classList.toggle('text-white'); button?.classList.toggle('border-orange-600');"
                            id="report-button"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-orange-500 hover:text-orange-600 dark:border-[#333] dark:bg-[#111] dark:text-gray-200 dark:hover:border-orange-600 dark:hover:text-orange-400">
                            <i class="fas fa-flag"></i>
                            {{ __('site.report_video') }}
                        </button>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-red-500 hover:text-red-600 dark:border-[#333] dark:bg-[#111] dark:text-gray-200 dark:hover:border-red-600 dark:hover:text-red-400">
                            <i class="far fa-thumbs-up"></i>
                            {{ __('site.login_to_like') }}
                        </a>

                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-orange-500 hover:text-orange-600 dark:border-[#333] dark:bg-[#111] dark:text-gray-200 dark:hover:border-orange-600 dark:hover:text-orange-400">
                            <i class="fas fa-flag"></i>
                            {{ __('site.report_video') }}
                        </a>
                    @endauth
                </div>

                @auth
                    <section id="report-panel" class="hidden rounded-2xl border border-orange-200 bg-orange-50 p-4 dark:border-orange-900/40 dark:bg-orange-950/20">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-orange-900 dark:text-orange-200">{{ __('site.report_video') }}</h3>
                                <p class="text-xs text-orange-800/80 dark:text-orange-200/80">{{ __('site.help_us_improve') }}</p>
                            </div>

                            <button type="button" onclick="document.getElementById('report-panel').classList.add('hidden')" class="text-sm font-medium text-orange-700 hover:text-orange-900 dark:text-orange-200 dark:hover:text-orange-100">
                                {{ __('site.cancel') }}
                            </button>
                        </div>

                        <form action="{{ route('videos.report', $video) }}" method="POST" class="grid gap-4 md:grid-cols-2">
                            @csrf

                            <div>
                                <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('site.report_reason') }}</label>
                                <select id="reason" name="reason" required class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-[#333] dark:bg-[#101010] dark:text-gray-200">
                                    <option value="">{{ __('site.select_reason') }}</option>
                                    <option value="spam">{{ __('site.report_spam') }}</option>
                                    <option value="abuse">{{ __('site.report_abuse') }}</option>
                                    <option value="copyright">{{ __('site.report_copyright') }}</option>
                                    <option value="inappropriate">{{ __('site.report_inappropriate') }}</option>
                                    <option value="other">{{ __('site.report_other') }}</option>
                                </select>
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('site.report_description') }}</label>
                                <textarea id="description" name="description" rows="3" class="mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-orange-500 focus:outline-none dark:border-[#333] dark:bg-[#101010] dark:text-gray-200" placeholder="{{ __('site.report_placeholder') }}"></textarea>
                            </div>

                            <div class="md:col-span-2 flex justify-end">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-orange-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-orange-700">
                                    <i class="fas fa-paper-plane text-xs"></i>
                                    {{ __('site.submit_report') }}
                                </button>
                            </div>
                        </form>
                    </section>
                @endauth

                @if (!empty($formatOptions))
                    <div class="grid gap-3 md:grid-cols-3" id="watch-controls" data-variants='@json($variantMap)'>
                        <label class="space-y-1">
                            <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('site.video_format') }}</span>
                            <select id="watch-format" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-red-500 focus:outline-none dark:border-[#333] dark:bg-[#101010] dark:text-gray-200">
                                @foreach ($formatOptions as $formatOption)
                                    <option value="{{ $formatOption }}">{{ strtoupper($formatOption) }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="space-y-1">
                            <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('site.video_resolution') }}</span>
                            <select id="watch-resolution" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-red-500 focus:outline-none dark:border-[#333] dark:bg-[#101010] dark:text-gray-200"></select>
                        </label>

                        <div class="space-y-1">
                            <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('site.active_stream') }}</span>
                            <div id="watch-meta" class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-[#333] dark:bg-[#101010] dark:text-gray-200"></div>
                            <div id="watch-actual-meta" class="text-xs text-gray-500 dark:text-gray-400"></div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('site.no_watchable_variants') }}</p>
                @endif
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#262626] dark:bg-[#181818] md:p-6">
            <div class="mb-5 flex items-center justify-between gap-2">
                <h2 class="text-lg font-semibold md:text-xl">{{ __('site.comments') }}</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $video->comments_count }}</span>
            </div>

            @auth
                <form action="{{ route('videos.comments.store', $video) }}" method="POST" class="space-y-3">
                    @csrf
                    <label for="comment-body" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('site.write_comment') }}</label>
                    <textarea id="comment-body" name="body" rows="4" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 focus:border-red-500 focus:outline-none dark:border-[#333] dark:bg-[#111] dark:text-gray-200" placeholder="{{ __('site.comment_placeholder') }}">{{ old('body') }}</textarea>

                    @error('body')
                        <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700">
                        <i class="fas fa-paper-plane text-xs"></i>
                        {{ __('site.post_comment') }}
                    </button>
                </form>
            @else
                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-sm text-gray-600 dark:border-[#3a3a3a] dark:bg-[#121212] dark:text-gray-300">
                    {{ __('site.login_to_comment') }}
                    <a href="{{ route('login') }}" class="font-semibold text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">{{ __('site.login') }}</a>
                </div>
            @endauth

            <div class="mt-6 space-y-4">
                @forelse ($comments as $comment)
                    <article class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-[#2f2f2f] dark:bg-[#101010]">
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ $comment->user?->profile_photo_url }}" alt="{{ $comment->user?->name ?? '' }}" class="h-9 w-9 rounded-full object-cover">
                            <div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $comment->user?->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ optional($comment->created_at)->diffForHumans() }}</p>
                            </div>
                        </div>
                        <p class="whitespace-pre-line text-sm leading-6 text-gray-700 dark:text-gray-200">{{ $comment->body }}</p>
                    </article>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('site.no_comments_yet') }}</p>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $comments->links() }}
            </div>
        </section>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // The watch page keeps all playable variants grouped by format.
    // When user changes format/resolution, we only swap the <video> source URL.
    const controlBlock = document.getElementById('watch-controls');
    if (!controlBlock) {
        return;
    }

    const player = document.getElementById('watch-player');
    const formatSelect = document.getElementById('watch-format');
    const resolutionSelect = document.getElementById('watch-resolution');
    const meta = document.getElementById('watch-meta');
    const actualMeta = document.getElementById('watch-actual-meta');

    let variantsByFormat = {};
    try {
        variantsByFormat = JSON.parse(controlBlock.dataset.variants || '{}');
    } catch {
        variantsByFormat = {};
    }

    const readSelectionFromUrl = () => {
        const params = new URLSearchParams(window.location.search);
        return {
            format: params.get('format'),
            resolution: params.get('resolution'),
        };
    };

    const initialSelection = readSelectionFromUrl();

    if (initialSelection.format && Array.from(formatSelect.options).some((option) => option.value === initialSelection.format)) {
        formatSelect.value = initialSelection.format;
    }

    const syncUrl = (selectedFormat, selectedIndex) => {
        const url = new URL(window.location.href);
        url.searchParams.set('format', selectedFormat);
        url.searchParams.set('resolution', String(selectedIndex));
        window.history.replaceState({}, '', url.toString());
    };

    const changeSource = (url) => {
        if (!url) {
            return;
        }

        // Preserve current timestamp so the switch feels like YouTube quality switching.
        const currentTime = player.currentTime || 0;
        const wasPlaying = !player.paused;

        player.src = url;
        player.load();

        player.addEventListener('loadedmetadata', () => {
            if (actualMeta) {
                actualMeta.textContent = `${player.videoWidth} × ${player.videoHeight}`;
            }

            if (currentTime > 0 && currentTime < player.duration) {
                player.currentTime = currentTime;
            }

            if (wasPlaying) {
                player.play().catch(() => {});
            }
        }, { once: true });
    };

    const updateResolutions = () => {
        const selectedFormat = formatSelect.value;
        const options = variantsByFormat[selectedFormat] || [];
        const { resolution } = readSelectionFromUrl();

        resolutionSelect.innerHTML = '';

        options.forEach((option, index) => {
            const optionEl = document.createElement('option');
            optionEl.value = String(index);
            optionEl.textContent = option.quality;
            resolutionSelect.appendChild(optionEl);
        });

        const preferredIndex = Number.isFinite(Number(resolution)) ? Number(resolution) : 0;
        resolutionSelect.value = String(Math.min(Math.max(preferredIndex, 0), Math.max(options.length - 1, 0)));

        updateStream();
    };

    const updateStream = () => {
        const selectedFormat = formatSelect.value;
        const options = variantsByFormat[selectedFormat] || [];
        const selectedIndex = Number(resolutionSelect.value || 0);
        const selectedOption = options[selectedIndex] || options[0];

        if (!selectedOption) {
            meta.textContent = @json(__('site.no_watchable_variants'));
            return;
        }

        meta.textContent = `${selectedFormat.toUpperCase()} ${selectedOption.quality}`;
        syncUrl(selectedFormat, selectedIndex);
        changeSource(selectedOption.path);
    };

    formatSelect.addEventListener('change', updateResolutions);
    resolutionSelect.addEventListener('change', updateStream);

    updateResolutions();
});
</script>

@endsection
