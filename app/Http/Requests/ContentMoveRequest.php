<?php namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ContentMoveRequest extends FormRequest {


    public function rules()
    {
        $rules =  [
            'owner' => 'required|integer',                  // On 'Content Group' (pivot data)
            'owner_type' => 'required|in:leaf,tour,page',   // On 'Content Group' (pivot data)
            'content' => 'required|array',                  // Array of Content IDs
        ];

        return $rules;
    }

    public function authorize()
    {
        return true; //handled by middleware
    }
}
