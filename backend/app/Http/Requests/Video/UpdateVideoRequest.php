<?php

namespace App\Http\Requests\Video;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255|required_without_all:image,description',
            'description' => 'nullable|string|max:5000|required_without_all:title,image',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|required_without_all:title,description',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => __('site.video_title'),
            'description' => __('site.video_description'),
            'image' => __('site.video_thumbnail'),
        ];
    }
}
