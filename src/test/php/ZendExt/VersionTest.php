<?php
/*
*  Copyright 2011, Monits, S.A.
*  Released under the Apache 2 and New BSD Licenses.
*  More information: https://github.com/Monits/ZendExt/
*/

/**
 * Unit Test for ZendExt_Version.
 *
 * @category  ZendExt
 * @package   ZendExt
 * @copyright 2011 Monits
 * @license   Copyright (C) 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */

/**
 * Unit Test for ZendExt_Version.
 *
 * @category  ZendExt
 * @package   ZendExt
 * @author    Juan Martín Sotuyo Dodero <jmsotuyo@monits.com>
 * @copyright 2011 Monits
 * @license   Copyright 2011. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.0.0
 */
class ZendExt_VersionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests that version_compare() and its "proxy"
     * ZendExt_Version::compareVersion() work as expected.
     */
    public function testVersionCompare()
    {
        $expect = -1;
        
        for ($i=0; $i <= 1; $i++) {
            for ($j=0; $j < 12; $j++) {
                for ($k=0; $k < 20; $k++) {
                    foreach (array('dev', 'pr', 'PR', 'alpha', 'a1', 'a2', 'beta', 'b1', 'b2', 'RC', 'RC1', 'RC2', 'RC3', '', 'pl1', 'PL1') as $rel) {
                        $ver = "$i.$j.$k$rel";
                        $normalizedVersion = strtolower(ZendExt_Version::VERSION);
                        if (strtolower($ver) === $normalizedVersion
                            || strtolower("$i.$j.$k-$rel") === $normalizedVersion
                            || strtolower("$i.$j.$k.$rel") === $normalizedVersion
                            || strtolower("$i.$j.$k $rel") === $normalizedVersion
                        ) {
                            if ($expect == -1) {
                                $expect = 1;
                            }
                        } else {
                            $this->assertSame(
                                ZendExt_Version::compareVersion($ver),
                                $expect,
                                "For version '$ver' and ZendExt_Version::VERSION = '"
                                . ZendExt_Version::VERSION . "': result=" . (ZendExt_Version::compareVersion($ver))
                                . ', but expected ' . $expect);
                        }
                    }
                }
            }
        }
        if ($expect === -1) {
            $this->fail('Unable to recognize ZendExt_Version::VERSION ('. ZendExt_Version::VERSION . '); last version compared: ' . $ver);
        }
    }

}
