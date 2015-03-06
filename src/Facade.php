<?php namespace Regulus\Formation;

class Facade extends \Illuminate\Support\Facades\Facade {

	protected static function getFacadeAccessor() { return 'Regulus\Formation\Formation'; }

}