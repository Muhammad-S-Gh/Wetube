<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class LoginRequest extends FormRequest
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
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $email = $this->input('email');

            if (!$email) {
                return;
            }

            $adminExists = User::query()
                ->where('email', $email)
                ->where('is_admin', true)
                ->exists();

            if (!$adminExists) {
                $validator->errors()->add('email', __('site.admin_not_authorized'));
            }
        });
    }
}
