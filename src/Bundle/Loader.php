<?php

namespace Message\Mothership\Discount\Bundle;

use Message\User\Loader as UserLoader;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\DB\QueryBuilder;
use Message\Cog\DB\Entity\EntityLoaderCollection;

/**
 * Class Loader
 * @package Message\Mothership\Discount\Bundle
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Class for loading Bundles from the database
 */
class Loader
{
	const TABLE_NAME = 'discount_bundle';
	const IMAGE_TABLE_NAME = 'discount_bundle_image';

	/**
	 * @var QueryBuilderFactory
	 */
	private $_queryBuilderFactory;

	/**
	 * @var UserLoader
	 */
	private $_userLoader;

	/**
	 * @var EntityLoaderCollection
	 */
	private $_loaders;

	/**
	 * @var QueryBuilder
	 */
	private $_queryBuilder;

	/**
	 * @var string
	 */
	private $_defaultCurrency;

	/**
	 * @var array
	 */
	private $_columns = [
		'b.bundle_id AS id',
		'b.name',
		'b.allow_codes AS allowCodes',
		'b.start',
		'b.end',
		'b.created_at AS createdAt',
		'b.created_by AS createdBy',
		'b.updated_at AS updatedAt',
		'b.updated_by AS updatedBy',
		'b.deleted_at AS deletedAt',
		'b.deleted_by AS deletedBy',
		'bi.file_id AS imageID'
	];

	/**
	 * @var bool
	 */
	private $_includeDeleted = false;

	/**
	 * @param QueryBuilderFactory $queryBuilderFactory
	 * @param UserLoader $userLoader
	 * @param EntityLoaderCollection $loaders
	 * @param string $defaultCurrency
	 */
	public function __construct(
		QueryBuilderFactory $queryBuilderFactory,
		UserLoader $userLoader,
		EntityLoaderCollection $loaders,
		$defaultCurrency
	)
	{
		$this->_queryBuilderFactory = $queryBuilderFactory;
		$this->_userLoader          = $userLoader;
		$this->_loaders             = $loaders;
		$this->_defaultCurrency     = $defaultCurrency;
	}

	/**
	 * Load Bundle by an ID or an array of IDs
	 *
	 * @param $id
	 *
	 * @return array|BundleProxy
	 */
	public function getByID($id)
	{
		if (is_array($id)) {
			return $this->getByIDs($id);
		}

		if (!is_numeric($id) || (int) $id != $id) {
			throw new \LogicException('Bundle ID must be a whole number');
		}

		$this->_buildQuery();

		$this->_queryBuilder
			->where('b.bundle_id = ?i', [$id])
		;

		return $this->_load(false);
	}

	/**
	 * Load a set of bundles by an array of IDs
	 *
	 * @param array $ids
	 *
	 * @return array
	 */
	public function getByIDs(array $ids)
	{
		foreach ($ids as $id) {
			if (!is_numeric($id) || (int) $id != $id) {
				throw new \LogicException('Bundle ID must be a whole number');
			}
		}

		$this->_buildQuery();

		$this->_queryBuilder
			->where('b.bundle_id IN (?ji)', [$ids])
		;

		return $this->_load();
	}

	/**
	 * Load a bundle by its name
	 *
	 * @param $name
	 *
	 * @return BundleProxy
	 */
	public function getByName($name)
	{
		if (!is_string($name)) {
			throw new \LogicException('Bundle name must be a string');
		}

		$this->_buildQuery();

		$this->_queryBuilder
			->where('b.name = ?s', [$name])
		;

		return $this->_load(false);
	}

	/**
	 * Load all bundles
	 *
	 * @return array
	 */
	public function getAll()
	{
		$this->_buildQuery();

		return $this->_load();
	}

	/**
	 * @param bool $includeDeleted
	 *
	 * @return Loader
	 */
	public function includeDeleted($includeDeleted = true)
	{
		$this->_includeDeleted = (bool) $includeDeleted;

		return $this;
	}

	/**
	 * Run query and use result data to build an instance of BundleProxy
	 *
	 * @param bool $returnAsArray      Will return an array of Bundles if set to true, will return the first value
	 *                                 of the array if false
	 * @return array | BundleProxy
	 */
	private function _load($returnAsArray = true)
	{
		if (null === $this->_queryBuilder) {
			throw new \LogicException('No query builder set!');
		}

		$result = $this->_queryBuilder
			->getQuery()
			->run()
		;

		$bundles = [];

		foreach ($result as $row) {
			$bundle = new BundleProxy($this->_loaders, $this->_defaultCurrency);

			$bundle->setID((int) $row->id);
			$bundle->setName($row->name);
			$bundle->setAllowCodes((bool) $row->allowCodes);

			if ($row->start) {
				$bundle->setStart(new DateTimeImmutable(date('c', $row->start)));
			}

			if ($row->end) {
				$bundle->setEnd(new DateTimeImmutable(date('c', $row->end)));
			}

			if ($row->imageID) {
				$bundle->setImageID($row->imageID);
			}

			$bundle->getAuthorship()->create(
				new DateTimeImmutable(date('c', $row->createdAt)),
				$this->_userLoader->getByID($row->createdBy)
			);

			if ($row->updatedAt) {
				$bundle->getAuthorship()->update(
					new DateTimeImmutable(date('c', $row->updatedAt)),
					$this->_userLoader->getByID($row->updatedBy)
				);
			}

			if ($row->deletedAt) {
				$bundle->getAuthorship()->delete(
					new DateTimeImmutable(date('c', $row->deletedAt)),
					$this->_userLoader->getByID($row->deletedAt)
				);
			}

			$bundles[$bundle->getID()] = $bundle;
		}

		return $returnAsArray ? $bundles : array_shift($bundles);
	}

	/**
	 * Build query without where statement
	 */
	private function _buildQuery()
	{
		$queryBuilder = $this->_queryBuilderFactory
			->getQueryBuilder()
			->select($this->_columns)
			->from('b', self::TABLE_NAME)
			->leftJoin('bi', 'b.bundle_id = bi.bundle_id', self::IMAGE_TABLE_NAME)
		;

		if (false === $this->_includeDeleted) {
			$queryBuilder->where('deleted_at IS NULL');
		}

		$this->_queryBuilder = $queryBuilder;
	}
}