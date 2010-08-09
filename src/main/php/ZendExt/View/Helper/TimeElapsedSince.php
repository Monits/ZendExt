<?php
/**
 * View helper to compute time elapsed since a date.
 *
 * @category  ZendExt
 * @package   ZendExt_View_Helper
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.3.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */

/**
 * View helper to compute time elapsed since a date.
 *
 * @category  ZendExt
 * @package   ZendExt_View_Helper
 * @author    jsotuyod <jmsotuyo@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.3.0
 * @link      http://www.zendext.com/
 * @since     1.3.0
 */
class ZendExt_View_Helper_TimeElapsedSince extends Zend_View_Helper_Abstract
{
    const PART_MINUTES = 'mins';
    const PART_HOURS = 'hours';
    const PART_DAYS = 'days';
    const PART_MONTHS = 'months';
    const PART_YEARS = 'years';

    protected static $_formatParts = array(
        self::PART_YEARS,
        self::PART_MONTHS,
        self::PART_DAYS,
        self::PART_HOURS,
        self::PART_MINUTES
    );

    protected static $_formatStrings = array(
        self::PART_MINUTES => array('%d minute', '%d minutes'),
        self::PART_HOURS => array('%d hour', '%d hours'),
        self::PART_DAYS => array('%d day', '%d days'),
        self::PART_MONTHS => array('%d month', '%d months'),
        self::PART_YEARS => array('%d year', '%d years'),
    );

    protected static $_separator = ', ';

    /**
     * Sets the separator to be used among parts when computing elapsed time.
     *
     * @param string $separator The separator to be used.
     *
     * @return void
     */
    public static function setSeparator($separator)
    {
        self::$_separator = $separator;
    }

    /**
     * Sets the template for a given part.
     *
     * @param string $part             The part for which to set the template.
     * @param string $templateSingular The template to be set for singulars.
     * @param string $templatePlural   The template to be set for plurals.
     *
     * @return void
     */
    public static function setTemplateForPart($part, $templateSingular, $templatePlural)
    {
        if (!in_array($part, self::$_formatParts)) {
            throw new ZendExt_Exception('Unknown part: ' . $part);
        }

        self::$_formatStrings[$part][0] = $templateSingular;
        self::$_formatStrings[$part][1] = $templatePlural;
    }

    /**
     * Computes the time elapsed since a given date.
     *
     * @param Zend_Date|string|integer $since    The date or timestamp from
     *                                           which to compute.
     * @param boolean                  $retArray Wether to return an array of
     *                                           parts or a string.
     *
     * @return string|array
     */
    public function timeElapsedSince($since, $retArray = false)
    {
        if (!$since instanceof Zend_Date) {
            // let Zend_Date guess the format, not nice...
            $since = new Zend_Date($since);
        }

        // Get diff in minutes
        $diff = Zend_Date::now()->subTimestamp($since->getTimestamp());
        $diff = (int) ($diff->getTimestamp() / 60);

        $diffArr = array();

        $diffArr[self::PART_MINUTES] = $diff % 60;
        $diff = (int) ($diff / 60);

        $diffArr[self::PART_HOURS] = $diff % 24;
        $diff = (int) ($diff / 24);

        // we consider all months 30 days
        $diffArr[self::PART_DAYS] = $diff % 30;
        $diff = (int) ($diff / 30);

        $diffArr[self::PART_MONTHS] = $diff % 12;
        $diff = (int) ($diff / 12);

        $diffArr[self::PART_YEARS] = $diff;

        if (true === $retArray) {
            return $diffArr;
        }

        $parts = array();
        foreach (self::$_formatParts as $part) {
            switch ($diffArr[$part]) {
                case 0:
                    break;

                case 1:
                    $parts[] = sprintf(
                        self::$_formatStrings[$part][0],
                        $diffArr[$part]
                    );
                    break;

                default:
                    $parts[] = sprintf(
                        self::$_formatStrings[$part][1],
                        $diffArr[$part]
                    );
                    break;
            }
        }

        return implode(self::$_separator, $parts);
    }
}