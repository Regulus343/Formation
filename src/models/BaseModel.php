<?php namespace Aquanode\Formation;

use Illuminate\Database\Eloquent\Model as Eloquent;

use Aquanode\Formation\Formation as Form;

use Regulus\TetraText\TetraText as Format;

class BaseModel extends Eloquent {

	/**
	 * The special typed fields for the model.
	 *
	 * @var    array
	 */
	protected static $types = array();

	/**
	 * Get the special typed fields for the model.
	 *
	 * @return array
	 */
	public function getFieldTypes()
	{
		return static::$types;
	}

	/**
	 * The default values for the model.
	 *
	 * @return array
	 */
	public static function defaults()
	{
		return array();
	}

	/**
	 * Get the validation rules used by the model.
	 *
	 * @param  mixed    $id
	 * @return string
	 */
	public static function validationRules($id = null)
	{
		return array();
	}

	/**
	 * Set the validation rules for the model.
	 *
	 * @return string
	 */
	public function setValidationRules()
	{
		Form::setValidationRules(static::validationRules((int) $this->id));
	}

	/**
	 * Get the formatted values for populating a form.
	 *
	 * @param  array    $relationships
	 * @return string
	 */
	public function getFormattedValues($relationships = array())
	{
		foreach ($this->getFieldTypes() as $field => $type) {
			if (isset($this->{$field})) {
				$value = $this->{$field};
				$this->{$field} = static::formatValue($value, $type);
			}
		}

		foreach ($relationships as $relationship) {
			if ($this->{$relationship}) {
				foreach ($this->{$relationship} as &$item) {
					foreach ($item->getFieldTypes() as $field => $type) {
						if (isset($item->{$field})) {
							$value = $item->{$field};
							$item->{$field.'_formatted'} = static::formatValue($value, $type);
						}
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Get the formatted values for populating a form.
	 *
	 * @param  string   $value
	 * @param  string   $type
	 * @return string
	 */
	private static function formatValue($value, $type)
	{
		switch ($type) {
			case "date":      $value = ($value != "0000-00-00" ? date(Form::getDateFormat(), strtotime($value)) : ""); break;
			case "date-time": $value = ($value != "0000-00-00 00:00:00" ? date(Form::getDateTimeFormat(), strtotime($value)) : ""); break;
		}

		return $value;
	}

	/**
	 * Set the default values for the model for a new item.
	 *
	 * @param  mixed    $prefix
	 * @return array
	 */
	public static function setDefaultsForNew($prefix = null)
	{
		return Form::setDefaults(static::defaults(), array(), $prefix);
	}

	/**
	 * Add a prefix to the default values if one is set.
	 *
	 * @param  array    $defaults
	 * @param  mixed    $prefix
	 * @return array
	 */
	public static function addPrefixToDefaults($defaults = array(), $prefix = null)
	{
		if (is_string($prefix) && $prefix != "")
			$prefix .= ".";

		$defaultsFormatted = array();
		foreach ($defaults as $field => $value) {
			$defaultsFormatted[$prefix.$field] = $value;
		}

		return $defaultsFormatted;
	}

	/**
	 * Set the default values for the model.
	 *
	 * @param  array    $relationships
	 * @param  mixed    $prefix
	 * @return array
	 */
	public function setDefaults($relationships = array(), $prefix = null)
	{
		return Form::setDefaults($this->getFormattedValues($relationships), $relationships, $prefix);
	}

	/**
	 * Save the input data to the model.
	 *
	 * @param  mixed    $input
	 * @return void
	 */
	public function saveData($input = null)
	{
		if (is_null($input))
			$input = Input::all();

		foreach ($this->getFieldTypes() as $field => $type) {
			if (isset($input[$field])) {
				switch ($type) {
					case "date":        $input[$field] = ($input[$field] != "" ? date('Y-m-d', strtotime($input[$field])) : "0000-00-00"); break;
					case "date-time":   $input[$field] = ($input[$field] != "" ? date('Y-m-d H:i:s', strtotime($input[$field])) : "0000-00-00 00:00:00"); break;
					case "slug":        $input[$field] = Format::slug($input[$field]); break;
					case "unique-slug": $input[$field] = Format::uniqueSlug($input[$field], $this->table, $field, $this->id); break;
				}
			}
		}

		$this->fill($input);
		$this->save();
	}

	/**
	 * Gets the model by a field other than its ID.
	 *
	 * @param  string   $field
	 * @param  string   $value
	 * @return Page
	 */
	public static function findBy($field = 'slug', $value)
	{
		return static::where($field, $slug)->first();
	}

	/**
	 * Gets the model by its slug.
	 *
	 * @param  string   $slug
	 * @return Page
	 */
	public static function findBySlug($slug)
	{
		return static::findBy('slug', $slug);
	}

}