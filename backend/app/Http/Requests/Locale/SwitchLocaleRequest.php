<?php

namespace App\Http\Requests\Locale;

use Illuminate\Foundation\Http\FormRequest;

class SwitchLocaleRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'locale' => $this->route('locale'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => 'required|in:en,ar',
        ];
    }
}
