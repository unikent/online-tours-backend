<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// ZONES
// **********************************************************************************
//make this home
Route::get('/', 'ZoneController@index');

//publish staging db to live
Route::get('/publish','ZoneController@syncLive');

//order tours within a zone
Route::patch('/zone/{id_or_slug}/tours/order', 'ZoneController@orderTours');

Route::resource('zone', 'ZoneController', [ 'except' => [ 'index' ] ]);


// POI (LEAF/LEAVES)
// **********************************************************************************
//optionally open branch of tree
Route::get('/poi/{id?}', 'POIController@index')->where('id', '[0-9]+');

//create a leaf with under a specified parent
Route::get('/poi/create/{id?}', 'POIController@create');

Route::resource('poi', 'POIController', [ 'except' => [ 'index', 'create', 'show' ] ]);

// LOCATIONS
// **********************************************************************************
Route::resource('location', 'LocationController', [ 'except' => ['show' ] ]);

// CONTENT
// **********************************************************************************

// - Add existing to a group
Route::post('/content/{id}/attach','ContentController@attach');

// - Remove existing to a group
Route::post('/content/{id}/detach','ContentController@detach');

// - move content within group
Route::post('/content/order','ContentController@order');

// - search for content (select2)
Route::post('/content/search','ContentController@search');

Route::resource('content', 'ContentController', [ 'except' => [ 'index' ,'show' ] ]);

// TOURS
// **********************************************************************************
Route::resource('zone.tours', 'TourController', [ 'except' => ['show' ] ]);


// PAGES
// **********************************************************************************
Route::resource('page','PageController');


// API
// **********************************************************************************

Route::group([ 'prefix' => 'api', 'middleware' => [ 'json' ] ], function()
{
	Route::group([ 'prefix' => 'v1' ], function()
	{
		Route::group([ 'prefix' => '{connection}', 'middleware' => [ 'dbconn' ] ], function()
		{
			// primary
			Route::get('/zones', 'APIController@index');
			Route::get('/zone/{id}', 'APIController@zone');
			Route::get('/poi/{id}', 'APIController@poi');

			// extras
			Route::get('/tour/{id}', 'APIController@tour');
			Route::get('/zone/{id}/tours', 'APIController@tour_content');

	        Route::get('/page/{id_or_slug}', 'APIController@page');
		});
	});
});

// MISC
// **********************************************************************************

Route::controllers([
	'auth' => 'AuthController'
]);
