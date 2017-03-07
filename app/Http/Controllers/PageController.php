<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Req;

class PageController extends Controller {


    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$pages = Page::all();

        return $this->layout('pages.page.index', array('pages'=>$pages));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
	    $page=new Page();

        return $this->layout('pages.page.create', array('page'=>$page));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{

        $this->validate($request, [
            'title' => 'required|string|max:255',
        ]);

        $page=new Page();
        $page->title = Input::get('title');
        if($page->save()) {
            if($request->ajax() || $request->format()==='json'){
                return response()->json($page);
            }else {
                Session::flash('alert', ['type' => 'success', 'message' => 'Page Created']);
                return Redirect::action('PageController@edit',[$page->id]);
            }
        }else{
            return $this->error('Unable to create page.',500);
        }
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  mixed  $slug_or_id
	 * @return Response
	 */
	public function show($slug_or_id)
	{
        return Redirect::action('PageController@edit',[$slug_or_id]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  mixed  $slug_or_id
	 * @return Response
	 */
	public function edit($slug_or_id)
	{

        $page = Page::fetchOrFail($slug_or_id);

        return $this->layout('pages.page.edit', array('page'=>$page));

	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  mixed  $slug_or_id
	 * @return Response
	 */
	public function update(Request $request, $slug_or_id)
	{

        try {
            $page = Page::fetchOrFail($slug_or_id);
        } catch (ModelNotFoundException $x){
            return $this->error('This page does not exist!',404);
        }

        $this->validate($request, [
            'title' => 'required|string|max:255',
        ]);

        $page->title = Input::get('title');


        if($page->save()) {
            if($request->ajax() || $request->format()==='json'){
                return response()->json(['success'=>true,'page'=>$page]);
            }else {
                Session::flash('alert', ['type' => 'success', 'message' => 'Page Updated']);
                return Redirect::action('PageController@index');
            }
        }else{
            return $this->error('Unable to update page.',500);
        }
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  mixed  $slug_or_id
	 * @return Response
	 */
	public function destroy($slug_or_id)
	{
        try {
            $page = Page::fetchOrFail($slug_or_id);
        } catch (ModelNotFoundException $x){
            return $this->error('This page does not exist!',404);
        }

        if($page->delete()) {

            Session::flash('alert', ['type' => 'success', 'message' => 'Page Deleted']);
            if(Req::ajax() || Req::format()==='json'){
                return response()->json([ 'redirect_to' => action('PageController@index') ], 200);
            }else{
                return Redirect::action('PageController@index');
            }
        }else{
            return $this->error('Unable to delete page.',500);
        }
	}

}
