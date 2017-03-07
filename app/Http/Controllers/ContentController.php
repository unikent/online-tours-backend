<?php namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Tour;
use App\Models\Page;
use App\Models\Leaf;
use App\Http\Requests\ContentPersistRequest;
use App\Http\Requests\ContentMoveRequest;
use App\Http\Requests\ContentSearchRequest;
use App\Http\Requests\ContentRelationshipRequest;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;

class ContentController extends Controller {

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('ajax');
    }

    public function update(ContentPersistRequest $request, $id)
    {

        /** @var $content Content */
        $content = Content::findOrFail($id);

        foreach(Input::all() as $field => $value){
            $content->{$field} = $value;
        }

        $saved =  $content->save();


        $html = view('components.content_form_single', ['content'=>$content])->render();

        return response()->json(['success' => $saved,'html'=>$html]);

    }

    public function store(ContentPersistRequest $request)
    {
        $type = Input::get('type');

        $class = 'App\\Models\\Content\\' . ucfirst($type);
        $content = new $class;

        $owner = Input::get('owner');
        $owner_type = Input::get('owner_type');


        foreach(Input::except('owner','owner_type') as $field => $value){
            $content->{$field} = $value;
        }

        $saved =  $content->save();

        if($saved && (!empty($owner)) && (!empty($owner_type))){
            $content->attachTo($owner,$owner_type);
        }

        $html = view('components.content_form_single', ['content'=>$content])->render();

        return response()->json(['success'=>$saved,'html'=>$html,'id'=>$content->id,'type'=>$content::TYPE]);

    }


    public function create()
    {
        $type = (Input::has('type') && in_array(Input::get('type'), Content::getTypes())) ? Input::get('type') : 'text';
        $class = 'App\\Models\\Content\\' . ucfirst($type);
        $content = new $class;

        $html = view('components.content_form_single', ['content'=>$content])->render();

        return response()->json(['success' => true, 'html' => $html, 'type' => $content::TYPE]);
    }

    public function edit($id)
    {
        $content = Content::findOrFail($id);

        $html = view('components.content_form_single', ['content'=>$content])->render();
        return response()->json(['success'=>true,'html'=>$html,'id'=>$content->id,'type'=>$content::TYPE]);
    }

    public function attach(ContentRelationshipRequest $request, $id)
    {

        $owner = Input::get('owner');
        $owner_type = Input::get('owner_type');

        $class= 'App\\Models\\' . ucfirst($owner_type);

        //fail early if owner doesnt exist
        $ownerObject = $class::findOrFail($owner);

        /** @var $content Content */
        $content = Content::findOrFail($id);

        $content->attachTo($owner,$owner_type);
        $html = view('components.content_form_single', ['content'=>$content])->render();
        return response()->json(['success'=>true,'html'=>$html,'id'=>$content->id,'type'=>$content::TYPE]);

    }

    public function detach(ContentRelationshipRequest $request, $id)
    {

        $owner = Input::get('owner');
        $owner_type = Input::get('owner_type');

        $class= 'App\\Models\\' . ucfirst($owner_type);

        //fail early if owner doesnt exist
        $ownerObject = $class::findOrFail($owner);

        /** @var $content Content */
        $content = Content::findOrFail($id);

        $success = $content->detachFrom($owner,$owner_type);
        return response()->json(['success'=>$success]);


    }

    public function order(ContentMoveRequest $request)
    {
        $owner = Input::get('owner');
        $owner_type = Input::get('owner_type');
        $content_ids = Input::get('content');

        $class= 'App\\Models\\' . ucfirst($owner_type);

        /** @var $owner Leaf|Tour|Page */
        $owner = $class::findOrFail($owner);

        $contents=[];
        foreach($content_ids as $i => $id){
            $contents[$id]= ['sequence'=>($i+1)];
        }

        $owner->contents()->sync($contents);

        return response()->json(['success'=>true]);
    }


    public function search(ContentSearchRequest $request)
    {
        $s = Input::get('search');
        $type = Input::get('type');
        $owner = Input::get('owner');
        $owner_type = Input::get('owner_type');

        $search = '%' . trim($s) .'%';

        $class= 'App\\Models\\' . ucfirst($owner_type);

        $owner = class_exists($class)?$class::find($owner):false;;
        $currentContents = [];

        if($owner){
            $currentContents = $owner->contents->modelKeys();
        }

        $contentClass = 'App\\Models\\Content\\' . ucfirst($type);

        $contents = Content::where(function($query) use ($currentContents,$type){
            if(!empty($currentContents)) {
                $query->whereNotIn('id', $currentContents);
            }
            if(!empty($type)){
                $query->where('type','=',$type);
            }

        })->where(function($query) use ($contentClass,$s,$search,$type){
            if(!empty(trim($s))) {
                $query->where('name', 'LIKE', $search);
            }
            if(class_exists($contentClass)){
                $query = $contentClass::refineSearch($query, $search);
            }

        })->paginate(10);

        $results = [];
        foreach($contents as $c) {
            $results[] = $c->getForSearch();
        }

        return response()->json(['more'=>$contents->hasMorePages(),'items'=>$results]);
    }

    public function destroy($id){

        $content = Content::findOrFail($id);

        /* @var Content $content */
        $deleted = $content->delete();
        return response()->json(['success'=>$deleted]);

    }

}
