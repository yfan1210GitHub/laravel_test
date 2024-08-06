<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;


class CompanyRequest extends FormRequest
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
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:50', Rule::unique('companies', 'email')->ignore($this->company ?? 0)->whereNull('deleted_at')],
            'address'     => ['required', 'string', 'max:255'],
            'website'     => ['required', 'string', 'max:255'],
            'logo'        => ['required', 'string', 'max:255'],
            'status'      => ['required', 'string', 'max:15'],
            'contact_num' => ['required', 'string', 'max:11'],
        ];
    }
}
