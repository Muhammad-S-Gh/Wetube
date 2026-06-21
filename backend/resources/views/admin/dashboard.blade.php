@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-8 dark:bg-[#0f0f0f]">
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ __('site.admin_dashboard') }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('site.admin_welcome_back', ['name' => auth()->user()->name]) }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fas fa-shield-alt text-3xl text-red-600"></i>
                </div>
            </div>
        </section>

        <!-- Main Stats Grid -->
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Users -->
            <div class="rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 to-blue-100 p-6 dark:border-blue-900/40 dark:from-blue-900/10 dark:to-blue-900/20">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ __('site.admin_total_users') }}</p>
                        <p class="mt-2 text-3xl font-bold text-blue-700 dark:text-blue-200">{{ $stats['total_users'] }}</p>
                        <p class="mt-1 text-xs text-blue-600 dark:text-blue-400">{{ __('site.admin_new_users_this_month', ['count' => $stats['new_users_this_month']]) }}</p>
                    </div>
                    <i class="fas fa-users text-4xl text-blue-300 dark:text-blue-700"></i>
                </div>
            </div>

            <!-- Videos -->
            <div class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-emerald-100 p-6 dark:border-emerald-900/40 dark:from-emerald-900/10 dark:to-emerald-900/20">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">{{ __('site.admin_total_videos') }}</p>
                        <p class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-200">{{ $stats['total_videos'] }}</p>
                        <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">{{ __('site.admin_completed_count', ['count' => $stats['completed_videos']]) }}</p>
                    </div>
                    <i class="fas fa-video text-4xl text-emerald-300 dark:text-emerald-700"></i>
                </div>
            </div>

            <!-- Views -->
            <div class="rounded-xl border border-purple-200 bg-gradient-to-br from-purple-50 to-purple-100 p-6 dark:border-purple-900/40 dark:from-purple-900/10 dark:to-purple-900/20">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-600 dark:text-purple-400">{{ __('site.admin_total_views') }}</p>
                        <p class="mt-2 text-3xl font-bold text-purple-700 dark:text-purple-200">{{ $stats['total_views'] }}</p>
                        <p class="mt-1 text-xs text-purple-600 dark:text-purple-400">{{ __('site.admin_engagement') }}</p>
                    </div>
                    <i class="fas fa-eye text-4xl text-purple-300 dark:text-purple-700"></i>
                </div>
            </div>

            <!-- Alerts -->
            <div class="rounded-xl border border-red-200 bg-gradient-to-br from-red-50 to-red-100 p-6 dark:border-red-900/40 dark:from-red-900/10 dark:to-red-900/20">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ __('site.admin_pending_alerts') }}</p>
                        <p class="mt-2 text-3xl font-bold text-red-700 dark:text-red-200">{{ $stats['total_alerts'] }}</p>
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ __('site.admin_need_attention') }}</p>
                    </div>
                    <i class="fas fa-exclamation-triangle text-4xl text-red-300 dark:text-red-700"></i>
                </div>
            </div>
        </section>

        <!-- Charts Grid -->
        <section class="grid gap-6 lg:grid-cols-2">
            <!-- Users Growth Chart -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
                <h3 class="mb-4 text-lg font-semibold">{{ __('site.admin_users_growth_last_30_days') }}</h3>
                <div style="position: relative; height: 320px;">
                    <canvas id="usersChart"></canvas>
                </div>
            </div>

            <!-- Videos Upload Chart -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
                <h3 class="mb-4 text-lg font-semibold">{{ __('site.admin_videos_uploaded_last_30_days') }}</h3>
                <div style="position: relative; height: 320px;">
                    <canvas id="videosChart"></canvas>
                </div>
            </div>

            <!-- Video Status Pie Chart -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
                <h3 class="mb-4 text-lg font-semibold">{{ __('site.admin_video_status_distribution') }}</h3>
                <div style="position: relative; height: 320px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- Views Trend Chart -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
                <h3 class="mb-4 text-lg font-semibold">{{ __('site.admin_views_trend_last_30_days') }}</h3>
                <div style="position: relative; height: 320px;">
                    <canvas id="viewsChart"></canvas>
                </div>
            </div>

        </section>

        <!-- Video Stats Row -->
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-[#262626] dark:bg-[#181818]">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('site.status_processing') }}</p>
                <div class="mt-2 h-1 bg-gray-200 dark:bg-gray-700">
                    <div class="h-full w-1/3 bg-yellow-500"></div>
                </div>
                <p class="mt-2 text-2xl font-bold">{{ $stats['processing_videos'] ?? 0 }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-[#262626] dark:bg-[#181818]">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('site.status_failed') }}</p>
                <div class="mt-2 h-1 bg-gray-200 dark:bg-gray-700">
                    <div class="h-full w-1/4 bg-red-500"></div>
                </div>
                <p class="mt-2 text-2xl font-bold">{{ $stats['failed_videos'] ?? 0 }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-[#262626] dark:bg-[#181818]">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('site.admin_admins') }}</p>
                <div class="mt-2 h-1 bg-gray-200 dark:bg-gray-700">
                    <div class="h-full w-1/12 bg-indigo-500"></div>
                </div>
                <p class="mt-2 text-2xl font-bold">{{ $stats['admin_users'] }}</p>
            </div>
        </section>

        <!-- Engagement Stats -->
        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-[#262626] dark:bg-[#181818]">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('site.admin_total_likes') }}</p>
                <p class="mt-2 text-3xl font-bold">{{ $stats['total_likes'] ?? 0 }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-[#262626] dark:bg-[#181818]">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('site.admin_total_comments') }}</p>
                <p class="mt-2 text-3xl font-bold">{{ $stats['total_comments'] }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-[#262626] dark:bg-[#181818]">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('site.admin_avg_video_size') }}</p>
                <p class="mt-2 text-3xl font-bold">{{ __('site.admin_size_mb', ['size' => $stats['average_video_size_mb']]) }}</p>
            </div>
        </section>

        <!-- Admin Actions Navigation -->
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
            <h2 class="text-xl font-semibold mb-4">{{ __('site.admin_management') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('admin.users') }}" class="rounded-lg border border-gray-200 p-4 text-center hover:bg-gray-50 dark:border-[#303030] dark:hover:bg-[#202020]">
                    <i class="fas fa-users text-2xl text-blue-600 mb-2"></i>
                    <p class="font-medium">{{ __('site.admin_manage_users') }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('site.admin_users_count', ['count' => $stats['total_users']]) }}</p>
                </a>
                <a href="{{ route('admin.videos') }}" class="rounded-lg border border-gray-200 p-4 text-center hover:bg-gray-50 dark:border-[#303030] dark:hover:bg-[#202020]">
                    <i class="fas fa-video text-2xl text-emerald-600 mb-2"></i>
                    <p class="font-medium">{{ __('site.admin_manage_videos') }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('site.admin_videos_count', ['count' => $stats['total_videos']]) }}</p>
                </a>
                <a href="{{ route('admin.alerts') }}" class="rounded-lg border border-gray-200 p-4 text-center hover:bg-gray-50 dark:border-[#303030] dark:hover:bg-[#202020]">
                    <i class="fas fa-exclamation-triangle text-2xl text-red-600 mb-2"></i>
                    <p class="font-medium">{{ __('site.admin_view_alerts') }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('site.admin_pending_count', ['count' => $stats['total_alerts']]) }}</p>
                </a>
            </div>
        </section>

        <!-- Recent Activities -->
        <section class="grid gap-8 lg:grid-cols-2">
            <!-- Recent Videos -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
                <h3 class="text-lg font-semibold mb-4">{{ __('site.admin_recent_videos') }}</h3>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse ($activities['recent_videos'] as $video)
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-[#303030]">
                            <div class="flex-1">
                                <p class="font-medium truncate">{{ $video->title }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('site.admin_by_user', ['name' => $video->user->name]) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-500">{{ $video->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="ml-2 inline-block rounded-full px-2 py-1 text-xs font-medium @if($video->processed->value === 'completed') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 @elseif($video->processed->value === 'processing') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300 @else bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 @endif">
                                {{ $video->processed->value }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">{{ __('site.admin_no_recent_videos') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Users -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
                <h3 class="text-lg font-semibold mb-4">{{ __('site.admin_recent_users') }}</h3>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse ($activities['recent_users'] as $user)
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-[#303030]">
                            <div class="flex items-center gap-3 flex-1">
                                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-8 w-8 rounded-full">
                                <div>
                                    <p class="font-medium">{{ $user->name }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
                                </div>
                            </div>
                            @if($user->is_admin)
                                <span class="inline-block rounded-full bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">{{ __('site.admin_badge') }}</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">{{ __('site.admin_no_recent_users') }}</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-red-200 bg-red-50 p-6 shadow-sm dark:border-red-900/40 dark:bg-red-900/10">
            <h3 class="text-lg font-semibold text-red-700 dark:text-red-300">{{ __('site.admin_danger_zone') }}</h3>
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ __('site.admin_delete_account_note') }}</p>

            <form
                action="{{ route('admin.account.destroy') }}"
                method="POST"
                class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end"
                onsubmit="return confirm(@json(__('site.admin_confirm_delete_account')))">
                @csrf
                @method('DELETE')
                <div class="w-full sm:max-w-xs">
                    <label for="delete-account-password" class="mb-1 block text-sm font-medium text-red-700 dark:text-red-300">{{ __('site.confirm_password') }}</label>
                    <input
                        id="delete-account-password"
                        name="password"
                        type="password"
                        required
                        class="w-full rounded-lg border border-red-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/30 dark:border-red-800 dark:bg-[#181818] dark:text-gray-100"
                        placeholder="{{ __('site.admin_enter_password') }}">
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/40">
                    {{ __('site.admin_delete_my_account') }}
                </button>
            </form>
        </section>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

<script>
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(75, 85, 99, 0.2)' : 'rgba(201, 203, 207, 0.2)';
    const textColor = isDark ? '#c0c0c0' : '#666';
    const borderColor = isDark ? 'rgba(75, 85, 99, 0.5)' : 'rgba(201, 203, 207, 0.5)';

    // Store chart instances to prevent memory leaks
    const chartInstances = {};

    function initChart(canvasId, chartConfig) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        // Destroy existing chart if it exists
        if (chartInstances[canvasId]) {
            chartInstances[canvasId].destroy();
        }

        // Create new chart
        chartInstances[canvasId] = new Chart(ctx, chartConfig);
    }

    // Users Chart
    initChart('usersChart', {
        type: 'line',
        data: {
            labels: @json($chartData['usersPerDay']['labels']),
            datasets: [{
                label: @json(__('site.admin_new_users')),
                data: @json($chartData['usersPerDay']['data']),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { gridColor, ticks: { color: textColor }, beginAtZero: true },
                x: { gridColor, ticks: { color: textColor } }
            }
        }
    });

    // Videos Chart
    initChart('videosChart', {
        type: 'line',
        data: {
            labels: @json($chartData['videosPerDay']['labels']),
            datasets: [{
                label: @json(__('site.admin_new_videos')),
                data: @json($chartData['videosPerDay']['data']),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { gridColor, ticks: { color: textColor }, beginAtZero: true },
                x: { gridColor, ticks: { color: textColor } }
            }
        }
    });

    // Video Status Pie Chart
    initChart('statusChart', {
        type: 'doughnut',
        data: {
            labels: @json($chartData['videoStatus']['labels']),
            datasets: [{
                data: @json($chartData['videoStatus']['data']),
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#6b7280'],
                borderColor: isDark ? '#1a1a1a' : '#fff',
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    labels: { color: textColor },
                    position: 'bottom'
                }
            }
        }
    });

    // Views Chart
    initChart('viewsChart', {
        type: 'bar',
        data: {
            labels: @json($chartData['viewsPerDay']['labels']),
            datasets: [{
                label: @json(__('site.views')),
                data: @json($chartData['viewsPerDay']['data']),
                backgroundColor: 'rgba(168, 85, 247, 0.8)',
                borderColor: '#a855f7',
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { gridColor, ticks: { color: textColor }, beginAtZero: true },
                x: { gridColor, ticks: { color: textColor } }
            }
        }
    });

    // Engagement Chart
    initChart('engagementChart', {
        type: 'doughnut',
        data: {
            labels: @json($chartData['engagement']['labels']),
            datasets: [{
                data: @json($chartData['engagement']['data']),
                backgroundColor: ['#ec4899', '#8b5cf6'],
                borderColor: isDark ? '#1a1a1a' : '#fff',
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    labels: { color: textColor },
                    position: 'bottom'
                }
            }
        }
    });
</script>

@endsection
