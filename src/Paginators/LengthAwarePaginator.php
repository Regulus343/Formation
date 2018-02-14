<?php namespace Regulus\Formation\Paginators;

use Illuminate\Pagination\LengthAwarePaginator as Base;

use Countable;
use ArrayAccess;
use JsonSerializable;
use IteratorAggregate;
use Regulus\Formation\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;

use Regulus\TetraText\Facade as Format;

class LengthAwarePaginator extends Base implements Arrayable, ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Jsonable, LengthAwarePaginatorContract {

	/**
	 * The default attribute set.
	 *
	 * @var    mixed
	 */
	protected $defaultAttributeSet = "standard";

	/**
	 * Get the paginator as an array.
	 *
	 * @param  mixed   $attributeSet
	 * @param  mixed   $camelizeArrayKeys
	 * @param  mixed   $dataOnly
	 * @return array
	 */
	public function toArray($attributeSet = null, $camelizeArrayKeys = null, $dataOnly = null)
	{
		if (is_null($camelizeArrayKeys))
			$camelizeArrayKeys = config('form.camelize_array_keys');

		if (is_null($dataOnly))
			$dataOnly = config('form.paginator.array_data_only');

		$data = $this->items->toArray($attributeSet, $camelizeArrayKeys);

		if ($dataOnly)
		{
			return $data;
		}

		$array = [
			'current_page'   => $this->currentPage(),
			'first_page_url' => $this->url(1),
			'from'           => $this->firstItem(),
			'last_page'      => $this->lastPage(),
			'last_page_url'  => $this->url($this->lastPage()),
			'next_page_url'  => $this->nextPageUrl(),
			'path'           => $this->path,
			'per_page'       => $this->perPage(),
			'prev_page_url'  => $this->previousPageUrl(),
			'to'             => $this->lastItem(),
			'total'          => $this->total(),
		];

		if ($camelizeArrayKeys)
		{
			$array = Format::camelizeKeys($array);
		}

		$array['data'] = $data;

		return $array;
	}

	/**
	 * Create a limited array from a paginator with attribute set limiting and the option to camelize array keys.
	 *
	 * @param  mixed   $attributeSet
	 * @param  mixed   $camelizeArrayKeys
	 * @param  mixed   $dataOnly
	 * @return array
	 */
	public function toLimitedArray($attributeSet = null, $camelizeArrayKeys = null, $dataOnly = null)
	{
		if (is_null($attributeSet))
			$attributeSet = $this->getDefaultAttributeSet();

		return $this->toArray($attributeSet, $camelizeArrayKeys);
	}

	/**
	 * Get the collection as an array.
	 *
	 * @param  mixed   $attributeSet
	 * @param  mixed   $camelizeArrayKeys
	 * @return array
	 */
	public function collectionToArray($attributeSet = null, $camelizeArrayKeys = null)
	{
		return $this->toArray($attributeSet, $camelizeArrayKeys, true);
	}

	/**
	 * Create a limited array from a collection with attribute set limiting and the option to camelize array keys.
	 *
	 * @param  mixed   $attributeSet
	 * @param  mixed   $camelizeArrayKeys
	 * @return array
	 */
	public function collectionToLimitedArray($attributeSet = null, $camelizeArrayKeys = null)
	{
		return $this->toLimitedArray($attributeSet, $camelizeArrayKeys, true);
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