<?php

namespace App\Http\Requests;

use App\Models\Travel;
use Illuminate\Foundation\Http\FormRequest;

class StoreCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'travel_id' => ['required', 'uuid', 'exists:travels,id'],
            'email' => ['required', 'email:rfc'],
            'seats' => ['required', 'integer', 'min:1', 'max:'.Travel::MAX_SEATS],
        ];
    }
}
