<?php namespace Regulus\Formation;

use Illuminate\Database\Eloquent\Collection as Base;

use stdClass;
use Countable;
use Exception;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use CachingIterator;
use JsonSerializable;
use IteratorAggregate;
use Illuminate\Support\Debug\Dumper;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class Collection extends Base implements ArrayAccess, Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable {

	/**
	 * The default attribute set.
	 *
	 * @var    mixed
	 */
	protected $defaultAttributeSet = "standard";

	/**
	 * Create an array from a collection with attribute set limiting and the option to camelize array keys.
	 *
	 * @param  mixed   $attributeSet
	 * @param  mixed   $camelizeArrayKeys
	 * @return array
	 */
	public function toArray($attributeSet = null, $camelizeArrayKeys = null)
	{
		return array_map(function($value) use ($attributeSet, $camelizeArrayKeys)
		{
			return $value instanceof Arrayable ? $value->toArray($attributeSet, $camelizeArrayKeys) : $value;

		}, $this->items);
	}

	/**
	 * Create a limited array from a collection with attribute set limiting and the option to camelize array keys.
	 *
	 * @param  mixed   $attributeSet
	 * @param  mixed   $camelizeArrayKeys
	 * @return array
	 */
	public function toLimitedArray($attributeSet = null, $camelizeArrayKeys = null)
	{
		if (is_null($attributeSet))
			$attributeSet = $this->getDefaultAttributeSet();

		return $this->toArray($attributeSet, $camelizeArrayKeys);
	}

	/**
	 * Get the default attribute set.
	 *
	 * @return mixed
	 */
	public function getDefaultAttributeSet()
	{
		return $this->defaultAttributeSet;
	}

	/**
	 * Set the default attribute set.
	 *
	 * @param  string  $attributeSet
	 * @return mixed
	 */
	public function setDefaultAttributeSet($attributeSet)
	{
		$this->defaultAttributeSet = $attributeSet;
	}

}