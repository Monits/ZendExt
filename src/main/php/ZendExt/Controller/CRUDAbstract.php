<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * CRUD Controller.
 *
 * @category  ZendExt
 * @package   ZendExt_Controller
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */

/**
 * CRUD Controller.
 *
 * @category  ZendExt
 * @package   ZendExt_Controller
 * @author    itirabasso <itirabasso@monits.com>
 * @author    lbritez <lbritez@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
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
    protected $_viewToColumnMap = null;

    /**
     * @var ZendExt_DataSource_Adapter
     */
    protected $_dataSource = null;

    protected $_updateTitle;
    protected $_newTitle;
    protected $_listTitle;

    protected $_formData = null;

    protected $_templateList = null;
    protected $_templateNew = null;
    protected $_templateUpdate = null;

    protected $_listModifyTitle = null;
    protected $_listNewButton = null;
    protected $_listDeleteButton = null;
    protected $_listEditButton = null;
    protected $_createButton = null;

    private $_actualForm = null;

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

        $select = $this->_dataSource->select();
        $select->order($order);

        $paginator = $this->_dataSource->paginate($select);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($ipp);

        $this->view->orderField = $orderBy;
        $this->view->order = $orderAlignment;
        $this->view->paginator = $paginator;
        $this->view->pk = $pk;
        $this->view->fieldsMap = $this->_fieldToColumnMap;
        $this->view->viewMap = $this->_viewToColumnMap;
        $this->view->controllerName = $request->getControllerName();
        $this->view->defaultIpp = $this->_itemsPerPage;

        if ('default' !== $request->getModuleName()) {
            $this->view->moduleUrl = $request->getModuleName() . '/';
        } else {
            $this->view->moduleUrl = '';
        }

        if (null == $this->_templateList) {
            $title = 'List';
            if (null !== $this->_listTitle) {
                $title = $this->_listTitle;
            }

            $translatedList = array(
                'modifyTitle' => $this->_listModifyTitle,
            	'newButton' => $this->_listNewButton,
                'deleteButton' => $this->_listDeleteButton,
                'editButton' => $this->_listEditButton
            );

            $this->_renderTemplate('List', $title, $translatedList);
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
        $this->_actualForm = 'new';

        if (!$request->isPost()) {
            // Assign the form
            $this->view->form = $this->_newForm();

            // Render the script
            if (null == $this->_templateNew) {
                $title = 'New';
                if (null !== $this->_newTitle) {
                    $title = $this->_newTitle;
                }

                $this->_renderTemplate('New', $title);
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
            $build = true;
            if ($this->_dataSource->isSequence()) {
                $build = false;
            }

            $data = $this->_completeData($fields, $build);

            $this->_dataSource->insert($data);

            /**
             * TODO : Optionally alow the user to "add another"
             *        and rerender the empty form with a success message.
             */

            $this->_redirectTo('list');

        } catch (Exception $e) {
            if ($e instanceof Zend_Db_Exception) {
                $this->view->errorDb = 'Duplicate entry';
            }
            $this->view->failedField = $e->getField();
            $this->view->errors = $e->getErrors();

            $data = $this->_getData($fields);
            $checks = $this->_getCheckboxValue($fields);
            // Assign the form
            $this->view->form = $this->_newForm(null, $data, $checks);

            // Render the script
            if (null == $this->_templateNew) {
                $title = 'New';
                if (null !== $this->_newTitle) {
                    $title = $this->_newTitle;
                }

                $this->_renderTemplate('New', $title);
            } else {
                $template = $this->_templateNew;
                $this->_helper->viewRenderer->renderScript($template);
            }

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
        $this->_actualForm = 'update';

        if (!$request->isPost()) {
            // Retrieve params for primary key
            $pkFields = $this->_dataSource->getPk();

            $pk = array();
            foreach ($pkFields as $column) {
                $fieldName = array_search($column, $this->_fieldToColumnMap);
                $pk[$fieldName] = $request->getParam($fieldName);
            }

            // Display the form with the current values
            $this->view->form = $this->_newForm($pk);
            // Render the script
            if (null == $this->_templateUpdate) {
                $title = 'Update';
                if (null !== $this->_updateTitle) {
                    $title = $this->_updateTitle;
                }

                $this->_renderTemplate('Update', $title);
            } else {
                $template = $this->_templateUpdate;
                $this->_helper->viewRenderer->renderScript($template);
            }
            return;
        }

        // Update database!
        $builder = new $this->_builderClass();
        $fields = $builder->getFieldsNames();

        $data = array();
        try{
            $data = $this->_completeData($fields, true);

            $primaryKey = $this->_completePkValues();

            $this->_dataSource->update($data, $primaryKey);

            $this->_redirectTo('list');
        } catch (Exception $e) {
            if ($e instanceof Zend_Db_Exception) {
                $this->view->errorDb = 'Duplicate entry';
            }
            $this->view->failedField = $e->getField();
            $this->view->errors = $e->getErrors();

            $data = $this->_getData($fields);
            $checks = $this->_getCheckboxValue($fields);

            // Assign the form
            $this->view->form = $this->_newForm(null, $data, $checks);
            // Render the script
            if (null == $this->_templateUpdate) {
                $title = 'Update';
                if (null !== $this->_updateTitle) {
                    $title = $this->_updateTitle;
                }

                $this->_renderTemplate('Update', $title);
            } else {
                $template = $this->_templateUpdate;
                $this->_helper->viewRenderer->renderScript($template);
            }
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

        try {
            $primaryKey = $this->_completePkValues();

            $this->_dataSource->delete($primaryKey, $primaryKey);

        } catch (ZendExt_Builder_ValidationException $e) {
            $this->view->failedField = $e->getField();
            $this->view->errors = $e->getErrors();
        }

        $this->_redirectTo('list');
    }

    /**
     * Retrieves the col_name => value pair for each primary key column.
     *
     * @return array
     */
    private function _completePkValues()
    {
        $pk = $this->_dataSource->getPk();

        $builder = new $this->_builderClass();
        $where = array();

        foreach ($pk as $k) {
            $field = array_search($k, $this->_fieldToColumnMap);
            $method = 'with' . ucfirst($field);
            $value = $this->_getParam($field);
            $builder->$method($value);
            $where[$k] = $value;
        }

        return $where;
    }

    /**
     * Retrieves the data from the form for each field.
     *
     * @param array $fields The names of the fields.
     * @param bool  $build  Define if is used the method build().
     *
     * @return array
     */
    private function _completeData(array $fields, $build = false)
    {
        $data = array();

        $builder = new $this->_builderClass();
        foreach ($fields as $field) {
            $value = $this->_getParam($field);
            $method = 'with' . ucfirst($field);
            $isChecked = $this->_getParam('check' . $field);

            if ('0' == $isChecked) {
                // If empty, take the default (if there is any)
                if ($builder->hasDefault($field)) {
                    $value = $builder->getDefault($field);
                }
            } else {
                $builder->$method($value);
            }

            $data[$this->_fieldToColumnMap[$field]] = $value;
        }
        if (true == $build) {
            $builder->build();
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
     * Retrieves the values of the checkbox of the nullable fields.
     *
     * @param array $fields The names of the fields.
     *
     * @return array
     */
    private function _getCheckboxValue(array $fields)
    {
        $data = array();

        foreach ($fields as $field) {
            $isChecked = $this->_getParam('check' . $field);

            $data[$field] = $isChecked;
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
        $select = $this->_dataSource->select();

        foreach ($pk as $field => $value) {
            $column = $this->_fieldToColumnMap[$field];
            $select->where($column . ' = ?', $value);
        }

        return $this->_dataSource->fetchOne($select);
    }

    /**
     * Create a new form.
     *
     * @param array $pk        Optional array for field => value
     *                         of primary to do the lookup.
     * @param array $dataField Optional array for field => value.
     * @param array $checks    Optional array for field => checkbox value.
     *
     * @return void.
     */
    private function _newForm(
            array $pk = null, array $dataField = null, array $checks = null)
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

        if (null !== $this->view->errorDb) {
            //FIXME : cambiar esta forma de mostrar el error de la db!!!
            $hiddenElement = new Zend_Form_Element_Hidden('errorDb');
            $hiddenElement->addError($this->view->errorDb)
                          ->removeDecorator('label');
            $form->addElement($hiddenElement);
        }

        $checkbox = array();
        $elements = array();
        foreach ($fields as $field) {

            $type = $this->_getType($field);
            $nullable = $this->_dataSource->isFieldNullable($field);

            //if type is a array add a input type radio
            if (is_array($type)) {
                $elements[$field] = new Zend_Form_Element_Radio($field);
                $decorators = array(array(
                        'Label', array('tag' => 'dt'))
                );
                $elements[$field]->addDecorators($decorators);
                $elements[$field]->setLabel(
                    (isset($this->_viewToColumnMap[$field]) ?
                        $this->_viewToColumnMap[$field] : $field) . ':'
                );
                $elements[$field]->addMultiOptions($type);

            } else {
                $zendElement = 'Zend_Form_Element_' . $type;

                $elements[$field] = new $zendElement($field);
                if ($type !== 'Hidden') {
                    $decorators = array('Errors', array(
                            'Label', array('tag' => 'dt'))
                    );
                    $elements[$field]->addDecorators($decorators);
                    $elements[$field]
                        ->setLabel(
                            (isset($this->_viewToColumnMap[$field]) ?
                                $this->_viewToColumnMap[$field] : $field) . ':'
                    );

                    $required = true == $nullable ? false : true;

                    $elements[$field]->setRequired($required);

                } else {
                    $elements[$field]->removeDecorator('label')
                                     ->removeDecorator('HtmlTag');
                }
            }

            //Complete the form with the DB data for update form.
            if (null !== $row) {
                $column = $this->_fieldToColumnMap[$field];
                $value = $row[$column];
                $elements[$field]->setValue($value);
            }

            if (null !==$dataField && 'Hidden' !== $type) {
                $value = $dataField[$field];
                $elements[$field]->setValue($value);
                if ($this->view->failedField == $field) {
                    $elements[$field]->addErrors($this->view->errors);
                }
            }

            /*
             * If the field can be null add a checkbox
             * to make de field able or disable.
             */
            if (true == $nullable) {
                $checkbox[$field] = new Zend_Form_Element_Checkbox(
                    'check' . $field
                );

                $checkbox[$field]->setAttrib('class', 'checkField');
                $checkValue = 'checked';
                if (null !== $checks) {
                    $checkValue = $checks[$field] == '0' ? '' : 'checked';
                } else if (null !== $row) { // We are updating from db, check if null
                    $column = $this->_fieldToColumnMap[$field];
                    $value = $row[$column];
                    $checkValue = $value === null ? '' : 'checked';
                }
                $textLabel = 'Enable';

                if ('' == $checkValue) {
                    $elements[$field]->setAttrib('disable', true);
                } else {
                    $checkbox[$field]->setAttrib('checked', $checkValue);

                }
                $jsFunction = 'checkField(\''.$field.'\')';

                $checkbox[$field]->setAttrib('onClick', $jsFunction);
                $checkbox[$field]->setLabel($textLabel.' the field');
                $checkbox[$field]->addDecorator(
                    'Label', array(
                        'tag' => 'dt',
                        'class' => 'checkboxLabel'
                    )
                );
            }

            $form->addElement($elements[$field]);
            $existCheck = array_key_exists($field, $checkbox);
            if (true == $existCheck) {
                $form->addElement($checkbox[$field]);
            }
        }

        $sendButton = 'send';
        if ($this->_actualForm === 'new'
        	&& null != $this->_createButton
        ) {
            $sendButton = $this->_createButton;
        }
        elseif ($this->_actualForm === 'update'
        	&& null != $this->_listEditButton
        ) {
            $sendButton = $this->_listEditButton;
        }

        $submit = new Zend_Form_Element_Submit($sendButton);
        $submit->setName('send');
        $submit->setLabel($sendButton);
        $form->addElement($submit);

        return $form;
    }

    /**
     * Retrieves the type of the field.
     *
     * @param string $field The field.
     *
     * @return string|array
     */
    private function _getType($field)
    {
        if ($this->_dataSource->isSequence()) {
            $pk = $this->_dataSource->getPk();
            foreach ((array) $pk as $k) {
                $pkField = array_search($k, $this->_fieldToColumnMap);
                if ($pkField == $field) {
                    return 'Hidden';
                }
            }
        }

        $desc = $this->_dataSource->getFieldType($field);

        if ($desc == 'text') {
            return 'Textarea';
        }

        $enum = strpos($desc, 'enum');
        if (false !== $enum) {
            $ret = array();
            foreach ($this->_getEnum($desc) as $enumVal) {
                $ret[$enumVal] = $enumVal;
            }
            return $ret;
        }

        // TODO : If there is a better fit than 'text' use that
        return 'Text';
    }

    /**
     * Retrieves a array with values for the radio buttons.
     *
     * @param string $enum The enum string given by the table description.
     *
     * @return array
     */
    private function _getEnum($enum)
    {
        // TODO : Test this in other dbs besides MySQL
        $enum = str_replace(
            array('enum','(',')','\''), array('', '', '', ''), $enum
        );
        $enum = explode(',', $enum);

        return $enum;
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

        if ('default' === $module) {
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
     * @param string $type           The type of render.
     * @param string $title          The title of the form.
     * @param array  $translatedList Array with the buttons and titles
     * 	                             of the list template translated
     *
     * @return void
     */
    private function _renderTemplate($type, $title, $translatedList = null)
    {
        $template = 'ZendExt_Crud_Template_' . $type;

        $renderer = new $template($this->view, $translatedList);
        $renderer->setTitle($title);
        $renderer->render();

        $this->_helper->viewRenderer->setNoRender();
    }
}