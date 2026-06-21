<?php

namespace App\Http\Requests\Video;

use Illuminate\Foundation\Http\FormRequest;

class ReportVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|in:spam,abuse,copyright,inappropriate,other',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function attributes(): array
    {
        return [
            'reason' => __('site.report_reason'),
            'description' => __('site.report_description'),
        ];
    }
}
