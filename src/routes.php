<?php

/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
|
| The routes for examples.
|
*/

$exampleRoutes = config('form.example_routes');

if (is_null($exampleRoutes))
	$exampleRoutes = env('APP_ENV') != "production";

if ($exampleRoutes)
{
	Route::get('formation', 'Regulus\Formation\Controllers\ExamplesController@index');
}