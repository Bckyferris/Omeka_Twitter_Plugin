<?php
/**
 * TwitterImport_IndexController class - represents the Twitter Import index controller
 */
 
class Twitter_IndexController extends Omeka_Controller_AbstractActionController
{

     protected $_pluginConfig = array();

      /**
     * Initialize the controller.
     */
    public function init()
    {
        $this->session = new Zend_Session_Namespace('TwitterImport');
        $this->_helper->db->setDefaultModelName('item');        
    }

    /**
     * Configure a new import.
     */
    public function indexAction()    
    {        
        $form = $this->_getMainForm();
        $this->view->form = $form;
        $iterator = new TwitterImport_RowIterator;
			$formattedrow = $iterator->_formatRow();
			
        if (!$this->getRequest()->isPost()) {
            return;
        }
     
        if (!$form->isValid($this->getRequest()->getPost())) {
            $this->_helper->flashMessenger(__('Invalid form input. Please see errors below and try again.'), 'error');
            return;
        }
        
        if (!$form->JSON_file->receive()) {
            $this->_helper->flashMessenger(__('Error uploading file. Please try again.'), 'error');
            return;
        } 
        
        $filePath = $form->JSON_file->getFileName();
        $file = new TwitterImport_File($filePath);
        
        $this->session->setExpirationHops(2);
        $this->session->originalFilename = $_FILES['JSON_file']['name'];
        $this->session->filePath = $filePath;  
        $this->session->itemsArePublic = $form->getValue('items_are_public');
        $this->session->ownerId = $this->getInvokeArg('bootstrap')->currentuser->id;

        $this->_helper->redirector->goto('process');
    }                 
	
  	 public function processAction()
     {
     
		if (!$this->_sessionIsValid()) {
				$this->_helper->flashMessenger(__('Import settings expired. Please try again.'), 'error');
				$this->_helper->redirector->goto('index');
				return;
			}
			 
		 $TwitterImport = new TwitterImport_Import();        
			foreach ($this->session->getIterator() as $key => $value) {
				$setMethod = 'set' . ucwords($key);
				if (method_exists($TwitterImport, $setMethod)) {
					$TwitterImport->$setMethod($value);
				}
			}
			
			if ($TwitterImport->queue()) {    
				$this->_dispatchImportTask($TwitterImport, TwitterImport_ImportTask::METHOD_START);
				$this->_helper->flashMessenger(__('Import started. Reload this page for status updates.'), 'success');
			} else {
				$this->_helper->flashMessenger(__('Import could not be started. Please check error logs for more details.'), 'error');
			}
			
			$this->session->unsetAll();
			
			$this->_helper->redirector->goto('browse');
			
		
	  }
	
	public function browseAction()
  	{
  		$db = $this->_helper->db;
  		$item_table = $db->getTable('Item');
  		
      if (!$this->_getParam('sort_field')) {
            $this->_setParam('sort_field', 'Tweet ID');
            $this->_setParam('sort_dir', 'd');
        }
        
      $this->_browseRecordsPerPage = (int)get_option('twitter_per_page');  
      $tweet_item_type_id =  $this->_getTweetTypeId();
      $this->_setParam('item_type_id', $tweet_item_type_id);
     
      parent::browseAction();
  	
  	}
    
 
  
   /**
   * Get the main Twitter Import form.
   * 
   * @return Twitter_Form_Main
   */
    protected function _getMainForm()
    {
        require_once TWITTER_DIRECTORY . '/forms/Main.php';
        $twitterConfig = $this->_getPluginConfig();
        $form = new Twitter_Form_Main($twitterConfig);
        return $form;
    }
    
    
    /**
    * Returns the plugin configuration
    * 
    * @return array
    */
    protected function _getPluginConfig()
    {
        if (!$this->_pluginConfig) {
            $config = $this->getInvokeArg('bootstrap')->config->plugins;
            if ($config && isset($config->Twitter)) {
                $this->_pluginConfig = $config->Twitter->toArray();
            }
            if (!array_key_exists('fileDestination', $this->_pluginConfig)) {
                $this->_pluginConfig['fileDestination'] =
                    Zend_Registry::get('storage')->getTempDir();
            }
        }
        
        return $this->_pluginConfig;                   
    }
	
	 /**
     * Returns whether the session is valid
     * 
     * @return boolean
     */
    protected function _sessionIsValid()
    {
        $requiredKeys = array('itemsArePublic',  
                              'ownerId',
							  'originalFilename',
							  'filePath');
        foreach ($requiredKeys as $key) {
            if (!isset($this->session->$key)) {
                return false;
            }
        }
        return true;
    }
	
	protected function _getTweetTypeId()
	{
		$db = $this->_helper->db;
		$tweet_type_id = $db->getTable('ItemType')->findByName('Tweet')->id;
		return $tweet_type_id;
	}
	
	 /**
     * Dispatch an import task.
     *
     * @param TwitterImport_Import $TwitterImport The import object
     * @param string $method The method name to run in the TwitterImport_Import object
     */    
    protected function _dispatchImportTask($TwitterImport, $method=null) 
    {        
        if ($method === null) {
            $method = TwitterImport_ImportTask::METHOD_START;
        }
        $twittervConfig = $this->_getPluginConfig();
        
        $options = array(
            'importId' => $TwitterImport->id,
            'memoryLimit' => @$twitterConfig['memoryLimit'],
            'batchSize' => @$twitterConfig['batchSize'],
            'method' => $method,
        );        
        
        $jobDispatcher = Zend_Registry::get('job_dispatcher');
        $jobDispatcher->setQueueName(TwitterImport_ImportTask::QUEUE_NAME);
        $jobDispatcher->sendLongRunning('TwitterImport_ImportTask',
            array(
                'importId' => $TwitterImport->id,
                'memoryLimit' => @$twitterConfig['memoryLimit'],
                'batchSize' => @$twitterConfig['batchSize'],
                'method' => $method,
            )
        );
    }
}