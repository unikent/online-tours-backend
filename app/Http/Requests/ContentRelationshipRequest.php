<?php namespace App\Http\Requests;

use App\Http\Helpers\Content\ContentType;
use App\Models\Content;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Input;

class ContentRelationshipRequest extends FormRequest {


    public function rules()
    {

        $rules =  [
            'owner' => 'required|integer',
            'owner_type' => 'required|in:leaf,tour,page'
        ];


        return $rules;
    }

    public function authorize()
    {
        return true; //handled by middleware
    }
}
