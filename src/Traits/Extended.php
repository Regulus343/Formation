<?php namespace Regulus\Formation\Traits;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;

use Regulus\Formation\Facade as Form;
use Regulus\TetraText\Facade as Format;

trait Extended {

	/**
	 * The special typed fields for the model.
	 *
	 * @var    array
	 */
	protected static $types = [
		/*
		'activated_at' => 'date-time',
		'published_at' => 'checkbox-timestamp',
		*/
	];

	/**
	 * The special formatted fields for the model.
	 *
	 * @var    array
	 */
	protected static $formats = [
		/*
		'title' => 'uppercase-first',
		*/
	];

	/**
	 * The special formatted fields for the model for saving to the database.
	 *
	 * @var    array
	 */
	protected static $formatsForDb = [
		/*
		'description' => 'null-if-blank',
		*/
	];

	/**
	 * The methods to automatically include in a model's array / JSON object.
	 *
	 * @var    array
	 */
	protected static $arrayIncludedMethods = [
		/*
		'name' => 'getName(true)',
		'url'  => 'getUrl', // parameters not required
		*/
	];

	/**
	 * The attribute sets for the model.
	 *
	 * @var    array
	 */
	protected static $attributeSets = [
		/*
		'standard' => [
			'id',
			'title',
			'created_at',
		],
		*/
	];

	/**
	 * The attribute sets for related models.
	 *
	 * @var    array
	 */
	protected static $relatedAttributeSets = [
		/*
		'standard' => [
			'items' => 'set:standard', // this will use an attribute set from the model used for the 'items' relationship
		],
		*/
	];

	/**
	 * The related data requested for related models' arrays / JSON objects.
	 *
	 * @var    mixed
	 */
	protected static $relatedDataRequested = null;

	/**
	 * The static cached data for the model.
	 *
	 * @var    mixed
	 */
	protected static $staticCached;

	/**
	 * The foreign key for the model.
	 *
	 * @var    mixed
	 */
	protected $foreignKey = null;

	/**
	 * The cached data for the model.
	 *
	 * @var    mixed
	 */
	protected $cached;

	/**
	 * Create a new Eloquent model instance.
	 *
	 * @param  array  $attributes
	 * @return void
	 */
	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);
	}

	/**
	 * Get the default foreign key name for the model.
	 *
	 * @return string
	 */
	public function getForeignKey()
	{
		if (!is_null($this->foreignKey))
			return $this->foreignKey;
		else
			return snake_case(class_basename($this)).'_id';
	}

	/**
	 * Get an attribute from the model.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function getAttribute($key)
	{
		$snakedKey = snake_case($key);

		$checkSnakedKey = $snakedKey != $key;

		if (array_key_exists($key, $this->attributes) || $this->hasGetMutator($key))
			return $this->getAttributeValue($key);

		if ($checkSnakedKey && (array_key_exists($snakedKey, $this->attributes) || $this->hasGetMutator($snakedKey)))
			return $this->getAttributeValue($snakedKey);

		return $this->getRelationValue($key);
	}

	/**
	 * Set a given attribute on the model.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return $this
	 */
	public function setAttribute($key, $value)
	{
		$key = snake_case($key);

		// First we will check for the presence of a mutator for the set operation
		// which simply lets the developers tweak the attribute as it is set on
		// the model, such as "json_encoding" an listing of data for storage.
		if ($this->hasSetMutator($key))
		{
			$method = 'set'.Str::studly($key).'Attribute';

			return $this->{$method}($value);
		}

		// If an attribute is listed as a "date", we'll convert it from a DateTime
		// instance into a form proper for storage on the database tables using
		// the connection grammar's date format. We will auto set the values.
		elseif ($value && (in_array($key, $this->getDates()) || $this->isDateCastable($key)))
		{
			$value = $this->fromDateTime($value);
		}

		if ($this->isJsonCastable($key) && ! is_null($value))
		{
			$value = $this->asJson($value);
		}

		// auto-trim value if config option set
		if (config('form.auto_trim') && is_string($value))
			$value = trim($value);

		$this->attributes[$key] = $value;

		return $this;
	}

	/**
	 * Convert the model instance to an array.
	 *
	 * @param  mixed   $camelizeArrayKeys
	 * @return array
	 */
	public function toArray($camelizeArrayKeys = null)
	{
		$attributes = $this->attributesToArray($camelizeArrayKeys);

		return array_merge($attributes, $this->relationsToArray());
	}

	/**
	 * Get an attribute array of all arrayable values.
	 *
	 * @param  array   $values
	 * @param  mixed   $camelizeArrayKeys
	 * @return array
	 */
	protected function getArrayableItems(array $values, $camelizeArrayKeys = null)
	{
		if (count($this->getVisible()) > 0)
			$values = array_intersect_key($values, array_flip($this->getVisible()));

		if (count($this->getHidden()) > 0)
			$values = array_diff_key($values, array_flip($this->getHidden()));

		$formattedValues = [];

		if ($camelizeArrayKeys === null)
			$camelizeArrayKeys = config('form.camelize_array_keys');

		if ($camelizeArrayKeys)
		{
			foreach ($values as $key => $value)
			{
				$formattedValues[camel_case($key)] = $value;
			}
		}
		else
		{
			$formattedValues = $values;
		}

		return $formattedValues;
	}

	/**
	 * Convert the model's attributes to an array.
	 *
	 * @param  mixed   $camelizeArrayKeys
	 * @return array
	 */
	public function attributesToArray($camelizeArrayKeys = null)
	{
		$attributes = $this->getArrayableAttributes($camelizeArrayKeys);

		$visible = $this->getVisible();
		$hidden  = $this->getHidden();

		// If an attribute is a date, we will cast it to a string after converting it
		// to a DateTime / Carbon instance. This is so we will get some consistent
		// formatting while accessing attributes vs. arraying / JSONing a model.
		foreach ($this->getDates() as $key)
		{
			$key = static::formatArrayKey($key, $camelizeArrayKeys);

			if (!isset($attributes[$key]))
				continue;

			$attributes[$key] = $this->serializeDate(
				$this->asDateTime($attributes[$key])
			);
		}

		$mutatedAttributes = $this->getMutatedAttributes();

		// We want to spin through all the mutated attributes for this model and call
		// the mutator for the attribute. We cache off every mutated attributes so
		// we don't have to constantly check on attributes that actually change.
		foreach ($mutatedAttributes as $key)
		{
			$key = static::formatArrayKey($key, $camelizeArrayKeys);

			if (!array_key_exists($key, $attributes))
				continue;

			$attributes[$key] = $this->mutateAttributeForArray(
				$key, $attributes[$key]
			);
		}

		// Next we will handle any casts that have been setup for this model and cast
		// the values to their appropriate type. If the attribute has a mutator we
		// will not perform the cast on those attributes to avoid any confusion.
		foreach ($this->getCasts() as $key => $value)
		{
			$key = static::formatArrayKey($key, $camelizeArrayKeys);

			if (!array_key_exists($key, $attributes) || in_array($key, $mutatedAttributes))
				continue;

			$attributes[$key] = $this->castAttribute(
				$key, $attributes[$key]
			);

			if ($attributes[$key] && ($value === 'date' || $value === 'datetime'))
			{
				$attributes[$key] = $this->serializeDate($attributes[$key]);
			}
		}

		// Here we will grab all of the appended, calculated attributes to this model
		// as these attributes are not really in the attributes array, but are run
		// when we need to array or JSON the model for convenience to the coder.
		foreach ($this->getArrayableAppends() as $key)
		{
			$key = static::formatArrayKey($key, $camelizeArrayKeys);

			$attributes[$key] = $this->mutateAttributeForArray($key, null);
		}

		// additionally, we will append the "array-included methods" which allows for more advanced specification
		foreach ($this->getArrayIncludedMethods() as $key => $includedMethod)
		{
			$keyFormatted = static::formatArrayKey($key, $camelizeArrayKeys);

			if (substr($includedMethod, -1) != ")")
				$includedMethod .= "()";

			$method = Format::getMethodFromString($includedMethod);

			$add = !count($visible) && !count($hidden);

			if (count($visible) && (in_array($key, $visible) || in_array($keyFormatted, $visible)))
				$add = true;

			if (count($hidden) && !in_array($key, $visible) && !in_array($keyFormatted, $visible))
				$add = true;

			foreach ($method['parameters'] as &$parameter)
			{
				if (is_string($parameter))
				{
					if (substr($parameter, 0, 7) == "cached:")
						$parameter = $this->getCached(substr($parameter, 7));

					if (substr($parameter, 0, 14) == "static-cached:")
						$parameter = static::getStaticCached(substr($parameter, 14));
				}
			}

			if ($add)
				$attributes[$keyFormatted] = call_user_func_array([$this, $method['name']], $method['parameters']);
		}

		// run through them one more time in case relationships and remove any attributes that are not allowed
		foreach ($this->getRelations() as $key => $attribute)
		{
			$remove = false;

			if (count($visible) && !in_array($key, $visible))
				$remove = true;

			if (count($hidden) && in_array($key, $visible))
				$remove = true;

			if ($remove)
				unset($attributes[$key]);
		}

		return $attributes;
	}

	/**
	 * Get an attribute array of all arrayable attributes.
	 *
	 * @param  mixed   $camelizeArrayKeys
	 * @return array
	 */
	protected function getArrayableAttributes($camelizeArrayKeys = null)
	{
		return $this->getArrayableItems($this->attributes, $camelizeArrayKeys);
	}

	/**
	 * Get the model's relationships in array form.
	 *
	 * @param  mixed   $camelizeArrayKeys
	 * @return array
	 */
	public function relationsToArray($camelizeArrayKeys = null)
	{
		$attributes = [];

		$relatedDataRequested = [];

		if (isset(static::$relatedDataRequested[get_class($this)]))
			$relatedDataRequested = static::$relatedDataRequested[get_class($this)];

		foreach ($this->getArrayableRelations($camelizeArrayKeys) as $key => $value)
		{
			// If the values implements the Arrayable interface we can just call this
			// toArray method on the instances which will convert both models and
			// collections to their proper array form and we'll set the values.
			if ($value instanceof Arrayable || (is_object($value) && method_exists($value, 'toArray')))
			{
				// if "related data requested" is set, adjust visible and hidden arrays for related items
				if (is_callable([$value, 'setVisible']) && !is_null($relatedDataRequested))
				{
					$visibleAttributes = [];

					if (method_exists($value, 'scopeLimitRelatedData'))
						$value->limitRelatedData();

					if (isset($relatedDataRequested[$key]))
					{
						$relatedDataRequestedForKey = [];
						foreach ($relatedDataRequested[$key] as $field)
						{
							if (strtolower(substr($field, 0, 7)) != "select:")
								$relatedDataRequestedForKey[] = $field;
						}

						$visibleAttributes = $relatedDataRequestedForKey;
					}
					else
					{
						foreach ($relatedDataRequested as $field)
						{
							if (is_string($field) && strtolower(substr($field, 0, 7)) != "select:")
							{
								$fieldArray = explode('.', $field);

								if ($fieldArray[0] == $key && count($fieldArray) > 1)
									$visibleAttributes[] = str_replace($key.'.', '', $field);
							}
						}
					}

					if (!empty($visibleAttributes) && is_array($visibleAttributes) && !in_array('*', $visibleAttributes))
					{
						if (get_class($value) == "Illuminate\Database\Eloquent\Collection")
						{
							foreach ($value as $item)
							{
								$item->setVisible($visibleAttributes);
								$item->setHidden([]);
							}
						}
						else
						{
							$value->setVisible($visibleAttributes);
							$value->setHidden([]);
						}
					}
				}

				$relation = $value->toArray($camelizeArrayKeys);
			}

			// If the value is null, we'll still go ahead and set it in this list of
			// attributes since null is used to represent empty relationships if
			// if it a has one or belongs to type relationships on the models.
			elseif (is_null($value)) {
				$relation = $value;
			}

			// If the relationships snake-casing is enabled, we will snake case this
			// key so that the relation attribute is snake cased in this returned
			// array to the developers, making this consistent with attributes.
			if (static::$snakeAttributes) {
				$key = Str::snake($key);
			}

			// If the relation value has been set, we will set it on this attributes
			// list for returning. If it was not arrayable or null, we'll not set
			// the value on the array because it is some type of invalid value.
			if (isset($relation) || is_null($value)) {
				$attributes[$key] = $relation;
			}

			unset($relation);
		}

		return $attributes;
	}

	/**
	 * Get an attribute array of all arrayable relations.
	 *
	 * @param  mixed   $camelizeArrayKeys
	 * @return array
	 */
	protected function getArrayableRelations($camelizeArrayKeys = null)
	{
		return $this->getArrayableItems($this->relations, $camelizeArrayKeys);
	}

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
	 * Get the special formatted values for the model.
	 *
	 * @return array
	 */
	public function getFieldFormats()
	{
		return static::$formats;
	}

	/**
	 * Get the special formatted values for DB insertion for the model.
	 *
	 * @return array
	 */
	public function getFieldFormatsForDb()
	{
		return static::$formatsForDb;
	}

	/**
	 * Get the JSON-included methods for the model.
	 *
	 * @return array
	 */
	public function getArrayIncludedMethods()
	{
		return static::$arrayIncludedMethods;
	}

	/**
	 * Set an array-included method for the model.
	 *
	 * @param  string   $attribute
	 * @param  string   $method
	 * @return void
	 */
	public static function setArrayIncludedMethod($attribute, $method)
	{
		static::$arrayIncludedMethods[$attribute] = $method;
	}

	/**
	 * Set array-included methods for the model.
	 *
	 * @param  array    $methods
	 * @return void
	 */
	public static function setArrayIncludedMethods($methods)
	{
		foreach ($methods as $attribute => $method)
		{
			static::setArrayIncludedMethod($attribute, $method);
		}
	}

	/**
	 * Get the JSON-included methods for the model.
	 *
	 * @param  mixed    $relatedData
	 * @param  boolean  $limitSelect
	 * @return QueryBuilder
	 */
	public function scopeLimitRelatedData($query, $relatedData = 'standard', $limitSelect = true)
	{
		if (is_string($relatedData))
			$relatedData = $this->getAttributeSet($relatedData, true);

		static::$relatedDataRequested[get_class($this)] = $relatedData;

		if ($limitSelect)
		{
			$with = [];

			foreach ($relatedData as $relation => $fields)
			{
				// remove "select" prefixes that tell toArray() that field is only selected in query, not kept in returned array
				if (is_array($fields))
				{
					foreach ($fields as &$field)
					{
						if (strtolower(substr($field, 0, 7)) == "select:")
							$field = substr($field, 7);
					}
				}

				$with[$relation] = function($relationQuery) use ($fields)
				{
					if (is_array($fields))
					{
						// remove array-included methods and relationships from select statement
						$model = $relationQuery->getModel();

						$arrayIncludedMethods = [];
						if (method_exists($model, 'getArrayIncludedMethods'))
							$arrayIncludedMethods = array_keys($relationQuery->getModel()->getArrayIncludedMethods());

						foreach ($fields as $f => $field)
						{
							if (in_array($field, $arrayIncludedMethods) || method_exists($model, $field))
								unset($fields[$f]);
						}

						$relationQuery->select($fields);
					}
				};
			}

			$query->with($with);
		}

		return $query;
	}

	/**
	 * The default values for the model.
	 *
	 * @return array
	 */
	public static function defaults()
	{
		return [];
	}

	/**
	 * Get the validation rules used by the model.
	 *
	 * @param  mixed    $id
	 * @param  mixed    $input
	 * @return array
	 */
	public static function validationRules($id = null, $input = null)
	{
		return [];
	}

	/**
	 * Set the validation rules for the model for a new record.
	 *
	 * @return void
	 */
	public static function setValidationRulesForNew()
	{
		Form::setValidationRules(static::validationRules());
	}

	/**
	 * Set the validation rules for the model.
	 *
	 * @return void
	 */
	public function setValidationRules()
	{
		Form::setValidationRules(static::validationRules((int) $this->id));
	}

	/**
	 * Get the formatted values for populating a form.
	 *
	 * @param  array    $relations
	 * @return object
	 */
	public function getFormattedValues($relations = [])
	{
		$model = clone $this;

		// format fields based on field types
		foreach ($model->getFieldTypes() as $field => $type)
		{
			if (isset($model->{$field}))
			{
				$value = $model->{$field};
				$model->{$field} = static::formatValue($value, $type);
			}
		}

		// format fields based on special format rules
		foreach ($model->getFieldFormats() as $field => $formats)
		{
			$fieldTested = isset($format[1]) ? $format[1] : $field;
			$valueTested = $model->{$fieldTested} ? isset($model->{$field}) : null;

			$model->{$field} = $model->formatValueForSpecialFormats($field, $model->toArray(false), $formats);
		}

		foreach ($relations as $relation)
		{
			if ($model->{$relation})
			{
				foreach ($model->{$relation} as &$item)
				{
					foreach ($item->getFieldTypes() as $field => $type)
					{
						if (isset($item->{$field}))
						{
							$value = $item->{$field};
							$item->{$field.'_formatted'} = static::formatValue($value, $type);
						}
					}

					foreach ($item->getFieldFormats() as $field => $formats)
					{
						if (isset($model->{$field}))
							$model->{$field} = $model->formatValueForSpecialFormats($model->{$field}, $formats);
					}
				}
			}
		}

		return $model;
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
		$formattedValue = null;

		switch ($type)
		{
			case "date":

				if ($value != "0000-00-00" && $value != "" && !is_null($value))
					$formattedValue = date(Form::getDateFormat(), strtotime($value));

				break;

			case "date-time":

				if ($value != "0000-00-00 00:00:00" && $value != "" && !is_null($value))
					$formattedValue = date(Form::getDateTimeFormat(), strtotime($value));

				break;
		}

		return $formattedValue;
	}

	/**
	 * Set the default values for the model for a new item.
	 *
	 * @param  mixed    $prefix
	 * @return array
	 */
	public static function setDefaultsForNew($prefix = null)
	{
		return Form::setDefaults(static::defaults(), [], $prefix);
	}

	/**
	 * Add a prefix to the default values if one is set.
	 *
	 * @param  array    $defaults
	 * @param  mixed    $prefix
	 * @return array
	 */
	public static function addPrefixToDefaults($defaults = [], $prefix = null)
	{
		if (is_string($prefix) && $prefix != "")
			$prefix .= ".";

		$defaultsFormatted = [];
		foreach ($defaults as $field => $value) {
			$defaultsFormatted[$prefix.$field] = $value;
		}

		return $defaultsFormatted;
	}

	/**
	 * Set the default values for the model.
	 *
	 * @param  array    $relations
	 * @param  mixed    $prefix
	 * @return array
	 */
	public function setDefaults($relations = [], $prefix = null)
	{
		return Form::setDefaults($this->getFormattedValues($relations), $relations, $prefix);
	}

	/**
	 * Save the input data to the model.
	 *
	 * @param  mixed    $input
	 * @param  mixed    $create
	 * @param  boolean  $saveRelational
	 * @return void
	 */
	public function formatSave($input = null, $create = null, $saveRelational = false)
	{
		if (is_null($input))
			$input = Input::all();

		// if create is not specified, use ID to determine whether creating or updating record
		if (is_null($create))
			$create = is_null($this->id);

		// format data for special types and special formats
		$input = $this->formatValuesForDb($input, $create);

		$this->fill($input)->save();

		if ($saveRelational)
		{
			foreach ($input as $field => $value)
			{
				if (is_array($value))
				{
					$fieldCamelCase = camel_case($field);

					if (isset($this->{$fieldCamelCase}) || $this->{$fieldCamelCase})
						$this->saveRelationalData($value, $fieldCamelCase);
				}
			}
		}
	}

	/**
	 * Save the input data to the model including the relational data if it is present.
	 *
	 * @param  mixed    $input
	 * @param  mixed    $create
	 * @return void
	 */
	public function formatSaveWithRelational($input = null, $create = null)
	{
		return $this->formatSave($input, $create, true);
	}

	/**
	 * Save the relational input data to the model.
	 *
	 * @param  mixed    $input
	 * @param  string   $modelMethod
	 * @return void
	 */
	public function saveRelationalData($input, $modelMethod)
	{
		$idsSaved        = [];
		$items           = $this->{$modelMethod};
		$model           = get_class($this->{$modelMethod}()->getModel());
		$formattedSuffix = Form::getFormattedFieldSuffix();
		$pivotTimestamps = config('form.pivot_timestamps');

		// create or update related items
		foreach ($input as $index => $itemData)
		{
			if ($index != "pivot")
			{
				if (isset($itemData['id']) && $itemData['id'] > 0 && $itemData['id'] != "")
					$create = false;
				else
					$create = true;

				$found = false;
				if (!$create)
				{
					foreach ($items as $item)
					{
						if ((int) $itemData['id'] == (int) $item->id)
						{
							$found = true;

							// remove formatted fields from item to prevent errors in saving data
							foreach ($item->toArray(false) as $field => $value)
							{
								if (substr($field, -(strlen($formattedSuffix))) == $formattedSuffix)
									unset($item->{$field});
							}

							// format data for special types and special formats
							$itemData = $item->formatValuesForTypes($itemData);
							$itemData = $item->formatValuesForSpecialFormats($itemData);

							// save data
							$item->fill($itemData)->save();

							$currentItem = $item;

							if (!in_array((int) $item->id, $idsSaved))
								$idsSaved[] = (int) $item->id;
						}
					}
				}

				// if model was not found, it may still exist in the database but not have a current relationship with item
				if (!$found && !$create)
				{
					$item = $model::find($itemData['id']);

					if ($item)
					{
						// format data for special types
						$itemData = $item->formatValuesForTypes($itemData);

						// save data
						$item->fill($itemData)->save();

						$currentItem = $item;

						if (!in_array((int) $item->id, $idsSaved))
							$idsSaved[] = (int) $item->id;
					}
					else
					{
						$create = true;
					}
				}

				if ($create)
				{
					$item = new $model;

					// attempt to add foreign key ID in case relationship doesn't require a pivot table
					$itemData[$this->getForeignKey()] = $this->id;

					// format data for special types
					$itemData = $item->formatValuesForTypes($itemData);

					$item->fill($itemData)->save();

					$currentItem = $item;

					if (!in_array((int) $item->id, $idsSaved))
						$idsSaved[] = (int) $item->id;
				}

				// save pivot data
				if (isset($itemData['pivot']))
				{
					$pivotTable = $this->{$modelMethod}()->getTable();
					$pivotKeys  = [
						$this->getForeignKey() => $this->id,
						$item->getForeignKey() => $currentItem->id,
					];

					$pivotData = array_merge($itemData['pivot'], $pivotKeys);

					// set updated timestamp
					if ($pivotTimestamps)
					{
						$timestamp = date('Y-m-d H:i:s');
						$pivotData['updated_at'] = $timestamp;
					}

					// attempt to select pivot record by both keys
					$pivotItem = DB::table($pivotTable);
					foreach ($pivotKeys as $key => $id)
					{
						$pivotItem->where($key, $id);
					}

					// if id exists, add it to where clause and unset it
					if (isset($pivotData['id']) && (int) $pivotData['id'])
					{
						$pivotItem->where('id', $pivotData['id']);
						unset($pivotData['id']);
					}

					// attempt to update and if it doesn't work, insert a new record
					if (!$pivotItem->update($pivotData))
					{
						if ($pivotTimestamps)
							$pivotData['created_at'] = $timestamp;

						DB::table($pivotTable)->insert($pivotData);
					}
				}
			} else {
				// data is entirely pivot data; save pivot data

				$item = new $model; //create dummy item to get foreign key

				$pivotTable    = $this->{$modelMethod}()->getTable();
				$pivotIdsSaved = [];

				foreach ($itemData as $pivotId)
				{
					$pivotKeys  = [
						$this->getForeignKey() => $this->id,
						$item->getForeignKey() => $pivotId,
					];

					$pivotData = $pivotKeys;

					// set updated timestamp
					if ($pivotTimestamps)
					{
						$timestamp = date('Y-m-d H:i:s');
						$pivotData['updated_at'] = $timestamp;
					}

					// attempt to select pivot record by both keys
					$pivotItem = DB::table($pivotTable);
					foreach ($pivotKeys as $key => $id)
					{
						$pivotItem->where($key, $id);
					}

					// if id exists, add it to where clause and unset it
					if (isset($pivotData['id']) && (int) $pivotData['id'])
					{
						$pivotItem->where('id', $pivotData['id']);
						unset($pivotData['id']);
					}

					// attempt to update and if it doesn't work, insert a new record
					if (!$pivotItem->update($pivotData))
					{
						if ($pivotTimestamps)
							$pivotData['created_at'] = $timestamp;

						DB::table($pivotTable)->insert($pivotData);
					}

					if (!in_array((int) $pivotId, $pivotIdsSaved))
						$pivotIdsSaved[] = (int) $pivotId;
				}

				if (empty($pivotIdsSaved))
					DB::table($pivotTable)
						->where($this->getForeignKey(), $this->id)
						->delete();
				else
					DB::table($pivotTable)
						->where($this->getForeignKey(), $this->id)
						->whereNotIn($item->getForeignKey(), $pivotIdsSaved)
						->delete();
			}
		}

		// remove any items no longer present in input data
		if ($index != "pivot")
		{
			foreach ($items as $item)
			{
				if (!in_array((int) $item->id, $idsSaved))
				{
					// check for pivot data and delete pivot item instead of item if it exists
					if (isset($itemData['pivot']))
					{
						DB::table($pivotTable)
							->where($this->getForeignKey(), $this->id)
							->where($item->getForeignKey(), $item->id)
							->delete();
					}
					else
					{
						$item->delete();
					}
				}
			}
		}
	}

	/**
	 * Format values for insertion into database.
	 *
	 * @param  array    $values
	 * @param  mixed    $create
	 * @return array
	 */
	public function formatValuesForDb($values, $create = null)
	{
		// if create is not specified, use ID to determine whether creating or updating record
		if (is_null($create))
			$create = is_null($this->id);

		$values = $this->formatValuesForTypes($values);
		$values = $this->formatValuesForSpecialFormats($values);
		$values = $this->formatValuesForModel($values, $create);

		return $values;
	}

	/**
	 * Fill model with formatted values.
	 *
	 * @param  array    $values
	 * @return void
	 */
	public function fillFormattedValues($values)
	{
		$values = $this->formatValuesForDb($values);

		$this->fill($values);
	}

	/**
	 * Format values based on the model's special field types for data insertion into database.
	 *
	 * @param  array    $values
	 * @return array
	 */
	public function formatValuesForTypes($values)
	{
		foreach ($this->getFieldTypes() as $field => $type)
		{
			$value      = isset($values[$field]) ? $values[$field] : null;
			$unsetValue = false;

			switch ($type)
			{
				case "checkbox":
					$value = (!is_null($value) && $value != false); break;

				case "date":
					$value = (!is_null($value) && $value != "" ? date('Y-m-d', strtotime($value)) : null); break;

				case "date-time":
				case "datetime":
				case "timestamp":
					$value = (!is_null($value) && $value != "" ? date('Y-m-d H:i:s', strtotime($value)) : null); break;

				case "date-not-null":
					$value = (!is_null($value) && $value != "" ? date('Y-m-d', strtotime($value)) : "0000-00-00"); break;

				case "date-time-not-null":
				case "datetime-not-null":
				case "timestamp-not-null":
					$value = (!is_null($value) && $value != "" ? date('Y-m-d H:i:s', strtotime($value)) : "0000-00-00 00:00:00"); break;

				case "slug":
					$value = Format::slug($value); break;

				case "unique-slug":
					$value = Format::uniqueSlug($value, $this->table, $field, $this->id); break;
			}

			// set a timestamp based on the checked status of a checkbox
			if (in_array(substr($type, 0, 18), ['checkbox-timestamp', 'checkbox-date-time', 'checkbox-datetime']))
			{
				$value = (!is_null($value) && $value != false);

				$typeArray = explode(':', $type);

				// set checkbox value in case checkbox field actually exists in model
				$values[$field] = (bool) $value;

				// set field name for timestamp field
				if (count($typeArray) == 1)
					$field .= "_at";
				else
					$field = $typeArray[1];

				// set timestamp based on checkbox status and previous timestamp value
				if ($value)
				{
					if (is_null($this->{$field}))
						$value = date('Y-m-d H:i:s');
					else
						$unsetValue = true;
				}
				else
				{
					$value = null;
				}
			}

			if (!$unsetValue)
			{
				$values[$field] = $value;
			}
			else
			{
				if (isset($values[$field]))
					unset($values[$field]);
			}
		}

		return $values;
	}

	/**
	 * Format values based on the model's special field types for data insertion into database.
	 *
	 * @param  array    $values
	 * @return array
	 */
	public function formatValuesForSpecialFormats($values)
	{
		// automatically trim field values if config set
		if (config('form.auto_trim'))
		{
			foreach ($values as $field => $value)
			{
				if (is_string($value))
					$values[$field] = $this->trimValue($value);
			}
		}

		foreach ($this->getFieldFormatsForDb() as $field => $formats)
		{
			if (isset($values[$field]))
			{
				$values[$field] = $this->formatValueForSpecialFormats($field, $values, $formats);
			}
			else
			{
				if (is_string($formats))
					$formats = [$formats];

				if (in_array('blank-if-not-set', $formats))
					$values[$field] = "";

				if (in_array('null-if-not-set', $formats))
					$values[$field] = null;

				if (in_array('pivot-array', $formats))
					$values[$field] = ['pivot' => []];
			}
		}

		return $values;
	}

	/**
	 * Format values based on the model's special formats for data insertion into database.
	 *
	 * @param  string   $field
	 * @param  array    $values
	 * @param  array    $formats
	 * @return array
	 */
	public function formatValueForSpecialFormats($field, $values, $formats)
	{
		if (is_object($values))
			$value = isset($values->{$field}) ? $values->{$field} : null;
		else
			$value = isset($values[$field]) ? $values[$field] : null;

		if (is_string($formats))
			$formats = [$formats];

		foreach ($formats as $format)
		{
			$format = explode(':', $format);

			$fieldTested = isset($format[1]) && $format[1] != "" ? $format[1] : $field;
			$valueTested = isset($values[$fieldTested]) ? $values[$fieldTested] : null;

			switch ($format[0])
			{
				case "false-if-null":
					if (is_null($valueTested))
						$value = false;

					break;

				case "true-if-null":
					if (is_null($valueTested))
						$value = true;

					break;

				case "false-if-not-null":
					if (!is_null($valueTested))
						$value = false;

					break;

				case "true-if-not-null":
					if (!is_null($valueTested))
						$value = true;

					break;

				case "false-if-blank":
					if ($valueTested == "" || $valueTested == "0000-00-00" || $valueTested == "0000-00-00 00:00:00")
						$value = false;

					break;

				case "true-if-blank":
					if ($valueTested == "" || $valueTested == "0000-00-00" || $valueTested == "0000-00-00 00:00:00")
						$value = true;

					break;

				case "null-if-blank":
					if ($valueTested == "" || $valueTested == "0000-00-00" || $valueTested == "0000-00-00 00:00:00")
						$value = null;

					break;

				case "false-if-not-blank":
					if ($valueTested != "")
						$value = false;

					break;

				case "true-if-not-blank":
					if ($valueTested != "")
						$value = true;

					break;

				case "null-if-not-blank":
					if ($valueTested != "")
						$value = null;

					break;

				case "json":
					$value = json_encode($value);

					break;

				case "json-or-null":
					if (is_null($value) || empty($value))
						$value = null;
					else
						$value = json_encode($value);

					break;

				case "trim":
					$value = $this->trimValue($value);

					break;

				case "uppercase-first":
					$value = ucfirst(trim($value));

					break;

				case "uppercase-words":
					$value = ucwords(trim($value));

					break;

				case "uppercase":
					$value = strtoupper($value);

					break;

				case "lowercase":
					$value = strtolower($value);

					break;
			}
		}

		return $value;
	}

	/**
	 * Trim a value or array of values.
	 *
	 * @param  mixed    $value
	 * @return array
	 */
	private function trimValue($value)
	{
		if (is_array($value))
		{
			foreach ($value as $v => $subValue)
			{
				if (is_string($subValue))
					$value[$v] = trim($subValue);
			}
		}
		else
		{
			$value = trim($value);
		}

		return $value;
	}

	/**
	 * Custom formatting method for a specific model. This function exists to be extended by a specific model to allow
	 * custom formatting before data is inserted into the database.
	 *
	 * @param  array    $values
	 * @param  boolean  $create
	 * @return array
	 */
	public function formatValuesForModel($values, $create = false)
	{
		return $values;
	}

	/**
	 * Get a cached value by key.
	 *
	 * @param  mixed    $key
	 * @return mixed
	 */
	public function getCached($key = null)
	{
		if (is_null($key))
			return $this->cached;

		$keyArray = explode('.', $key);

		switch (count($keyArray))
		{
			case 1:

				if (isset($this->cached[$keyArray[0]]))
					return $this->cached[$keyArray[0]];

				break;

			case 2:

				if (isset($this->cached[$keyArray[0]][$keyArray[1]]))
					return $this->cached[$keyArray[0]][$keyArray[1]];

				break;

			case 3:

				if (isset($this->cached[$keyArray[0]][$keyArray[1]][$keyArray[2]]))
					return $this->cached[$keyArray[0]][$keyArray[1]][$keyArray[2]];

				break;
		}

		return null;
	}

	/**
	 * Set a cached value by key.
	 *
	 * @param  string   $key
	 * @param  mixed    $value
	 * @return mixed
	 */
	public function setCached($key, $value = null)
	{
		if (is_null($key))
			return $this->cached;

		$keyArray = explode('.', $key);

		switch (count($keyArray))
		{
			case 1:

				$this->cached[$keyArray[0]] = $value;

				break;

			case 2:

				if (!isset($this->cached[$keyArray[0]]))
					$this->cached[$keyArray[0]] = [];

				$this->cached[$keyArray[0]][$keyArray[1]] = $value;

				break;

			case 3:

				if (!isset($this->cached[$keyArray[0]]))
					$this->cached[$keyArray[0]] = [];

				if (!isset($this->cached[$keyArray[0]][$keyArray[1]]))
					$this->cached[$keyArray[0]][$keyArray[1]] = [];

				$this->cached[$keyArray[0]][$keyArray[1]][$keyArray[2]] = $value;

				break;
		}

		return null;
	}

	/**
	 * Get a static cached value by key.
	 *
	 * @param  mixed    $key
	 * @return mixed
	 */
	public static function getStaticCached($key = null)
	{
		if (is_null($key))
			return static::$staticCached;

		$keyArray = explode('.', $key);

		switch (count($keyArray))
		{
			case 1:

				if (isset(static::$staticCached[$keyArray[0]]))
					return static::$staticCached[$keyArray[0]];

				break;

			case 2:

				if (isset(static::$staticCached[$keyArray[0]][$keyArray[1]]))
					return static::$staticCached[$keyArray[0]][$keyArray[1]];

				break;

			case 3:

				if (isset(static::$staticCached[$keyArray[0]][$keyArray[1]][$keyArray[2]]))
					return static::$staticCached[$keyArray[0]][$keyArray[1]][$keyArray[2]];

				break;
		}

		return null;
	}

	/**
	 * Set a static cached value by key.
	 *
	 * @param  string   $key
	 * @param  mixed    $value
	 * @return mixed
	 */
	public static function setStaticCached($key, $value = null)
	{
		if (is_null($key))
			return static::$staticCached;

		$keyArray = explode('.', $key);

		switch (count($keyArray))
		{
			case 1:

				static::$staticCached[$keyArray[0]] = $value;

				break;

			case 2:

				if (!isset(static::$staticCached[$keyArray[0]]))
					static::$staticCached[$keyArray[0]] = [];

				static::$staticCached[$keyArray[0]][$keyArray[1]] = $value;

				break;

			case 3:

				if (!isset(static::$staticCached[$keyArray[0]]))
					static::$staticCached[$keyArray[0]] = [];

				if (!isset(static::$staticCached[$keyArray[0]][$keyArray[1]]))
					static::$staticCached[$keyArray[0]][$keyArray[1]] = [];

				static::$staticCached[$keyArray[0]][$keyArray[1]][$keyArray[2]] = $value;

				break;
		}

		return null;
	}

	/**
	 * Create a model item and save the input data to the model.
	 *
	 * @param  mixed    $input
	 * @param  boolean  $saveRelational
	 * @return object
	 */
	public static function formatCreate($input = null, $saveRelational = false)
	{
		$item = new static;

		$item->formatSave($input, true, $saveRelational);

		return $item;
	}

	/**
	 * Get the model by a field other than its ID.
	 *
	 * @param  string   $field
	 * @param  string   $value
	 * @param  mixed    $relations
	 * @param  boolean  $returnQuery
	 * @return object
	 */
	public static function findBy($field, $value, $relations = [], $returnQuery = false)
	{
		$item = new static;

		if (is_null($relations) || is_bool($relations))
		{
			// if relations is boolean, assume it to be returnQuery instead
			if (is_bool($relations))
				$returnQuery = $relations;

			$relations = [];
		}

		$item = $item->where($field, $value);

		if ((is_array($relations) && !empty($relations)) || is_string($relations))
			$item = $item->with($relations);

		if (!$returnQuery)
			$item = $item->first();

		return $item;
	}

	/**
	 * Get the model by its slug.
	 *
	 * @param  string   $slug
	 * @param  mixed    $relations
	 * @param  boolean  $returnQuery
	 * @return object
	 */
	public static function findBySlug($slug, $relations = [], $returnQuery = false)
	{
		return static::findBy('slug', $slug, $relations, $returnQuery);
	}

	/**
	 * Get the model by its slug, stripped of dashes.
	 *
	 * @param  string   $slug
	 * @param  mixed    $relations
	 * @param  boolean  $returnQuery
	 * @return object
	 */
	public static function findByDashlessSlug($slug, $relations = [], $returnQuery = false)
	{
		return static::findBy(DB::raw('replace(slug, \'-\', \'\')'), str_replace('-', '', $slug), $relations, $returnQuery);
	}

	/**
	 * Format an array key.
	 *
	 * @param  string   $key
	 * @param  mixed    $camelizeArrayKeys
	 * @return string
	 */
	public static function formatArrayKey($key, $camelizeArrayKeys = null)
	{
		if ($camelizeArrayKeys === null)
			$camelizeArrayKeys = config('form.camelize_array_keys');

		if ($camelizeArrayKeys)
			$key = camel_case($key);

		return $key;
	}

	/**
	 * Get an attribute set for the model.
	 *
	 * @param  string   $key
	 * @param  boolean  $related
	 * @return array
	 */
	public static function getAttributeSet($key = 'standard', $related = false)
	{
		if ($related)
			$attributeSet = isset(static::$relatedAttributeSets[$key]) ? static::$relatedAttributeSets[$key] : [];
		else
			$attributeSet = isset(static::$attributeSets[$key]) ? static::$attributeSets[$key] : [];

		foreach ($attributeSet as $attribute => $attributes)
		{
			if (is_string($attribute) && is_string($attributes))
			{
				$model = new static;

				if (strtolower(substr($attributes, 0, 4)) == "set:")
				{
					$attributeSegments = explode('.', $attribute);

					foreach ($attributeSegments as $attributeSegment)
					{
						if (method_exists($model, $attributeSegment))
						{
							$relationship = $model->{$attributeSegment}();

							if (is_callable([$relationship, 'getModel']))
							{
								$class = get_class($relationship->getModel());

								$model = new $class;
							}
						}
					}

					$set = substr($attributes, 4);

					if (method_exists($model, 'getAttributeSet'))
						$attributeSet[$attribute] = $model->getAttributeSet($set);
				}
				else
				{
					if (strtolower(substr($attributes, 0, 6)) == "class:" && method_exists($model, $attribute))
					{
						$class = explode(';', substr($attributes, 6));
						$set   = isset($class[1]) ? $class[1] : "standard";

						// add leading backslash to namespaced class if it doesn't already exist
						$class = $class[0];
						if (substr($class, 0, 1) != "\\")
							$class = "\\".$class;

						$model = new $class;

						if (method_exists($model, 'getAttributeSet'))
							$attributeSet[$attribute] = $model->getAttributeSet($set);
					}
				}
			}
		}

		return $attributeSet;
	}

}