<?php
/**
 * TwitterImport_File class - represents a JSON file
 */
 
class TwitterImport_File implements IteratorAggregate
{
    private $_filePath;
    private $_parseErrors = array();
    private $_rowIterator;

    /**
     * @param string $filePath Absolute path to the file.
     */
    public function __construct($filePath) 
    {
        $this->_filePath = $filePath;
    }

    /**
     * Absolute path to the file.
     * 
     * @return string
     */
    public function getFilePath() 
    {
        return $this->_filePath;
    }

    /**
     * Get an iterator for the lines (objects) in the JSON file.
     * 
     * @return TwitterImport_RowIterator
     */
    public function getIterator()
    {
        if (!$this->_rowIterator) {
            $this->_rowIterator = new TwitterImport_RowIterator(
                $this->getFilePath());
        }
        return $this->_rowIterator;
    }


    /**
     * Get the error string
     * 
     * @return string
     */
    public function getErrorString()
    {
        return join(' ', $this->_parseErrors);
    }
}