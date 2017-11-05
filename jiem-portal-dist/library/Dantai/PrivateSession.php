<?php
namespace Dantai;

use Zend\Session\Container as SessionContainer;

class PrivateSession
{

    /**
     *
     * @var StorageInterface
     */
    protected static $session = null;

    protected static $sessionNamespace;

    /**
     *
     * @return \Zend\Authentication\Storage\StorageInterface
     */
    protected static function getStorage()
    {
        static::$sessionNamespace = __CLASS__;
        if (static::$session == null) {
            static::$session = new SessionContainer(static::$sessionNamespace);
        }
        return static::$session;
    }

    public static function setData($dataKey, $dataValue)
    {
        static::getStorage()->{$dataKey} = $dataValue;
    }

    public static function getData($dataKey)
    {
        return static::getStorage()->{$dataKey};
    }

    public static function isEmpty($dataKey)
    {
        return ! isset(static::getStorage()->{$dataKey});
    }

    public static function clear($dataKey = null)
    {
        if (null == $dataKey) {
            static::getStorage()->getManager()
                ->getStorage()
                ->clear(static::$sessionNamespace);
        } else {
            unset(static::getStorage()->{$dataKey});
        }
    }
}