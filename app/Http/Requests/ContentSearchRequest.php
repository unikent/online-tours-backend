<?php namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Content;

class ContentSearchRequest extends FormRequest {


    public function rules()
    {
        $rules =  [
            'search' => 'string|max:255',
            'page' => 'integer',
            'type'  =>  'sometimes|required|in:' . implode(',', Content::getTypes()),
            'owner' => 'required_with:owner_type|integer',
            'owner_type' => 'required_with:owner|in:leaf,tour,page'
        ];
        return $rules;
    }

    public function authorize()
    {
        return true; //handled by middleware
    }
}
