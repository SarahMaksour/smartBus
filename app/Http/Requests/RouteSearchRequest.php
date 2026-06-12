<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RouteSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_lat' => ['required', 'numeric', 'between:-90,90'],
            'from_lng' => ['required', 'numeric', 'between:-180,180'],
            'to_lat'   => ['required', 'numeric', 'between:-90,90'],
            'to_lng'   => ['required', 'numeric', 'between:-180,180'],
            // departure_time غير مستخدم بالحساب بالـ MVP، بس منقبله للمستقبل
            'departure_time' => ['nullable', 'string'],
        ];
    }
}