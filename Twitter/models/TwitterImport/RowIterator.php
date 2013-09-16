<?php
/**
 * TwitterImport_RowIterator class
 *
 */
 
class TwitterImport_RowIterator implements SeekableIterator
{
    
    private $_filePath;
    private $_handle;
    private $_currentRow;
    private $_currentRowNumber;
    private $_valid = true;
    private $_skipInvalidRows = true;
    private $_skippedRowCount = 0;

    /**
     * @param string $filePath
     * @param string $columnDelimiter  The column delimiter
     */
   // public function __construct($filePath) 
   // {
   //     $this->_filePath = $filePath;
   // }
    
    
    /**
     * Rewind the Iterator to the first element.
     * Similar to the reset() function for arrays in PHP
     */
    public function rewind()
    {
        if ($this->_handle) {
            fclose($this->_handle);
            $this->_handle = null;
        }
        $this->_currentRowNumber = 0;
        $this->_valid = true;
       
    }

    /**
     * Return the current element.
     * Similar to the current() function for arrays in PHP
     *
     * @return mixed current element
     */
    public function current()
    {
        return $this->_currentRow;
    }

    /**
     * Return the identifying key of the current element.
     * Similar to the key() function for arrays in PHP
     *
     * @return scalar
     */
    public function key()
    {
        return $this->_currentRowNumber;
    }

    /**
     * Move forward to next element.
     * Similar to the next() function for arrays in PHP
     *
     * @throws Exception
     */
    public function next()
    {
        try {
            $this->_moveNext();
        } catch (TwitterImport_FormattingException $e) {
            if ($this->_skipInvalidRows) {
                $this->_skippedRowCount++;
                $this->next();
            } else {
                throw $e;
            }
        }
    }

    /**
     * Seek to a starting position for the file.
     *
     * @param int The offset
     */
    public function seek($index)
    {
        $fh = $this->_getFileHandle();
        fseek($fh, $index);
        $this->_moveNext();
    }

    /**
     * Returns current position of the file pointer
     *
     * @return int The current position of the filer pointer
     */
    public function tell()
    {
        return ftell($this->_getFileHandle());
    }

    /**
     * Move to the next row in the file
     */
    protected function _moveNext()
    {
        if ($nextRow = $this->_getNextRow()) {
            $this->_currentRow = $this->_formatRow($nextRow);
        } else {
            $this->_currentRow = array();
        }
        
        if (!$this->_currentRow) {
            fclose($this->_handle);
            $this->_valid = false;
            $this->_handle = null;
        }
    }

    /**
     * Returns whether the current file position is valid
     *
     * @return boolean
     */
    public function valid()
    {
        if (!file_exists($this->_filePath)) {
            return false;
        }
        if (!$this->_getFileHandle()) {
            return false;
        }
        return $this->_valid;
    }


    /**
     * Returns the number of rows that were skipped since the last time 
     * the function was called.
     *
     * Skipped count is reset to 0 after each call to getSkippedCount(). This 
     * makes it easier to aggregate the number over multiple job runs.
     *
     * @return int The number of rows skipped since last time function was called 
     */
    public function getSkippedCount()
    {
        $skipped = $this->_skippedRowCount;
        $this->_skippedRowCount = 0;
        return $skipped;
    }

    /**
     * Sets whether to skip invalid rows
     *
     * @param boolean $flag
     */
    public function skipInvalidRows($flag)
    {
        $this->_skipInvalidRows = (boolean)$flag;
    }
    
    /**
     * Formats a row
     *
     * @throws LogicException
     * @return array The formatted row
     */
    protected function _formatRow($row)
    {		
        $formattedRow = array(); 
        
		$rawRow = json_decode($row, true);
		
	    $flag = false;
		$missingKey = array();
		
		//Test if any required keys are missing from the row
		if(!isset($rawRow['created_at']))
		{
			$flag = true;
			$missingKey[] = "Tweet_Date_Created";
		}
		if(!isset($rawRow['id_str']))
		{
			$flag = true;
			$missingKey[] = "Tweet_ID";
		}
		if(!isset($rawRow['text']))
		{
			$flag = true;
			$missingKey[] = "Tweet_Text";
		}
		if(!isset($rawRow['user']['id_str']))
		{
			$flag = true;
			$missingKey[] = "Tweet_User_ID";
		}
		if(!isset($rawRow['user']['screen_name']))
		{
			$flag = true;
			$missingKey[] = "Tweet_Screen_Name";
		}
		
		if($flag)
		{
			/*$missingMessage = "test";
			foreach($missingKey as $key => $value)
			{
				$missingMessage += $value;
			}
			throw new TwitterImport_FormattingException("Incorrect JSON Twitter format. Missing the following key(s): " . $missingKey[1]);*/
			
			return false;
		} 
			
		
		$formattedRow = array(
		'Tweet_Date_Created' => $rawRow['created_at'], 
		'Tweet_ID' => $rawRow['id_str'], 
		'Tweet_Text' => $rawRow['text'], 
		'Tweet_User_ID' => $rawRow['user']['id_str'], 
		'Tweet_Screen_Name' => $rawRow['user']['screen_name'], 
		'Tweet_Replied_to_ID' => $rawRow['in_reply_to_status_id_str'],
		'Tweet_Replied_to_User_ID' => $rawRow['in_reply_to_user_id_str'],
		'Tweet_Longitude' => $rawRow['coordinates']['coordinates'][0],
		'Tweet_Latitude' => $rawRow['coordinates']['coordinates'][1],
		'Tweet_Retweet_Count' => $rawRow['retweet_count'],
		'Tweet_Favorite_Count' => $rawRow['favorite_count'],
		'Tweet Language' => $rawRow['lang']
		);
		
		if(isset($rawRow['entities']['hashtags'][0]) && array_key_exists('text', $rawRow['entities']['hashtags'][0])){	
			$totalTags = count($rawRow['entities']['hashtags']);
			for($i = 0; $i < $totalTags; $i++){
				$formattedRow['Tweet_Hash_Tags'][$i] = $rawRow['entities']['hashtags'][$i]['text'];
			}
		}
		
		if(isset($rawRow['entities']['user_mentions'][0]) && array_key_exists('screen_name', $rawRow['entities']['user_mentions'][0])){	
			$totalMentions = count($rawRow['entities']['user_mentions']);
			for($i = 0; $i < $totalMentions; $i++){
				$formattedRow['Tweet_Mention_Screen_Names'][$i] = $rawRow['entities']['user_mentions'][$i]['screen_name'];	
			}
		}
		
		if(array_key_exists('media', $rawRow['entities']) && isset($rawRow['entities']['media'][0])){	
			$totalUrls = count($rawRow['entities']['media']);
			
				for($i = 0; $i < $totalUrls; $i++){
					if(isset($rawRow['entities']['media'][$i]['media_url']))
					{
					$formattedRow['Tweet_Media_Urls'][$i] = $rawRow['entities']['media'][$i]['media_url'];
					}
				}
			
		}
        
    	return $formattedRow;
    }

    /**
     * Returns a file handle for the JSON file
     *
     * @return resource The file handle
     */
    protected function _getFileHandle()
    {
        if (!$this->_handle) {
            ini_set('auto_detect_line_endings', true);
            $this->_handle = fopen($this->_filePath, 'r');
        }
        return $this->_handle;
    }

    /**
     * Returns the next row in the JSON file
     *
     * @return array The row
     */
    protected function _getNextRow()
    {
        $currentRow = array();
        $handle = $this->_getFileHandle();
        while (($row = fgets($handle)) !== FALSE) {
            $this->_currentRowNumber++;
            return $row;
        }
    }
	}
    
  