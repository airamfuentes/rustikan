<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'apellidos' => ['nullable', 'string', 'max:255'],
            'telefono'  => ['nullable', 'string', 'min:9', 'max:20'],
            'edad'      => ['nullable', 'integer', 'min:14', 'max:120'],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:' . now()->subYears(14)->format('Y-m-d'), 'after_or_equal:' . now()->subYears(120)->format('Y-m-d')],
            'calle'     => ['nullable', 'string', 'max:100'],
            'numero'    => ['nullable', 'string', 'max:10'],
            'puerta'    => ['nullable', 'string', 'max:20'],
            'cp'        => ['nullable', 'string', 'regex:/^\d{5}$/'],
            'localidad' => ['nullable', 'string', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'email'     => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}
