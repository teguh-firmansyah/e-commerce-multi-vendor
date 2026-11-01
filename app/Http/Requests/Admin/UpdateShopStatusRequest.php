<?php

namespace App\Http\Requests\Admin;

use App\Enums\ShopStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShopStatusRequest extends FormRequest
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
        return [
            'status' => [
                'required',
                Rule::in([
                    ShopStatus::Approved->value,
                    ShopStatus::Rejected->value,
                    ShopStatus::Suspended->value,
                ]),
            ],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
