<?php namespace Aquanode\Formation;

/*----------------------------------------------------------------------------------------------------------
	Formation
		A powerful form creation composer package for Laravel 4.

		created by Cody Jassman / Aquanode - http://aquanode.com
		last updated on May 4, 2014
----------------------------------------------------------------------------------------------------------*/

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class Formation {

	/**
	 * The default values for form fields.
	 *
	 * @var array
	 */
	public static $defaults = array();

	/**
	 * The labels for form fields.
	 *
	 * @var array
	 */
	public static $labels = array();

	/**
	 * The access keys for form fields.
	 *
	 * @var array
	 */
	public static $accessKeys = array();

	/**
	 * The validation rules (routed through Formation's validation() method to Validator library to allow
	 * automatic addition of error classes to labels and fields).
	 *
	 * @var array
	 */
	public static $validation = array();

	/**
	 * The form fields to be validated.
	 *
	 * @var array
	 */
	public static $validationFields = array();

	/**
	 * The form errors.
	 *
	 * @var array
	 */
	public static $errors = array();

	/**
	 * Whether form fields are being reset to their default values rather than the POSTed values.
	 *
	 * @var bool
	 */
	public static $reset = false;

	/**
	 * The registered custom macros.
	 *
	 * @var array
	 */
	public static $macros = array();

	/**
	 * The request spoofer.
	 *
	 * @var string
	 */
	public static $spoofer = '_method';

	/**
	 * Cache application encoding locally to save expensive calls to config::get().
	 *
	 * @var string
	 */
	public static $encoding = null;

	/**
	 * Registers a custom macro.
	 *
	 * @param  string   $name
	 * @param  Closure  $macro
	 * @return void
	 */
	public static function macro($name, $macro)
	{
		static::$macros[$name] = $macro;
	}

	/**
	 * Assigns default values to form fields.
	 *
	 * @param  array    $defaults
	 * @param  array    $relationships
	 * @return void
	 */
	public static function setDefaults($defaults = array(), $relationships = array())
	{
		$defaultsArray = $defaults;

		//turn Eloquent collection into an array
		if (isset($defaults) && isset($defaults->incrementing) && isset($defaults->timestamps))
			$defaultsArray = $defaults->toArray();

		//turn object into array
		if (is_object($defaultsArray)) $defaultsArray = (array) $defaults;

		//format default values for times
		$defaults = static::formatDefaults($defaults);

		//add relationships data to defaults array if it is set
		if (!empty($relationships)) {
			$i = 0;
			foreach ($relationships as $relationship => $field) {
				if (count($defaults->{$relationship})) {
					$id = isset($defaults->{$relationship}->id) ? $defaults->{$relationship}->id : $i;

					if (is_bool($field) && $field) {
						if (isset($defaults->{$relationship}->{$field})) {
							foreach ($defaults->{$relationship} as $field => $value) {
								$defaultsArray[$relationship.'.'.$id.'.'.$field] = $value;
							}
						} else {
							foreach ($defaults->{$relationship} as $item) {
								$item = $item->toArray();
								$id   = isset($item['id']) ? $item['id'] : $i;
								foreach ($item as $field => $value) {
									if ($field == "pivot") {
										foreach ($value as $pivotField => $pivotValue) {
											$defaultsArray[$relationship.'.'.$id.'.'.$pivotField] = $pivotValue;
										}
									}

									$defaultsArray[$relationship.'.'.$id.'.'.$field] = $value;
								}
							}
						}
					} else {
						if (isset($defaults->{$relationship}->{$field})) {
							$defaultsArray[$relationship.'.'.$id] = $defaults->{$relationship}->{$field};
						} else {
							foreach ($defaults->{$relationship} as $item) {
								$id = isset($item->id) ? $item->id : $i;
								if (isset($item->{$field}))
									$defaultsArray[$relationship.'.'.$id] = $item->{$field};
							}
						}
					}

					$i ++;
				}
			}
		}

		static::$defaults = $defaultsArray;
		return static::$defaults;
	}

	/**
	 * Format default values for times.
	 *
	 * @param  array    $defaults
	 * @return void
	 */
	private static function formatDefaults($defaults = array())
	{
		foreach ($defaults as $field => $value) {
			$fieldArray = explode('.', $field);

			//divide any field that starts with "time" into "hour", "minutes", and "meridiem" fields
			if (substr(end($fieldArray), 0, 4) == "time") {
				$valueArray = explode(':', $value);
				if (count($valueArray) >= 2) {
					$defaults[$field.'_hour']     = $valueArray[0];
					$defaults[$field.'_minutes']  = $valueArray[1];
					$defaults[$field.'_meridiem'] = "am";
					if ($valueArray[0] >= 12) {
						$defaults[$field.'_hour']     -= 12;
						$defaults[$field.'_meridiem']  = "pm";
					}
				}
			}
		}
		return $defaults;
	}

	/**
	 * Get an array of all default values. Turns values with decimal notation names back into proper arrays.
	 *
	 * @param  mixed    $name
	 * @param  integer  $levelsDeep
	 * @param  boolean  $object
	 * @param  boolean  $id
	 * @return array
	 */
	public static function getDefaultsArrayX($name = null, $levelsDeep = 0, $object = false, $id = null)
	{
		$values    = array();

		foreach (static::$defaults as $field => $value) {
			$fieldNameArray = explode('.', $field);

			$add = false;
			if (!$name) {
				$add = true;
			} else {
				/*if (isset($values['id'])) {
					echo '<pre>';
					var_dump($values['id']);
					var_dump($fieldNameArray[1]);
					var_dump('------');
					echo '</pre><br /><br />';
				}*/
				\Regulus\Exterminator\Exterminator::a($id);
				\Regulus\Exterminator\Exterminator::a($name);
				\Regulus\Exterminator\Exterminator::a($fieldNameArray[0]);
				\Regulus\Exterminator\Exterminator::a($values);

				if ($name == $fieldNameArray[1]) {
					\Regulus\Exterminator\Exterminator::a('Tiger');
					//if (!is_null($id) && $id == $fieldNameArray[0]) {
						$add = true;
						\Regulus\Exterminator\Exterminator::a('Add!');
					//}
				}

				\Regulus\Exterminator\Exterminator::a('-----------------------');
			}

			if ($add) {
				if (count($fieldNameArray) > $levelsDeep + 1) {
					\Regulus\Exterminator\Exterminator::a('========');
					\Regulus\Exterminator\Exterminator::a($fieldNameArray);
					$id = is_numeric($fieldNameArray[1]) ? $fieldNameArray[1] : null;
					\Regulus\Exterminator\Exterminator::a($id);

					//\Regulus\Exterminator\Exterminator::a($fieldNameArray[1]);

					$values[$fieldNameArray[$levelsDeep]] = static::getDefaultsArray($fieldNameArray[1], ($levelsDeep + 1), $object, $id);
				} else {
					$values[$fieldNameArray[$levelsDeep]] = $value;
				}
			}
		}

		if (!$levelsDeep && is_string($name) && isset($values[$name]))
			$values = $values[$name];

		if ($object)
			return (object) $values;
		else
			return $values;
	}

	/**
	 * Get an array of all default values. Turns values with decimal notation names back into proper arrays.
	 *
	 * @param  mixed    $name
	 * @param  integer  $levelsDeep
	 * @param  boolean  $object
	 * @return array
	 */
	public static function getDefaultsArray($name = null, $levelsDeep = 0, $object = false, $matchId = null)
	{
		$values    = array();
		$nameArray = is_string($name) ? explode('.', $name) : null;

		foreach (static::$defaults as $field => $value) {
			$fieldNameArray = explode('.', $field);

			$add = false;
			$id  = (end($fieldNameArray) == "id") ? $value : null;

			/*echo '<pre>';
			var_dump($name);
			isset($fieldNameArray[$levelsDeep]) ? var_dump($fieldNameArray[$levelsDeep]) : var_dump('----');
			echo '</pre><br /><br />';*/

			//if (isset($fieldNameArray[$levelsDeep]) && !isset($values[$fieldNameArray[$levelsDeep]])) {
				if (!$name) {
					$add = true;
				} else {
					if ($nameArray[0] == $fieldNameArray[0]) {
						/*if (!isset($nameArray[1]) || !isset($fieldNameArray[1]) || $nameArray[1] == $fieldNameArray[1])
							$add = true;*/

						//$id = isset($fieldNameArray[1]) && is_numeric($fieldNameArray[1]) ? $fieldNameArray[1] : null;

						if (!$id || !$matchId || $id == $matchId)
							$add = true;

						/*echo '<pre>';
						var_dump($id);
						var_dump($nameArray);
						var_dump($fieldNameArray);
						var_dump($add);
						var_dump('------');
						echo '</pre><br /><br />';*/
					}
				}
			//}

			if ($add) {
				$fieldNameSegment = $fieldNameArray[$levelsDeep];

				if (count($fieldNameArray) > $levelsDeep + 1) {
					$values[$fieldNameSegment] = static::getDefaultsArray($fieldNameArray[0].'.'.$fieldNameArray[1], ($levelsDeep + 1), $object, $fieldNameSegment);
				} else {
					$values[$fieldNameSegment] = $value;
				}
			}
		}

		if (!$levelsDeep && is_string($name) && isset($values[$name]))
			$values = $values[$name];

		/*echo '<pre>';
		var_dump($values);
		var_dump(!isset($nameArray[1]) || !isset($fieldNameArray[1]) || $nameArray[1] == $fieldNameArray[1]);
		var_dump('------');
		echo '</pre><br /><br />';*/

		if ($object)
			return (object) $values;
		else
			return $values;
	}

	/**
	 * Get an object of all default values.
	 *
	 * @param  mixed    $name
	 * @param  integer  $levelsDeep
	 * @return object
	 */
	public static function getDefaultsObject($name = null, $levelsDeep = 0)
	{
		return static::getDefaultsArray($name, $levelsDeep, true);
	}

	/**
	 * Resets form field values back to defaults and ignores POSTed values.
	 *
	 * @param  array    $defaults
	 * @return void
	 */
	public static function resetDefaults($defaults = array())
	{
		if (!empty($defaults)) static::setDefaults($defaults); //if new defaults are set, pass them to static::$defaults
		static::$reset = true;
	}

	/**
	 * Assigns labels to form fields.
	 *
	 * @param  array    $labels
	 * @return void
	 */
	public static function setLabels($labels = array())
	{
		if (is_object($labels)) $labels = (array) $labels;
		static::$labels = $labels;
	}

	/**
	 * Route Validator validation rules through Formation to allow Formation
	 * to automatically add error classes to labels and fields.
	 *
	 * @param  array    $rules
	 * @return array
	 */
	public static function setValidationRules($rules = array())
	{
		static::$validationFields = array();

		$rulesFormatted = array();
		foreach ($rules as $name => $rulesItem) {
			static::$validationFields[] = $name;

			$rulesArray = explode('.', $name);
			$last = $rulesArray[(count($rulesArray) - 1)];
			if (count($rulesArray) < 2) {
				$rulesFormatted['root'][$last] = $rulesItem;
			} else {
				$rulesFormatted[str_replace('.'.$last, '', $name)][$last] = $rulesItem;
			}
		}

		foreach ($rulesFormatted as $name => $rules) {
			if ($name == "root") {
				static::$validation['root'] = Validator::make(Input::all(), $rules);
			} else {
				$data = Input::get($name);
				if (is_null($data)) $data = array();
				static::$validation[$name] = Validator::make($data, $rules);
			}
		}

		return static::$validation;
	}

	/**
	 * Check if one or all Validator instances are valid.
	 *
	 * @param  string   $index
	 * @return bool
	 */
	public static function validated($index = null)
	{
		//if index is null, cycle through all Validator instances
		if (is_null($index)) {
			foreach (static::$validation as $fieldName => $validation) {
				if ($validation->fails()) return false;
			}
		} else {
			if (substr($index, -1) == ".") { //index ends in "."; validate all fields that start with that index
				foreach (static::$validation as $fieldName => $validation) {
					if (substr($fieldName, 0, strlen($index)) == $index) {
						if ($validation->fails()) return false;
					}
				}
			} else {
				if (isset(static::$validation[$index])) {
					if (static::$validation[$index]->fails()) return false;
				} else {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Set up whole form with one big array
	 *
	 * @param  array    $form
	 * @return array
	 */
	public static function setup($form = array())
	{
		$labels = array();
		$rules = array();
		$defaults = array();

		if (is_object($form)) $form = (array) $form;
		foreach ($form as $name => $field) {
			if (is_object($field)) $field = (array) $field;
			if (isset($field[0]) && !is_null($field[0]) && $field[0] != "") $labels[$name]   = $field[0];
			if (isset($field[1]) && !is_null($field[1]) && $field[1] != "") $rules[$name]    = $field[1];
			if (isset($field[2]) && !is_null($field[2]) && $field[2] != "") $defaults[$name] = $field[2];
		}

		static::setLabels($labels);
		static::setValidationRules($rules);
		static::setDefaults($defaults);

		return static::$validation;
	}

	/**
	 * Open an HTML form.
	 *
	 * <code>
	 *		// Open a "POST" form to the current request URI
	 *		echo Form::open();
	 *
	 *		// Open a "POST" form to a given URI
	 *		echo Form::open('user/profile');
	 *
	 *		// Open a "PUT" form to a given URI
	 *		echo Form::open('user/profile', 'put');
	 *
	 *		// Open a form that has HTML attributes
	 *		echo Form::open('user/profile', 'post', array('class' => 'profile'));
	 * </code>
	 *
	 * @param  string   $action
	 * @param  string   $method
	 * @param  array    $attributes
	 * @param  bool     $https
	 * @return string
	 */
	public static function open($action = null, $method = 'POST', $attributes = array(), $https = null)
	{
		$method = strtoupper($method);

		$attributes['method'] = static::method($method);

		$attributes['action'] = static::action($action, $https);

		// If a character encoding has not been specified in the attributes, we will
		// use the default encoding as specified in the application configuration
		// file for the "accept-charset" attribute.
		if ( ! array_key_exists('accept-charset', $attributes))
		{
			$attributes['accept-charset'] = static::$encoding;
		}

		$append = '';

		// Since PUT and DELETE methods are not actually supported by HTML forms,
		// we'll create a hidden input element that contains the request method
		// and set the actual request method variable to POST.
		if ($method == 'PUT' or $method == 'DELETE')
		{
			$append = static::hidden(static::$spoofer, $method);
		}

		$html = '<form'.static::attributes($attributes).'>'.$append . "\n";
		if (Config::get('formation::autoCsrfToken')) {
			$html .= static::token();
		}
		return $html;
	}

	/**
	 * Determine the appropriate request method to use for a form.
	 *
	 * @param  string  $method
	 * @return string
	 */
	protected static function method($method)
	{
		return ($method !== 'GET') ? 'POST' : $method;
	}

	/**
	 * Determine the appropriate request method for a resource controller form.
	 *
	 * @param  mixed   $action
	 * @param  mixed   $controller
	 * @return string
	 */
	public static function methodResource($action = null, $controller = null)
	{
		$action = static::action($action);

		$method = "POST";
		$actionArray = explode('/', $action);
		$actionLastSegment = $actionArray[(count($actionArray) - 1)];

		if (is_numeric($actionLastSegment) || $actionLastSegment == "edit")
			$method = "PUT";

		if (!is_null($controller) && $actionLastSegment != $controller && $actionLastSegment != "create")
			$method = "PUT";

		return $method;
	}

	/**
	 * Determine the appropriate action parameter to use for a form.
	 *
	 * If no action is specified, the current request URI will be used.
	 *
	 * @param  string   $action
	 * @param  bool     $https
	 * @return string
	 */
	protected static function action($action = null, $https = false)
	{
		$uri = (is_null($action)) ? Request::fullUrl() : $action;

		return static::entities(URL::to($uri, $https));
	}

	/**
	 * Open an HTML form with an HTTPS action URI.
	 *
	 * @param  string  $action
	 * @param  string  $method
	 * @param  array   $attributes
	 * @return string
	 */
	public static function openSecure($action = null, $method = 'POST', $attributes = array())
	{
		return static::open($action, $method, $attributes, true);
	}

	/**
	 * Open an HTML form that accepts file uploads.
	 *
	 * @param  string  $action
	 * @param  string  $method
	 * @param  array   $attributes
	 * @param  bool    $https
	 * @return string
	 */
	public static function openForFiles($action = null, $method = 'POST', $attributes = array(), $https = false)
	{
		$attributes['enctype'] = 'multipart/form-data';

		return static::open($action, $method, $attributes, $https);
	}

	/**
	 * Open an HTML form that accepts file uploads with an HTTPS action URI.
	 *
	 * @param  string  $action
	 * @param  string  $method
	 * @param  array   $attributes
	 * @return string
	 */
	public static function openSecureForFiles($action = null, $method = 'POST', $attributes = array())
	{
		return static::openForFiles($action, $method, $attributes, true);
	}

	/**
	 * Open an HTML form that automatically corrects the action for a resource controller.
	 *
	 * @param  string  $action
	 * @param  array   $attributes
	 * @param  mixed   $controller
	 * @param  bool    $https
	 * @return string
	 */
	public static function openResource($action = null, $attributes = array(), $controller = null, $https = false)
	{
		$action = static::action($action, $https);

		//set method based on action
		$method = static::methodResource($action, $controller);

		//remove "create" suffix and whatever URI content may be appended to the end of the action
		if (preg_match('/\/create(.*)/', $action, $match))
			$action = str_replace($match[0], '', $action);

		//remove "create" and "edit" suffixes from action
		$action = str_replace('/create', '', str_replace('/edit', '', $action));

		return static::open($action, $method, $attributes, $https);
	}

	/**
	 * Open an HTML form for a resource controller with an HTTPS action URI.
	 *
	 * @param  string  $action
	 * @param  array   $attributes
	 * @param  mixed   $controller
	 * @return string
	 */
	public static function openResourceSecure($action = null, $attributes = array(), $controller = null)
	{
		return static::openResource($action, $attributes, $controller, true);
	}

	/**
	 * Open an HTML form for a resource controller that accepts file uploads.
	 *
	 * @param  string  $action
	 * @param  array   $attributes
	 * @param  mixed   $controller
	 * @param  bool    $https
	 * @return string
	 */
	public static function openResourceForFiles($action = null, $attributes = array(), $controller = null, $https = false)
	{
		$attributes['enctype'] = 'multipart/form-data';

		return static::openResource($action, $attributes, $controller, $https);
	}

	/**
	 * Open an HTML form for a resource controller that accepts file uploads with an HTTPS action URI.
	 *
	 * @param  string  $action
	 * @param  array   $attributes
	 * @param  mixed   $controller
	 * @return string
	 */
	public static function openResourceSecureForFiles($action = null, $attributes = array(), $controller = null)
	{
		$attributes['enctype'] = 'multipart/form-data';

		return static::openResourceForFiles($action, $attributes, $controller, true);
	}

	/**
	 * Close an HTML form.
	 *
	 * @return string
	 */
	public static function close()
	{
		return '</form>';
	}

	/**
	 * Generate a hidden field containing the current CSRF token.
	 *
	 * @return string
	 */
	public static function token()
	{
		return static::input('hidden', Config::get('formation::csrfToken'), Session::getToken());
	}

	/**
	 * Get the value of the form or of a form field array.
	 *
	 * @param  string  $name
	 * @param  string  $type
	 * @return mixed
	 */
	public static function values($name = null)
	{
		if (is_string($name))
			$name = str_replace('(', '', str_replace(')', '', $name));

		if ($_POST && !static::$reset) {
			return Input::get($name);
		} else if (Input::old($name) && !static::$reset) {
			return Input::old($name);
		} else {
			return static::getDefaultsArray($name);
		}
	}

	/**
	 * Get the value of the form field. If no POST data exists or reinitialize() has been called, default value
	 * will be used. Otherwise, POST value will be used. Using "checkbox" type ensures a boolean return value.
	 *
	 * @param  string  $name
	 * @param  string  $type
	 * @return mixed
	 */
	public static function value($name, $type = 'standard')
	{
		$name  = str_replace('(', '', str_replace(')', '', $name));
		$value = "";

		if (isset(static::$defaults[$name]))
			$value = static::$defaults[$name];

		if ($_POST && !static::$reset)
			$value = Input::get($name);

		if (Input::old($name) && !static::$reset)
			$value = Input::old($name);

		if ($type == "checkbox" && is_null($value)) $value = 0; //if type is "checkbox", use 0 for null values - this helps when using Form::value() to add values to an insert or update query

		return $value;
	}

	/**
	 * Get the time value from 3 individual fields created from the selectTime() method.
	 *
	 * @param  string  $name
	 * @param  string  $type
	 * @return mixed
	 */
	public static function valueTime($name)
	{
		if (substr($name, -1) != "_") $name .= "_";

		$hour     = Input::get($name.'hour');
		$minutes  = Input::get($name.'minutes');
		$meridiem = Input::get($name.'meridiem');

		if ($hour == 12)
			$hour = 0;

		if ($meridiem == "pm")
			$hour += 12;

		return sprintf('%02d', $hour).':'.sprintf('%02d', $minutes).':00';
	}

	/**
	 * Add values to a data object or array.
	 *
	 * @param  mixed   $values
	 * @param  array   $fields
	 * @return mixed
	 */
	public static function addValues($data = array(), $fields = array())
	{
		$associative = (bool) count(array_filter(array_keys((array) $fields), 'is_string'));

		if ($associative) {
			foreach ($fields as $field => $config) {
				$add = true;

				if (is_bool($config) || $config == "text") {
					$value = trim(static::value($field));

					if (!$config)
						$add = false;
				} else if (is_array($config)) {
					$value = trim(static::value($field));

					if (!in_array($value, $config))
						$add = false;
				} else if ($config == "checkbox") {
					$value = static::value($field, 'checkbox');
				}

				if ($add) {
					if (is_object($data))
						$data->{$field} = $value;
					else
						$data[$field]   = $value;
				}
			}
		} else {
			foreach ($fields as $field) {
				$value = trim(static::value($field));

				if (is_object($data))
					$data->{$field} = $value;
				else
					$data[$field]   = $value;
			}
		}

		return $data;
	}

	/**
	 * Add checkbox values to a data object or array.
	 *
	 * @param  mixed   $values
	 * @param  array   $checkboxes
	 * @return mixed
	 */
	public static function addCheckboxValues($data = array(), $checkboxes = array())
	{
		foreach ($checkboxes as $checkbox) {
			$value = static::value($checkbox, 'checkbox');

			if (is_object($data))
				$data->{$checkbox} = $value;
			else
				$data[$checkbox]   = $value;
		}

		return $data;
	}

	/**
	 * Check whether a checkbox is checked.
	 *
	 * @param  string  $name
	 * @return boolean
	 */
	public static function checked($name)
	{
		return static::value($name, 'checkbox');
	}

	/**
	 * Format array named form fields from strings with period notation for arrays ("data.id" = "data[id]")
	 *
	 * @param  string  $name
	 * @return string
	 */
	protected static function name($name)
	{
		//remove index number from between round brackets
		if (preg_match("/\((.*)\)/i", $name, $match)) $name = str_replace($match[0], '', $name);

		$nameArray = explode('.', $name);
		if (count($nameArray) < 2) return $name;

		$nameFormatted = $nameArray[0];
		for ($n=1; $n < count($nameArray); $n++) {
			$nameFormatted .= '['.$nameArray[$n].']';
		}
		return $nameFormatted;
	}

	/**
	 * Create an HTML label element.
	 *
	 * <code>
	 *		// Create a label for the "email" input element
	 *		echo Form::label('email', 'Email Address');
	 * </code>
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  array   $attributes
	 * @param  boolean $save
	 * @return string
	 */
	public static function label($name = null, $label = null, $attributes = array(), $save = true)
	{
		$attributes = static::addErrorClass($name, $attributes);

		if (!is_null($name) && $name != "") {
			if (is_null($label)) $label = static::nameToLabel($name);
		} else {
			if (is_null($label)) $label = "";
		}

		//save label in labels array if a label string contains any characters and $save is true
		if ($label != "" && $save) static::$labels[$name] = $label;

		//get ID of field for label's "for" attribute
		if (!isset($attributes['for'])) {
			$id = static::id($name);
			$attributes['for'] = $id;
		}

		//if any "{" characters are used, do not add "access" class for accesskey; Handlebars.js may be being used in field name or label
		if (preg_match('/\{/', $name)) $attributes['accesskey'] = false;

		//add label suffix
		$suffix = Config::get('formation::labelSuffix');
		if ($suffix != "" && (!isset($attributes['suffix']) || $attributes['suffix']))
			$label .= $suffix;

		if (isset($attributes['suffix']))
			unset($attributes['suffix']);

		//also do not add accesskey depiction if label already contains HTML tags or HTML special characters
		if ($label != strip_tags($label) || $label != static::entities($label)) {
			$attributes['accesskey'] = false;
		} else {
			$label = static::entities($label); //since there is no HTML present in label, convert entities to HTML special characters
		}

		//add accesskey
		$attributes = static::addAccessKey($name, $label, $attributes, false);

		//add "control-label" class
		if (!isset($attributes['control-label-class']) || $attributes['control-label-class']) {
			if (isset($attributes['class']) && $attributes['class'] != "") {
				$attributes['class'] .= ' control-label';
			} else {
				$attributes['class'] = 'control-label';
			}
		}
		if (isset($attributes['control-label-class'])) unset($attributes['control-label-class']);

		//add non-breakable space if label is empty
		if ($label == "") $label = "&nbsp;";

		if (is_array($attributes) && isset($attributes['accesskey'])) {
			if (is_string($attributes['accesskey'])) {
				$newLabel = preg_replace('/'.strtoupper($attributes['accesskey']).'/', '<span class="access">'.strtoupper($attributes['accesskey']).'</span>', $label, 1);
				if ($newLabel == $label) { //if nothing changed with replace, try lowercase
					$newLabel = preg_replace('/'.$attributes['accesskey'].'/', '<span class="access">'.$attributes['accesskey'].'</span>', $label, 1);
				}
				$label = $newLabel;
			}
			unset($attributes['accesskey']);
		}

		$attributes = static::attributes($attributes);

		return '<label'.$attributes.'>'.$label.'</label>' . "\n";
	}

	/**
	 * Create an HTML label element.
	 *
	 * <code>
	 *		// Create a label for the "email" input element
	 *		echo Form::label('email', 'E-Mail Address');
	 * </code>
	 *
	 * @param  string  $name
	 * @return string
	 */
	protected static function nameToLabel($name)
	{
		$nameArray = explode('.', $name);
		if (count($nameArray) < 2) {
			$nameFormatted = str_replace('_', ' ', $name);
		} else { //if field is an array, create label from last array index
			$nameFormatted = str_replace('_', ' ', $nameArray[(count($nameArray) - 1)]);
		}

		//convert icon code to markup
		if (preg_match('/\[ICON:(.*)\]/', $nameFormatted, $match)) {
			$nameFormatted = str_replace($match[0], '<span class="glyphicon glyphicon-'.str_replace(' ', '', $match[1]).'"></span>&nbsp; ', $nameFormatted);
		}

		if ($nameFormatted == strip_tags($nameFormatted)) $nameFormatted = ucwords($nameFormatted);
		return $nameFormatted;
	}

	/**
	 * Add an accesskey attribute to a field based on its name.
	 *
	 * @param  string  $name
	 * @param  string  $label
	 * @param  array   $attributes
	 * @param  boolean $returnLowercase
	 * @return array
	 */
	public static function addAccessKey($name, $label = null, $attributes = array(), $returnLowercase = true)
	{
		if (!isset($attributes['accesskey']) || (!is_string($attributes['accesskey']) && $attributes['accesskey'] === true)) {
			$accessKey = false;
			if (is_null($label)) {
				if (isset(static::$labels[$name])) {
					$label = static::$labels[$name];
				} else {
					$label = static::nameToLabel($name);
				}
			}

			$label = strtr($label, 'Ã Ã¡Ã¢Ã£Ã¤Ã§Ã¨Ã©ÃªÃ«Ã¬Ã­Ã®Ã¯Ã±Ã²Ã³Ã´ÃµÃ¶Ã¹ÃºÃ»Ã¼Ã½Ã¿Ã€ÃÃ‚ÃƒÃ„Ã‡ÃˆÃ‰ÃŠÃ‹ÃŒÃÃŽÃÃ‘Ã’Ã“Ã”Ã•Ã–Ã™ÃšÃ›ÃœÃ', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
			$ignoreCharacters = array(' ', '/', '!', '@', '#', '$', '%', '^', '*', '(', ')', '-', '_', '+', '=', '\\', '~', '?', '{', '}', '[', ']', '.');

			//first check to see if an accesskey is already set for this field
			foreach (static::$accessKeys as $character => $nameAccessKey) {
				if ($nameAccessKey == $name) $accessKey = $character;
			}

			//if no accesskey is set, loop through the field name's characters and set one
			for ($l=0; $l < strlen($label); $l++) {
				if (!$accessKey) {
					$character = strtolower($label[$l]);
					if (!isset(static::$accessKeys[$character]) && !in_array($character, $ignoreCharacters)) {
						static::$accessKeys[$character] = $name;
						$accessKey = $character;
					}
				}
			}

			if ($accessKey) {
				$attributes['accesskey'] = $accessKey;
				if ($returnLowercase) $attributes['accesskey'] = strtolower($attributes['accesskey']);
			}
		} else {
			if ($attributes['accesskey'] === false) unset($attributes['accesskey']); //allow ability to prevent accesskey by setting it to false
		}
		return $attributes;
	}

	/**
	 * Determine the ID attribute for a form element.
	 *
	 * @param  string  $name
	 * @param  array   $attributes
	 * @return string
	 */
	protected static function id($name, $attributes = array())
	{
		// If an ID has been explicitly specified in the attributes, we will
		// use that ID. Otherwise, we will look for an ID in the array of
		// label names so labels and their elements have the same ID.
		if (array_key_exists('id', $attributes)) {
			$id = $attributes['id'];
		} else {
			//replace array denoting periods and underscores with dashes
			$id = strtolower(str_replace('.', '-', str_replace('_', '-', str_replace(' ', '-', $name))));
		}

		//remove icon code
		if (preg_match('/\[ICON:(.*)\]/i', $id, $match)) {
			$id = str_replace($match[0], '', $id);
		}

		//remove round brackets that are used to prevent index number from appearing in field name
		$id = str_replace('(', '', str_replace(')', '', $id));

		//replace double dashes with single dash
		$id = str_replace('--', '-', $id);

		//remove end dash if one exists
		if (substr($id, -1) == "-")
			$id = substr($id, 0, (strlen($id) - 1));

		return $id;
	}

	/**
	 * Automatically set the field class for a field.
	 *
	 * @param  string  $name
	 * @param  array   $attributes
	 * @return array
	 */
	protected static function setFieldClass($name, $attributes = array())
	{
		$class = Config::get('formation::fieldClass');
		if ($class != "") {
			if (isset($attributes['class']) && $attributes['class'] != "") {
				$attributes['class'] .= ' '.$class;
			} else {
				$attributes['class'] = $class;
			}
		}
		return $attributes;
	}

	/**
	 * Automatically set a "placeholder" attribute for a field.
	 *
	 * @param  string  $name
	 * @param  array   $attributes
	 * @return array
	 */
	protected static function setFieldPlaceholder($name, $attributes = array())
	{
		$placeholder = Config::get('formation::autoFieldPlaceholder');
		if ($placeholder && !isset($attributes['placeholder'])) {
			$namePlaceholder = $name;
			if (isset(static::$labels[$name]) && static::$labels[$name] != "") {
				$namePlaceholder = static::$labels[$name];
			} else {
				$namePlaceholder = static::nameToLabel($name);
			}

			if (substr($namePlaceholder, -1) == ":")
				$namePlaceholder = substr($namePlaceholder, 0, (strlen($namePlaceholder) - 1));

			$attributes['placeholder'] = $namePlaceholder;
		}
		return $attributes;
	}

	/**
	 * Build a list of HTML attributes from an array.
	 *
	 * @param  array   $attributes
	 * @return string
	 */
	public static function attributes($attributes)
	{
		$html = array();

		foreach ((array) $attributes as $key => $value)
		{
			// For numeric keys, we will assume that the key and the value are the
			// same, as this will convert HTML attributes such as "required" that
			// may be specified as required="required", etc.
			if (is_numeric($key)) $key = $value;

			if ( ! is_null($value))
			{
				$html[] = $key.'="'.static::entities($value).'"';
			}
		}

		return (count($html) > 0) ? ' '.implode(' ', $html) : '';
	}

	/**
	 * Convert HTML characters to entities.
	 *
	 * The encoding specified in the application configuration file will be used.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function entities($value)
	{
		return htmlentities($value, ENT_QUOTES, Config::get('formation::encoding'), false);
	}

	/**
	 * Create a field along with a label and error message (if one is set).
	 *
	 * @param  string  $name
	 * @param  string  $type
	 * @param  array   $attributes
	 * @return string
	 */
	public static function field($name, $type = null, $attributes = array())
	{
		//set any field named "submit" to a "submit" field automatically and set it's type to attributes to
		//to simplify creation of "submit" fields with field() macro
		if ($name == "submit") {
			if (is_array($type)) {
				$name = null;
				$attributes = $type;
				$type = "submit";
			}

			$types = array('text', 'search', 'password', 'url', 'number', 'date', 'textarea', 'hidden', 'select', 'checkbox', 'radio', 'checkbox-set', 'radio-set', 'file', 'button', 'submit');
			if (!is_array($type) && !in_array($type, array($types))) {
				$name = $type;
				$type = "submit";
				$attributes = array();
			}
		}

		//allow label to be set via attributes array (defaults to labels array and then to a label derived from the field's name)
		$fieldLabel = Config::get('formation::autoFieldLabel');
		if (!is_null($name)) {
			$label = static::nameToLabel($name);
		} else {
			$label = $name;
		}
		if (is_array($attributes) && isset($attributes['label'])) {
			$label = $attributes['label'];
			unset($attributes['label']);
			$fieldLabel = true;
		}
		if (is_null($label)) $fieldLabel = false;

		if (!is_array($attributes)) $attributes = array();

		//allow options for select, radio-set, and checkbox-set to be set via attributes array
		$options = array();
		if (isset($attributes['options'])) {
			$options = $attributes['options'];
			unset($attributes['options']);
		}

		///allow the null option ("Select a ...") for a select field to be set via attributes array
		$nullOption = null;
		if (isset($attributes['null-option'])) {
			$nullOption = $attributes['null-option'];
			unset($attributes['null-option']);
		}

		///allow the field's value to be set via attributes array
		$value = null;
		if (isset($attributes['value'])) {
			$value = $attributes['value'];
			unset($attributes['value']);
		}

		//set any field named "password" to a "password" field automatically; no type declaration required
		if (substr($name, 0, 8) == "password" && is_null($type)) $type = "password";

		//if type is still null, assume it to be a regular "text" field
		if (is_null($type)) $type = "text";

		//set attributes up for label and field (remove element-specific attributes from label and vice versa)
		$attributesLabel = array();
		foreach ($attributes as $key => $attribute) {
			if (substr($key, -6) != "-field" && substr($key, -10) != "-container" && $key != "id") {
				$key = str_replace('-label', '', $key);
				$attributesLabel[$key] = $attribute;
			}
			if (($key == "id" || $key == "id-field") && !isset($attributes['for'])) {
				$attributesLabel['for'] = $attribute;
			}
		}

		$attributesField = array();
		foreach ($attributes as $key => $attribute) {
			if (substr($key, -6) != "-label" && substr($key, -16) != "-field-container") {
				$key = str_replace('-field', '', $key);
				$attributesField[$key] = $attribute;
			}
		}

		$attributesFieldContainer = array();
		foreach ($attributes as $key => $attribute) {
			if (substr($key, -16) == "-field-container") {
				$key = str_replace('-field-container', '', $key);
				$attributesFieldContainer[$key] = $attribute;
			}
		}
		if (!isset($attributesFieldContainer['class']) || $attributesFieldContainer['class'] == "") {
			$attributesFieldContainer['class'] = Config::get('formation::fieldContainerClass');
		} else {
			$attributesFieldContainer['class'] .= ' '.Config::get('formation::fieldContainerClass');
		}
		if (!isset($attributesFieldContainer['id'])) {
			$attributesFieldContainer['id'] = static::id($name, $attributesFieldContainer).'-area';
		} else {
			if (is_null($attributesFieldContainer['id']) || !$attributesFieldContainer['id'])
				unset($attributesFieldContainer['id']);
		}

		if ($type == "checkbox") $attributesFieldContainer['class'] .= ' checkbox';
		if ($type == "radio")    $attributesFieldContainer['class'] .= ' radio';
		if ($type == "hidden")   $attributesFieldContainer['class'] .= ' hidden';

		$attributesFieldContainer = static::addErrorClass($name, $attributesFieldContainer);

		$html = '<'.Config::get('formation::fieldContainer').static::attributes($attributesFieldContainer).'>' . "\n";
		switch ($type) {
			case "text":
				if ($fieldLabel) $html .= static::label($name, $label, $attributesLabel);
				$html .= static::text($name, $value, $attributesField) . "\n";
				break;
			case "search":
				if ($fieldLabel) $html .= static::label($name, $label, $attributesLabel);
				$html .= static::search($name, $value, $attributesField) . "\n";
				break;
			case "password":
				if ($fieldLabel) $html .= static::label($name, $label, $attributesLabel);
				$html .= static::password($name, $attributesField) . "\n";
				break;
			case "url":
				if ($fieldLabel) $html .= static::label($name, $label, $attributesLabel);
				$html .= static::url($name, $value, $attributesField) . "\n";
				break;
			case "number":
				if ($fieldLabel) $html .= static::label($name, $label, $attributesLabel);
				$html .= static::number($name, $value, $attributesField) . "\n";
				break;
			case "date":
				if ($fieldLabel) $html .= static::label($name, $label, $attributesLabel);
				$html .= static::date($name, $value, $attributesField) . "\n";
				break;
			case "textarea":
				if ($fieldLabel) $html .= static::label($name, $label, $attributesLabel);
				$html .= static::textarea($name, $value, $attributesField);
				break;
			case "hidden":
				$html .= static::hidden($name, $value, $attributesField);
				break;
			case "select":
				if ($fieldLabel) $html .= static::label($name, $label, $attributesLabel);
				$html .= static::select($name, $options, $nullOption, $value, $attributesField);
				break;
			case "checkbox":
				if (is_null($value)) $value = 1;
				if (isset($attributesLabel['class'])) {
					$attributesLabel['class'] .= " checkbox";
				} else {
					$attributesLabel['class']  = "checkbox";
				}
				$html .= '<label>'.static::checkbox($name, $value, false, $attributesField).' '.$label.'</label>';
				break;
			case "radio":
				if (isset($attributesLabel['class'])) {
					$attributesLabel['class'] .= " radio";
				} else {
					$attributesLabel['class']  = "radio";
				}
				$html .= '<label>'.static::radio($name, $value, false, $attributesField).' '.$label.'</label>';
				break;
			case "checkbox-set":
				//for checkbox set, use options as array of checkbox names
				if ($fieldLabel) $html .= static::label(null, $label, $attributesLabel);

				$html .= static::checkboxSet($options, $name, $attributesField);
				break;
			case "radio-set":
				if ($fieldLabel) $html .= static::label(null, $label, $attributesLabel);
				$html .= static::radioSet($name, $options, null, $attributesField);
				break;
			case "file":
				if ($fieldLabel) $html .= static::label($name, $label, $attributesLabel);
				$html .= static::file($name, $attributesField) . "\n";
				break;
			case "button":
				$html .= static::button($label, $attributesField);
				break;
			case "submit":
				$html .= static::submit($label, $attributesField);
				break;
		}
		$html .= static::error($name) . "\n";
		if (Config::get('formation::fieldContainerClear')) $html .= '<div class="clear"></div>' . "\n";
		$html .= '</'.Config::get('formation::fieldContainer').'>' . "\n";
		return $html;
	}

	/**
	 * Create an HTML input element.
	 *
	 * <code>
	 *		// Create a "text" input element named "email"
	 *		echo Form::input('text', 'email');
	 *
	 *		// Create an input element with a specified default value
	 *		echo Form::input('text', 'email', 'example@gmail.com');
	 * </code>
	 *
	 * @param  string  $type
	 * @param  string  $name
	 * @param  mixed   $value
	 * @param  array   $attributes
	 * @return string
	 */
	public static function input($type, $name, $value = null, $attributes = array())
	{
		if (!in_array($type, array('hidden', 'checkbox', 'radio'))) {
			//add the field class if config option is set
			$attributes = static::setFieldClass($name, $attributes);

			//automatically set placeholder attribute if config option is set
			$attributes = static::setFieldPlaceholder($name, $attributes);
		}

		//remove "placeholder" attribute if it is set to false
		if (isset($attributes['placeholder']) && !$attributes['placeholder'])
			unset($attributes['placeholder']);

		$name = (isset($attributes['name'])) ? $attributes['name'] : $name;
		$attributes = static::addErrorClass($name, $attributes);

		$attributes['id'] = static::id($name, $attributes);
		if (isset($attributes['id']) && (!$attributes['id'] || $attributes['id'] == ""))
			unset($attributes['id']);

		if (is_null($value) && $type != "password") $value = static::value($name);

		$name = static::name($name);

		if ($type != "hidden") $attributes = static::addAccessKey($name, null, $attributes);

		$attributes = array_merge($attributes, compact('type', 'name', 'value'));

		return '<input'.static::attributes($attributes).'>' . "\n";
	}

	/**
	 * Create an HTML text input element.
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  array   $attributes
	 * @return string
	 */
	public static function text($name, $value = null, $attributes = array())
	{
		return static::input('text', $name, $value, $attributes);
	}

	/**
	 * Create an HTML password input element.
	 *
	 * @param  string  $name
	 * @param  array   $attributes
	 * @return string
	 */
	public static function password($name, $attributes = array())
	{
		return static::input('password', $name, null, $attributes);
	}

	/**
	 * Create an HTML hidden input element.
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  array   $attributes
	 * @return string
	 */
	public static function hidden($name, $value = null, $attributes = array())
	{
		return static::input('hidden', $name, $value, $attributes);
	}

	/**
	 * Create an HTML search input element.
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  array   $attributes
	 * @return string
	 */
	public static function search($name, $value = null, $attributes = array())
	{
		return static::input('search', $name, $value, $attributes);
	}

	/**
	 * Create an HTML email input element.
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  array   $attributes
	 * @return string
	 */
	public static function email($name, $value = null, $attributes = array())
	{
		return static::input('email', $name, $value, $attributes);
	}

	/**
	 * Create an HTML telephone input element.
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  array   $attributes
	 * @return string
	 */
	public static function telephone($name, $value = null, $attributes = array())
	{
		return static::input('tel', $name, $value, $attributes);
	}

	/**
	 * Create an HTML URL input element.
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  array   $attributes
	 * @return string
	 */
	public static function url($name, $value = null, $attributes = array())
	{
		return static::input('url', $name, $value, $attributes);
	}

	/**
	 * Create an HTML number input element.
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  array   $attributes
	 * @return string
	 */
	public static function number($name, $value = null, $attributes = array())
	{
		return static::input('number', $name, $value, $attributes);
	}

	/**
	 * Create an HTML date input element.
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  array   $attributes
	 * @return string
	 */
	public static function date($name, $value = null, $attributes = array())
	{
		return static::input('date', $name, $value, $attributes);
	}

	/**
	 * Create an HTML file input element.
	 *
	 * @param  string  $name
	 * @param  array   $attributes
	 * @return string
	 */
	public static function file($name, $attributes = array())
	{
		return static::input('file', $name, null, $attributes);
	}

	/**
	 * Create an HTML textarea element.
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  array   $attributes
	 * @return string
	 */
	public static function textarea($name, $value = null, $attributes = array())
	{
		$attributes['name'] = $name;
		$attributes['id'] = static::id($name, $attributes);

		//add the field class if config option is set
		$attributes = static::setFieldClass($name, $attributes);

		//automatically set placeholder attribute if config option is set
		$attributes = static::setFieldPlaceholder($name, $attributes);

		$attributes = static::addErrorClass($name, $attributes);

		if (is_null($value)) $value = static::value($name);
		if (is_null($value)) $value = ''; //if value is still null, set it to an empty string

		$attributes['name'] = static::name($attributes['name']);

		$attributes = static::addAccessKey($name, null, $attributes);

		return '<textarea'.static::attributes($attributes).'>'.static::entities($value).'</textarea>' . "\n";
	}

	/**
	 * Create an HTML select element.
	 *
	 * <code>
	 *		// Create a HTML select element filled with options
	 *		echo Form::select('sizes', array('S' => 'Small', 'L' => 'Large'));
	 *
	 *		// Create a select element with a default selected value
	 *		echo Form::select('sizes', array('S' => 'Small', 'L' => 'Large'), 'Select a size', 'L');
	 * </code>
	 *
	 * @param  string  $name
	 * @param  array   $options
	 * @param  string  $nullOption
	 * @param  string  $selected
	 * @param  array   $attributes
	 * @return string
	 */
	public static function select($name, $options = array(), $nullOption = null, $selected = null, $attributes = array())
	{
		if (!isset($attributes['id'])) $attributes['id'] = static::id($name, $attributes);
		$attributes['name'] = $name;
		$attributes = static::addErrorClass($name, $attributes);

		//add the field class if config option is set
		$attributes = static::setFieldClass($name, $attributes);

		if (is_null($selected)) $selected = static::value($name);

		$html = array();
		if (!is_null($nullOption)) $html[] = static::option('', $nullOption, $selected);
		foreach ($options as $value => $display) {
			$value = str_replace('[DUPLICATE]', '', $value); //allow the possibility of the same value appearing in the options array twice by appending "[DUPLICATE]" to its key

			if (is_array($display)) {
				$html[] = static::optgroup($display, $value, $selected);
			} else {
				$html[] = static::option($value, $display, $selected);
			}
		}

		$attributes['name'] = static::name($attributes['name']);

		$attributes = static::addAccessKey($name, null, $attributes);

		return '<select'.static::attributes($attributes).'>'.implode("\n", $html). "\n" .'</select>' . "\n";
	}

	/**
	 * Create an HTML select element optgroup.
	 *
	 * @param  array   $options
	 * @param  string  $label
	 * @param  string  $selected
	 * @return string
	 */
	protected static function optgroup($options, $label, $selected)
	{
		$html = array();

		foreach ($options as $value => $display) {
			$html[] = static::option($value, $display, $selected);
		}

		return '<optgroup label="'.static::entities($label).'">'.implode('', $html).'</optgroup>';
	}

	/**
	 * Create an HTML select element option.
	 *
	 * @param  string  $value
	 * @param  string  $display
	 * @param  string  $selected
	 * @return string
	 */
	protected static function option($value, $display, $selected)
	{
		if (is_array($selected)) {
			$selected = (in_array($value, $selected)) ? 'selected' : null;
		} else {
			$selected = ((string) $value == (string) $selected) ? 'selected' : null;
		}
		$attributes = array('value' => static::entities($value), 'selected' => $selected);

		return '<option'.static::attributes($attributes).'>'.static::entities($display).'</option>';
	}

	/**
	 * Create a set of select boxes for times.
	 *
	 * @param  string  $name
	 * @param  array   $options
	 * @param  string  $nullOption
	 * @param  string  $selected
	 * @param  array   $attributes
	 * @return string
	 */
	public static function selectTime($namePrefix = 'time', $selected = null, $attributes = array())
	{
		$html = "";
		if ($namePrefix != "" && substr($namePrefix, -1) != "_") $namePrefix .= "_";

		//create hour field
		$hoursOptions = array();
		for ($h=0; $h <= 12; $h++) {
			$hour = sprintf('%02d', $h);
			if ($hour == 12) {
				$hoursOptions[$hour.'[DUPLICATE]'] = $hour;
			} else {
				if ($h == 0) $hour = 12;
				$hoursOptions[$hour] = $hour;
			}
		}
		$attributesHour = $attributes;
		if (isset($attributesHour['class'])) {
			$attributesHour['class'] .= " time time-hour";
		} else {
			$attributesHour['class'] = "time time-hour";
		}
		$html .= static::select($namePrefix.'hour', $hoursOptions, null, null, $attributesHour);

		$html .= '<span class="time-hour-minutes-separator">:</span>' . "\n";

		//create minutes field
		$minutesOptions = array();
		for ($m=0; $m < 60; $m++) {
			$minute = sprintf('%02d', $m);
			$minutesOptions[$minute] = $minute;
		}
		$attributesMinutes = $attributes;
		if (isset($attributesMinutes['class'])) {
			$attributesMinutes['class'] .= " time time-minutes";
		} else {
			$attributesMinutes['class'] = "time time-minutes";
		}
		$html .= static::select($namePrefix.'minutes', $minutesOptions, null, null, $attributesMinutes);

		//create meridiem field
		$meridiemOptions = static::simpleOptions(array('am', 'pm'));
		$attributesMeridiem = $attributes;
		if (isset($attributesMeridiem['class'])) {
			$attributesMeridiem['class'] .= " time time-meridiem";
		} else {
			$attributesMeridiem['class'] = "time time-meridiem";
		}
		$html .= static::select($namePrefix.'meridiem', $meridiemOptions, null, null, $attributesMeridiem);

		return $html;
	}

	/**
	 * Create a set of HTML checkboxes.
	 *
	 * @param  array   $names
	 * @param  string  $namePrefix
	 * @param  array   $attributes
	 * @return string
	 */
	public static function checkboxSet($names = array(), $namePrefix = null, $attributes = array())
	{
		if (!empty($names) && is_array($names)) {
			$containerAttributes = array('class'=> 'checkbox-set');
			foreach ($attributes as $attribute => $value) {

				//appending "-container" to attributes means they apply to the
				//"checkbox-set" container rather than to the checkboxes themselves
				if (substr($attribute, -10) == "-container") {
					if (str_replace('-container', '', $attribute) == "class") {
						$containerAttributes['class'] .= ' '.$value;
					} else {
						$containerAttributes[str_replace('-container', '', $attribute)] = $value;
					}
					unset($attributes[$attribute]);
				}
			}
			$containerAttributes = static::addErrorClass('roles', $containerAttributes);
			$html = '<div'.static::attributes($containerAttributes).'>';

			foreach ($names as $name => $display) {
				//if a simple array is used, automatically create the label from the name
				$associativeArray = true;
				if (isset($attributes['associative'])) {
					if (!$attributes['associative'])
						$associativeArray = false;
				} else {
					if (is_numeric($name))
						$associativeArray = false;
				}
				if (!$associativeArray) {
					$name = $display;
					$display = static::nameToLabel($name);
				}

				if (isset($attributes['name-values']) && $attributes['name-values']) {
					$value = $name;
				} else {
					$value = 1;
				}

				if (!is_null($namePrefix)) $name = $namePrefix . $name;

				if ($value == static::value($name, 'checkbox')) {
					$checked = true;
				} else {
					$checked = false;
				}

				//add selected class to list item if checkbox is checked to allow styling for selected checkboxes in set
				$subContainerAttributes = array('class' => 'checkbox');
				if ($checked) $subContainerAttributes['class'] .= ' selected';
				$checkbox = '<div'.static::attributes($subContainerAttributes).'>' . "\n";

				$checkboxAttributes = $attributes;
				$checkboxAttributes['id'] = static::id($name);

				if (isset($checkboxAttributes['associative'])) unset($checkboxAttributes['associative']);
				if (isset($checkboxAttributes['name-values'])) unset($checkboxAttributes['name-values']);

				$checkbox .= static::checkbox($name, $value, $checked, $checkboxAttributes);
				$checkbox .= static::label($name, $display, array('accesskey' => false));

				$checkbox .= '</div>' . "\n";
				$html .= $checkbox;
			}

			$html .= '</div>' . "\n";
			return $html;
		}
	}

	/**
	 * Create an HTML checkbox input element.
	 *
	 * <code>
	 *		// Create a checkbox element
	 *		echo Form::checkbox('terms', 'yes');
	 *
	 *		// Create a checkbox that is selected by default
	 *		echo Form::checkbox('terms', 'yes', true);
	 * </code>
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  bool    $checked
	 * @param  array   $attributes
	 * @return string
	 */
	public static function checkbox($name, $value = 1, $checked = false, $attributes = array())
	{
		if ($value == static::value($name)) $checked = true;

		if (!isset($attributes['id'])) $attributes['id'] = static::id($name, $attributes);
		$name = static::name($name);

		return static::checkable('checkbox', $name, $value, $checked, $attributes);
	}

	/**
	 * Create a set of HTML radio buttons.
	 *
	 * @param  string  $name
	 * @param  array   $options
	 * @param  string  $selected
	 * @param  array   $attributes
	 * @return string
	 */
	public static function radioSet($name, $options = array(), $selected = null, $attributes = array())
	{
		if (!empty($options) && is_array($options)) {
			$containerAttributes = array('class'=> 'radio-set');
			foreach ($attributes as $attribute => $value) {

				//appending "-container" to attributes means they apply to the
				//"radio-set" container rather than to the checkboxes themselves
				if (substr($attribute, -10) == "-container") {
					if (str_replace('-container', '', $attribute) == "class") {
						$containerAttributes['class'] .= ' '.$value;
					} else {
						$containerAttributes[str_replace('-container', '', $attribute)] = $value;
					}
					unset($attributes[$attribute]);
				}
			}
			$containerAttributes = static::addErrorClass($name, $containerAttributes);
			$html = '<div'.static::attributes($containerAttributes).'>';

			$label = static::label($name); //set dummy label so ID can be created in line below
			$idPrefix = static::id($name, $attributes);

			if (is_null($selected)) $selected = static::value($name);
			foreach ($options as $value => $display) {
				if ($selected === (string) $value) {
					$checked = true;
				} else {
					$checked = false;
				}

				//add selected class to list item if radio button is set to allow styling for selected radio buttons in set
				$subContainerAttributes = array('class' => 'radio');
				if ($checked) $subContainerAttributes['class'] .= ' selected';
				$radioButton = '<div'.static::attributes($subContainerAttributes).'>' . "\n";

				//append radio button value to the end of ID to prevent all radio buttons from having the same ID
				$idSuffix = str_replace('.', '-', str_replace(' ', '-', str_replace('_', '-', strtolower($value))));
				if ($idSuffix == "") $idSuffix = "blank";
				$attributes['id'] = $idPrefix.'-'.$idSuffix;

				$radioButton .= '<label>'.static::radio($name, $value, $checked, $attributes).' '.$display.'</label></div>' . "\n";
				$html .= $radioButton;
			}

			$html .= '</div>' . "\n";
			return $html;
		}
	}

	/**
	 * Create an HTML radio button input element.
	 *
	 * <code>
	 *		// Create a radio button element
	 *		echo Form::radio('drinks', 'Milk');
	 *
	 *		// Create a radio button that is selected by default
	 *		echo Form::radio('drinks', 'Milk', true);
	 * </code>
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  bool    $checked
	 * @param  array   $attributes
	 * @return string
	 */
	public static function radio($name, $value = null, $checked = false, $attributes = array())
	{
		if (is_null($value)) $value = $name;
		if ((string) $value === static::value($name)) $checked = true;

		if (!isset($attributes['id'])) $attributes['id'] = static::id($name.'-'.strtolower($value), $attributes);
		$name = static::name($name);

		return static::checkable('radio', $name, $value, $checked, $attributes);
	}

	/**
	 * Create a checkable (checkbox or radio button) input element.
	 *
	 * @param  string  $type
	 * @param  string  $name
	 * @param  string  $value
	 * @param  bool    $checked
	 * @param  array   $attributes
	 * @return string
	 */
	protected static function checkable($type, $name, $value, $checked, $attributes)
	{
		if ($checked) $attributes['checked'] = 'checked';

		return static::input($type, $name, $value, $attributes);
	}

	/**
	 * Prepare an options array from a database object or other complex
	 * object/array for a select field, checkbox set, or radio button set.
	 *
	 * @param  array   $options
	 * @param  array   $vars
	 * @return array
	 */
	public static function prepOptions($options = array(), $vars = array())
	{
		$optionsFormatted = array();

		//turn Eloquent instances into an array
		$optionsArray = $options;
		if (isset($optionsArray[0]) && isset($optionsArray[0]->incrementing) && isset($optionsArray[0]->timestamps))
			$optionsArray = $options->toArray();

		if (is_string($vars) || (is_array($vars) && count($vars) > 0)) {
			foreach ($optionsArray as $key => $option) {

				//turn object into array
				$optionArray = $option;
				if (is_object($option))
					$optionArray = (array) $option;

				//set label and value according to specified variables
				if (is_string($vars)) {
					$label = $vars;
					$value = $vars;
				} else if (is_array($vars) && count($vars) == 1) {
					$label = $vars[0];
					$value = $vars[0];
				} else {
					$label = $vars[0];
					$value = $vars[1];
				}

				//check whether the value is a method
				preg_match('/\(\)/', $value, $functionMatch);
				if (isset($optionValue)) unset($optionValue);
				if (!empty($functionMatch)) { //value is a method of object; call it
					$function = str_replace('()', '', $value);
					$optionValue = $options[$key]->$function();
				} else if (isset($optionArray[$value])) {
					$optionValue = $optionArray[$value];
				}

				//if a label and a value are set, add it to options array
				if (isset($optionArray[$label]) && isset($optionValue)) {
					$optionsFormatted[$optionArray[$label]] = $optionValue;
				}
			}
		}
		return $optionsFormatted;
	}

	/**
	 * Create an associative array from a simple array for a select field, checkbox set, or radio button set.
	 *
	 * @param  array   $options
	 * @return array
	 */
	public static function simpleOptions($options = array())
	{
		$optionsFormatted = array();
		foreach ($options as $option) {
			$optionsFormatted[$option] = $option;
		}
		return $optionsFormatted;
	}

	/**
	 * Offset a simple array by 1 index to prevent any options from having an
	 * index (value) of 0 for a select field, checkbox set, or radio button set.
	 *
	 * @param  array   $options
	 * @return array
	 */
	public static function offsetOptions($options = array())
	{
		$optionsFormatted = array();
		for ($o=0; $o < count($options); $o++) {
			$optionsFormatted[($o + 1)] = $options[$o];
		}
		return $optionsFormatted;
	}

	/**
	 * Create an options array of numbers within a specified range
	 * for a select field, checkbox set, or radio button set.
	 *
	 * @param  integer $start
	 * @param  integer $end
	 * @param  integer $increment
	 * @param  integer $decimals
	 * @return array
	 */
	public static function numberOptions($start = 1, $end = 10, $increment = 1, $decimals = 0)
	{
		$options = array();
		if (is_numeric($start) && is_numeric($end)) {
			if ($start <= $end) {
				for ($o = $start; $o <= $end; $o += $increment) {
					if ($decimals) {
						$value = number_format($o, $decimals, '.', '');
					} else {
						$value = $o;
					}
					$options[$value] = $value;
				}
			} else {
				for ($o = $start; $o >= $end; $o -= $increment) {
					if ($decimals) {
						$value = number_format($o, $decimals, '.', '');
					} else {
						$value = $o;
					}
					$options[$value] = $value;
				}
			}
		}
		return $options;
	}

	/**
	 * Get an options array of countries.
	 *
	 * @return array
	 */
	public static function countryOptions()
	{
		return static::simpleOptions(array(
			'Canada', 'United States', 'Afghanistan', 'Albania', 'Algeria', 'American Samoa', 'Andorra', 'Angola', 'Anguilla', 'Antarctica', 'Antigua And Barbuda', 'Argentina', 'Armenia', 'Aruba',
			'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bermuda', 'Bhutan', 'Bolivia', 'Bosnia And Herzegowina',
		 	'Botswana', 'Bouvet Island', 'Brazil', 'British Indian Ocean Territory', 'Brunei Darussalam', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon', 'Cape Verde', 'Cayman Islands',
		 	'Central African Republic', 'Chad', 'Chile', 'China', 'Christmas Island', 'Cocos (Keeling) Islands', 'Colombia', 'Comoros', 'Congo', 'Congo, The Democratic Republic Of The', 'Cook Islands',
		 	'Costa Rica', 'Cote D\'Ivoire', 'Croatia (Local Name: Hrvatska)', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'East Timor', 'Ecuador','Egypt',
			'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Ethiopia', 'Falkland Islands (Malvinas)', 'Faroe Islands', 'Fiji', 'Finland', 'France', 'France, Metropolitan', 'French Guiana',
		 	'French Polynesia', 'French Southern Territories', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Gibraltar', 'Greece', 'Greenland', 'Grenada', 'Guadeloupe', 'Guam', 'Guatemala','Guinea',
		 	'Guinea-Bissau', 'Guyana', 'Haiti', 'Heard And Mc Donald Islands', 'Holy See (Vatican City State)', 'Honduras', 'Hong Kong', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland',
		 	'Israel', 'Italy', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Korea, Democratic People\'S Republic Of', 'Korea, Republic Of', 'Kuwait', 'Kyrgyzstan',
		 	'Lao People\'S Democratic Republic', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libyan Arab Jamahiriya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macau',
		 	'Macedonia, Former Yugoslav Republic Of', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Martinique', 'Mauritania', 'Mauritius', 'Mayotte', 'Mexico',
		 	'Micronesia, Federated States Of', 'Moldova, Republic Of', 'Monaco', 'Mongolia', 'Montserrat', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands',
		 	'Netherlands Antilles', 'New Caledonia', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Niue', 'Norfolk Island', 'Northern Mariana Islands', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Panama',
		 	'Papua New Guinea', 'Paraguay', 'Peru','Philippines', 'Pitcairn', 'Poland', 'Portugal', 'Puerto Rico', 'Qatar', 'Reunion', 'Romania', 'Russian Federation', 'Rwanda', 'Saint Kitts And Nevis',
		 	'Saint Lucia','Saint Vincent And The Grenadines', 'Samoa', 'San Marino', 'Sao Tome And Principe', 'Saudi Arabia', 'Senegal', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia (Slovak Republic)',
		 	'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Georgia, South Sandwich Islands', 'Spain', 'Sri Lanka', 'St. Helena', 'St. Pierre And Miquelon', 'Sudan', 'Suriname',
		 	'Svalbard And Jan Mayen Islands', 'Swaziland', 'Sweden', 'Switzerland', 'Syrian Arab Republic', 'Taiwan', 'Tajikistan', 'Tanzania, United Republic Of', 'Thailand', 'Togo', 'Tokelau', 'Tonga',
		 	'Trinidad And Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Turks And Caicos Islands', 'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom',
		 	'United States Minor Outlying Islands', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Venezuela', 'Viet Nam', 'Virgin Islands (British)', 'Virgin Islands (U.S.)', 'Wallis And Futuna Islands',
		 	'Western Sahara', 'Yemen', 'Yugoslavia', 'Zambia', 'Zimbabwe'
		));
	}

	/**
	 * Get an options array of Canadian provinces.
	 *
	 * @param  bool    $useAbbrev
	 * @return array
	 */
	public static function provinceOptions($useAbbrev = true)
	{
		$provinces = array(
			'AB' => 'Alberta', 'BC' => 'British Columbia', 'MB' => 'Manitoba', 'NB' => 'New Brunswick', 'NL' => 'Newfoundland', 'NT' => 'Northwest Territories', 'NS' => 'Nova Scotia',
			'NU' => 'Nunavut', 'ON' => 'Ontario', 'PE' => 'Prince Edward Island', 'QC' => 'Quebec', 'SK' => 'Saskatchewan', 'YT' => 'Yukon Territory'
		);
		if ($useAbbrev) {
			return $provinces;
		} else {
			return static::simpleOptions(array_values($provinces)); //remove abbreviation keys
		}
	}

	/**
	 * Get an options array of US states.
	 *
	 * @param  bool    $useAbbrev
	 * @return array
	 */
	public static function stateOptions($useAbbrev = true)
	{
		$states = array(
			'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'DC' => 'District of Columbia',
			'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine',
			'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
			'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon',
			'PA' => 'Pennsylvania', 'PR' => 'Puerto Rico', 'RI' => 'Rhode Island', 'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont',
			'VA' => 'Virginia', 'VI' => 'Virgin Islands', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming'
		);
		if ($useAbbrev) {
			return $states;
		} else {
			return static::simpleOptions(array_values($states)); //remove abbreviation keys
		}
	}

	/**
	 * Get an options array of times.
	 *
	 * @param  string  $minutes
	 * @param  bool    $useAbbrev
	 * @return array
	 */
	public static function timeOptions($minutes = 'half')
	{
		$times = array();
		$minutesOptions = array('00');
		switch ($minutes) {
			case "full":
				$minutesOptions = array('00'); break;
			case "half":
				$minutesOptions = array('00', '30'); break;
			case "quarter":
				$minutesOptions = array('00', '15', '30', '45'); break;
			case "all":
				$minutesOptions = array();
				for ($m=0; $m < 60; $m++) {
					$minutesOptions[] = sprintf('%02d', $m);
				}
				break;
		}

		for ($h=0; $h < 24; $h++) {
			$hour = sprintf('%02d', $h);
			if ($h < 12) { $meridiem = "am"; } else { $meridiem = "pm"; }
			if ($h == 0) $hour = 12;
			if ($h > 12) {
				$hour = sprintf('%02d', ($hour - 12));
			}
			foreach ($minutesOptions as $minutes) {
				$times[sprintf('%02d', $h).':'.$minutes.':00'] = $hour.':'.$minutes.$meridiem;
			}
		}
		return $times;
	}

	/**
	 * Create an options array of months. You may use an integer to go a number of months back from your start month
	 * or you may use a date to go back or forward to a specific date. If the end month is later than the start month,
	 * the select options will go from earliest to latest. If the end month is earlier than the start month, the select
	 * options will go from latest to earliest. If an integer is used as the end month, use a negative number to go back
	 * from the start month. Setting $endDate to true will use the last day of the month instead of the first day.
	 *
	 * @param  mixed   $start
	 * @param  mixed   $end
	 * @param  boolean $endDate
	 * @param  string  $format
	 * @return array
	 */
	public static function monthOptions($start = 'current', $end = -12, $endDate = false, $format = 'F Y')
	{
		//prepare start & end months
		if ($start == "current" || is_null($start) || !is_string($start)) $start = date('Y-m-01');
		if (is_int($end)) {
			$startMid = date('Y-m-15', strtotime($start)); //get mid-day of month to prevent long months or short months from producing incorrect month values
			if ($end > 0) {
				$ascending = true;
				$end       = date('Y-m-01', strtotime($startMid.' +'.$end.' months'));
			} else {
				$ascending = false;
				$end       = date('Y-m-01', strtotime($startMid.' -'.abs($end).' months'));
			}
		} else {
			if ($end == "current") $end = date('Y-m-01');
			if (strtotime($end) > strtotime($start)) {
				$ascending = true;
			} else {
				$ascending = false;
			}
		}

		//create list of months
		$options = array();
		$month   = $start;
		if ($ascending) {
			while (strtotime($month) <= strtotime($end)) {
				$monthMid = date('Y-m-15', strtotime($month));
				if ($endDate) {
					$date = static::lastDayOfMonth($month);
				} else {
					$date = $month;
				}

				$options[$date] = date($format, strtotime($date));
				$month = date('Y-m-01', strtotime($monthMid.' +1 month'));
			}
		} else {
			while (strtotime($month) >= strtotime($end)) {
				$monthMid = date('Y-m-15', strtotime($month));
				if ($endDate) {
					$date = static::lastDayOfMonth($month);
				} else {
					$date = $month;
				}

				$options[$date] = date($format, strtotime($date));
				$month = date('Y-m-01', strtotime($monthMid.' -1 month'));
			}
		}
		return $options;
	}

	/**
	 * Get the last day of the month. You can use the second argument to format the date (example: "F j, Y").
	 *
	 * @param  string  $date
	 * @return string
	 */
	private static function lastDayOfMonth($date = 'current')
	{
		if ($date == "current") {
			$date = date('Y-m-d');
		} else {
			$date = date('Y-m-d', strtotime($date));
			$originalMonth = substr($date, 5, 2);
		}
		$year = substr($date, 0, 4); $month = substr($date, 5, 2); $day = substr($date, 8, 2); $result = "";
		if (isset($originalMonth) && $month != $originalMonth) $month = $originalMonth; //prevent invalid dates having wrong month assigned (June 31 = July, etc...)
		if ($month == "01" || $month == "03" || $month == "05" || $month == "07" || $month == "08" || $month == "10" || $month == "12") {
			$result = $year.'-'.$month.'-31';
		} else if ($month == "04" || $month == "06" || $month == "09" || $month == "11") {
			$result = $year.'-'.$month.'-30';
		} else if ($month == "02") {
			if (($year/4) == round($year/4)) {
				if (($year/100) == round($year/100)) {
					if (($year/400) == round($year/400)) {
						$result = $year.'-'.$month.'-29';
					} else {
						$result = $year.'-'.$month.'-28';
					}
				} else {
					$result = $year.'-'.$month.'-29';
				}
			} else {
				$result = $year.'-'.$month.'-28';
			}
		}
		return $result;
	}

	/**
	 * Create a set of boolean options (Yes/No, On/Off, Up/Down...)
	 * You may pass a string like "Yes/No" or an array with just two options.
	 *
	 * @param  mixed   $options
	 * @param  boolean $startWithOne
	 * @return array
	 */
	public static function booleanOptions($options = array('Yes', 'No'), $startWithOne = true)
	{
		if (is_string($options)) $options = explode('/', $options); //allow options to be set as a string like "Yes/No"
		if (!isset($options[1])) $options[1] = "";

		if ($startWithOne) {
			return array(
				1 => $options[0],
				0 => $options[1],
			);
		} else {
			return array(
				0 => $options[0],
				1 => $options[1],
			);
		}
	}

	/**
	 * Get all error messages.
	 *
	 * @return array
	 */
	public static function getErrors()
	{
		if (empty(static::$errors)) {
			foreach (static::$validationFields as $fieldName) {
				$error = static::errorMessage($fieldName);
				if ($error)
					static::$errors[] = $error;
			}
		}

		return static::$errors;
	}

	/**
	 * Set error messages from session data.
	 *
	 * @param  string  $errors
	 * @return array
	 */
	public static function setErrors($session = 'errors')
	{
		static::$errors = Session::get($session);
	}

	/**
	 * Reset error messages.
	 *
	 * @param  string  $errors
	 * @return array
	 */
	public static function resetErrors($session = 'errors')
	{
		if ($session)
			Session::forget($session);

		static::$errors = array();
	}

	/**
	 * Add an error class to an HTML attributes array if a validation error exists for the specified form field.
	 *
	 * @param  string  $name
	 * @param  array   $attributes
	 * @return array
	 */
	public static function addErrorClass($name, $attributes = array())
	{
		if (static::errorMessage($name)) { //an error exists; add the error class
			if (!isset($attributes['class'])) {
				$attributes['class'] = "has-error";
			} else {
				$attributes['class'] .= " has-error";
			}
		}
		return $attributes;
	}

	/**
	 * Create error div for validation error if it exists for specified form field.
	 *
	 * @param  string  $name
	 * @param  boolean $alwaysExists
	 * @param  mixed   $replacementFieldName
	 * @return string
	 */
	public static function error($name, $alwaysExists = false, $replacementFieldName = false)
	{
		$attr = "";
		if (substr($name, -1) == ".") $name = substr($name, 0, (strlen($name) - 1));
		if ($alwaysExists) $attr = ' id="'.str_replace('_', '-', $name).'-error"';

		$message = static::errorMessage($name, $replacementFieldName);

		if ($message && $message != "") {
			return '<div class="error"'.$attr.'>'.$message.'</div>';
		} else {
			if ($alwaysExists) return '<div class="error"'.$attr.' style="display: none;"></div>';
		}
	}

	/**
	 * Get validation error message if it exists for specified form field. Modified to work with array fields.
	 *
	 * @param  string  $name
	 * @param  mixed   $replacementFieldName
	 * @return string
	 */
	public static function errorMessage($name, $replacementFieldName = false)
	{
		//replace field name in error message with label if it exists
		$name = str_replace('(', '', str_replace(')', '', $name));
		$nameFormatted = $name;

		if ($replacementFieldName && is_string($replacementFieldName) && $replacementFieldName != "" && $replacementFieldName != "LOWERCASE") {
			$nameFormatted = $replacementFieldName;
		} else {
			if (isset(static::$labels[$name]) && static::$labels[$name] != "")
				$nameFormatted = static::$labels[$name];

			if (substr($nameFormatted, -1) == ":")
				$nameFormatted = substr($nameFormatted, 0, (strlen($nameFormatted) - 1));

			if ($replacementFieldName == "LOWERCASE")
				$nameFormatted = strtolower($nameFormatted);
		}

		//return error message if it already exists
		if (isset(static::$errors[$name]))
			return str_replace($name, $nameFormatted, static::$errors[$name]);

		//cycle through all validation instances to allow the ability to get error messages in root fields
		//as well as field arrays like "field[array]" (passed to errorMessage in the form of "field.array")
		foreach (static::$validation as $fieldName => $validation) {
			$valid = $validation->passes();

			if ($validation->messages()) {
				$messages = $validation->messages();
				$nameArray = explode('.', $name);
				if (count($nameArray) < 2) {
					if ($_POST && $fieldName == "root" && $messages->first($name) != "") {
						static::$errors[$name] = str_replace(str_replace('_', ' ', $name), $nameFormatted, $messages->first($name));
						return static::$errors[$name];
					}
				} else {
					$last =	$nameArray[(count($nameArray) - 1)];
					$first = str_replace('.'.$nameArray[(count($nameArray) - 1)], '', $name);

					if ($replacementFieldName && is_string($replacementFieldName) && $replacementFieldName != "" && $replacementFieldName != "LOWERCASE") {
						$nameFormatted = $replacementFieldName;
					} else {
						if ($nameFormatted == $name) {
							$nameFormatted = static::entities(ucwords($last));
						}
						if (substr($nameFormatted, -1) == ":")
							$nameFormatted = substr($nameFormatted, 0, (strlen($nameFormatted) - 2));

						if ($replacementFieldName == "LOWERCASE")
							$nameFormatted = strtolower($nameFormatted);
					}

					if ($_POST && $fieldName == $first && $messages->first($last) != "") {
						static::$errors[$name] = str_replace(str_replace('_', ' ', $last), $nameFormatted, $messages->first($last));
						return static::$errors[$name];
					}
				}
			}
		}

		return false;
	}

	/**
	 * Get the validators array.
	 *
	 */
	public static function getValidation()
	{
		return static::$validation;
	}

	/**
	 * Create an HTML submit input element.
	 *
	 * @param  string  $value
	 * @param  array   $attributes
	 * @return string
	 */
	public static function submit($value = 'Submit', $attributes = array())
	{
		return static::input('submit', null, $value, $attributes);
	}

	/**
	 * Create an HTML reset input element.
	 *
	 * @param  string  $value
	 * @param  array   $attributes
	 * @return string
	 */
	public static function reset($value = null, $attributes = array())
	{
		return static::input('reset', null, $value, $attributes);
	}

	/**
	 * Create an HTML image input element.
	 *
	 * <code>
	 *		// Create an image input element
	 *		echo Form::image('img/submit.png');
	 * </code>
	 *
	 * @param  string  $url
	 * @param  string  $name
	 * @param  array   $attributes
	 * @return string
	 */
	public static function image($url, $name = null, $attributes = array())
	{
		$attributes['src'] = URL::to_asset($url);

		return static::input('image', $name, null, $attributes);
	}

	/**
	 * Create an HTML button element.
	 *
	 * @param  string  $value
	 * @param  array   $attributes
	 * @return string
	 */
	public static function button($value = null, $attributes = array())
	{
		if (!isset($attributes['class'])) {
			$attributes['class'] = 'btn btn-default';
		} else {
			$attributes['class'] .= ' btn btn-default';
		}

		if ($value == strip_tags($value)) $value = static::entities($value);
		return '<button'.static::attributes($attributes).'>'.$value.'</button>' . "\n";
	}

	/**
	 * Create a label for a submit function based on a resource controller URL.
	 *
	 * @param  mixed   $itemName
	 * @param  mixed   $action
	 * @param  mixed   $update
	 * @return string
	 */
	public static function submitResource($itemName = null, $action = null, $update = null, $icon = null)
	{
		//if null, check config button icon config setting
		if (is_null($icon))
			$icon = Config::get('formation::autoButtonIcon');

		if (is_null($update))
			$update = static::updateResource($action);

		if ($update) {
			$label = 'Update';
			if (is_bool($icon) && $icon)
				$icon = 'ok';
		} else {
			$label = 'Create';
			if (is_bool($icon) && $icon)
				$icon = 'plus';
		}

		//add icon code
		if (is_string($icon) && $icon != "")
			$label = '[ICON: '.$icon.']'.$label;

		if (!is_null($itemName) && $itemName != "") {
			$label .= ' '.$itemName;
		}
		return $label;
	}

	/**
	 * Get the status create / update status from the resource controller URL.
	 *
	 * @param  mixed   $action
	 * @return bool
	 */
	public static function updateResource($action = null)
	{
		$action = static::action($action);

		//set method based on action
		$actionArray = explode('/', $action);
		$actionLastSegment = $actionArray[(count($actionArray) - 1)];
		if (is_numeric($actionLastSegment) || $actionLastSegment == "edit") {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the appliction.encoding without needing to request it from Config::get() each time.
	 *
	 * @return string
	 */
	protected static function encoding()
	{
		return static::$encoding ?: static::$encoding = Config::get('site.encoding');
	}

	/**
	 * Dynamically handle calls to custom macros.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public static function __callStatic($method, $parameters)
	{
		if (isset(static::$macros[$method]))
		{
			return call_user_func_array(static::$macros[$method], $parameters);
		}

		throw new \Exception("Method [$method] does not exist.");
	}

}