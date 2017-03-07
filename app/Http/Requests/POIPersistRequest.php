<?php namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class POIPersistRequest extends FormRequest {
    public function rules()
    {
        $rules = [
            'name' => 'required_without:parent_id|string|max:255',
            'location_id' => 'required_without:parent_id|exists:location,id',
            'parent_id' => 'sometimes|integer',
        ];

        return $rules;
    }

    public function authorize()
    {
        return true;
    }
}
