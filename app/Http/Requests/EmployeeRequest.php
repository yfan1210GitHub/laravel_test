<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;


class EmployeeRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    // Command 1st due havent do authorize !!!
    // public function authorize()
    // {
    //     return true;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'first_name'  => ['required', 'max:255'],
            'last_name'   => ['required', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:50', Rule::unique('employee', 'email')->ignore($this->employee ?? 0)->whereNull('deleted_at')],
            'company_id'  => ['exists:companies,id'],
            'status'      => ['required', 'string', 'max:15'],
            'contact_num' => ['required', 'string', 'max:11'],
        ];
    }
}
