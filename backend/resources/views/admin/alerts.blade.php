@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-8 dark:bg-[#0f0f0f]">
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ __('site.admin_alerts_title') }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('site.admin_alerts_subtitle') }}</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-300 dark:bg-[#303030] dark:hover:bg-[#404040]">
                    <i class="fas fa-arrow-left"></i>
                    {{ __('site.back') }}
                </a>
            </div>
        </section>

        <!-- Alerts List -->
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-[#262626] dark:bg-[#181818]">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-[#262626]">
                            <th class="px-6 py-4 text-left text-sm font-semibold">{{ __('site.admin_video') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">{{ __('site.admin_reported_by') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">{{ __('site.report_reason') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">{{ __('site.admin_status') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">{{ __('site.admin_date') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">{{ __('site.admin_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-[#262626]">
                        @forelse ($alerts as $alert)
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#202020]">
                                <td class="px-6 py-4">
                                    @if($alert->video)
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $alert->video->thumbnailDisplayUrl() }}" alt="{{ $alert->video->title }}" class="h-8 w-8 rounded object-cover">
                                            <div>
                                                <a href="{{ route('admin.videos.show', $alert->video) }}" class="font-medium hover:text-red-600">{{ $alert->video->title }}</a>
                                                <p class="text-xs text-gray-500 dark:text-gray-500">{{ __('site.by') }} {{ $alert->video->user->name }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400">{{ __('site.deleted') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $alert->user->profile_photo_url }}" alt="{{ $alert->user->name }}" class="h-8 w-8 rounded-full">
                                        <div>
                                            <p class="font-medium text-sm">{{ $alert->user->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-500">{{ $alert->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-block rounded-full px-3 py-1 text-xs font-medium
                                        @switch($alert->reason)
                                            @case('spam') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300 @break
                                            @case('abuse') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 @break
                                            @case('copyright') bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300 @break
                                            @case('inappropriate') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 @break
                                            @default bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-300
                                        @endswitch
                                    ">
                                        {{ $alert->reason ? __('site.report_' . $alert->reason->value) : 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-block rounded-full px-3 py-1 text-xs font-medium {{ $alert->resolved ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' }}">
                                        {{ $alert->resolved ? __('site.admin_resolved') : __('site.admin_pending') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $alert->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($alert->video)
                                            <a href="{{ route('admin.videos.show', $alert->video) }}" class="inline-flex items-center gap-1 rounded px-2 py-1 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20">
                                            <i class="fas fa-external-link-alt text-xs"></i>
                                            {{ __('site.view') }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center">
                                    <p class="text-gray-500 dark:text-gray-400">{{ __('site.admin_no_alerts_found') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($alerts->hasPages())
                <div class="border-t border-gray-200 px-6 py-4 dark:border-[#262626]">
                    {{ $alerts->links() }}
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
