<?php

return [
    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),
    'video_upload_preset' => env('CLOUDINARY_VIDEO_UPLOAD_PRESET'),
    'image_upload_preset' => env('CLOUDINARY_IMAGE_UPLOAD_PRESET'),

    'video_resolutions' => [1080, 720, 480, 360, 240],
    'video_formats' => ['mp4', 'webm'],

    'thumbnail' => [
        'width' => 320,
        'height' => 180,
    ],
];
