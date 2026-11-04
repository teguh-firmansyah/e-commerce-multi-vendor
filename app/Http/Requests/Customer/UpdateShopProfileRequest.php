<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShopProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $shopId = $this->user()?->shop?->id;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('shops', 'name')->ignore($shopId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{8,30}$/'],
            'is_open' => ['nullable', 'boolean'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'digits:5'],
            'address_line' => ['required', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=6000,max_height=6000'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }
}
