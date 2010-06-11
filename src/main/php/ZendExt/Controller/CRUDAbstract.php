<?php
/**
 * CRUD Controller.
 *
 * @category  ZendExt
 * @package   ZendExt_Controller
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */

/**
 * CRUD Controller.
 *
 * @category  ZendExt
 * @package   ZendExt_Controller
 * @author    itirabasso <itirabasso@monits.com>, lbritez <lbritez@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
abstract class ZendExt_Controller_CRUDAbstract
        extends Zend_Controller_Action
{
    protected $_builderClass = null;
    protected $_fieldToColumnMap = null;
    protected $_itemsPerPage = 10;

    protected $_dataSource = null;

    protected $_formName = '';

    protected $_formData = null;

    protected $_templateList = null;
    protected $_templateNew = null;
    protected $_templateUpdate = null;

    const DEFAULT_PAGE = 1;

    /**
     * indexAction.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_forward('list');
    }

    /**
     * listAction.
     *
     * @return void
     */
    public function listAction()
    {
        $request = $this->getRequest();

        foreach ((array)$this->_dataSource->getPk() as $key) {
            $pk[] = $key;
        }

        $builder = new $this->_builderClass();
        $fields = $builder->getFieldsNames();

        $page           = $this->_getParam('page', self::DEFAULT_PAGE);
        $ipp            = $this->_getParam('ipp', $this->_itemsPerPage);
        $orderBy        = $this->_getParam('by', $pk);
        $orderAlignment = $this->_getParam('order', 'ASC');

        $orderAlignment = ($orderAlignment == 'ASC' ? 'ASC' : 'DESC');

        if (!is_array($orderBy)) {
            $orderBy = explode(',', $orderBy);
        }

        $arr = array();
        foreach ($orderBy as $key) {
            $arr[] = $key . ' ' . $orderAlignment;
        }
        $order = $arr;

        $table = $this->_dataSource->getTable();

        $select = $table->select()
                        ->order($order);

        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($ipp);

        $this->view->orderField = $orderBy[0];
        $this->view->order = $orderAlignment;
        $this->view->paginator = $paginator;
        $this->view->pk = $pk;
        $this->view->fieldsMap = $this->_fieldToColumnMap;
        $this->view->controllerName = $request->getControllerName();

        if (null == $this->_templateList) {
            $title = 'List of ' . $this->_builderClass;
            $this->_renderTemplate('List', $title);
        } else {
            $template = $this->_templateList;
            $this->_helper->viewRenderer->renderScript($template);
        }
    }

    /**
     * newAction.
     *
     * @return void
     */
    public function newAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            // Assign the form
            $this->view->form = $this->_newForm();

            // Render the script
            if (null == $this->_templateNew) {
                $this->_renderTemplate('New', $this->_formName);
            } else {
                $template = $this->_templateNew;
                $this->_helper->viewRenderer->renderScript($template);
            }
            return;
        }
        $data = array();

        $builder = new $this->_builderClass();
        $fields = $builder->getFieldsNames();

        /*
         * If it's a sequence, the pk should be autogenerated,
         * remove them from field list.
         */
        if ($this->_dataSource->isSequence()) {
            $pk = $this->_dataSource->getPk();
            $fields = $this->_unsetPk($pk, $fields);
        }

        try {
            $table = $this->_dataSource->getTable();

            $data = $this->_completeData($fields);

            $table->insert($data);

            /**
             * TODO : Optionally alow the user to "add another"
             *        and rerender the empty form with a success message.
             */

            $this->_redirectTo('list');

        } catch (ZendExt_Builder_ValidationException $e) {
            $this->view->failedField = $e->getField();
            $this->view->errors = $e->getErrors();

            $data = $this->_getData($fields);
            // Assign the form
            $this->view->form = $this->_newForm(null, $data);

            // Render the script
            if (null == $this->_templateNew) {
                $this->_renderTemplate('New', $this->_formName);
            } else {
                $template = $this->_templateNew;
                $this->_helper->viewRenderer->renderScript($template);
            }
            // TODO : Re-render form with error messages
        }
    }

    /**
     * updateAction.
     *
     * @return void
     */
    public function updateAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {

            // Retrieve params for primary key
            $pkFields = $this->_dataSource->getPk();

            $pk = array();
            foreach ($pkFields as $column) {
                $fieldName = array_search($column, $this->_fieldToColumnMap);
                $pk[$fieldName] = $request->getParam($fieldName);
            }

            // Display the form with the current values
            $this->view->Updateform = $this->_newForm($pk);
            // Render the script
            if (null == $this->_templateUpdate) {
                $this->_renderTemplate('Update', $this->_formName);
            } else {
                $template = $this->_templateUpdate;
                $this->_helper->viewRenderer->renderScript($template);
            }
            return;
        }

        // Update database!
        $builder = new $this->_builderClass();
        $fields = $builder->getFieldsNames();

        $pk = $this->_dataSource->getPk();

        $data = array();
        try{
            $table = $this->_dataSource->getTable();

            $data = $this->_completeData($fields);

            $where = $this->_completeWhere($pk, $table);

            $table->update($data, $where);

            $this->_redirectTo('list');
        } catch (ZendExt_Builder_ValidationException $e) {
            $this->view->failedField = $e->getField();
            $this->view->errors = $e->getErrors();

            $data = $this->_getData($fields);
            // Assign the form
            $this->view->Updateform = $this->_newForm(null, $data);
            // Render the script
            if (null == $this->_templateUpdate) {
                $this->_renderTemplate('Update', $this->_formName);
            } else {
                $template = $this->_templateUpdate;
                $this->_helper->viewRenderer->renderScript($template);
            }
            // TODO : Re-render form with error messages
        }


    }

    /**
     * deleteAction.
     *
     * @return void
     */
    public function deleteAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $this->_redirectTo('list');
            return;
        }

        $pk = $this->_dataSource->getPk();

        try {
            $table = $this->_dataSource->getTable();

            $where = $this->_completeWhere($pk, $table);

            $table->delete($where);

        } catch (ZendExt_Builder_ValidationException $e) {
            $this->view->failedField = $e->getField();
            $this->view->errors = $e->getErrors();
        }

        $this->_redirectTo('list');
    }

    /**
     * Retrieves the WHERE sentence for each primary key.
     *
     * @param string|array           $pk    The primary key of the table.
     * @param Zend_Db_Table_Abstract $table The table.
     *
     * @return array
     */
    private function _completeWhere($pk, $table)
    {
        $adapter = $table->getAdapter();
        $builder = new $this->_builderClass();
        $where = array();

        foreach ($pk as $k) {
            $field = array_search($k, $this->_fieldToColumnMap);
            $method = 'with' . ucfirst($field);
            $value = $this->_getParam($field);
            $builder->$method($value);
            $where[] = $adapter->quoteInto($k . ' = ?', $value);
        }

        return $where;
    }

    /**
     * Retrieves the data from the form for each field.
     *
     * @param array $fields The names of the fields.
     *
     * @return array
     */
    private function _completeData(array $fields)
    {
        $data = array();

        $builder = new $this->_builderClass();

        foreach ($fields as $field) {
            $value = $this->_getParam($field);
            $method = 'with' . ucfirst($field);
            // If empty, take the default (if there is any)
            if (empty($value) && $builder->hasDefault($field)) {
                $value = $builder->getDefault($field);
            }

            // Only validate if the given value is not a default
            if (!$builder->hasDefault($field)
                    || $value != $builder->getDefault($field)) {
                $builder->$method($value);
            }

            $data[$this->_fieldToColumnMap[$field]] = $value;
        }
        return $data;
    }

    /**
     * Retrieves the data from the form.
     *
     * Retrieves the data from the form
     * for each field without builder validate.
     *
     * @param array $fields The names of the fields.
     *
     * @return array
     */
    private function _getData(array $fields)
    {
        $data = array();

        foreach ($fields as $field) {
            $value = $this->_getParam($field);
            $data[$field] = $value;
        }
        return $data;
    }
    /**
     * Unset the primary key in the form.
     *
     * @param array|string $pk     The primary key to be unseted.
     * @param array        $fields The name of the felds.
     *
     * @return array
     */
    private function _unsetPk($pk, $fields)
    {
        foreach ((array) $pk as $k) {
            $pkField = array_search($k, $this->_fieldToColumnMap);
            $indexField = array_search($pkField, $fields);

            unset($fields[$indexField]);
        }
        return $fields;
    }

    /**
     * Retrieves the row for the primary key.
     *
     * @param array $pk Array for field => value of primary to do the lookup.
     *
     * @return array
     */
    private function _getRow(array $pk)
    {

        $table = $this->_dataSource->getTable();
        $select = $table->select();

        foreach ($pk as $field => $value) {
            $column = $this->_fieldToColumnMap[$field];
            $select->where($column . ' = ?', $value);
        }

        $row = $table->fetchRow($select);
        if (null === $row) {
            return null;
        }

        return $row->toArray();
    }

    /**
     * Create a new form.
     *
     * @param array $pk        Optional array for field => value
     *                         of primary to do the lookup.
     * @param array $dataField Optional array for field => value.
     *
     * @return void.
     */
    private function _newForm(array $pk = null, array $dataField = null)
    {
        $row = null;

        if (null !== $pk) {
            $row = $this->_getRow($pk);
        }
        $builder = new $this->_builderClass();
        $fields = $builder->getFieldsNames();

        $form = new Zend_Form();
        $form->setAttrib('id', '')
             ->setAttrib('class', '')
             ->setAction('')
             ->setMethod('post')
             ->addDecorator('HtmlTag', array('tag' => 'dl','class' => ''));

        foreach ($fields as $key => $field) {
            $type = ucfirst($this->_getType($field));

            $zendElement = 'Zend_Form_Element_' . $type;

            $elements[$key] = new $zendElement($field);
            if ($type !== 'Hidden') {
                $decorators = array('Errors', array(
                        'Label', array('tag' => 'dt'))
                );
                $elements[$key]->addDecorators($decorators);
                $elements[$key]->setLabel($field . ' :')
                              /*
                               * TODO: Verificar en la db o builder
                               *       si el campo es requerido o no.
                               */
                              ->setRequired(true);
            }

            if (null !== $row ) {
                $column = $this->_fieldToColumnMap[$field];
                $value = $row[$column];
                $elements[$key]->setValue($value);
            }
            if (null !==$dataField && 'Hidden' !== $type) {
                $value = $dataField[$field];
                $elements[$key]->setValue($value);
                if ($this->view->failedField == $field) {
                    $elements[$key]->addErrors($this->view->errors);
                }
            }
        }
        $form->addElements($elements);

        $form->addElement('submit', 'send');

        return $form;
    }

    /**
     * Retrieves the type of the field.
     *
     * @param string $field The field.
     *
     * @return string
     */
    private function _getType($field)
    {
        if ($this->_dataSource->isSequence()) {
            $pk = $this->_dataSource->getPk();
            foreach ((array) $pk as $k) {
                $pkField = array_search($k, $this->_fieldToColumnMap);
                if ($pkField == $field) {
                    return 'hidden';
                }
            }
        }

        // TODO : If there is a better fit than 'text' use that
        return 'text';
    }

    /**
     * Redirects to the given action.
     *
     * @param string $action The action.
     *
     * @return void
     */
    protected function _redirectTo($action)
    {
        $module = $this->getRequest()->getModuleName();

        if ('default' !== $module) {
            $url = '/' . $this->getRequest()->getControllerName() . '/'
                    . $action;
        } else {
            $url = '/' . $module . '/'
                . $this->getRequest()->getControllerName() . '/' . $action;
        }

        $this->_redirect($url);
    }

    /**
     * Render the form/list.
     *
     * @param string $type  The type of render.
     * @param string $title The title of the form.
     *
     * @return void
     */
    private function _renderTemplate($type, $title)
    {
        $template = 'ZendExt_Crud_Template_' . $type;

        $renderer = new $template($this->view);
        $renderer->setTitle($title);
        $renderer->render();

        $this->_helper->viewRenderer->setNoRender();
    }
}