<?php namespace Regulus\Formation;

use Illuminate\Database\Eloquent\Builder as Base;

use Regulus\Formation\Paginators\Paginator;
use Regulus\Formation\Traits\BuildsQueries;

class Builder extends Base {

	use BuildsQueries;

	/**
	 * The default attribute set.
	 *
	 * @var    mixed
	 */
	protected $defaultAttributeSet = "standard";

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

	/**
	 * Execute the query as a "select" statement.
	 *
	 * @param  array  $columns
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public function get($columns = ['*'])
	{
		$builder = $this->applyScopes();

		// If we actually found models we will also eager load any relationships that
		// have been specified as needing to be eager loaded, which will solve the
		// n+1 query issue for the developers to avoid running a lot of queries.
		if (count($models = $builder->getModels($columns)) > 0)
		{
			$models = $builder->eagerLoadRelations($models);
		}

		$collection = $builder->getModel()->newCollection($models);

		// set the default attribute set in case one has previously been defined in selectAttributeSet() scope
		$collection->setDefaultAttributeSet($this->getDefaultAttributeSet());

		return $collection;
	}

	/**
	 * Paginate the given query.
	 *
	 * @param  int  $page
	 * @param  string  $pageName
	 * @return \Regulus\Formation\Builder
	 */
	public function setPage($page = null)
	{
		Paginator::currentPageResolver(function() use ($page)
		{
			return $page;
		});

		return $this;
	}

	/**
	 * Paginate the given query.
	 *
	 * @param  int  $perPage
	 * @param  array  $columns
	 * @param  string  $pageName
	 * @param  int|null  $page
	 * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
	 *
	 * @throws \InvalidArgumentException
	 */
	public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
	{
		$page = $page ?: Paginator::resolveCurrentPage($pageName);

		$perPage = $perPage ?: $this->model->getPerPage();

		$results = ($total = $this->toBase()->getCountForPagination())
									? $this->forPage($page, $perPage)->get($columns)
									: $this->model->newCollection();

		$paginator = $this->paginator($results, $total, $perPage, $page, [
			'path'     => Paginator::resolveCurrentPath(),
			'pageName' => $pageName,
		]);

		// set the default attribute set in case one has previously been defined in selectAttributeSet() scope
		$paginator->setDefaultAttributeSet($this->getDefaultAttributeSet());

		return $paginator;
	}

	/**
	 * Paginate the given query into a simple paginator.
	 *
	 * @param  int  $perPage
	 * @param  array  $columns
	 * @param  string  $pageName
	 * @param  int|null  $page
	 * @return \Illuminate\Contracts\Pagination\Paginator
	 */
	public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
	{
		$page = $page ?: Paginator::resolveCurrentPage($pageName);

		$perPage = $perPage ?: $this->model->getPerPage();

		// Next we will set the limit and offset for this query so that when we get the
		// results we get the proper section of results. Then, we'll create the full
		// paginator instances for these results with the given page and per page.
		$this->skip(($page - 1) * $perPage)->take($perPage + 1);

		$paginator = $this->simplePaginator($this->get($columns), $perPage, $page, [
			'path'     => Paginator::resolveCurrentPath(),
			'pageName' => $pageName,
		]);

		// set the default attribute set in case one has previously been defined in selectAttributeSet() scope
		$paginator->setDefaultAttributeSet($this->getDefaultAttributeSet());

		return $paginator;
	}

}