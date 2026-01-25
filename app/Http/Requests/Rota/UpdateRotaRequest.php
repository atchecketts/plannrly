<?php

namespace App\Http\Requests\Rota;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $rota = $this->route('rota');

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        if ($rota->location_id && $user->isLocationAdmin($rota->location_id)) {
            return true;
        }

        return $rota->department_id && $user->isDepartmentAdmin($rota->department_id);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a rota name.',
            'start_date.required' => 'Please select a start date.',
            'end_date.required' => 'Please select an end date.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
        ];
    }
}
