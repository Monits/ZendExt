<?php

class ZendExt_Tool_Generator_Builder extends ZendExt_Tool_Generator_Abstract
{

    protected $_requiresSchema = true;

    /**
     * Actually generates the code.
     *
     * @return void
     */
    protected function _doGenerate()
    {
        echo 'generated all my stuff!';

        print_r($this->_schema);
    }

    /**
     * Retrieves all the extra options that the generator needs.
     *
     * @return array An array formatted like needed by Zend_Console_Getopt.
     */
    protected function _getExtraOptions()
    {
        return array('table|t=s' => 'The builder of which table to generate.');
    }
}