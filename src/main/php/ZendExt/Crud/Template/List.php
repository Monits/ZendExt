<?php
/**
 * List crud template.
 *
 * @category  ZendExt
 * @package   ZendExt_Crud_Template
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */

/**
 * List crud template.
 *
 * @category  ZendExt
 * @package   ZendExt_Crud_Template
 * @author    itirabasso <itirabasso@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
class ZendExt_Crud_Template_List extends ZendExt_Crud_TemplateAbstract
{
    protected $_view;

    /**
     * Crud template construct.
     *
     * @param Zend_View $view The view
     */
    public function __construct(Zend_View $view)
    {
        $this->_view = $view;
    }

    /**
     * Renders the list.
     *
     * @return void
     */
    protected function _renderContent()
    {
        echo '<style type="text/css">';
        $this->_style();
        echo '</style>';

        $items = $this->_view->paginator->getCurrentItems();
        $controllerName = $this->_view->controllerName;
        $moduleUrl = $this->_view->moduleUrl;

        echo '<div class="crudContent">';
        echo     '<div class="newButton">';
        echo         '<a href="/' . $moduleUrl . $controllerName . '/new">';
        echo             '<button>New '. $controllerName .' </button>';
        echo         '</a>';
        echo     '</div>';

        $this->_renderPageBar();

        echo '<table>';
        echo '<thead>';
        echo '<tr>';

        $order = $this->_view->order;
        $orderField = $this->_view->orderField;

        $currentPage = $this->_view->paginator->getCurrentPageNumber();

        foreach ($this->_view->fieldsMap as $field => $col) {
            echo '<th class="row">';
            echo '<a  class ="cols" href="/',
                    $moduleUrl, $controllerName, '/list/';
            echo 'page/' . $currentPage . '/';
            echo 'order' . '/';
            if (in_array($col, $orderField)) {
                echo $order == 'ASC' ? 'DESC' : 'ASC';
            } else {
                echo 'ASC';
            }
            echo '/by/' . $col;
            echo (
                    $this->_view->defaultIpp ==
                    $this->_view->paginator->getItemCountPerPage() ?
                    '' : "/ipp/{$this->_view->paginator->getItemCountPerPage()}"
            );
            echo '">';
            echo (isset($this->_view->viewMap[$col]) ? $this->_view->viewMap[$col] : $field);
            echo '</a>';
            echo '</th>';
        }
        echo '<th colspan="2">';
        echo     'Modify';
        echo '</th>';

        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        $trColor = 'normColor';

        foreach ($items as $item) {

            $arrCols = $item->toArray();

            echo '<tr class="'.$trColor.'" >';

            foreach ($arrCols as $col => $c) {
                echo '<td>';
                echo '<span class="colValue">';
                $isPk = in_array($col, $this->_view->pk);
                if ($isPk) {
                    echo '<a href="/', $moduleUrl, $controllerName, '/update/';
                    foreach ($this->_view->pk as $k) {
                        $field = array_search($k, $this->_view->fieldsMap);
                        echo $field . '/'. $arrCols[$k] . '/';
                    }
                    echo '">';
                }
                echo $c . ($isPk ? '</a>' : '') . '</span>';
                echo '</td>';
            }

            echo '<td>';
            echo '<a href="/' . $moduleUrl . $controllerName . '/update/';
            foreach ($this->_view->pk as $k) {
                $field = array_search($k, $this->_view->fieldsMap);
                echo $field . '/'. $arrCols[$k] . '/';
            }
            echo '">';
            echo '<button> Edit </button>';
            echo '</a>';
            echo '</td>';

            echo '<td>';
            echo '<form action="/' . $moduleUrl . $controllerName . '/delete"',
                    ' method="post">',
                    '<input class="button_delete" type="submit"',
                    ' name="delete" value="Delete">';

            foreach ($this->_view->pk as $k) {
                $field = array_search($k, $this->_view->fieldsMap);
                echo "<input type=\"hidden\" name=\"{$field}\"',
                		' value=\"{$arrCols[$k]}\">";
            }
            echo '</form>';

            echo '</td>'; // delete

            echo '</tr>';

            $trColor = $trColor == 'altColor' ? 'normColor' : 'altColor';
        }

        echo '</tbody>';
        echo '</table>';

        $this->_renderPageBar();

        echo '<div class="newButton">';
        echo     '<a href="/' . $moduleUrl . $controllerName . '/new">';
        echo         '<button>New '. $controllerName .' </button>';
        echo     '</a>';
        echo '</div>';

        // Close .crudContent
        echo '</div>';
    }

    /**
     * Renders the page bar.
     *
     * @return void
     */
    private function _renderPageBar()
    {
        $paginator = $this->_view->paginator;

        $defaultIpp = $this->_view->defaultIpp;
        $ipp = $this->_view->paginator->getItemCountPerPage();
        $order = $this->_view->order;
        $orderField = $this->_view->orderField;
        $controllerName = $this->_view->controllerName;
        $moduleUrl = $this->_view->moduleUrl;
        $first =  1;
        $previous = $this->_view->paginator->getCurrentPageNumber() - 1;
        $current = $this->_view->paginator->getCurrentPageNumber();
        $next = $this->_view->paginator->getCurrentPageNumber() + 1;
        $last = ceil(
            $this->_view->paginator->getTotalItemCount() / $ipp
        );

        if ($last < 2) {
            return;
        }

        echo '<div class="pageBar">';

        if ($first < $current) {
            echo '<span class="page">';
            echo "<a href=\"/{$moduleUrl}{$controllerName}/list/page/{$first}",
                    "/order/{$order}/by/",
                    implode(',', $orderField),
                    ($defaultIpp == $ipp ? '' : "/ipp/{$ipp}"),
                    '">First',
                    '</a></span>';
            echo '<span class="page">';
            echo"<a href=\"/{$moduleUrl}",
                    "{$controllerName}/list/page/{$previous}",
                    "/order/{$order}/by/",
                    implode(',', $orderField),
                    ($defaultIpp == $ipp ? '' : "/ipp/{$ipp}"),
                    '">Previous',
                    '</a></span>';
        }

        echo '<span class="page">Current</span>';

        if ($last > $current) {
            echo '<span class="page">';
            echo "<a href=\"/{$moduleUrl}{$controllerName}/list/page/{$next}",
                    "/order/{$order}/by/",
                    implode(',', $orderField),
                    ($defaultIpp == $ipp ? '' : "/ipp/{$ipp}"),
                    '">Next',
                    '</a></span>';
            echo '<span class=\"page\">';
            echo '<a href="/', $moduleUrl, $controllerName,
                    '/list/page/', $last,
                    "/order/{$order}/by/",
                    implode(',', $orderField),
                    ($defaultIpp == $ipp ? '' : "/ipp/{$ipp}"),
                    '">Last',
                    '</a></span>';
        }

        echo '</div>';
    }

    /**
     * Set the style of the list.
     *
     * @return void
     */
    protected function _style()
    {

        echo '.crudContent .field {font-size: 14px}' .
            '.crudContent .colValue{padding:3px; font-size: 12px}' .
            '.crudContent .delete {float: right}' .
            '.crudContent table {border-collapse:collapse;}' .
            '.crudContent table, .crudContent th, .crudContent td ' .
                    '{border: 1px solid black;}' .
            '.crudContent td {text-align:center;}' .
            '.crudContent th ' .
                    '{padding:3px;background-color:#A7C942;color:#ffffff;}' .
            '.crudContent tr.altColor td ' .
                    '{color:#000000;background-color:#EAF2D3;heigth}' .
            '.crudContent div.pageBar {margin-left:auto;margin-right:auto;',
                    'width:300px;text-align:center;}' .
            '.crudContent span.page a{padding:4px;}' .
            '.crudContent a:link{text-decoration:none;}' .
            '.crudContent a:visited{color:blue;text-decoration:none;}' .
            '.crudContent a.cols:link{color:#ffffff;}' .
            '.crudContent a.cols:hover{color:#ffffff}' .
            '.crudContent a.cols:visited{color:#ffffff;}';
    }
}