@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#0f0f0f] py-10 px-4">

    <div class="max-w-3xl mx-auto">

        <div class="bg-white dark:bg-[#181818]
                    border border-gray-200 dark:border-[#262626]
                    rounded-2xl shadow-sm p-6 md:p-8">

            <div class="text-center mb-8">
                <h2 class="text-2xl md:text-3xl font-semibold">
                    {{ __('site.upload_new_video') }}
                </h2>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">
                    {{ __('Share your content with the world') }}
                </p>
            </div>

            <div id="upload-client-error" class="hidden mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900/40 dark:bg-rose-900/10 dark:text-rose-200">
                <div class="flex items-start gap-2">
                    <i class="fas fa-circle-exclamation mt-0.5"></i>
                    <p id="upload-client-error-text"></p>
                </div>
            </div>

            <form id="upload-form" action="{{ route('videos.optimizedStore') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                <div>
                    <label class="block mb-2 text-sm font-medium">
                        {{ __('site.video_title') }}
                    </label>

                    <input type="text" name="title" value="{{ old('title') }}"
                        class="w-full px-4 py-2.5 rounded-xl border
                               dark:border-[#333]
                               bg-white dark:bg-[#0f0f0f]
                               text-gray-900 dark:text-white
                               focus:ring-2 focus:ring-red-500 focus:outline-none
                               transition
                               @error('title') border-red-500 @enderror">

                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium">
                        {{ __('site.video_description') }}
                    </label>

                    <textarea name="description" rows="4"
                        class="w-full px-4 py-2.5 rounded-xl border
                               dark:border-[#333]
                               bg-white dark:bg-[#0f0f0f]
                               text-gray-900 dark:text-white
                               focus:ring-2 focus:ring-red-500 focus:outline-none
                               transition
                               @error('description') border-red-500 @enderror"
                        placeholder="{{ __('site.video_description_placeholder') }}">{{ old('description') }}</textarea>

                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('site.video_thumbnail') }}
                    </label>

                    <div id="image-drop"
                        class="relative border-2 border-dashed rounded-xl p-6 text-center cursor-pointer
                            border-gray-300 hover:border-red-500
                            dark:border-[#262626] dark:hover:border-red-500
                            transition">

                        <input type="file" id="image" name="image" accept="image/*"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">

                        <div class="space-y-2 pointer-events-none">
                            <i class="fas fa-image text-3xl text-gray-400"></i>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('site.drag_or_click_image') }}
                            </p>
                        </div>
                    </div>

                    <div id="image-preview-container" class="hidden">
                        <div class="relative w-full max-w-sm rounded-xl overflow-hidden border
                                    border-gray-200 dark:border-[#262626] shadow-sm">

                            <img id="cover-img-thumb" class="w-full h-48 object-cover">

                            <div class="absolute bottom-0 left-0 right-0 bg-black/60 text-white text-xs px-3 py-2 truncate"
                                id="image-name">
                            </div>
                        </div>
                    </div>

                    @error('image')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium">
                        {{ __('site.video_clip') }}
                    </label>

                    <div id="video-drop"
                        class="relative border-2 border-dashed rounded-xl p-6 text-center cursor-pointer
                            border-gray-300 hover:border-red-500
                            dark:border-[#262626] dark:hover:border-red-500
                            transition">

                        <input type="file" id="video" name="video" accept="video/*"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">

                        <div class="space-y-2 pointer-events-none">
                            <i class="fas fa-video text-3xl text-gray-400"></i>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">
                                {{ __('site.drag_or_click_video') }}
                            </p>
                        </div>
                    </div>

                    <div id="video-preview-container" class="hidden mt-4">
                        <video id="video-preview"
                            class="w-full max-h-64 rounded-xl border dark:border-[#262626]"
                            controls>
                        </video>

                        <p id="video-name" class="text-sm mt-2 text-gray-600 dark:text-gray-400"></p>
                    </div>

                    @error('video')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" id="submit-btn"
                        class="w-full md:w-auto px-8 py-2.5
                               bg-red-600 text-white rounded-xl
                               hover:bg-red-700 transition font-medium shadow-md">
                        {{ __('site.submit_video') }}
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
@endsection

@section('script')
<script>
(function() {
    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    const videoId = @json(session('video_id'));

    if (videoId && window.WeTubeUpload?.register) {
        window.WeTubeUpload.register({
            videoId,
            messages: {
                success: @json(__('site.video_upload_completed')),
                failed: @json(__('site.upload_check_notifications')),
            },
        });
    }

    ready(function() {
        const imageInput = document.getElementById('image');
        const imageDrop = document.getElementById('image-drop');
        const videoInput = document.getElementById('video');
        const videoDrop = document.getElementById('video-drop');
        const uploadForm = document.getElementById('upload-form');
        const submitBtn = document.getElementById('submit-btn');
        const clientError = document.getElementById('upload-client-error');
        const clientErrorText = document.getElementById('upload-client-error-text');

        function showClientError(message) {
            clientErrorText.textContent = message;
            clientError.classList.remove('hidden');
        }

        function clearClientError() {
            clientError.classList.add('hidden');
            clientErrorText.textContent = '';
        }

        function handleImage(file) {
            if (!file || !file.type.startsWith('image')) return;
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('cover-img-thumb').src = e.target.result;
                document.getElementById('image-name').textContent = file.name;
                document.getElementById('image-preview-container').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }

        imageInput.addEventListener('change', e => handleImage(e.target.files[0]));
        imageDrop.addEventListener('dragover', e => { e.preventDefault(); imageDrop.classList.add('border-red-500'); });
        imageDrop.addEventListener('dragleave', () => imageDrop.classList.remove('border-red-500'));
        imageDrop.addEventListener('drop', e => {
            e.preventDefault();
            imageDrop.classList.remove('border-red-500');
            imageInput.files = e.dataTransfer.files;
            handleImage(e.dataTransfer.files[0]);
        });

        function handleVideo(file) {
            if (!file || !file.type.startsWith('video')) return;
            const url = URL.createObjectURL(file);
            document.getElementById('video-preview').src = url;
            document.getElementById('video-name').textContent = file.name;
            document.getElementById('video-preview-container').classList.remove('hidden');
        }

        videoInput.addEventListener('change', e => handleVideo(e.target.files[0]));
        videoDrop.addEventListener('dragover', e => { e.preventDefault(); videoDrop.classList.add('border-red-500'); });
        videoDrop.addEventListener('dragleave', () => videoDrop.classList.remove('border-red-500'));
        videoDrop.addEventListener('drop', e => {
            e.preventDefault();
            videoDrop.classList.remove('border-red-500');
            videoInput.files = e.dataTransfer.files;
            handleVideo(e.dataTransfer.files[0]);
        });

        if (videoId) {
            uploadForm.classList.add('opacity-50', 'pointer-events-none');
            submitBtn.disabled = true;
            submitBtn.textContent = @json(__('site.Preparing_upload'));
            return;
        }

        uploadForm.addEventListener('submit', function(event) {
            clearClientError();

            if (!imageInput.files[0] || !videoInput.files[0]) {
                event.preventDefault();
                showClientError(@json(__('site.upload_missing_files')));
                return;
            }

            if (submitBtn.disabled) {
                event.preventDefault();
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + @json(__('site.Preparing_upload'));
        });
    });
})();
</script>
@endsection
