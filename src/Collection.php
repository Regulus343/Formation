<?php namespace Regulus\Formation;

use Illuminate\Database\Eloquent\Collection as Base;

class Collection extends Base {

	/**
	 * Create an array from a collection with attribute set limiting and the option to camelize array keys.
	 *
	 * @param  Collection  $collection
	 * @param  mixed       $attributeSet
	 * @param  mixed       $camelizeArrayKeys
	 * @return array
	 */
	public function toArray($attributeSet = null, $camelizeArrayKeys = null)
	{
		$array = [];

		foreach ($this as $record)
		{
			if (method_exists($record, 'toLimitedArray'))
			{
				$array[] = $record->toArray($attributeSet, $camelizeArrayKeys);
			}
			else
			{
				$array[] = $record->toArray();
			}
		}

		return $array;
	}

	/**
	 * Create a limited array from a collection with attribute set limiting and the option to camelize array keys.
	 *
	 * @param  mixed   $attributeSet
	 * @param  mixed   $camelizeArrayKeys
	 * @return array
	 */
	public function toLimitedArray($attributeSet = 'standard', $camelizeArrayKeys = null)
	{
		return $this->toArray($attributeSet, $camelizeArrayKeys);
	}

}