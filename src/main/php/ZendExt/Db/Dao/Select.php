<?php
/**
 * Custom select for DAOs.
 *
 * @category  ZendExt
 * @package   ZendExt_Db_Dao
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Custom select for DAOs.
 *
 * @category  ZendExt
 * @package   ZendExt_Db_Dao
 * @author    jsotuyod <juansotuyo@gmail.com>
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
class ZendExt_Db_Dao_Select extends Zend_Db_Table_Select
{
    /*
     * This class allows a query to be executed on a given sets of shards.
     * Since the shards may have different adapters, all quoting must happen
     * delayed, at the time of executing the query. The idea is to override all
     * public methods making use of the adapter and just record the calls to be
     * reproduced later on.
     */

    const CALL_METHOD = 'method';
    const CALL_ARGUMENTS = 'arguments';

    protected $_calls;

    /**
     * The DAO for which this query is created.
     *
     * @var ZendExt_Db_Dao_Abstract
     */
    protected $_dao;

    protected $_tables;

    /**
     * The original from, when deferred calls where first triggered.
     *
     * @var array
     */
    protected $_originalFrom;

    /**
     * Indicates how many times the deferred calls whre triggered.
     *
     * @var integer
     */
    protected $_deferredCallsMade;

    /**
     * Class constructor.
     *
     * @param ZendExt_Db_Dao_Abstract $dao The DAO to whcih this query belongs.
     *
     * @return ZendExt_Db_Dao_Select
     */
    public function __construct(ZendExt_Db_Dao_Abstract $dao)
    {
        $this->_dao = $dao;
        $this->_tables = array();
        $this->_calls = array();

        $this->_originalFrom = null;
        $this->_deferredCallsMade = 0;

        $this->_parts = self::$_partsInit;
    }

    /**
     * Sets the tables to be used for this query.
     *
     * @param array|Zend_Db_Table_Abstract $tables
     *
     * @return void
     */
    public function setTables($tables)
    {
        if (!is_array($tables)) {
            $tables = array($tables);
        }

        /*
         * Make sure no tables are repeated, therefore the query won't
         * be executed twice on the same database.
         */
        $this->_tables = array_unique($tables);
    }

    /**
     * Retrieve the query's tables.
     *
     * @return array of Zend_Db_Table_Abstract
     */
    public function getTables()
    {
        return $this->_tables;
    }

    /**
     * Clear parts of the Select object, or an individual part.
     *
     * @param string $part Optional. The part to be reset.
     *
     * @return ZendExt_Db_Dao_Select
     */
    public function reset($part = null)
    {
        if ($part == null) {
            $this->_calls = array();
        } else if ($part === self::HAVING || $part === self::WHERE) {
            $count = count($this->_calls);

            for ($i = 0; $i < $count; $i++) {
                $callMethod = $this->_calls[$i][self::CALL_METHOD];

                if (stristr($callMethod, $part) !== false) {
                    unset($this->_calls[$i]);
                    $i--; // Decrease iterator to prevent skipping elements
                }
            }
        } else if ($part === self::FROM) {
            $count = count($this->_calls);

            for ($i = 0; $i < $count; $i++) {
                $callMethod = $this->_calls[$i][self::CALL_METHOD];

                if ($callMethod === '_joinUsing') {
                    unset($this->_calls[$i]);
                    $i--; // Decrease iterator to prevent skipping elements
                }
            }
        }

        return parent::reset($part);
    }

    /**
     * Get part of the structured information for the currect query.
     *
     * @param string $part The part of the query to retrieve.
     *
     * @return mixed
     *
     * @throws ZendExt_Db_Dao_Select_Exception
     * @throws Zend_Db_Select_Exception
     */
    public function getPart($part)
    {
        if ($part === self::HAVING || $part === self::WHERE
                || $part === self::FROM) {
            // Get original FROM, it may be modified by _joinUsing...
            $this->_executeDeferredCalls();
        }

        $ret = parent::getPart($part);

        if ($part === self::HAVING || $part === self::WHERE
                || $part === self::FROM) {
            // Reset!
            $this->_clearDeferredCalls();
        }

        return $ret;
    }

    /**
     * Adds a WHERE condition to the query by AND.
     *
     * If a value is passed as the second param, it will be quoted
     * and replaced into the condition wherever a question-mark
     * appears. Array values are quoted and comma-separated.
     *
     * <code>
     * // simplest but non-secure
     * $select->where("id = $id");
     *
     * // secure (ID is quoted but matched anyway)
     * $select->where('id = ?', $id);
     *
     * // alternatively, with named binding
     * $select->where('id = :id');
     * </code>
     *
     * Note that it is more correct to use named bindings in your
     * queries for values other than strings. When you use named
     * bindings, don't forget to pass the values when actually
     * making a query:
     *
     * <code>
     * $db->fetchAll($select, array('id' => 5));
     * </code>
     *
     * @param string   $cond  The WHERE condition.
     * @param mixed    $value OPTIONAL The value to quote into the condition.
     * @param constant $type  OPTIONAL The type of the given value
     *
     * @return ZendExt_Db_Dao_Select This ZendExt_Db_Dao_Select object.
     */
    public function where($cond, $value = null, $type = null)
    {
        $this->_calls[] = array(
            self::CALL_METHOD    => __FUNCTION__,
            self::CALL_ARGUMENTS => func_get_args()
        );

        return $this;
    }

    /**
     * Adds a WHERE condition to the query by OR.
     *
     * Otherwise identical to where().
     *
     * @param string   $cond  The WHERE condition.
     * @param mixed    $value OPTIONAL The value to quote into the condition.
     * @param constant $type  OPTIONAL The type of the given value
     *
     * @return Zend_Db_Select This Zend_Db_Select object.
     *
     * @see where()
     */
    public function orWhere($cond, $value = null, $type = null)
    {
        $this->_calls[] = array(
            self::CALL_METHOD    => __FUNCTION__,
            self::CALL_ARGUMENTS => func_get_args()
        );

        return $this;
    }

    /**
     * Adds a HAVING condition to the query by AND.
     *
     * If a value is passed as the second param, it will be quoted
     * and replaced into the condition wherever a question-mark
     * appears. See {@link where()} for an example
     *
     * @param string $cond The HAVING condition.
     * @param string|Zend_Db_Expr $val The value to quote into the condition.
     *
     * @return ZendExt_Db_Dao_Select This ZendExt_Db_Dao_Select object.
     */
    public function having($cond)
    {
        $this->_calls[] = array(
            self::CALL_METHOD    => __FUNCTION__,
            self::CALL_ARGUMENTS => func_get_args()
        );

        return $this;
    }

    /**
     * Adds a HAVING condition to the query by OR.
     *
     * Otherwise identical to orHaving().
     *
     * @param string $cond The HAVING condition.
     * @param mixed  $val  The value to quote into the condition.
     *
     * @return ZendExt_Db_Dao_Select This ZendExt_Db_Dao_Select object.
     *
     * @see having()
     */
    public function orHaving($cond)
    {
        $this->_calls[] = array(
            self::CALL_METHOD    => __FUNCTION__,
            self::CALL_ARGUMENTS => func_get_args()
        );

        return $this;
    }

    /**
     * Executes the current select object and returns the result
     *
     * @param integer $fetchMode OPTIONAL
     * @param  mixed  $bind An array of data to bind to the placeholders.
     *
     * @return array of PDO_Statement|Zend_Db_Statement
     *
     * @throws ZendExt_Db_Dao_Select_Exception
     */
    public function query($fetchMode = null, $bind = array())
    {
        if (empty($this->_tables)) {
            throw new ZendExt_Db_Dao_Select_Exception('No tables were set!');
        }

        $ret = array();

        // Perform the query for each adapter and group all retrieved data
        foreach ($this->_tables as $table) {
            $this->setTable($table);

            // Perform the query itself now it's ready
            $ret[] = parent::query($fetchMode, $bind);
        }

        return $ret;
    }

    /**
     * Handle JOIN... USING... syntax
     *
     * This is functionality identical to the existing JOIN methods, however
     * the join condition can be passed as a single column name. This method
     * then completes the ON condition by using the same field for the FROM
     * table and the JOIN table.
     *
     * <code>
     * $select = $db->select()->from('table1')
     *                        ->joinUsing('table2', 'column1');
     *
     * // SELECT * FROM table1 JOIN table2 ON table1.column1 = table2.column2
     * </code>
     *
     * These joins are called by the developer simply by adding 'Using' to the
     * method name. E.g.
     * * joinUsing
     * * joinInnerUsing
     * * joinFullUsing
     * * joinRightUsing
     * * joinLeftUsing
     *
     * @return ZendExt_Db_Dao_Select This ZendExt_Db_Dao_Select object.
     */
    public function _joinUsing($type, $name, $cond, $cols = '*', $schema = null)
    {
        $this->_calls[] = array(
            self::CALL_METHOD    => __FUNCTION__,
            self::CALL_ARGUMENTS => func_get_args()
        );

        return $this;
    }

    /**
     * Transform the query into a string using the currently set adapter.
     *
     * @return string|null This object as a SELECT string
     *                     (or null if a string cannot be produced)
     *
     * @throws ZendExt_Db_Dao_Select
     */
    public function assemble()
    {
        // Execute deferred calls
        $this->_executeDeferredCalls();

        // Actually do it
        $ret = parent::assemble();

        // Reset!
        $this->_clearDeferredCalls();

        return $ret;
    }

    /**
     * Execute all deferred calls to parent class.
     *
     * Some methods end up in deferred calls being triggered multiple times.
     * The idea is to keep track on the number of calls and clears so that
     * the calls are only performed once at the start, and the cleanup only
     * once at the end.
     *
     * @throws ZendExt_Db_Dao_Select_Exception
     */
    protected function _executeDeferredCalls()
    {
        // Increment deferred call counter for our call stack
        $this->_deferredCallsMade++;

        if ($this->_deferredCallsMade > 1) {
            // Call already made, just ignore them
            return;
        }

        // Store the original from now the call stack is started
        $this->_originalFrom = $this->_parts[self::FROM];

        if (null === $this->_table) {
            if (isset($this->_tables[0])) {
                $this->setTable($this->_tables[0]);
            } else {
                throw new ZendExt_Db_Dao_Select_Exception('No tables were set!');
            }
        }

        // Call every intercepted function on the parent...
        foreach ($this->_calls as $fCall) {
            $fName = $fCall[self::CALL_METHOD];
            $fArgs = $fCall[self::CALL_ARGUMENTS];

            call_user_func_array(array($this, 'parent::' . $fName), $fArgs);
        }
    }

    /**
     * Clear all data altered by deferred calls to parent class.
     *
     * Some methods end up in deferred calls being triggered multiple times.
     * The idea is to keep track on the number of calls and clears so that
     * the calls are only performed once at the start, and the cleanup only
     * once at the end.
     */
    protected function _clearDeferredCalls()
    {
        $this->_deferredCallsMade--;

        if ($this->_deferredCallsMade != 0) {
            // Still not done, just ignore them
            return;
        }

        $this->_parts[self::FROM] = $this->_originalFrom;
        $this->_parts[self::WHERE] = array();
        $this->_parts[self::HAVING] = array();
    }
}