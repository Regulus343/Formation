<?php namespace Regulus\Formation\Traits;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;

use Regulus\Formation\Collection;

use Regulus\Formation\Facade as Form;
use Regulus\TetraText\Facade as Format;

use Regulus\Formation\Builder;

trait Extended {

	/**
	 * The special typed fields for the model.
	 *
	 * @var array
	 */
	protected static $types = [
		/*
		'activated_at' => 'timestamp',
		'published_at' => 'checkbox-timestamp',
		*/
	];

	/**
	 * The special formatted fields for the model.
	 *
	 * @var array
	 */
	protected static $formats = [
		/*
		'title' => 'uppercase-first',
		*/
	];

	/**
	 * The special formatted fields for the model for saving to the database.
	 *
	 * @var array
	 */
	protected static $formatsForDb = [
		/*
		'description' => 'null-if-blank',
		*/
	];

	/**
	 * The methods to automatically include in a model's array / JSON object.
	 *
	 * @var array
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
	 * @var array
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
	 * @var array
	 */
	protected static $relatedAttributeSets = [
		/*
		'standard' => [
			'items' => 'set:standard', // this will use an attribute set from the model used for the 'items' relationship
		],
		*/
	];

	/**
	 * The cached attribute sets for the model.
	 *
	 * @var array
	 */
	protected static $cachedAttributeSets = [];

	/**
	 * The cached attribute sets for related models.
	 *
	 * @var array
	 */
	protected static $cachedRelatedAttributeSets = [];

	/**
	 * The cached relations from related attribute sets.
	 *
	 * @var array
	 */
	protected static $cachedRelationsFromAttributeSets = [];

	/**
	 * The related data requested for related models' arrays / JSON objects.
	 *
	 * @var mixed
	 */
	protected static $relatedDataRequested = null;

	/**
	 * The default attribute set.
	 *
	 * @var mixed
	 */
	protected static $defaultAttributeSet = "standard";

	/**
	 * The static cached data for the model.
	 *
	 * @var mixed
	 */
	protected static $staticCached;

	/**
	 * The content type for the model.
	 *
	 * @var string
	 */
	protected static $contentType = "Section";

	/**
	 * The foreign key for the model.
	 *
	 * @var mixed
	 */
	protected $foreignKey = null;

	/**
	 * The number of models to return for pagination.
	 *
	 * @var mixed
	 */
	protected $itemsPerPage = null;

	/**
	 * The default attribute set.
	 *
	 * @var mixed
	 */
	protected $selectedAttributeSet = null;

	/**
	 * The cached data for the model.
	 *
	 * @var mixed
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

		$this->selectAttributeSet(static::$defaultAttributeSet);
	}

	/**
	 * Create a new Eloquent query builder for the model.
	 *
	 * @param  \Illuminate\Database\Query\Builder  $query
	 * @return \Illuminate\Database\Eloquent\Builder|static
	 */
	public function newEloquentBuilder($query)
	{
		if (is_null($this->itemsPerPage) && config('form.paginator.items_per_page'))
		{
			$this->setPerPage(config('form.paginator.items_per_page'));
		}

		$this->setTransformConfig();

		return new Builder($query);
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
	 * Get the content type for the record.
	 *
	 * @return boolean
	 */
	public function getContentType()
	{
		return static::$contentType;
	}

	/**
	 * Set the transform config when using the trait outside of the Base model.
	 *
	 * @return void
	 */
	public function setTransformConfig()
	{
		if (empty(static::$attributeSets) && isset(static::$transformConfig) && is_array(static::$transformConfig))
		{
			if (isset(static::$transformConfig['arrayIncludedMethods']))
			{
				static::$arrayIncludedMethods = static::$transformConfig['arrayIncludedMethods'];
			}

			if (isset(static::$transformConfig['attributeSets']))
			{
				static::$attributeSets = static::$transformConfig['attributeSets'];
			}

			if (isset(static::$transformConfig['relatedAttributeSets']))
			{
				static::$relatedAttributeSets = static::$transformConfig['relatedAttributeSets'];
			}
		}
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
	 * @param  mixed   $attributeSet
	 * @param  mixed   $camelizeArrayKeys
	 * @return array
	 */
	public function toArray($attributeSet = null, $camelizeArrayKeys = null)
	{
		if (is_null($camelizeArrayKeys))
			$camelizeArrayKeys = config('form.camelize_array_keys');

		// turn the attributes into an array
		$attributes = $this->attributesToArray($attributeSet, $camelizeArrayKeys);

		// convert related data to arrays and merge it in
		$attributes = array_merge($attributes, $this->relationsToArray($camelizeArrayKeys));

		// prune the attributes that are not in the set
		$attributes = static::pruneAttributesBySet($attributes, $attributeSet);

		// camelize pivot data if necessary
		if ($camelizeArrayKeys && isset($attributes['pivot']))
		{
			$attributes['pivot'] = Format::camelizeKeys($attributes['pivot']);
		}

		return $attributes;
	}

	/**
	 * Convert the model instance to an array.
	 *
	 * @param  mixed   $attributeSet
	 * @param  mixed   $camelizeArrayKeys
	 * @return array
	 */
	public function toLimitedArray($attributeSet = null, $camelizeArrayKeys = null)
	{
		if (is_null($attributeSet))
			$attributeSet = $this->getSelectedAttributeSet();

		return $this->toArray($attributeSet, $camelizeArrayKeys);
	}

	/**
	 * Get an attribute array of all arrayable values.
	 *
	 * @param  array   $values
	 * @param  mixed   $camelizeArrayKeys
	 * @param  array   $relations
	 * @return array
	 */
	protected function getArrayableItems(array $values, $camelizeArrayKeys = null, $relations = false)
	{
		$visibleAttributes = $this->getVisible();

		if (count($visibleAttributes))
		{
			if ($relations)
			{
				foreach ($values as $key => $value)
				{
					if (!in_array(snake_case($key), $visibleAttributes))
					{
						unset($values[$key]);
					}
				}
			}
			else
			{
				$values = array_intersect_key($values, array_flip($visibleAttributes));
			}
		}

		if (count($this->getHidden()))
			$values = array_diff_key($values, array_flip($this->getHidden()));

		$formattedValues = [];

		if (is_null($camelizeArrayKeys))
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
	 * @param  mixed   $attributeSet
	 * @param  mixed   $camelizeArrayKeys
	 * @return array
	 */
	public function attributesToArray($attributeSet = null, $camelizeArrayKeys = null)
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
			$keyFormatted = static::formatArrayKey($key, $camelizeArrayKeys);

			if ((!array_key_exists($key, $attributes) && !array_key_exists($keyFormatted, $attributes)) || in_array($key, $mutatedAttributes))
				continue;

			$attribute = array_key_exists($key, $attributes) ? $attributes[$key] : $attributes[$keyFormatted];

			$attributes[$keyFormatted] = $this->castAttribute(
				$key, $attribute
			);

			if ($attributes[$keyFormatted] && ($value === 'date' || $value === 'datetime'))
			{
				$attributes[$keyFormatted] = $this->serializeDate($attribute);
			}

			if ($camelizeArrayKeys && (is_array($attributes[$keyFormatted]) || is_object($attributes[$keyFormatted])))
			{
				$attributes[$keyFormatted] = Format::camelizeKeys($attributes[$keyFormatted]);
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

		$attributeSet = static::getAttributeSet($attributeSet);

		// additionally, we will append the "array-included methods" which allows for more advanced specification
		$attributes = $this->addArrayIncludedMethods($attributes, $attributeSet, $camelizeArrayKeys);

		// run through relationships one more time and remove any attributes that are not allowed
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
	 * Trim attributes that aren't part of the set.
	 *
	 * @param  array   $attributes
	 * @param  mixed   $attributeSet
	 * @param  mixed   $camelizeArrayKeys
	 * @return array
	 */
	public static function pruneAttributesBySet($attributes, $attributeSet)
	{
		if (!is_null($attributeSet))
		{
			$attributeSet = static::getAttributeSet($attributeSet);

			$allowedAttributes = [];

			foreach ($attributeSet as $attribute => $attributeConfig)
			{
				if (!$attributeConfig->selectOnly)
				{
					$allowedAttributes[] = $attribute;
				}
			}

			if (!empty($attributeSet))
			{
				foreach ($attributes as $attribute => $value)
				{
					if (!in_array(snake_case($attribute), $allowedAttributes))
					{
						unset($attributes[$attribute]);
					}
				}
			}
		}

		return $attributes;
	}

	/**
	 * Get the model's relationships in array form.
	 *
	 * @param  mixed   $camelizeArrayKeys
	 * @return array
	 */
	public function relationsToArray($camelizeArrayKeys = null)
	{
		if (is_null($camelizeArrayKeys))
			$camelizeArrayKeys = config('form.camelize_array_keys');

		$attributes = [];

		$arrayIncludedMethodAttributes = array_keys(static::$arrayIncludedMethods);

		$class = get_class($this);

		foreach ($this->getArrayableRelations($camelizeArrayKeys) as $key => $value)
		{
			// ensure relationship is not being overridden by array-included methods
			if (!in_array(snake_case($key), $arrayIncludedMethodAttributes))
			{

				// If the values implements the Arrayable interface we can just call this
				// toArray method on the instances which will convert both models and
				// collections to their proper array form and we'll set the values.
				if ($value instanceof Arrayable || (is_object($value) && method_exists($value, 'toArray')))
				{
					$relationClass = get_class($value);

					$collection = strpos($relationClass, 'Collection') !== false;

					if ($collection && $value->count())
						$relationClass = get_class($value[0]);

					$relationModel = new $relationClass;

					$relatedDataRequested = [];

					if (isset(static::$relatedDataRequested[get_class($this)]))
						$relatedDataRequested = static::$relatedDataRequested[get_class($this)];

					// if "related data requested" is set, adjust visible and hidden arrays for related items
					if (is_callable([$value, 'setVisible']) && !is_null($relatedDataRequested))
					{
						$visibleAttributes = [];

						if (method_exists($value, 'scopeLimitRelatedData'))
						{
							$value->limitRelatedData();
						}

						// get array of visible attributes
						if (isset($relatedDataRequested[$key]))
						{
							// if attribute set is string (relationship was polymorphic), get attribute set by key
							if (is_string($relatedDataRequested[$key]))
							{
								$relatedDataRequested[$key] = $relationModel::getAttributeSet($relatedDataRequested[$key]);
							}

							$relatedDataRequestedForKey = [];
							foreach ($relatedDataRequested[$key] as $attribute => $attributeConfig)
							{
								// check if attribute is relation that loops back to root model (and ignore it if so)
								$attributeIsRootModel = false;

								if ($attributeConfig->isRelation && method_exists($relationModel, $attribute))
								{
									$relationModelClass = get_class($relationModel->{$attribute}()->getModel());

									if ($relationModelClass == $class)
									{
										$attributeIsRootModel = true;
									}
								}

								// ensure attribute wasn't intended for selection only
								if (!$attributeConfig->selectOnly && !$attributeIsRootModel)
								{
									$relatedDataRequestedForKey[$attribute] = $attributeConfig;
								}
							}

							$visibleAttributes = array_keys($relatedDataRequestedForKey);
						}

						// set visible and hidden arrays
						if (!empty($visibleAttributes) && is_array($visibleAttributes) && !in_array('*', $visibleAttributes))
						{
							if ($collection)
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

					$attributeSet = isset($relatedDataRequestedForKey) ? $relatedDataRequestedForKey : null;

					$relation = $value->toArray($attributeSet, $camelizeArrayKeys);
				}

				// If the value is null, we'll still go ahead and set it in this list of
				// attributes since null is used to represent empty relationships if
				// if it a has one or belongs to type relationships on the models.
				elseif (is_null($value))
				{
					$relation = $value;
				}

				$key = static::formatArrayKey($key, $camelizeArrayKeys);

				// If the relation value has been set, we will set it on this attributes
				// list for returning. If it was not arrayable or null, we'll not set
				// the value on the array because it is some type of invalid value.
				if (isset($relation) || is_null($value))
				{
					$attributes[$key] = $relation;
				}

				unset($relation);
			}
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
		return $this->getArrayableItems($this->relations, $camelizeArrayKeys, true);
	}

	/**
	 * Add the array-included methods to an attributes array.
	 *
	 * @param  array   $attributes
	 * @param  mixed   $attributeSet
	 * @param  mixed   $camelizeArrayKeys
	 * @return array
	 */
	public function addArrayIncludedMethods($attributes = [], $attributeSet = null, $camelizeArrayKeys = null)
	{
		if (is_null($camelizeArrayKeys))
			$camelizeArrayKeys = config('form.camelize_array_keys');

		$attributeSet = static::getAttributeSet($attributeSet);

		$visible = $this->getVisible();
		$hidden  = $this->getHidden();

		foreach ($this->getArrayIncludedMethods() as $key => $method)
		{
			if (is_null($attributeSet) || (is_array($attributeSet) && isset($attributeSet[$key]) && !$attributeSet[$key]->ignoreMethod))
			{
				$keyFormatted = static::formatArrayKey($key, $camelizeArrayKeys);

				if (is_string($method))
				{
					if (substr($method, -1) != ")")
						$method .= "()";

					$method = Format::getMethodFromString($method);

					static::$arrayIncludedMethods[$key] = $method;
				}

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
				{
					$attributes[$keyFormatted] = call_user_func_array([$this, $method['name']], $method['parameters']);
				}
			}
		}

		return $attributes;
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
	 * Create a collection for the models.
	 *
	 * @return array
	 */
	public function newCollection(array $models = [])
	{
		return new Collection($models);
	}

	public function newBuilder(array $models = [])
	{
		return new Collection($models);
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
		static::$arrayIncludedMethods[snake_case($attribute)] = $method;
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
	 * Select a particular attribute set of data.
	 *
	 * @param  mixed    $attributeSet
	 * @param  boolean  $limitRelatedData
	 * @return QueryBuilder
	 */
	public function scopeSelectAttributeSet($query, $attributeSet = 'select', $limitRelatedData = false)
	{
		$selectableAttributes = static::getSelectableAttributeSet($attributeSet);

		if (!empty($selectableAttributes))
		{
			$query->select($selectableAttributes);
		}

		$query->setDefaultAttributeSet($attributeSet);

		if ($limitRelatedData)
		{
			$query->limitRelatedData($attributeSet);
		}

		return $query;
	}

	/**
	 * Select a particular attribute set of data (alias for selectAttributeSet scope).
	 *
	 * @param  mixed    $attributeSet
	 * @param  boolean  $limitRelatedData
	 * @return QueryBuilder
	 */
	public function scopeSelectSet($query, $attributeSet = 'select', $limitRelatedData = false)
	{
		return $query->selectAttributeSet($attributeSet, $limitRelatedData);
	}

	/**
	 * Get the JSON-included methods for the model.
	 *
	 * @param  mixed    $relatedAttributeSet
	 * @param  boolean  $limitSelect
	 * @return QueryBuilder
	 */
	public function scopeLimitRelatedData($query, $relatedAttributeSet = null, $limitSelect = true)
	{
		if (is_null($relatedAttributeSet))
		{
			$relatedAttributeSet = $query->getDefaultAttributeSet();
		}
		else
		{
			$query->setDefaultAttributeSet($relatedAttributeSet);
		}

		if (is_string($relatedAttributeSet))
			$relatedAttributeSet = $this->getAttributeSet($relatedAttributeSet, true);

		static::$relatedDataRequested[get_class($this)] = $relatedAttributeSet;

		$with = [];

		foreach ($relatedAttributeSet as $relation => $attributes)
		{
			if ($limitSelect)
			{
				$with[$relation] = function($relationQuery) use ($relation, $attributes)
				{
					if (is_array($attributes))
					{
						// remove array-included methods and relationships from select statement
						$model  = $relationQuery->getModel();
						$prefix = !is_null($model->getTable()) ? $model->getTable().'.' : '';

						$formattedAttributes = [];

						foreach ($attributes as $attribute => $attributeConfig)
						{
							if (!$attributeConfig->isRelation && (!$attributeConfig->hasMethod || in_array($attribute, $this->fillable)))
							{
								$formattedAttributes[] = $prefix.$attribute;
							}
						}

						if (!empty($formattedAttributes))
						{
							$relationQuery->select($formattedAttributes);
						}
					}
				};
			}
			else
			{
				$with[] = $relation;
			}
		}

		$query->with($with);

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
	 * @param  mixed    $record
	 * @param  mixed    $input
	 * @param  mixed    $action
	 * @return array
	 */
	public static function validationRules($record = null, $input = null, $action = null)
	{
		if (is_integer($record) || is_string($record))
			$record = static::find($record);

		if (is_null($input))
			$input = Input::all();

		return [];
	}

	/**
	 * Set the validation rules for the model.
	 *
	 * @param  mixed    $record
	 * @param  mixed    $input
	 * @param  mixed    $action
	 * @return void
	 */
	public static function setValidationRulesForModel($record = null, $input = null, $action = null)
	{
		if ($record)
			$record->setValidationRules($input, $action);
		else
			static::setValidationRulesForNew($input, $action);
	}

	/**
	 * Set the validation rules for the model for a new record.
	 *
	 * @param  mixed    $input
	 * @param  mixed    $action
	 * @return void
	 */
	public static function setValidationRulesForNew($input = null, $action = null)
	{
		if (is_null($input))
			$input = Input::all();

		Form::setValidationRules(static::validationRules(null, $input, $action), $input);
	}

	/**
	 * Set the validation rules for the model.
	 *
	 * @param  mixed    $input
	 * @param  mixed    $action
	 * @return void
	 */
	public function setValidationRules($input = null, $action = null)
	{
		if (is_null($input))
			$input = Input::all();

		Form::setValidationRules(static::validationRules($this, $input, $action), $input);
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

			$model->{$field} = $model->formatValueForSpecialFormats($field, $model->toArray(null, false), $formats);
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
		switch ($type)
		{
			case "date":

				if ($value != "0000-00-00" && $value != "" && !is_null($value))
					$value = date(Form::getDateFormat(), strtotime($value));

				break;

			case "date-time":
			case "datetime":
			case "timestamp":

				if ($value != "0000-00-00 00:00:00" && $value != "" && !is_null($value))
					$value = date(Form::getDateTimeFormat(), strtotime($value));

				break;
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
	public function formatSave($input = null, $create = null)
	{
		if (is_null($input))
			$input = Input::all();

		// if create is not specified, use ID to determine whether creating or updating record
		if (is_null($create))
			$create = is_null($this->id);

		// format data for special types and special formats
		$input = $this->formatValuesForDb($input, $create);

		// save the data
		$this->fill($input)->save();

		// execute save triggers for custom post-save logic in your model
		$this->executeSaveTriggers($create);
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

		$values = $this->formatValuesForTypes($values, $create);
		$values = $this->formatValuesForSpecialFormats($values);
		$values = $this->formatValuesForModel($values);

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
	 * @param  boolean  $create
	 * @return array
	 */
	public function formatValuesForTypes($values, $create = false)
	{
		foreach ($this->getFieldTypes() as $field => $type)
		{
			$value      = isset($values[$field]) ? $values[$field] : null;
			$unsetValue = false;

			$typeArray = explode(':', $type);

			switch ($typeArray[0])
			{
				case "checkbox":

					$value = (!is_null($value) && $value != false);
					break;

				case "date":

					$value = (!is_null($value) && $value != "" ? date('Y-m-d', strtotime($value)) : null);
					break;

				case "date-time":
				case "datetime":
				case "timestamp":

					$value = (!is_null($value) && $value != "" ? date('Y-m-d H:i:s', strtotime($value)) : null);
					break;

				case "date-not-null":

					$value = (!is_null($value) && $value != "" ? date('Y-m-d', strtotime($value)) : "0000-00-00");
					break;

				case "date-time-not-null":
				case "datetime-not-null":
				case "timestamp-not-null":

					$value = (!is_null($value) && $value != "" ? date('Y-m-d H:i:s', strtotime($value)) : "0000-00-00 00:00:00");
					break;

				case "checkbox-timestamp":
				case "checkbox-date-time":
				case "checkbox-datetime":

					$value = (!is_null($value) && $value != false);

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

					break;

				case "slug":

					if (isset($typeArray[1]) && isset($values[$typeArray[1]]))
						$value = $values[$typeArray[1]];

					$value = Format::slug($value);
					break;

				case "unique-slug":

					if (isset($typeArray[1]) && isset($values[$typeArray[1]]))
						$value = $values[$typeArray[1]];

					$value = Format::uniqueSlug($value, $this->table, $field, $this->id);
					break;

				case "token":

					if ($create)
						$value = str_random(isset($typeArray[1]) ? $typeArray[1] : 32);
					else
						$unsetValue = true;

					break;
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

				case "false-if-set":
				case "false-if-true":

					if ($valueTested)
						$value = false;

					break;

				case "true-if-set":
				case "true-if-true":

					if ($valueTested)
						$value = true;

					break;

				case "null-if-set":
				case "null-if-true":

					if ($valueTested)
						$value = null;

					break;

				case "false-if-not-set":
				case "false-if-false":

					if (!$valueTested)
						$value = false;

					break;

				case "true-if-not-set":
				case "true-if-false":

					if (!$valueTested)
						$value = true;

					break;

				case "null-if-not-set":
				case "null-if-false":

					if (!$valueTested)
						$value = null;

					break;

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

					if (is_string($value))
						$value = ucfirst(trim($value));

					break;

				case "uppercase-words":

					if (is_string($value))
						$value = ucwords(trim($value));

					break;

				case "uppercase":

					if (is_string($value))
						$value = strtoupper($value);

					break;

				case "lowercase":

					if (is_string($value))
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
	 * @return array
	 */
	public function formatValuesForModel($values)
	{
		return $values;
	}

	/**
	 * Custom logic in your model that is run post-save.
	 *
	 * @param  boolean  $create
	 * @return void
	 */
	public function executeSaveTriggers($create = false)
	{
		//
	}

	/**
	 * Get the current attribute set.
	 *
	 * @return mixed
	 */
	public function getSelectedAttributeSet()
	{
		return $this->selectedAttributeSet;
	}

	/**
	 * Set the current attribute set.
	 *
	 * @param  string  $attributeSet
	 * @return mixed
	 */
	public function selectAttributeSet($attributeSet)
	{
		$this->selectedAttributeSet = $attributeSet;
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
	 * @param  boolean  $returnQuery
	 * @return object
	 */
	public static function findBy($field, $value, $returnQuery = false)
	{
		$item = static::where($field, $value);

		if (!$returnQuery)
			$item = $item->first();

		return $item;
	}

	/**
	 * Get the model by its slug.
	 *
	 * @param  string   $slug
	 * @param  boolean  $returnQuery
	 * @return object
	 */
	public static function findBySlug($slug, $returnQuery = false)
	{
		$item = static::where(function($query) use ($slug)
		{
			$query
				->where('slug', $slug)
				->orWhere(DB::raw('replace(slug, \'-\', \'\')'), str_replace('-', '', $slug));
		});

		if (!$returnQuery)
			$item = $item->first();

		return $item;
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
		if (is_null($camelizeArrayKeys))
			$camelizeArrayKeys = config('form.camelize_array_keys');

		if ($camelizeArrayKeys)
			$key = camel_case($key);
		else
			$key = snake_case($key);

		return $key;
	}

	/**
	 * Get an attribute set for the model.
	 *
	 * @param  string   $key
	 * @param  boolean  $related
	 * @param  boolean  $selectable
	 * @return array
	 */
	public static function getAttributeSet($key = 'standard', $related = false)
	{
		// attribute set is already an array; return it
		if (is_array($key))
			return $key;

		$model = new static;
		$class = get_class($model);

		if ($related)
			$attributeSetRaw = isset(static::$relatedAttributeSets[$key]) ? static::$relatedAttributeSets[$key] : [];
		else
			$attributeSetRaw = isset(static::$attributeSets[$key]) ? static::$attributeSets[$key] : [];

		$selectable = $key == "select";

		// if key is "select" and a "select" attribute set does not exist, return standard set
		if ($key == "select" && empty($attributeSetRaw))
		{
			$key = static::$defaultAttributeSet;

			if ($related)
				$attributeSetRaw = isset(static::$relatedAttributeSets[$key]) ? static::$relatedAttributeSets[$key] : [];
			else
				$attributeSetRaw = isset(static::$attributeSets[$key]) ? static::$attributeSets[$key] : [];

			$selectable = true;
		}

		$cached = $related ? isset(static::$cachedRelatedAttributeSets[$class][$key]) : isset(static::$cachedAttributeSets[$class][$key]);

		if (!$cached)
		{
			// if key is "fillable" and a "fillable" attribute set does not exist, return model's fillable array (plus the "id" attribute)
			if ($key == "fillable" && !$related)
			{
				$attributeSetRaw = array_merge(['id'], $model->getFillable());
			}

			$attributeSet = [];

			$attributeSetKeys = array_keys($attributeSetRaw);

			$multipleSets = !empty($attributeSetKeys) && is_string($attributeSetKeys[0]);

			if ($multipleSets)
			{
				foreach ($attributeSetRaw as $attribute => $attributes)
				{
					$polymorphic = false;

					if (is_string($attribute) && is_string($attributes))
					{
						if (substr($attributes, 0, 4) == "set:")
						{
							$attributeSegments = explode('.', $attribute);

							$modelUsed = $model;

							foreach ($attributeSegments as $s => $attributeSegment)
							{
								if (method_exists($modelUsed, $attributeSegment))
								{
									$relationship = $modelUsed->{$attributeSegment}();

									if ($related && is_callable([$relationship, 'getModel']))
									{
										$class = get_class($relationship->getModel());

										$polymorphic = get_class($relationship) == "Illuminate\Database\Eloquent\Relations\MorphTo";

										$modelUsed = new $class;
									}
								}
							}

							$set = substr($attributes, 4);

							if (is_object($modelUsed) && method_exists($modelUsed, 'getAttributeSet'))
							{
								$attributeSetRaw[$attribute] = $polymorphic ? $set : $modelUsed->getAttributeSet($set);
							}
							else
							{
								$attributeSetRaw[$attribute] = [];
							}
						}
						else
						{
							if (strtolower(substr($attributes, 0, 6)) == "class:" && method_exists($model, $attribute))
							{
								$class = explode(';', substr($attributes, 6));
								$set   = isset($class[1]) ? $class[1] : static::$defaultAttributeSet;

								// add leading backslash to namespaced class if it doesn't already exist
								$class = $class[0];
								if (substr($class, 0, 1) != "\\")
									$class = "\\".$class;

								$model = new $class;

								if (method_exists($model, 'getAttributeSet'))
								{
									$attributeSetRaw[$attribute] = $model->getAttributeSet($set);
								}
								else
								{
									$attributeSetRaw[$attribute] = [];
								}
							}
						}
					}
				}

				$attributeSet = $attributeSetRaw;
			}
			else
			{
				$attributeSet = [];

				foreach ($attributeSetRaw as $attribute)
				{
					if (substr($attribute, 0, 4) == "set:")
					{
						$set = substr($attribute, 4);

						if ($set != $key)
						{
							$attributeSet = array_merge($attributeSet, array_keys(static::getAttributeSet($set, $related)));
						}
					}
					else
					{
						$attributeSet[] = $attribute;
					}
				}
			}

			$prefix = !is_null($model->getTable()) ? $model->getTable().'.' : '';

			if ($multipleSets)
			{
				$attributeSets = [];

				foreach ($attributeSet as $attributeSetKey => $attributeSetListed)
				{
					if (!is_string($attributeSetListed))
					{
						$attributeSets[$attributeSetKey] = static::formatAttributeSet($attributeSetListed, null, $model);
					}
					else
					{
						$attributeSets[$attributeSetKey] = $attributeSetListed;
					}
				}
			}
			else
			{
				$attributeSets = static::formatAttributeSet($attributeSet, $prefix, $model);
			}

			if ($related)
			{
				static::$cachedRelatedAttributeSets[$class][$key] = $attributeSets;
			}
			else
			{
				static::$cachedAttributeSets[$class][$key] = $attributeSets;
			}
		}

		return $related ? static::$cachedRelatedAttributeSets[$class][$key] : static::$cachedAttributeSets[$class][$key];
	}

	/**
	 * Get a selectable attribute set for the model.
	 *
	 * @param  string   $key
	 * @param  boolean  $related
	 * @return array
	 */
	public static function getSelectableAttributeSet($key = 'select', $related = false)
	{
		if ($key == static::$defaultAttributeSet)
		{
			$key = "select";
		}

		$attributeSet = static::getAttributeSet($key, $related);

		$formattedAttributeSet = [];

		foreach ($attributeSet as $attribute => $attributeConfig)
		{
			if (!$attributeConfig->isRelation && (!$attributeConfig->hasMethod || $attributeConfig->isFillable) && $attribute != "pivot")
			{
				$formattedAttributeSet[] = $attributeConfig->attribute;
			}
		}

		return $formattedAttributeSet;
	}

	/**
	 * Get the relations from the related attribute sets.
	 *
	 * @return array
	 */
	public static function getRelationsFromAttributeSets()
	{
		$class = static::class;

		if (!isset(static::$cachedRelationsFromAttributeSets[$class]))
		{
			$relations = [];

			foreach (static::$relatedAttributeSets as $attributeSet => $relationsInSet)
			{
				$relations = array_merge($relations, array_keys($relationsInSet));
			}

			static::$cachedRelationsFromAttributeSets[$class] = array_unique($relations);
		}

		return static::$cachedRelationsFromAttributeSets[$class];
	}

	/**
	 * Format an attribute set.
	 *
	 * @param  array    $attributeSet
	 * @param  mixed    $prefix
	 * @param  mixed    $model
	 * @return array
	 */
	public static function formatAttributeSet($attributeSet, $prefix = null, $model = null)
	{
		if (is_array($attributeSet) && count($attributeSet) && is_object(end($attributeSet))) // return if already formatted
			return $attributeSet;

		if (is_null($prefix))
			$prefix = "";

		if (is_null($model))
			$model = new static;

		$arrayIncludedMethods = array_keys(static::$arrayIncludedMethods);

		$attributeSetFormatted = [];

		$relations = static::getRelationsFromAttributeSets();

		if (is_array($attributeSet))
		{
			foreach ($attributeSet as $a => $attribute)
			{
				$selectOnly   = false;
				$ignoreMethod = false;
				$isRelation   = in_array($attribute, $relations) || in_array(camel_case($attribute), $relations);

				if (substr($attribute, 0, 7) == "select:")
				{
					$attribute = substr($attribute, 7);

					$selectOnly = true;
				}

				if (substr($attribute, 0, 10) == "attribute:")
				{
					$attribute = substr($attribute, 10);

					$ignoreMethod = true;
				}

				$attributeSetFormatted[$attribute] = (object) [
					'attribute'    => $prefix.$attribute,
					'selectOnly'   => $selectOnly,
					'isRelation'   => $isRelation,
					'hasMethod'    => in_array($attribute, $arrayIncludedMethods),
					'isFillable'   => in_array($attribute, $model->fillable),
					'ignoreMethod' => $ignoreMethod,
				];
			}
		}

		return $attributeSetFormatted;
	}

}