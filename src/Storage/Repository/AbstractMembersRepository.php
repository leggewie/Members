<?php

namespace Bolt\Extension\Bolt\Members\Storage\Repository;

use Bolt\Extension\Bolt\Members\Pager;
use Bolt\Storage\Repository;
use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\DoctrineDbalAdapter;

/**
 * Base repository for Members.
 *
 * Copyright (C) 2014-2016 Gawain Lynch
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   https://opensource.org/licenses/MIT MIT
 */
abstract class AbstractMembersRepository extends Repository
{
    const ALIAS = null;

    /** @var bool */
    protected $pagerEnabled;
    /** @var Pager\Pager */
    protected $pager;

    /**
     * {@inheritdoc}
     */
    public function createQueryBuilder($alias = null)
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->from($this->getTableName(), static::ALIAS)
        ;

        return $queryBuilder;
    }

    /**
     * @return boolean
     */
    public function isPagerEnabled()
    {
        return $this->pagerEnabled;
    }

    /**
     * @param boolean $pagerEnabled
     *
     * @return AbstractMembersRepository
     */
    public function setPagerEnabled($pagerEnabled)
    {
        $this->pagerEnabled = $pagerEnabled;

        return $this;
    }

    /**
     * @param QueryBuilder $query
     * @param string       $column
     *
     * @return Pager\Pager
     */
    public function getPager(QueryBuilder $query, $column)
    {
        if ($this->pager === null) {
            $countField = static::ALIAS . '.' . $column;
            $select = $this->createSelectForCountField($countField);
            $callback = function (QueryBuilder $queryBuilder) use ($select, $countField) {
                $queryBuilder
                    ->select($select)
                    ->addGroupBy($countField)
                    ->setMaxResults(1)
                ;
            };
            $adapter = new DoctrineDbalAdapter($query, $callback);
            $this->pager = new Pager\Pager($adapter, $this->getEntityBuilder());
        }

        return $this->pager;
    }

    private function createSelectForCountField($countField)
    {
        return sprintf('COUNT(DISTINCT %s) AS total_results', $countField);
    }
}
