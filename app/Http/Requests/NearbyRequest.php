<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NearbyRequest extends FormRequest
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
            'lat'    => ['required', 'numeric', 'between:-90,90'],
            'lng'    => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'integer', 'min:100', 'max:10000'],
        ];
    }
       public function messages(): array
    {
        return [
            'lat.required' => 'الموقع الجغرافي مطلوب',
            'lng.required' => 'الموقع الجغرافي مطلوب',
        ];
    }

    public function radius(): int
    {
        return $this->integer('radius', 2000);
    }
}
