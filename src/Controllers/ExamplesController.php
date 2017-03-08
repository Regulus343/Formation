<?php namespace Regulus\Formation\Controllers;

use Illuminate\Routing\Controller;

class ExamplesController {

	public function index()
	{
		return view('vendor.formation.examples');
	}

}