<?php
/**
 * Scaffolding Controller.
 *
 * @category  FCoach
 * @package   FCoach_Controller
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */

/**
 * Scaffolding Controller.
 *
 * @category  FCoach
 * @package   FCoach_Controller
 * @author    itirabasso <itirabasso@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
abstract class ZendExt_Controller_ScaffoldingAbstract
        extends Zend_Controller_Action
{
    protected $_builderClass = null;
    protected $_fieldToColumnMap = null;

    protected $_dataSource = null;

    /**
     * indexAction.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_forward('list');
    }

    public function listAction()
    {

    }

    /**
     * newAction.
     *
     * @return void
     */
    public function newAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            return;
        }

        $builder = new $this->_builderClass();
        $dataSource = new $this->_dataSource();

        $data = array();
        $fields = $builder->getFieldNames();
        if ($dataSource->isSequence()) {
            $pk = $dataSource->getPk();
            foreach ($pk as $k) {
                unset($field[$k]);
            }
        }

        try {
            foreach ($fields as $field) {
                $method = 'with' . ucfirst($field);

                $value = $this->getParam($field);

                $builder->$method($value);
                $data[$this->_fieldToColumnMap[$field]] = $value;
            }


            $table = $dataSource
                ->getTable(ZendExt_Dao_Abstract::OPERATION_WRITE);

            $table->insert($data);

        } catch (ZendExt_Builder_ValidationException $e) {
            $this->view->failedField = $e->getField();
            $this->view->errors = $e->getErrors();
        }
    }

    /**
     * updateAction.
     *
     * @return void
     */
    public function updateAction()
    {

    }

    /**
     * deleteAction.
     *
     * @return void
     */
    public function deleteAction()
    {

    }
}