<?php
/**
 * Twitter_Form_Main class - represents the form on twitter/index/index.
 *
 */

class Twitter_Form_Main extends Omeka_Form
{
  
    private $_fileDestinationDir;
    private $_maxFileSize;

    /**
     * Initialize the form.
     */
    public function init()
    {
        parent::init();
        
        $this->setAttrib('id', 'twitterimport');
        $this->setMethod('post');

        $this->_addFileElement();
        
        $this->addElement('checkbox', 'items_are_public', array(
            'label' => __('Make All Items Public?'),
        ));
        
        
        $this->applyOmekaStyles();
        $this->setAutoApplyOmekaStyles(false);
        
        $submit = $this->createElement('submit', 
                                       'submit', 
                                       array('label' => __('Submit'),
                                             'class' => 'submit submit-medium'));
            
        
        $submit->setDecorators(array('ViewHelper',
                                      array('HtmlTag', 
                                            array('tag' => 'div', 
                                                  'class' => 'twitterimportnext'))));
                                            
        $this->addElement($submit);
    }

    /**
     * Add the file element to the form
     */
    protected function _addFileElement()
    {
        $size = $this->getMaxFileSize();
        $byteSize = clone $this->getMaxFileSize();
        $byteSize->setType(Zend_Measure_Binary::BYTE);

        $fileValidators = array(
            new Zend_Validate_File_Size(array(
                'max' => $byteSize->getValue())),
            new Zend_Validate_File_Count(1),
        );
        if ($this->_requiredExtensions) {
            $fileValidators[] =
                new Omeka_Validate_File_Extension($this->_requiredExtensions);
        }
        if ($this->_requiredMimeTypes) {
            $fileValidators[] =
                new Omeka_Validate_File_MimeType($this->_requiredMimeTypes);
        }
        // Random filename in the temporary directory.
        // Prevents race condition.
        $filter = new Zend_Filter_File_Rename($this->_fileDestinationDir
                    . '/' . md5(mt_rand() + microtime(true)));
        $this->addElement('file', 'JSON_file', array(
            'label' => __('Upload JSON File'),
            'required' => true,
            'validators' => $fileValidators,
            'destination' => $this->_fileDestinationDir,
            'description' => __("Maximum file size is %s.", $size->toString())
        ));
        $this->JSON_file->addFilter($filter);
    }

    /**
     * Validate the form post
     */
    public function isValid($post)
    {
        // Too much POST data, return with an error.
        if (empty($post) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
            $maxSize = $this->getMaxFileSize()->toString();
            $this->twitter_file->addError(
                __('The file you have uploaded exceeds the maximum post size '
                . 'allowed by the server. Please upload a file smaller '
                . 'than %s.', $maxSize));
            return false;
        }

        return parent::isValid($post);
    }

   
    /**
     * Set the file destination for the form.
     *
     * @param string $dest The file destination
     */
    public function setFileDestination($dest)
    {
        $this->_fileDestinationDir = $dest;
    }

    /**
     * Set the maximum size for an uploaded JSON file.
     *
     * If this is not set in the plugin configuration,
     * defaults to the smaller of 'upload_max_filesize' and 'post_max_size'
     * settings in php.
     *
     * If this is set but it exceeds the aforementioned php setting, the size
     * will be reduced to that lower setting.
     * 
     * @param string|null $size The maximum file size
     */
    public function setMaxFileSize($size = null)
    {
        $postMaxSize = $this->_getBinarySize(ini_get('post_max_size'));
        $fileMaxSize = $this->_getBinarySize(ini_get('upload_max_filesize'));
        
        // Start with the max size as the lower of the two php ini settings.
        $strictMaxSize = $postMaxSize->compare($fileMaxSize) > 0
                        ? $fileMaxSize
                        : $postMaxSize;

        // If the plugin max file size setting is lower, choose it as the strict max size
        $pluginMaxSizeRaw = trim(get_option(CsvImportPlugin::MEMORY_LIMIT_OPTION_NAME));
        if ($pluginMaxSizeRaw != '') {
            if ($pluginMaxSize = $this->_getBinarySize($pluginMaxSizeRaw)) {
                $strictMaxSize = $strictMaxSize->compare($pluginMaxSize) > 0
                                ? $pluginMaxSize
                                : $strictMaxSize;
            }
        }

        if ($size === null) {
            $maxSize = $this->_maxFileSize;
        } else {
            $maxSize = $this->_getBinarySize($size);            
        }
        
        if ($maxSize === false || 
            $maxSize === null || 
            $maxSize->compare($strictMaxSize) > 0) {
            $maxSize = $strictMaxSize;
        }
        
        $this->_maxFileSize = $maxSize;
    }

    /**
     * Return the max file size
     * 
     * @return string The max file size
     */
    public function getMaxFileSize()
    {
        if (!$this->_maxFileSize) {
            $this->setMaxFileSize();
        }
        return $this->_maxFileSize;
    }

    /**
     * Return the binary size measure
     * 
     * @return Zend_Measure_Binary The binary size
     */
    protected function _getBinarySize($size)
    {
        if (!preg_match('/(\d+)([KMG]?)/i', $size, $matches)) {
            return false;
        }
        
        $sizeType = Zend_Measure_Binary::BYTE;

        $sizeTypes = array(
            'K' => Zend_Measure_Binary::KILOBYTE,
            'M' => Zend_Measure_Binary::MEGABYTE,
            'G' => Zend_Measure_Binary::GIGABYTE,
        );

        if (count($matches) == 3 && array_key_exists($matches[2], $sizeTypes)) {
            $sizeType = $sizeTypes[$matches[2]];
        }

        return new Zend_Measure_Binary($matches[1], $sizeType);
    }
}
