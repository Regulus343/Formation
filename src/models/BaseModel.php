<?php namespace Aquanode\Formation;

use Illuminate\Database\Eloquent\Model as Eloquent;

use Formation as Form;

use Regulus\TetraText as Format;

class BaseModel extends Eloquent {

	/**
	 * The special typed fields for the model.
	 *
	 * @var    string
	 */
	protected static $types = array();

	/**
	 * Get the validation rules used by the model.
	 *
	 * @return string
	 */
	public static function validationRules()
	{
		return array();
	}

	/**
	 * Save the input data to the model.
	 *
	 * @param  array    $input
	 * @return void
	 */
	public function saveData($input)
	{
		foreach (static::$types as $field => $type) {
			if (isset($input[$field])) {
				switch ($type) {
					case "date":        $input[$field] = $input[$field] != "" ? date('Y-m-d', strtotime($input[$field])) : "0000-00-00"; break;
					case "slug":        $input[$field] = Format::slug($input[$field]); break;
					case "unique-slug": $input[$field] = Format::uniqueSlug($input[$field], $this->table, $field, $this->id); break;
				}
			}
		}

		$this->fill($input);
		$this->save();
	}

	/**
	 * Gets the model by its slug.
	 *
	 * @param  string   $slug
	 * @return Page
	 */
	public static function findBySlug($slug)
	{
		return static::where('slug', $slug)->first();
	}

}