<?php

namespace DB\Mongo;

/**
 * Helper class to easily build aggregations pipeline arrays
 *
 * @see https://docs.mongodb.com/manual/aggregation/
 * @see https://docs.mongodb.com/manual/reference/operator/aggregation-pipeline/
 * @see https://docs.mongodb.com/php-library/current/reference/method/MongoDBCollection-aggregate/
 */
class aggregation
{

	/**
	 * The aggregation pipeline array
	 * @var array
	 */
	protected $pipeline = [];

	/**
	 * Add a $match step
	 *
	 * @example [field => value]
	 * @example [field => ['$gte' => value]]
	 * @example ['$or' => [[field => value], [field => ['$ne' => 1]]]
	 *
	 * @param mixed $filter The BSON filter to match against
	 */
	public function match($filter)
	{
		$this->pipeline[] = [
			'$match' => $filter,
		];

		return $this;
	}

	/**
	 * Add a lookup step
	 *
	 * @param string $from
	 * @param string $localField
	 * @param string $foreignField
	 * @param string $as
	 */
	public function lookup(string $from, string $localField, string $foreignField, string $as)
	{
		$this->pipeline[] = [
			'$lookup' => [
				'from'			 => $from,
				'localField'	 => $localField,
				'foreignField'	 => $foreignField,
				'as'			 => $as,
			],
		];

		return $this;
	}

	/**
	 * Add a graphLookup step
	 *
	 * @param string   $from
	 * @param string   $startWith
	 * @param string   $connectFrom
	 * @param string   $connectTo
	 * @param string   $as
	 * @param int      $maxDepth
	 * @param stdClass $restrictWithin
	 */
	public function graphLookup(string $from, string $startWith, string $connectFrom, string $connectTo, string $as, int $maxDepth, $restrictWithin = null)
	{
		$this->pipeline[] = [
			'$graphLookup' => [
				'from'						 => $from,
				'startWith'					 => $startWith,
				'connectFromField'			 => $connectFrom,
				'connectToField'			 => $connectTo,
				'as'						 => $as,
				'maxDepth'					 => $maxDepth,
				'restrictSearchWithMatch'	 => $restrictWithin ?? new \stdClass(),
			]
		];

		return $this;
	}

	/**
	 * Add an unwind step
	 *
	 * @param string $path
	 * @param bool $preserveEmpty
	 */
	public function unwind(string $path, bool $preserveEmpty = false)
	{
		$this->pipeline[] = [
			'$unwind' => [
				'path'						 => $path,
				'preserveNullAndEmptyArrays' => $preserveEmpty,
			]
		];

		return $this;
	}

	/**
	 * Add a projection step
	 *
	 * @param array $projection
	 */
	public function project(array $projection)
	{
		$this->pipeline[] = [
			'$project' => $projection,
		];

		return $this;
	}

	/**
	 * Add an addFields step
	 *
	 * @param array $fields
	 * @return $this
	 */
	public function addFields(array $fields)
	{
		$this->pipeline[] = [
			'$addFields' => $fields
		];

		return $this;
	}

	/**
	 * Add a count step
	 *
	 * @param string $name
	 * @return $this
	 */
	public function count(string $name)
	{
		$this->pipeline[] = [
			'$count' => $name,
		];

		return $this;
	}

	/**
	 * Add a sorting step
	 *
	 * @param array $sortBy
	 * @return $this
	 */
	public function sort(array $sortBy)
	{
		$this->pipeline[] = [
			'$sort' => $sortBy,
		];

		return $this;
	}

	/**
	 * Skip a number of documents before the next stage
	 *
	 * @param int $count
	 * @return $this
	 */
	public function skip(int $count)
	{
		$this->pipeline[] = [
			'$skip' => $count,
		];

		return $this;
	}

	/**
	 * Filter aggregated array of data
	 *
	 * @param sting $input
	 * @param string $as
	 * @param array $condition
	 * @return $this
	 */
	public function filter(string $input, string $as, array $condition)
	{
		$this->pipeline[] = [
			'input'	 => $input,
			'as'	 => $as,
			'cond'	 => $condition,
		];

		return $this;
	}

	/**
	 * Limit the number of documents passed to the next stage
	 *
	 * @param int $count
	 * @return $this
	 */
	public function limit(int $count)
	{
		$this->pipeline[] = [
			'$limit' => $count,
		];

		return $this;
	}

	/**
	 * Get the resulting pipeline
	 *
	 * @return array
	 */
	public function getPipeline()
	{
		return $this->pipeline;
	}

}
