<?php namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TourPersistRequest extends FormRequest {
    public function rules()
    {
        $rules = [
            'leaf_id' => 'required|exists:leaf,id',
            'name'        => 'required|max:255',
            'description' => 'required|string',
            'duration'    => 'required|integer',
            'items'       => 'required|string|min:1',
        ];

        return $rules;
    }

    public function authorize()
    {
        return true;
    }

    public function messages(){
        return [
            'items.array' => 'You must select at least one POI',
            'items.min' => 'You must select at least one POI',
        ];
    }
}
