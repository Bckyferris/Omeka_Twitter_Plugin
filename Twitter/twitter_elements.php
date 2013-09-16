<?php
$elements = array(

	 array(
        'name' => 'Tweet Date Created',
        'description' => 'Date and time when tweet was created ("created_at")',
    ),

	 array(
        'name' => 'Tweet ID',
        'description' => 'Twitter assigned ID of the Tweet ("id_str")',
    ),
	
     array(
        'name' => 'Tweet Text',
        'description' => 'Body text of the Tweet ("text")',
    ),
	
	 array(
        'name' => 'Tweet Replied to ID',
        'description' => 'The ID of the Tweet that this Tweet Replied to ("in_reply_to_status_id_str")',
    ),
	
	 array(
        'name' => 'Tweet Replied to User ID',
        'description' => 'The ID of the user that wrote the Tweet that this Tweet Replied to ("in_reply_to_user_id_str")',
    ),
	
     array(
        'name' => 'Tweet User ID',
        'description' => 'Twitter assigned ID of the user ("user":{"id_str"})',
    ),
	
	 array(
        'name' => 'Tweet Screen Name',
        'description' => 'The screen name of the user ("screen_name")',
    ),
    

     array(
        'name' => 'Tweet Latitude',
        'description' => 'Latitude of the Tweet ("coordinates":[,])/Can be used with Geolocation Plugin',
    ),
	
	 array(
        'name' => 'Tweet Longitude',
        'description' => 'Longitude of the Tweet ("coordinates":[,])/Can be used with Geolocation Plugin',
    ),
	
	 array(
        'name' => 'Tweet Retweet Count',
        'description' => 'Number of times this tweet was re-tweeted ("retweet_count")',
		'order' => 9
    ),
	
	 array(
        'name' => 'Tweet Favorite Count',
        'description' => 'Number of times this tweet was favorited ("favorite_count")',
		'order' => 10
    ),
	
	 array(
        'name' => 'Tweet Hash Tag',
        'description' => 'Hash Tag listed in the Tweet ("hashtags":[{"text"}])/Place in Omeka "Tags" as well',
		'order' => 11
    ),
	
	 array(
        'name' => 'Tweet Media Url',
        'description' => 'Links to any media associated with the tweet ("media":[{"media_url"}])',
		'order' => 12
    ),
	
	 array(
        'name' => 'Tweet User Mention Screen Name',
        'description' => 'The screen name of a user mentioned in the Tweet ("user_mentions":[{"screen_name"}])',
		'order' => 13
    ),
	
	 array(
        'name' => 'Tweet Language',
        'description' => 'Language of the Tweet ("lang")',
		'order' => 14
    ),
 );
 


?>