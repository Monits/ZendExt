<?php

/**
 * Wraps several statements as one, allowing to be used in DAOs.
 *
 * @category  ZendExt
 * @package   ZendExt_Db_Dao
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Wraps several statements as one, allowing to be used in DAOs.
 *
 * @category  ZendExt
 * @package   ZendExt_Db_Dao
 * @author    jsotuyod <juansotuyo@gmail.com>
 * @copyright 2010 Juan Sotuyo
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
/*
 *  Copyright 2011, Monits, S.A.
 *  Released under the Apache 2 and New BSD Licenses.
 *  More information: https://github.com/Monits/ZendExt/
 */
class ZendExt_Db_Dao_Statement implements Zend_Db_Statement_Interface
{
    /**
     * List of all statements beign wrapped.
     * @var array
     */
    private $_statements;

    /**
     * Creates a new instance wrappign the given statements.
     *
     * @param array $statements The statements to be wrapped.
     *
     * @return ZendExt_Db_Dao_Statement
     */
    public function __construct(array $statements)
    {
        $this->_statements = $statements;
    }

    /**
     * Bind a column of the statement result set to a PHP variable.
     *
     * @param string $column Name the column in the result set, either by
     *                       position or by name.
     * @param mixed  $param  Reference to the PHP variable 
     * 	                     containing the value.
     * @param mixed  $type   OPTIONAL
     * 
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function bindColumn($column, &$param, $type = null)
    {
        $ret = true;

        foreach ($this->_statements as $stmt) {
            $ret = $ret && $stmt->bindColumn($column, $param, $type);
        }

        return $ret;
    }

    /**
     * Binds a parameter to the specified variable name.
     *
     * @param mixed $parameter Name the parameter, either integer or string.
     * @param mixed $variable  Reference to PHP variable containing the value.
     * @param mixed $type      OPTIONAL Datatype of SQL parameter.
     * @param mixed $length    OPTIONAL Length of SQL parameter.
     * @param mixed $options   OPTIONAL Other options.
     * 
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function bindParam($parameter, &$variable, $type = null, 
        $length = null, $options = null)
    {
        $ret = true;

        foreach ($this->_statements as $stmt) {
            $ret = $ret && $stmt->
                bindParam($parameter, $variable, $type, $length, $options);
        }

        return $ret;
    }

    /**
     * Binds a value to a parameter.
     *
     * @param mixed $parameter Name the parameter, either integer or string.
     * @param mixed $value     Scalar value to bind to the parameter.
     * @param mixed $type      OPTIONAL Datatype of the parameter.
     * 
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function bindValue($parameter, $value, $type = null)
    {
        $ret = true;

        foreach ($this->_statements as $stmt) {
            $ret = $ret && $stmt->bindValue($parameter, $value, $type);
        }

        return $ret;
    }

    /**
     * Closes the cursor, allowing the statement to be executed again.
     *
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function closeCursor()
    {
        $ret = true;

        foreach ($this->_statements as $stmt) {
            $ret = $ret && $stmt->closeCursor();
        }

        return $ret;
    }

    /**
     * Returns the number of columns in the result set.
     * Returns null if the statement has no result set metadata.
     *
     * @return int The number of columns.
     * @throws Zend_Db_Statement_Exception
     */
    public function columnCount()
    {
        /*
         * Since we executed the same query on each adapter, this should be
         * the same for all statements.
         */
        return $this->_statements[0]->columnCount();
    }

    /**
     * Retrieves the first error code, if any, associated with the last
     * operation on the statement handle.
     * Use {@link allErrorCodes} to get them all.
     *
     * @return string|boolean error code, or false if none.
     * @throws Zend_Db_Statement_Exception
     */
    public function errorCode()
    {
        foreach ($this->_statements as $stmt) {
            $errorCode = $stmt->errorCode();

            if (!empty($errorCode)) {
                return $errorCode;
            }
        }

        return false;
    }

    /**
     * Retrieves an array of the first error information, if any,
     * associated with the last operation on the statement handle.
     * Use {@link allErrorInfo} to get them all.
     *
     * @return array|boolean The error info, or false if none.
     * @throws Zend_Db_Statement_Exception
     */
    public function errorInfo()
    {
        foreach ($this->_statements as $stmt) {
            $errorInfo = $stmt->errorInfo();

                return $errorInfo;
            if (!empty($errorInfo)) {
            }
        }

        return false;
    }

    /**
     * Executes a prepared statement.
     *
     * @param array $params OPTIONAL Values to bind to parameter placeholders.
     * 
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function execute(array $params = array())
    {
        $ret = false;

        foreach ($this->_statements as $stmt) {
            $ret = $ret && $stmt->execute($params);
        }

        return $ret;
    }

    /**
     * Fetches a row from the result set.
     *
     * @param int $style  OPTIONAL Fetch mode for this fetch operation.
     * @param int $cursor OPTIONAL Absolute, relative, or other.
     * @param int $offset OPTIONAL Number for absolute or relative cursors.
     * 
     * @return mixed Array, object, or scalar depending on fetch mode.
     * @throws Zend_Db_Statement_Exception
     */
    public function fetch($style = null, $cursor = null, $offset = null)
    {
        // TODO : Support usage of $cursor (is there any adapter supporting it?)
        foreach ($this->_statements as $stmt) {
            $ret = $stmt->fetch($style, $cursor, $offset);
            if ($ret !== false) {
                return $ret;
            }
        }

        return false;
    }

    /**
     * Returns an array containing all of the result set rows.
     *
     * @param int $style OPTIONAL Fetch mode.
     * @param int $col   OPTIONAL Column number, if fetch mode is by column.
     * 
     * @return array Collection of rows, each in a format by the fetch mode.
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchAll($style = null, $col = null)
    {
        $ret = array();

        foreach ($this->_statements as $stmt) {
            $ret = array_merge($ret, $stmt->fetchall($style, $col));
        }

        return $ret;
    }

    /**
     * Returns a single column from the next row of a result set.
     *
     * @param int $col OPTIONAL Position of the column to fetch.
     * 
     * @return string
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchColumn($col = 0)
    {
        foreach ($this->_statements as $stmt) {
            $ret = $stmt->fetchcolumn($col);
            if ($ret !== false) {
                return $ret;
            }
        }

        return false;
    }

    /**
     * Fetches the next row and returns it as an object.
     *
     * @param string $class  OPTIONAL Name of the class to create.
     * @param array  $config OPTIONAL Constructor arguments for the class.
     * 
     * @return mixed One object instance of the specified class.
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchObject($class = 'stdClass', array $config = array())
    {
        foreach ($this->_statements as $stmt) {
            $ret = $stmt->fetchObject($class, $config);
            if ($ret !== false) {
                return $ret;
            }
        }

        return false;
    }

    /**
     * Retrieve a statement attribute.
     *
     * @param string $key Attribute name.
     * 	
     * @return mixed      Attribute value.
     * @throws Zend_Db_Statement_Exception
     */
    public function getAttribute($key)
    {
        /*
         * this should be the same on all statements, so just retrieve
         * the value from the first one.
         */
        return $this->_statements[0]->getAttribute($key);
    }

    /**
     * Retrieves the next rowset (result set) for a SQL statement that has
     * multiple result sets.  An example is a stored procedure that returns
     * the results of multiple queries.
     *
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function nextRowset()
    {
        $ret = true;

        foreach ($this->_statements as $stmt) {
            $ret = $ret && $stmt->nextRowset();
        }

        return $ret;
    }

    /**
     * Returns the number of rows affected by the execution of the
     * last INSERT, DELETE, or UPDATE statement executed by this
     * statement object.
     *
     * @return int     The number of rows affected.
     * @throws Zend_Db_Statement_Exception
     */
    public function rowCount()
    {
        $ret = 0;

        foreach ($this->_statements as $stmt) {
            $ret += $stmt->rowCount();
        }

        return $ret;
    }

    /**
     * Set a statement attribute.
     *
     * @param string $key Attribute name.
     * @param mixed  $val Attribute value.
     * 
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function setAttribute($key, $val)
    {
        $ret = true;

        foreach ($this->_statements as $stmt) {
            $ret = $ret && $stmt->setAttribute($key, $val);
        }

        return $ret;
    }

    /**
     * Set the default fetch mode for this statement.
     *
     * @param int $mode The fetch mode.
     * 
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function setFetchMode($mode)
    {
        $ret = true;

        foreach ($this->_statements as $stmt) {
            $ret = $ret && $stmt->setFetchMode($mode);
        }

        return $ret;
    }
}