<?php

declare(strict_types=1);

namespace LaravelPlus\Referral\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'An email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'message.max' => 'The message may not exceed 500 characters.',
        ];
    }
}
