<?php

namespace App\Http\Requests\Video;

use App\Enums\UploadStateEnum;
use App\Models\Video;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class OptimizedUploadRequest extends FormRequest
{
    public int $userId;

    protected function prepareForValidation(): void
    {
        $this->userId = $this->user()?->id;
        $this->merge(['user_id' => $this->userId]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            'video' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska|max:102400',
            'user_id' => 'required|exists:users,id',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $hasProcessing = Video::where('user_id', $this->userId)
                ->where('processed', UploadStateEnum::PROCESSING->value)
                ->exists();

            if ($hasProcessing) {
                $validator->errors()->add('video', __('site.already_have_video_processing'));
            }
        });
    }

    public function attributes(): array
    {
        return [
            'title' => __('site.video_title'),
            'description' => __('site.video_description'),
            'image' => __('site.video_thumbnail'),
            'video' => __('site.video_clip'),
        ];
    }
}
