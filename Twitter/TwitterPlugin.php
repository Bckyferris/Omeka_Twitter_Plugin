<?php
/**
* TwitterPlugin class - represents the Twitter plugin
*
*/
defined('TWITTER_DIRECTORY') or define('TWITTER_DIRECTORY', dirname(__FILE__));
define('TWITTER_MAX_LOCATIONS_PER_PAGE', 50);
define('TWITTER_DEFAULT_LOCATIONS_PER_PAGE', 10);

/**
 * Twitter plugin.
 */
 
class TwitterPlugin extends Omeka_Plugin_AbstractPlugin
{
    
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array('install', 
                              'uninstall',
							                'config_form',
            				          'config',
                              'admin_head',
                              'public_head',
                              'items_browse_sql'
                              );

	/**
    * @var array Filters for the plugin.
    */
    protected $_filters = array('public_navigation_items',
                                'admin_navigation_main'
								                );

    /**
     * @var array Options and their default values.
     */
  
    
    /**
    * Install the plugin.
    */
    public function hookInstall()
    {
    		require_once('twitter_elements.php');
        $db = $this->_db;
    
    		//Create Twitter imports table
        $db->query("CREATE TABLE IF NOT EXISTS `{$db->prefix}twitter_import_imports` (
           `id` int(10) unsigned NOT NULL auto_increment,
           `original_filename` text collate utf8_unicode_ci NOT NULL,
           `file_path` text collate utf8_unicode_ci NOT NULL,
		   `owner_id` int unsigned NOT NULL,
		   `is_public` tinyint(1) default '0',
           `file_position` bigint unsigned NOT NULL,
           `status` varchar(255) collate utf8_unicode_ci,
           `added` timestamp NOT NULL default '0000-00-00 00:00:00',
		   `skipped_row_count` int(10) unsigned NOT NULL,
           `skipped_item_count` int(10) unsigned NOT NULL,
           PRIMARY KEY  (`id`)
           ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        //Create Twitter imported items table
        $db->query("CREATE TABLE IF NOT EXISTS `{$db->prefix}twitter_import_imported_items` (
          `id` int(10) unsigned NOT NULL auto_increment,
          `item_id` int(10) unsigned NOT NULL,
          `import_id` int(10) unsigned NOT NULL,
          PRIMARY KEY  (`id`),
          KEY (`import_id`),
          UNIQUE (`item_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
          
    		//Create Tweet item type
    		$db->query("INSERT INTO {$db->prefix}item_types (name,description)
    		            VALUES ('Tweet','A resource containing twitter messages and their relevant metadata');");
    					
    		//Create Tweet metadata elements
    		$element_set_id = $db->getTable('ElementSet')->findByName('Item Type Metadata')->id;
    		$item_type_id = $db->getTable('ItemType')->findByName('Tweet')->id;
    			
  			foreach($elements as $element => $array)
  			{
  				//$order = (int)$array['order'];
  				$query = "INSERT INTO {$db->prefix}elements (element_set_id,name,description)
  				          VALUES (". $element_set_id .",'". $array['name'] ."', '". $array['description'] . "');";
  			  $db->query($query);
  			
  	      //Link the Tweet item type to it's metadata elements	
  				$element_id = $db->getTable('Element')->findByElementSetNameAndElementName('Item Type Metadata', $array['name'])->id;
  				
  				$query = "INSERT INTO {$db->prefix}item_types_elements (item_type_id,element_id)
  				          VALUES (" . $item_type_id . ", " . $element_id . ");";
  			  $db->query($query);
  			}
    			    
          $this->_installOptions();
    }


    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
    		require_once('twitter_elements.php');
        $db = $this->_db;
        $item_type_id = $db->getTable('ItemType')->findByName('Tweet')->id;
            
    		//Delete the tweet metadata elements	
    		foreach($elements as $element => $array)
    		{
    			$query = "DELETE FROM {$db->prefix}elements 
    					  WHERE name = '". $array['name'] ."'";
    			$db->query($query);
    		
    			$query = "DELETE FROM {$db->prefix}item_types_elements
    					  WHERE item_type_id = '". $item_type_id ."'";
    			$db->query($query);
    		} 
    		
    		//Delete the Tweet Item Type
    		$sql = "DELETE FROM {$db->prefix}item_types WHERE name='Tweet'";
    		$db->query($sql);    
        
        // Drop the tables
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}twitter_import_imports`";
        $db->query($sql);
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}twitter_import_imported_items`";
        $db->query($sql);
        
        $this->_uninstallOptions();    
    }
	
	 public function hookConfigForm()
    {      
        include 'config_form.php';        
    }
    
    public function hookConfig($args)
    {
        // Use the form to set default options in the db
        set_option('twitter_link_to_nav', $_POST['geolocation_link_to_nav']);    
		    $perPage = (int)$_POST['per_page'];
        if ($perPage <= 0) {
            $perPage = TWITTER_DEFAULT_LOCATIONS_PER_PAGE;
        } else if ($perPage > TWITTER_MAX_LOCATIONS_PER_PAGE) {
            $perPage = TWITTER_MAX_LOCATIONS_PER_PAGE;
        }
        set_option('twitter_per_page', $perPage); 
    }
	
	
  /**
   * Add a link to the Tweet display to the public navigation browse menu
   *      
	 * @param array Navigation array.
   * @return array Filtered navigation array.
   */
	 public function filterPublicNavigationItems($navArray)
    {
   		 if (get_option('twitter_link_to_nav')) {
            $navArray['Tweets'] = array(
                                            'label'=>__('Tweets'),
                                            'uri' => url('twitter/index/browse') 
                                            );
        }
        return $navArray;     
    }   
    
    
        /**
    * Configure admin theme header
    *
    * @param array $args 
    */
    public function hookAdminHead($args)
    {        
        $request = Zend_Controller_Front::getInstance()->getRequest();        
        if ($request->getModuleName() == 'twitter') {
            queue_css_file('twitter-main');
            queue_js_file('twitter-import');
        }
    }    
    
          /**
    * Configure public theme header
    *
    * @param array $args 
    */
    public function hookPublicHead($args)
    {        
        $request = Zend_Controller_Front::getInstance()->getRequest();        
        if ($request->getModuleName() == 'twitter') {
            queue_css_file('twitter-main');
        }
    }    
    
    /**
     * Add the Simple Pages link to the admin main navigation.
     * 
     * @param array Navigation array.
     * @return array Filtered navigation array.
     */
    public function filterAdminNavigationMain($nav)
    {            
        $nav[] = array(
            'label' => __('Twitter Import'),
            'uri' => url('twitter'),
        );       
        return $nav;
    }   
    
    /**
     * Use SQL to display only Tweets on the browse page
     * 
     * @param array $args.
     */
    public function hookItemsBrowseSql($args)
    {
      $select = $args['select'];
      $params = $args['params'];
      $columns = array_keys(get_class_vars('item'));
       foreach($columns as $column) {
            if(array_key_exists($column, $params)) {
                $select->where("$column = ?", $params[$column]);
            }
        }
      
    }          
}