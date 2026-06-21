@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50 px-4 py-8 dark:bg-[#0f0f0f]">
    <div class="mx-auto max-w-7xl space-y-8">
        <!-- Header -->
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ __('site.admin_user_management_title') }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __('site.admin_user_management_subtitle') }}</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-300 dark:bg-[#303030] dark:hover:bg-[#404040]">
                    <i class="fas fa-arrow-left"></i>
                    {{ __('site.back') }}
                </a>
            </div>
        </section>

        <!-- Search -->
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
            <form method="GET" action="{{ route('admin.users') }}" class="flex flex-col gap-3 md:flex-row md:items-end">
                <div class="flex-1">
                    <label for="user-search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('site.search') }}</label>
                    <input
                        id="user-search"
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
                        <a href="{{ route('admin.users') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-[#333] dark:text-gray-200 dark:hover:bg-[#202020]">
                            {{ __('site.cancel') }}
                        </a>
                    @endif
                </div>
            </form>
        </section>

        @if($selectedUser)
            <section class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
                    <div class="flex items-center gap-4">
                        <img src="{{ $selectedUser->profile_photo_url }}" alt="{{ $selectedUser->name }}" class="h-16 w-16 rounded-full object-cover">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('site.admin_selected_user') }}</p>
                            <h2 class="text-2xl font-bold tracking-tight">{{ $selectedUser->name }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $selectedUser->email }}</p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-[#2f2f2f] dark:bg-[#101010]">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('site.admin_total_videos') }}</p>
                            <p class="mt-1 text-2xl font-semibold">{{ $selectedUser->total_videos }}</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-[#2f2f2f] dark:bg-[#101010]">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('site.admin_completed_count', ['count' => $selectedUser->completed_videos]) }}</p>
                            <p class="mt-1 text-2xl font-semibold">{{ $selectedUser->completed_videos }}</p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2">
                        @if($selectedUser->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.delete', $selectedUser) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300" onclick="return confirm(@json(__('site.admin_confirm_delete_user')))">
                                    <i class="fas fa-trash"></i>
                                    {{ __('site.delete') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#262626] dark:bg-[#181818]">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-semibold">{{ __('site.admin_user_videos') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('site.admin_manage_videos') }}</p>
                        </div>
                        <a href="{{ route('admin.videos', ['search' => $selectedUser->name]) }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-[#333] dark:text-gray-200 dark:hover:bg-[#202020]">
                            <i class="fas fa-list"></i>
                            {{ __('site.admin_videos') }}
                        </a>
                    </div>

                    <div class="space-y-4">
                        @forelse($selectedUserVideos as $video)
                            @php
                                $thumbnailUrl = $video->thumbnailDisplayUrl();
                                $statusValue = $video->processed->value;
                                $statusClasses = match ($statusValue) {
                                    'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                    'processing' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                                    default => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                };
                            @endphp
                            <article class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-[#2f2f2f] dark:bg-[#101010]">
                                <div class="flex gap-4">
                                    <div class="h-20 w-32 overflow-hidden rounded-lg bg-gray-900">
                                        @if($thumbnailUrl)
                                            <img src="{{ $thumbnailUrl }}" alt="{{ $video->title }}" class="h-full w-full object-cover">
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <div>
                                                <h4 class="truncate font-semibold">{{ $video->title }}</h4>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $video->created_at->diffForHumans() }}</p>
                                            </div>
                                            <span class="inline-block rounded-full px-3 py-1 text-xs font-medium {{ $statusClasses }}">
                                                {{ __('site.status_' . $statusValue) }}
                                            </span>
                                        </div>

                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <a href="{{ route('admin.videos.show', $video) }}" class="inline-flex items-center gap-1 rounded-lg bg-blue-100 px-3 py-2 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                <i class="fas fa-eye"></i>
                                                {{ __('site.watch') }}
                                            </a>
                                            <!-- Block/unblock actions removed -->
                                            <form method="POST" action="{{ route('admin.videos.delete', $video) }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300" onclick="return confirm(@json(__('site.admin_confirm_delete_video')))">
                                                    <i class="fas fa-trash"></i>
                                                    {{ __('site.delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('site.admin_no_videos_found') }}</p>
                        @endforelse
                    </div>

                    @if($selectedUserVideos && $selectedUserVideos->hasPages())
                        <div class="mt-4 border-t border-gray-200 pt-4 dark:border-[#262626]">
                            {{ $selectedUserVideos->links() }}
                        </div>
                    @endif
                </div>
            </section>
        @endif

        <!-- Users Table -->
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-[#262626] dark:bg-[#181818]">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-[#262626]">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $search ? __('site.search') . ': ' . $search : __('site.admin_user_management_subtitle') }}
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-[#262626]">
                            <th class="px-6 py-4 text-left text-sm font-semibold">{{ __('site.admin_user') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">{{ __('site.email') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">{{ __('site.admin_videos') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">{{ __('site.status') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">{{ __('site.admin_joined') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">{{ __('site.admin_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-[#262626]">
                        @forelse ($users as $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#202020]">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-10 w-10 rounded-full">
                                        <div>
                                            <p class="font-medium">{{ $user->name }}</p>
                                            @if($user->is_admin)
                                                <span class="inline-block rounded-full bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">{{ __('site.admin_badge') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <p class="font-medium">{{ $user->total_videos }}</p>
                                        <p class="text-gray-600 dark:text-gray-400">{{ __('site.admin_completed_count', ['count' => $user->completed_videos]) }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->email_verified_at)
                                        <span class="inline-block rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ __('site.admin_verified') }}</span>
                                    @else
                                        <span class="inline-block rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300">{{ __('site.admin_unverified') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $user->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.users', ['user' => $user->id, 'search' => $search]) }}" class="inline-flex items-center gap-1 rounded-lg bg-blue-100 px-3 py-2 text-xs font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/40">
                                            <i class="fas fa-folder-open"></i>
                                            {{ __('site.admin_view') }}
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.delete', $user) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-red-100 px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-900/40" onclick="return confirm(@json(__('site.admin_confirm_delete_user')))">
                                                    <i class="fas fa-trash"></i>
                                                    {{ __('site.delete') }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-500">{{ __('site.admin_you_current_user') }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center">
                                    <p class="text-gray-500 dark:text-gray-400">{{ __('site.admin_no_users_found') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($users->hasPages())
                <div class="border-t border-gray-200 px-6 py-4 dark:border-[#262626]">
                    {{ $users->links() }}
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
