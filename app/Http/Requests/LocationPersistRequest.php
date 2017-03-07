<?php namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocationPersistRequest extends FormRequest {
    public function rules()
    {
        $rules =  [
            'id' => 'sometimes|required|exists:location,id',
            'name' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'disabled_go_url' => 'url'
        ];

        return $rules;
    }

    public function authorize()
    {
        return true;
    }
}
