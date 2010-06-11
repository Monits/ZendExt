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
        $this->_renderPageBar();

        $items = $this->_view->paginator->getCurrentItems();
        $controllerName = $this->_view->controllerName;

        echo '<style type="text/css">';
        $this->_style();
        echo '</style>';

        echo '<table>';
        echo '<thead>';
        echo '<tr>';

        $arrCols = $items->offsetGet(0)->toArray();
        $order = $this->_view->order;
        $orderField = $this->_view->orderField;

        $currentPage = $this->_view->paginator->getCurrentPageNumber();

        foreach ($arrCols as $col => $c) {
            $field = array_search($col, $this->_view->fieldsMap);
            echo '<th class="row">';
            echo '<a  class ="cols" href="/' . $controllerName . '/list/';
            echo 'page/' . $currentPage . '/';
            echo 'order' . '/';
            if ($col == $orderField) {
                echo $order == 'ASC' ? 'DESC' : 'ASC';
            } else {
                echo 'ASC';
            }
            echo '/by/' . $col . '">';
            echo $field;
            echo '</a>';
            echo '</th>';
        }
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        //echo '<th class="field"></th>';

        $trColor = 'normColor';

        foreach ($items as $item) {

            $arrCols = $item->toArray();

            echo '<tr class="'.$trColor.'" >';

            foreach ($arrCols as $col => $c) {
                echo '<td>';
                echo '<span class="colValue">';
                $isPk = in_array($col, $this->_view->pk);
                if ($isPk) {
                    echo '<a href="/' . $controllerName . '/update/';
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
            echo '<form action="/' . $controllerName . '/delete"',
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
    }

    /**
     * Renders the page bar.
     *
     * @return void
     */
    private function _renderPageBar()
    {
        $paginator = $this->_view->paginator;

        $order = $this->_view->order;
        $orderField = $this->_view->orderField;
        $controllerName = $this->_view->controllerName;
        $first =  1;
        $previous = $this->_view->paginator->getCurrentPageNumber() - 1;
        $current = $this->_view->paginator->getCurrentPageNumber();
        $next = $this->_view->paginator->getCurrentPageNumber() + 1;
        $last = ceil(
            $this->_view->paginator->getTotalItemCount() /
            $this->_view->paginator->getItemCountPerPage()
        );
        echo '<div class="pageBar">';

        if ($first != $current) {
            echo '<span class="page">';
            echo "<a href=\"/{$controllerName}/list/page/{$first}"
                    . "/order/{$order}/by/{$orderField}\">First"
                    . '</a></span>';
            echo '<span class="page">';
            echo"<a href=\"/{$controllerName}/list/page/{$previous}"
                    . "/order/{$order}/by/{$orderField}\">Previous"
                    . '</a></span>';
        }

        echo '<span class="page">Current</span>';

        if ($last != $current) {
            echo '<span class="page">';
            echo "<a href=\"/{$controllerName}/list/page/{$next}"
                    . "/order/{$order}/by/{$orderField}\">Next"
                    . '</a></span>';
            echo '<span class=\"page\">';
            echo '<a href="/' . $controllerName . '/list/page/' . $last
                    . "/order/{$order}/by/{$orderField}\">Last"
                    . '</a></span>';
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

        echo '.field {font-size: 14px}' .
            '.delete {float: right}' .
            'table {border-collapse:collapse;}' .
            'table, th, td {border: 1px solid black;}' .
            'td {text-align:center;}' .
            '.colValue {font-size: 12px}' .
            'th {padding:3px;background-color:#A7C942;color:#ffffff;}' .
            'tr.altColor td {color:#000000;background-color:#EAF2D3;heigth}' .
            'div.pageBar {margin-left:auto;margin-right:auto;',
                   'width:300px;text-align:center;}' .
            'span.page a{padding:4px;}' .
            'a:link{text-decoration:none;}' .
            'a:visited{text-decoration:none;}' .
            'a.cols:link{color:#ffffff;}' .
            'a.cols:hover{color:#ffffff}' .
            'a.cols:visited{color:#ffffff;}';
    }
}