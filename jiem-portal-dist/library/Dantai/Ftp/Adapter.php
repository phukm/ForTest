<?php

namespace Dantai\Ftp;

Class Adapter {

    private $_connection = null;
    private $_passive = false;
    private $_currentPath = null;
    private $_systemType = null;

    public function __construct($config = null) {
        if ($config !== null) {
            $this->setConfig($config);
        }
    }

    public function setConfig($config) {
        if (!is_array($config)) {
            throw new Exception('Array expected, got ' . gettype($config));
        }

        foreach ($config as $k => $v) {
            $this->config[strtolower($k)] = $v;
        }

        return $this;
    }

    private function _connect() {
        if ($this->_connection === null) {
            if ($this->config['ssl']) {
                $connection = @ftp_ssl_connect($this->config['host'], $this->config['port'], $this->config['timeout']);
            } else {
                $connection = @ftp_connect($this->config['host'], $this->config['port'], $this->config['timeout']);
            }
            if ($connection === false) {
                throw new \Exception('Unable to connect to host "' . $this->config['host'] . '" on port ' . $this->config['port']);
            }

            $this->_connection = $connection;

            $login = @ftp_login($this->_connection, $this->config['username'], $this->config['password']);
            if ($login === false) {
                throw new \Exception('Unable to login with username "' . $this->config['username'] . '"');
            }
        }
    }
    
    public function getConnection(){
        $this->_connect();
        return $this->_connection;
    }

    public function getCurrentPath() {
        $this->_connect();

        $this->_currentPath = ftp_pwd($this->_connection);
        if ($this->_currentPath === false) {
            throw new \Exception('Unable to get current directory');
        }

        return $this->_currentPath;
    }

    public function changeDir($path) {
        $this->_connect();

        $chdir = @ftp_chdir($this->_connection, $path);
        if ($chdir === false) {
            throw new \Exception('Unable to change to directory');
        }

        $this->getCurrentPath();
    }

    public function rawList() {
        $data = array();

        $lines = ftp_rawlist($this->_connection, $this->_currentPath);

        foreach ($lines as $i => $line) {
            $sysType = $this->getSystemType();

            // Sometimes the first line is not a file row
            if ($i == 0 && preg_match('/^total \d+$/', $line)) {
                continue;
            }

            $match = preg_match('/^([\-dl])([rwx\-]+)\s+(\d+)\s+(\w+)\s+(\w+)\s+(\d+)\s+(\w+\s+\d+\s+[\d\:]+)\s+(.*)$/', $line, $matches);
            if ($match == 0) {
                return array();
//                throw new \Exception('Unknown list format');
            }

            list($trash, $type, $permissions, $unknown, $owner, $group, $bytes, $date, $name) = $matches;

            $name = trim($name);
            if ($name == '.' || $name == '..') {
                continue;
            }

            if ($type != 'l') {
                $data[] = array(
                    'type' => $type,
                    'permissions' => $permissions,
                    'bytes' => $bytes,
                    'name' => $name,
                );
            }
        }

        return $data;
    }

    public function getSystemType() {
        $this->_connect();

        if ($this->_systemType == null) {
            $this->_systemType = ftp_systype($this->_connection);
        }

        return $this->_systemType;
    }

}
