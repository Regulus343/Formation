<?php namespace Regulus\Formation\Traits;

use Illuminate\Database\Concerns\BuildsQueries as Base;

use Illuminate\Container\Container;

use Regulus\Formation\Paginators\LengthAwarePaginator;
use Regulus\Formation\Paginators\Paginator;

trait BuildsQueries {

	use Base;

	/**
	 * Execute the query and get the first result.
	 *
	 * @param  array  $columns
	 * @return \Illuminate\Database\Eloquent\Model|object|static|null
	 */
	public function first($columns = ['*'])
	{
		$record = $this->take(1)->get($columns)->first();

		if (method_exists($record, 'selectAttributeSet'))
		{
			$record->selectAttributeSet($this->getDefaultAttributeSet());
		}

		return $record;
	}

	/**
	 * Create a new length-aware paginator instance.
	 *
	 * @param  \Illuminate\Support\Collection  $items
	 * @param  int  $total
	 * @param  int  $perPage
	 * @param  int  $currentPage
	 * @param  array  $options
	 * @return \Illuminate\Pagination\LengthAwarePaginator
	 */
	protected function paginator($items, $total, $perPage, $currentPage, $options)
	{
		return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
			'items', 'total', 'perPage', 'currentPage', 'options'
		));
	}

	/**
	 * Create a new simple paginator instance.
	 *
	 * @param  \Illuminate\Support\Collection  $items
	 * @param  int $perPage
	 * @param  int $currentPage
	 * @param  array  $options
	 * @return \Illuminate\Pagination\Paginator
	 */
	protected function simplePaginator($items, $perPage, $currentPage, $options)
	{
		return Container::getInstance()->makeWith(Paginator::class, compact(
			'items', 'perPage', 'currentPage', 'options'
		));
	}

}