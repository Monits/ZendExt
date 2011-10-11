<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * Interface for sharding strategies.
 *
 * @category  ZendExt
 * @package   ZendExt_Sharding_Strategy
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */

/**
 * Interface for sharding strategies.
 *
 * @category  ZendExt
 * @package   ZendExt_Sharding_Strategy
 * @author    jsotuyod <juansotuyo@gmail.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.zendext.com/
 * @since     1.0.0
 */
interface ZendExt_Sharding_Strategy_Interface
{
    /**
     * Retrieves the shard to which a given value belongs.
     *
     * @param mixed $value The value by which to apply sharding.
     *
     * @return int The id of the shard to which the given value belongs.
     */
    public function getShard($value);
}