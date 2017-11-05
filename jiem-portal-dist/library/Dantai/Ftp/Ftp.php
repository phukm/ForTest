<?php

namespace Dantai\Ftp;

class Ftp {
    
    /**
     * ASCII transfer mode
     */
    const MODE_ASCII = FTP_ASCII;
    
    /**
     * Binary transfer mode
     */
    const MODE_BINARY = FTP_BINARY;
    
    /**
     * Automatic transfer mode detection
     */
    const MODE_AUTO = 4;
    
    /**
     * Sync only if newer
     */
    const SYNC_NEWER = 1;
    
    /**
     * Sync only if the file size is different
     */
    const SYNC_DIFFERENT = 2;
    
    /**
     * Sync if newer or if the file size is different
     */
    const SYNC_NEWER_OR_DIFFERENT = 4;
    
    /**
     * Unix system type
     */
    const SYSTYPE_UNIX = 'UNIX';
    

    protected $config = array(
        'adapter' => '\Dantai\Ftp\Adapter',
    );
    /**
     * @var Adapter 
     */
    protected $adapter = null;
    private $_currentPath = null;
    private $_currentDirectory = null;
    /**
     * The current transfer mode
     * 
     * @var int
     */
    protected $_currentMode = self::MODE_AUTO;
    
    /**
     * File types to be transferred in ASCII mode when using automatic detection
     * 
     * @var array
     */
    protected $_asciiTypes = array('txt', 'html', 'htm', 'php', 'phtml');

    public function __construct($config = null) {
        if ($config !== null) {
            $this->setConfig($config);
        }
    }
    
    public function getConnection(){
        return $this->getAdapter()->getConnection();
    }

    public function setConfig($config = array()) {
        if (!is_array($config)) {
            throw new \Exception('Array expected, got ' . gettype($config));
        }

        foreach ($config as $k => $v) {
            $this->config[strtolower($k)] = $v;
        }

        // Pass configuration options to the adapter if it exists
        if ($this->adapter instanceof \Dantai\Ftp\Adapter) {
            $this->adapter->setConfig($config);
        }

        return $this;
    }

    public function getCurrentDirectory() {
        if ($this->_currentDirectory == null) {
            if ($this->_currentPath == null) {
                $this->_currentPath = $this->getAdapter()->getCurrentPath();
            }
            $this->_currentDirectory = new Directory($this->_currentPath, $this);
        }

        return $this->_currentDirectory;
    }

    public function setAdapter($adapter) {
        if (is_string($adapter)) {
            $adapter = new $adapter;
        }

        if (!$adapter instanceof \Dantai\Ftp\Adapter) {
            throw new \Exception('Passed adapter is not a valid FTP connection adapter');
        }

        $this->adapter = $adapter;
        $config = $this->config;
        unset($config['adapter']);
        $this->adapter->setConfig($config);
    }

    public function getAdapter() {
        if ($this->adapter == null) {
            $this->setAdapter($this->config['adapter']);
        }

        return $this->adapter;
    }

    public function changeDir($path) {
        $this->getAdapter()->changeDir($path);

        $this->_currentPath = $path;
    }
    
    /**
     * Determine the transfer mode for the given filename
     * 
     * @param string $filename
     * @return int
     */
    public function determineMode($filename)
    {
        if ($this->_currentMode == self::MODE_AUTO) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            if (in_array($extension, $this->_asciiTypes)) {
                return self::MODE_ASCII;
            }
            return self::MODE_BINARY;
        }
        return $this->_currentMode;
    }

}
