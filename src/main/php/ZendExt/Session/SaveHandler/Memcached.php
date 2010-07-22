<?php
/**
 * Session save handler using memcache as backend.
 *
 * @category  ZendExt
 * @package   ZendExt_Session_SaveHandler
 * @copyright 2010 Monits
 * @license   Copyright (C) 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.3.0
 */

/**
 * Session save handler using memcache as backend.
 *
 * @category  ZendExt
 * @package   ZendExt_Session_SaveHandler
 * @author    jsotuyod <jmsotuyo@monits.com>
 * @copyright 2010 Monits
 * @license   Copyright 2010. All rights reserved.
 * @version   Release: 1.0.0
 * @link      http://www.monits.com/
 * @since     1.3.0
 */
class ZendExt_Session_SaveHandler_Memcached extends Zend_Cache_Backend_Memcached
    implements Zend_Session_SaveHandler_Interface
{
    const PREFIX = 'prefix';

    /**
     * The prefix to be used for session's names.
     *
     * @var string
     */
    protected $_prefix = 'mcsession';

    /**
     * Constructor
     *
     * $config is an instance of Zend_Config or an array of key/value pairs
     * containing configuration options for Zend_Session_SaveHandler_Memcached
     * and Zend_Cache_Backend_Memcached. These are the configuration options
     * for Zend_Session_SaveHandler_Memcached:
     *
     * prefix          => (string) The prefix to be used when storing
     *                    sessions in memcache (optional; default: 'mcsession')
     *
     * overrideLifetime  => (boolean) Whether or not an existing session's
     *                   lifetime should be overridden
     *                   (optional; default: false)
     *
     * @param  Zend_Config|array $config      User-provided configuration
     *
     * @return void
     *
     * @throws Zend_Session_SaveHandler_Exception
     */
    public function __construct($config)
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        } else if (!is_array($config)) {
            throw new Zend_Session_SaveHandler_Exception(
                '$config must be an instance of Zend_Config or array');
        }

        $parentConfig = array();

        foreach ($config as $key => $value) {
            switch ($key) {
                case self::PREFIX:
                    $this->_prefix = $value;
                    break;

                default:
                    // unrecognized options passed to parent::__construct()
                    $parentConfig[$key] = $value;
                    break;
            }
        }

        parent::__construct($parentConfig);
    }

    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        Zend_Session::writeClose();
    }

    /**
     * Open Session - retrieve resources
     *
     * @param string $save_path The path in which to store sessions.
     * @param string $name      The name of the session to be used.
     *
     * @return boolean
     */
    public function open($save_path, $name)
    {
        return true;
    }

    /**
     * Close Session - free resources
     *
     * @return boolean
     */
    public function close()
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id The id of the session to be retrieved.
     *
     * @return The serialized session
     */
    public function read($id)
    {
        $session = $this->load($this->_prefix . $id);

        return $session ? $session : '';
    }

    /**
     * Write Session - commit data to resource
     *
     * @param string $id   The id of the session being stored.
     * @param mixed  $data The data to be stored with the session.
     *
     * @return boolean
     */
    public function write($id, $data)
    {
        return $this->save($data, $this->_prefix . $id);
    }

    /**
     * Destroy Session - remove data from resource for given session id.
     *
     * @param string $id The id of the session being destroyed.
     *
     * @return boolean
     */
    public function destroy($id)
    {
        return $this->remove($this->_prefix . $id);
    }

    /**
     * Garbage Collection, remove old session data
     *
     * @param int $maxlifetime The threshold, in seconds,
     *                         for removing old sessions.
     *
     * @return boolean
     */
    public function gc($maxlifetime)
    {
        // Memcached takes care of this on it's own
        return true;
    }
}