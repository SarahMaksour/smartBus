<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RouteDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to_lat' => ['required', 'numeric', 'between:-90,90'],
            'to_lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}