<?php
/**
 * TwitterImport_ImportTask class
 */
 
 
class TwitterImport_ImportTask extends Omeka_Job_AbstractJob
{
    const QUEUE_NAME = 'twitter_import_imports';
    const METHOD_START = 'start';
    const METHOD_UNDO = 'undo';
    
    private $_importId;
    private $_method;
    private $_memoryLimit;
    private $_batchSize;

    public function __construct(array $options)
    {
        $this->_method = self::METHOD_START;
        parent::__construct($options);
    }

    /**
     * Performs the import task 
     */
    public function perform()
    {
        if ($this->_memoryLimit) {
            ini_set('memory_limit', $this->_memoryLimit);
        }
        if (!($import = $this->_getImport())) {
            return;
        }    

        $import->setBatchSize($this->_batchSize);
        call_user_func(array($import, $this->_method));
        
        if ($import->isQueued() || $import->isQueuedUndo()) {
            $this->_dispatcher->setQueueName(self::QUEUE_NAME);
            $this->_dispatcher->sendLongRunning(__CLASS__, 
                array(
                    'importId' => $import->id, 
                    'memoryLimit' => $this->_memoryLimit,
                    'method' => 'resume',
                    'batchSize' => $this->_batchSize,
                )
            );
        }
    }

    /**
     * Set the number of items to create before pausing the import.
     * 
     * @param int $size
     */
    public function setBatchSize($size)
    {
        $this->_batchSize = (int)$size;
    }

    /**
     * Set the memory limit for the task
     * 
     * @param string $limit
     */
    public function setMemoryLimit($limit)
    {
        $this->_memoryLimit = $limit;
    }

    /**
     * Set the import id for the task
     * 
     * @param int $id
     */
    public function setImportId($id)
    {
        $this->_importId = (int)$id;
    }

    /**
     * Set the method name of the import object to be run by the task
     * 
     * @param string $name
     */
    public function setMethod($name)
    {
        $this->_method = $name;
    }

    /**
     * Returns the import of the import task
     * 
     * @return CsvImport_Import The import of the import task
     */
    protected function _getImport()
    {
        return $this->_db->getTable('TwitterImport_Import')->find($this->_importId);
    }
}
