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
    private function _renderContent()
    {
        $this->_renderPageBar();

        $items = $this->_view->paginator->getCurrentItems();
        $i = 0;

        echo '<style type="text/css">' .
                '.field {font-size: 14px}' .
                '.cell {font-size: 13px}' .
                '.delete {float: right}' .
            '</style>';


        echo '<table>';

        $arrCols = $items->offsetGet(1)->toArray();

        foreach ($arrCols as $col => $c) {
            echo '<th class="field">' . $col . '</th>';
        }

        foreach ($items as $item) {
            $arrCols = $item->toArray();
            echo '<tr>';
            echo '<div class="cell">';
            foreach ($arrCols as $col => $c) {
                echo '<td>';
                echo '<span class="' . $col .
                    '" style="font-size: 8px">' . $c . '</span>';
                echo '</td>';
            }
            echo '<td>';
            echo '<form action="/index/delete" method="post">';
                echo '<input class="button_delete" type="submit"',
                            ' name="delete" value="Delete">';
            foreach ($this->_view->pk as $k) {
                $field = array_search($k, $this->_view->fieldsMap);
                echo "<input type=\"hidden\" name=\"{$field}\"',
                		' value=\"{$arrCols[$k]}\">";
            }
            echo '</form>';
            echo '</td>';
            echo '</div>';
            echo '</tr>';
        }
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

        $first =  1;
        $previous = $this->_view->paginator->getCurrentPageNumber() - 1;
        $current = $this->_view->paginator->getCurrentPageNumber();
        $next = $this->_view->paginator->getCurrentPageNumber() + 1;
        $last = ceil(
            $this->_view->paginator->getTotalItemCount() /
            $this->_view->paginator->getItemCountPerPage()
        );
        echo '<center>';
        echo '<div class="pageBar">';

        if ($first != $current) {
            echo "<span class=\"page\"><a href=\"/?page={$first}"
                    . '"First </a></span>';
            echo "<span class=\"page\"><a href=\"/?page={$previous}"
                    . '"> Previous </a></span>';
        }

        echo '<span class=\"page\">Current</span>';

        if ($last != $current) {
            echo "<span class=\"page\"><a href=\"/?page={$next}"
                    . '"> Next </a></span>';
            echo "<span class=\"page\"><a href=\"/?page={$last}"
                    . '"> Last </a></span>';
        }

        echo '</div>';
        echo '</center>';
    }
}