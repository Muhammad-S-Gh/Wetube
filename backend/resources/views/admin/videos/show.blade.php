@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-8 dark:bg-[#0f0f0f]">
    <div class="mx-auto max-w-7xl space-y-8">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ $video->title }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('site.admin_video_review_subtitle') }}</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.videos') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-[#333] dark:text-gray-300 dark:hover:bg-[#1b1b1b]">
                        <i class="fas fa-arrow-left"></i>
                        {{ __('site.back') }}
                    </a>

                    <!-- Alerts quick link removed -->
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-[#262626] dark:bg-[#181818]">
            <div class="bg-black">
                @if (!empty($formatOptions))
                    <video id="admin-watch-player" class="aspect-video w-full" controls poster="{{ $thumbnailUrl ?? '' }}"></video>
                @else
                    <div class="flex aspect-video w-full items-center justify-center text-gray-400">
                        <div class="text-center">
                            <i class="fas fa-video text-5xl"></i>
                            <p class="mt-3 text-sm">{{ __('site.no_watchable_variants') }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="grid gap-6 p-5 md:grid-cols-[1.2fr_0.8fr] md:p-6">
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <img src="{{ $video->user?->profile_photo_url }}" alt="{{ $video->user?->name ?? '' }}" class="h-12 w-12 rounded-full object-cover">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('site.creator') }}</p>
                            <p class="font-semibold">{{ $video->user?->name ?? '-' }}</p>
                        </div>

                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $video->processed->value === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : ($video->processed->value === 'failed' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300') }}">
                            {{ $video->processed->value === 'completed' ? __('site.status_completed') : ($video->processed->value === 'failed' ? __('site.status_failed') : __('site.status_processing')) }}
                        </span>
                    </div>

                    <p class="whitespace-pre-line text-sm leading-7 text-gray-600 dark:text-gray-300">
                        {{ $video->description ?? __('site.admin_video_review_note') }}
                    </p>

                    @if (!empty($alerts) && $alerts->count())
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900/40 dark:bg-red-900/10">
                            <p class="text-sm font-semibold text-red-700 dark:text-red-300">{{ __('site.admin_related_alerts') }}</p>
                            <div class="mt-3 space-y-2">
                                @foreach ($alerts as $alert)
                                    <div class="rounded-lg bg-white px-3 py-2 text-sm shadow-sm dark:bg-[#101010]">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-3">
                                                <img src="{{ $alert->user?->profile_photo_url }}" alt="{{ $alert->user?->name }}" class="h-6 w-6 rounded-full">
                                                <div>
                                                    <p class="font-medium text-sm">{{ $alert->user?->name ?? __('site.deleted') }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $alert->description ?: ($alert->reason?->value ? __('site.report_' . $alert->reason->value) : __('site.no_data_found')) }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ optional($alert->created_at)->diffForHumans() }}</span>
                                                <form method="POST" action="{{ route('admin.alerts.approve', $alert) }}">
                                                    @csrf
                                                    <button type="submit" class="ml-2 inline-flex items-center gap-1 rounded px-2 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300">{{ __('site.admin_approve') }}</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-[#303030] dark:bg-[#101010]">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">{{ __('site.views') }}</p>
                                <p class="font-semibold">{{ $video->views_count }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">{{ __('site.likes') }}</p>
                                <p class="font-semibold">{{ $video->likes_count }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">{{ __('site.comments') }}</p>
                                <p class="font-semibold">{{ $video->comments_count }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">{{ __('site.admin_date') }}</p>
                                <p class="font-semibold">{{ $video->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-[#303030] dark:bg-[#101010]">
                        <p class="mb-3 text-sm font-semibold">{{ __('site.admin_management') }}</p>
                        <div class="space-y-2">
                            <!-- Block/unblock buttons removed -->

                            <form method="POST" action="{{ route('admin.videos.delete', $video) }}">
                                @csrf
                                <button type="submit" class="w-full rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                                    {{ __('site.delete') }}
                                </button>
                            </form>
                                <!-- Approve alert per-video removed; use per-alert approve controls in the alerts list -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const player = document.getElementById('admin-watch-player');
    const variants = @json($variantMap);
    const formats = @json($formatOptions);

    if (!player || !formats.length) {
        return;
    }

    const firstFormat = formats[0];
    const firstVariant = variants[firstFormat]?.[0];

    if (firstVariant?.path) {
        player.src = firstVariant.path;
        player.load();
    }
});
</script>
@endsection
