<?php

namespace Dantai\Ftp;

class DirectoryIterator implements \SeekableIterator, \Countable, \ArrayAccess
{
    /**
     * The directory
     * 
     * @var string
     */
    protected $_dir = null;

    /**
     * The converted files and folders
     * 
     * @var array
     */
    protected $_rows = array();
    
    /**
     * The raw files and folders
     * 
     * @var array
     */
    protected $_data = array();
    
    /**
     * The FTP connection
     * 
     * @var 
     */
    protected $_directory = null;
    
    /**
     * The number of rows
     * 
     * @var int
     */
    protected $_count = 0;
    
    /**
     * The iterator pointer
     * 
     * @var int
     */
    protected $_pointer = 0;
    
    protected $_ftp;


    /**
     * Instantiate
     * 
     * @param string $dir The full path
     * @param \Dantai\Ftp\Directory $directory The FTP directory
     */
    public function __construct($dir, $directory)
    {
        $this->_dir = $dir;
        $this->_directory = $directory;
        
        $this->_ftp = $directory->getFtpConection();
        
        $this->_data = $this->_directory->rawList();
        
        $this->_count = count($this->_data);
    }
    
    /**
     * Rewind the pointer, required by Iterator
     * 
     * @return \Dantai\Ftp\DirectoryIterator
     */
    public function rewind()
    {
        $this->_pointer = 0;
        
        return $this;
    }
    
    /**
     * Get the current row, required by iterator
     * 
     * @return \Dantai\Ftp\Directory|\Dantai\Ftp\File|null
     */
    public function current()
    {
        if ($this->valid() === false) {
            return null;
        }

        if (empty($this->_rows[$this->_pointer])) {
            $row = $this->_data[$this->_pointer];
            switch ($row['type']) {
                case 'd': // Directory
                    $this->_rows[$this->_pointer] = new Directory($this->_getSlashedPath() . $row['name'] . '/', $this->_ftp);
                	break;
                case '-': // File
                    $this->_rows[$this->_pointer] = new File($this->_getSlashedPath() . $row['name'], $this->_ftp);
                	break;
                case 'l': // Symlink
            	default:
            }
        }
        
        return $this->_rows[$this->_pointer];
    }
    
    /**
     * Get the path with a trailing slash
     * 
     * @return string
     */
    protected function _getSlashedPath()
    {
        if (substr($this->_dir, -1) == '/') {
            return $this->_dir;
        }
        
        return $this->_dir . '/';
    }
    
    /**
     * Return the key of the current row, required by iterator
     * 
     * @return int
     */
    public function key()
    {
        return $this->_pointer;
    }

    /**
     * Continue the pointer to the next row, required by iterator
     */
    public function next()
    {
        ++$this->_pointer;
    }
    
    /**
     * Whether or not there is another row, required by iterator
     * 
     * @return boolean
     */
    public function valid()
    {
        return $this->_pointer < $this->_count;
    }
    
    /**
     * Return the number of rows, required by countable
     * 
     * @return int
     */
    public function count()
    {
        return $this->_count;
    }

    /**
     * Seek to the given position, required by seekable
     * 
     * @param int $position
     * @return \Dantai\Ftp\DirectoryIterator
     */
    public function seek($position)
    {
        $position = (int)$position;
        if ($position < 0 || $position >= $this->_count) {
            throw new Zend_Exception('Illegal index ' . $position);
        }
        $this->_pointer = $position;
        
        return $this;
    }

    /**
     * Whether or not the offset exists, required by seekable
     * 
     * @param int $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->_data[(int)$offset]);
    }

    /**
     * Get the item at the given offset, required by seekable
     * 
     * @param int $offset
     * @return \Dantai\Ftp\Directory|\Dantai\Ftp\File|null
     */
    public function offsetGet($offset)
    {
        $this->_pointer = (int)$offset;
        
        return $this->current();
    }

    /**
     * Set the item at the given offset (ignored), required by seekable
     * 
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * Unset the item at the given offset (ignored), required by seekable
     * 
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
    }

    /**
     * Get a given row, required by seekable
     * 
     * @param int $position
     * @param boolean $seek [optional]
     * @return \Dantai\Ftp\Directory|\Dantai\Ftp\File|null
     */
    public function getRow($position, $seek = false)
    {
        $key = $this->key();
        try {
            $this->seek($position);
            $row = $this->current();
        } catch (Zend_Exception $e) {
            throw new Zend_Exception('No row could be found at position ' . (int)$position);
        }
        if ($seek == false) {
            $this->seek($key);
        }
        
        return $row;
    }
}