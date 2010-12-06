<?php
/**
 * Zend_Paginator adapter for ZendExt_Db_Dao_Select.
 *
 * @category  ZendExt
 * @package   ZendExt_DataSource
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com
 * @since     1.0.0
 */

/**
 * Zend_Paginator adapter for ZendExt_Db_Dao_Select.
 *
 * @category  ZendExt
 * @package   ZendExt_DataSource
 * @author    imtirabasso <jmsotuyo@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
class ZendExt_Paginator_Adapter_DbDaoSelect
    implements Zend_Paginator_Adapter_Interface
{
    /**
     * Name of the row count column
     *
     * @var string
     */
    const ROW_COUNT_COLUMN = 'zend_paginator_row_count';

    /**
     * The COUNT query
     *
     * @var ZendExt_Db_Dao_Select
     */
    protected $_countSelect = null;

    /**
     * Database query
     *
     * @var ZendExt_Db_Dao_Select
     */
    protected $_select = null;

    /**
     * Total item count
     *
     * @var integer
     */
    protected $_rowCount = null;

    /**
     * Total item count per shard
     *
     * @var array
     */
    protected $_rowCountPerShard = array();

    /**
     * Constructor.
     *
     * @param ZendExt_Db_Dao_Select $select The select query
     */
    public function __construct(ZendExt_Db_Dao_Select $select)
    {
        $this->_select = $select;
    }

    /**
     * Sets the total row count, either directly or through a supplied
     * query.  Without setting this, {@link getPages()} selects the count
     * as a subquery (SELECT COUNT ... FROM (SELECT ...)).  While this
     * yields an accurate count even with queries containing clauses like
     * LIMIT, it can be slow in some circumstances.  For example, in MySQL,
     * subqueries are generally slow when using the InnoDB storage engine.
     * Users are therefore encouraged to profile their queries to find
     * the solution that best meets their needs.
     *
     * @param ZendExt_Db_Dao_Select|array $rowCount Total row count integer
     *                                              per shard or query
     *
     * @return Zend_Paginator_Adapter_DbDaoSelect
     *
     * @throws Zend_Paginator_Exception
     */
    public function setRowCount($rowCount)
    {
        if ($rowCount instanceof ZendExt_Db_Dao_Select) {
            $columns = $rowCount->getPart(Zend_Db_Select::COLUMNS);

            $countColumnPart = $columns[0][2];

            if ($countColumnPart instanceof Zend_Db_Expr) {
                $countColumnPart = $countColumnPart->__toString();
            }

            $rowCountColumn = self::ROW_COUNT_COLUMN;

            // The select query can contain only one column, which should be the row count column
            if (false === strpos($countColumnPart, $rowCountColumn)) {
                /**
                 * @see Zend_Paginator_Exception
                 */
                throw new Zend_Paginator_Exception('Row count column not found');
            }

            $queries = $rowCount->query(Zend_Db::FETCH_ASSOC);

            // Compute the total row count per shard
            $this->_rowCountPerShard = array();
            foreach ($queries as $query) {
                $result = $query->fetch();

                $this->_rowCountPerShard[] = count($result) > 0
                                                ? $result[$rowCountColumn] : 0;
            }

            $this->_rowCount = array_sum($this->_rowCountPerShard);
        } else if (is_array($rowCount)) {
            $this->_rowCountPerShard = $rowCount;
            $this->_rowCount = array_sum($this->_rowCountPerShard);
        } else {
            /**
             * @see Zend_Paginator_Exception
             */
            throw new Zend_Paginator_Exception('Invalid row count');
        }

        return $this;
    }

    /**
     * Returns an array of items for a page.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        // Check which statement whould be executed...
        $left = $offset;
        $i = 0;
        while ($i < count($this->_rowCountPerShard)
                && $left > $this->_rowCountPerShard[$i]) {
            $left -= count($this->_rowCountPerShard[$i]);
            $i++;
        }

        // Have we overflowed?
        if ($i == count($this->_rowCountPerShard)) {
            return array();
        }

        $this->_select->limit($itemCountPerPage, $left);
        $queries = $this->_select->query();

        return $queries[$i]->fetchAll();
    }

    /**
     * Returns the total number of rows in the result set.
     *
     * @return integer
     */
    public function count()
    {
        if ($this->_rowCount === null) {
            $this->setRowCount(
                $this->getCountSelect()
            );
        }

        return $this->_rowCount;
    }

    /**
     * Get the COUNT select object for the provided query
     *
     * TODO: Have a look at queries that have both GROUP BY and DISTINCT specified.
     * In that use-case I'm expecting problems when either GROUP BY or DISTINCT
     * has one column.
     *
     * @return Zend_Db_Select
     */
    public function getCountSelect()
    {
        /*
         * We only need to generate a COUNT query once. It will not change for
         * this instance.
         */
        if ($this->_countSelect !== null) {
            return $this->_countSelect;
        }

        $rowCount= new ZendExt_Db_Dao_Select();
        $rowCount->setTables($this->_select->getTables());
        $rowCount->setIntegrityCheck(false)->bind($this->_select->getBind());

        $countColumn = self::ROW_COUNT_COLUMN;
        $expression  = new Zend_Db_Expr('COUNT(1)');
        $columns     = array($countColumn => $expression);

        /*
         * Execute the original query as subquery. Not optimum,
         * but works for every case.
         */
        $rowCount->from($this->_select, $columns);

        $this->_countSelect = $rowCount;

        return $rowCount;
    }
}
